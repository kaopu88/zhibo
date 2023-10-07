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
                        {name: '启用', value: '1'},
                        {name: '禁用', value: '0'},
                        {name: '已出售', value: '2'}
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
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 8%;">靓号</td>
                <td style="width: 8%;">所需钻石</td>
                <td style="width: 8%;">所需积分</td>
                <td style="width: 8%;">位数</td>
                <td style="width: 8%;">购买人ID</td>
                <td style="width: 15%;">购买时间</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">发布时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.name}</td>
                        <td>{$vo.coin}</td>
                        <td>{$vo.score}</td>
                        <td>{$vo.length}</td>
                        <td>{$vo.uid}</td>
                        <td>
                            {$vo.buytime|time_format='无','date'}
                        </td>
                        <td>{$vo.list_order}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:liang:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status"
                                 tgradio="{:url('liang/change_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            {$vo.addtime|time_format='无','date'}
                        </td>
                        <td>
                            <auth rules="admin:liang:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>
                            </auth>
                            <auth rules="admin:liang:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('delete',array('id'=>$vo['id']))}?__JUMP__">删除</a>
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

    <script>
        $(function () {
            new SearchList('.filter_box',myConfig);
        });
    </script>

</block>