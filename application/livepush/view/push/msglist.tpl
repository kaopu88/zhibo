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

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索用户id、用户姓名"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 10%;">ID</td>
                <td style="width: 10%;">管理员</td>
                <td style="width: 10%;">目标房间</td>
                <td style="width: 10%;">目标uid</td>
                <td style="width: 40%;">内容</td>
                <td style="width: 10%;">IP</td>
                <td style="width: 20%;">时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>
                            {$vo.id}
                        </td>

                        <td>
                            {$vo.manager}
                        </td>

                        <td>
                            {$vo.push_object}
                        </td>

                        <td>
                            <if condition="$vo.user_id==0">
                                全部房间
                            </if>

                            <if condition="$vo.user_id gt 0">
                                {$vo.user_id}
                            </if>
                        </td>

                        <td>
                            {$vo.content}
                        </td>

                        <td>
                            {$vo.ip}
                        </td>


                        <td>
                            {$vo.create_time|time_format}
                        </td>
                       <!-- <td>
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('category_delete',array('id'=>$vo['id']))}?__JUMP__">删除</a>
                        </td>-->
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
        new FinderController('.finder', '');
    </script>

</block>