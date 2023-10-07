<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'sub_channel',
                    title: '包含子频道',
                    opts: [
                        {name: '是', value: '1'},
                        {name: '否', value: '0'}
                    ]
                },
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                }
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
                <div class="filter_order">
                    <div style="float: left">
                        <auth rules="admin:live_channel:add">
                            <a class="base_button base_button_s" href="{:url('live_channel/add')}?__JUMP__">新增频道</a>
                        </auth>
                        <auth rules="admin:live_channel:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="父频道ID" type="text" name="parent_id" value="{:input('parent_id')}"/>
                        <input placeholder="ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="sub_channel" value="{:input('sub_channel')}"/>
        </div>
        <div class="table_slide">
         <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">标题</td>
                <td style="width: 10%;">父频道标题</td>
                <td style="width: 10%;">是否有子频道</td>
                <td style="width: 15%;">频道描述</td>
                <td style="width: 10%;">排序</td>
                <td style="width: 10%;">启用状态</td>
                <td style="width: 15%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['icon'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['icon'],'200_200','icon')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.name}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.parent_str}</td>
                        <td>{$vo.sub_channel ? '是' : '否'}</td>
                        <td>{$vo.description}</td>
                        <td>{$vo.sort_order}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:live_channel:update')?'0':'1'}"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <auth rules="admin:live_channel:update">
                                <a href="{:url('edit',['id'=>$vo.id])}?__JUMP__">编辑频道</a><br/>
                            </auth>
                            <auth rules="admin:live_channel:delete">
                                <a class="fc_red" ajax-confirm ajax="get"
                                   href="{:url('delete',array('id'=>$vo['id']))}?__JUMP__">删除频道</a>
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
</block>

<block name="layer">
</block>
