<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;
use think\facade\Request;

class AdminLog extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('admin_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('admin_log');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['aid'] != '') {
            $where['aid'] = $get['aid'];
        }
        if ($get['route'] != '') {
            $where['route'] = $get['route'];
        }
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder()
    {
        $order = array();
        $order['add_time'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function add($route, $operation, $path, $aid=0)
    {
        if(empty($route)){
            return false;
        }
        $title = $this->getRes($route);
        if(empty($title)){
            return false;
        }
        $data['username'] = session("admin");
        if(!empty($aid)){
            $data['aid'] = $aid;
        }else {
            $data['aid'] = AID;
        }
        $data['title'] = $title;
        $data['operation'] = $operation;
        $data['add_time'] = time();
        $data['route'] = $route;
        $data['url'] = $path;
        $data['ip'] = Request::ip();
        Db::name('admin_log')->insertGetId($data);
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择记录');
        $num = Db::name('admin_log')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除失败');
        return $num;
    }

    public function getRes($route)
    {
        $allTypes = $this->getAllTypes();
        foreach ($allTypes as $value) {
            if ($value['type'] == $route) {
                return $value['text'];
            }
        }
        return "";
    }

    public function getAllTypes()
    {
        $data = [];
        $allType= $this->getAllOperate();
        foreach ($allType as $key => $value){
            if(is_array($value)) {
                foreach ($value as $ke => $val) {
                    if (is_array($val)) {
                        foreach ($val as $k => $v) {
                            $res[] = array(
                                'type' => $key . "." . $ke . "." . $k,
                                'text' => $value['name']."-".$val['name']."-".$v,
                            );
                        }
                    }
                }
            }
            $data = $res;
        }
        return $data;
    }

    public function getAllOperate()
    {
        return array(
            'personal' => $this->operateLogPersonal(),
            'account' => $this->operateLogAccount(),
            'system_config' => $this->operateLogSystemConfig(),
            'system' => $this->operateLogSystem(),
            'manager' => $this->operateLogManager(),
            'content' => $this->operateLogContent(),
            'user' => $this->operateLogUser(),
            'video' => $this->operateLogVideo(),
            'live' => $this->operateLogLive(),
            'sociaty' => $this->operateLogSociaty(),
            'shop' => $this->operateLogShop(),
            'friend' => $this->operateLogFriend(),
            'taokeshop' => $this->operateLogTaokeShop(),
            'taoke' => $this->operateLogTaoke(),
            'operate' => $this->operateLogOperateCenter(),
            'gift' => $this->operateLogGift(),
            'firstinvest' => $this->operateLogFirstinvest(),
            'videotask' => $this->operateLogVideoTask(),
            'voice' => $this->operateLogVoiceTask(),
            'medal' => $this->operateLogMedal(),
        );
    }

    protected function operateLogPersonal()
    {
        return array(
            'name' => '个人中心',
            'password' => array(
                'name' => '我的账号',
                'edit' => '修改密码',
            ),
        );
    }

    protected function operateLogAccount()
    {
        return array(
            'name' => '账户管理',
            'user'=> array(
                'name' => '用户管理',
                'login' => '登录',
            ),
        );
    }

    protected function operateLogSystemConfig()
    {
        return array(
            'name' => '系统配置',
            'index' => array(
                'name' => '站点信息',
                'edit' => '编辑',
            ),
            'app' => array(
                'name' => '公共配置',
                'edit' => '编辑',
            ),
            'video' => array(
                'name' => '视频配置',
                'edit' => '编辑',
            ),
            'third' => array(
                'name' => '服务配置',
                'edit' => '编辑',
            ),
            'live' => array(
                'name' => '直播配置',
                'edit' => '编辑',
            ),
            'upload' => array(
                'name' => '存储配置',
                'edit' => '编辑',
            ),
            'sms' => array(
                'name' => '消息配置',
                'edit' => '编辑',
            ),
            'product' => array(
                'name' => '产品信息',
                'edit' =>  '编辑',
            ),
            'payment' => array(
                'name' => '支付配置',
                'edit' => '编辑',
            ),
            'agent' => array(
                'name' => '合作商配置',
                'edit' => '编辑',
            ),
            'beauty' => array(
                'name' => '美颜配置',
                'edit' => '编辑',
            ),
        );
    }

    protected function operateLogSystem()
    {
        return array(
            'name' => '系统管理',
            'menu' => array(
                'name' => '菜单管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'category' => array(
                'name' => '类目管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'setting' => array(
                'name' => '缓存管理',
                'clear_runtime' => '清除文件缓存',
                'clear_redis' => '清除redis缓存',
                'clear_conf' => '重新生成配置',
            ),
            'timer' => array(
                'name' => '定时器',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'admin' => array(
                'name' => '管理员',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'admin_group' => array(
                'name' => '管理权限组',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'admin_rule' => array(
                'name' => '权限管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'work_types' => array(
                'name' => '工作类型',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'work' => array(
                'name' => '工作类型',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'database' => array(
                'name' => '数据库管理',
                'back' => '备份数据库',
                'optimize' => '优化数据表',
                'repair' => '修复数据表',
                'del' => '删除数据表',
                'restore' => '恢复数据库',
            ),
            'packages' => array(
                'name' => '版本管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'annex' => array(
                'name' => '附件管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'kpi' => array(
                'name' => '业绩管理',
                'transfer' => '移交',
            ),
            'help' => array(
                'name' => '帮助',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
        );
    }

    protected function operateLogManager()
    {
        return array(
            'name' => '常规管理',
            'vip' => array(
                'name' => 'VIP套餐',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'recharge' => array(
                'name' => '兑换规则',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'audit' => '审核',
            ),
            'recommend_space' => array(
                'name' => '推荐位管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'sms_template' => array(
                'name' => '短信模版管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'resources' => array(
                'name' => '资源管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'complaint' => array(
                'name' => '举报类型',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'audit' => '审核',
            ),
        );
    }

    protected function operateLogContent()
    {
        return array(
            'name' => '内容管理',
            'article' => array(
                'name' => '文章管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'notice' => array(
                'name' => '公告管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'article_comment' => array(
                'name' => '文章评论管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'reply' => '回复',
            ),
            'recommend_content' => array(
                'name' => '推荐文章管理',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'movie' => array(
                'name' => '电影管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'add_progress' => '进度新增',
                'edit_progress' => '进度编辑',
                'del_progress' => '进度删除',
            ),
        );
    }

    protected function operateLogUser()
    {
        return array(
            'name' => '用户管理',
            'user' => array(
                'name' => '用户管理',
                'add' => '新增',
                'edit' => '编辑',
                'verified' => '实名审核',
                'audit' => '用户审核',
            ),
            'anchor' => array(
                'name' => '主播管理',
                'add' => '新增',
                'edit' => '编辑',
                'cancel' => '取消',
                'audit' => '审核',
            ),
            'promoter' => array(
                'name' => '经纪人管理',
                'add' => '添加',
                'correct' => '校正数据',
                'cancel' => '取消',
                'transfer' => '转移客户',
                'release' => '解绑用户',
            ),
            'video' => array(
                'name' => '视频管理',
                'add' => '添加',
                'edit' => '编辑',
                'del' => '删除',
                'add_user' => '添加虚拟创作人',
                'cancel_user' => '取消虚拟创作人',
                'transfer' => '转移客户',
            ),
            'robot' => array(
                'name' => '机器人管理',
                'add' => '添加',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'impression' => array(
                'name' => '印象标签',
                'add' => '添加',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'credit_rule' => array(
                'name' => '信用规则',
                'add' => '添加',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'level' => array(
                'name' => '用户等级',
                'add' => '添加',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'anchor_level' => array(
                'name' => '主播等级',
                'add' => '添加',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'millet_cash' => array(
                'name' => '提现记录',
                'edit' => '编辑',
            ),
            'user_transfer' => array(
                'name' => '用户转移',
                'confirm' => '确认转移',
            ),
        );
    }

    protected function operateLogVideo()
    {
        return array(
            'name' => '视频管理',
            'teenager' => array(
                'name' => '青少年视频',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'music_category' => array(
                'name' => '音乐分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'music_singer' => array(
                'name' => '歌手',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'music_album' => array(
                'name' => '专辑',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'music' => array(
                'name' => '音乐',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'film_comment' => array(
                'name' => '视频评论',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'topic' => array(
                'name' => '话题',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'film_tags' => array(
                'name' => '视频标签',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'ad_video' => array(
                'name' => '短视频广告',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'flush' => '清空',
            ),
        );
    }

    protected function operateLogLive()
    {
        return array(
            'name' => '直播管理',
            'activity' => array(
                'name' => '活动',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'lottery_type' => array(
                'name' => '大转盘分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'topic' => array(
                'name' => '话题',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'lottery' => array(
                'name' => '大转盘活动',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'props' => array(
                'name' => '座驾',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'props_bean' => array(
                'name' => '座驾价格',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'gift' => array(
                'name' => '礼物',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'channel' => array(
                'name' => '频道',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'live' => array(
                'name' => '直播',
                'add_robot' => '新增机器人',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'push' => '直播间推送消息',
            ),
            'film' => array(
                'name' => '直播视频',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'set_anchor' => '设置主播',
                'cancel_anchor' => '取消主播',
            ),
            'live_film_ad' => array(
                'name' => '直播视频广告',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'lottery_gift' => array(
                'name' => '大转盘奖品',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'poster' => array(
                'name' => '海报',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'clear' => '清除',
            ),
            'week_star' => array(
                'name' => '周星活动',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'config' => '编辑设置',
            ),
        );
    }

    protected function operateLogSociaty()
    {
        return array(
            'name' => '公会管理',
            'agent' => array(
                'name' => '公会',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'withdraw' => '提现申请',
                'audit' => '审核',
                'check' => '搜索',
            ),
        );
    }

    protected function operateLogShop()
    {
        return array(
            'name' => '商城管理',
            'shop' => array(
                'name' => '商城小店',
                'set' => '条件设置',
                'del_log' => '删除支付记录',
            ),
        );
    }

    protected function operateLogFriend()
    {
        return array(
            'name' => '交友管理',
            'config' => array(
                'name' => '基础设置',
                'set' => '条件设置',
            ),
            'topic' => array(
                'name' => '话题管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'report' => array(
                'name' => '举报管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'lyrics' => array(
                'name' => '歌词管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'friend' => array(
                'name' => '发布信息管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'evaluate' => array(
                'name' => '评论留言管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'author' => array(
                'name' => '歌曲作者管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'comment' => array(
                'name' => '信息评论管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'circle' => array(
                'name' => '圈子管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'expresslist' => array(
                'name' => '表白推荐词',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'add_cate' => '新增分类',
                'edit_cate' => '编辑分类',
                'del_cate' => '删除分类',
            ),
        );
    }

    protected function operateLogTaokeShop()
    {
        return array(
            'name' => '淘客小店管理',
            'goods_cate' => array(
                'name' => '橱窗分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'audit' => '审核'
            ),
            'setting' => array(
                'name' => '小店设置',
                'edit' => '编辑',
            ),
            'goods' => array(
                'name' => '橱窗商品管理',
                'del' => '删除',
            ),
            'shop' => array(
                'name' => '小店管理',
                'edit' => '编辑',
                'del' => '删除',
                'del_log' => '删除开通支付记录',
            ),
        );
    }

    protected function operateLogTaoke()
    {
        return array(
            'name' => '淘客管理',
            'goods_cate' => array(
                'name' => '商品分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'goods' => array(
                'name' => '商品分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'ad' => array(
                'name' => '广告',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'ad_position' => array(
                'name' => '广告位',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'app_setting' => array(
                'name' => 'APP设置',
                'index' => '首页设置',
                'base' => '基础设置',
                'share' => '分享设置',
                'kuaizhan' => '快站设置',
                'template' => '推送消息模版设置',
            ),
            'bussiness' => array(
                'name' => '商学院',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'bussiness_cate' => array(
                'name' => '商学院分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'circle' => array(
                'name' => '商学院分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'circle_cate' => array(
                'name' => '商学院分类',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'common' => array(
                'name' => '淘客管理',
                'del_collect' => '删除收藏记录',
                'del_view' => '删除浏览记录',
                'del_order' => '删除订单',
                'del_profit' => '删除收益记录',
            ),
            'setting' => array(
                'name' => '淘客设置',
                'index' => '基础设置',
                'tb_auth' => '更新淘宝授权',
                'pdd_auth' => '更新拼多多授权',
                'channel' => '渠道设置',
                'search' => '超级搜索设置',
                'other' => '其他设置',
                'duomai' => '多麦设置',
                'distribute' => '分销设置',
                'withdraw' => '提现设置',
            ),
            'duomai' => array(
                'name' => '多麦广告',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'del_order' => '删除多麦订单',
            ),
            'level' => array(
                'name' => '淘客会员等级',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'module' => array(
                'name' => '模块管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'module_position' => array(
                'name' => '模块位管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'user' => array(
                'name' => '用户管理',
                'edit' => '淘客信息编辑',
            ),
            'special' => array(
                'name' => '特殊专题',
                'edit' =>  '编辑',
            ),
        );
    }

    protected function operateLogOperateCenter()
    {
        return array(
            'name' => '运营中心',
            'ad_content' => array(
                'name' => '广告内容',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'ad_space' => array(
                'name' => '广告位',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'creation' => array(
                'name' => '创作号',
                'edit' => '编辑',
            ),
            'viewback' => array(
                'name' => '反馈',
                'audit' => '审核',
            ),
            'film_timeline' => array(
                'name' => '电影时间线',
                'add' => '新增',
                'cancel' => '取消',
            ),
        );
    }

    protected function operateLogGift()
    {
        return array(
            'name' => '打赏分销',
            'config' => array(
                'name' => '基本设置',
                'setting' => '分销设置',
            ),
            'level' => array(
                'name' => '分销等级',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
        );
    }

    protected function operateLogFirstinvest()
    {
        return array(
            'name' => '首充管理',
            'config' => array(
                'name' => '首充设置',
                'setting' => '基本设置',
            ),
            'invest' => array(
                'name' => '首充活动',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'investgift' => array(
                'name' => '首充活动礼物',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
        );
    }

    protected function operateLogVideoTask()
    {
        return array(
            'name' => '短视频任务',
            'config' => array(
                'name' => '任务设置',
                'setting' => '基本设置',
            ),
            'task' => array(
                'name' => '任务管理',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
        );
    }

    protected function operateLogVoiceTask()
    {
        return array(
            'name' => '语聊',
            'voice_bg' => array(
                'name' => '语聊背景图',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
            'voice' => array(
                'name' => '语聊背景图',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
            ),
        );
    }

    protected function operateLogMedal()
    {
        return array(
            'name' => '勋章',
            'index' => array(
                'name' => '勋章列表',
                'add' => '新增',
                'edit' => '编辑',
                'del' => '删除',
                'change_status' => '切换',
            ),
        );
    }
}