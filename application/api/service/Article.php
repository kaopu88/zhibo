<?php


namespace app\api\service;


use app\common\service\Service;
use think\Db;

class Article extends Service
{
    public function getList($get = array(), $offset = 0, $length = 10, $fields = '*')
    {
        $needFields = is_array($fields) ? implode(',', $fields) : $fields;
        $arr = [];
        $where = array('status' => '1');
        if (!empty($get['pcat_id'])) $where['pcat_id'] = $get['pcat_id'];
        $rows = Db::name('article')
            ->where($where)
            ->field($needFields)
            ->limit($offset, $length)
            ->order(['create_time'=>'desc','update_time'=>'desc'])
            ->cursor();

        foreach ($rows as $row)
        {
            if( isset($row['images']) )
            {
                $row['imageList'] = !empty($row['images']) ? explode(",",$row['images']) : [];
                if( !empty($row['imageList']) )
                {
                    foreach ( $row['imageList'] as &$img)
                    {
                        $img = img_url($img,'200_147','thumb');
                    }
                }

                $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
                preg_match_all($pattern,$row['content'],$imgs);

                $row['imageCount'] = count($imgs[0]);
                unset($row['images']);
                unset($row['content']);
            }

            if( isset($row['release_time']) )
            {
                $row['release_time'] = time_before($row['release_time'], '前');
            }

            $row['title'] =  str_replace(" ","",$row['title']);
            $row['subtitle'] = '关注最多的直播热点';
            $row['author'] = config('site.company_full_name');
            $row['h5_url'] = H5_URL . '/app_article/show/id/' . $row['id'];

            $arr[] = $row;
        }

        return $arr;
    }
}