<?php

use \think\facade\Env as Env;

$config = [

    'cache_path' => Env::get('runtime_path') . 'upload_cache',
    'platform' => 'qiniu',
    'resource_cdn' => '',
    'resource_version' => '',//静态资源版本
    'platform_config' => [
        'access_key' => 'f13PeL1zcxFGIHIYqwjuSt2ppIqH7eXW5c2b25qv',
        'secret_key' => 'c1F1q1q2gWj0Zin6E-2vpN09IRaG6Fq6PqM9khF0',
        'bucket' => 'bingxinlive',
        'root_path' => 'bingxin/',
        'base_url' => 'https://static.cnibx.cn'
    ],

    //上传配置
    'upload_config' => array(
        //默认配置
        '_default' => array(
            'fsizeMin' => 1,
            'fsizeLimit' => 10485760,//10M 单位B
            //image/*表示只允许上传图片类型 image/jpeg;image/png表示只允许上传jpg和png类型的图片
            //!application/json;text/plain表示禁止上传json文本和纯文本
            'mimeLimit' => 'image/*',
            'allowExts' => 'png,jpg,jpeg,gif',
            'path' => 'tmp/{$date}/{$uniqid}.{$ext}'
        ),
        //相册
        'album' => array(
            'path' => 'user/{$user_id}/album/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        //头像
        'avatar' => array(
            'path' => 'user/{$user_id}/avatar/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        //机器人
        'robot_avatar' => array(
            'path' => 'admin/robot/avatar/{$uniqid}.{$ext}',
        ),
        //证件
        'id_card' => array(
            'path' => 'user/{$user_id}/id_card/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        //个人封面
        'cover' => array(
            'path' => 'user/{$user_id}/cover/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        //评论
        'comment_imgs' => array(
            'path' => 'user/{$user_id}/comment_imgs/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        //短视频封面
        'film_cover' => array(
            'path' => 'user/{$user_id}/film_cover/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        'admin_live' => array(
            'path' => 'admin/live/{$uniqid}.{$ext}',
        ),
        //前端上传图片
        'images' => array(
            'path' => 'user/{$user_id}/images/{$uniqid}.{$ext}',
            'is_login' => '1'
        ),
        'char_cover' => array(
            'path' => 'admin/artists/cover/{$uniqid}.{$ext}',
        ),
        'char_avatar' => array(
            'path' => 'admin/artists/avatar/{$uniqid}.{$ext}',
        ),
        'tmp' => array(
            'path' => 'tmp/{$uniqid}.{$ext}',
        ),
        //后台上传的电影图片
        'movie_thumb' => array(
            'path' => 'admin/movie/thumb/{$uniqid}.{$ext}',
        ),
        //后台上传的电影附件
        'movie_attachment' => array(
            'path' => 'admin/movie/attachment/{$uniqid}.{$ext}',
            'fsizeLimit' => 104857600,//100M
            'mimeLimit' => 'text/plain;application/msword;application/vnd.ms-excel;application/vnd.ms-powerpoint;application/pdf;application/vnd.openxmlformats-officedocument.wordprocessingml.document;application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;application/vnd.openxmlformats-officedocument.presentationml.presentation;image/*',
            'allowExts' => 'txt,pdf,xls,xlsx,doc,docx,ppt,pptx,png,jpg,jpeg,png,gif',
        ),
        //后台上传的音乐
        'music' => array(
            'path' => 'admin/music/{$uniqid}.{$ext}',
            'mimeLimit' => 'audio/mpeg;audio/ogg;audio/wav',
            'allowExts' => 'mp3,ogg,wav',
            'fsizeLimit' => 524288000,//500M
        ),
        //后台上传的音乐歌词
        'music_lrc' => array(
            'path' => 'admin/music/lrc/{$uniqid}.{$ext}',
            'mimeLimit' => 'application/octet-stream',
            'allowExts' => 'lrc',
            'fsizeLimit' => 524288000,//500M
        ),
        //后台上传的音乐图片 
        'music_image' => array(
            'path' => 'admin/music/image/{$uniqid}.{$ext}'
        ),
        //后台上传的音乐分类图片
        'music_category_icon' => array(
            'path' => 'admin/music/category/icon/{$uniqid}.{$ext}',
        ),
        //后台上传的音乐歌手图片
        'music_singer_avatar' => array(
            'path' => 'admin/music/singer/avatar/{$uniqid}.{$ext}',
        ),
        //后台上传的音乐专辑图片
        'music_album_image' => array(
            'path' => 'admin/album/singer/image/{$uniqid}.{$ext}',
        ),
        //后台上传的用户等级icon
        'user_exp_level_icon' => array(
            'path' => 'admin/user/exp_level/icon/{$uniqid}.{$ext}'
        ),
        //后台上传的主播等级icon
        'anchor_exp_level_icon' => array(
            'path' => 'admin/anchor/exp_level/icon/{$uniqid}.{$ext}'
        ),
        //后台上传的礼物icon
        'gift_icon' => array(
            'path' => 'admin/gift/icon/{$uniqid}.{$ext}'
        ),
        //后台上传的道具封面icon
        'props_cover_icon' => array(
            'path' => 'admin/props/cover/icon/{$uniqid}.{$ext}'
        ),
        'music_icon' => array(
            'path' => 'admin/music/cover/icon/{$uniqid}.{$ext}'
        ),
        //后台上传的道具展示icon
        'props_user_icon' => array(
            'path' => 'admin/props/user/icon/{$uniqid}.{$ext}'
        ),
        //后台上传的vip缩略图
        'vip_thumb' => array(
            'path' => 'admin/vip/thumb/{$uniqid}.{$ext}'
        ),
        'reg_avatar' => array(
            'path' => 'admin/reg_avatar/thumb/{$uniqid}.{$ext}'
        ),
        'image_defaults' => array(
            'path' => 'admin/image_defaults/thumb/{$uniqid}.{$ext}'
        ),
        //后台上传的直播频道ICON
        'live_channel_icon' => array(
            'path' => 'admin/live_channel/icon/{$uniqid}.{$ext}'
        ),
        //后台上传的其他图片
        'admin_images' => array(
            'path' => 'admin/images/{$uniqid}.{$ext}',
        ),
        //后台上传支付证书
        'admin_cert' => array(
            'path' => 'admin/wxpay_cert/{$uniqid}.{$ext}',
        ),
        //后台上传的其他视频
        'admin_videos' => array(
            'path' => 'admin/videos/{$uniqid}.{$ext}',
            'mimeLimit' => 'video/mp4;video/ogg;video/webm',
            'allowExts' => 'mp4,ogg,webm',
            'fsizeLimit' => 524288000,//500M
        ),
        'admin_packages' => array(
            'path' => 'admin/packages/{$package_name}_v{$package_version}_{$package_channel}_{$uniqid2}.{$ext}',
            'mimeLimit' => 'application/zip;application/x-rar-compressed;application/x-rar;application/vnd.android;application/vnd.android.package-archive;application/vnd.android.*',
            'allowExts' => 'apk,zip',
            'fsizeLimit' => 524288000
        ),
        'gift_packages' => array(
            'path' => 'admin/gift_packages/{$package_name}_{$uniqid2}.{$ext}',
            'mimeLimit' => 'application/zip;application/octet-stream',
            'allowExts' => 'zip,bundle',
            'fsizeLimit' => 524288000
        ),
        //以下是UE
        'ue_images' => array(
            'path' => 'admin/ue_images/{$date}/{$uniqid}.{$ext}',
        ),
        'ue_videos' => array(
            'path' => 'admin/ue_videos/{$date}/{$uniqid}.{$ext}',
            'mimeLimit' => 'video/mp4;video/ogg;video/webm',
            'allowExts' => 'mp4,ogg,webm',
            'fsizeLimit' => 73400320,
        ),
        'ue_files' => array(
            'path' => 'admin/ue_files/{$date}/{$uniqid}.{$ext}',
            'mimeLimit' => 'text/plain;application/msword;application/vnd.ms-excel;application/vnd.ms-powerpoint;application/pdf;application/vnd.openxmlformats-officedocument.wordprocessingml.document;application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'allowExts' => 'txt,pdf,xls,xlsx,doc,docx,ppt,pptx',
            'fsizeLimit' => 73400320,
        ),
        'ue_cache' => array(
            'path' => 'admin/ue_cache/{$date}/{$uniqid}.{$ext}',
            'fsizeLimit' => 20485760
        ),
        'agent_logo' => array(
            'path' => 'agent/{$agent_id}/logo/{$uniqid}.{$ext}',
        ),
        'live_film_cover' => array(
            'path' => 'admin/images/live_film_cover/{$uniqid}.{$ext}',
        ),
        'live_ad_video_cover' => array(
            'path' => 'admin/images/live_ad_video_cover/{$uniqid}.{$ext}',
        ),
        'admin_resources_images' => array(
            'path' => 'admin/resources/images/{$resource_type}/{$resource_name}.{$ext}',
        ),
        'admin_resources_packages' => array(
            'path' => 'admin/resources/packages/{$resource_type}/{$resource_name}.{$ext}',
            'mimeLimit' => 'application/octet-stream',
            'allowExts' => 'bundle',
            'fsizeLimit' => 73400320,
        ),
        'admin_resources_props_packages' => array(
            'path' => 'admin/resources/packages/{$resource_type}/{$uniqid}.{$ext}',
            'mimeLimit' => 'application/octet-stream',
            'allowExts' => 'bundle',
            'fsizeLimit' => 73400320,
        ),
        //后台上传淘客图片
        'taoke_images' => array(
            'path' => 'taoke/images/{$uniqid}.{$ext}',
        ),
        //交友上传图片
        'friend_images' => array(
            'path' => 'friend/images/{$uniqid}.{$ext}',
        ),
        //交友上传视频
        'friend_video' => array(
            'path' => 'friend/video/{$uniqid}.{$ext}',
        ),
        //交友上传声音
        'friend_voice' => array(
            'path' => 'friend/voice/{$uniqid}.{$ext}',
        ),
        //后台上传直播背景图片
        'voice_bg' => array(
            'path' => 'voice/images/{$uniqid}.{$ext}',
        ),
        //前端上传图片
        'grass' => array(
            'path' => 'grass/images/{$uniqid}.{$ext}',
        ),
    ),

    //图片规则
    'image_versions' => array(
        '200_200' => array(
            'width' => 200,
            'height' => 200,
            'mode' => 1,//裁切模式
        ),
        'comment' => array(
            'width' => 260,
            'height' => 260,
            'mode' => 1,//裁切模式
        ),
        '640_640' => array(
            'width' => 640,
            'height' => 640,
            'mode' => 1,//裁切模式
        ),
        '67_90' => array(
            'width' => 67,
            'height' => 90,
            'mode' => 1,
        ),
        '355_477' => array(
            'width' => 355,
            'height' => 477,
            'mode' => 1,
        ),
        '450_253' => array(
            'width' => 450,
            'height' => 253,
            'mode' => 1,
        ),
        '520_144' => array(
            'width' => 520,
            'height' => 144,
            'mode' => 1,
        ),
        '175_242' => array(
            'width' => 175,
            'height' => 242,
            'mode' => 1,
        ),
        '311_205' => array(
            'width' => 311,
            'height' => 205,
            'mode' => 1,
        ),
        '750_304' => array(
            'width' => 750,
            'height' => 304,
            'mode' => 1,
        ),
        'min_live' => array(
            'width' => 206,
            'height' => 206,
            'mode' => 1,
        ),
        'live' => array(
            'width' => 750,
            'height' => 688,
            'mode' => 1,
        ),
        'film' => array(
            'width' => 750,
            'height' => 370,
            'mode' => 1,
        ),
        '120_68' => array(
            'width' => 120,
            'height' => 68,
            'mode' => 1,
        ),
        '200_147' => array(
            'width' => 200,
            'height' => 147,
            'mode' => 1,
        )
    ),

    //用户注册默认头像
    'reg_avatar' => [
        'bx://router.bxtv.com/'.'/static/common/image/default/1.png',
        'bx://router.bxtv.com/'.'/static/common/image/default/2.png',
        'bx://router.bxtv.com/'.'/static/common/image/default/3.png',
        'bx://router.bxtv.com/'.'/static/common/image/default/4.png',
    ],

    //默认图片
    'image_defaults' => array(
        //前端用户通用默认头像
        'avatar' => 'bx://router.bxtv.com/'.'/static/common/image/default/3.png',
        //官方小助手通知等头像
        'official_avatar' => 'bx://router.bxtv.com/'.'/static/common/image/default/official.png',
        //后端用户默认头像
        'admin_avatar' => 'bx://router.bxtv.com/'.'/static/common/image/default/official.png',
        //长方形的默认缩略图
        'thumb' => 'bx://router.bxtv.com/'.'/static/common/image/default/thumb.png',
        //正方形的默认缩略图
        'thumb2' => 'bx://router.bxtv.com/'.'/static/common/image/default/thumb2.png',
        'char_cover' => 'bx://router.bxtv.com/'.'/static/common/image/default/char_cover.png',//355_477
        'movie_thumb' => 'bx://router.bxtv.com/'.'/static/common/image/default/movie_thumb.png',//450_253
        //全局logo
        'logo' => 'bx://router.bxtv.com/'.'/static/common/image/default/logo.png',
        //主站logo
        'home_logo' => 'bx://router.bxtv.com/'.'/static/common/image/default/home_logo.png',
        //下载站logo
        'download_logo' => 'bx://router.bxtv.com/'.'/static/common/image/default/download_logo.png',
        //wap网站logo
        'wap_logo' => 'bx://router.bxtv.com/'.'/static/common/image/default/wap_logo.png',
        //电影封面
        'film_cover' => 'bx://router.bxtv.com/'.'/static/common/image/default/film_cover.png',
        //电影直播间封面
        'live_film_cover' => 'bx://router.bxtv.com/'.'/static/common/image/default/live_film.png',
        //主播直播间封面
        'live_anchor_cover' => 'bx://router.bxtv.com/'.'/static/common/image/default/live_anchor.png',
        //小助手头像
        'helper_avatar' => 'bx://router.bxtv.com/'.'/static/common/image/default/helper_avatar.png',
    ),

    //百度编辑器
    'ueditor_path' => Env::get('root_path') . 'public/static/vendor/ueditor',
    'umeditor_path' => Env::get('root_path') . 'public/static/vendor/umeditor',
    'ueditor_config' => array(
        'imagePathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'scrawlPathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'snapscreenPathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'catcherPathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'videoPathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'filePathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'imageManagerListPath' => 'pro/erp/{$ue_act}/other/{$md5_name}',
        'fileManagerListPath' => 'pro/erp/{$ue_act}/other/{$md5_name}',
    ),
    'umeditor_config' => array(
        'imagePathFormat' => 'pro/erp/{$ue_act}/other/{$md5_name}'
    ),

    //腾讯上传配置
    /*'upload_config_tencent' => array(
        'qcloud_erp' => [
            'class_id' => 522557,
            'source' => 'erp'
        ],
        'qcloud_ad' => [
            'class_id' => 525676,
            'source' => 'erp'
        ]
    ),*/
];

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/upload.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config2 = array_merge($config, $env_config);

        $config2['upload_config'] = array_merge($config['upload_config'], $env_config['upload_config']);

        $config = $config2;
    }
}

return $config;
