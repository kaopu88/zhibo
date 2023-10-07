<?php

namespace app\admin\controller;

use bxkj_module\service\Tree;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class RecommendContent extends Controller
{
    public function art()
    {
        $this->checkAuth('admin:recommend_content:rec_art');
        $adService = new \app\admin\service\RecommendContentArt();
        $get = input();
        $get['rel_type'] = 'art';
        $catTree = new Tree('category');
        $catList = $catTree->getCategoryByMark('article_category');
        $this->assign('cat_list', $catList ? $catList : []);
        if (!empty($get['pcat_id'])) {
            $catList2 = Db::name('category')->where('pid', $get['pcat_id'])->field('id,name,mark')->select();
            $this->assign('cat_list2', $catList2 ? $catList2 : []);
        }
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function film()
    {
        $this->checkAuth('admin:recommend_content:rec_film');
        $adService = new \app\admin\service\RecommendContentFilm();
        $get = input();
        $get['rel_type'] = 'film';
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function user()
    {
        $this->checkAuth('admin:recommend_content:rec_user');
        $adService = new \app\admin\service\RecommendContentUser();
        $get = input();
        $get['rel_type'] = 'user';
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function save()
    {
        $relType = input('type');
        $this->checkAuth('admin:recommend_content:rec_' . $relType);
        if (Request::isGet()) {
            $get = input();
            $where = ['type' => $relType];
            $spaces = Db::name('recommend_space')->field('id,mark,name')->where($where)->order(['create_time' => 'desc'])->select();
            $spaces = $spaces ? $spaces : [];
            foreach ($spaces as &$space) {
                $content = Db::name('recommend_content')->where(['rec_id' => $space['id'], 'rel_id' => $get['id']])->find();
                $space['checked'] = 0;
                $space['sort'] = 0;
                if ($content) {
                    $space['checked'] = 1;
                    $space['sort'] = $content['sort'];
                }
            }
            $this->success('获取成功', [
                'spaces' => $spaces,
                'type' => $get['type'],
                'id' => $get['id']
            ]);
        } else {
            $post = Request::post();
            $relId = $post['id'];
            if (empty($relType) || empty($relId)) $this->error('请选择推荐内容');
            $recIds = is_array($post['rec_id']) ? $post['rec_id'] : [];
            $sorts = is_array($post['sort']) ? $post['sort'] : [];
            $relations = Db::name('recommend_content')->where(['rel_type' => $relType, 'rel_id' => $relId])->select();
            $hasIds = [];
            foreach ($relations as $relation) {
                $hasIds[] = $relation['rec_id'];
                $index = array_search($relation['rec_id'], $recIds);
                if ($index !== false) {
                    $sort = $sorts[$index] ? $sorts[$index] : 0;
                    Db::name('recommend_content')->where('id', $relation['id'])->update([
                        'sort' => $sort
                    ]);
                } else {
                    Db::name('recommend_content')->where('id', $relation['id'])->delete();
                }
            }
            foreach ($recIds as $index => $recId) {
                if (!in_array($recId, $hasIds)) {
                    $sort = $sorts[$index] ? $sorts[$index] : 0;
                    Db::name('recommend_content')->insertGetId([
                        'rec_id' => $recId,
                        'sort' => $sort,
                        'rel_type' => $relType,
                        'rel_id' => $relId,
                        'create_time' => time()
                    ]);
                }
            }
            alog("content.recommend_content.edit", "编辑推荐文章 ID：".$relId);
            $this->success('保存成功');
        }
    }

    public function del_recommend()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('recommend_content')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("content.recommend_content.del", "删除推荐文章 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function sort_handler()
    {
        if (Request::isGet()) {
            $this->success('获取成功', input());
        } else {
            $res = Db::name('recommend_content')->where('id',input('id'))->update(['sort'=>input('sort')]);
            if (!$res) $this->error('修改成功');
            alog("content.recommend_content.edit", "编辑推荐 ID：".input('id')."<br>修改排序 sort:".input('sort'));
            $this->success('修改成功');
        }
    }

    public function get_rec()
    {
        $types = Db::name('recommend_space')->field('id value,name')->where('type',input('type'))->order(['create_time' => 'desc'])->select();
        return json_success($types ? $types : [], '获取成功');
    }
}
