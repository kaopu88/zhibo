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
                    <div style="float: left">
                        <auth rules="admin:live_film_ad:add">
                            <a class="base_button base_button_s" href="{:url('live_film_ad/add')}?__JUMP__">新增广告</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="云点播ID" type="text" name="video_id" value="{:input('video_id')}"/>
                        <input placeholder="广告标题、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 8%;">ID</td>
                <td style="width: 15%;">广告标题</td>
                <td style="width: 12%;">广告链接</td>
                <td style="width: 10%;">视频时长</td>
                <td style="width: 10%;">宽高比</td>
                <td style="width: 10%;">启用状态</td>
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
                        <td><include file="live_film_ad/film_info"/></td>
                        <td>
                            <notempty name="vo['ad_link']">
                                <a target="_blank" href="{$vo.ad_link}">{$vo.ad_link|short=20}</a>
                                <else/>
                                无链接
                            </notempty>
                        </td>
                        <td>
                            {$vo.video_duration_str}<br/>
                            VID:{$vo.video_id}
                        </td>
                        <td>{$vo.video_rate}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:live_film_ad:update')?'0':'1'}"
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
                        <td>{$vo.create_time|time_format='无'}</td>
                        <td>
                            <auth rules="admin:live_film_ad:update">
                                <a href="{:url('edit',['id'=>$vo['id']])}?__JUMP__">编辑</a><br/>
                            </auth>
                            <auth rules="admin:live_film_ad:delete">
                                <a class="fc_red" ajax-confirm ajax="get"
                                   href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
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
        new SearchList('.filter_box', myConfig);
    </script>
</block>

<block name="layer">
</block>