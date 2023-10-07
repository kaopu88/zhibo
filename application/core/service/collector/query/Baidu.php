<?php

namespace app\core\service\collector\query;

use app\core\service\collector\QueryMusic;
use bxkj_common\RedisClient;
use think\Db;

class Baidu extends QueryMusic
{

    protected static $base_url = 'http://tingapi.ting.baidu.com/v1/restserver/ting?format=json&from=webapp_music&method=';

    protected static $singer_parse_url = 'baidu.ting.artist.getInfo';

    protected static $album_list_parse_url = 'baidu.ting.artist.getAlbumList';

    protected static $album_info_parse_url = 'baidu.ting.album.getAlbumInfo';

    protected static $channel = 'baidu';

    protected static $song_parse_url = 'baidu.ting.song.play';


    //失败的集合
    protected static $query_key = 'musicQuery:baidu:';



    /**
     * 添加歌曲任务
     * @param mixed $songList
     */
    public function addSongTask($song_id)
    {


    }


    /**
     * 添加专辑任务
     * @param $albumList
     */
    public function addAlbumTask($album_id)
    {


    }


    /**
     * 添加歌手任务
     * @param $singerList
     */
    public function addSingerTask($singer_id)
    {



    }


    /**
     * 添加分类下的歌曲任务
     * @param $url
     * @param $category_id
     * @param $max_page
     * @return string
     */
    public function addCategoryTask($url, $category_id, $start, $end)
    {
        set_time_limit(0);

        $this->category_url = $url;

        $key = self::$query_key.$category_id;

        //分析分类页面下的所有歌曲并获取歌曲id
        $song_all_id = $this->parseCategoryAllSongId($url, $start, $end);

        $add_songs = Db::name('music')
            ->where(['channel_file_id' => $song_all_id])
            ->field('channel_file_id')
            ->select();

        if (!empty($add_songs))
        {
            $ok_adds = array_column($add_songs, 'channel_file_id');

            $song_all_id = array_diff($song_all_id, $ok_adds);
        }

        while ($song_all_id)
        {
            $song_id = array_shift($song_all_id);

            $this->redis->lpush($key, $song_id);
        }

        //添加定时任务
        if (!$this->redis->exists($key.':timer'))
        {
            $time = rand(10, 30);

            $timer_id = $this->Rpc->post(CORE_URL.'/timer/add',
                    ['url' => H5_URL.'/music/handleCategoryTask', 'data'=> json_encode(['channel' => self::$channel, 'category_id' => $category_id]), 'cycle'=>-1, 'interval'=>20, 'trigger_time' => time()+$time, 'method'=>'post'])->getData('json');

            $this->redis->lpush($key.':timer', $timer_id['data']['key']);
        }

        $this->redis->sadd(self::$query_key.'add', $this->category_url);

        return '任务已添加';
    }


    /**
     * 处理分类下的歌曲任务
     * @param $category_id
     * @param $channel
     */
    public function handleCategoryTask($category_id)
    {
        set_time_limit(0);

        $key = self::$query_key.$category_id;

        //处理完则删除任务
        if (!$this->redis->exists($key))
        {
            while (true)
            {
                $timer_id = $this->redis->rpop($key.':timer');

                if ($timer_id)
                {
                    //删除定时任务
                    $rs = $this->Rpc->post(CORE_URL.'/timer/remove', ['key' => $timer_id])->getData('json');

                    if ($rs['status'] != 0) $this->redis->rpush($key.':timer', $timer_id);
                }
                else{
                    return;
                }
            }
        }

        //单次任务处理最多量
        $max = 20;

        $length = 20;

        $song_ids = [];

        do{
            $song_id = $this->redis->rpop($key);

            array_push($song_ids, $song_id);

        }while($song_id && --$max);

        $rs = $this->addMusicsByCategory($song_ids, $category_id);

        if ($rs !== true)
        {
            //写入未成功队列
            array_map(function ($id) use ($category_id){
                $this->redis->lpush(self::$query_key.$category_id.':fails', $id);
            }, $rs);
        }
        return true;
        die;
        $real_song_num = count($song_ids);

        if ($real_song_num <= $length)
        {
            $rs = $this->addMusicsByCategory($song_ids, $category_id);

            if ($rs !== true)
            {
                //写入未成功队列
                array_map(function ($id) use ($category_id){
                    $this->redis->lpush(self::$query_key.$category_id.':fails', $id);
                }, $rs);
            }
            return;
        }
        else
        {
            $fork_num = ceil($real_song_num/$length);

            $length = ceil($real_song_num/$fork_num);

            $start = 0;

            //批量处理分类任务(每个进程独立处理20个歌曲id)
            while ($fork_num)
            {
                $child_song_ids = array_slice($song_ids, ($start*$length), $length);

                $pid = pcntl_fork();

                if ($pid == -1) {
                    //记录日志
                    exit('no pcntl');
                }
                else if ($pid) {
                    //父进程
                    $child_id = pcntl_wait($status, WUNTRACED);

                    //记录日志
                    if (pcntl_wifexited($status)) echo "process :$child_id exit with status:$status\n";

                }
                else {
                    $rs = $this->addMusicsByCategory($child_song_ids, $category_id);

                    if ($rs !== true)
                    {
                        array_map(function ($id) use ($category_id){
                            $this->redis->lpush(self::$query_key.$category_id.':fails', $id);
                        }, $rs);
                    }

                    return;
                }

                ++$start;

                --$fork_num;
            }
        }
    }


    //添加歌曲信息
    protected function addMusicsByCategory(array $song_ids, $category_id)
    {
        $song_data_all = [];

        $fails = [];

        do{
            $song_id = array_shift($song_ids);

            //获取歌曲信息
            $parseSongResult = $this->parseSong($song_id);

            //已存在
            if ($parseSongResult === true) continue;

            //未知原因未获取到则重试(或放入未成功集合中)
            if ($parseSongResult === null)
            {
                array_push($fails, $song_id);
                continue;
            }

            //获取歌手信息
            $parseSingerResult = $this->parseSinger($parseSongResult['songinfo']['ting_uid']);

            if ($parseSingerResult === null)
            {
                array_push($fails, $song_id);
                continue;
            }

            //解析专辑信息
            $parseAlbumResult = $this->parseAlbum($parseSongResult['songinfo']['album_id'], $parseSingerResult['id']);

            if ($parseAlbumResult === null)
            {
                array_push($fails, $song_id);
                continue;
            }

            $song_data = [
                'title' => $parseSongResult['songinfo']['title'],
                'album' => $parseAlbumResult['title'],
                'album_id' => $parseAlbumResult['id'],
                'singer' => $parseSingerResult['name'],
                'singer_id' => $parseSingerResult['id'],
                'image' => $this->image($parseSongResult['songinfo']['pic_small']),
                'duration' => $parseSongResult['bitrate']['file_duration'],
                'link' => $parseSongResult['bitrate']['show_link'],
                'size' => $parseSongResult['bitrate']['file_size'],
                'bitrate' => $parseSongResult['bitrate']['file_bitrate'],
                'ext' => $parseSongResult['bitrate']['file_extension'],
                'all_bitrate' => $parseSongResult['songinfo']['all_rate'],
                'channel_file_id' => $song_id,
                'channel' => self::$channel,
                'desc' => '',
                'category_id' => $category_id,
                'company' => $parseSongResult['songinfo']['si_proxycompany'],
                'create_time' => time(),
                'release_time' => $parseAlbumResult['release_time'],
                'lrc_link' => $parseSongResult['songinfo']['lrclink'],
            ];

            array_push($song_data_all, $song_data);

        }while($song_ids);

        $mid = Db::name('music')->insertAll($song_data_all);

        if (!$mid)
        {
            array_map(function ($value){
                //重新加入任务队列中
                array_push($fails, $value['channel_file_id']);
            }, $song_data_all);

        }

        return empty($fails) ? true : array_unique($fails);
    }


    public function handleSongTask($song_id)
    {
        // TODO: Implement handleAlbumTask() method.
    }

    public function handleAlbumTask($album_id)
    {
        // TODO: Implement handleAlbumTask() method.
    }

    public function handleSingerTask($singer_id)
    {
        // TODO: Implement handleSingerTask() method.
    }



    //解析歌曲
    protected function parseSong($song_id)
    {
        $exists = Db::name('music')->where(['channel_file_id' => $song_id])->count();

        if (!empty($exists)) return true;

        $song_parse_url = self::$base_url.self::$song_parse_url.'&songid='.$song_id;

        $song_info = $this->Rpc->get($song_parse_url, [], 5000)->getData('json');

        if (empty($song_info)) return null;

        return $song_info;
    }


    //解析专辑(不存在则创建)
    protected function parseAlbum($album_id, $singer_id)
    {
        $album_info = Db::name('music_album')->where(['channel_album_id' => $album_id])->find();

        if (!empty($album_info)) return $album_info;

        $album_url = self::$base_url.self::$album_info_parse_url."&album_id={$album_id}&from=web";

        $album_info = $this->Rpc->get($album_url)->getData('json');

        if (empty($album_info)) return null;

        $album_data = [
            'title' => $album_info['albumInfo']['title'],
            'image' => $this->image($album_info['albumInfo']['pic_big']),
            'company' => $album_info['albumInfo']['publishcompany'],
            'release_time' => strtotime($album_info['albumInfo']['publishtime']),
            'desc' => $album_info['albumInfo']['info'],
            'singer_id' => $singer_id,
            'channel_album_id' => $album_id,
            'create_time' => time(),
        ];

        $album_id_local = Db::name('music_album')->insertGetId($album_data);

        if (!$album_id_local) return null;

        $album_data['id'] = $album_id_local;

        return $album_data;
    }

    //解析专辑列表
    protected function parseAlbumList($album_id)
    {
        $album_url = self::$base_url.self::$album_list_parse_url."&album_id={$album_id}&from=web";

        $album_info = $this->Rpc->get($album_url)->getData('json');

        if (empty($album_info)) return null;

        return $album_info;
    }


    //解析歌手(不存在则创建)
    protected function parseSinger($singer_id)
    {
        $singer_info = Db::name('music_singer')->where(['channel_singer_id' => $singer_id])->find();

        if (!empty($singer_info)) return $singer_info;

        //获取歌手信息
        $singer_url = self::$base_url.self::$singer_parse_url.'&tinguid='.$singer_id;

        $singer_info = $this->Rpc->get($singer_url)->getData('json');

        if (empty($singer_info)) return null;

        $data = [
            'name' => $singer_info['name'],
            'avatar' => $this->image($singer_info['avatar_s500']),
            'gender' => $singer_info['gender'] != 2 ? $singer_info['gender']+1 : $singer_info['gender']-1,
            'classify' => $singer_info['gender'] != 2 ? $singer_info['gender']+1 : 0,
            'birth' => $singer_info['birth'],
            'country' => $singer_info['country'],
            'intro' => $singer_info['intro'],
            'songs_total' => $singer_info['songs_total'],
            'channel_singer_id' => $singer_id,
            'mv_total' => $singer_info['mv_total'],
            'albums_total' => $singer_info['albums_total'],
            'create_time' => time(),
        ];

        $singer_id_local = Db::name('music_singer')->insertGetId($data);

        if (!$singer_id_local) return null;

        $data['id'] = $singer_id_local;

        return $data;
    }




    protected function image($url)
    {
        @list($new_url, ) = explode('@', $url);
        return strtolower($new_url);
    }




    //守护进程
    protected function guard()
    {
        $checkNum = 0;
        $checkNum2 = 100;
        while (true) {
            sleep(5);
            $checkNum++;
            if ($checkNum % $checkNum2 == 0) {
                $this->log->info("pong...");
            }
            foreach ($this->children as $process_name => &$processChildren) {
                $process = $this->config['processes'][$process_name];
                foreach ($processChildren as $index => $processChild) {
                    $pid = $processChild;
                    $res = pcntl_waitpid($pid, $status, WNOHANG);
                    if ($res == -1 || $res > 0) {
                        $statusStr = "PID{$pid}";
                        if (!pcntl_wifexited($status)) {
                            $statusStr .= " exit unexpected";
                        } else {
                            //获取进程终端的退出状态码;
                            $code = pcntl_wexitstatus($status);
                            $statusStr .= " exit #{$code}";
                        }
                        if (pcntl_wifsignaled($status)) {
                            $statusStr .= " signal no";//不是通过接受信号中断
                        } else {
                            $signal = pcntl_wtermsig($status);
                            $statusStr .= " signal #$signal";
                        }
                        if (pcntl_wifstopped($status)) {
                            $statusStr .= " stop normal";
                        } else {
                            $signal = pcntl_wstopsig($status);
                            $statusStr .= " stop #$signal";
                        }
                        $this->log->info($statusStr);
                        unset($this->children[$process_name][$index]);
                    }
                }
                $num = count($this->children[$process_name]);
                if ($checkNum % $checkNum2 == 0) {
                    $this->log->info("{$process_name} has {$num}");
                }
                $pid = $this->batchFork($process_name, $process, $num);
                if ($pid == -1) {
                    $this->log->info('create child error');
                } else if (empty($pid)) {
                    $this->log->info('exit');
                    exit();
                }
            }
        }
    }





}