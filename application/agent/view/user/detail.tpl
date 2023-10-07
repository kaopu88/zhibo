<extend name="public:base_nav"/>
<block name="css">
    <link href="__CSS__/user/detail.css?v=__RV__" type="text/css" rel="stylesheet"/>
</block>
<block name="js">
    <script src="__JS__/user/detail.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
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
                <h1>
                    [{$user.user_id}]&nbsp;{$user.nickname}
                </h1>
                    &nbsp;&nbsp;
                <eq name="user['verified']" value="1">
                    <span style="font-size: 12px;" class="fc_green">已认证</span>
                    <else/>
                    <span style="font-size: 12px;" class="fc_gray">未认证</span>
                </eq>
                <eq name="agent.add_sec" value="1">
                    <a ajax="post" ajax-confirm="确认重置用户昵称？"
                       href="{:url('user/reset_nickname',['user_id'=>$user['user_id']])}">重置昵称</a>&nbsp;&nbsp;&nbsp;
                    <a ajax="post" ajax-confirm="确认重置用户密码？"
                       href="{:url('user/reset_password',['user_id'=>$user['user_id']])}">重置密码</a>&nbsp;&nbsp;&nbsp;
                    <a ajax="post" ajax-confirm="确认重置用户头像？"
                       href="{:url('user/reset_avatar',['user_id'=>$user['user_id']])}">重置头像</a>&nbsp;&nbsp;&nbsp;
                    <a ajax="post" ajax-confirm="确认重置用户封面？"
                       href="{:url('user/reset_cover',['user_id'=>$user['user_id']])}">重置封面</a>&nbsp;&nbsp;&nbsp;
                    <a ajax="post" ajax-confirm="确认重置限制时间？"
                       href="{:url('user/reset_rename_time',['user_id'=>$user['user_id']])}">重置限制时间</a>
                </eq>
                </div>
                <p>
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
                        &nbsp;&nbsp;<a class="fc_red" href="">协议
                        <eq name="user['type']" value="robot">机器人
                            <else/>
                            用户
                        </eq>
                    </a>
                    </eq>
                    <eq name="user['is_promoter']" value="1">
                        &nbsp;&nbsp;<a target="_blank" class="fc_orange"
                                       href="{:url('promoter/detail',['user_id'=>$user.user_id])}">{:config('app.agent_setting.promoter_name')}</a>
                    </eq>
                    <eq name="user['is_anchor']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange" href="">主播</a>
                    </eq>
                    <eq name="user['is_creation']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange" href="">创作号</a>
                    </eq>
                </p>
                <p>
                    {$user.sign|default='这个家伙太懒了，什么也没留下。'}
                </p>
                <p>
                    <notempty name="$user['agent_info']">
                        {:config('app.agent_setting.agent_name')}：{$user['agent_info']['name']}
                    </notempty>
                    <notempty name="$user['promoter_info']">
                        &nbsp;&nbsp; {:config('app.agent_setting.promoter_name')}：<a target="_blank"
                                            href="{:url('promoter/detail',['user_id'=>$user.promoter_info.user_id])}">{$user.promoter_info|user_name}</a>
                    </notempty>
                    &nbsp;&nbsp;注册时间：{$user.create_time}
                </p>
            </div>
            <ul class="user_base_btns">
                <li>
                    <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$user.status}"
                         tgradio-name="status"
                         tgradio="{:url('user/change_status',['id'=>$user['user_id']])}"></div>
                </li>
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
                        信用评分
                        <br/>
                        <span class="show_num_span">{$user.credit_score}</span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            {:APP_BEAN_NAME}
            <div class="content_links"></div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 bean_tab md_width">
                <tr>
                    <td class="field_name font_nowrap">账户余额</td>
                    <td class="field_value">
                        {$user.bean}
                        <br/>
                        <span class="fc_red">不计入业绩额度:{$user.loss_bean}</span>
                    </td>
                    <td class="field_name font_nowrap">冻结余额</td>
                    <td class="field_value">{$user.fre_bean}</td>
                    <td class="field_name font_nowrap">累计充值</td>
                    <td class="field_value">{$user.recharge_total}</td>
                    <td class="field_name font_nowrap">支付功能</td>
                    <td class="field_value">
                        <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$user.pay_status}"
                            tgradio-name="pay_status"
                            tgradio="{:url('user/change_pay_status',['id'=>$user['user_id']])}"></div>
                    </td>
                    <td class="field_name font_nowrap">最后支付</td>
                    <td class="field_value">{$user.last_pay_time|time_format='从未支付','datetime'}</td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            {:APP_MILLET_NAME}
            <div class="content_links"></div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 bean_tab md_width">
                <tr>
                    <td class="field_name font_nowrap">剩余{:APP_MILLET_NAME}</td>
                    <td class="field_value">{$user.millet}</td>
                    <td class="field_name font_nowrap">冻结{:APP_MILLET_NAME}</td>
                    <td class="field_value">{$user.fre_millet}</td>
                    <td class="field_name font_nowrap">累计{:APP_MILLET_NAME}</td>
                    <td class="field_value">{$user.his_millet}</td>
                    <td class="field_name font_nowrap">提现功能</td>
                    <td class="field_value">
                        <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$user.millet_status}"
                            tgradio-name="millet_status"
                            tgradio="{:url('user/change_millet_status',['id'=>$user['user_id']])}"></div>
                    </td>
                    <td class="field_name font_nowrap">最近提现</td>
                    <td class="field_value">{$user.millet_change_time|time_format='未变动','datetime'}</td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            消费记录
            <div class="content_links">
                <a target="_blank" href="{:url('user/cons',['user_id'=>$user.user_id])}">更多记录({$cons_total})</a>
            </div>
        </div>
        <div class="mt_10">
            <include file="user/cons_list"/>
        </div>

        <div class="content_title2">
            充值记录
            <div class="content_links">
                <a target="_blank"
                   href="{:url('recharge/index',['user_id'=>$user.user_id])}">更多记录({$recharge_total})</a>
            </div>
        </div>
        <div class="mt_10">
            <include file="recharge/recharge_list"/>
        </div>

    </div>
</block>

<block name="layer">
</block>