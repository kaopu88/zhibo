<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            action:'{:url("admin/red_packet/index")}',
            list: [
                {
                    name: 'red_type',
                    title: '红包类型',
                    opts: [
                        {name: '拼手气', value: '0'},
                        {name: '平分包', value: '1'}
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

            </div>
            <input type="hidden" name="red_type" value="{:input('red_type')}"/>
        </div>


        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 3%;">ID</td>
                    <td style="width: 10%;">发送用户</td>
                    <td style="width: 10%;">红包标题</td>
                    <td style="width: 10%;">红包金额</td>
                    <td style="width: 10%;">红包数量</td>
                    <td style="width: 10%;">红包类型</td>
                    <td style="width: 10%;">发送方式</td>
                    <td style="width: 10%;">开始时间</td>
                    <td style="width: 10%;">结束时间</td>
                    <td style="width: 10%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>
                                {$vo.id}
                            </td>
                            <td> <include file="anchor/user_info"/></td>
                            <td>
                                {$vo.title}
                            </td>

                            <td>
                                {$vo.price}
                            </td>

                            <td>
                                {$vo.num}
                            </td>

                            <td>
                                <if condition="$vo.red_type eq 0">
                                  拼手气
                                <elseif condition="$vo.red_type eq 1">
                                    平分包
                                <elseif condition="$vo.red_type eq 2">
                                    口令红包
                                </if>
                            </td>

                            <td>
                                <if condition="$vo.send_mode eq 1">
                                    立即
                                <else>
                                    延时
                                </if>
                            </td>

                            <td>
                                {$vo.create_time|time_format}
                            </td>

                            <td>
                                {$vo.end_time|time_format}
                            </td>

                            <td>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
                            </td>
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