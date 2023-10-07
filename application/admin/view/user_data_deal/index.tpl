<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="audit_status" type="hidden" class="modal_select_value finder"
                               value="{:input('audit_status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="-1">处理中</li>
                            <li class="modal_select_option" value="0">待审核</li>
                            <li class="modal_select_option" value="1">已通过</li>
                            <li class="modal_select_option" value="2">未通过</li>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="用户ID"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">用户</td>
                <td style="width: 15%;">头像</td>
                <td style="width: 15%;">封面</td>
                <td style="width: 25%;">处理描述</td>
                <td style="width: 25%;">审核状态</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <notempty name="vo['data']['avatar']">
                                <img src="{$vo.data.avatar}" width="150" height="150"/>
                            </notempty>
                        </td>
                        <td>
                            <notempty name="vo['data']['cover']">
                                <img src="{$vo.data.cover}" height="150"/>
                            </notempty>
                        </td>
                        <td>
                            <switch name="vo['audit_status']">
                                <case value="-1">
                                    系统审核中
                                <div ajax="post" ajax-url="{:url('UserDataDeal/submit_mq',['id'=>$vo['id']])}"
                                     class="base_button base_button_s">重试
                                </div>
                                </case>
                                <case value="0">
                                    待审核
                                </case>
                                <case value="1"><a href="javascript:;" class="fc_green">已通过</a>
                                </case>
                                <case value="2">
                                    <a href="javascript:;" class="fc_red">未通过</a>
                                </case>
                            </switch>
                            <br/><notempty name="vo['audit_admin']">
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                <else/>
                                系统处理
                            </notempty>
                        </td>
                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            处理：<if condition="$vo.handle_time != '0'">{$vo.handle_time|time_format='未处理'}<else/>未处理</if>
                            <if condition="$vo.audit_status != '0'">
                            <if condition="$vo.audit_status != '-1'">
                                <br/>详情：{$vo.handle_desc}
                            </if>
                            </if>
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
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="recharge_app/recharge_app_handler"/>
    <include file="components/task_transfer_box"/>
</block>