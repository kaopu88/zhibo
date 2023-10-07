<?php

namespace app\h5\controller;

use bxkj_module\service\User;
use think\Db;

class Promotion extends BxController
{
    public function index()
    {
        return $this->fetch();
    }

    public function bindview()
    {
        return $this->fetch();
    }

    public function applyview()
    {
        return $this->fetch();
    }

    /*
     * 获取当前登录用户信息
     */
    public function getuser()
    {
        $user_id = request()->param('user_id');

        $user = new User();
        $user_info = $user->getUser($user_id);

        $data_info['user_id'] = $user_id;
        $data_info['nickname'] = $user_info['nickname'];
        $data_info['avatar'] = img_url($user_info['avatar'], '200_200', 'avatar');
        $data_info['cover'] = img_url($user_info['cover'], 'film', 'logo');

        $promoter = Db::name('promoter')->where(['user_id'=>$user_id])->find();

        $agent_admin = Db::name('agent_admin')->where("FIND_IN_SET($user_id,promoter_uid)")->where(['status'=>1])->field('id,username,phone,status')->find();

        if( !empty($promoter) ){
            $data_info['agent_id'] = $promoter['agent_id'];
        }

        if( !empty($agent_admin) ){
            $data_info['agent_admin'] = $agent_admin;
        }

        return $this->success('ok', $data_info);
    }

    public function getuserinfo(){
        $user_id = request()->param('user_id');
        $user = Db::name('user')->where(['user_id'=>$user_id])->field('user_id, nickname')->find();
        if( !$user ){
            $this->error('没有该用户');
        }

        $data = $user['nickname'] . '('. $user['user_id'] .')';

        return $this->success('', $data);
    }

    public function bind()
    {
        $user_id = request()->param('user_id');
        $username = request()->param('username');
        $password = request()->param('password');

        if (empty($username)) $this->error('用户名不能为空');
        if (empty($password)) $this->error('密码不能为空');

        $where = array('delete_time' => null);
        if (validate_regex($username, 'phone')) {
            $where['phone'] = $username;
        } else {
            $where['username'] = $username;
        }
        if (empty($where)) return $this->error('请输入用户名');
        $agent_admin = Db::name('agent_admin')->where($where)->find();
        if (empty($agent_admin)) return $this->error('用户名或密码错误');

        $promoter = Db::name('promoter')->where(['user_id'=>$user_id])->find();

        if( $promoter['agent_id'] != $agent_admin['agent_id'] ){
            return $this->error('该帐号不属于您的公会!');
        }

        $old = str_pad('', 40, '*');
        if ($agent_admin['salt'] == $old) {
            $auth = md5(md5('rCt52pF2cnnKNB3Hkp' . $password));
        } else {
            $auth = sha1($password . $agent_admin['salt']);
        }

        if ( $auth != $agent_admin['password'] ){
            return $this->error('用户名或密码错误');
        }

        if( empty($agent_admin['promoter_uid']) ){
            $data = ['promoter_uid' => $user_id];
        } else {
            $data = [
                'promoter_uid' => $agent_admin['promoter_uid'] . ',' . $user_id
            ];
        }
        $reslut = Db::name('agent_admin')->where($where)->update($data);
        return $this->success('绑定成功', $reslut);
    }

    public function getlist()
    {
        $user_id = request()->param('user_id');
        $offset = (int)request()->param('offset');
        $listRows = 20;

        $list = [];

        $where = [
            'promoter_uid' => $user_id
        ];

        $total = Db::name('promotion_relation_apply')->where($where)->count();
        $list = Db::name('promotion_relation_apply')->where($where)->limit($offset,$listRows)->order('create_time desc')->select();

        $user = new User();

        foreach ( $list as &$item ){
            $user_info = $user->getUser($item['user_id']);
            $item['nickname'] = $user_info['nickname'];
            $item['avatar'] = img_url($user_info['avatar'],'200_200','avatar');
            $item['create_time'] = time_format($item['create_time'],'','date');
            $item['review_time'] = time_format($item['review_time'],'','date');
        }

        return $this->success('返回成功', ['total'=>$total, 'list'=>$list]);
    }

    public function info(){
        $uid = request()->param('user_id');
        $id = request()->param('id');
        $where = [
            'id' => $id,
            'promoter_uid' => $uid,
        ];
        $data = Db::name('promotion_relation_apply')->where($where)->find();
        if( !empty($data) ){
            $user = Db::name('user')->where(['user_id'=>$data['user_id']])->field('user_id, nickname')->find();
            $data['user_info'] = $user['nickname'] . '(UID:'. $user['user_id'] .')';
            $data['create_time'] = time_format($data['create_time'],'','date');
            $data['review_time'] = time_format($data['review_time'],'','date');
            if( !empty($data['pics']) ){
                $data['pic_list'] = explode(',', $data['pics']);
            }
        }

        return $this->success('返回成功', $data);
    }

    public function apply()
    {
        $user_id = request()->param('user_id');
        $agent_id = request()->param('agent_id');
        $promoter_uid = request()->param('promoter_uid');
        $remark = request()->param('remark');
        $pics = request()->param('pics');

        if( !empty($pics) ){
            $pics = implode(",", $pics);
        }

        if( empty($user_id) || empty($agent_id) || empty($promoter_uid) ){
            return $this->error('信息错误!');
        }

        if( $user_id == $promoter_uid ){
            return $this->error('不能绑定自己!');
        }

        $user_info = Db::name('user')->where(['user_id'=>$user_id])->find();

        if( empty($user_info) ){
            return $this->error('没有找到该用户,请填写正确的用户ID!');
        }

        $data = [
            'user_id' => $user_id,
            'agent_id' => $agent_id,
            'promoter_uid' => $promoter_uid,
            'remark' => $remark,
            'pics' => $pics,
            'create_time' => time()
        ];

        $where = [
            'user_id' => $user_id,
            'agent_id' => $agent_id
        ];

        //是否有申请中
        $is_use = Db::name('promotion_relation_apply')->where($where)->where(['promoter_uid'=>$promoter_uid, 'status'=>0])->count();

        if( $is_use > 0 ){
            return $this->error('已有申请审核中,请等待管理员审核!');
        }

        $is_bind = Db::name('promotion_relation')->where($where)->find();

        if( $is_bind && $is_bind['promoter_uid'] > 0 ){
            return $this->error('该用户已绑定!');
        }

        $reslut = Db::name('promotion_relation_apply')->insertGetId($data);

        return $this->success('绑定已提交,等待审核', $reslut);
    }
}