<?php

namespace app\api\service;


use think\Db;
use app\common\service\Service;

use app\api\service\music\Favorite as FavoriteMusic;
use bxkj_common\HttpClient;



class Music extends Service
{
    protected static $url = [
        'baidu' => 'http://tingapi.ting.baidu.com/v1/restserver/ting?format=json&from=webapp_music&method=baidu.ting.song.play&songid=',
    ];

    protected static $default_image = 'https://static.cnibx.cn/73f2a5a3189fb1c41122f780005f28e6279c616b.png';


    protected static $image_size = [
        'baidu' => [
            'list' => '@s_2,w_56,h_56',
            'detail' => '@s_2,w_375,h_380',
            'turntable' => '@s_2,w_32,h_32'
        ],

    ];


    public static $default_category = [
        [
            'title' => '飙升榜',
            'icon' => 'https://static.cnibx.cn/bsb.png',
            'category_id' => 1,
        ],
        [
            'title' => '热歌榜',
            'icon' => 'https://static.cnibx.cn/rgb.png',
            'category_id' => 2,
        ]
    ];


    protected $auth_hash = '';


    public function formatImage(&$data, $format_image='list')
    {
        if (isset($data['channel']) && array_key_exists($data['channel'], self::$image_size))
        {
            $data['image'] = $data['image'].self::$image_size[$data['channel']][$format_image];
        }
    }


    public function initialize(array &$data, $format_image='list')
    {
        if (empty($data)) return;

        $MusicFavorite = new FavoriteMusic();

        $Http = new HttpClient();

        foreach ($data as &$music)
        {
            $music['is_collect'] = $MusicFavorite->isFavorite($music['music_id']);

            $this->formatImage($music, $format_image);

            if (isset($music['channel']) && isset($music['link']) && isset($music['channel_file_id']) && $music['channel'] != 'local')
            {
                $url = self::$url[$music['channel']];

                $current_music_info = $Http->get($url.$music['channel_file_id'])->getData('json');

                if(!empty($current_music_info['bitrate']['file_link']))
                {
                    /*@list(, $xcode) = explode('?', $current_music_info['bitrate']['show_link']);

                    $this->auth_hash = $xcode;*/

                    $music['link'] = $current_music_info['bitrate']['file_link'];
                }
            }

            /*if (isset($music['link']) && !empty($this->auth_hash))
            {
                @list($link, ) = explode('?', $music['link']);
                $music['link'] = $link.'?'.$this->auth_hash;
            }*/

            $music['duration_str'] = duration_format($music['duration']);
            $music['music_id']=(string)$music['music_id'];
            $music['use_num'] = Db::name('video')->where(['music_id' => $music['music_id']])->count();
            unset($music['channel_file_id'], $music['channel'], $music['duration']);
        }
    }

}