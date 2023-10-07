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
                        <input name="status" type="hidden" class="modal_select_value finder"
                               value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="0">待审核</li>
                            <li class="modal_select_option" value="1">已通过</li>
                            <li class="modal_select_option" value="2">未通过</li>
                        </ul>
                    </div>
                    <input placeholder="用户ID" name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder=""/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
         <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">申请人</td>
                <td style="width: 10%;">粉丝数</td>
                <td style="width: 10%;">视频数</td>
                <td style="width: 10%;">被举报数</td>
                <td style="width: 10%;">实名认证</td>
                <td style="width: 20%;">处理描述</td>
                <td style="width: 20%;">审核状态</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            {$vo.fans_num}
                        </td>
                        <td>
                            {$vo.film_num}
                        </td>
                        <td>
                            {$vo.reported_num}
                        </td>
                        <td>
                            {$vo.verified ? '是' : '否'}
                        </td>
                        <td>
                            <switch name="vo['status']">
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
                                未分配
                            </notempty>
                            <if condition="$vo.status != '0'">
                                <br/>处理详情：{$vo.handle_desc}
                            </if>
                        </td>
                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            处理：<if condition="$vo.handle_time != '0'">{$vo.handle_time|time_format='未处理'}<else/>未处理</if>
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