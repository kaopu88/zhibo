<extend name="public:base_nav"/>
<block name="css">
    <link href="__CSS__/user/detail.css?v=__RV__" rel="stylesheet" type="text/css"/>
</block>
<block name="js">
    <script src="__JS__/user/detail.js?v=__RV__"></script>
    <script src="__JS__/user/update.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="user_base">
            <a rel="avatar" href="{:img_url($user['avatar'],'','avatar')}" class="user_base_avatar fancybox" alt="">
                <img src="{:img_url($user['avatar'],'200_200','avatar')}"/>
                <div class="thumb_level_box">
                    <img title="{$user.level_name}" src="{$user.level_icon}"/>
                </div>
            </a>
            <div class="user_base_info">
                <div class="user_base_title">
                    <h1>[{$user.user_id}]&nbsp;{$user.nickname}&nbsp;&nbsp;</h1>
                    <eq name="user['verified']" value="1"><span class="fc_green">已认证</span>
                        <else/>
                        <span class="fc_gray">未认证</span></eq>
                    <auth rules="admin:user:remark">
                        &nbsp;&nbsp;&nbsp;
                        <a data-id="user_id:{$user.user_id}" poplink="user_remark_box" href="javascript:;">
                            {$user.remark_name|default='未备注'}
                            <span class="icon-pencil"></span>
                        </a>
                    </auth>
                    <auth rules="admin:user:reset_nickname">
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户昵称？"
                           href="{:url('user/reset_nickname',['user_id'=>$user['user_id']])}">重置昵称</a>
                    </auth>
                    <auth rules="admin:user:reset_password">
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户密码？"
                           href="{:url('user/reset_password',['user_id'=>$user['user_id']])}">重置密码</a>
                    </auth>
                    <auth rules="admin:user:reset_nickname">
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户头像？"
                           href="{:url('user/reset_avatar',['user_id'=>$user['user_id']])}">重置头像</a>
                    </auth>
                    <auth rules="admin:user:reset_nickname">
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户封面？"
                           href="{:url('user/reset_cover',['user_id'=>$user['user_id']])}">重置封面</a>
                    </auth>
                    <auth rules="admin:user:reset_rename_time">
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置限制时间？"
                           href="{:url('user/reset_rename_time',['user_id'=>$user['user_id']])}">重置限制时间</a>
                    </auth>
                </div>
                <p>
                    <span class="icon-phone"></span>&nbsp;<eq name="admin['id']" value="1"> {$user.phone} <else/>{$user.phone|str_hide=3,4|default='未绑定'}</eq>&nbsp;
                    <switch name="user['gender']">
                        <case value="0">保密</case>
                        <case value="1"><span class="fc_blue">男</span></case>
                        <case value="2"><span class="fc_magenta">女</span></case>
                    </switch>
                    &nbsp;&nbsp;{$user.province_name|default='省份'}-{$user.city_name|default='城市'}&nbsp;&nbsp;{$user.birthday|default='生日'}
                    &nbsp;&nbsp;
                    <switch name="user['vip_status']">
                        <case value="0">
                            <span class="fc_gray">VIP {$user.vip_expire_str}</span>
                        </case>
                        <case value="1">
                            <span class="fc_green">VIP {$user.vip_expire_str}</span>
                        </case>
                        <case value="2">
                            <span class="fc_red">VIP {$user.vip_expire_str}</span>
                        </case>
                    </switch>
                    <eq name="user['isvirtual']" value="1">
                        &nbsp;&nbsp;<a class="fc_red" href="javascript:;">虚拟用户</a>
                    </eq>
                    <eq name="user['is_promoter']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange" href="{:url('promoter/detail',['user_id'=>$user['user_id']])}">{:config('app.agent_setting.promoter_name')}</a>
                    </eq>
                    <eq name="user['is_anchor']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange"
                                       href="{:url('anchor/detail',['user_id'=>$user['user_id']])}">主播</a>
                    </eq>
                    <eq name="user['is_creation']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange" href="">创作号</a>
                    </eq>
                    <auth rules="admin:user:update">
                        &nbsp;&nbsp;&nbsp;
                        <a data-id="user_id:{$user.user_id}" poplink="user_update_box" href="javascript:;">
                            {$user.remark_name|default='编辑'}
                            <span class="icon-pencil"></span>
                        </a>
                    </auth>
                </p>
                <p>
                    {$user.sign|default='这个家伙太懒了，什么也没留下。'}
                    <auth rules="admin:user:clear_sign">
                        <notempty name="user['sign']">
                            &nbsp;&nbsp;<a ajax="get" ajax-confirm
                                           href="{:url('user/clear_sign',['user_id'=>$user['user_id']])}">清除签名</a>
                        </notempty>
                    </auth>
                </p>
                <p>
                    <if condition="empty($user['agent_info'])">
                        直属用户
                        <else/>
                        <notempty name="$user['agent_info']">
                            {:config('app.agent_setting.agent_name')}：{$user['agent_info']['name']}
                        </notempty>
                        <notempty name="$user['promoter_info']">
                            &nbsp;&nbsp; {:config('app.agent_setting.promoter_name')}：<a href="{:url('promoter/detail',['user_id'=>$user.promoter_uid])}">{$user.promoter_info|user_name}</a>
                        </notempty>
                    </if>
                    &nbsp;&nbsp;注册时间：{$user.create_time}
                </p>
            </div>


            <ul class="user_base_btns">
                <auth rules="admin:user:change_status">
                    <li>
                        <div tgradio-before="tgradioStatusBefore" tgradio-not="0" tgradio-on="1" tgradio-off="0"
                             tgradio-value="{$user.status}"
                             tgradio-name="status"
                             tgradio="{:url('user/change_status',['id'=>$user['user_id']])}"></div>
                    </li>
                </auth>
                <li>
                    <a ajax="get" ajax-reload="false" href="{:url('user/refresh_redis',['user_id'=>$user['user_id']])}">
                        <span class="icon-reload"></span>&nbsp;刷新数据
                    </a>
                </li>
                <auth rules="admin:credit_log:add">
                    <li>
                        <a poplink="user_credit_box" data-id="user_id:{$user.user_id}" href="javascript:;">
                            <span class="icon-ribbon"></span>&nbsp;添加信用记录
                        </a>
                    </li>
                </auth>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 show_num sm_width">
                <tr>
                    <td>
                        收到的赞
                        <br/>
                        <span class="show_num_span">{$user.like_num}</span>
                    </td>
                    <td>
                        粉丝数量
                        <br/>
                        <span class="show_num_span">{$user.fans_num}</span>
                    </td>
                    <td>
                        关注数量
                        <br/>
                        <span class="show_num_span">{$user.follow_num}</span>
                    </td>
                    <td>
                        收藏数量
                        <br/>
                        <span class="show_num_span">{$user.collection_num}</span>
                    </td>
                    <td>
                        下载数量
                        <br/>
                        <span class="show_num_span">{$user.download_num}</span>
                    </td>
                    <td>
                        用户积分
                        <br/>
                        <a class="show_num_span" href="{:url('user_point/index',['user_id'=>$user.user_id])}">{$user.points}</a>
                    </td>
                    <td>
                        信用评分
                        <br/>
                        <a class="show_num_span" href="{:url('credit_log/_list',['user_id'=>$user.user_id])}">{$user.credit_score}</a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            {:APP_BEAN_NAME}
            <div class="content_links">
                <a href="{:url('bean_log/index',['user_id'=>$user['user_id']])}">变更记录</a>
                <a target="_blank" href="{:url('recharge_order/index',['user_id'=>$user.user_id])}">充值记录</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 bean_tab md_width">
                <tr>
                    <td class="field_name font_nowrap">账户余额</td>
                    <td class="field_value">
                        <div style="display:flex">
                            {$user.bean}
                            <auth rules="admin:recharge_order:add,admin:recharge_order:add_isvirtual">
                                &nbsp;&nbsp;
                                <a poplink="user_recharge_box" data-id="user_id:{$user.user_id}" class="recharge_btn"
                                   href="javascript:;">充值</a>
                                <a style="margin-left: 5px;" poplink="user_deduction_box" data-id="user_id:{$user.user_id}"
                                   class="deduction_btn"
                                   href="javascript:;">扣除</a>
                            </auth>
                        </div>
                    </td>
                    <td class="field_name font_nowrap">冻结余额</td>
                    <td class="field_value">{$user.fre_bean}</td>
                    <td class="field_name font_nowrap">累计充值</td>
                    <td class="field_value">{$user.recharge_total}</td>
                    <td class="field_name font_nowrap">支付功能</td>
                    <td class="field_value">
                        <div tgradio-not="{:check_auth('admin:user:change_pay_status')?'0':'1'}" tgradio-on="1"
                             tgradio-off="0" tgradio-value="{$user.pay_status}"
                             tgradio-name="pay_status"
                             tgradio="{:url('user/change_pay_status',['id'=>$user['user_id']])}"></div>
                    </td>
                    <td class="field_name font_nowrap">最后支付</td>
                    <td class="field_value">{$user.last_pay_time|time_format='从未支付','datetime'}</td>
                    <td class="field_name font_nowrap">不计入额度</td>
                    <td class="field_value">{$user.loss_bean}</td>
                </tr>
            </table>
        </div>

        <div class="content_title2">
            {:APP_MILLET_NAME}
            <div class="content_links">
                <a href="{:url('millet_log/index',['user_id'=>$user['user_id']])}">变更记录</a>
                <a href="{:url('millet_cash/index',['user_id'=>$user['user_id']])}">提现记录</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 millet_tab md_width">
                <tr>
                    <td class="field_name font_nowrap">剩余{:APP_MILLET_NAME}</td>
                    <td class="field_value">{$user.millet}</td>
                    <td class="field_name font_nowrap">冻结{:APP_MILLET_NAME}</td>
                    <td class="field_value">{$user.fre_millet}</td>
                    <td class="field_name font_nowrap">累计{:APP_MILLET_NAME}</td>
                    <td class="field_value">{$user.his_millet}</td>
                    <td class="field_name font_nowrap">提现功能</td>
                    <td class="field_value">
                        <div tgradio-not="{:check_auth('admin:user:change_millet_status')?'0':'1'}" tgradio-on="1"
                             tgradio-off="0" tgradio-value="{$user.millet_status}"
                             tgradio-name="millet_status"
                             tgradio="{:url('user/change_millet_status',['id'=>$user['user_id']])}"></div>
                    </td>
                    <td class="field_name font_nowrap">最近提现</td>
                    <td class="field_value">{$user.millet_change_time|time_format='未变动','datetime'}</td>
                </tr>
            </table>
        </div>

        <eq name="distribute_status" value="1">
            <div class="content_title2">
                {$distribute_name}
                <div class="content_links">
                    <a href="{:url('/giftdistribute/gift_commission_log/index',['user_id'=>$user['user_id']])}">变更记录</a>
                    <a href="">提现记录</a>
                </div>
            </div>
            <div class="table_slide">
                <table class="content_list mt_10 millet_tab md_width">
                    <tr>
                        <td class="field_name font_nowrap">剩余{$distribute_name}</td>
                        <td class="field_value">{$user.commission_price}</td>
                        <td class="field_name font_nowrap">冻结{$distribute_name}</td>
                        <td class="field_value">{$user.commission_pre_price}</td>
                        <td class="field_name font_nowrap">累计{$distribute_name}</td>
                        <td class="field_value">{$user.commission_total_price}</td>
                    </tr>
                </table>
            </div>
        </eq>

        <div class="content_title2">
            背包
            <div class="content_links">
                <a href="{:url('user_package/index',['user_id'=>$user['user_id']])}">更多</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">礼物信息</td>
                    <td style="width: 10%;">类型</td>
                    <td style="width: 10%;">数量</td>
                    <td style="width: 10%;">花费</td>
                    <td style="width: 10%;">获取方式</td>
                    <td style="width: 10%;">状态</td>
                    <td style="width: 10%;">可使用时间</td>
                    <td style="width: 10%;">过期时间</td>
                    <td style="width: 10%;">获取时间</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="user_packages">
                    <volist name="user_packages" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <div class="thumb">
                                    <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                        <img src="{:img_url($vo['icon'],'200_200','icon')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.name}
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td>{$vo.type ? '大礼物' : '小礼物'}</td>
                            <td>{$vo.num}</td>
                            <td>{$vo.user_cost}</td>
                            <td>
                                <switch name="vo['access_method']">
                                    <case value="liudanji">扭蛋机</case>
                                    <case value="lottery">大转盘</case>
                                </switch>
                            </td>
                            <td>
                                <switch name="vo['status']">
                                    <case value="0">失效</case>
                                    <case value="1">有效</case>
                                    <case value="2">已使用</case>
                                </switch>
                            <td>
                                <notempty name="vo.use_time">
                                    {$vo.use_time|time_format='','datetime'}
                                </notempty>
                            </td>
                            <td>
                                <notempty name="vo.expire_time">
                                    {$vo.expire_time|time_format='','datetime'}
                                </notempty>
                            </td>
                            <td>
                                {$vo.create_time|time_format='','datetime'}
                            </td>
                        </tr>
                    </volist>
                    <else/>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                </notempty>
                </tbody>
            </table>
        </div>

        <div class="content_title2">启动日志</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">会话标识</td>
                    <td style="width: 10%;">APP版本</td>
                    <td style="width: 10%;">系统版本</td>
                    <td style="width: 10%;">设备型号</td>
                    <td style="width: 10%;">网络状态</td>
                    <td style="width: 10%;">MEID</td>
                    <td style="width: 10%;">IP</td>
                    <td style="width: 10%;">启动位置</td>
                    <td style="width: 10%;">启动时间</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="start_logs">
                    <volist name="start_logs" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>{:str_hide($vo['access_token'],3,4,'*',true)}</td>
                            <td>{$vo.v_code}</td>
                            <td>{$vo.os_name} {$vo.os_version}</td>
                            <td>{$vo.brand_name} {$vo.device_model}</td>
                            <td>{$vo.network_status}</td>
                            <td>{:str_hide($vo['meid'],3,4,'*',true)}</td>
                            <td>{$vo.client_ip}</td>
                            <td></td>
                            <td>{$vo.start_time|time_format='','datetime'}</td>
                        </tr>
                    </volist>
                    <else/>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                </notempty>
                </tbody>
            </table>
        </div>

        <div class="content_title2 mt_10">登录日志</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">登录方式</td>
                    <td style="width: 10%;">APP版本</td>
                    <td style="width: 10%;">系统版本</td>
                    <td style="width: 10%;">设备型号</td>
                    <td style="width: 10%;">网络状态</td>
                    <td style="width: 10%;">MEID</td>
                    <td style="width: 10%;">IP</td>
                    <td style="width: 10%;">登录时间</td>
                    <td style="width: 10%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="login_logs">
                    <volist name="login_logs" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <switch name="vo['login_way']">
                                    <case value="quick">快捷登录</case>
                                    <case value="login">用户名登录</case>
                                    <case value="device">设备码登录</case>
                                    <case value="third">三方登录</case>
                                    <case value="after">注册后自动登录</case>
                                </switch>
                            </td>
                            <td>{$vo.v_code}</td>
                            <td>{$vo.os_name} {$vo.os_version}</td>
                            <td>{$vo.brand_name} {$vo.device_model}</td>
                            <td>{$vo.network_status}</td>
                            <td>{:str_hide($vo['meid'],3,4,'*',true)}</td>
                            <td>{$vo.login_ip}</td>
                            <td>{$vo.login_time|time_format='','datetime'}</td>
                            <td>
                                <auth rules="admin:ad_space:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('sbjin',array('sb'=>$vo['meid']))}?__JUMP__">禁用设备号</a>
                                </auth>
                            </td>
                        </tr>
                    </volist>
                    <else/>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                </notempty>
                </tbody>
            </table>
        </div>

        <div class="content_title2">网速监测</div>
        <div class="table_slide">
            <table class="content_list mt_10" style="min-width:1220px;">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">APP版本</td>
                    <td style="width: 10%;">系统版本</td>
                    <td style="width: 10%;">设备型号</td>
                    <td style="width: 10%;">[次数]场景/网络状态</td>
                    <td style="width: 10%;">上行速率(kbps)</td>
                    <td style="width: 10%;">下行速率(kbps)</td>
                    <td style="width: 10%;">MEID</td>
                    <td style="width: 10%;">IP</td>
                    <td style="width: 10%;">上报时间</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="network_logs">
                    <volist name="network_logs" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>{$vo.v_code}</td>
                            <td>{$vo.os_name} {$vo.os_version}</td>
                            <td>{$vo.brand_name} {$vo.device_model}</td>
                            <td>
                                [{$vo.num}]
                                <switch name="vo['scene']">
                                    <case value="live">直播重连</case>
                                </switch>
                                <br/>
                                {$vo.network_status}
                            </td>
                            <td>{$vo.upload_rate}</td>
                            <td>{$vo.download_rate}</td>
                            <td>{:str_hide($vo['meid'],3,4,'*',true)}</td>
                            <td>{$vo.client_ip}</td>
                            <td>{$vo.create_time|time_format='','datetime'}</td>
                        </tr>
                    </volist>
                    <else/>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                </notempty>
                </tbody>
            </table>
        </div>

        <!--  <div class="content_title2">VIP订单</div>
          <div class="content_title2">收到礼物</div>
          <div class="content_title2">送出礼物</div>
          <div class="content_title2">兴趣标签</div>
          <div class="content_title2">实名认证</div>-->
    </div>
</block>

<block name="layer">
    <include file="user/recharge_pop"/>
    <include file="user/deduction_pop"/>
    <include file="user/remark_pop"/>
    <include file="user/user_credit_pop"/>
    <include file="user/disable_pop"/>
    <include file="user/update_pop"/>
</block>