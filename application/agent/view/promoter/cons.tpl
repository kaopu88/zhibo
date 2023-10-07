<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var list = [

        ];
        var myConfig = {
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
            list: list
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
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
                    <div class="time_ranger" style="margin-left: 10px;">
                        <select class="base_select range_unit"></select>
                        <select class="base_select range_num"></select>
                    </div>
                    <div class="filter_search">

                        <input placeholder="{:config('app.agent_setting.promoter_name')}ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
            <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
            <input type="hidden" name="agent_id" value="{$get.agent_id}"/>
        </div>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 10%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 10%;">ID</td>
                    <td style="width: 20%;">用户信息</td>
                    <td style="width: 20%;">客户消费({:APP_BEAN_NAME})</td>
                    <td style="width: 20%;">折合RMB(元)</td>
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
                                <include link="{:url('promoter/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                            </td>
                            <td>
                                {$vo.cons}
                            </td>
                            <td>
                                {$vo.cons|equ_rmb}
                            </td>
                            <td>
                                <a href="{:url('promoter/detail',['user_id'=>$vo.user_id])}">{:config('app.agent_setting.promoter_name')}详情</a>
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
        var startTime = $('[name=start_time]').flatpickr({
            dateFormat: 'Y-m-d',
            onChange: function (dateObj, dateStr, instance) {
                endTime.set('minDate', dateStr);
            }
        });
        var endTime = $('[name=end_time]').flatpickr({
            dateFormat: 'Y-m-d',
            onChange: function (dateObj, dateStr, instance) {
                startTime.set('maxDate', dateStr);
            }
        });

        new SearchList('.filter_box', myConfig);
    </script>
</block>
<block name="layer">
</block>