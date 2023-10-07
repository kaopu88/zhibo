<?php

namespace app\core\controller;


use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use think\Db;
use think\Request;


/**
 * 第三方拉取音乐
 * Class Music
 * @package app\core\controller
 */
class Music extends Controller
{
    protected static $channels = [
        'baidu' => 'app\\core\\service\\collector\\query\\Baidu',
    ];

    private $redis = null;


    public function __construct()
    {
        parent::__construct();

        if ($this->redis === null) $this->redis = RedisClient::getInstance();
    }


    /**
     * 分类获取歌曲
     * @param Request $request
     * @return \App\Common\BuguCommon\BaseError|\bxkj_common\BaseError|string
     */
    public function musicByCategoryUrl(Request $request)
    {
        set_time_limit(0);

        $params = $request->param();

        //分类地址
        $url = $params['link'];

        //采集平台
        $channel = $params['channel'];

        //当前分类下最大分页数
        $max_page = $params['max_page'] ?: 0;

        //分类id
        $category = $params['category_id'];

        if (!array_key_exists($channel, self::$channels)) return make_error('不支持的平台');

        if ($this->redis->sismember("musicQuery:baidu:add", $url)) return make_error('当前链接已处理过!');

        $class = self::$channels[$channel];

        $start = 0;

        $end = $page = 10;

        $task_num = $fork_num = ceil($max_page/$page);

        while ($fork_num)
        {
            $pid = pcntl_fork();

            if ($pid == -1) {
                //记录日志
                exit('no pcntl');
            }
            else if($pid == 0) {

                $ChannelObject = new $class();

                $ChannelObject->addCategoryTask($url, $category, $start, $end);

                exit(0);
            }

            $start = $end;

            $end += $page;

            --$fork_num;
        }

        return $task_num.'个任务已提交,后台自动定时处理，请匆重复提交~';
    }


    /**
     * 歌手获取所有专辑与歌曲
     * @param Request $request
     * @return \App\Common\BuguCommon\BaseError|\bxkj_common\BaseError
     */
    public function musicBySingerId(Request $request)
    {
        $params = $request->param();

        $channel = $params['channel'];

        $singer = $params['singer_id'];

        if (!array_key_exists($channel, self::$channels)) return make_error('不支持的平台');

        $class = self::$channels[$channel];

        $ChannelObject = new $class();

        $rs = $ChannelObject->addSingerTask($singer);

        return $rs;
    }


    /**
     *
     * 专辑获取所有的歌曲
     */
    public function musicByAlbumId(Request $request)
    {
        $params = $request->param();

        $channel = $params['channel'];

        $album = $params['album_id'];

        if (!array_key_exists($channel, self::$channels)) return make_error('不支持的平台');

        $class = self::$channels[$channel];

        $ChannelObject = new $class();

        $rs = $ChannelObject->addAlbumTask($album);

        return $rs;

    }


    /**
     *
     * 歌曲获取指定歌曲
     */
    public function musicBySongId(Request $request)
    {
        $params = $request->param();

        $channel = $params['channel'];

        $song = $params['song_id'];

        if (!array_key_exists($channel, self::$channels)) return make_error('不支持的平台');

        $class = self::$channels[$channel];

        $ChannelObject = new $class();

        $rs = $ChannelObject->addSongTask($song);

        return $rs;

    }



    public function handleCategoryTask(Request $request)
    {
        $params = $request->param();

        //采集平台
        $channel = $params['channel'];

        //分类id
        $category = $params['category_id'];

        if (!array_key_exists($channel, self::$channels)) return make_error('不支持的平台');

        $class = self::$channels[$channel];

        $ChannelObject = new $class();

        $rs = $ChannelObject->handleCategoryTask($category);

        return $rs;

    }



    public function resetMusicLink()
    {
        $redis = RedisClient::getInstance();

        $last_id = $redis->get('cache:music_reset_link');

        $where[] = ['channel', 'eq', 'baidu'];

        !empty($last_id) && $where[] = ['id', 'lt', $last_id];

        $rs = Db::name('music')->field('channel_file_id, id')->where($where)->limit(20)->order('id desc')->select();

        if (empty($rs)) return;

        $music_update = [];

        $Http = new HttpClient();

        foreach ($rs as $value)
        {
            $current_music_info = $Http->get('http://tingapi.ting.baidu.com/v1/restserver/ting?format=json&from=webapp_music&method=baidu.ting.song.play&songid='.$value['channel_file_id'])->getData('json');

            if (empty($current_music_info)) continue;

            $music_tmp = [
                'id' => $value['id'],
                'link' => $current_music_info['bitrate']['file_link'],
            ];

            array_push($music_update, $music_tmp);
        }

        if (empty($music_update)) return;

        $User = new \app\core\model\Music();

        $User->saveAll($music_update);

        $lasts = end($rs);

        $redis->set('cache:music_reset_link', $lasts['id']);

        echo 'ok';
    }

}