<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'source',
                    title: '片源',
                    opts: JSON.parse('{:json_encode(enum_array("live_film_source"))}')
                },
                {
                    name: 'is_local',
                    title: '云点播',
                    opts: [
                        {name: '已上传', value: '1'},
                        {name: '未上传', value: '0'}
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
                        <auth rules="admin:live_film:add">
                            <a class="base_button base_button_s" href="{:url('live_film/add')}?__JUMP__">新增影片</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="云点播ID" type="text" name="video_id" value="{:input('video_id')}"/>
                        <input placeholder="视频标题ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="source" value="{:input('source')}"/>
            <input type="hidden" name="is_local" value="{:input('is_local')}"/>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 12%;">视频标题</td>
                <td style="width: 10%;">视频描述</td>
                <td style="width: 10%;">视频属性</td>
                <td style="width: 12%;">片源</td>
                <td style="width: 8%;">排片次数</td>
                <td style="width: 8%;">启用状态</td>
                <td style="width: 10%;">创建人</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <include file="live_film/film_info"/>
                        </td>
                        <td><span class="">{$vo.descr|short='20'}</span></td>
                        <td>
                            时长：{$vo.video_duration_str}<br/>
                            宽高：{$vo.video_rate}<br/>
                            格式：{$vo.play|default='未知'}
                        </td>
                        <td>
                            <include file="live_film/source"/>
                        </td>
                        <td>{$vo.use_num}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:liv_film:update')?'0':'1'}"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <notempty name="vo['admin']">
                                <a admin-id="{$vo.admin.id}" href="javascript:;">{$vo.admin|user_name}</a>
                                <else/>
                                未分配
                            </notempty>
                        </td>
                        <td>{$vo.create_time|time_format='无','date'}</td>
                        <td>
                            <auth rules="admin:live_film:update">
                                <a href="{:url('edit',['id'=>$vo.id])}?__JUMP__">编辑影片</a><br/>
                            </auth>
                            <auth rules="admin:live_film:delete">
                                <a class="fc_red" ajax-confirm ajax="get"
                                   href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除影片</a>
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
</block>

<block name="layer">
</block>