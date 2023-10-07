<extend name="public:base_iframe"/>
<block name="css">
    <style>
        .select_btn {
            color: #555;
        }

        .selected, .selected:visited, .selected:hover, .selected:active {
            color: #eb6100;
        }
    </style>
</block>

<block name="js">
    <script>
        var liveFilmItemUrl = '{:url("live_film/detail")}',
            selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/live_film/find.js?v=__RV__"></script>
    <script>
        var myConfig = {
            action: '{:url("live_film/find")}',//提交地址
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
                        <a class="show_selected" href="javascript:;">已选中影片(<span class="selected_num">0</span>)</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="云点播ID" type="text" name="video_id" value="{:input('video_id')}"/>
                        <input placeholder="视频标题、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="source" value="{:input('source')}"/>
            <input type="hidden" name="is_local" value="{:input('is_local')}"/>
        </div>
        <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 18%;">视频标题</td>
                <td style="width: 15%;">视频属性</td>
                <td style="width: 17%;">片源</td>
                <td style="width: 10%;">排片次数</td>
                <td style="width: 10%;">启用状态</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr class="find_list_li" data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <include file="live_film/film_info"/>
                        </td>
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
                        <td>{$vo.create_time|time_format='无','date'}</td>
                        <td>
                            <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                            <input class="find_params" type="hidden" name="video_title" value="{$vo.video_title}"/>
                            <input class="find_params" type="hidden" name="video_cover" value="{$vo.video_cover}"/>
                            <input class="find_params" type="hidden" name="video_duration"
                                   value="{$vo.video_duration}"/>
                            <input class="find_params" type="hidden" name="video_url" value="{$vo.video_url}"/>
                            <input class="find_params" type="hidden" name="video_id" value="{$vo.video_id}"/>
                            <input class="find_params" type="hidden" name="video_rate" value="{$vo.video_rate}"/>
                            <a data-id="{$vo.id}" class="select_btn" href="javascript:;">选择</a>
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
    <div class="selected_box" style="padding: 10px;display: none;">
        <table class="table" style="width: 100%;box-sizing: border-box;">
            <thead>
            <tr>
                <td>影片ID</td>
                <td>标题</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</block>