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
                    get: '{:url("credit_log/get_type")}'
                },
                {
                    name: 'change_type',
                    title: '更新类型',
                    opts: [
                        {name: '增长', value: 'inc'},
                        {name: '降低', value: 'exp'},
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/video_comment/index.js?v=__RV__"></script>
    <script src="__VENDOR__/raty/jquery.raty.min.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>
                <notempty name="get.user_id">
                    【{$user|user_name}】信用分记录
                    <else/>
                    <h1>{$admin_last.name}</h1>
                </notempty>
            </h1>
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
                    <ul class="filter_complex" style="margin-left: 10px;float: left">
                        <li>排序：</li>
                        <li sort-name="create_time" sort-by="desc">
                            <span>创建时间</span>
                            <em></em>
                        </li>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="用户ID、昵称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="ID、主题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
            <input type="hidden" name="type" value="{$get.type}"/>
            <input type="hidden" name="change_type" value="{$get.change_type}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">类型</td>
                <td style="width: 15%;">主题</td>
                <td style="width: 15%;">分值</td>
                <td style="width: 15%;">输入值</td>
                <td style="width: 15%;">管理员</td>
                <td style="width: 15%;">创建时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.name}</td>
                        <td>{$vo.subject}</td>
                        <td><span class="{$vo.change_type=='inc' ? 'fc_green' : 'fc_red'}">{$vo.change_type=='inc' ? '+' : '-'}{$vo.score}</span></td>
                        <td>{$vo.input_value}</td>
                        <td>{$vo.audit_admin|user_name}</td>
                        <td>
                            {$vo.create_time|time_format='Y-m-d H:i'}
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
</block>

<block name="layer">
    <include file="video_comment/update"/>
</block>