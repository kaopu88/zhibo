<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'room_model',
                    title: '房间模式',
                    opts: [
                        {name: '直播', value: '0'},
                        {name: '录播', value: '1'},
                        {name: '电影', value: '2'},
                        {name: '游戏', value: '3'}
                    ]
                },
                {
                    name: 'type',
                    title: '房间类型',
                    opts: [
                        {name: '普通', value: '0'},
                        {name: '私密', value: '1'},
                        {name: '收费', value: '2'},
                        {name: '计费', value: '3'},
                        {name: 'VIP', value: '4'},
                        {name: '等级', value: '5'}
                    ]
                }
            ]
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
                    <div class="filter_search">
                        <input placeholder="直播间ID" type="text" name="room_id" value="{:input('room_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="room_model" value="{:input('room_model')}"/>
            <input type="hidden" name="type" value="{:input('type')}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">主播信息</td>
                <td style="width: 20%;">直播属性</td>
                <td style="width: 15%;">房间人数</td>
                <td style="width: 10%">所在地区</td>
                <td style="width: 15%;">本次直播收益</td>
                <td style="width: 15%;">直播时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a href="{:url('anchor/detail',['user_id'=>$vo.user_id])}"
                                   target="_blank"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    {$vo.nickname|short='20'}
                                </p>
                            </div>
                        </td>
                        <td>
                            房间ID：{$vo.room_id}<br/>
                            标题：{$vo.title}<br/>
                            房间模式：{$vo.room_model_txt}<br/>
                            房间频道：{$vo.channel_info.name}<br/>
                            直播类型：{$vo.type_txt}<br/>
                            类型值：{$vo.type_val|default='暂无'}
                        </td>
                        <td>
                            观众数：{$vo.real_audience|default='0'}<br/>
                            机器人：{$vo.robot|default='0'}<br/>
                            实时显：{$vo.real_audience*2+$vo.robot*20}
                        </td>
                        <td>
                            {$vo.province}<br>
                            {$vo.city}<br>
                            {$vo.district}
                        </td>
                        <td>{$vo.profit}</td>
                        <td>
                            开播时间：{$vo.start_time|time_format='暂无','datetime'}<br/>
                            结束时间：{$vo.end_time|time_format='暂无','datetime'}
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