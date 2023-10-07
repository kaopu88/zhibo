<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb {
            width: 50%;
        }
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
        };
    </script>
</block>

<block name="body">
    <div class="pa_20 p_nav">

        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <include file="components/tab_nav"/>

        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <if condition="input('interval') != 'his'">
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
                        <div class="clear"></div>
                    </div>
                </div>
            </if>
            <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
            <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 xs_width">
            <thead>
            <tr>
                <td style="width: 10%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 15%;">排名</td>
                <td style="width: 15%;">用户ID</td>
                <td style="width: 25%;">用户信息</td>
                <td style="width: 15%;">{:APP_MILLET_NAME}</td>
                <td style="width: 20%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="heroes_rank">
                <volist name="heroes_rank" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.num}</td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <include file="user/user_info"/>
                        </td>
                        <td>{$vo.millet}</td>
                        <td>
                            <a data-query="user_id={$vo.user_id}&interval={:input('interval')}&millet={$vo.millet}&name=heroes:gift<if condition="$get.rnum != ''">&rnum={$get.rnum}</if>" poplink="millet_handler"
                               href="javascript:;">{:APP_MILLET_NAME}变更</a>
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
    <include file="rank/millet_handler"/>
    <include file="user/remark_pop"/>
</block>
