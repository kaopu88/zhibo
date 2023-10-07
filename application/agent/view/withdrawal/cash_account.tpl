<extend name="public:base_nav"/>
<block name="css">

</block>

<block name="js">
    <script>
        var list = [
            {
                name: 'type',
                title: '类型',
                opts: [
                    {name: '支付宝', value: 'alipay'},
                ]
            },
            {
                name: 'verify_status',
                title: '状态',
                opts: [
                    {name: '未知', value: '0'},
                    {name: '有效', value: '1'},
                    {name: '无效', value: '2'}
                ]
            }
        ];
        var myConfig = {
            list: list
        };
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

            </div>
            <ul class="content_toolbar_btns">
                <li><a href="{:url('agent/withdrawal/cash_account_add')}?__JUMP__" class="base_button base_button_s">新增账户</a> </li>
                <li><a href="{:url('agent/withdrawal/cash_account_del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_red base_button_s">删除</a></li>
            </ul>
            <input type="hidden" name="type" value="{:input('type')}"/>
            <input type="hidden" name="verify_status" value="{:input('verify_status')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 10%;">姓名</td>
                <td style="width: 10%;">类型</td>
                <td style="width: 10%;">卡名</td>
                <td style="width: 10%;">账号</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">是否默认</td>
                <td style="width: 10%;">绑定时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.name}</td>
                        <td>{$vo.type_str}</td>
                        <td>{$vo.card_name}</td>
                        <td>{$vo.account}</td>
                        <td>{$vo.verify_status_str}</td>
                        <td>
                            <if condition="$vo['is_default'] == '0'">
                                否
                                <else/>
                                    是
                            </if>
                        </td>
                        <td>
                            {$vo.create_time|time_format}
                        </td>

                        <td >
                            <a href="{:url('agent/withdrawal/cash_account_edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('agent/withdrawal/cash_account_del',array('id'=>$vo['id']))}">删除</a>
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
        new SearchList('.filter_box', myConfig);
    </script>

</block>
<block name="layer">
</block>