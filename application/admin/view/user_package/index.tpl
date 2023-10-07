<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '失效', value: '0'},
                        {name: '有效', value: '1'},
                        {name: '已使用', value: '2'}
                    ]
                },
                {
                    name: 'type',
                    title: '性质',
                    opts: [
                        {name: '小礼物', value: '1'},
                        {name: '大礼物', value: '0'}
                    ]
                }
            ]
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>
                <notempty name="get.user_id">
                    【{$user|user_name}】背包记录
                    <else/>
                    <h1>{$admin_last.name}</h1>
                </notempty>
            </h1>
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
                        <input placeholder="礼物ID、名称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="type" value="{:input('type')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">礼物信息</td>
                    <td style="width: 10%;">类型</td>
                    <td style="width: 10%;">数量</td>
                    <td style="width: 10%;">花费</td>
                    <td style="width: 10%;">获取方式</td>
                    <td style="width: 10%;">状态</td>
                    <td style="width: 10%;">可使用时间</td>
                    <td style="width: 10%;">过期时间</td>
                    <td style="width: 10%;">获取时间</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <div class="thumb">
                                    <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                        <img src="{:img_url($vo['icon'],'200_200','icon')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.name}
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td>{$vo.type ? '大礼物' : '小礼物'}</td>
                            <td>{$vo.num}</td>
                            <td>{$vo.user_cost}</td>
                            <td>
                                <switch name="vo['access_method']">
                                    <case value="liudanji">扭蛋机</case>
                                </switch>
                            </td>
                            <td>{$vo.status}</td>
                            <td>
                                <notempty name="vo.use_time">
                                    {$vo.use_time|time_format='','datetime'}
                                </notempty>
                            </td>
                            <td>
                                <notempty name="vo.expire_time">
                                    {$vo.expire_time|time_format='','datetime'}
                                </notempty>
                            </td>
                            <td>
                                {$vo.create_time|time_format='','datetime'}
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