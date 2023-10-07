<?php

namespace app\api\controller;

use app\api\service\UserPhotoWall;
use app\common\controller\UserController;
use think\Db;
use app\api\service\User_Album as UserAlbumModel;

class Useralbum extends UserController
{
    public function getList()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = $params['length'] ? ($params['length'] > 200 ? 200 : $params['length']) : PAGE_LIMIT;
        $where['user_id'] = $params['user_id'] ? $params['user_id'] : USERID;;
        $list = Db::name('user_album')->where($where)->order('create_time desc')->limit($offset, $length)->select();
        if ($list) {
            foreach ($list as &$item) {
                $item['thumb'] = img_url($item['image'], '200_200', 'thumb');
            }
            unset($item);
        }
        return $this->success($list ? $list : [], '获取成功');
    }

    public function save()
    {
        $params = request()->param();
        $image = explode(',', $params['image']);
        if (!$image) return $this->jsonError('请上传图片');
        if (!empty($image)) {
            $userData = Db::name('user')->where(array('user_id' => USERID))->find();
            //if (!$userData['is_anchor']) return $this->jsonError('抱歉，您没有操作权限！');
            $UserAlbumModel = new UserAlbumModel();
            $result = $UserAlbumModel->inserts($image);
            if (!$result) return $this->jsonError('上传失败！请稍后再试');
            return $this->success($result, '上传成功');
        }
    }

    public function delete()
    {
        $params = request()->param();
        $id = $params['id'];
        if (!$id) return $this->jsonError('参数错误');
        $result = Db::name('user_album')->delete(['id' => $id]);
        if (!$result) return $this->jsonError('删除失败！请稍后再试');
        return $this->success(['total' => $result], '删除成功');
    }

    public function savaPhoto()
    {
        $params = request()->param();
        $image = $params['image'];
        //if (!$image) return $this->jsonError('请上传图片');

       // if (!empty($image)) {
            $userPhotoWallModel = new UserPhotoWall();
            $result = $userPhotoWallModel->inserts($image);
            if (!$result) return $this->jsonError('上传失败！请稍后再试');
            return $this->success($result, '上传成功');
       // }
    }
}
