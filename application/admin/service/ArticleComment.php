<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class ArticleComment extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('article_comment');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('article_comment');
        $this->db->field('user.user_id,user.nickname,user.remark_name,user.avatar,user.phone,user.level');
        $this->db->field('comment.id,comment.content,comment.master_uid,comment.master_id,comment.reply_id,comment.reply_uid,comment.rel_type,comment.rel_id,comment.like_num,
        comment.is_top,comment.is_author,comment.create_time,comment.audit_status,comment.audit_aid,comment.audit_time');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $this->parseList($get, $result);
        return $result;
    }

    private function getRelInfo($relType, $relId)
    {
        $relInfo = null;
        switch ($relType) {
            case 'art':
                $relInfo = Db::name('article')->field('id,title,summary,image,aid')->where('id', $relId)->find();
                break;
            case 'movie':
                $relInfo = Db::name('movie')->field('id,title,descr summary,image,aid')->where('id', $relId)->find();
                break;
        }
        return $relInfo;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $this->db->alias('comment');
        $this->db->join('__USER__ user', 'user.user_id=comment.user_id', 'LEFT');
        $where = [];
        if ($get['id'] != '') {
            $where[] = ['comment.id', '=', $get['id']];
        } else {
            if ($get['rel_type'] != '' && $get['rel_id'] != '') {
                $where[] = ['comment.rel_type', '=', $get['rel_type']];
                $where[] = ['comment.rel_id', '=', $get['rel_id']];
            }
            if ($get['is_top'] != '') {
                $where[] = ['comment.is_top', '=', $get['is_top']];
            }
            if ($get['reply_id'] != '') {
                $where[] = ['comment.reply_id', '=', $get['reply_id']];
            }
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.phone');
        $this->db->setKeywords($get['content'], '', 'number comment.id', 'comment.content');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['comment.is_top'] = 'desc';
            $order['comment.create_time'] = 'desc';
            $order['comment.id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    //作者回复
    public function reply($inputData)
    {
        $replyId = $inputData['reply_id'];
        if (empty($inputData['content'])) return $this->setError('回复内容不能为空');
        if (!empty($replyId)) {
            $replyInfo = Db::name('article_comment')->where(['id' => $replyId])->find();
            if (empty($replyInfo)) return $this->setError('回复的评论不存在');
            $data['reply_id'] = $replyId;
            $data['reply_uid'] = $replyInfo['user_id'];
            $data['master_id'] = $replyInfo['master_id'] ? $replyInfo['master_id'] : $replyInfo['id'];
            $data['master_uid'] = $replyInfo['master_uid'] ? $replyInfo['master_uid'] : $replyInfo['user_id'];
            $relType = $replyInfo['rel_type'];
            $relId = $replyInfo['rel_id'];
        } else {
            $relType = $inputData['rel_type'];
            $relId = $inputData['rel_id'];
        }
        if (empty($relId)) return $this->setError('请选择关联内容');
        if ($relType == 'art') {
            $info = Db::name('article')->where(['id' => $relId])->find();
            if (empty($info)) return $this->setError('文章不存在');
        } else if ($relType == 'movie') {
            $info = Db::name('movie')->where(['id' => $relId])->find();
            if (empty($info)) return $this->setError('电影不存在');
        } else {
            return $this->setError('关联类型不正确');
        }
        $sha1 = sha1($inputData['content'] . '_' . $relType . $relId . '0');
        $has = Db::name('article_comment')->where(['sha1' => $sha1])->count();
        if ($has > 0) return $this->setError('评论内容重复');
        $data['content'] = $inputData['content'];
        $data['sha1'] = $sha1;
        $data['user_id'] = '0';
        $data['create_time'] = time();
        $data['rel_type'] = $relType;
        $data['rel_id'] = $relId;
        $data['is_author'] = '1';
        $data['audit_status'] = '1';
        $data['app_time'] = time();
        $data['audit_aid'] = 0;
        $id = Db::name('article_comment')->insertGetId($data);
        if (!$id) return $this->setError('回复失败');
        $data['id'] = $id;
        $commentNum2 = $info['comment_num'] + 1;
        if ($relType == 'art') {
            Db::name('article')->where(['id' => $info['id']])->update(['comment_num' => $commentNum2]);
        } else if ($relType == 'movie') {
            Db::name('movie')->where(['id' => $info['id']])->update(['comment_num' => $commentNum2]);
        }
        return $data;
    }

    public function delete($ids)
    {
        $total = 0;
        foreach ($ids as $id) {
            $info = Db::name('article_comment')->where(['id' => $id])->find();
            if ($info) {
                $relType = $info['rel_type'];
                $relId = $info['rel_id'];
                $num = Db::name('article_comment')->where('id', $id)->whereOr('reply_id', $id)->delete();
                if ($num) {
                    $where2 = ['rel_type' => $relType, 'rel_id' => $relId, 'audit_status' => '1'];
                    $commentNum = Db::name('article_comment')->where($where2)->count();
                    if ($relType == 'art') {
                        Db::name('article')->where(['id' => $relId])->update(['comment_num' => $commentNum]);
                    } else if ($relType == 'movie') {
                        Db::name('movie')->where(['id' => $relId])->update(['comment_num' => $commentNum]);
                    }
                    $total += $num;
                }
            }
        }
        return $total;
    }

    public function getAuditTotal($get)
    {
        $this->db = Db::name('article_comment');
        $this->setAuditWhere($get);
        return $this->db->count();
    }

    public function getAuditList($get, $offset, $length)
    {
        $this->db = Db::name('article_comment');
        $this->db->field('user.user_id,user.nickname,user.remark_name,user.avatar,user.phone,user.level');
        $this->db->field('comment.id,comment.content,comment.master_uid,comment.master_id,comment.reply_id,comment.reply_uid,comment.rel_type,comment.rel_id,comment.like_num,comment.is_top,comment.is_author,comment.create_time,comment.audit_status,comment.reason,comment.audit_aid,comment.audit_time');
        $this->setAuditWhere($get)->setAuditOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $this->parseList($get, $result);
        return $result;
    }

    protected function parseList($get, &$list)
    {
        $replyUsers = self::getRelList($list, function ($ids) {
            $users = Db::name('user')->whereIn('user_id', $ids)->field('user_id,nickname,avatar,phone')->select();
            return $users ? $users : [];
        }, 'reply_uid');
        $replyComments = self::getRelList($list, function ($ids) {
            $comments = Db::name('article_comment')->whereIn('id', $ids)->field('id,content,is_author,is_top')->select();
            return $comments ? $comments : [];
        }, 'reply_id');
        $relInfo = $get['rel_type'] ? $this->getRelInfo($get['rel_type'], $get['rel_id']) : null;
        $admin = [
            'nickname' => '管理员',
            'avatar' => img_url('', '', 'logo'),
            'phone' => ''
        ];
        $auditAdmins = [];
        if (empty($get['aid'])) {
            $auditAids = self::getIdsByList($list, 'audit_aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }
        foreach ($list as &$item) {
            $item['rel_info'] = isset($relInfo) ? $relInfo : $this->getRelInfo($item['rel_type'], $item['rel_id']);
            if (!empty($item['reply_id'])) {
                $item['reply_info'] = self::getItemByList($item['reply_id'], $replyComments, 'id');
                $item['reply_user'] = $item['reply_info']['is_author'] == '1' ? $admin : self::getItemByList($item['reply_uid'], $replyUsers, 'user_id');
            }
            $item['reply_num'] = Db::name('article_comment')->where('reply_id', $item['id'])->count();
            if ($item['is_author'] == '1') $item = array_merge($item, $admin);
            if (empty($get['aid']) && !empty($item['audit_aid'])) {
                $item['audit_admin'] = self::getItemByList($item['audit_aid'], $auditAdmins);
            }
        }
    }

    protected function setAuditWhere($get)
    {
        $this->db->alias('comment');
        $this->db->join('__USER__ user', 'user.user_id=comment.user_id', 'LEFT');
        $where = [];
        if ($get['aid'] != '') $where[] = ['audit_aid', 'eq', $get['aid']];
        if ($get['audit_status'] != '') {
            $where[] = ['comment.audit_status', '=', $get['audit_status']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.phone');
        $this->db->where($where);
        return $this;
    }

    protected function setAuditOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            if ($get['audit_status'] == '0') {
                $order['comment.app_time'] = 'asc';
                $order['comment.id'] = 'asc';
            } else if (empty($get['audit_status'])) {
                $order['comment.create_time'] = 'desc';
                $order['comment.id'] = 'desc';
            } else {
                $order['comment.audit_time'] = 'desc';
                $order['comment.id'] = 'desc';
            }
        }
        $this->db->order($order);
        return $this;
    }

    public function pass($ids, $aid)
    {
        if (empty($ids)) return $this->setError('请选择记录');
        $total = 0;
        foreach ($ids as $id) {
            $where = [['audit_aid', 'eq', $aid], ['audit_status', 'eq', '0'], ['id', 'eq', $id]];
            $num = Db::name('article_comment')->where($where)->update([
                'audit_status' => '1',
                'audit_time' => time()
            ]);
            if (!$num) return $this->setError('审核通过失败');
            $info = Db::name('article_comment')->where('id', $id)->find();
            if ($info) {
                $where2 = ['rel_type' => $info['rel_type'], 'rel_id' => $info['rel_id'], 'audit_status' => '1'];
                $commentNum = Db::name('article_comment')->where($where2)->count();
                if ($info['rel_type'] == 'art') {
                    Db::name('article')->where(['id' => $info['rel_id']])->update(['comment_num' => $commentNum]);
                } else if ($info['rel_type'] == 'movie') {
                    Db::name('movie')->where(['id' => $info['rel_id']])->update(['comment_num' => $commentNum]);
                }
            }
            $total++;
        }
        return $total;
    }

    public function turnDown($inputData, $aid)
    {
        $id = $inputData['id'];
        if (empty($id)) return $this->setError('请选择记录');
        if (empty($inputData['reason'])) return $this->setError('请填写驳回原因');
        $where = [['audit_aid', 'eq', $aid], ['audit_status', 'eq', '0'], ['id', 'eq', $id]];
        $num = Db::name('article_comment')->where($where)->update([
            'audit_status' => '2',
            'reason' => $inputData['reason'],
            'audit_time' => time()
        ]);
        if (!$num) return $this->setError('审核驳回失败');
        return $num;
    }


}