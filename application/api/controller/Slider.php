<?php


namespace app\api\controller;


use app\common\controller\Controller;
use app\common\service\DsSession;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use app\api\service\Article as ArticleModel;
use think\Db;

class Slider extends Controller
{
    //轮播广告页
    public function getSlider()
    {
        $coreSdk = new CoreSdk();
        $session = DsSession::get();
        $user = $session['user'];
        $purview = $user ? $user['purview'] : '*,not_login';
        $ad = $coreSdk->post('ad/get_contents', [
            'space' => 'app_home_focus',
            'purview' => $purview,
            'city_id' => '',
            'os' => APP_OS_NAME,
            'code' => APP_CODE,
            'client_seri' => ClientInfo::encode()
        ]);

        !empty($ad) && empty($ad['config']) && $ad['config']= (object)[];

        return $this->success(empty($ad) ? [] : $ad);
    }

    public function getSliderArt()
    {
        $coreSdk = new CoreSdk();
        $session = DsSession::get();
        $user = $session['user'];
        $purview = $user ? $user['purview'] : '*,not_login';
        $ad = $coreSdk->post('ad/get_contents', [
            'space' => 'app_home_focus',
            'purview' => $purview,
            'city_id' => '',
            'os' => APP_OS_NAME,
            'code' => APP_CODE,
            'client_seri' => ClientInfo::encode()
        ]);
        // var_dump($ad);die;
        !empty($ad) && empty($ad['config']) && $ad['config']= (object)[];

        $result['ad'] = empty($ad) ? [] : $ad;

        $params = request()->param();

        $cat = Db::name('category')->where(['mark'=>'wxapp_tab', 'status' => '1'])->find();

        !empty($cat) && $params['pcat_id'] = $cat['id'];

        $artModel = new ArticleModel();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 10 ? 10 : $params['length']) : 10;
        $articles = $artModel->getList($params, $offset, $length,'id,title,image');

        $result['articleList'] = $articles ? $articles : [];

        return $this->success($result);
    }
}