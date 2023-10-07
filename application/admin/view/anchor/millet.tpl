<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
            list: [

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
                        <input type="hidden" name="user_id" value=""/>
                    </div>
                    <div class="time_ranger" style="margin-left: 10px;">
                        <select class="base_select range_unit"></select>
                        <select class="base_select range_num"></select>
                        <input value="" readonly placeholder="请选择起始日期" type="text" class="base_text range_custom"/>
                    </div>
                    <div class="filter_search">
                        <input placeholder="主播ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
            <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 10%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 20%;">用户信息</td>
                <td style="width: 20%;">收获{:APP_MILLET_NAME}</td>
                <td style="width: 20%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <include link="{:url('anchor/detail',['user_id'=>$vo.user_id])}" file="anchor/user_info"/>
                        </td>
                        <td>
                            {$vo.millet}
                        </td>
                        <td>
                            <a href="{:url('anchor/detail',['user_id'=>$vo.user_id])}">主播详情</a>
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
        new SearchList('.filter_box', myConfig);
    </script>
</block>
<block name="layer">
</block>