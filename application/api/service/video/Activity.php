<?php

namespace app\api\service\video;


use app\api\service\Video;


/**
 * 视频活动类
 * Class Activity
 * @package App\Domain\video
 */
class Activity extends Video
{
    protected $h5_url = '';

    public function __construct()
    {
        parent::__construct();

        $this->h5_url = H5_URL;
    }


    /**
     * 获取当前正在进行的活动
     * @param array $video_info
     * @return array|object
     */
    public function getActivity(array $video_info)
    {
        return (object)[];

        if (in_array($video_info['id'], [151,150]))
        {
            return [
                'slider_url' => $this->h5_url.'/live/videoSlider',
                'position' => '5,40,60,100', //右，下，宽，高,
            ];
        }
    }

}