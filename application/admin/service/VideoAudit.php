<?php

namespace app\admin\service;

use app\friend\service\FriendCircleMessage;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use bxkj_module\exception\ApiException;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use bxkj_module\service\UserRedis;
use think\Db;

class VideoAudit extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('video_unpublished');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('video_unpublished');
        $this->setWhere($get)->setOrder($get)->setJoin();
        $result = $this->db->limit($offset, $length)->select();
        $result = $result ? $result : [];
        $this->parseList($get, $result);
        return $result;
    }

    protected function parseList($get, &$result)
    {
        $appAdmins = $this->getRelList($result, function ($aids) {
            $adminService = new Admin();
            $admins       = $adminService->getAdminsByIds($aids);
            return $admins;
        }, 'aid');
        $users = $this->getRelList($result, function ($userIds) {
            $userService = new User();
            $users       = $userService->getUsersByIds($userIds);
            return $users;
        }, 'user_id');
        foreach ($result as &$item) {
            if (!empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $appAdmins, 'id');
            }
            if (!empty($item['user_id'])) {
                $item['user'] = self::getItemByList($item['user_id'], $users, 'user_id');
            }
            $item['duration_str']  = duration_format($item['duration']);
            $item['file_size_str'] = format_bytes($item['file_size']);
            if (!empty($item['goods_id'])) {
                $goods         = Db::name('goods')->field('img,short_title')->where(['id' => $item['goods_id']])->find();
                $item['goods'] = $goods;
            }
        }
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['aid'] != '') {
            $where[] = ['vu.aid', '=', $get['aid']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['vu.audit_status', '=', $get['audit_status']];
        }
        $this->db->where($where);
        return $this;
    }

    private function setJoin()
    {
        $this->db->alias('vu')->join('__USER__ user', 'user.user_id=vu.user_id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.phone,user.level,user.remark_name,user.is_creation');
        $this->db->field('vu.id,vu.describe,vu.user_id,vu.video_id,vu.video_url,vu.animate_url,vu.cover_url,vu.topic,vu.friends,vu.tags,vu.tag_names,vu.location_lng,vu.location_lat,vu.city_id,vu.city_name,vu.audit_status,vu.audit_time,vu.aid,vu.reason,vu.source,vu.duration,vu.width,vu.height,vu.file_size,vu.copy_right,
        vu.rating,vu.status,vu.score,vu.location_name,vu.location_id,vu.music_id,vu.visible,vu.process_status,vu.create_time,vu.location_name');
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            if ($get['audit_status'] == '1') {
                $order['vu.id']       = 'desc';
                $order['vu.app_time'] = 'asc';
            } else if (!empty($get['audit_status'])) {
                $order['vu.audit_time'] = 'desc';
                $order['vu.id']         = 'desc';
            } else {
                $order['vu.create_time'] = 'desc';
                $order['vu.id']          = 'desc';
            }
        }
        $this->db->order($order);
        return $this;
    }

    public function audit_update($inputData)
    {
        if (empty($inputData['id'])) return $this->setError('请选择记录');
        $rating     = round($inputData['rating'] * 10);
        $copy_right = $inputData['copy_right'];
        if (!isset($copy_right) || !in_array($copy_right, ['0', '1'])) return $this->setError('是否有第三方平台的水印LOGO或者包含侵权内容？');
        if ($rating <= 0) return $this->setError('请对视频进行评分');
        $weight = $inputData['weight'];
        if ($weight < 0 || $weight >9) return $this->setError('权重范围错误');
        $where     = [['id', '=', $inputData['id']]];
        $videoInfo = Db::name('video_unpublished')->where($where)->find();
        if (empty($videoInfo)) return $this->setError('视频不存在');
        $update = [
            'describe'   => $inputData['describe'],
            'tags'       => $inputData['tags'],
            'tag_names'  => $inputData['tag_names'],
            'weight'  => $inputData['weight'],
            'copy_right' => $copy_right,
            'rating'     => $rating
        ];
        $num    = Db::name('video_unpublished')->where(['id' => $videoInfo['id']])->update($update);
        if (!$num) return $this->setError('操作失败');
        $rabbitChannel = new RabbitMqChannel(['video.update']);
        $rabbitChannel->exchange('main')->sendOnce('video.update.refresh', ['id' => $videoInfo['id']]);
        return $num;
    }

    //通过
    public function pass($inputData, $aid = null)
    {

        if (empty($inputData['id'])) return $this->setError('请选择记录');
        $rating     = round($inputData['rating'] * 10);
        $copy_right = $inputData['copy_right'];
        if (!isset($copy_right) || !in_array($copy_right, ['0', '1'])) return $this->setError('是否有第三方平台的水印LOGO或者包含侵权内容？');
        if ($rating <= 0) return $this->setError('请对视频进行评分');
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        $videoInfo = Db::name('video_unpublished')->where($where)->find();
        if (empty($videoInfo)) return $this->setError('视频不存在');
        if ($videoInfo['audit_status'] != '1') return $this->setError('视频已审核或者在处理中');
        $coverUrl = $videoInfo['cover_url'];
        if (empty($coverUrl) && !empty($videoInfo['ai_cover'])) {
            $coverUrl = $videoInfo['ai_cover'];
        }
        if (empty($coverUrl) && empty($videoInfo['animate_url'])) {
            return $this->setError('缺少封面图片');
        }
        if (empty($videoInfo['video_url']) || empty($videoInfo['width']) || empty($videoInfo['height'])) {
            return $this->setError('缺少视频信息');
        }
        $update = [
            'audit_status' => '2',
            'status'       => '0',
            'audit_time'   => time(),
            'copy_right'   => $copy_right,
            'rating'       => $rating
        ];
        $num    = Db::name('video_unpublished')->where(['id' => $videoInfo['id']])->update($update);
        if (!$num) return $this->setError('通过失败');
       if($inputData['audit_status']==2){
        $findvideo = Db::name('video_unpublished')->where(['id' => $videoInfo['id']])->find();
        $restmsg = Db::name('friend_circle_message')->where(['uid' => $findvideo['user_id']])->select();
        foreach ($restmsg as $k => $v) {
            if (!empty($v['systemplus'])) {
                $videoid = json_decode($v['systemplus'], true);
                if ($videoid['videoID'] == $videoInfo['id']) {
                    $cirMsg      = new FriendCircleMessage();
                    $changstatus = $cirMsg->changeStatus($v['id'], 1);
                }
            }
        }
       }
        //非私密视频直接发布
        if ($videoInfo['visible'] != '2') {
            $rabbitChannel = new RabbitMqChannel(['video.create_publish']);
            $rabbitChannel->exchange('main')->sendOnce('video.create.publish', ['id' => $videoInfo['id']]);
        }
        $message = new Message();
        $message->setSender('', 'helper')->setReceiver($videoInfo['user_id'])->sendNotice([
            'title'   => '您发布的视频已经审核通过',
            'summary' => '点击查看视频',
            'url'     => getJump('video_detail', ['id' => $videoInfo['id']]),
        ]);

        if (!empty($videoInfo['user_id'])) {
            $fansList = Db::name("follow")->field("user_id")->where(["follow_id" => $videoInfo['user_id']])->select();
            if ($fansList) {
                $publish_user = Db::name("user")->field('nickname')->where(["user_id" => $videoInfo['user_id'], 'delete_time' => null])->find();
                foreach ($fansList as $value) {
                    try {
                        $message->setSender('', 'helper')->setReceiver($value['user_id'])->sendNotice([
                            'type' => 'follow_new',
                            'title' => '您关注的'. $publish_user['nickname'] . '发布了短视频',
                            'summary' => '点击进入',
                            'url' => getJump('video_detail', ['id' => $videoInfo['id']])
                        ]);
                    } catch (ApiException $e) {
                        continue;
                    }
                }
            }
        }

        return $num;
    }

    public function turnDown($inputData, $aid = null)
    {
        if (empty($inputData['id'])) return $this->setError('请选择记录');
        if (empty($inputData['reason'])) return $this->setError('请填写驳回原因');
        $where = [['audit_status', '=', '1']];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        $video = Db::name('video_unpublished')->where($where)->where('id', $inputData['id'])->find();
        $num   = Db::name('video_unpublished')->where($where)->where('id', $inputData['id'])->update([
            'audit_status' => '3',
            'audit_time'   => time(),
            'reason'       => $inputData['reason'] ? $inputData['reason'] : '人工审核未通过'
        ]);
        if (!$num) return $this->setError('驳回失败');
        $rabbitChannel = new RabbitMqChannel(['video.delete', 'user.credit']);
        if ($inputData['audit_status'] == '13') {
            $rabbitChannel->exchange('main')->send('video.delete', ['id' => $inputData['id']]);
        }
        $rabbitChannel->exchange('main')->send('user.credit.video_turndown', ['user_id' => $video['user_id'], 'video_id' => $inputData['id'], 'reason' => $inputData['reason']]);
        $rabbitChannel->close();
        $message = new Message();
        $message->setSender('', 'helper')->setReceiver($video['user_id'])->sendNotice([
            'title'   => '您发布的视频审核未通过',
            'summary' => '原因:' . $inputData['reason'],
            'url'     => '',
        ]);
        return $num;
    }
}