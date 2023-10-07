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
            ]
        };
    </script>
    <script src="__JS__/loss/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <include file="components/tab_nav"/>

        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">

                    <div style="float: left">
                        <eq name=":input('audit_status')" value="0">
                            <auth rules="admin:loss:audit">
                                <div ajax="post" ajax-url="{:url('loss/audit',['audit_status'=>'1'])}"
                                     ajax-target="list_id"
                                     class="base_button base_button_s">批量清算
                                </div>
                            </auth>
                        </eq>
                    </div>

                    <div class="filter_search">
                        <input placeholder="{:config('app.agent_setting.agent_name')}ID" type="text" name="agent_id" value="{:input('agent_id')}"/>
                        <input placeholder="{:config('app.agent_setting.promoter_name')}ID" type="text" name="promoter_uid" value="{:input('promoter_uid')}"/>
                        <input placeholder="用户昵称、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.id}"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 10%;">清算{:APP_BEAN_NAME}
                    <switch name=":input('audit_status')">
                        <case value="0">
                            &nbsp;<span style="font-size: 12px;font-weight: normal;" class="fc_red">估算值</span>
                        </case>
                        <case value="1">
                            &nbsp;<span style="font-size: 12px;font-weight: normal;" class="fc_red">实际值</span>
                        </case>
                        <case value="2">
                            <span style="font-size: 12px;font-weight: normal;" class="fc_red">估算值</span>
                        </case>
                    </switch>
                </td>
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
                            <include file="loss/user_info"/>
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
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                <else/>
                                未分配
                            </notempty>
                        </td>
                        <td>
                            <include file="user/user_agent2"/>
                        </td>
                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            处理：{$vo.audit_time|time_format='未处理'}
                        </td>
                        <td>
                            <switch name="vo['audit_status']">
                                <case value="0">
                                    <neq name="vo['invalid']" value="1">
                                        <auth rules="admin:loss:audit">
                                            <a data-id="id:{$vo.id}" poplink="loss_audit_handler"
                                               href="javascript:;">审核</a>
                                        </auth>
                                        <br/>
                                        <a data-query="id={$vo.id}&type=audit_clear_agent" poplink="task_transfer_box"
                                           href="javascript:;">转交</a>
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
    <include file="loss/loss_audit_handler"/>
    <include file="components/task_transfer_box"/>
</block>