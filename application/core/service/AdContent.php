<?php

namespace app\core\service;

use bxkj_module\service\Service;
use think\Db;

class AdContent extends Service
{
    const CACHE_DURATION = 10800;//3个小时

    public function getSpaceInfo($space)
    {
        $where = array('status' => '1', 'delete_time' => null);
        if (preg_match('/^\d+$/', $space)) {
            $where['id'] = $space;
        } else {
            $where['mark'] = $space;
        }
        $spaceInfo = Db::name('ad_space')->where($where)->find();
        return $spaceInfo;
    }

    public function getContents($spaces, $options = array())
    {
        $multi = isset($options['multi']) ? $options['multi'] : '0';
        $arr = [];
        $spaceArr = is_array($spaces) ? $spaces : explode(',', $spaces);
        // var_dump($spaceArr);die;
        foreach ($spaceArr as $space) {
            $key = $this->getSpaceKey($space, $options);
            $item = cache($key);
            if (empty($item)) {
                $spaceInfo = $this->getSpaceInfo($space);
                if (empty($spaceInfo)) return $this->setError('广告位不存在');
                $tmp = $options;
                unset($tmp['multi'], $tmp['space']);
                $tmp['space_id'] = $spaceInfo['id'];
                $this->db = Db::name('ad_content');
                $this->setWhere($tmp);
                $length = isset($options['length']) ? $options['length'] : $spaceInfo['length'];
                $contents = $this->db->field('id,space_id,title,image,url,video,start_time,end_time,sort,purview,allow_close,create_time')
                    ->limit(0, $length)->order('sort desc,create_time desc')->select();
                $contents = is_array($contents) ? $contents : [];
                foreach ($contents as &$content) {
                    if (!empty($content['image'])) {
                        $content['image'] = json_decode($content['image'], true);
                    }
                }
                $item = array(
                    'contents' => $contents,
                    'space_id' => $spaceInfo['id'],
                    'space_name' => $spaceInfo['name'],
                    'config' => $spaceInfo['config'] ? json_decode($spaceInfo['config'], true) : (object)[],
                    'type' => $spaceInfo['type'],
                    'length' => $spaceInfo['length']
                );
                cache($key, $item, self::CACHE_DURATION);//缓存两个小时
            }
            $item['contents'] = $this->removeExpired($item['contents']);
            $arr[$space] = $item;
        }
        if ($multi == '1') return $arr;
        return $arr[$spaceArr[0]];
    }

    protected function getSpaceKey($space, $options)
    {
        $str = '';
        if ($options['os'] != '') $str .= $options['os'];
        if (isset($options['code'])) $str .= $options['code'];
        if (isset($options['city_id'])) $str .= $options['city_id'];
        if (isset($options['length'])) $str .= $options['length'];
        $sha1 = sha1($str);
        return "ad:space_contents:{$space}:{$sha1}";
    }

    protected function removeExpired($contents)
    {
        $newContents = [];
        $now = time();
        foreach ($contents as $content) {
            if ($content['start_time'] <= $now && $content['end_time'] >= $now) {
                $newContents[] = $content;
            }
        }
        return $newContents;
    }

    public function getContentTotal($space, $options = array())
    {
        $spaceInfo = $this->getSpaceInfo($space);
        if (empty($spaceInfo)) return $this->setError('广告位不存在');
        $options['space_id'] = $spaceInfo['id'];
        $this->db = Db::name('ad_content');
        $this->setWhere($options);
        $total = $this->db->count();
        return $total;
    }

    protected function setWhere($options)
    {
        $where2['space_id'] = $options['space_id'];
        $where2['status'] = '1';
        $where2['delete_time'] = null;
        $this->db->where($where2);
        $now = time();
        //时间范围
        $this->db->where('start_time', '<=', $now + self::CACHE_DURATION + 5);//提前载入3个小时的，正好载入后缓存3个小时 多5秒防止清除缓存不及时
        $this->db->where('end_time', '>=', $now);
        //系统类型
        if ($options['os'] != '') {
            $this->db->where(function ($query2) use ($options) {
                $query2->where('os', '=', '');
                $query2->setLikeOr('os', $options['os']);
            });
        }
        //版本号范围
        if ($options['code'] != '') {
            $this->db->where('code_min', '<=', $options['code']);
            $this->db->where('code_max', '>=', $options['code']);
        }
        //浏览权限
        //城市
        if ($options['city_id'] != '') {
            $this->db->where(function ($query2) use ($options) {
                $query2->where('city_ids', '=', '');
                $query2->setLikeOr('city_ids', $options['city_id']);
            });
        }
    }


}