<?php

namespace app\api\service\location;

use app\common\service\Service;
use bxkj_common\HttpClient;
use think\Db;

class ParseLocation extends Service
{
    /**
     * 计算距离
     * @param $origins
     * @param $destination
     */
    public function distance($origins, $destination)
    {
        if (is_array($origins)) $origins = implode(',', $origins);

        if (is_array($destination)) $destination = implode(',', $destination);

        $key = config('app.map_setting.web_service_key');

        $url = "https://restapi.amap.com/v3/distance?origins={$origins}&destination={$destination}&output=json&key={$key}&type=0";

        $Http = new HttpClient();

        $rst = $Http->get($url)->getData('json');

        if ($rst['status'] != 1) return '';

        $distance = $rst['results'][0]['distance'];

        return format_distance($distance);

    }


    public function getLocation($id, $origins_lng, $origins_lat)
    {

        //获取相关位置信息
        $location_info = Db::name('location')->where(['id'=>$id])->find();

        if (empty($location_info)) return $this->setError('位置信息错误');

        $LocationFavorite = new Favorite();

        $location_info['is_collect'] = $LocationFavorite->isFavorite($id);

        $position = get_position_Lng_lat($location_info['lng'], $location_info['lat']);

        if ($position['status'] == 1)
        {
            $city = !empty($position['regeocode']['addressComponent']['city'])? $position['regeocode']['addressComponent']['city'] :$position['regeocode']['addressComponent']['province'];

            $str = $city.$position['regeocode']['addressComponent']['district']. $position['regeocode']['addressComponent']['township'].$position['regeocode']['addressComponent']['streetNumber']['street'].$position['regeocode']['addressComponent']['streetNumber']['number'];
        }
        else{
            $str = $location_info['street_address'];
        }

        if ($location_info['level'] == 4 && $origins_lng && $origins_lat)
        {
            $location_info['address'] = $str;

            $destination = $location_info['lng'].','.$location_info['lat'];

            $location_info['distance_str'] = '距离您当前的位置'.$this->distance($origins_lng.','.$origins_lat, $destination);

            $location_info['goto_num'] = ($location_info['publish_num']+$location_info['collect_num']).'人来过';
        }
        else{
            $location_info['distance_str'] = '';
            $location_info['address'] = '';
            $location_info['goto_num'] = '';
        }
        unset($location_info['poi_id'], $location_info['publish_num'], $location_info['collect_num'], $location_info['create_time']);

        return $location_info;
    }
}