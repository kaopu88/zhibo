<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '用户状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/promoter/index.js?v=__RV__"></script>
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
                    <div class="filter_search">
                        <input placeholder="{:config('app.agent_setting.promoter_name')}ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 10%;">{:APP_BEAN_NAME}</td>
                <td style="width: 10%;">累计客消</td>
                <td style="width: 10%;">累计拉新</td>
                <td style="width: 8%;">用户状态</td>
                <td style="width: 10%;">客户数量</td>
                <td style="width: 10%;">所属{:config('app.agent_setting.agent_name')}</td>
                <td style="width: 8%;">加入时间</td>
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
                            <include file="promoter/user_info"/>
                        </td>
                        <td>
                        </td>
                        <td>{$vo.total_cons}</td>
                        <td>{$vo.total_fans}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:user:change_status')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status"
                                 tgradio="{:url('user/change_status',['id'=>$vo['user_id']])}"></div>
                        </td>
                        <td>
                            {$vo.client_num}&nbsp;<a ajax="post" title="校正客户数量"
                                                     href="{:url('correct_client_num',['user_id'=>$vo['user_id']])}"><span
                                class="icon-reload"></span></a>
                        </td>
                        <td>
                            <include file="user/user_agent2"/>
                        </td>
                        <td>{$vo.create_time|time_format='','date'}</td>
                        <td>
                            <a href="{:url('promoter/detail',['user_id'=>$vo['user_id']])}">{:config('app.agent_setting.promoter_name')}详情</a><br/>
                            <auth rules="admin:user:assign_agent">
                                <a data-transfer="user" class="transfer_btn" data-rel="promoter"
                                   data-id="{$vo.user_id}" href="javascript:;">移交客户</a><br/>
                            </auth>
                            <auth rules="admin:promoter:cancel">
                                <a ajax="get" ajax-confirm="是否确认取消{:config('app.agent_setting.promoter_name')}？" class="fc_red"
                                   href="{:url('cancel',['user_id'=>$vo['user_id']])}">取消{:config('app.agent_setting.promoter_name')}</a>
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
    <script>
    </script>
</block>

<block name="layer">
</block>