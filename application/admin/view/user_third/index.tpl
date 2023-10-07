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
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '已绑定', value: 'bind'},
                        {name: '已取消', value: 'unbind'}
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
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{$get.status}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">用户信息</td>
                <td style="width: 10%;">标识符</td>
                <td style="width: 10%;">APPID</td>
                <td style="width: 10%;">openid</td>
                <td style="width: 10%;">全局ID</td>
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
                        <td>{$vo.type_str} <if condition="$vo.is_from eq 1">(网页授权)</if></td>
                        <td>{$vo.appid}</td>
                        <td>{$vo.openid}</td>
                        <td>{$vo.uuid}</td>
                        <td>
                            <switch name="vo['status']">
                                <case value="bind">
                                    <span class="fc_green">
                                </case>
                                <case value="unbind">
                                    <span class="fc_red">
                                </case>
                            </switch>
                            {$vo.status_str}
                            </span>
                        </td>
                        <td>
                            {$vo.bind_time|time_format}
                        </td>
                        <td>
                            {$vo.unbind_time|time_format}
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