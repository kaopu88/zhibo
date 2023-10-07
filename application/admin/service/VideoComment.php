<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_common\CoreSdk;
use think\Db;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;

class VideoComment extends Service
{
    protected static $commentPrefix = 'comment:';
    
	public function getTotal($get){
		$this->db = Db::name('video_comment');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
	}

    private function setJoin()
    {
        $this->db->alias('vc')->join('__USER__ user', 'user.user_id=vc.user_id', 'LEFT');
        $this->db->join('__VIDEO_UNPUBLISHED__ vu', 'vu.id=vc.video_id', 'LEFT');
        $this->db->field('vc.*,vu.animate_url,vu.cover_url');
        return $this;
    }

	public function setWhere($get){
        $where = array();
        if (!empty($get['video_id'])) {
            $where[] = ['vc.video_id', '=', $get['video_id']];
        }
        if (!empty($get['user_id'])) {
            $where[] = ['vc.user_id', '=', $get['user_id']];
        }
        if ($get['status'] != '') {
            $where[] = ['vc.status', '=', $get['status']];
        }
        if ($get['city'] != '') {
            $where[] = ['vc.city_id', '=', $get['city']];
        }
        if (!empty($get['master_id'])) {
            $where[] = ['vc.master_id', '=', $get['master_id']];
        }else{
            $where[] = ['vc.master_id', '=', 0];
        }    
        $this->db->setKeywords(trim($get['keyword']),'','number vc.id','vc.content,number vc.id');
        $this->db->setKeywords(trim($get['user_keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
		$this->db->where($where);
		return $this;
	}

	public function setOrder($get){
		$order = array();

        if ($get['sort']=='complex' || empty($get['sort'])) {
            $order['vc.is_top'] = 'desc';
            $order['vc.like_count'] = 'desc';
            $order['vc.create_time'] = 'desc';
        }else{
            $order['vc.'.$get['sort']] = $get['sort_by'];
        }
        
		$this->db->order($order);
		return $this;
	}

	public function getList($get,$offset,$lenth){
		$this->db = Db::name('video_comment');
		$this->setWhere($get)->setOrder($get)->setJoin();
		$result = $this->db->limit($offset,$lenth)->select();
		$result = $result ? $result : [];
		$this->parseList($get,$result);
		return $result;
	}

    public function delete($ids)
   {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择评论');
        $total = 0;
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        foreach ($ids as $id) {
            Service::startTrans();
            $comment = Db::name('video_comment')->where('id',$id)->find();
            $num = Db::name('video_comment')->where('id', $id)->whereOr('reply_id', $id)->whereOr('master_id', $id)->delete();
            $comment_count = Db::name('video_comment')->where(['video_id'=>$comment['video_id']])->count();
            $comment_count--;
            if ($comment_count>0)
            {
                $update = Db::name('video')->where(['id'=>$comment['video_id']])->update(array('comment_sum'=>$comment_count));
                if (!$update) {
                    Service::rollback();
                    continue;
                }
            }

            if (!$num) {
                Service::rollback();
                continue;
            }
            $redis = RedisClient::getInstance();
            $redis->del(self::$commentPrefix.$id); //该评论下的点赞记录删除
            $rabbitChannel->exchange('main')->send('user.behavior.comment_delete', [
                'user_id' => $comment['user_id'],
                'video_id' => $comment['video_id'],
                'del_num' => $num,
                'parent_id' => isset($comment['master_id']) ? $comment['master_id'] : 0,
                'comment_id' => $id
            ]);
            Service::commit();
            $total++;
        }
        $rabbitChannel->close();
        if (!$total) return $this->setError('删除评论失败');
        return $total;
    }

	public function parseList($get,&$result){
		$relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');

        $outKeySecond = 'reply';
        $recAccountsSecond = $this->getRelList($result, [new User(), 'getUsersByIds'], 'reply_uid');

        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if (!empty($item['reply_uid'])) {
                $item[$outKeySecond] = self::getItemByList($item['reply_uid'], $recAccountsSecond, $relKey);
            }
            if (!empty($item['city_id'])) {
                $item['city_name'] = Db::name('region')->where(array('id' => $item['city_id']))->value('name');
            }
        }
	}

    public function changeHotStatus($ids, $is_hot)
    {
        if (!in_array($is_hot, ['0', '1'])) return false;
        $num = Db::name('video_comment')->whereIn('id', $ids)->update(['is_hot' => $is_hot]);
        return $num;
    }

    public function changeDelicateStatus($ids, $is_delicate)
    {
        if (!in_array($is_delicate, ['0', '1'])) return false;
        $num = Db::name('video_comment')->whereIn('id', $ids)->update(['is_delicate' => $is_delicate]);
        return $num;
    }

    public function changeTopStatus($ids, $is_top)
    {
        if (!in_array($is_top, ['0', '1'])) return false;
        $num = Db::name('video_comment')->whereIn('id', $ids)->update(['is_top' => $is_top]);
        return $num;
    }

    public function changeSensitiveStatus($ids, $is_sensitive)
    {
        if (!in_array($is_sensitive, ['0', '1'])) return false;
        $num = Db::name('video_comment')->whereIn('id', $ids)->update(['is_sensitive' => $is_sensitive]);
        return $num;
    }
}