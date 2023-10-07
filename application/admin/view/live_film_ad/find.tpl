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
        var liveFilmAdItemUrl = '{:url("live_film_ad/detail")}',
            selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/live_film_ad/find.js?v=__RV__"></script>
    <script>
        var myConfig = {
            action: '{:url("live_film_ad/find")}',//提交地址
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
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left;line-height: 30px;font-size: 12px;">
                        <a class="show_selected" href="javascript:;">已选中广告(<span class="selected_num">0</span>)</a>
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
                    <tr class="find_list_li" data-id="{$vo.id}">
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
                            <div tgradio-not="1"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td></td>
                        <td>{$vo.create_time|time_format='无'}</td>
                        <td>
                            <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                            <input class="find_params" type="hidden" name="ad_title" value="{$vo.ad_title}"/>
                            <input class="find_params" type="hidden" name="ad_link" value="{$vo.ad_link}"/>
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
                <td>广告ID</td>
                <td>标题</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</block>