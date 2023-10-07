<?php
/**
 * Created by PhpStorm.
 * Author: belost
 * Date: 19-4-20
 * Time: 下午1:18
 */

namespace app\core\service\collector;

require_once 'ql/QueryList.php';

use bxkj_common\HttpClient;

use bxkj_common\RedisClient;
use QL\QueryList;


abstract class QueryMusic
{
    protected $category_url = '';

    protected $Rpc = null;

    protected $redis = null;

    public function __construct()
    {
        if ($this->Rpc === null)
            $this->Rpc = new HttpClient();

        if ($this->redis === null)
            $this->redis = RedisClient::getInstance();
    }


    //解析歌曲
    abstract protected function parseSong($song_id);


    //解析专辑
    abstract protected function parseAlbum($album_id, $singer_id);


    //解析歌手
    abstract protected function parseSinger($singer_id);


    //添加歌曲任务(处理单个歌曲信息)
    abstract public function addSongTask($song_id);


    //添加专辑任务(处理单个专辑与专辑下的所有歌曲信息)
    abstract public function addAlbumTask($album_id);


    //添加歌手任务(处理歌手与歌手下所有专辑与所有的歌曲信息)
    abstract public function addSingerTask($singer_id);


    //添加分类任务
    abstract public function addCategoryTask($url, $category_id, $start, $end);


    //处理歌曲任务
    abstract public function handleSongTask($song_id);


    //处理专辑任务
    abstract public function handleAlbumTask($album_id);


    //处理歌手任务
    abstract public function handleSingerTask($singer_id);


    //处理分类任务
    abstract public function handleCategoryTask($category_id);



    //获取分类地址下的所有歌曲id
    protected function parseCategoryAllSongId($url, &$start, &$end, &$result=[])
    {
        /*$base_url = 'http://music.taihe.com';

        //解析列表规则
        $rule = [
            //影片名称
            'song_id' => ['.song-title>a', 'href']
        ];

        $QL = QueryList::getInstance();

        $data = $QL->Query($this->url, $rule)->getData();*/

        set_time_limit(0);

        $content = file_get_contents($url);

        $move_class = preg_match_all("/<a[\s]*href=.*\/song\/(\d*)/", $content, $match);

        if ($move_class)
        {
            $result = array_merge($result, $match[1]);

            $url = $content = $move_class = $match = null;

            if ($start < $end)
            {
                $url = $this->category_url.'?start='.(++$start*20);

                $this->parseCategoryAllSongId($url, $start, $end, $result);
            }
        }

        return array_unique($result);
    }


}