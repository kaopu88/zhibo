<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
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
                        {name: '虚拟用户', value: 'isvirtual'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/promoter/clients.js?v=__RV__"></script>
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
                    <a style="display: inline-block" class="ml_5" ajax-reload="0" ajax="get"
                       ajax-confirm="系统将尝试校正客户数量？此过程可能需要花费一点时间"
                       href="{:url('promoter/correct_client_num',['user_id'=>input('promoter_uid')])}">客户数量不对？</a>
                    <div class="filter_search">
                        <input placeholder="用户ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="vip_status" value="{:input('vip_status')}"/>
            <input type="hidden" name="user_type" value="{:input('user_type')}"/>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
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
                            <include link="{:url('user/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                        </td>
                        <td>
                            <include file="user/user_type"/>
                        </td>
                        <td>
                            <include file="user/user_vip_status"/>
                        </td>
                        <td>
                            <include file="user/user_agent2"/>
                        </td>
                        <td>
                            <include file="user/user_bean"/>
                        </td>
                        <td>
                            <include file="user/user_fun"/>
                        </td>
                        <td>
                            <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status"
                                 tgradio="{:url('user/change_status',['id'=>$vo['user_id']])}"></div>
                        </td>
                        <td>{$vo.login_time|time_before='前'}</td>
                        <td>
                            <a href="{:url('user/detail',['user_id'=>$vo['user_id']])}">用户详情</a><br/>
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
    <include file="user/remark_pop"/>
</block>