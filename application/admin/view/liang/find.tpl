<extend name="public:base_iframe"/>
<block name="css">
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>

<block name="js">
    <script>
        var selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '启用', value: '1'},
                        {name: '禁用', value: '0'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/props/find.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left">
                        <auth rules="admin:props:add">
                            <a href="{:url('add')}?__JUMP__" class="base_button base_button_s">新增</a>
                        </auth>
                        <auth rules="admin:props:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 8%;">标题</td>
                <td style="width: 8%;">道具封面</td>
                <td style="width: 8%;">道具展示</td>
                <td style="width: 8%;">性质</td>
                <td style="width: 8%;">总销量</td>
                <td style="width: 15%;">描述</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">添加时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}" class="find_list_li">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.name}</td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['cover_icon'],'200_200','cover')}"/>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['user_icon'],'','cover')}"/>
                                </a>
                            </div>
                        </td>
                        <td>{$vo.type_str}</td>
                        <td>{$vo.sales}</td>
                        <td>{$vo.describe}</td>
                        <td>{$vo.sort}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:props:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status"
                                 tgradio="{:url('props/change_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            {$vo.create_time|time_format='无','date'}
                        </td>
                        <td>
                            <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                            <input class="find_params" type="hidden" name="name" value="{$vo.name}"/>
                            <a data-id="{$vo.id}" class="select_btn" href="javascript:;">选择</a>
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