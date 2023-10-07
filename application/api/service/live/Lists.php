<?php

namespace app\api\service\live;


use app\common\service\LiveRecommend;
use app\common\service\DsSession;
use app\api\service\LiveBase2;
use app\api\service\Follow;
use app\common\service\User;
use app\taokeshop\service\LiveGoods;
use bxkj_common\CoreSdk;
use think\Db;


/**
 *
 * Class Lists
 * @package App\Domain\live
 */
class Lists extends LiveBase2
{
    private $offset = 0;

    private $length = PAGE_LIMIT;

    //在线条件
    protected $on_line_where = [['status', 'eq', 1]];

    //离线条件
    protected $off_line_where = [['status', 'eq', '1']];

    protected $order = 'room_model asc';

    protected $custom_order = [];//自定义order

    protected $all_user_id = [];


    public function __construct()
    {
        parent::__construct();

        //苹果审核期间屏蔽电影直播
        if (APP_OS_NAME == 'ios' && $this->redis->get('config:ios:review')) array_push($this->on_line_where, ['room_model', 'neq', self::FILM_MODE]);

        $this->all_user_id = $this->getOnAndOffLineUserId();
    }


    /**
     * 重置数据
     *
     * @return $this
     */
    public function reset()
    {
        $this->offset = 0;
        $this->length = PAGE_LIMIT;
        $this->on_line_where = [['status', 'eq', 1]];
        $this->off_line_where = [['status', 'eq', '1']];
        $this->order = '';
        return $this;
    }


    /**
     * 获取所有主播
     *
     * @return array
     */
    private function getOnAndOffLineUserId()
    {
        $key = 'cache:off_line';

        if (!$this->redis->exists($key)) {
            $off_users = Db::name('recommend_space')
                ->alias('rs')
                ->join('recommend_content rc', 'rs.id=rc.rec_id')
                ->join('user u', 'rc.rel_id=u.user_id')
                ->field('rc.rel_id,rc.sort')
                ->where(['rs.status' => '1', 'rs.mark' => 'live_offline_user', 'u.status' => '1'])
                ->order('sort desc')
                ->select();

            if (empty($off_users)) return [];

            foreach ($off_users as $value) {
                $this->redis->zadd($key, $value['sort'], $value['rel_id']);
            }

            $this->redis->expire($key, 4 * 3600);
        }

        //在线主播
        $living = $this->redis->smembers(self::$livePrefix . 'Living');

        //离线主播
        $off_living = $this->redis->zrevrange($key, 0, -1);

        $off_living = array_diff($off_living, $living);

        //推荐置顶
        $tops = $this->redis->get('cache:hotTop');

        if (!empty($tops)) {
            $tops = explode(',', $tops);

            //取交集(过滤置顶内正在直播的)
            $tops = array_intersect($tops, $living);
        } else {
            $tops = [];
        }

        //获取已推荐主播记录
        $recommend = DsSession::get('recommend_list');
        empty($recommend) && $recommend = [];
        $filter = array_unique(array_merge($recommend, $tops));

        if (!empty($filter)) {
            //从在线集合中取差集-过滤掉推荐的
            !empty($living) && $living = array_diff($living, $filter);

            //从离线集合中取差集-过滤掉推荐的
            !empty($off_living) && $off_living = array_diff($off_living, $filter);
        }

        return ['living' => $living, 'off_living' => $off_living];
    }


    /**
     * 递减长度
     * @param $length
     */
    public function setLengthDec($length)
    {
        $this->length -= $length;
    }


    /**
     * 初始化直播在线数据
     *
     * @param array $data
     * @param int $israndcover
     * @return array
     */
    public function initializeLive($data = [], $israndcover = 0, $lng = 0, $lat = 0)
    {
        if (empty($data)) return [];
        $coreSdk = new CoreSdk();
        //后面加->获取当前正在看直播的人所关注的
        $Follow = new Follow();
        $follow_users = $Follow->getAllFollowUser(USERID);
        //后面加->获取当天收益第一人 热门榜单
        $keys = 'rank:charm:d:' . date('Ymd'); //当天收益榜单key
        $top_one = $this->redis->zrevrange($keys, 0, 0, true); //获取收益第一人 就是热门人
        $charm_top_ten = $this->redis->zrevrange($keys, 1, 9, true); //魅力榜单前十收益
        $live = new LiveGoods();
        $User = new User();
        $room_ids = array_column($data, 'room_id');
        $roomAudiences = $coreSdk->post('zombie/getRoomAudience', ['room_id' => $room_ids]);
        $now = time();
        $user_shop = config('taoke.user_shop') ? config('taoke.user_shop') : '0';
        foreach ($data as $key => &$value) {
            $value['create_time'] = $this->diffTime($now - $value['create_time']);
            $value['cover_url'] = img_url($value['cover_url'], 'live');

            if (intval($value['lat']) > 0 && intval($value['lng']) && intval($lat) > 0 && intval($lng) > 0) {
                $value['distance'] = (int)getDistanceBetweenPointsNew($value['lat'], $value['lng'], $lat, $lng)['meters'];
            } else {
                $value['distance'] = -1;
            }

            if (!empty($charm_top_ten)) {
                if (array_key_exists($value['user_id'], $charm_top_ten)) $value['type'] = 9; //魅力主播前十
            }


            if (!empty($follow_users)) {
                if (in_array($value['user_id'], $follow_users)) $value['type'] = 7; //关注的主播
            }

            if (!empty($top_one)) {
                if (array_key_exists($value['user_id'], $top_one)) $value['type'] = 8; //热门主播
            }

            //直播导购商品后面加的
            if (!empty($user_shop)) {
                $value['live_goods'] = array_reverse($live->getLiveGoods(['room_id' => $value['room_id']]));
                if ($value['live_goods']) {
                    $value['type'] = 5; //导购
                    $anchor_shop = Db::name('anchor_shop')->where(['user_id' => $value['user_id']])->find();
                    $value['anchor_shop_name'] = $anchor_shop['title']; //导购
                }
            }
            //性别后面加的
            $anchor_user = $User->getUser($value['user_id']);
            $value['gender'] = $anchor_user['gender'];
            $value['level'] = $anchor_user['level'];
            /*if( $israndcover == 1 ){
                $albumCount = Db::name('user_album')->where(['user_id'=>$value['user_id']])->count();
                if( $albumCount > 1 ){
                    $album = Db::name('user_album')->where(['user_id'=>$value['user_id']])->orderRand()->value('image');
                    $value['cover_url'] = img_url($album, 'live');
                }
            }*/
            empty($value['city']) && $value['city'] = empty($value['province']) ? '未知' : $value['province'];
            $pk_status = $this->checkPk($value['user_id']);
            //性别后面加的
            if ((int)$pk_status['is_pk']) {
                $value['type'] = 6; //Pk
            }
            $value['is_pk'] = (int)$pk_status['is_pk'];
            $value['audience'] = empty($roomAudiences[$value['room_id']]) ? 0 : $roomAudiences[$value['room_id']];
            $value['room_desc'] = self::$room_desc[$value['type']] . self::$room_mode[$value['room_model']];
            $value['status_desc'] = '正在直播';
            $value['is_living'] = 1;
            //当前主播是否为活动王者
            $points = $this->redis->zscore('activity:pk_rank:pk_rank_points', $value['user_id']);
            $value['photo_frame'] = $points > 2000 ? self::$photo_frame : '';
            $value['red_icon'] = 0;
            $value['nickname'] = !empty($value['title']) ? $value['title'] : $value['nickname'];
            $value['jump'] = getJump('enter_room', ['room_id' => $value['room_id'], 'from' => 'hot']);
        }
        return $data;
    }


    /**
     * 初始化直播离线数据
     *
     * @param array $data
     * @param int $israndcover
     * @return array
     */
    public function initializeUser($data = [], $israndcover = 0)
    {
        if (empty($data)) return [];

        foreach ($data as $key => &$value) {
            $value['room_id'] = $value['room_model'] = $value['type'] = '0';
            $value['create_time'] = '刚刚';
            $value['cover_url'] = img_url($value['avatar'], 'live');

            if ($value['room_model'] == 0) {
                $value['create_time'] = '刚刚';
            }

            if ($israndcover == 1) {
                $albumCount = Db::name('user_album')->where(['user_id' => $value['user_id']])->count();
                if ($albumCount > 1) {
                    $album = Db::name('user_album')->where(['user_id' => $value['user_id']])->orderRand()->value('image');
                    $value['cover_url'] = img_url($album, 'live');
                }
            }
            $value['city'] = '未知';
            $value['level'] = userMsg($value['user_id'], 'level')['level'];
            $value['is_pk'] = $value['is_living'] = 0;
            $value['audience'] = 0;
            $value['room_desc'] = '';
            $value['status_desc'] = '休息中';
            $value['title'] = $value['title'] ? $value['title'] : userMsg($value['user_id'], 'nickname')['nickname'];
            //当前主播是否为活动王者
            $points = $this->redis->zscore('activity:pk_rank:pk_rank_points', $value['user_id']);
            $value['photo_frame'] = $points > 2000 ? self::$photo_frame : '';
            //直播间红包图标
            $value['red_icon'] = 0;
            $value['jump'] = getJump('personal', ['user_id' => $value['user_id']]);
        }

        return $data;
    }


    /**
     * 获取在线直播列表
     *
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getLiveList()
    {

        $live = Db::name('live');

        $live->field('id room_id, user_id, nickname,pull,stream, avatar, title, cover_url, province, city, create_time, type, room_model,lng,lat,sort')
            ->where($this->on_line_where);

        !empty($this->order) && $live->order($this->order);

        if (!empty($this->custom_order)) {

            $whereuid = implode(',', $this->custom_order);

            $exp = new \think\db\Expression('field(user_id,' . $whereuid . ')');

            $live->order($exp);
        }

        $res = $live->select();

        return $res;
    }
    
    public function getserchLiveList()
    {

        $live = Db::name('live');

        $live->field('id room_id, user_id, nickname,pull,stream, avatar, title, cover_url, province, city, create_time, type, room_model,lng,lat,sort')
            ->where($this->on_line_where);

        !empty($this->order) && $live->order($this->order);

        if (!empty($this->custom_order)) {

            $whereuid = implode(',', $this->custom_order);

            $exp = new \think\db\Expression('field(user_id,' . $whereuid . ')');

            $live->order($exp);
        }

        $res = $live->limit($this->p, $this->length)->select();

        return $res;
    }


    /**
     * 获取直播离线列表
     *
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getLiveHistory()
    {
        $res = Db::name('user')
            ->where($this->off_line_where)
            ->field('user_id, nickname, avatar')
            ->select();
        return $res;
    }


    /**
     * 获取推荐置顶
     *
     * @return array
     */
    public function getHotTopList()
    {
        $key = 'cache:hotTop';

        $list = [];

        $user_ids = $this->redis->get($key);

        if (empty($user_ids)) return $list;

        $user_ids = explode(',', $user_ids);

        $living = $this->redis->smembers(self::$livePrefix . 'Living');

        //取交集
        $on_line = array_intersect($user_ids, $living);

        if (!empty($on_line)) {
            array_push($this->on_line_where, ['user_id', 'in', $on_line]);
            $on_list = $this->getLiveList();
            $on_list = $this->initializeLive($on_list);
            $list = array_merge($list, $on_list);
        }

        if (count($list) > 1) {
            $user_ids = array_flip($user_ids);

            foreach ($list as &$value) {
                if (array_key_exists($value['user_id'], $user_ids)) $value['sort'] = $value['sort'];
            }

            //排序
            usort($list, function ($a, $b) {

                if ($a['sort'] == $b['sort']) return 0;

                return ($a['sort'] < $b['sort']) ? 1 : -1;
            });
        }

        return $list;
    }


    /**
     * 设置热门直播
     *
     * @param $offset
     * @return $this
     */
    public function setHotLive($offset)
    {
        $keys = 'rank:charm:d:' . date('Ymd');
        //推荐上热门条件设置
        array_push($this->on_line_where, ['hot_status', 'eq', 1]);
        $this->offset = $offset;
        $rank = [];
        $live_bean = $this->redis->zrevrange($keys, 0, -1, true);
        foreach ($this->all_user_id['living'] as $user_id) {
            $rank[$user_id] = array_key_exists($user_id, $live_bean) ? $live_bean[$user_id] : 1;
        }
        arsort($rank);
        $living = array_keys($rank);

        //后面加的
        $Follow = new Follow();

        $followList = $Follow->getAllFollow(USERID);

        $follow_users = array_column($followList, 'follow_id');

        $follow_line = array_intersect($living, $follow_users);

        $living = array_unique(array_merge($follow_line, $living));

        $union_uid = array_unique(array_merge($living, $this->all_user_id['off_living']));

        $anchors = array_slice($union_uid, $this->offset, $this->length);
        //取交集
        $on_line = array_intersect($anchors, $living);
        //取差集
        $off_line = array_diff($anchors, $on_line);
        array_push($this->on_line_where, ['user_id', 'in', $on_line]);
        array_push($this->off_line_where, ['user_id', 'in', $off_line]);

        if ($follow_line) {
            $this->custom_order = $on_line;
        }
        return $this;
    }

    public function setNewHotLive($offset)
    {
        $channel_live = Db::name('live')
            ->where(['status' => 1, 'hot_status' => 1])
            ->field('id room_id, user_id, nickname,pull,stream, avatar, title, cover_url, province, city, create_time, type, room_model,lng,lat,sort')
            ->order('type','asc')
            ->select();
        $this->offset = $offset;
        if (!empty($channel_live)) {
            $Follow = new Follow();
            $follow_users = $Follow->getAllFollowUser(USERID);
            $channel_user = array_column($channel_live, 'user_id');
            $follow_line = array_intersect($channel_user, $follow_users);
            $channel_user = array_unique(array_merge($follow_line, $channel_user));
            $union_uid = array_unique(array_merge($channel_user, $this->all_user_id['off_living']));
        } else {
            $union_uid = $this->all_user_id['off_living'];
        }

        $anchors = array_slice($union_uid, $this->offset, $this->length);
        //取交集
        $on_line = array_intersect($anchors, $this->all_user_id['living']);
        //取差集
        $off_line = array_diff($anchors, $on_line);
        array_push($this->on_line_where, ['user_id', 'in', $on_line]);
        array_push($this->off_line_where, ['user_id', 'in', $off_line]);
        return $this;
    }

    /**
     * 设置关注直播
     *
     * @param $offset
     * @return $this
     */
    public function setFollowLive($offset)
    {
        $this->offset = $offset;

        $Follow = new Follow();

        $followList = $Follow->getAllFollow(USERID);

        if (empty($followList)) {
            array_push($this->on_line_where, ['user_id', 'in', []]);
            return $this;
        }

        $follow_users = array_column($followList, 'follow_id');


        $anchors = array_slice($follow_users, $this->offset, $this->length);

        //取交集
        $on_line = array_intersect($anchors, $this->all_user_id['living']);

        //取差集
        $off_line = array_diff($anchors, $on_line);

        array_push($this->on_line_where, ['user_id', 'in', $on_line]);

        array_push($this->off_line_where, ['user_id', 'in', $off_line]);

        return $this;
    }


    /**
     * 设置导购的直播
     *
     * @param $offset
     * @return $this
     */
    public function setShoppingLive($offset)
    {

        $this->offset = $offset;
        $liveGoods = Db::name('live_goods')->where("live_status != -1")->field('id,user_id,live_status')->select();
        $shoping_users = array_unique(array_column($liveGoods, 'user_id'));

        if (empty($shoping_users)) return [];

        $live = Db::name('live');
        $live->field('id room_id, user_id, nickname,pull,stream, avatar, title, cover_url, province, city, create_time, type, room_model,lng,lat,sort')
            ->where('user_id', 'in', $shoping_users);
        !empty($this->order) && $live->order($this->order);
        $res = $live->select();
        $anchors = array_slice($res, $this->offset, $this->length);
        return $anchors;

        //取交集
        /* $on_line = array_intersect($anchors, $this->all_user_id['living']);
         //取差集
         $off_line = array_diff($anchors, $on_line);
         array_push($this->on_line_where, ['user_id', 'in', $on_line]);
         array_push($this->off_line_where, ['user_id', 'in', $off_line]);*/
    }

    public function setVoiceLive($offset, $voice_value = -1)
    {
        $this->offset = $offset;
        $live = Db::name('live');
        $live->field('id room_id, user_id, nickname,pull,stream, avatar, title, cover_url, province, city, create_time, type, room_model,lng,lat,sort')
            ->where('room_model = 4 or room_model = 5')->limit($offset, $this->length);
        if ($voice_value != -1) $live->where('voice_number = ' . $voice_value);
        $anchors = $live->select();

        return $anchors;
    }


    /**
     * 设置附近的人
     *
     * @param $offset
     * @return $this
     */
    public function setNearbyLive($offset, $lng, $lat, $city, $sex, $age)
    {

        $this->offset = $offset;

        $where = '';

        if ($sex) $where .= ' and u.gender=' . "'$sex'";

        if ($city) $where .= ' and lv.city=' . "'$city'";

        if ($age) $where .= ' and TIMESTAMPDIFF(YEAR,FROM_UNIXTIME(u.birthday,"%Y-%m-%d %H:%i:%s"),NOW())  BETWEEN 0 and ' . $age;

        $sql = "SELECT lv.id as room_id,lv.user_id,lv.nickname,lv.pull,lv.stream,lv.avatar,lv.title,lv.cover_url,lv.province,lv.city,lv.lat,lv.lng, lv.create_time,lv.type,lv.room_model,lv.sort, u.gender FROM `bx_live` `lv` LEFT JOIN `bx_user` `u` ON `lv`.`user_id` = `u`.`user_id`  where `u`.`status`>1 and lv.lng>0 and lv.status=1 " . $where;

        $nearbyUsers = Db::query($sql);

        if (empty($nearbyUsers)) return [];

        foreach ($nearbyUsers as $key => $val) {
            $nearbyUsers[$key]['distance'] = getDistanceBetweenPointsNew($val['lat'], $val['lng'], $lat, $lng)['kilometers'];
        }

        $distance = array_column($nearbyUsers, 'distance');

        array_multisort($distance, SORT_ASC, $nearbyUsers);

        $nearbyUsers = array_slice($nearbyUsers, $this->offset, $this->length);

        return $nearbyUsers;

        /*$nearby_users = array_column($nearbyUsers, 'user_id');

        if (empty($nearby_users)) return $this;

        $anchors = array_slice($nearby_users, $this->offset, $this->length);

        //取交集
        $on_line = array_intersect($anchors, $this->all_user_id['living']);

        //取差集
        $off_line = array_diff($anchors, $on_line);

        array_push($this->on_line_where, ['user_id', 'in', $on_line]);

        array_push($this->off_line_where, ['user_id', 'in', $off_line]);

        $this->custom_order=$on_line;

        return $this;*/
    }


    /**
     * 设置最新直播
     *
     * @param $offset
     * @return $this
     */
    public function setNewLive($offset)
    {
        $this->offset = $offset;

        $union_uid = array_unique(array_merge($this->all_user_id['living'], $this->all_user_id['off_living']));

        $anchors = array_slice($union_uid, $this->offset, $this->length);

        //取交集
        $on_line = array_intersect($anchors, $this->all_user_id['living']);

        //取差集
        $off_line = array_diff($anchors, $on_line);

        array_push($this->on_line_where, ['user_id', 'in', $on_line]);

        array_push($this->off_line_where, ['user_id', 'in', $off_line]);

        $this->order = 'id desc';

        return $this;
    }


    /**
     * 频道下的直播
     *
     * @param $channel
     * @param $offset
     * @return $this
     */
    public function setChannelLive($channel, $offset)
    {
        $channel_live = Db::name('live')->where(['room_channel' => $channel])->field('user_id')->select();

        $this->offset = $offset;

        if (!empty($channel_live)) {
            $channel_user = array_column($channel_live, 'user_id');

            $union_uid = array_unique(array_merge($channel_user, $this->all_user_id['off_living']));
        } else {
            $union_uid = $this->all_user_id['off_living'];
        }

        $anchors = array_slice($union_uid, $this->offset, $this->length);

        //取交集
        $on_line = array_intersect($anchors, $this->all_user_id['living']);

        //取差集
        $off_line = array_diff($anchors, $on_line);

        array_push($this->on_line_where, ['user_id', 'in', $on_line]);

        array_push($this->off_line_where, ['user_id', 'in', $off_line]);

        return $this;
    }


    /**
     * 推荐直播
     *
     * @return array
     */
    public function recommend()
    {
        $top = $this->getHotTopList();
        //获取推荐池数据
        $pools = [];
        //$this->recommendPool(['hot' => 3, 'pk' => 1, 'activity' => 1, 'cover' => 1]);
        !empty($top) && $this->reset();
        //所有推荐的没有一个在线直播
        //则按正常热门取
        if (empty($pools)) {
            $length = !empty($top) ? (6 - count($top)) : 6;
            //临时处理
            DsSession::set('recommend_list', []);
            //将当前已看的uid放入个看已推记录
            $list = $this->recommendOffLine(0, $length);
            $on_line_where = array_pop($this->on_line_where);
            $off_line_where = array_pop($this->off_line_where);
            $recommend = array_merge($on_line_where[2], $off_line_where[2]);
        } else {
            $recommend = array_keys($pools);
            array_push($this->on_line_where, ['user_id', 'in', $recommend]);
            $on_list = $this->getLiveList();
            $list = $this->initializeLive($on_list);
            //排序
            foreach ($list as &$value) {
                if (array_key_exists($value['user_id'], $pools)) {
                    $value['weight'] = $pools[$value['user_id']];
                } else {
                    $value['weight'] = 0;
                }
            }
            //排序
            usort($list, function ($a, $b) {

                if ($a['weight'] == $b['weight']) return 0;

                return ($a['weight'] > $b['weight']) ? 1 : -1;
            });
            $amount = count($list);
            if ($amount < 6) {
                //补足6个
                $need_amount = 6 - $amount;
                $list_off = $this->recommendOffLine($amount, $need_amount);
                $list = array_merge($list, $list_off);
                $on_line_where = array_pop($this->on_line_where);
                $off_line_where = array_pop($this->off_line_where);
                $recommend2 = array_merge($on_line_where[2], $off_line_where[2]);
                $recommend = array_merge($recommend, $recommend2);
            }
        }
        //将当前已看的uid放入个看已推记录
        //DsSession::set('recommend_list', $recommend);
        !empty($top) && $list = array_merge($top, $list);
        $group1 = array_slice($list, 0, 4);
        $group2 = array_slice($list, 4, 2);
        return [$group1, $group2];
    }


    /**
     * 获取推荐直播离线数据
     *
     * @param $offset
     * @param $length
     * @return array
     */
    protected function recommendOffLine($offset, $length)
    {
        $this->length = $length;
        $this->setHotLive($offset);
        $hotList = $this->getLiveList();
        if (!empty($hotList)) $hotList = $this->initializeLive($hotList, 1);
        if (config('app.live_setting.is_rest_display')) {
            $offList = $this->getLiveHistory();
            if (!empty($offList)) $offList = $this->initializeUser($offList, 1);
            $list = array_merge($hotList, $offList);
        } else {
            $list = $hotList;
        }
        return $list;
    }


    /**
     * 推荐池
     *
     * @param array $flags
     * @return array
     */
    protected function recommendPool(array $flags)
    {
        $data = [];

        if (empty($flags)) return $data;

        $weight = [1, 3, 5, 4, 2, 6];

        //已观看过的
        $seen = DsSession::get('recommend_list');

        foreach ($flags as $flag => $amount) {
            //获取所有推荐的
            $flag_res = $this->redis->zRevRange('liveRecommend:' . $flag, 0, -1, true);

            if (empty($flag_res)) continue;

            //获取用户id
            $flagPool = array_keys($flag_res);

            //取差集
//            $flagPool = array_diff($flagPool, $seen);

            //取交集(正在直播的 )
            $recommend = array_intersect($flagPool, $this->all_user_id['living']);

            $this->arrange($recommend, $amount, $data);
        }

        if (!empty($data)) {
            $list = [];

            foreach ($data as $key => $value) {
                $list[$value] = $weight[$key];
            }

            return $list;
        }

        return $data;
    }


    protected function arrange($pools, $length, &$data = [])
    {
        if (!empty($pools)) {
            $_flagPool = array_slice($pools, 0, $length);

            if (!empty($data)) {
                //查看是否有重复的数据
                $_repeat = array_intersect($_flagPool, $data);

                //有重复数据
                if (!empty($_repeat)) {
                    $pools = array_slice($pools, $length);

                    $this->arrange($pools, $length - count($_repeat), $data);
                }
            }

            $data = array_merge($data, $_flagPool);
        }
    }


    //设置热门直播排序
    public function setHotLiveSort($data)
    {
        $keys = 'rank:charm:d:' . date('Ymd');

        $dayRank = $this->redis->zrevrange($keys, 0, -1, 1);

        if (!empty($dayRank)) {
            foreach ($data as $key => &$val) {
                if (array_key_exists($val['user_id'], $dayRank)) {
                    $val['income'] = $dayRank[$val['user_id']];
                } else {
                    $val['income'] = 0;
                }
            }

            usort($data, function ($a, $b) {

                if ($a['income'] == $b['income']) return 0;

                return ($a['income'] > $b['income']) ? -1 : 1;
            });
        }

        $length = 10;

        $start = ($this->hot_p - 1) * $length;

        $data = array_slice($data, $start, $length);

        return $data;
    }


    //设置电影直播
    public function setFilmLive($p)
    {
        $this->p = empty($p) ? 0 : ($p - 1) * $this->length;

        $this->where['room_model'] = self::FILM_MODE;

        return $this;
    }


    //设置搜索
    public function setSearch($key_word, $offset, $length = 10)
    {
        $this->p = $offset;

        $this->length = $length;

        if (preg_match('/^\d+$/', $key_word)) {
            $this->where[] = ['user_id|id', 'like', "%{$key_word}"];
        } else {
            $this->where[] = ['nickname|title', 'like', "%{$key_word}%"];
        }
        return $this;
    }

    /**
     * 设置导购的直播
     *
     * @param $offset
     * @return $this
     */
    public function setShoppingLiveNew($offset,$goods_type,$room_second_channel)
    {

        $this->offset = $offset;
        if(is_numeric($goods_type)){
            $liveGoods = Db::name('live_goods')->where("live_status != -1")->where(['goods_type'=>$goods_type])->field('id,user_id,live_status')->select();
        }else{
            $liveGoods = Db::name('live_goods')->where("live_status != -1")->field('id,user_id,live_status')->select();
        }

        $shoping_users = array_unique(array_column($liveGoods, 'user_id'));

        if (empty($shoping_users)) return [];
        if($room_second_channel>0){
            $where = [["room_second_channel","=",$room_second_channel],["user_id", "in", $shoping_users]];
        }else{
            $where = [["user_id", "in", $shoping_users]];
        }
        $live = Db::name('live');
        $live->field('id room_id, user_id, nickname,pull,stream, avatar, title, cover_url, province, city, create_time, type, room_model,lng,lat,sort')
          //  ->where('user_id', 'in', $shoping_users);
             ->where($where);
        !empty($this->order) && $live->order($this->order);
        $res = $live->select();
        $anchors = array_slice($res, $this->offset, $this->length);
        return $anchors;

        //取交集
        /* $on_line = array_intersect($anchors, $this->all_user_id['living']);
         //取差集
         $off_line = array_diff($anchors, $on_line);
         array_push($this->on_line_where, ['user_id', 'in', $on_line]);
         array_push($this->off_line_where, ['user_id', 'in', $off_line]);*/
    }


    /**
     * 初始化直播在线数据
     *
     * @param array $data
     * @param int $israndcover
     * @return array
     */
    public function initializeLiveNew($data = [], $israndcover = 0, $lng = 0, $lat = 0)
    {
        if (empty($data)) return [];
        $coreSdk = new CoreSdk();
        //后面加->获取当前正在看直播的人所关注的
        $Follow = new Follow();
        $followList = $Follow->getAllFollow(USERID);
        $follow_users = array_column($followList, 'follow_id');
        //后面加->获取当天收益第一人 热门榜单
        $keys = 'rank:charm:d:' . date('Ymd'); //当天收益榜单key
        $top_one = $this->redis->zrevrange($keys, 0, 0, true); //获取收益第一人 就是热门人
        $charm_top_ten = $this->redis->zrevrange($keys, 1, 9, true); //魅力榜单前十收益
        $live = new LiveGoods();
        $room_ids = array_column($data, 'room_id');
        $roomAudiences = $coreSdk->post('zombie/getRoomAudience', ['room_id' => $room_ids]);
        $now = time();
        foreach ($data as $key => &$value) {
            $value['create_time'] = $this->diffTime($now - $value['create_time']);
            $value['cover_url'] = img_url($value['cover_url'], 'live');

            if (intval($value['lat']) > 0 && intval($value['lng']) && intval($lat) > 0 && intval($lng) > 0) {
                $value['distance'] = (int)getDistanceBetweenPointsNew($value['lat'], $value['lng'], $lat, $lng)['meters'];
            } else {
                $value['distance'] = -1;
            }

            if (!empty($charm_top_ten)) {
                if (array_key_exists($value['user_id'], $charm_top_ten)) $value['type'] = 9; //魅力主播前十
            }


            if (!empty($follow_users)) {
                if (in_array($value['user_id'], $follow_users)) $value['type'] = 7; //关注的主播
            }

            if (!empty($top_one)) {
                if (array_key_exists($value['user_id'], $top_one)) $value['type'] = 8; //热门主播
            }

            //直播导购商品后面加的
            $value['live_goods'] = array_reverse($live->getLiveGoodsNew(['room_id' => $value['room_id']]));
            if ($value['live_goods']) {
                $value['type'] = 5; //导购
                $anchor_shop = Db::name('anchor_shop')->where(['user_id' => $value['user_id']])->find();
                $value['anchor_shop_name'] = $anchor_shop['title']; //导购
            }
            //性别后面加的
            $value['gender'] = userMsg($value['user_id'], 'gender')['gender'];
            $value['level'] = userMsg($value['user_id'], 'level')['level'];
            /*if( $israndcover == 1 ){
                $albumCount = Db::name('user_album')->where(['user_id'=>$value['user_id']])->count();
                if( $albumCount > 1 ){
                    $album = Db::name('user_album')->where(['user_id'=>$value['user_id']])->orderRand()->value('image');
                    $value['cover_url'] = img_url($album, 'live');
                }
            }*/
            empty($value['city']) && $value['city'] = empty($value['province']) ? '未知' : $value['province'];
            $pk_status = $this->checkPk($value['user_id']);
            //性别后面加的
            if ((int)$pk_status['is_pk']) {
                $value['type'] = 6; //Pk
            }
            $value['is_pk'] = (int)$pk_status['is_pk'];
            $value['audience'] = empty($roomAudiences[$value['room_id']]) ? 0 : $roomAudiences[$value['room_id']];
            $value['room_desc'] = self::$room_desc[$value['type']] . self::$room_mode[$value['room_model']];
            $value['status_desc'] = '正在直播';
            $value['is_living'] = 1;
            //当前主播是否为活动王者
            $points = $this->redis->zscore('activity:pk_rank:pk_rank_points', $value['user_id']);
            $value['photo_frame'] = $points > 2000 ? self::$photo_frame : '';
            $value['red_icon'] = 0;
            $value['nickname'] = !empty($value['title']) ? $value['title'] : $value['nickname'];
            $value['jump'] = getJump('enter_room', ['room_id' => $value['room_id'], 'from' => 'hot']);
        }
        return $data;
    }


}