<extend name="public:base_iframe"/>
<block name="css">
    <style>
        .select_btn {
            color: #555;
        }

        .selected, .selected:visited, .selected:hover, .selected:active {
            color: #eb6100;
        }
    </style>
</block>

<block name="js">
    <script>
        var userItemUrl = '{:url("user/detail")}', selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/user/find.js?v=__RV__"></script>
    <script>
        var myConfig = {
            action: '{:url("user/find")}',//提交地址
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
                        {name: '机器人', value: 'robot'}
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
            ]
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left;line-height: 30px;font-size: 12px;">
                        <a class="show_selected" href="javascript:;">已选中用户(<span class="selected_num">0</span>)</a>
                    </div>
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
        </div>
        <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 6%;">ID</td>
                <td style="width: 15%;">用户</td>
                <td style="width: 10%;">类型</td>
                <td style="width: 12%;">属性</td>
                <td style="width: 12%;">{:config('app.agent_setting.promoter_name')}信息</td>
                <td style="width: 10%;">{:config('app.product_info.bean_name')}</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">注册时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr class="find_list_li" data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <include link="{:url('user/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                        </td>
                        <td>
                            <include file="user/user_type"/>
                        </td>
                        <td>
                            <include file="user/user_vip_status"/>
                        </td>
                        <td>
                            <include file="public/vo_agent"/>
                        </td>
                        <td>
                            <include file="user/user_bean"/>
                        </td>
                        <td>
                            <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status" tgradio=""></div>
                        </td>
                        <td>{$vo.login_time|time_before='前'}</td>
                        <td>
                            <input class="find_params" type="hidden" name="user_id" value="{$vo.user_id}"/>
                            <input class="find_params" type="hidden" name="avatar" value="{$vo.avatar}"/>
                            <input class="find_params" type="hidden" name="nickname" value="{$vo.nickname}"/>
                            <input class="find_params" type="hidden" name="phone" value="{$vo.phone}"/>
                            <input class="find_params" type="hidden" name="real_name" value="{$vo.real_name}"/>
                            <input class="find_params" type="hidden" name="username" value="{$vo.username}"/>
                            <a data-id="{$vo.user_id}" class="select_btn" href="javascript:;">选择</a>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
</block>

<block name="layer">
    <div class="selected_box" style="padding: 10px;display: none;">
        <table class="table" style="width: 100%;box-sizing: border-box;">
            <thead>
            <tr>
                <td>用户ID</td>
                <td>昵称</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</block>