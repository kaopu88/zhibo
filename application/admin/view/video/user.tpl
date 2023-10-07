<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var countdown = parseInt('{$countdown}');
        var regUrl = '{:url("user/reg")}';
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'user_type',
                    title: '用户身份',
                    opts: [
                        {name: '普通用户', value: 'user'},
                        {name: '主播', value: 'anchor'},
                        {name: '{:config('app.agent_setting.promoter_name')}', value: 'promoter'},
                        {name: '非{:config('app.agent_setting.promoter_name')}', value: 'not_promoter'},
                        {name: '非主播', value: 'not_anchor'},
                        {name: '虚拟用户', value: 'isvirtual'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/user/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <auth rules="admin:film_user:add">
                    <a class="base_button base_button_s" href="javascript:;" poplink="add_video_user">新增用户</a>
                    </auth>
                    <div class="filter_search">
                        <input placeholder="用户ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="live_status" value="{:input('live_status')}"/>
            <input type="hidden" name="vip_status" value="{:input('vip_status')}"/>
            <input type="hidden" name="user_type" value="{:input('user_type')}"/>
            <input type="hidden" name="level" value="{:input('level')}"/>
            <input type="hidden" name="province" value="{:input('province')}"/>
            <input type="hidden" name="city" value="{:input('city')}"/>
            <input type="hidden" name="district" value="{:input('district')}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10" style="min-width: 840px;">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 7%;">ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 9%;">用户类型</td>
                <td style="width: 10%;">用户属性</td>
                <td style="width: 13%;">{:config('app.agent_setting.agent_name')}信息</td>
                <td style="width: 8%;">{:APP_BEAN_NAME}</td>
                <td style="width: 8%;">功能</td>
                <td style="width: 9%;">状态</td>
                <td style="width: 7%;">最近登录</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <include file="user/user_info"/>
                        </td>
                        <td>
                            <include file="user/user_type"/>
                        </td>
                        <td>
                            <b>{$vo.city_info.name|default='未知'}</b><br/>
                            <include file="user/user_vip_status"/>
                            <br/>
                            <eq name="vo['verified']" value="1">
                                <span class="fc_green">已认证</span>
                                <else/>
                                <span class="fc_gray">未认证</span>
                            </eq>
                        </td>
                        <td>
                            <include file="user/user_agent"/>
                        </td>
                        <td>
                            <eq name="vo['pay_status']" value="1">
                                <span class="icon-credit"></span>&nbsp;<span>{$vo.bean}</span><br/>
                                <else/>
                                <span class="icon-credit"></span>&nbsp;<span title="支付功能已禁用"
                                                                             class="fc_red">{$vo.bean}</span><br/>
                            </eq>
                            <span class="fc_gray">  <span class="icon-lock"></span>&nbsp;{$vo.fre_bean}</span>
                        </td>
                        <td>
                            <include file="user/user_fun"/>
                        </td>
                        <td>
                            <div tgradio-before="tgradioStatusBefore"
                                 tgradio-not="{:check_auth('admin:user:change_status')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status"
                                 tgradio="{:url('user/change_status',['id'=>$vo['user_id']])}"></div>
                        </td>
                        <td>{$vo.login_time|time_before='前'}</td>
                        <td>
                            <auth rules="admin:film_user:cancel">
                            <a ajax="get" ajax-confirm="是否确认取消视频用户？" class="fc_red"
                               href="{:url('cancel',['user_id'=>$vo['user_id']])}">取消视频用户</a>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
</block>

<block name="layer">
    <include file="user/reg_pop"/>
    <include file="user/remark_pop"/>
    <include file="components/recommend_pop"/>
    <include file="user/role_pop"/>
    <include file="user/disable_pop"/>
    <include file="video/add_video_user"/>
</block>