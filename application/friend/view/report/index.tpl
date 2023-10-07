<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'audit_status',
                    title: '状态',
                    opts: [
                        {name: '待审核', value: '0'},
                        {name: '已通过', value: '1'},
                        {name: '未通过', value: '2'}
                    ]
                },
                {
                    name: 'type',
                    title: '举报分类',
                    opts: {:htmlspecialchars_decode($_erarry)}
                }
            ]

        };
    </script>
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
                        <input placeholder="举报id" type="text" name="report_msg_id" value="{:input('report_msg_id')}"/>
                        <input placeholder="举报内容" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="room_model" value="{:input('room_model')}"/>
            <input type="hidden" name="type" value="{:input('type')}"/>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">举报Id</td>
                <td style="width: 10%;">用户ID</td>
                <td style="width: 15%;">举报分类</td>
                <td style="width: 15%;">举报对象</td>
                <td style="width: 10%;">发布时间</td>
                <td style="width: 20%;">处理描述</td>
                <td style="width: 7%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.report_msg_id}</td>
                        <td>
                            <include file="public/vo_user"/>
                        </td>
                        <td>{$vo.classfiy}</td>
                        <td>{$vo.report_type}</td>
                        <td>
                            发布时间：{$vo.ctime|time_format='暂无','datetime'}<br/>
                        </td>
                        <td>
                            <switch name="vo['audit_status']">
                                <case value="0">
                                    待审核
                                </case>
                                <case value="1"><a href="javascript:;" class="fc_green">已通过</a>
                                </case>
                                <case value="2">
                                    <a href="javascript:;" class="fc_red">未通过</a>
                                </case>
                            </switch>
                            <br/><notempty name="vo['audit_status']">
                                 管理员
                                <else/>
                                未分配
                            </notempty>
                            <if condition="$vo.audit_status != '0'">
                                <br/>处理详情：{$vo.handle_desc}
                            </if>
                        </td>
                        <td>
                            <auth rules="admin:complaint:check">
                                <a data-id="id:{$vo.id}" poplink="complaint_handler"
                                   href="javascript:;" class="repair_font">处理</a><br/>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
    </script>

</block>
<block name="layer">
    <include file="report/complaint_handler"/>
</block>