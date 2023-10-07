<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'account_type',
                    title: '类型',
                    opts: [
                        {name: '支付宝', value: '0'},
                        {name: '微信', value: '1'},
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
                        <input placeholder="用户ID、姓名、卡名" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="type" value="{$get.type}"/>
            <input type="hidden" name="verify_status" value="{$get.verify_status}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">用户信息</td>
                <td style="width: 10%;">姓名</td>
                <td style="width: 10%;">类型</td>
                <td style="width: 10%;">卡名</td>
                <td style="width: 10%;">账号</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">绑定时间</td>
                <td style="width: 10%;">解绑时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <include file="recharge_app/user_info"/>
                        </td>
                        <td>{$vo.name}</td>
                        <td>{$vo.type_str}</td>
                        <td>{$vo.card_name}</td>
                        <td>{$vo.account}</td>
                        <td>{$vo.verify_status_str}</td>
                        <td>
                            {$vo.create_time|time_format}
                        </td>
                        <td>
                            {$vo.delete_time|time_format}
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
        $(function () {
            new SearchList('.filter_box',myConfig);
        });
    </script>

</block>