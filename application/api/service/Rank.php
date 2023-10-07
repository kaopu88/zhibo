<?php

namespace app\api\service;
use app\common\service\Service;
use bxkj_module\service\User;
use think\Db;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;

class Rank extends Service
{

    public function getList($get, $offset = 0, $length = 10, $opts = null)
    {
        if (!isset($opts)) {
            $opts = array(
                'scoreKey' => 'score',
                'memberKey' => 'user_id',
                'stealth' => '',
                'stealth_exp' => []
            );
        }
        $name = $get['name'];
        $interval = $get['interval'];
        $order = $get['order'] ? $get['order'] : 'desc';
        $key = $this->getIntervalKey($name, $interval);
        $redis = RedisClient::getInstance();
        //需要求交集
        if ($get['numkeys']) {
            $sha1 = sha1($key);
            $lastTime = $redis->get("zunionstore:time:{$sha1}");
            $period = isset($get['period']) ? $get['period'] : 0;
            if (time() >= $lastTime + $period) {
                $keys = [];
                foreach ($get['numkeys'] as $numkey) {
                    $tmpKey = $this->getIntervalKey($numkey, $interval);
                    $keys[] = $tmpKey;
                }
                $redis->zUnion($key, $keys, null, 'SUM');
                $redis->set("zunionstore:time:{$sha1}", time());
            }
        }
        $total = $redis->zCount($key, '-inf', '+inf');
        if ($order == 'desc') {
            $ranks = $redis->zRevRange($key, $offset, $offset + $length - 1, true);
        } else {
            $ranks = $redis->zRange($key, $offset, $offset + $length - 1, true);
        }
        $list = [];
        $memberIds = [];
        $stealthExp = is_array($opts['stealth_exp']) ? $opts['stealth_exp'] : [];
        $newList = [];
        if (!empty($ranks)) {
            $i = 0;
            foreach ($ranks as $member => $score) {
                $memberIds[] = $member;
                $rank = $order == 'desc' ? $offset + $i : ($total - ($offset + $i) - 1);
                $i++;
                $list[] = array(
                    $opts['memberKey'] => $member,
                    $opts['scoreKey'] => $score,
                    'rank' => (int)$rank,
                    'num' => $rank + 1
                );
            }
            $members = [];
            if (!empty($memberIds)) {
                $userModel = new user();
                $self = !empty(USERID) ? USERID : '';
                $members = $userModel->getUsers($memberIds, $self, '_list');
                $members = $members ? $members : [];
            }
            foreach ($list as &$item) {
                $user = $this->getItemByList($item['user_id'], $members, 'user_id');
                if ($get['is_self'] && $user['isvirtual']) continue;
                $user['is_show'] = '1';
                if (!empty($opts['stealth'])) {
                    $stealth = $user[$opts['stealth']];
                    if ($stealth == '1' && !in_array($item['user_id'], $stealthExp)) {
                        $user = array(
                            'is_show' => '0',
                            'user_id' => '0',
                            'avatar' => img_url('', '200_200', 'avatar'),
                            'nickname' => 'xxxxxx',
                            'phone' => 'xxxxxx',
                            'level' => 0,
                            'exp' => 0,
                            'birthday' => '',
                            'city_id' => 0,
                            'city_name' => '',
                            'like_num' => 0,
                            'fans_num' => 0,
                            'age' => 0,
                            'sign' => '暂无签名',
                            'verified' => '0',
                            'is_creation' => '0',
                            'vip_status' => '0',
                            'vip_expire_str' => '未开通',
                            'vip_expire' => 0,
                        );
                    }
                }
                $item = array_merge_notrepeat($item, $user ? $user : array(), 'user_');
                $item['is_follow'] = (string)($user['is_follow']?:0);
                if (isset($item['user_id'])) $item['user_id'] = (string)$item['user_id'];
                $newList[] = $item;
            }
        }
        return array(
            'list' => $newList,
            'total' => $total
        );
    }

    private function getIntervalKey($name, $interval)
    {
        switch ($interval) {
            case 'his':
                $key = "{$name}:history";
                break;
            case 'y':
                $y = date('Y');
                $key = "{$name}:y:{$y}";
                break;
            case 'm':
                $m = date('Ym');
                $key = "{$name}:m:{$m}";
                break;
            case 'w':
                $w = DateTools::getWeekNum();
                $key = "{$name}:w:{$w}";
                break;
            case 'f':
                $fnum = DateTools::getFortNum();
                $key = "{$name}:f:{$fnum}";
                break;
            default:
                $d = date('Ymd');
                $key = "{$name}:d:{$d}";
        }
        return 'rank:' . $key;
    }


}