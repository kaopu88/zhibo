<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'},
                    ]
                },
                {
                    name: 'hot',
                    title: '热门',
                    opts: [
                        {name: '普通', value: '0'},
                        {name: '热门', value: '1'},
                    ]
                },
                {
                    name: 'new',
                    title: '最新',
                    opts: [
                        {name: '普通', value: '0'},
                        {name: '最新', value: '1'},
                    ]
                },
                {
                    name: 'pcat_id',
                    title: '一级分类',
                    data: {type: 'resources_types'},
                    auto_sub: false,
                    get: '{:url("common/get_category")}'
                },
                {
                    name: 'cat_id',
                    parent: 'pcat_id',
                    title: '二级分类',
                    get: '{:url("common/get_category")}'
                }
            ]
        };
    </script>
    <script src="__JS__/resources/index.js?v=__RV__"></script>
    <script src="__VENDOR__/raty/jquery.raty.min.js?v=__RV__"></script>
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
                    <ul class="content_toolbar_btns">
                        <auth rules="admin:resources:update">
                            <li><a href="{:url('add')}?__JUMP__"
                                   class="base_button base_button_s">新增</a></li>
                        </auth>
                        <auth rules="admin:resources:delete">
                            <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm
                                   class="base_button base_button_s base_button_red">删除</a></li>
                        </auth>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="ID、标题、资源名称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{$get.status}"/>
            <input type="hidden" name="pcat_id" value="{$get.pcat_id}"/>
            <input type="hidden" name="cat_id" value="{$get.cat_id}"/>
            <input type="hidden" name="hot" value="{$get.hot}"/>
            <input type="hidden" name="new" value="{$get.new}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 3%"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%">标题</td>
                <td style="width: 7%">资源名称</td>
                <td style="width: 8%">类别</td>
                <td style="width: 15%">资源包</td>
                <td style="width: 8%">资源包大小</td>
                <td style="width: 5%">热门</td>
                <td style="width: 5%">最新</td>
                <td style="width: 5%">排序权重</td>
                <td style="width: 5%">启用状态</td>
                <td style="width: 10%">添加时间</td>
                <td style="width: 10%">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr>
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;" class="thumb_img">
                                    <img src="{:img_url($vo['image'],'200_200','thumb')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.title}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.name}</td>
                        <td>{$vo.cat_name}</td>
                        <td>{$vo.file_url}</td>
                        <td>{$vo.file_size}</td>
                        <td><div tgradio-not="{:check_auth('admin:resources:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.hot}"
                                 tgradio-on-name="热门" tgradio-off-name="普通"
                                 tgradio-name="hot"
                                 tgradio="{:url('resources/change_hot_status',['id'=>$vo['id']])}"></div></td>
                        <td><div tgradio-not="{:check_auth('admin:resources:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.new}"
                                 tgradio-on-name="最新" tgradio-off-name="普通"
                                 tgradio-name="new"
                                 tgradio="{:url('resources/change_new_status',['id'=>$vo['id']])}"></div></td>
                        <td>{$vo.sort}</td>
                        <td>
                        <div tgradio-not="{:check_auth('admin:resources:update')?'0':'1'}" tgradio-on="1"
                             tgradio-off="0" tgradio-value="{$vo.status}"
                             tgradio-name="status"
                             tgradio="{:url('resources/change_status',['id'=>$vo['id']])}">
                        </div>
                        </td>
                        <td>
                            {$vo.create_time|time_format}
                        </td>
                        <td>
                            <auth rules="admin:resources:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('resources/del',array('id'=>$vo['id']))}">删除</a>
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