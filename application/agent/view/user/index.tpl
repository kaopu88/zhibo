<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var countdown=parseInt('{$countdown}');
        var regUrl='{:url("user/reg")}';
        var list = [];
        var add_sec = '{$agent.add_sec}';
        if (add_sec=='1'){
            var item = {
                name: 'agent_id',
                title: "所属{:config('app.agent_setting.agent_name')}",
                get: '{:url("kpi_cons/get_agent")}'
            };
            list.push(item);
        }
        var myConfig = {
            list: list
        };
    </script>
    <script src="__JS__/user/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}【<span>显示我的用户以及二级{:config('app.agent_setting.agent_name')}的用户</span>】</h1>
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
            <input type="hidden" name="agent_id" value="{:input('agent_id')}"/>
        </div>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                    <td style="width: 7%;">ID</td>
                    <td style="width: 15%;">用户信息</td>
                    <td style="width: 13%;">{:config('app.agent_setting.agent_name')}信息</td>
                    <td style="width: 13%;">{:config('app.agent_setting.promoter_name')}信息</td>
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
                                {$vo.agent_info.name}
                            </td>
                            <td>
                                <if condition="$vo.promoter_uid eq 0">
                                    直属
                                    <else />
                                    [{$vo.promoter_info.user_id}]{$vo.promoter_info|user_name}
                                </if>
                            </td>
                            <td>
                                <div tgradio-not="1" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status"
                                    tgradio="{:url('user/change_status',['id'=>$vo['user_id']])}"></div>
                            </td>
                            <td>{$vo.login_time|time_before='前'}</td>
                            <td>
                                <a href="{:url('user/detail',['user_id'=>$vo['user_id']])}">用户详情</a><br/>
                                <a class="transfer_btn" data-transfer="user" data-id="{$vo.user_id}" data-transferway="agent"
                                href="javascript:;">分配{:config('app.agent_setting.agent_name')}</a><br/>
                                <eq name="vo['promoter_current']" value="1">
                                    <a ajax="get" ajax-confirm="是否确认取消{:config('app.agent_setting.promoter_name')}？" class="fc_red" href="{:url('promoter/cancel',['user_id'=>$vo.user_id])}">取消{:config('app.agent_setting.promoter_name')}</a><br/>
                                    <else/>
                                    <a ajax="get" href="{:url('promoter/create',['user_id'=>$vo.user_id,'agent_id'=>$vo.agent_id])}">设为{:config('app.agent_setting.promoter_name')}</a><br/>
                                </eq>
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