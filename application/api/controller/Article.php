<?php


namespace app\api\controller;


use app\common\controller\Controller;
use think\Db;
use app\api\service\Article as ArticleModel;

class Article extends Controller
{
    //查询新闻
    public function getArticleList()
    {
        $params = request()->param();

        $cat = Db::name('category')->where(['mark'=>'wxapp_tab', 'status' => '1'])->find();

        !empty($cat) && $params['pcat_id'] = $cat['id'];

        $ArtModel = new ArticleModel();

        $offset = isset($params['offset']) ? $params['offset'] : 0;

        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : 10;

        $result = $ArtModel->getList($params, $offset, $length,'id,title,release_time,images,content');

        return $this->success($result?: []);
    }
}