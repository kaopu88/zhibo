<?php

namespace app\api\controller;


use app\common\controller\UserController;
use app\common\service\DsSession;
use app\api\service\Music as MusicService;
use app\api\service\music\MusicData;
use app\api\service\Video;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use think\Db;
use think\Request;


class Music extends UserController
{
    protected $is_recommend = [];


    //分类菜单(安卓端)
    public function categoryList()
    {
        $CategoryMusic = new MusicData();

        $categorys = $CategoryMusic->categoryByAll();

        if (empty($categorys)) return [];

        $res = array_map(function ($item)
        {
            if ($item['is_recommend'])
            {
                $this->is_recommend[$item['category_id']] = $item['title'];
            }

            unset($item['is_recommend']);

            return $item;

        }, $categorys);

        $categorys =  array_merge(MusicData::$default_category, $res);

        return $this->success([
            'title' => '分类菜单',
            'category_id' => '',
            'item' => $categorys,
        ]);
    }



    /**
     * 搜索歌曲
     * @return mixed
     */
    public function search()
    {
        $params = request()->param();

        $words = $params['words'];

        $MusicData = new MusicData();

        $res = $MusicData->page($params)->searchMusic($words);

        $MusicData->initialize($res);

        return $this->success($res);
    }


    /**
     * 轮播广告
     * @return \think\response\Json
     */
    public function sliderAd()
    {
        $coreSdk = new CoreSdk();
        $user = DsSession::get('user');
        $purview = $user ? $user['purview'] : '*,not_login';
        $ad = $coreSdk->post('ad/get_contents', [
            'space' => 'music_home_focus',
            'purview' => $purview,
            'city_id' => '',
            'os' => APP_OS_NAME,
            'code' => APP_CODE,
            'client_seri' => ClientInfo::encode()
        ]);

        return $this->success(empty($ad) ? [] : $ad);
    }


    /**
     * 推荐曲目
     * @return \think\response\Json
     */
    public function recommend()
    {
        $params = request()->param();

        $MusicData = new MusicData();

        $res = $MusicData->page($params)->musicsByOrder(['category_id' => 101], 'id desc');

        $MusicData->initialize($res);

        $result = [
            'title' => '推荐音乐',
            'category_id' => '',
            'item' => $res,
        ];

        return $this->success($result);
    }


    /**
     * 分类下的音乐作品
     * @return array|\think\response\Json
     */
    public function musicsByCategory()
    {
        $params = request()->param();

        $category_id = (int)$params['category_id'];

        $offset = isset($params['offset']) ? $params['offset'] : 0;

        $MusicData = new MusicData();

        $no_categorys = array_column(MusicData::$default_category, 'category_id');

        if (in_array($category_id, $no_categorys))
        {
            if ($offset > 15) return [];

            $order = $category_id == 1 ? 'use_num desc' : 'id asc';
            $where = $category_id == 1 ? ['category_id' => 100] : ['category_id' => 101];

            $musics = $MusicData->page($params)->musicsByOrder($where, $order);
        }
        else{
            $musics = $MusicData->page($params)->musicsByCategoryId($category_id);
        }

        $MusicData->initialize($musics);

        return $this->success($musics);
    }


    /**
     * 歌曲主页
     * @return array
     */
    public function home()
    {
        $show = [];

        $MusicData = new MusicData();

        $params = ['length' => 12];

        foreach (MusicData::$default_category as &$value)
        {
            $order = $value['category_id'] == 1 ? 'use_num desc' : 'id asc';
            $where = $value['category_id'] == 1 ? ['category_id' => 100] : ['category_id' => 101];

            $res = $MusicData->page($params)->musicsByOrder($where, $order);

            $MusicData->initialize($res);

            $value['item'] = $res;

            unset($value['icon']);
        }

        $this->categoryList();

        //处理分类显示的数据
        if (!empty($this->is_recommend))
        {
            foreach ($this->is_recommend as $category_id => $category_name)
            {
                $res = $MusicData->page($params)->musicsByCategoryId($category_id);

                if (empty($res)) continue;

                $MusicData->initialize($res);

                $show[] = [
                    'title' => $category_name,
                    'category_id' => $category_id,
                    'item' => $res,
                ];
            }
        }

        $result = array_merge(MusicData::$default_category, $show);

        return $this->success($result);
    }


    /**
     * 音乐的详情信息
     *
     * @return \think\response\Json
     */
    public function detailByMusicId()
    {
        $params = request()->param();

        $music_id = (int)$params['music_id'];

        $music_info = Db::name('music')->where(['id' => $music_id])->field('title, image, singer, id music_id, user_id, link, use_num')->find();

        if (empty($music_info)) return $this->jsonError('请求错误, 暂无数据');

        //封面、歌名、歌手、用户id（无则不跳转）、使用量、是否收藏
        $music_info = [$music_info];

        $music = new MusicService();

        $music->initialize($music_info, 'detail');

        return $this->success($music_info[0]);
    }


    /**
     * 音乐下的最新视频集合
     * @return array
     */
    public function videosByMusicId()
    {
        $params = request()->param();

        $music_id = (int)$params['music_id'];

        $MusicData = new MusicData();

        $videos = $MusicData->page($params)->videosByMusicId($music_id, 'play_sum desc');

        if (empty($videos)) return $this->success([]);

        $Video = new Video();

        $res = $Video->initializeFilm($videos, Video::$allow_fields['common']);

        return $this->success($res);
    }



    /**
     * 上报使用音乐(目录只在直播场景下)
     *
     */
    public function reportUseMusic(Request $request)
    {
        $params = $request->param();

        !isset($params['scene']) && $params['scene'] = 'live';

        $MusicData = new MusicData();

        $MusicData->insertLog($params);

        return $this->success([], '成功');
    }


    /**
     * 个人使用过的音乐
     *
     */
    public function useMusicList(Request $request)
    {
        $params = $request->param();

        $MusicData = new MusicData();

        $res = $MusicData->page($params)->useMusicList();

        $MusicData->initialize($res);

        return $this->success($res, '获取成功');
    }

    public function lrcReport(Request $request)
    {
        $params = $request->param();

        $music_id = (int)$params['music_id'];

        Db::name('music')->where(['id' => $music_id])->setInc('lrc_report');

        return $this->success([], '举报成功');
    }

}