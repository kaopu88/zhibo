<extend name="public:base_nav"/>
<block name="css">
    <link href="__CSS__/user/detail.css?v=__RV__" rel="stylesheet" type="text/css"/>
    <style>
        .show_num td {
            width: 12.5%;
        }
    </style>
</block>
<block name="js">
    <script src="__JS__/user/detail.js?v=__RV__"></script>
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
                    <h1 style="color: #FF12A6;">[{$user.user_id}]&nbsp;{$user.nickname}&nbsp;&nbsp;</h1>
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
                </div>
                <p>
                    <span class="icon-phone"></span>&nbsp;{$user.phone|default='未绑定'}&nbsp;&nbsp;
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
                        &nbsp;&nbsp;<a class="fc_red" href="">虚拟用户</a>
                    </eq>
                    <eq name="user['is_promoter']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange" href="{:url('promoter/detail',['user_id'=>$user['user_id']])}">{:config('app.agent_setting.promoter_name')}</a>
                    </eq>
                    <eq name="user['is_creation']" value="1">
                        &nbsp;&nbsp;<a class="fc_orange" href="">创作号</a>
                    </eq>
                    &nbsp;&nbsp;<a href="{:url('user/detail',['user_id'=>$user['user_id']])}">转到用户详情</a>
                </p>
                <p>
                    {$user.sign|default='这个家伙太懒了，什么也没留下。'}
                </p>
                <p>
                    <if condition="empty($user['agent_info'])">
                        直属用户
                        <else/>
                        <notempty name="$user['agent_info']">
                            {:config('app.agent_setting.agent_name')}：<a href="">{$user['agent_info']['name']}</a>
                        </notempty>
                        <notempty name="$user['promoter_info']">
                            &nbsp;&nbsp; {:config('app.agent_setting.promoter_name')}：<a href="">{$user.promoter_info|user_name}</a>
                        </notempty>
                    </if>
                    &nbsp;&nbsp;加入时间：{$user.anchor.create_time|time_format='','date'}
                </p>
            </div>


            <ul class="user_base_btns">
                <auth rules="admin:user:change_status">
                    <li>
                        <div tgradio-not="0" tgradio-on="1" tgradio-off="0" tgradio-value="{$user.status}"
                             tgradio-name="status"
                             tgradio="{:url('user/change_status',['id'=>$user['user_id']])}"></div>
                    </li>
                </auth>
                <li>
                    <a ajax="get" ajax-reload="false" href="{:url('user/refresh_redis',['user_id'=>$user['user_id']])}">
                        <span class="icon-reload"></span>&nbsp;刷新数据
                    </a>
                </li>
            </ul>
            <div class="clear"></div>
        </div>
        <table class="content_list mt_10 show_num">
            <tr>
                <td>
                    累计收获{:APP_MILLET_NAME}
                    <br/>
                    <span class="show_num_span">{$user.anchor.total_millet}</span>
                </td>
                <td>
                    累计直播时长
                    <br/>
                    <span class="show_num_span">{$user.anchor.total_duration_str}</span>
                </td>
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
        <div class="content_title2">
            {:APP_MILLET_NAME}
            <div class="content_links">
                <a href="">变更记录</a>
                <a href="">提现记录</a>
            </div>
        </div>
        <table class="content_list mt_10 bean_tab">
            <tr>
                <td class="field_name">剩余{:APP_MILLET_NAME}</td>
                <td class="field_value">{$user.millet}</td>
                <td class="field_name">冻结{:APP_MILLET_NAME}</td>
                <td class="field_value">{$user.fre_millet}</td>
                <td class="field_name">累计{:APP_MILLET_NAME}</td>
                <td class="field_value">{$user.his_millet}</td>
                <td class="field_name">提现功能</td>
                <td class="field_value">
                    <div tgradio-not="{:check_auth('admin:user:change_millet_status')?'0':'1'}" tgradio-on="1"
                         tgradio-off="0" tgradio-value="{$user.millet_status}"
                         tgradio-name="millet_status"
                         tgradio="{:url('user/change_millet_status',['id'=>$user['user_id']])}"></div>
                </td>
                <td class="field_name">最近提现</td>
                <td class="field_value">{$user.millet_change_time|time_format='未变动','datetime'}</td>
            </tr>
        </table>
        <div class="content_title2">
            TA的守护&nbsp;({$guard_total})
            <div class="content_links">
                <auth rules="admin:anchor:guard">
                    <a href=""><span style="margin-right: 10px;" class="icon-plus"></span>守护</a>
                </auth>
            </div>
        </div>
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                <td style="width: 10%;">用户ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 15%;">到期时间</td>
                <td style="width: 20%;">所属{:config('app.agent_setting.agent_name')}</td>
                <td style="width: 30%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="guards">
                <volist name="guards" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <include file="user/user_info"/>
                        </td>
                        <td>{$vo.guard_expire|time_format=''}</td>
                        <td>
                            <include file="user/user_agent2"/>
                        </td>
                        <td>
                            <auth rules="admin:anchor:guard">
                                <a ajax="post" ajax-confirm="是否确认移除守护？" class="fc_red"
                                   href="{:url('anchor/remove_guard',['anchor_uid'=>$user['user_id'],'user_id'=>$vo.user_id])}">移除守护</a>
                            </auth>
                        </td>
                    </tr>
                </volist>
                <else/>
                <tr>
                    <td>
                        <div class="content_empty">
                            <div class="content_empty_icon"></div>
                            <p class="content_empty_text">暂无守护</p>
                        </div>
                    </td>
                </tr>
            </notempty>
            </tbody>
        </table>
    </div>
</block>

<block name="layer">
    <include file="user/remark_pop"/>
</block>