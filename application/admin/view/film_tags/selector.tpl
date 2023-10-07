<extend name="public:base_iframe"/>
<block name="js">
    <script>
        var getInfoUrl = '{:url("film_tags/get_info")}',
            addTypeUrl = '{:url("film_tags/add")}',
            editTypeUrl = '{:url("film_tags/edit")}',
            pid = '{$_pid}';
            selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/film_tags/index.js?v=__RV__"></script>
    <script src="__JS__/film_tags/find.js?v=__RV__"></script>
</block>
<block name="css"></block>

<block name="body">
    <div class="pa_20">
        <div class="content_toolbar mt_10">
        	<div style="float: left;line-height: 30px;font-size: 12px;">
                <a class="show_selected" href="javascript:;">已选中标签(<span class="selected_num">0</span>)</a>
            </div>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、名称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td colspan="9">
                    <volist name="_path" id="pa">
                        <a href="{:url('film_tags/selector',array('pid'=>$pa['id']))}">{$pa.name}</a>&nbsp;>&nbsp;
                    </volist>
                </td>
            </tr>
            <tr class="thead_tr">
                <td style="width: 10%">ID</td>
                <td style="width: 25%">名称</td>
                <td style="width: 35%">描述</td>
                <td style="width: 20%">子项</td>
                <td style="width: 10%">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}" class="find_list_li">
                        <td>{$vo.id}</td>
                        <td><a href="{:url('film_tags/selector',array('pid'=>$vo['id']))}">{$vo.name}</a></td>
                        <td>{$vo.descr}</td>
                        <td>{$vo.child_num}</td>
                        <td>
                            <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                            <input class="find_params" type="hidden" name="name" value="{$vo.name}"/>
                        	<notempty name="vo['pid']">
                            <a data-id="{$vo.id}" class="select_btn" href="javascript:;">选择</a>
                            </notempty>
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

        <div class="pageshow mt_10">
            {:htmlspecialchars_decode($_page)}
        </div>

    </div>
    <script>
        new FinderController('.finder', '');
    </script>
</block>


<block name="layer">
    <include file="film_tags/add_pop"/>
    <div class="selected_box" style="padding: 10px;display: none;">
        <table class="table" style="width: 100%;box-sizing: border-box;">
            <thead>
            <tr>
                <td>标签ID</td>
                <td>名称</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</block>