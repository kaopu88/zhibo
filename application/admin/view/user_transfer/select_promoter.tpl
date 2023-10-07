<extend name="public:base_iframe"/>
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
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left;line-height: 30px;font-size: 12px;">
                        <a href="javascript:history.back();">返回{:config('app.agent_setting.agent_name')}列表</a>&nbsp;
                        <a class="fc_orange ml_5" ajax-after="confirmAfter" ajax="post"
                           href="{:url('user_transfer/confirm')}">确认(不选{:config('app.agent_setting.promoter_name')})</a>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <include file="user_transfer/selected"/>
        <div class="content_title2">移至以下{:config('app.agent_setting.promoter_name')}</div>
        <div class="filter_box mt_10">
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div class="filter_search">
                        <input placeholder="ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="level" value="{:input('level')}"/>
            <input type="hidden" name="grade" value="{:input('grade')}"/>
            <input type="hidden" name="province" value="{:input('province')}"/>
            <input type="hidden" name="city" value="{:input('city')}"/>
            <input type="hidden" name="district" value="{:input('district')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 10%"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 10%">ID</td>
                    <td style="width: 20%">用户信息</td>
                    <td style="width: 20%">{:config('app.agent_setting.agent_name')}信息</td>
                    <td style="width: 10%">客户数量</td>
                    <td style="width: 15%">注册时间</td>
                    <td style="width: 15%">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                            <td>{$vo.user_id}</td>
                            <td>
                                <include file="user/user_info"/>
                            </td>
                            <td>
                                <include file="user/user_agent"/>
                            </td>
                            <td>{$vo.client_num}</td>
                            <td>
                                {$vo.create_time|time_format}
                            </td>

                            <td>
                                <a ajax-after="confirmAfter" ajax="post"
                                href="{:url('user_transfer/confirm',['user_id'=>$vo.user_id])}">确认</a>
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
        new SearchList('.filter_box', myConfig);

        function confirmAfter(result, next) {
            if (result['status'] == 0) {
                $s.success(result['message'], 2, function () {
                    if (typeof _closeSelf == 'function') {
                        _closeSelf();
                    }
                    if (parent) {
                        parent.location.reload();
                    }
                });
            } else {
                result['reload'] = false;
                next();
            }
        }
    </script>

</block>

<block name="layer">
    <include file="user/remark_pop"/>
</block>