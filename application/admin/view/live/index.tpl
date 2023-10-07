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
                    <div style="float: left">
                        <auth rules="admin:live:delete">
                            <a class="base_button base_button_s" href="{:url('live/delete')}?__JUMP__">关闭直播</a>
                            <a href="{:url('live/robot_delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">一键修复机器人</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="直播间ID" type="text" name="room_id" value="{:input('room_id')}"/>
                        <!--<input placeholder="主播ID" type="text" name="keyword" value="{:input('keyword')}"/> -->
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
                <td style="width: 8%;">推播流状态</td>
                <td style="width: 6%;">房间人数</td>
                <td style="width: 15%;">今日任务</td>
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
                            <include file="live_film/source"/>
                        </td>
                        <td>
                            观众数：{$vo.audience|default='0'}<br/>
                            机器人：{$vo.robot|default='0'}<br/>
                            实时显：{$vo.audience*2+$vo.robot*20}
                        </td>
                        <td>
                            累计时长：{$vo.task_duration||default=''}<br/>
                            累计点亮：{$vo.task_light|default=''}<br/>
                            今日收益：{$vo.task_profit|default=''}<br/>
                            新增粉丝：{$vo.task_fans|default=''}<br/>
                            PK胜场：{$vo.task_pk|default=''}<br/>
                        </td>
                          <td>
                            <div tgradio-not="{:check_auth('admin:live:hot')?'0':'1'}"
                                 tgradio-value="{$vo.hot_status}" tgradio-name="hot"
                                 tgradio="{:url('change_hot',array('id'=>$vo['id']))}"></div>
                        </td>

                        <!--<td>
                            <div tgradio-not="{:check_auth('admin:live:top')?'0':'1'}"
                                 tgradio-value="{$vo.top_status}" tgradio-name="top"
                                 tgradio="{:url('change_top',array('id'=>$vo['id']))}"></div>
                        </td>-->

                        <td>
                            开播时间：{$vo.create_time|time_format='暂无','datetime'}<br/>
                            直播时长：{$vo.live_duration}
                        </td>
                        <td>
                            <if condition="$vo.room_model eq 1">
                            <auth rules="admin:live:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}">编辑信息</a><br/>
                            </auth>
                            </if>
                            <auth rules="admin:live:robot">
                                <a poplink="live_add_robot" data-id="room_id:{$vo.id}" href="javascript:;">调配机器人</a><br/>
                            </auth>
                           <a poplink="live_top_box" data-query="room_id={$vo.id}&sort={$vo.sort}"  href="javascript:;">
                                添加置顶排序
                            </a><br/>
                            <auth rules="admin:live:select">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('live/delete',array('room_id'=>$vo['id']))}">关播</a>
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
</block>

<block name="layer">
    <include file="live/live_add_robot_pop"/>
    <include file="live/top_pop"/>
</block>