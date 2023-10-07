<?php

namespace bxkj_common\wxsdk;

use think\Db;

class WxUser extends WxModel
{
    protected $app;

    public function __construct(WxApp $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    public function updateByOpenId($openid)
    {
        $where = array(
            'appid' => $this->app->getAppId(),
            'openid' => $openid
        );
        $result = Db::name('wx_user')->where($where)->find();
        $this->updateRowData($result ? $result : array());
    }

    public function save($inputData)
    {
        $id = $this->data['id'];
        if (!empty($id)) {
            $inputData['update_time'] = time();
            $num = Db::name('wx_user')->where(array('id' => $id))->update($inputData);
            if (!$num) return $this->setError('更新失败');
        } else {
            unset($inputData['id']);
            $inputData['create_time'] = time();
            $inputData['appid'] = $this->app->getAppId();
            $id = Db::name('wx_user')->insertGetId($inputData);
            if (!$id) return $this->setError('新增失败');
            $inputData['id'] = $id;
        }
        $this->updateRowData($inputData);
        return $id;
    }

    //关联本地用户
    public function relationLocalUser($userId)
    {
        $data['user_id'] = $userId;
        // 待修改
        $data['wx_id'] = $this->data['id'];
        $result = $this->db->where($data)->select('id')->limit(1)->get('user_weixin')->row_array();
        if (empty($result)) {
            $data['appid'] = $this->data['appid'];
            $data['openid'] = $this->data['openid'];
            $data['create_time'] = time();
            $id = $this->db->insert('user_weixin', $data) ? $this->db->insert_id() : false;
        } else {
            $id = $result['id'];
        }
        if (!$id) return $this->setError('关联失败');
        return $id;
    }
}