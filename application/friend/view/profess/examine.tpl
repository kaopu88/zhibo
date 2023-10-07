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
                    <ul class="content_toolbar_btns">
                        <div style="float: left">
                            <a href="{:url('batch_pass')}" ajax="post" ajax-target="list_id" ajax-confirm
                               class="base_button base_button_s base_button_red">批量通过</a>
                        </div>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="消息id" type="text" name="fcmid" value="{:input('fcmid')}"/>
                        <input placeholder="评论内容" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="room_model" value="{:input('room_model')}"/>
            <input type="hidden" name="type" value="{:input('type')}"/>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">信息ID</td>
                <td style="width: 10%;">用户ID</td>
                <td style="width: 10%;">发布时间</td>
                <td style="width: 8%;">点赞数量</td>
                <td style="width: 15%;">审核</td>
                <td style="width: 7%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.fcmid}</td>
                        <td>
                            {$vo.uid}
                            <include file="public/vo_user"/>
                        </td>
                        <td>
                            发布时间：{$vo.create_time|time_format='暂无','datetime'}<br/>
                        </td>
                        <td>
                            {$vo.like_count|default='--'}

                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('friend:evaluate:change_status')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status" tgradio-on-name="启用" tgradio-off-name="关闭"
                                 tgradio="{:url('profess/change_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            <auth rules="friend:friend:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">查看</a><br/>
                            </auth>


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
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
    </script>

</block>