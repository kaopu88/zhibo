<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: []
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
                        <auth rules="admin:topic:add">
                            <a class="base_button base_button_s" href="{:url('topic/add')}?__JUMP__">新增话题</a>
                        </auth>
                    </div>

                    <ul class="filter_complex" style="margin-left: 10px;float: left">
                        <li>排序：</li>
                        <li sort-name="participate_num" sort-by="desc">
                            <span>参与量</span>
                            <em></em>
                        </li>
                    </ul>

                    <div class="filter_search">
                        <input placeholder="标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 sm_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 10%;">图标</td>
                <td style="width: 15%;">标题</td>
                <td style="width: 20%;">描述</td>
                <td style="width: 10%;">参与量</td>
                <td style="width: 10%;">权重</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td><div class="thumb"><img style="max-width: 100px;" src="{:img_url($vo['icon'],'200_200','thumb2')}"/></div></td>
                        <td>{$vo.title}</td>
                        <td>{$vo.descr}</td>
                        <td>{$vo.participate_num}</td>
                        <td>{$vo.sort}</td>
                        <td>{$vo.create_time|time_format='无','date'}</td>
                        <td>
                            <auth rules="admin:topic:update">
                                <a href="{:url('edit',['id'=>$vo.id])}?__JUMP__">编辑话题</a><br/>
                            </auth>
                            <auth rules="admin:topic:delete">
                                <a class="fc_red" ajax-confirm ajax="get"
                                   href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除话题</a>
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