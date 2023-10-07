<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'type',
                    title: '类型',
                    opts: [
                        {name: '未关注', value: '0'},
                        {name: '关注', value: '1'}
                    ]
                },
                {
                    name: 'ismutual',
                    title: '互关',
                    opts: [
                        {name: '是', value: '1'},
                        {name: '否', value: '0'}
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
                        <input placeholder="被关注用户ID" type="text" name="follow_id" value="{:input('follow_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="type" value="{:input('type')}"/>
            <input type="hidden" name="ismutual" value="{:input('ismutual')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 25%;">用户信息</td>
                <td style="width: 25%;">被关注用户信息</td>
                <td style="width: 15%;">类型</td>
                <td style="width: 15%;">互关</td>
                <td style="width: 15%;">创建时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <include file="recharge_app/user_info_a"/></td>
                        </td>
                        <td>
                            <switch name="vo['type']">
                                <case value="0">
                                    未关注
                                </case>
                                <case value="1">
                                    关注
                                </case>
                            </switch>
                        </td>
                        <td>
                            <switch name="vo['ismutual']">
                                <case value="0">
                                    否
                                </case>
                                <case value="1">
                                    是
                                </case>
                            </switch>
                        </td>
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