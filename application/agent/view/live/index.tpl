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
                        {name: '录播', value: '1'},
                    ]
                },
                {
                    name: 'type',
                    title: '房间类型',
                    opts: [
                        {name: '普通', value: '0'},
                        {name: '私密', value: '1'},
                        {name: '收费', value: '2'},
                        {name: '计费', value: '3'},
                        {name: 'VIP', value: '4'},
                        {name: '等级', value: '5'}
                    ]
                }
            ]
        };
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
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
                    <div style="float: left">
                        <a class="base_button base_button_s" href="{:url('live/delete')}?__JUMP__">关闭直播</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="直播间ID" type="text" name="room_id" value="{:input('room_id')}"/>
                        <input placeholder="主播ID" type="text" name="user_id" value="{:input('user_id')}"/>
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
                <td style="width: 5%;">ID</td>
                <td style="width: 12%;">直播标题</td>
                <td style="width: 10%;">主播信息</td>
                <td style="width: 7%;">直播属性</td>

                <td style="width: 6%;">房间人数</td>
                <td style="width: 6%;">热门推荐</td>
                <!--<td style="width: 6%;">热门置顶</td>-->
                <td style="width: 12%;">直播时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>
                            <input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/>
                        </td>
                        <td>{$vo.id}</td>
                        <td>
                            <include file="live/live_info"/>
                        </td>
                        <td>
                            ID：{$vo.user_id}<br/>
                            昵称：<span class="">{$vo.nickname|short='20'}</span><br/>
                        </td>
                        <td>
                            房间模式：{$vo.model_str}<br/>
                            直播类型：{$vo.type_str}<br/>
                            类型值：{$vo.type_val|default='暂无'}
                        </td>

                        <td>
                            观众数：{$vo.audience|default='0'}<br/>
                            机器人：{$vo.robot|default='0'}<br/>
                            实时显：{$vo.audience*2+$vo.robot*20}
                        </td>

                          <td>
                              <if condition="$vo.hot_status eq 0">
                                  否
                                  <else/>
                                  是
                              </if>
                        </td>


                        <td>
                            开播时间：{$vo.create_time|time_format='暂无','datetime'}<br/>
                            直播时长：{$vo.live_duration}
                        </td>
                        <td>
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('live/delete',array('room_id'=>$vo['id']))}">关播</a>
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
