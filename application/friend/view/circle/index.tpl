<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '是否审核',
                    opts: {:htmlspecialchars_decode($isRecom)}
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
                    <ul class="content_toolbar_btns">
                        <auth rules="admin:article:add">
                            <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__"
                                   class="base_button base_button_s">新增</a></li>
                        </auth>
                        <auth rules="admin:article:delete">
                            <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm
                                   class="base_button base_button_s base_button_red">批量关闭</a></li>
                        </auth>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="用户id" type="text" name="uid" value="{:input('uid')}"/>
                        <input placeholder="圈子内容" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="room_model" value="{:input('room_model')}"/>
            <input type="hidden" name="type" value="{:input('type')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 8%;">ID</td>
                    <td style="width: 9%;">用户ID</td>
                    <td style="width: 15%;">圈子标题</td>
                    <td style="width: 20%;">发布时间</td>
                    <td style="width: 8%;">是否为推荐</td>
                    <td style="width: 8%;">是否通过审核</td>
                    <td style="width: 13%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">

                        <tr data-id="{$vo.circle_id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.circle_id}"/></td>
                            <td>{$vo.circle_id}</td>
                            <td>
                                <include file="public/vo_user"/>
                            </td>

                            <td>
                                {$vo.circle_name|default='--'}

                            </td>
                            <td>
                                发布时间：{$vo.ctime|time_format='暂无','datetime'}<br/>
                            </td>
                            <td>
                                <div tgradio-not="{:check_auth('friend:friend:change_recom')?'0':'1'}" tgradio-on="1"
                                     tgradio-off="0" tgradio-value="{$vo.is_recom}"
                                     tgradio-name="status" tgradio-on-name="推荐" tgradio-off-name="普通"
                                     tgradio="{:url('circle/change_recom',['id'=>$vo['circle_id']])}"></div>
                            </td>
                            <td>
                                <div tgradio-not="{:check_auth('friend:friend:change_status')?'0':'1'}" tgradio-on="1"
                                     tgradio-off="0" tgradio-value="{$vo.status}"
                                     tgradio-name="status" tgradio-on-name="通过" tgradio-off-name="关闭"
                                     tgradio="{:url('circle/change_status',['id'=>$vo['circle_id']])}"></div>
                            </td>
                            <td>
                                <auth rules="friend:friend:update">
                                    <a href="{:url('edit',array('id'=>$vo['circle_id']))}?__JUMP__">编辑</a><br/>
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
    </script>

</block>