
<extend name="public:base_iframe"/>
<block name="css">
    <style>
        .select_btn {
            color: #555;
        }

        .selected, .selected:visited, .selected:hover, .selected:active {
            color: #eb6100;
        }
    </style>
</block>

<block name="js">
    <script>
        var liveFilmItemUrl = '{:url("gift/detail")}',
            selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script>
        $(function () {
            new SearchList('.filter_box', myConfig);
            new FindListController('body', {
                primary_key: 'id',
                name_key: 'name',
                selected: selectedListJson,
                item_url: liveFilmItemUrl
            });
        });

        var myConfig = {
            action: '{:url("gift/find")}',//提交地址
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'cid',
                    title: '使用场景',
                    opts: [
                        {name: '直播礼物', value: '0'},
                        {name: '视频礼物', value: '1'},
                        {name: '道具礼物', value: '1'},
                    ]
                },
                {
                    name: 'type',
                    title: '类型',
                    opts: [
                        {name: '连送', value: '1'},
                        {name: '单送', value: '0'}
                    ]
                }
            ]
        };
    </script>
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
                    <div style="float: left;line-height: 30px;font-size: 12px;">
                        <a class="show_selected" href="javascript:;">已选中礼物(<span class="selected_num">0</span>)</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="礼物ID" type="text" name="id" value="{:input('id')}"/>
                        <input placeholder="礼物名称、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="source" value="{:input('source')}"/>
            <input type="hidden" name="is_local" value="{:input('is_local')}"/>
        </div>
        <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 18%;">礼物名称</td>
                <td style="width: 15%;">礼物价值</td>
                <td style="width: 10%;">类型</td>
                <td style="width: 15%;">应用场景</td>
                <td style="width: 10%;">启用状态</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr class="find_list_li" data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <div style="margin-right: 8px; float: left;"><img style="vertical-align: middle;" src="{$vo['picture_url']}?imageView2/1/w/50/h/50" alt=""></div>
                            <div style="float: left;">
                                礼物名称：<a href="javascript:;">{$vo['name']|short='20'}</a>
                            </div>
                        </td>
                        <td>
                            {:config('app.product_info.bean_name')}：{$vo.price}<br/>
                            优惠：{$vo.discount_str}<br/>
                        </td>
                        <td>
                            {$vo.type_str}
                        </td>
                        <td>{$vo.cid_str}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:gift:update')?'0':'1'}"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.create_time|time_format='无','date'}</td>
                        <td>
                            <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                            <input class="find_params" type="hidden" name="name" value="{$vo.name}"/>
                            <input class="find_params" type="hidden" name="images" value="{$vo.picture_url}"/>
                            <input class="find_params" type="hidden" name="price" value="{$vo.price}"/>

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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>



</block>

<block name="layer">
    <div class="selected_box" style="padding: 10px;display: none;">
        <table class="table" style="width: 100%;box-sizing: border-box;">
            <thead>
            <tr>
                <td>礼物ID</td>
                <td>名称</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</block>