<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: []
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
                    <div class="filter_search">
                        <input readonly="readonly" placeholder="开始时间" name="start_time" value="{:input('start_time')}" type="text" class="base_text flatpickr-input">
                        <input readonly="readonly" placeholder="结束时间" name="end_time" value="{:input('end_time')}" type="text" class="base_text flatpickr-input">
                        <input placeholder="用户ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 7%;">ID</td>
                <td style="width: 10%;">用户信息</td>
                <td style="width: 9%;">任务类型</td>
                <td style="width: 9%;">任务奖励</td>
                <td style="width: 9%;">任务状态</td>
                <td style="width: 9%;">日期</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a href="{:url('user/detail',['user_id'=>$vo.user_id])}" class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
                                    <div class="thumb_level_box">
                                        <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
                                    </div>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('user/detail',['user_id'=>$vo.user_id])}">
                                        {$vo|user_name}
                                        <br/>
                                        {$vo.phone|str_hide=3,4|default='未绑定'}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.type_txt}</td>
                        <td>{$vo.point}</td>
                        <td>
                            <switch $vo.status >
                            <case 1> <span class="fc_orange"> </case>
                            <case 2> <span> </case>
                            <default /> <span class="fc_red">
                            </switch>
                            {$vo.status_txt}
                            </span>
                        </td>
                        <td>{$vo.create_time|date='Y-m-d'}</td>
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
    </script>
</block>