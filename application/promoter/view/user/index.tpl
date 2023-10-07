<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var countdown=parseInt('{$countdown}');
        var regUrl='{:url("user/reg")}';
        var list = [
            {
                name: 'status',
                title: '启用状态',
                opts: [
                    {name: '禁用', value: '0'},
                    {name: '启用', value: '1'}
                ]
            },
            {
                name: 'vip_status',
                title: '会员状态',
                opts: [
                    {name: '未开通', value: '0'},
                    {name: '服务中', value: '1'},
                    {name: '已过期', value: '2'}
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
                    {name: '协议用户', value: 'isvirtual'},
                ]
            },
            {
                name: 'level',
                title: '用户等级',
                get: '{:url("common/get_levels")}'
            },
            {
                name: 'province',
                title: '所在省份',
                data: {country: 0},
                auto_sub: false,
                get: '{:url("common/get_area")}'
            },
            {
                name: 'city',
                parent: 'province',
                title: '所在城市',
                get: '{:url("common/get_area")}'
            },
            {
                name: 'district',
                parent: 'city',
                title: '所在区县',
                get: '{:url("common/get_area")}'
            }
        ];
        var myConfig = {
            list: list
        };
    </script>
    <script src="__JS__/user/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$promoter_last.name}</h1>
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
                    <div class="filter_search">
                        <input placeholder="用户ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="vip_status" value="{:input('vip_status')}"/>
            <input type="hidden" name="user_type" value="{:input('user_type')}"/>
            <input type="hidden" name="level" value="{:input('level')}"/>
            <input type="hidden" name="province" value="{:input('province')}"/>
            <input type="hidden" name="city" value="{:input('city')}"/>
            <input type="hidden" name="district" value="{:input('district')}"/>
            <input type="hidden" name="agent_id" value="{:input('agent_id')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                    <td style="width: 7%;">ID</td>
                    <td style="width: 15%;">用户信息</td>
                    <td style="width: 9%;">用户类型</td>
                    <td style="width: 10%;">用户属性</td>
                    <td style="width: 13%;">{:config('app.agent_setting.promoter_name')}信息</td>
                    <td style="width: 8%;">{:config('app.product_info.bean_name')}</td>
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
                                <include link="{:url('user/detail',['user_id'=>$vo.user_id])}" file="user/user_info" />
                            </td>
                            <td>
                                <include file="user/user_type" />
                            </td>
                            <td>
                                <include file="user/user_vip_status" />
                            </td>
                            <td>
                                <if condition="$vo.promoter_uid eq 0">
                                    直属
                                    <else />
                                    [{$vo.promoter_info.user_id}]{$vo.promoter_info|user_name}
                                </if>
                            </td>
                            <td>
                                <include file="user/user_bean" />
                            </td>
                            <td>
                                <include file="user/user_fun"/>
                            </td>
                            <td>
                                <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status"
                                    tgradio="{:url('user/change_status',['id'=>$vo['user_id']])}"></div>
                            </td>
                            <td>{$vo.login_time|time_before='前'}</td>
                            <td>
                                <a href="{:url('user/detail',['user_id'=>$vo['user_id']])}">用户详情</a>
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
    <include file="user/import_pop"/>
    <include file="user/role_pop"/>
</block>