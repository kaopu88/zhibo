<extend name="public:base_nav"/>
<block name="css">
    <link href="__CSS__/user/detail.css?v=__RV__" type="text/css" rel="stylesheet"/>
</block>
<block name="js">
    <script src="__VENDOR__/echarts/echarts.min.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/shine.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/dataTool.js?v=__RV__"></script>
    <script>
        var userId='{$promoter.user_id}';
    </script>
    <script src="__JS__/promoter/detail.js?v=__RV__"></script>
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
                <h1>
                    [{$user.user_id}]&nbsp;{$user.nickname}
                    &nbsp;&nbsp;
                    <eq name="user['verified']" value="1">
                        <span style="font-size: 12px;" class="fc_green">已认证</span>
                        <else/>
                        <span style="font-size: 12px;" class="fc_gray">未认证</span>
                    </eq>
                </h1>
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
                    &nbsp;&nbsp;加入{:config('app.agent_setting.promoter_name')}时间：{$promoter.create_time|time_format='','date'}
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

        <table class="content_list mt_10 show_num">
            <tr>
                <td>
                    总客户消费
                    <br/>
                    <span class="show_num_span">
                        <span style="font-size: 12px;color: #888;">暂不可用</span>
                        <!--{$promoter.total_cons}-->
                    </span>
                </td>
                <td>
                    总收入{:APP_MILLET_NAME}
                    <br/>
                    <span class="show_num_span">
                        <span style="font-size: 12px;color: #888;">暂不可用</span>
                        <!--{$promoter.total_millet}-->
                    </span>
                </td>
                <td>
                    总拉新人数
                    <br/>
                    <span class="show_num_span">{$promoter.total_fans}</span>
                </td>
                <td>
                    总客户人数
                    <br/>
                    <a href="{:url('promoter/clients',['promoter_uid'=>$promoter.user_id])}" class="show_num_span">{$promoter.client_num}</a>
                </td>
            </tr>
        </table>

        <div class="data_block mt_10 cons_trend">
            <div class="data_title">客户消费趋势图</div>
            <div class="data_toolbar">
                <div class="data_date">
                    <div class="data_date_line">
                        <!--  <a href="javascript:;" class="date_range" range-unit="d" range-num="0" range-default>今日</a>
                          <a href="javascript:;" class="date_range" range-unit="d" range-num="-1">昨日</a>-->
                        <a href="javascript:;" class="date_range" range-unit="w" range-num="0">本周</a>
                        <a href="javascript:;" class="date_range" range-unit="w" range-num="-1">上周</a>
                        <a href="javascript:;" class="date_range" range-unit="m" range-num="0" range-default>本月</a>
                        <a href="javascript:;" class="date_range" range-unit="m" range-num="-1">上月</a>
                    </div>
                    <input class="data_date_input" readonly/>
                    <input type="hidden" class="data_date_unit"/>
                    <input type="hidden" class="data_date_start"/>
                    <input type="hidden" class="data_date_end"/>
                </div>
            </div>
            <div style="width: 100%;height:450px;" class="mt_10 my_container">
            </div>
        </div>

        <div class="content_title2">
            客消明细
            <div class="content_links">
                <a target="_blank" href="{:url('kpi_cons/index',['promoter_uid'=>$promoter.user_id])}">更多记录({$cons_total})</a>
            </div>
        </div>
        <div class="mt_10">
            <include file="kpi_cons/cons_list"/>
        </div>

        <div class="content_title2">
            拉新明细
            <div class="content_links">
                <a target="_blank" href="{:url('kpi_fans/index',['promoter_uid'=>$promoter.user_id])}">更多记录({$fans_total})</a>
            </div>
        </div>
        <div class="mt_10">
            <include file="kpi_fans/fans_list"/>
        </div>

    </div>
</block>

<block name="layer">
</block>