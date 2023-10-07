<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'is_code',
                    title: '短信验证码',
                    opts: [
                        {name: '是', value: '1'},
                        {name: '否', value: '0'}
                    ]
                },
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '已发送', value: '0'},
                        {name: '成功', value: '1'},
                        {name: '失败', value: '2'},
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
                        <input placeholder="手机号、模板ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{$get.status}"/>
            <input type="hidden" name="is_code" value="{$get.is_code}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">手机号</td>
                <td style="width: 10%;">模板ID</td>
                <td style="width: 25%;">内容</td>
                <td style="width: 15%;">返回结果</td>
                <td style="width: 10%;">短信验证码</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">发送时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td><span class="fc_green">+{$vo.phone_code}</span>&nbsp;<eq name="admin['id']" value="1"> {$vo.phone} <else/>{$vo.phone|str_hide=3,4|default=''}</eq></td>
                        <td>{$vo.template}</td>
                        <td>{$vo.content}</td>
                        <td>{$vo.result.Message}</td>
                        <td>{$vo.is_code ? '是' : '否'}</td>
                        <td>
                            <switch name="vo['status']">
                                <case value="0">
                                    已发送
                                </case>
                                <case value="1">
                                    <span class="fc_green">成功</span>
                                </case>
                                <case value="2">
                                    <span class="fc_red">失败</span>
                                </case>
                            </switch>
                        </td>
                        <td>
                            {$vo.send_time|time_format}
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