<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'masterid',
                    title: '交友主分类',
                    opts: {:htmlspecialchars_decode($_classfiyArray)}
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
                            <li>
                                <a href="{:url('addclassfiy',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__"
                                   class="base_button base_button_s">新增</a></li>
                        </auth>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="内容关键字" type="text" name="keyword" value="{:input('keyword')}"/>
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
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">主分类</td>
                <td style="width: 10%;">分类名称</td>
                <td style="width: 10%;">发布时间</td>
                <td style="width: 8%;">是否启用</td>
                <td style="width: 7%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <p class="thumb_info">
                                    {$vo.clname|short='20'}
                                </p>
                            </div>
                        </td>
                        <td>
                            {$vo.child_name|short='20'}<br/>
                        </td>
                        <td>
                            发布时间：{$vo.create_time|time_format='暂无','datetime'}<br/>
                        </td>

                        <td>
                            <div tgradio-not="{:check_auth('friend:config:change_status')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status" tgradio-on-name="启用" tgradio-off-name="关闭"
                                 tgradio="{:url('config/changeclassfiystatus',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            <auth rules="friend:config:update">
                                <a href="{:url('editclassfiy',array('id'=>$vo['id']))}?__JUMP__">编辑</a><br/>
                            </auth>




                                <auth rules="friend:friend:deleteLog">
                                    <a class="fc_red" ajax-confirm ajax="get"
                                       href="{:url('delclassfiy',array('id'=>$vo['id']))}?__JUMP__"  <if condition="$vo['masterid'] == 0">hidden</if> >删除</a>
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