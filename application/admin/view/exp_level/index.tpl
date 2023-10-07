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

        };
    </script>
    <script src="__JS__/exp_level/index.js?v=__RV__"></script>
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
                        <auth rules="admin:exp_level:add">
                            <a href="{:url('add')}?__JUMP__" class="base_button base_button_s">新增</a>
                        </auth>
                        <auth rules="admin:exp_level:delete">
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
            <input type="hidden" name="is_recommend" value="{$get.is_recommend}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10 sm_width">
            <thead>
            <tr>
                <td style="width: 10%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 25%;">标题</td>
                <td style="width: 15%;">经验值</td>
                <td style="width: 20%;">添加时间</td>
                <td style="width: 20%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.levelid}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.levelid}"/></td>
                        <td>{$vo.levelid}</td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['icon'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.levelname}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.level_up}</td>
                        <td>
                            {$vo.addtime|time_format='无','date'}
                        </td>
                        <td>
                            <auth rules="admin:exp_level:update">
                                <a href="{:url('edit',array('id'=>$vo['levelid']))}?__JUMP__">编辑</a>
                            </auth>
                            <auth rules="admin:exp_level:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('delete',array('id'=>$vo['levelid']))}?__JUMP__">删除</a>
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