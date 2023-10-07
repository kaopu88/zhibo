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
                    name: 'props_id',
                    title: '道具',
                    get: '{:url("props/get_list")}'
                },
                {
                    name: 'use_status',
                    title: '使用中',
                    opts: [
                        {name: '否', value: '0'},
                        {name: '是', value: '1'}
                    ]
                },
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '失效', value: '0'},
                        {name: '有效', value: '1'},
                        {name: '已使用', value: '2'}
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
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="props_id" value="{:input('props_id')}"/>
            <input type="hidden" name="use_status" value="{:input('use_status')}"/>
            <input type="hidden" name="status" value="{:input('status')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 15%;">道具</td>
                <td style="width: 10%;">物品数量</td>
                <td style="width: 10%;">使用中</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">过期时间</td>
                <td style="width: 10%;">更新时间</td>
                <td style="width: 10%;">获取时间</td>
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
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['icon'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['icon'],'200_200','cover')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.name}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.num}</td>
                        <td>{$vo.use_status_str}</td>
                        <td>{$vo.status_str}</td>
                        <td>
                            <notempty name="vo.expire_time">
                            {$vo.expire_time|time_format}
                            </notempty>
                        </td>
                        <td>
                            <notempty name="vo.update_time">
                            {$vo.update_time|time_format}
                            </notempty>
                        </td>
                        <td>
                            {$vo.create_time|time_format}
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