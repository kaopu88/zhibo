<?php

namespace app\core\service;

use bxkj_module\service\Service;
use think\Db;

class RecommendContent extends Service
{
    public function getSpaceInfo($space)
    {
        $where = array('status' => '1');
        if (preg_match('/^\d+$/', $space)) {
            $where['id'] = $space;
        } else {
            $where['mark'] = $space;
        }
        $spaceInfo = Db::name('recommend_space')->where($where)->find();
        return $spaceInfo;
    }

    public function getContents($spaces, $options = array(), $offset = 0, $length = 10)
    {
        $multi = isset($options['multi']) ? $options['multi'] : '0';
        $arr = [];
        $spaceArr = is_array($spaces) ? $spaces : explode(',', $spaces);
        foreach ($spaceArr as $space) {
            $spaceInfo = $this->getSpaceInfo($space);
            if (empty($spaceInfo)) return $this->setError('推荐位不存在');
            $contents = [];
            if ($spaceInfo['type'] == 'art') {
                $contents = $this->relationArticles($spaceInfo, $offset, $length);
            }
            $arr[$space] = array(
                'contents' => $contents,
                'rec_id' => $spaceInfo['id'],
                'rec_name' => $spaceInfo['name'],
                'config' => $spaceInfo['config'] ? json_decode($spaceInfo['config'], true) : array(),
                'type' => $spaceInfo['type']
            );
        }
        if ($multi == '1') return $arr;
        return $arr[$spaceArr[0]];
    }

    protected function relationArticles($spaceInfo, $offset = 0, $length = 10)
    {
        $result = Db::name('recommend_content')->alias('content')->join('__ARTICLE__ art', 'art.id=content.rel_id')
            ->where(array(
                'content.rec_id' => $spaceInfo['id'],
                'content.rel_type' => $spaceInfo['type'],
                'art.status' => '1'
            ))->order('content.sort desc,content.create_time desc')
            ->field('art.id,art.mark,art.pcat_id,art.cat_id,art.title,art.summary,art.aid,art.content,art.url,art.mobile_content,art.pv,
            art.share_num,art.image,art.video,art.images,content.sort,art.status,art.comment_status,art.comment_num,art.like_num,art.update_time,
            art.create_time,art.release_time')->limit($offset, $length)->select();
        return $result ? $result : [];
    }

    public function getContentTotal($space, $options = array())
    {
        $spaceInfo = $this->getSpaceInfo($space);
        if (empty($spaceInfo)) return $this->setError('广告位不存在');
        $options['rec_id'] = $spaceInfo['id'];
        $options['rel_type'] = $spaceInfo['type'];
        $this->db = Db::name('recommend_content');
        $this->setRelWhere($options);
        $total = $this->db->count();
        return $total;
    }

    protected function setRelWhere($options)
    {
        $this->db->alias('content');
        $this->db->join('__ARTICLE__ art', 'art.id=content.rel_id');
        $where = array(
            'content.rec_id' => $options['rec_id'],
            'content.rel_type' => $options['rel_type'],
            'art.status' => '1'
        );
        $this->db->where($where);
        return $this;
    }


}