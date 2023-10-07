<?php

namespace app\common\controller;

use bxkj_common\CoreSdk;
use bxkj_module\controller\Api;
use bxkj_module\exception\ApiException;
use app\common\service\DsSession;
use app\common\service\User;
use think\facade\Request;

class Controller extends Api
{
    protected static $pnum = 10;
    protected $user;
    protected $accessToken;
    protected $meid;
    protected $v;//ios_70
    protected $notVerifyTokenList = [
        'common' => array('appinit', 'refreshtoken', 'getprivate'),
        'tools' => array('__un__'),
        'test' => array('__un__'),
        'paycallback' => array('__un__'),
        'livecallback' => array('__un__'),
        'home' => array('search'),
        'room'=>array('webonlineaudience'),
        'taokegoods'=>array('getshopcatelist','analysiscontent','getshopgoodslist','getshopdetail','getgoodsdetail'),
        'taoke.cate' => array('__un__'),
        'taoke.seckill' => array('__un__'),
        'taoke.hot_rank' => array('__un__'),
        'taoke.talent_article' => array('__un__'),
        'taoke.brand' => array('__un__'),
        'taoke.special' => array('__un__'),
        'taoke.search' => array('__un__'),
        'taoke.nine' => array('__un__'),
        'taoke.douquan' => array('__un__'),
        'taoke.goods_detail' => array('__un__'),
        'taoke.goods' => array('__un__'),
        'taoke.bussiness' => array('__un__'),
        'taoke.common' => array('__un__'),
        'taoke.collect' => array('__un__'),
        'taoke.duomai' => array('getlist','receiveorder'),
        'taokeshop' => array('getshopdetail'),
        'taoke.theme' => array('getpddthemelist','getpddthemegoodslist','getmaterialthemegoods','getmatrialcatelist'),
        'taoke.order' => ['gettborder','getpddorder','getjdorder'],
        //   'friend.friend' => array('__un__'),
        'taoke.system' => array('__un__'),
        'taoke.share' => array('getshareurl', 'createqrcode'),
        'taoke.circle' => array('__un__'),
        'taoke.publisher' => array('accesstoken'),
        'taoke.live' => array('getlivelist'),
        'week_star' => array('__un__'),
    ];

    public function __construct()
    {
        parent::__construct();
        $this->accessToken = ACCESS_TOKEN;
        if ((!empty($this->accessToken) && !DsSession::isRestore()) || empty($this->accessToken)) {
            $this->accessToken = '';
            if (!is_allow($this->notVerifyTokenList))
                throw new ApiException('access_token invalid', 1000);
        }
        $user = DsSession::get('user');
        if (!empty($user)) {
            $userService = new User();
            $checkRes = $userService->checkLogin($user, APP_OS_NAME);
            $errorMsg = '';
            $error = null;
            if ($checkRes === false) {
                $user = null;
                DsSession::set('user', $user);//清除掉在当前session的登录态
                $error = $userService->getError();
                $errorMsg = $error->getMessage();
                $errorMsg = $errorMsg ? $errorMsg : '登录失效';
            } else {
                //更新用户信息
                if ($checkRes['update_v'] > $user['update_v']) {
                    $core = new CoreSdk();
                    $updateUser = $core->post('user/get_user', ['user_id' => $user['user_id']]);
                    if ($updateUser) {
                        foreach ($user as $k => $value) {
                            if (isset($updateUser[$k])) {
                                $user[$k] = $updateUser[$k];
                            }
                        }
                        $user['update_v'] = $checkRes['update_v'];
                    } else {
                        $user = null;
                        $errorMsg = '用户不存在';
                    }
                    DsSession::set('user', $user);
                }
            }
            $blockUserExceptions = [
                'common' => ['appinit', 'refreshtoken'],
                'home' => ['apphome']
            ];
            if (!empty($errorMsg) && !is_allow($blockUserExceptions)) {
                throw new ApiException((string)$errorMsg, 1002);
            }
        }
        $this->user = DsSession::get('user');

        if ($this->user) {
            $this->user['user_id'] = (string)$this->user['user_id'];
        }
       /* if ($this->user['user_id'] == 10004419) {
            $str = $this->user['user_id']. Request::controller() . '.' . Request::action() .'.' . date("Y-m-d H:i:s") .'-----';
            file_put_contents('shouji.txt', $str, FILE_APPEND );
        }*/

        define('USERID', $this->user ? $this->user['user_id'] : '');
        define('USERTAG', !empty(USERID) ? USERID : ACCESS_TOKEN);
    }
}
