<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Music extends Service
{
    protected $db;

    public function add($inputData)
    {
        Service::startTrans();
        $data = $this->df->process('add@music', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('music')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        // if (!empty($data['link'])) {
        //     $res = $this->updateFileInfo($id, $data['link']);
        //     if (!$res) return false;
        // }
        Service::commit();
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@music', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $link = Db::name('music')->where(array('id' => $data['id']))->value('link');
        $num = Db::name('music')->where(array('id' => $data['id']))->update($data);
        // if ($link != $data['link']){
        //     $res = $this->updateFileInfo($data['id'], $data['link']);
        //     if (!$res) return false;
        // }
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    protected function updateFileInfo($id, $file_url)
    {
        $link = $file_url.'?avinfo';
        $fileInfo = json_decode(curl_get($link),true);
        $fileInfo = $fileInfo['format'];
        $num = false;
        if ($fileInfo){
            $num = Db::name('music')->where('id', $id)->update(['duration' => $fileInfo['duration'],'size' => $fileInfo['size'],'bitrate' => $fileInfo['bit_rate'],'ext' => $fileInfo['format_name']]);
        }
        if (!$num) return $this->setError('更新音乐信息错误');
        return $fileInfo;
    }

    public function getmusicsByIds($musicsIds)
    {
        if (empty($musicsIds)) return [];
        $musics = Db::name('music')->whereIn('id', $musicsIds)->field('id,title,singer,user_id,image,link')->select();
        return $musics ? $musics : [];
    }

    public function getTotal($get){
        $this->db = Db::name('music');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    private function setJoin()
    {
        $this->db->alias('m')->join('__USER__ user', 'user.user_id=m.user_id', 'LEFT');
        $this->db->join('__MUSIC_CATEGORY__ mc', 'mc.id=m.category_id', 'LEFT');
        $this->db->field('m.*,mc.name cat_name');
        return $this;
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['category_id'])) {
            $where['m.category_id'] = $get['category_id'];
        }
        if ($get['singer_id'] != '') {
            $where['singer_id'] = $get['singer_id'];
        }
        if ($get['languages'] != '') {
            $where['m.languages'] = $get['languages'];
        }
        if ($get['channel'] != '') {
            $where['m.channel'] = $get['channel'];
        }
        if ($get['status'] != '') {
            $where['m.status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number m.id','m.title,number m.id');
        $this->db->setKeywords(trim($get['user_keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['m.create_time'] = 'desc';
        $order['m.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('music');
        $this->setWhere($get)->setOrder($get)->setJoin();
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
        }
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, ['0', '1'])) return false;
        $num = Db::name('music')->whereIn('id', $ids)->update(['status' => $status]);
        return $num;
    }

}