<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20 p-0">

        <include file="components/tab_nav"/>
        <div class="bg_container">
            <div class="content_toolbar mt_10">
                <eq name=":input('audit_status')" value="0">
                <auth rules="admin:user_data_deal:check">
                    <div ajax="post" ajax-url="{:url('UserDataDeal/batch_deal',['audit_status'=>'1'])}"
                        ajax-target="list_id"
                        class="base_button base_button_s">批量通过
                    </div>
                </auth>
                </eq>
                <div class="content_toolbar_search">
                    <div class="base_group">
                        <div class="modal_select modal_select_s">
                            <span class="modal_select_text"></span>
                            <input name="audit_status" type="hidden" class="modal_select_value finder"
                                value="{:input('audit_status')}"/>
                            <ul class="modal_select_list">
                                <li class="modal_select_option" value="">全部</li>
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
                <table class="content_list mt_10 sm_width">
                    <thead>
                    <tr>
                        <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                        <td style="width: 5%;">ID</td>
                        <td style="width: 15%;">用户</td>
                        <td style="width: 20%;">头像</td>
                        <td style="width: 15%;">封面</td>
                        <td style="width: 25%;">审核描述</td>
                        <td style="width: 15%;">操作</td>
                    </tr>
                    </thead>
                    <tbody>
                    <notempty name="_list">
                        <volist name="_list" id="vo">
                            <tr data-id="{$vo.id}">
                                <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
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
                                    <if condition="$audit_status === ''">
                                    <switch name="vo['audit_status']">
                                        <case value="0">
                                            待审核
                                        </case>
                                        <case value="1"><a href="javascript:;" class="fc_green">已通过</a>
                                        </case>
                                        <case value="2">
                                            <a href="javascript:;" class="fc_red">未通过</a>
                                        </case>
                                    </switch><br/>
                                    </if>
                                    申请：{$vo.create_time|time_format}<br/>
                                    处理：<if condition="$vo.handle_time != '0'">{$vo.handle_time|time_format='未处理'}<else/>未处理</if>
                                    <if condition="$vo.audit_status != '0'">
                                        <br/>详情：{$vo.handle_desc}
                                    </if>
                                </td>
                                <td>
                                    <switch name="vo['audit_status']">
                                        <case value="0">
                                            <auth rules="admin:user_data_deal:check">
                                                <a data-id="id:{$vo.id}" poplink="userdata_handler"
                                                href="javascript:;">处理</a><br/>
                                            </auth>
                                            <a data-query="id={$vo.id}&type=viewback" poplink="task_transfer_box"
                                            href="javascript:;">转交</a>
                                        </case>
                                        <case value="1">
                                        </case>
                                        <case value="2">
                                            原因：{$vo.audit_remark}
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
            </div>
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="user_data_deal/user_data_handler"/>
    <include file="components/task_transfer_box"/>
</block>