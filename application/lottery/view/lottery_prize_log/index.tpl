<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <div class="content_toolbar_search">
                <div class="base_group">

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索用户id、用户姓名"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 10%;">ID</td>
                <td style="width: 20%;">用户id</td>
                <td style="width: 20%;">用户信息名</td>
                <td style="width: 10%;">中奖礼物</td>
                <td style="width: 20%;">中奖时间</td>
                <td style="width: 20%;">中奖来源</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>
                            {$vo.id}
                        </td>

                        <td>
                            {$vo.user_id}
                        </td>
                        <td> <include file="public/user"/></td>
                        <td>
                            {$vo.name}
                        </td>

                        <td>
                            <switch name="vo['gift_source']">
                                <case value="egg">
                                    砸金蛋
                                </case>
                            </switch>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>