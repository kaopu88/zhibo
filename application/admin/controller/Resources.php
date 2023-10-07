<?php

namespace app\admin\controller;

use bxkj_module\exception\Exception;
use bxkj_module\service\Tree;
use think\Db;
use think\facade\Request;
use Qiniu\Auth;

class Resources extends Controller
{
    public function home()
    {
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:resources:select');
        $resourcesService = new \app\admin\service\Resources();
        $get = input();
        $total = $resourcesService->getTotal($get);
        $page = $this->pageshow($total, 27);
        $resourcesList = $resourcesService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $resourcesList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:resources:update');
        if (Request::isGet()) {
            $info = [];
            $get = input();
            if ($get['type'] != '') $info['type'] = $get['type'];
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $resourcesService = new \app\admin\service\Resources();
            $post = input();
            $categoryTree = Db::name('category')->field('id')->where(array('pid' => $post['pcat_id'], 'status'=>1))->select();
            $arr = [];
            foreach ($categoryTree as $key => $value) {
                $arr[] = $value['id'];
            }
            $where = 'name = "'.$post['name'].'" and cat_id in ('.implode(',', $arr).')';
            $find = Db::name('resources')->where($where)->find();
            if ($find) {
                $this->error('资源名称已存在');
            }
            unset($post['mark']);
            unset($post['pcat_id']);
            $result = $resourcesService->add($post);
            if (!$result) $this->error($resourcesService->getError());
            alog("manager.resources.add", '新增资源 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('admin:resources:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $resourcesService = new \app\admin\service\Resources();
        $num = $resourcesService->delete($ids);
        if (!$num) $this->error('删除失败');
        alog("manager.resources.del", '删除资源 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:resources:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('resources')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("manager.resources.edit", '编辑资源 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_hot_status()
    {
        $this->checkAuth('admin:resources:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $hot = input('hot');
        if (!in_array($hot, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('resources')->whereIn('id', $ids)->update(['hot' => $hot]);
        if (!$num) $this->error('切换失败');
        alog("manager.resources.edit", '编辑资源 ID：'.implode(",", $ids)."<br>修改热门状态：".($hot == 1 ? "是" : "否"));
        $this->success('切换成功');
    }

    public function change_new_status()
    {
        $this->checkAuth('admin:resources:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $new = input('new');
        if (!in_array($new, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('resources')->whereIn('id', $ids)->update(['new' => $new]);
        if (!$num) $this->error('切换失败');
        alog("manager.resources.edit", '编辑资源 ID：'.implode(",", $ids)."<br>修改最新状态：".($new == 1 ? "是" : "否"));
        $this->success('切换成功');
    }

    public function get_tree()
    {
        $this->checkAuth('admin:article:select,admin:category:select');
        $categoryTree = new Tree('category', 'pid', 'id');
        $result = $categoryTree->typeControllerTree(input(), 'resources_types', false, 2);
        $this->success('', $result);
    }

    public function check_name(){
        $post = input();
        $pcat_id = $post['pcat_id'];
        if (!$pcat_id) {
            $this->error('请选择所属类目');
        }

        $mark = Db::name('category')->where('id',$pcat_id)->value('mark');
        
        $name = $post['name'];
        if (!$name) {
            $this->error('请填写资源名称');
        }
        
        $categoryTree = Db::name('category')->field('id')->where(array('pid' => $pcat_id, 'status'=>1))->select();

        $arr = [];
        foreach ($categoryTree as $key => $value) {
            $arr[] = $value['id'];
        }
        $where = 'name = "'.$name.'" and cat_id in ('.implode(',', $arr).')';
        $find = Db::name('resources')->where($where)->find();
        if ($find) {
            $this->error('资源名称已存在');
        }else{
            $this->success('', $mark);
        }
    }

    public function lists()
    {
        //删除七牛云资源
        $platform = config('upload.platform');
        if ($platform != 'qiniu') throw new Exception('非七牛云存储');
        $storage = config('upload.platform_config');
        $accessKey = $storage['access_key'];
        $secretKey = $storage['secret_key'];
        $bucket = $storage['bucket'];
        $base = $storage['base_url'];
        $root = $storage['root_path'];

        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

        // 要列取文件的公共前缀
        $prefix = '';
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $marker = '';
        // 本次列举的条目数
        $limit = 50;
        $delimiter = '/';
        $fileList = [];
        $prefixes = [];
//        do {
//            list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
//            if ($err !== null) {
//                echo "\n====> list file err: \n";
//                var_dump($err);
//            } else {
//                $marker = null;
//                if (array_key_exists('marker', $ret) && count($ret['items']) == $limit ) {
//                    $marker = $ret['marker'];
//                }
//                if( count($ret['items']) > 0 ){
//                    $fileList = array_merge($fileList,$ret['items']);
//                }
//                if (array_key_exists('commonPrefixes', $ret)) {
//                    array_push($prefixes,$ret['commonPrefixes']);
//                }
//            }
//        } while (!empty($marker));


//        dump($fileList);

//        list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
        $list = $bucketManager->listFiles($bucket,$prefix, $marker, $limit, $delimiter);
//
//        dump($ret);
        dump($list);
    }
}
