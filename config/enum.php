<?php

$config = [
    'third_type' => array(
        array('name' => 'QQ', 'value' => 'qq', 'appid' => '1106848154', 'appsecret' => 'EUw4VjrNsIO6hqWD'),
        array('name' => '微信', 'value' => 'weixin', 'appid' => 'wx51c65e831190eab7', 'appsecret' => 'e9535316f3966922d9bd77ea80b015dc'),
        array('name' => 'apple', 'value' => 'apple', 'appid' => 'apple_id', 'appsecret' => 'apple_appsecret')
    ),
    'sms_code_scenes' => array(
        //exists 1手机号需要已存在user表中 0手机号需要不存在
        //bind 1 需要登录且当前登录用户已绑定这个手机号
        //sms_tpl国内短信模板 g_sms_tpl 国际短信模板
        array('name' => '登录', 'value' => 'login', 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '注册', 'value' => 'reg', 'exists' => 0, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '找回密码', 'value' => 'get_back_pwd', 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '第三方登录绑定手机号', 'value' => 'third_bind', 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '绑定手机号', 'value' => 'bind', 'exists' => 0, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '修改密码', 'value' => 'change_pwd', 'bind' => 1, 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '重置密码', 'value' => 'reset_pwd', 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '提现账号', 'value' => 'auth_cash', 'bind' => 1, 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '设置主账号', 'value' => 'set_root', 'exists' => 0, 'main' => 'agent_admin', 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '重置后台密码', 'value' => 'admin_reset_pwd', 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '注销账户', 'value' => 'user_logoff', 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
        array('name' => '后台任务通知	', 'value' => 'admin_task', 'exists' => 1, 'sms_tpl' => 'SMS_181495960', 'g_sms_tpl' => ''),
    ),
    //支付方式
    'pay_methods' => array(
        array('name' => '支付宝手机页面', 'value' => 'alipay_wap', 'platform' => '支付宝'),
        array('name' => '支付宝PC页面', 'value' => 'alipay_page', 'platform' => '支付宝'),
        array('name' => '支付宝APP', 'value' => 'alipay_app', 'platform' => '支付宝'),
        array('name' => '微信支付APP', 'value' => 'wxpay_app', 'platform' => '微信支付'),
        array('name' => '微信支付小程序', 'value' => 'wxpay_wxapp', 'platform' => '微信支付'),
        array('name' => '微信公众号支付', 'value' => 'wxpay_h5', 'platform' => '微信支付'),
        array('name' => '微信公众号H5支付', 'value' => 'wxpay_wxwap', 'platform' => '微信支付'),
        array('name' => '系统赠送', 'value' => 'system_free', 'platform' => '后台系统'),
        array('name' => '系统结算', 'value' => 'system_pay', 'platform' => '后台系统'),
        array('name' => 'Apple支付', 'value' => 'applepay_app', 'platform' => 'Apple支付'),
        array('name' => '皇嘉支付', 'value' => 'hjpay', 'platform' => '皇嘉支付'),
    ),
    'pay_platforms' => array(
        array('name' => '支付宝', 'value' => 'alipay'),
        array('name' => '微信支付', 'value' => 'wxpay'),
        array('name' => '系统操作', 'value' => 'system'),
        array('name' => 'Apple支付', 'value' => 'applepay'),
    ),
    'cash_account_types' => array(
        array('name' => '支付宝', 'value' => 'alipay'),
        array('name' => '微信', 'value' => 'wxpay'),
        array('name' => '银行卡', 'value' => 'bank'),
    ),
    'recharge_pay_methods' => array(
        // array('name' => '虚拟号充值（免审核）', 'value' => 'isvirtual', 'rules' => 'admin:recharge_order:add_isvirtual,admin:recharge_order:add'),
        array('name' => '免费赠送（需审核）', 'value' => 'free', 'rules' => 'admin:recharge_order:add'),
        array('name' => '支付宝（需审核）', 'value' => 'alipay', 'rules' => 'admin:recharge_order:add'),
        array('name' => '现金（需审核）', 'value' => 'cash', 'rules' => 'admin:recharge_order:add'),
        array('name' => '微信支付（需审核）', 'value' => 'wxpay', 'rules' => 'admin:recharge_order:add'),
        array('name' => '银行转账（需审核）', 'value' => 'bank', 'rules' => 'admin:recharge_order:add'),
    ),
    'deduction_methods' => array(
        //array('name' => '虚拟号扣款（免审核）', 'value' => 'isvirtual', 'rules' => 'admin:recharge_order:deduction,admin:recharge_order:deduction_isvirtual'),
      //  array('name' => '充值退款（需审核）', 'value' => 'refund', 'rules' => 'admin:recharge_order:deduction'),
        array('name' => '其他扣款', 'value' => 'other', 'rules' => 'admin:recharge_order:deduction'),
    ),
    'share_channels' => array(
        array('name' => '朋友圈', 'value' => 'friends'),
        array('name' => '微信', 'value' => 'wx'),
        array('name' => 'QQ', 'value' => 'qq'),
        array('name' => 'QQ空间', 'value' => 'qzone'),
        array('name' => '新浪微博', 'value' => 'weibo'),
    ),
    'ad_space_types' => array(
        array('name' => '轮播图', 'value' => 'banner'),
        array('name' => '文字', 'value' => 'text'),
        array('name' => '图文', 'value' => 'graphic'),
    ),
    'ad_purviews' => array(
        array('name' => '登录可见', 'value' => 'login'),
        array('name' => '未登录可见', 'value' => 'not_login'),
        array('name' => '会员可见', 'value' => 'vip'),
        array('name' => '非会员可见', 'value' => 'not_vip'),
    ),
    'ad_os' => array(
        array('name' => '安卓', 'value' => 'android'),
        array('name' => 'IOS', 'value' => 'ios'),
        array('name' => '小程序', 'value' => 'wxapp'),
        array('name' => 'PC网站', 'value' => 'pc'),
        array('name' => 'H5网站', 'value' => 'h5'),
    ),
    'mv_status' => array(
        array('name' => '筹划中', 'value' => '0', 'color' => '#1bbc9b'),
        array('name' => '预告片', 'value' => '1', 'color' => '#ff6600'),
        array('name' => '热映中', 'value' => '2', 'color' => '#f20000'),
        array('name' => '已下线', 'value' => '3', 'color' => '#b0b0b0'),
        array('name' => '储备中', 'value' => '4', 'color' => '#05D7FF'),
    ),
    'raise_status' => array(
        array('name' => '待支付', 'value' => '0'),
        array('name' => '核实中', 'value' => '1'),
        array('name' => '已支付', 'value' => '2'),
        array('name' => '已取消', 'value' => '3'),
        array('name' => '未核实', 'value' => '4'),
    ),
    'agent_subject_types' => array(
        array('name' => '个人', 'value' => 'personal'),
        array('name' => '企业', 'value' => 'company'),
    ),
    'agent_grades' => array(
        array('name' => '普通', 'value' => 1),
        array('name' => '青铜', 'value' => 2),
        array('name' => '白银', 'value' => 3),
        array('name' => '黄金', 'value' => 4),
        array('name' => '白金', 'value' => 5),
        array('name' => '钻石', 'value' => 6),
    ),
    'recommend_space_types' => array(
        array('name' => '文章', 'value' => 'art'),
        array('name' => '用户', 'value' => 'user'),
        array('name' => '视频', 'value' => 'film'),
    ),
    'freezing_account_rate' => array(
        "15 minutes" => '1',
        "1 hours" => '2',
        "6 hours" => '3',
        "1 days" => '4',
        "3 days" => '5',
        "7 days" => '6',
        "1 months" => '7',
        "1 months" => '8',
        "3 months" => '9',
        "1 years" => '10'
    ),
    'bean_reward_types' => array(
        array('name' => '邀请注册奖励', 'value' => 'reg_reward_bean'),
        array('name' => '邀请用户奖励', 'value' => 'invite_reward_bean'),
        array('name' => '发布视频奖励钻石', 'value' => 'video_reward_bean'),
        array('name' => '发布视频奖励金币', 'value' => 'video_reward_millet'),
        array('name' => '邀请新人现金奖励', 'value' => 'inviteFriends_reward_cash'),
    ),
    //交易类型
    'trade_types' => array(
        array('name' => 'VIP', 'value' => 'vip'),
        array('name' => '充值', 'value' => 'recharge'),
        array('name' => '提现', 'value' => 'cash'),
        array('name' => '观看付费视频', 'value' => 'pay_per_view'),
        array('name' => '视频礼物', 'value' => 'video_gift'),
        array('name' => '直播礼物', 'value' => 'live_gift'),
        array('name' => '直播弹幕', 'value' => 'barrage'),
        array('name' => '观看付费直播', 'value' => 'live_payment'),
        array('name' => '电影众筹', 'value' => 'raise'),
        array('name' => '封面之星投票', 'value' => 'cover_star_vote'),
        array('name' => '扭蛋机抽奖', 'value' => 'liudanji'),
        array('name' => '购买道具', 'value' => 'buyProps'),
        array('name' => '大转盘抽奖', 'value' => 'buyLottery'),
        array('name' => '拉新收益', 'value' => 'reward'),
        array('name' => '关注用户', 'value' => 'followFriends'),
        array('name' => '视频评论', 'value' => 'commentVideo'),
        array('name' => '视频点赞', 'value' => 'thumbsVideo'),
        array('name' => '邀请好友', 'value' => 'inviteFriends'),
        array('name' => '每日登陆', 'value' => 'dailyLogin'),
        array('name' => '观看视频任务', 'value' => 'watchVideo'),
        array('name' => '分享视频任务', 'value' => 'shareVideo'),
        array('name' => '上传视频任务', 'value' => 'postVideo'),
    ),
    'admin_millet_trade_types' => array(
        array('name' => '直播礼物', 'value' => 'live_gift'),
        array('name' => '直播弹幕', 'value' => 'barrage'),
        array('name' => '用户背包', 'value' => 'user_package'),
        array('name' => '视频礼物', 'value' => 'video_gift'),
        array('name' => '观看付费直播', 'value' => 'live_payment'),
        array('name' => '封面之星投票', 'value' => 'cover_star_vote'),
        array('name' => '拉新收益', 'value' => 'reward'),
        array('name' => '提现', 'value' => 'cash'),
        array('name' => '积分兑换', 'value' => 'score'),
        array('name' => '关注用户', 'value' => 'followFriends'),
        array('name' => '视频评论', 'value' => 'commentVideo'),
        array('name' => '视频点赞', 'value' => 'thumbsVideo'),
        array('name' => '发布视频', 'value' => 'postVideo'),
        array('name' => '分享视频', 'value' => 'shareVideo'),
        array('name' => '邀请好友', 'value' => 'inviteFriends'),
        array('name' => '观看视频', 'value' => 'watchVideo'),
        array('name' => '每日登陆', 'value' => 'dailyLogin'),
    ),
    'millet_trade_types' => array(
        array('name' => '直播礼物', 'value' => 'live_gift'),
        array('name' => '直播弹幕', 'value' => 'barrage'),
        array('name' => '用户背包', 'value' => 'user_package'),
        array('name' => '视频礼物', 'value' => 'video_gift'),
        array('name' => '观看付费直播', 'value' => 'live_payment'),
        array('name' => '封面之星投票', 'value' => 'cover_star_vote'),
        array('name' => '拉新收益', 'value' => 'reward'),
    ),
    'bean_trade_types' => array(
        array('name' => '直播礼物', 'value' => 'live_gift'),
        array('name' => '直播弹幕', 'value' => 'barrage'),
        array('name' => '用户背包', 'value' => 'user_package'),
        array('name' => '观看付费直播', 'value' => 'live_payment'),
        array('name' => '封面之星投票', 'value' => 'cover_star_vote'),
        array('name' => '砸金蛋', 'value' => 'egg'),
    ),
    'giftdistribute_trade_types' => array(
        array('name' => '直播礼物', 'value' => 'live_gift'),
        array('name' => '视频礼物', 'value' => 'video_gift'),
        array('name' => '提现', 'value' => 'cash'),
    ),
    'packages_update_types' => array(
        array('name' => '普通更新', 'value' => '1'),
        array('name' => '强制更新', 'value' => '2'),
    ),
    'packages_channel' => array(
        array('name' => '通用', 'value' => 'common'),
        array('name' => '小米', 'value' => 'xiaomi'),
        array('name' => '华为', 'value' => 'huawei'),
        array('name' => '360', 'value' => '360'),
        array('name' => '应用宝', 'value' => 'qq'),
        array('name' => '百度手机助手', 'value' => 'baidu'),
        array('name' => 'OPPO', 'value' => 'oppo'),
        array('name' => 'VIVO', 'value' => 'vivo'),
        array('name' => '魅族', 'value' => 'meizu'),
    ),
    'packages_os' => array(
        array('name' => 'Android', 'value' => 'android'),
        array('name' => 'IOS', 'value' => 'ios'),
    ),
    'live_film_source' => array(
        array('name' => '腾讯视频', 'value' => 'qq'),
        array('name' => '爱奇艺', 'value' => 'iqiyi'),
        array('name' => '搜狐视频', 'value' => 'sohu'),
        array('name' => 'yskk', 'value' => 'yskk'),
    ),
    'voice_type' => array(
        array('name' => '电台', 'value' => '0'),
        array('name' => '四人语音', 'value' => '4'),
        array('name' => '八人语音', 'value' => '8'),
    ),
    'agent_price_trade_type' => array(
        array('name' => '结算', 'value' => 'settlement'),
        array('name' => '提现', 'value' => 'cash'),
    ),
    'medal_type' => array(
        array('name' => '基础勋章', 'value' => 'basic'),
    ),
    'medal_condition_type' => array(
        array('name' => '消费', 'value' => 'bean'),
        array('name' => '收益', 'value' => 'millet'),
        array('name' => '粉丝数', 'value' => 'fans'),
    ),
    //未验证定义
    /*'user_type' => [
        ['name' => '普通用户', 'value' => 'user'],
        ['name' => '主播', 'value' => 'anchor'],
        ['name' => '虚拟用户', 'value' => 'isvirtual'],
        ['name' => '公会', 'value' => 'agent_name'],
        ['name' => '经纪人', 'value' => 'promoter_name']
    ],*/

];

use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/enum.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;