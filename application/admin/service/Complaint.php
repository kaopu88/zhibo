<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;
use bxkj_common\RabbitMqChannel;

class Complaint extends Service
{
    public function getTotal($get){
        $this->db = Db::name('complaint');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['aid'])) {
            $where['aid'] = $get['aid'];
        }
        if ($get['audit_status'] != '') {
            $where['audit_status'] = $get['audit_status'];
        }
        if (!empty($get['cid'])) {
            $where['cid'] = $get['cid'];
        }
        if (!empty($get['target_type'])) {
            $where['target_type'] = $get['target_type'];
        }
        if (!empty(trim($get['user_id']))) {
            $where['user_id'] = trim($get['user_id']);
        }
        if (!empty(trim($get['to_uid']))) {
            $where['to_uid'] = trim($get['to_uid']);
        }
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        if (empty($get['sort'])) {
            if ($get['audit_status'] == '0') {
                $order['create_time'] = 'asc';
                $order['id'] = 'asc';
            } else if (empty($get['audit_status'])) {
                $order['create_time'] = 'desc';
                $order['id'] = 'desc';
            } else {
                $order['handle_time'] = 'desc';
                $order['id'] = 'desc';
            }
        }
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('complaint');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'to_uid');

        $auditAdmins = [];
        if (empty($get['aid'])) {
            $auditAids = self::getIdsByList($result, 'aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }
        foreach ($result as &$item) {
            if (!empty($item['to_uid'])) {
                $item['to_user'] = self::getItemByList($item['to_uid'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if (!empty($item['target_type'])) {

                if ($item['target_type']=='film') {
                    $item['target_info'] = Db::name('video')->where('id',$item['target_id'])->find();
                }else if ($item['target_type']=='comment') {
                    $item['target_info'] = Db::name('video_comment')->where('id',$item['target_id'])->find();
                }else{
                    $item['target_info'] = Db::name($item['target_type'])->where($item['target_type']=='user' ? 'user_id' : 'id',$item['target_id'])->find();
                }

                switch ($item['target_type']) {
                    case 'user':
                        $item['target_type_name'] = '用户';
                        break;
                    case 'comment':
                        $item['target_type_name'] = '评论';
                        break;
                    case 'film':
                        $item['target_type_name'] = '小视频';
                        break;
                    case 'music':
                        $item['target_type_name'] = '音乐';
                        break;
                    default:
                        break;
                }

            }
            if (!empty($item['cid'])) {
                $item['cinfo'] = Db::name('complaint_category')->where('id',$item['cid'])->find();
            }
            if (empty($get['aid']) && !empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $auditAdmins);
            }
        }
    }

    public function handler_user($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('complaint')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '11', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update = ['handle_time' => time(), 'audit_status' => $status==2 ? 2 : 1];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('complaint')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);

        if ($status == '2') {
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_false', ['user_id' => $order['target_id'], 'nickname'=>$order['user_id']]);
        }else{
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_true', ['user_id' => $order['target_id'], 'nickname'=>$order['user_id']]);
            if ($status == '11') {
                $id = $order['target_id'];
                $change_status = $inputData['change_status'];
                if (!in_array($change_status, ['0', '1'])) return $this->setError('状态值不正确');
                $disable_length = $inputData['disable_length'];
                $disable_desc = $inputData['disable_desc'];
                $userService = new \app\admin\service\User();
                $num = $userService->changeStatus([$id], $change_status, null, $disable_length, $disable_desc, $aid);
                if (!$num) return $this->setError('切换状态失败');
            }
        }
        Service::commit();
        return array_merge($order, $update);
    }

    public function handler_film($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('complaint')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update = ['handle_time' => time(), 'audit_status' => $status];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('complaint')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        if ($status == '1') {
            $id = $order['target_id'];
            if (empty($id)) return $this->setError('请选择视频');
            $videoService = new \app\admin\service\Video();
            $num = $videoService->changeStatus([$id], 0);
            if (!$num) return $this->setError($videoService->getError());
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_true', ['user_id' => $order['to_uid'], 'nickname'=>$order['user_id']]);
        }
        if ($status == '2') {
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_false', ['user_id' => $order['to_uid'], 'nickname'=>$order['user_id']]);
        }
        Service::commit();
        return array_merge($order, $update);
    }

    public function handler_music($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('complaint')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update = ['handle_time' => time(), 'audit_status' => $status];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('complaint')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        if ($status == '1') {
            $id = $order['target_id'];
            if (empty($id)) return $this->setError('请选择音乐');
            Db::name('music')->where('id', $id)->update(array('status'=>0));
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_true', ['user_id' => $order['to_uid'], 'nickname'=>$order['user_id']]);
        }
        if ($status == '2') {
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_false', ['user_id' => $order['to_uid'], 'nickname'=>$order['user_id']]);
        }
        Service::commit();
        return array_merge($order, $update);
    }

    public function handler_comment($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('complaint')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update = ['handle_time' => time(), 'audit_status' => $status];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('complaint')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        if ($status == '1') {
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_true', ['user_id' => $order['to_uid'], 'nickname'=>$order['user_id']]);
            $videoCommentService = new \app\admin\service\VideoComment();
            $num = $videoCommentService->delete($order['target_id']);
            if (!$num) return $this->setError('删除失败');
        }else{
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint_false', ['user_id' => $order['to_uid'], 'nickname'=>$order['user_id']]);
        }
        Service::commit();
        return array_merge($order, $update);
    }

    public function getCategory($get,$offset,$lenth)
    {
        $this->db = Db::name('complaint_category');
        $this->setCategoryWhere($get)->setCategoryOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        return $result;
    }

    public function getCategoryTotal($get){
        $this->db = Db::name('complaint_category');
        $this->setCategoryWhere($get);
        return $this->db->count();
    }

    public function setCategoryWhere($get){
        $where = array();
        if (!empty($get['target'])) {
            $where['target'] = $get['target'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','name');
        $this->db->where($where);
        return $this;
    }

    public function setCategoryOrder($get){
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function category_add($inputData)
    {
        $data = $this->df->process('add@complaint_category', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('complaint_category')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function category_edit($inputData)
    {
        $data = $this->df->process('update@complaint_category', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('complaint_category')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }
}