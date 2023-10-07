<?php

namespace app\api\controller;

use app\common\controller\UserController;
use bxkj_common\QrcodeImg;
use app\common\service\DsSession;
use app\api\service\InviteRecord;
use bxkj_common\RedisClient;
use bxkj_module\service\User;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Exception;
use think\facade\Env;
use think\Db;

class Invite extends UserController
{
    protected static $invite = 'user_invite:imgs:';

    public function generateBak()
    {
        $anchor = request()->param('anchor');
        $params = ['user_id' => USERID];

        $userId = DsSession::get('user.user_id');
        $data = [
            'nickname' => DsSession::get('user.nickname'),
            'avatar' => DsSession::get('user.avatar'),
            'user_id' => (string)$userId,
            'level' => DsSession::get('user.level'),
        ];
        if (!empty($anchor)) {
            if ($anchor == USERID) return $this->jsonError('主播不能是自己');
            $anchorInfo = Db::name('user')->field('user_id,avatar,nickname,level')->where(array('user_id' => $anchor, 'status' => '1', 'delete_time' => null))->find();
            if (!$anchorInfo) return $this->jsonError('主播不存在');
            $params['anchor'] = $anchor;
            $data = array_merge($data, $anchorInfo);
        }
        // $url = H5_URL . '/invite/index?' . http_build_query($params);
        $url = FX_URL . '?' . http_build_query($params);
        $filename = sha1($url);
        $qrcodeImg = new QrcodeImg();
        $suffix = substr((string)USERID, strlen(USERID) - 2);
        $suffix = str_pad($suffix, 2, '0', STR_PAD_LEFT);
        $path = ROOT_PATH . "public/static/invite_imgs/{$suffix}/{$filename}.png";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        if (!file_exists($path)) {
            $res = $qrcodeImg->generate($url, $path, '');//不要头像了$avatar
        }
        $data['img_url'] = "/static/invite_imgs/{$suffix}/{$filename}.png";
        $data['url'] = $url;
        $where['invite_uid'] = (string)USERID;
        $invitePeopleNum = Db::name('user')->where([
            'promoter_uid' => USERID,
            'delete_time' => null
        ])->order('create_time desc')->count();
        $data['invite_people_num'] = $invitePeopleNum ? (int)$invitePeopleNum : 0;
        $data['reward_millet'] = (int)Db::name('invite_record')->where($where)->sum('reward_millet');
        return $this->success($data, '获取成功');
    }

    public function getListBak()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        //查询所有当前用户邀请的用户
        $users = Db::name('user')->field('user_id')->where([
            'promoter_uid' => USERID,
            'delete_time' => null
        ])->order('create_time desc')->limit($offset, $length)->select();

        $userIds = [];
        foreach ($users as $user) {
            if (!empty($user['user_id'])) {
                $userIds[] = $user['user_id'];
            }
        }
        $userIds = array_unique($userIds);
        $userService = new User();
        //获取邀请的用户信息
        $userList = $userService->getUsers($userIds);
        $userList = $userList ? $userList : [];
        $result = [];
        //初始化用户信息
        foreach ($users as $userItem) {
            $userId = $userItem['user_id'];
            $user = InviteRecord::getItemByList($userId, $userList, 'user_id');
            $item = [];
            $item['id'] = $user['user_id'];
            $item['invite_uid'] = USERID;
            $item['anchor_uid'] = 0;
            $item['user_id'] = $user['user_id'];
            $item['reward_exp'] = 0;
            $item['reward_bean'] = 0;
            $item['reward_millet'] = 0;
            $item['create_time'] = $user['create_time'];
            $item['anchor'] = [
                'user_id' => ''
            ];
            $item['user'] = copy_array($user, 'user_id,nickname,avatar,millet,millet_status,fre_millet,total_millet,level,
            status,vip_status,vip_expire,bean,sign');
            $result[] = $item;
        }

        return $this->success($result, '获取成功');
    }


    public function generate()
    {
        $anchor = request()->param('anchor');
        $params = ['user_id' => USERID];

        $userId = DsSession::get('user.user_id');
        $data = [
            'nickname' => DsSession::get('user.nickname'),
            'avatar' => DsSession::get('user.avatar'),
            'user_id' => (string)$userId,
            'level' => DsSession::get('user.level'),
        ];
        $user_info = Db::name('user')->where(['user_id' => USERID])->field('is_promoter,invite_code')->find();
        if (!empty($anchor)) {
            if ($anchor == USERID) return $this->jsonError('主播不能是自己');
            $anchorInfo = Db::name('user')->field('user_id,avatar,nickname,level')->where(array('user_id' => $anchor, 'status' => '1', 'delete_time' => null))->find();
            if (!$anchorInfo) return $this->jsonError('主播不存在');
            $params['anchor'] = $anchor;
            $data = array_merge($data, $anchorInfo);
        }
        if (!empty($user_info['invite_code'])) $params['scan_code'] = $user_info['invite_code'];
        // $url = H5_URL . '/invite/index?' . http_build_query($params);
        $url = FX_URL . '?' . http_build_query($params);
        $filename = sha1($url);
        $qrcodeImg = new QrcodeImg();
        $suffix = substr((string)USERID, strlen(USERID) - 2);
        $suffix = str_pad($suffix, 2, '0', STR_PAD_LEFT);
        $path = ROOT_PATH . "public/static/invite_imgs/{$suffix}/{$filename}.png";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        if (!file_exists($path)) {
            $res = $qrcodeImg->generate($url, $path, '');//不要头像了$avatar
        }
        $allImageUrl = $this->synthesisImage($path, $user_info['invite_code'], $suffix); //合成二维码
        $data['img_url'] = DOMAIN_URL . "/static/invite_imgs/{$suffix}/{$filename}.png";
        $data['url'] = $url;
        $where['invite_uid'] = (string)USERID;
        $invitePeopleNum = Db::name('promotion_relation')->where(['promoter_uid' => USERID])->count();
        $data['invite_apply_url'] = $user_info['is_promoter'] ? H5_URL . '/promotion/applyview' : '';
        $data['invite_people_num'] = $invitePeopleNum ? (int)$invitePeopleNum : 0;
        $data['invite_code'] = $user_info['invite_code'];
        $data['allImageUrl'] = $allImageUrl ? $allImageUrl : [];
        $data['reward_millet'] = (int)Db::name('invite_record')->where($where)->sum('reward_millet');
        return $this->success($data, '获取成功');
    }

    //合成用户二维码图片
    protected function synthesisImage($url_path, $inviteCode = '', $suffix = '')
    {
        if (empty($url_path) || empty($inviteCode) || empty($suffix)) return false;
        try {
            $redis = RedisClient::getInstance();
            $poster = $redis->get("poster:bx_live");

            if (empty($poster)) {
                $poster = Db::name('user_poster')->where(['status' => 1])->select();
                if (empty($poster)) return false;
                $poster = json_encode($poster);
                $redis->setnx("poster:bx_live", $poster);
            }
            $data = json_decode($poster, true);
            if (empty($data)) return false;
            $pathUrl = [];
            foreach ($data as $key => $value) {
                $bgImage = ROOT_PATH . "public/" . $value['bg_url'];
                $name = sha1($inviteCode . $suffix . $value['id']);
                $newpath = ROOT_PATH . "public/static/invite_imgs/{$suffix}/" . USERID . '_' . $value['id'] . ".png";
                /*$pathUrl[] = DOMAIN_URL . "/static/invite_imgs/{$suffix}/" . USERID . '_' . $value['id'] . ".png";
                if (file_exists($newpath)) {
                    continue;
                }*/
                $pathUrl[] = config('upload.resource_cdn') .'/invite_imgs/'. USERID . '_' . $value['id'] .'.png';
                if ($redis->sIsMember(self::$invite. $value['id'], USERID)) continue;

                $image = \think\Image::open($bgImage);
                $postion = json_decode($value['data'], true) ?: [];
                $image->text($inviteCode, ROOT_PATH . '/public/bx_static/admin/style/YouSheBiaoTiHei/YouSheBiaoTiHei-3.ttf', 18, '#ffffff', [$postion['fontwidth'], $postion['fontheight']])->water($url_path, [$postion['width'], $postion['height']])->save($newpath);
                $this->qiniu_upload($newpath, 'invite_imgs/'. USERID . '_' . $value['id'] .'.png');
                $redis->sadd(self::$invite. $value['id'], USERID);
            }
            return $pathUrl;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getList()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        //查询所有当前用户邀请的用户
        $users = Db::name('promotion_relation')->field('user_id')->where([
            'promoter_uid' => USERID
        ])->order('create_time desc')->limit($offset, $length)->select();

        if (empty($users)) return $this->success([], '获取成功');

        $userIds = array_unique(array_column($users, 'user_id'));
        $userService = new User();
        //获取邀请的用户信息
        $userList = $userService->getUsers($userIds);
        $userList = $userList ? $userList : [];
        $result = [];
        //初始化用户信息
        foreach ($users as $userItem) {
            $userId = $userItem['user_id'];
            $user = InviteRecord::getItemByList($userId, $userList, 'user_id');
            $item = [];
            $item['id'] = $user['user_id'];
            $item['invite_uid'] = USERID;
            $item['anchor_uid'] = 0;
            $item['user_id'] = $user['user_id'];
            $item['reward_exp'] = 0;
            $item['reward_bean'] = 0;
            $item['reward_millet'] = 0;
            $item['create_time'] = $user['create_time'];
            $item['anchor'] = [
                'user_id' => ''
            ];
            $item['user'] = copy_array($user, 'user_id,nickname,avatar,millet,millet_status,fre_millet,total_millet,level,
            status,vip_status,vip_expire,bean,sign');
            $result[] = $item;
        }

        return $this->success($result, '获取成功');
    }

    public function qiniu_upload($file_path = '', $file_name)
    {
        if (empty($file_path)) return false;

        $storer = config('upload.platform');
        $platform_config = config('upload.platform_config');
        $accessKey = $platform_config['access_key'];
        $secretKey = $platform_config['secret_key'];
        $bucket = $platform_config['bucket'];
        if ($storer == 'qiniu') {
            $auth = new Auth($accessKey, $secretKey);
            $token = $auth->uploadToken($bucket, $file_name);
            $uploadMgr = new UploadManager();

            list($ret, $err) = $uploadMgr->putFile($token, $file_name, $file_path);
            if ($err !== null) return false;

            return true;
        } else {
            return false;
        }
    }
}
