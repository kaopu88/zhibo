<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use Qiniu\Auth;
use think\Db;
use bxkj_common\RedisClient;

class Gift extends Service
{

    protected static $cid = [0=>'直播间礼物', 1=>'视频礼物', 10=>'道具礼物'];

    protected static $giftPrefix = 'BG_GIFT:', $giftKey = 'gift', $giftresourcesKey = 'resources', $giftguard = 'guard_all', $giftvideoKey = 'video',$giftvoiceKey = 'voice', $guardKey = 'guard';

    protected static $type = ['大礼物', '小礼物'];

    protected static $privileges = ['leave_msg'=>'留言'];

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('gift');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function add($inputData)
    {
        Service::startTrans();
        $data = $this->df->process('add@gift', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('gift')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        $this->delLiveGift();
        if (!empty($data['file'])) {
            $res = $this->updateFileInfo($id, $data['file']);
            if (!$res) return false;
        }
        Service::commit();
        return $id;
    }

    public function delLiveGift()
    {
        $redis = RedisClient::getInstance();
        $redis->del(self::$giftPrefix.self::$giftKey);
        $redis->del(self::$giftPrefix.self::$giftresourcesKey);
        $redis->del(self::$giftPrefix.self::$guardKey);
        $redis->del(self::$giftPrefix.self::$giftvideoKey);
        $redis->del(self::$giftPrefix.self::$giftvoiceKey);
    }

    /**
     * 验证礼物资源包
     *
     * @param $value
     * @param $rule
     * @param null $data
     * @param null $more
     * @return bool
     */
    public function giftResource($value, $rule, $data = null, $more = null)
    {
        if ($data['type'] == 1) return true;

        if (empty($value)) return false;

        return true;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@gift', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $saveData = $this->df->getLastData(false);
        $num = Db::name('gift')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        $this->delLiveGift();
        if (!empty($data['file']) && $saveData['file'] != $data['file']) {
            $this->updateFileInfo($data['id'], $data['file']);
        } else if (!empty($saveData['file']) && empty($saveData['file_size'])) {
            $this->updateFileInfo($data['id'], $saveData['file']);
        }
        return $num;
    }

    protected function updateFileInfo($id, $file_path)
    {
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');
        $bucket = config('upload.platform_config.bucket');
        $base = config('upload.platform_config.base_url');
        $key = ltrim(str_replace($base, '', $file_path), '/');
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        list($fileInfo, $err) = $bucketManager->stat($bucket, $key);
        if ($err) return $this->setError('获取资源包信息错误' . $err->message());
        $num = Db::name('gift')->where('id', $id)->update(['file_size' => $fileInfo['fsize']]);
        if (!$num) return $this->setError('更新资源包信息错误');
        return $fileInfo;
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('gift');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (empty($result)) return [];
        foreach ($result as &$value)
        {
            $value['cid_str'] = self::$cid[$value['cid']];

            $value['type_str'] = self::$type[$value['type']];

            $value['discount_str'] = $value['discount'] == 1 ? '无' : $value['discount'];

            $value['privileges_str'] = self::$privileges[$value['privileges']];
        }
        return $result;
    }



    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }

        if ($get['type'] != '') {
            $where[] = ['type', '=', $get['type']];
        }

        if ($get['is_week_star'] != '') {
            $where[] = ['is_week_star', '=', $get['is_week_star']];
        }

        if ($get['cid'] != '') {
            $where[] = ['cid', '=', $get['cid']];
        }

        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');

        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('gift')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }

    public function changeGuard($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('gift')->whereIn('id', $ids)->update(['isguard' => $status]);
        return $num;
    }

    public function changeWeek($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('gift')->whereIn('id', $ids)->update(['is_week_star' => $status]);
        return $num;
    }

    public function delete($ids)
    {
        $num = Db::name('gift')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除失败');
        $this->delLiveGift();
        return $num;
    }


    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('gift');
        $this->db->setKeywords($keyword, '', '', 'name');
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['id'],
                'name' => $item['name']
            ];
        }
        return $arr;
    }


    public function getGiftsByIds($giftIds)
    {
        if (empty($giftIds)) return [];
        $result = Db::name('gift')->whereIn('id', $giftIds)->field('id,name,picture_url')->limit(count($giftIds))->select();
        return $result ? $result : [];
    }

    public function getAllGuard()
    {
        $result = Db::name('gift')->where(['isguard' => 1])->field('id')->select();
        $redis = RedisClient::getInstance();
        if (empty($result)) $redis->del(self::$giftPrefix.self::$giftguard);
        $allId = array_column($result, 'id');
        $redis->set(self::$giftPrefix.self::$giftguard, json_encode($allId));
        return true;
    }

}