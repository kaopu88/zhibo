<?php

namespace app\admin\service;

use bugu_film\task\VideoManage;
use bxkj_common\YunBo;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use Qiniu\Auth;
use think\Db;

class LiveFilm extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_film');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_film');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $admins = $this->getRelList($result, function ($aids) {
            $adminService = new Admin();
            $admins = $adminService->getAdminsByIds($aids);
            return $admins;
        }, 'aid');
        foreach ($result as &$item) {
            if (!empty($item['aid'])) {
                $item['admin'] = self::getItemByList($item['aid'], $admins, 'id');
            }
            $item['video_duration_str'] = duration_format($item['video_duration']);
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['source'] != '') {
            $where[] = ['source', '=', $get['source']];
        }
        if (trim($get['video_id']) != '') {
            $where[] = ['video_id', '=', trim($get['video_id'])];
        }
        if ($get['is_local'] != '') {
            if ($get['is_local'] == '1') {
                $where[] = ['video_id', 'neq', ''];
            } else {
                $where[] = ['video_id', 'eq', ''];
            }
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'video_title');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        $this->preInputData($inputData);
        $data = $this->df->process('add@live_film', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        if (empty($inputData['video_url']) && empty($inputData['third_url'])) {
            return $this->setError('请上传视频或者填写播放地址');
        }
        if (!empty($inputData['third_url'])) {
            $thirdInfo = YunBo::getVideo($inputData['third_url']);
            if (empty($thirdInfo) || is_error($thirdInfo)) return $this->setError('播放地址无法解析');
            $data['parse_time'] = time();
            $data['parse_data'] = json_encode($thirdInfo);
            $data['play'] = $thirdInfo['play'];
            $data['source'] = $thirdInfo['source'];
        }
        $id = Db::name('live_film')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $this->preInputData($inputData);
        $data = $this->df->process('update@live_film', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        if (empty($inputData['video_url']) && empty($inputData['third_url'])) {
            return $this->setError('请上传视频或者填写播放地址');
        }
        $num2 = Db::name('live_film_timeline')->where(['film_id' => $data['id'], 'status' => '1'])->count();
        if ($num2 > 0) return $this->setError('正在播出，不能更改影片信息');
        $saveData = $this->df->getLastData();
        if (!empty($inputData['third_url']) && $saveData['third_url'] != $inputData['third_url']) {
            $thirdInfo = YunBo::getVideo($inputData['third_url']);
            if (empty($thirdInfo) || is_error($thirdInfo)) return $this->setError('播放地址无法解析');
            $data['parse_time'] = time();
            $data['parse_data'] = json_encode($thirdInfo);
            $data['play'] = $thirdInfo['play'];
            $data['source'] = $thirdInfo['source'];
        }
        $num = Db::name('live_film')->where('id', $data['id'])->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function preInputData(&$inputData)
    {
        $video_duration = 0;
        $inputData['video_duration'] = $video_duration;
        $inputData['video_rate'] = '';
        if (!empty($inputData['video_duration_h'])) {
            $video_duration += ((int)$inputData['video_duration_h'] * 3600);
        }
        if (!empty($inputData['video_duration_i'])) {
            $video_duration += ((int)$inputData['video_duration_i'] * 60);
        }
        if (!empty($inputData['video_duration_s'])) {
            $video_duration += ((int)$inputData['video_duration_s'] * 1);
        }
        if ($video_duration > 0) {
            $inputData['video_duration'] = $video_duration;
        }
        if (!empty($inputData['video_width']) && !empty($inputData['video_height'])) {
            $inputData['video_rate'] = (string)round($inputData['video_width'] / $inputData['video_height'], 2);
        }
        $inputData['source'] = '';
        if (!empty($inputData['third_source'])) {
            $inputData['source'] = strtolower($inputData['third_source']);
        }
        unset($inputData['video_duration_s'], $inputData['video_duration_i'], $inputData['video_duration_h']);
    }

    public function getThirdVideoInfo($id = null, $third_url = '')
    {
        if (!empty($id)) {
            $info = Db::name('live_film')->where('id', $id)->find();
            if (empty($info)) return $this->setError('视频不存在');
            $third_url = $info['third_url'];
        }
        $third_url = trim($third_url);
        if (empty($third_url)) return $this->setError('第三方地址不能为空');
        $urlInfo = parse_url($third_url);
        $arr = ['/qq\.com$/' => 'qq', '/iqiyi\.com$/' => 'iqiyi', '/sohu\.com$/' => 'sohu'];
        if (empty($urlInfo)) return $this->setError('播放地址不正确');
        $source = '';
        foreach ($arr as $pattern => $val) {
            if (preg_match($pattern, $urlInfo['host'])) {
                $source = $val;
                break;
            }
        }
        if (empty($source)) return $this->setError('不支持的视频网站');
        $sha1 = sha1($third_url);
        $redis = RedisClient::getInstance();
        $key = 'yunbo:' . $sha1;
        $json = $redis->get($key);
        if (empty($json)) {
            $httpClient = new HttpClient();
            $url = 'http://yunbo.cnibx.com:8085/get_video?url=' . urlencode($third_url);
            $result = $httpClient->get($url)->getData('json');
            if (empty($result) || $result['status'] != 0) return $this->setError('播放地址无法解析');
            $obj = [
                'data' => $result['data'],
                'url' => $third_url
            ];
            $redis->set($key, json_encode($obj), 7200);
        } else {
            $obj = json_decode($json, true);
        }
        $data = ['play' => $obj['data']['play'], 'source' => $source, 'src' => $obj['data']['src']];
        return $data;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('live_film');
        $this->db->setKeywords($keyword, '', 'number id', 'number id,video_title');
        $this->db->field('id,video_title');
        $this->db->order(['create_time' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['id'],
                'name' => $item['video_title']
            ];
        }
        return $arr;
    }

    public function getInfo($filmId)
    {
        $info = Db::name('live_film')->where(['id' => $filmId])->find();
        if ($info) {
            $info['video_duration_str'] = duration_format($info['video_duration']);
            self::explodeDurationStr($info['video_duration_str'], $info);
        }
        return $info;
    }

    public static function explodeDurationStr($duration_str, &$info, $prefix = 'video_duration_')
    {
        $durationArr = explode(':', $info['video_duration_str']);
        $info["{$prefix}h"] = $info["{$prefix}i"] = $info["{$prefix}s"] = 0;
        if (count($durationArr) == 1) {
            $info["{$prefix}s"] = (int)$durationArr[0];
        } elseif (count($durationArr) == 2) {
            $info["{$prefix}i"] = (int)$durationArr[0];
            $info["{$prefix}s"] = (int)$durationArr[1];
        } else {
            $info["{$prefix}h"] = (int)$durationArr[0];
            $info["{$prefix}i"] = (int)$durationArr[1];
            $info["{$prefix}s"] = (int)$durationArr[2];
        }
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $num = Db::name('live_film_timeline')->whereIn('film_id', $ids)->where([
            ['status', 'neq', '2']
        ])->limit(1)->count();
        if ($num > 0) return $this->setError('正在排片中');
        $result = Db::name('live_film')->whereIn('id', $ids)->select();
        if (empty($result)) return $this->setError('影片不存在');
        $num2 = Db::name('live_film')->whereIn('id', $ids)->delete();
        if (!$num2) return $this->setError('删除失败');
        $videoManage = new VideoManage();
        foreach ($result as $item) {
            if (!empty($item['video_id'])) {
                $delRes = $videoManage->deleteVodFile($item['video_id']);
            }
        }
        return $num2;
    }


}