<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'pk_type',
                    title: 'pk类型',
                    opts: [
                        {name: '全民PK', value: 'rand'},
                        {name: '好友PK', value: 'friend'},
                        {name: 'PK排位赛', value: 'pk_rank'},
                    ]
                },
                {
                    name: 'pk_res',
                    title: 'pk结果',
                    opts: [
                        {name: '平局', value: '0'},
                        {name: '我方胜', value: '1'},
                        {name: '对方胜', value: '-1'}
                    ]
                },
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '进行中', value: '0'},
                        {name: '已完成', value: '1'}
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
                    <div class="filter_search">
                        <input placeholder="主动方主播ID" type="text" name="active_id" value="{:input('active_id')}"/>
                        <input placeholder="对方主播ID" type="text" name="target_id" value="{:input('target_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="pk_type" value="{:input('pk_type')}"/>
            <input type="hidden" name="pk_res" value="{:input('pk_res')}"/>
            <input type="hidden" name="status" value="{:input('status')}"/>
        </div>
        <div class="table_slide">
         <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">主动方信息</td>
                <td style="width: 15%;">对方信息</td>
                <td style="width: 5%;">我方收益</td>
                <td style="width: 5%;">对方收益</td>
                <td style="width: 5%;">pk主题</td>
                <td style="width: 5%;">pk类型</td>
                <td style="width: 8%;">开始时间</td>
                <td style="width: 8%;">结束时间</td>
                <td style="width: 8%;">pk时长</td>
                <td style="width: 8%;">pk结果</td>
                <td style="width: 8%;">惩罚主题</td>
                <td style="width: 5%;">状态</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            【房间ID：{$vo.active_room_id}】<br/><include file="recharge_app/user_info"/>
                        </td>
                        <td>
                            【房间ID：{$vo.target_room_id}】<br/><include file="recharge_app/user_info_a"/>
                        </td>
                        <td>{$vo.active_income}</td>
                        <td>{$vo.target_income}</td>
                        <td>{$vo.pk_topic}</td>
                        <td>{$vo.pk_type_txt}</td>
                        <td>
                            {$vo.pk_start_time|time_format='无'}
                        </td>
                        <td>
                            {$vo.pk_end_time|time_format='无'}
                        </td>
                        <td>{$vo.pk_duration}</td>
                        <td>{$vo.pk_res_txt}</td>
                        <td>{$vo.ac_topic}</td>
                        <td>{$vo.status_txt}</td>
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