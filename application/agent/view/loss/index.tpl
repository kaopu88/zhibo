<extend name="public:base_nav"/>
<block name="css">
    <style>
        .invalid {
            opacity: 0.6;
        }
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'audit_status',
                    title: '审核状态',
                    opts: [
                        {name: '待审核', value: '0'},
                        {name: '已结算', value: '1'},
                        {name: '已驳回', value: '2'}
                    ]
                },
            ]
        };
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
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
                        <input placeholder="{:config('app.agent_setting.promoter_name')}ID" type="text" name="promoter_uid" value="{:input('promoter_uid')}"/>
                        <input placeholder="用户昵称、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="audit_status" value="{:input('audit_status')}"/>
        </div>

        <p style="font-size: 12px;color: #fe0000;margin-top: 10px;">
            客户超过{$loss_after_months}个月未产生任何消费将会被系统标记为“可能已流失客户”，此客户剩余{:APP_BEAN_NAME}(大于等于1000)将结算给所属的{:config('app.agent_setting.agent_name')}和{:config('app.agent_setting.promoter_name')}，检测客户消费由系统每日自动进行，不支持指定时间和人工干预，在审核期间客户有可能再次消费，故结算{:APP_BEAN_NAME}以实际剩余{:APP_BEAN_NAME}为准。
        </p>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 10%;">清算{:APP_BEAN_NAME}</td>
                <td style="width: 10%;">最后消费时间</td>
                <td style="width: 10%;">审核状态</td>
                <td style="width: 10%;">审核人</td>
                <td style="width: 10%;">所属{:config('app.agent_setting.agent_name')}</td>
                <td style="width: 10%;">相关时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr class="{$vo.invalid=='1'?'invalid':''}" data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.id}
                        </td>
                        <td>
                            <include link="{:url('anchor/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                        </td>
                        <td>{$vo.bean}</td>
                        <td>
                            {$vo.bean_info.last_pay_time|time_format}<br/>
                            {$vo.bean_info.last_pay_time|time_before='前'}
                        </td>
                        <td>
                            <include file="loss/vo_audit_status"/>
                        </td>
                        <td>
                            <notempty name="vo['audit_admin']">
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">客服【{$vo.audit_admin.id}】</a>
                                <else/>
                                未分配
                            </notempty>
                        </td>
                        <td>
                            <include file="public/vo_agent"/>
                        </td>
                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            处理：{$vo.audit_time|time_format='未处理'}
                        </td>
                        <td>
                            <switch name="vo['audit_status']">
                                <case value="0">
                                    <neq name="vo['invalid']" value="1">
                                        <else/>
                                        <span class="fc_red">已失效</span>
                                    </neq>
                                </case>
                                <case value="1">
                                </case>
                                <case value="2">
                                    原因：{$vo.reason}
                                </case>
                            </switch>
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
    <script>
    </script>
</block>

<block name="layer">
</block>