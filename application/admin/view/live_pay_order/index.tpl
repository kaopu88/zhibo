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
                        {name: '录像', value: '1'},
                        {name: '电影', value: '2'},
                        {name: '游戏', value: '3'}
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
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <input placeholder="主播ID" type="text" name="anchor_id" value="{:input('anchor_id')}"/>
                        <input placeholder="订单号、直播间ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="room_model" value="{:input('room_model')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">订单号</td>
                <td style="width: 5%;">直播间ID</td>
                <td style="width: 10%;">房间标题</td>
                <td style="width: 10%;">房间模式</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 15%;">主播信息</td>
                <td style="width: 10%;">付费额度({:APP_BEAN_NAME})</td>
                <td style="width: 10%;">实付额度({:APP_BEAN_NAME})</td>
                <td style="width: 10%;">创建时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>{$vo.trade_no}</td>
                        <td>{$vo.room_id}</td>
                        <td>{$vo.room_title}</td>
                        <td>{$vo.room_model_txt}</td>
                        <td>
                            <include file="recharge_app/user_info"/>
                        </td>
                        <td>
                            <include file="recharge_app/user_info_a"/>
                        </td>
                        <td>{$vo.room_bean}</td>
                        <td>{$vo.pay_bean}</td>
                        <td>
                            {$vo.create_time|time_format='无'}
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