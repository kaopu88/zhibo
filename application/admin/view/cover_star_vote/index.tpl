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
                        <input placeholder="发起人ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <input placeholder="选手ID" type="text" name="to_user_id" value="{:input('to_user_id')}"/>
                        <input placeholder="ID、交易单号" type="text" name="keyword" value="{:input('keyword')}"/>
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
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">交易单号</td>
                <td style="width: 25%;">投票用户</td>
                <td style="width: 25%;">主播</td>
                <td style="width: 10%;">票数</td>
                <td style="width: 10%;">消费{:APP_BEAN_NAME}数</td>
                <td style="width: 10%;">赠送时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>{$vo.trade_no}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <include file="recharge_app/user_info_a"/></td>
                        </td>
                        <td>
                            {$vo.votes}
                        </td>
                        <td>
                            {$vo.bean}
                        </td>
                        <td>
                            {$vo.create_time|time_format}
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

<block name="layer">
</block>