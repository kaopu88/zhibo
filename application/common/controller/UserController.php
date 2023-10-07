<?php

namespace app\common\controller;

use bxkj_module\exception\ApiException;
use think\facade\Request;

class UserController extends Controller
{
    //不需要登录的接口
    protected $notLoginList = [
        'account'    => ['__un__'],
        'feedback'   => ['reportlist'],
        'comment'    => ['commentlist', 'getcommentlist', 'getsubcommentlist'],
        'video'      => ['__un__', 'playhistory', 'clearplayrecord', 'support', 'otherpulishfilm', 'publish', 'delownfilm', 'downrecord', 'ownpublishfilm', 'downfilm', 'downlist', 'cleardownrecord', 'buyfilm', 'search', 'suggest', 'sendgift', 'openreward', 'preopenreward'],
        'film'       => ['__un__', 'playhistory', 'clearplayrecord', 'support', 'otherpulishfilm', 'publish', 'delownfilm', 'downrecord', 'ownpublishfilm', 'downfilm', 'downlist', 'cleardownrecord', 'buyfilm', 'search', 'suggest', 'sendgift'],
        'user'       => ['search'],
        'vip'        => ['index'],
        'room'       => ['hotlivelist', 'newlivelist', 'filmlivelist', 'newnotice', 'noticelist', 'pklivelist', 'livelist', 'getnavtree', 'gethotlivelist', 'webonlineaudience'],
        'gift'       => ['onlineupgrade', 'onlineupgradetest', 'getgiftresources'],
        'personal'   => ['gethomeinfo', 'getlikelist', 'getuservideo'],
        'behavior'   => ['watch'],
        'friends'    => ['friendcodeto'],
        'taokegoods' => ['getshopcatelist','analysiscontent','getshopgoodslist','getgoodsdetail'],
        'taokeshop' => ['getshopdetail'],
        'music'      => ['detailbymusicid', 'videosbymusicid'],
        'millet'     => ['getcharmrank', 'getheroesrank'],
        'taoke.duomai' => ['getlist','receiveorder'],
        'taoke.order' => ['gettborder','getpddorder','getjdorder'],
        'taoke.publisher' => ['accesstoken'],
        'taoke.live' => ['getlivelist'],
        'taoke.share' => ['getshareurl', 'createqrcode'],
        'week_star'     => ['getweekstargiftlist', 'getlist'],


    ];
    //不必须要手机号的接口
    protected $notRequirePhoneList = array(
        'account' => [],
        'comment' => [],
        'user' => ['bindphone'],
        'topic' => ['topicbyvideo', 'topicbyvideopages']
    );

    public function __construct()
    {
        parent::__construct();
        if (!is_allow($this->notLoginList)) {
            if (empty($this->user)) {
                throw new ApiException('请先登录', 1003);
            }
            // if (!is_allow($this->notRequirePhoneList) && empty($this->user['phone'])) {
            //     throw new ApiException('请绑定手机号', 1004);
            // }
        }
    }
}
