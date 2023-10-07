<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
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
                    <div class="filter_search">
                        <input placeholder="礼物ID" type="text" name="gift_id" value="{:input('gift_id')}"/>
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <input placeholder="视频ID" type="text" name="video_id" value="{:input('video_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">礼物信息</td>
                <td style="width: 5%;">礼物数量</td>
                <td style="width: 5%;">类型</td>
                <td style="width: 15%;">视频信息</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 5%;">连击次数</td>
                <td style="width: 10%;">获取总数</td>
                <td style="width: 10%;">更新时间</td>
                <td style="width: 10%;">创建时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['gift_info']['picture_url'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.gift_info.name}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.num}</td>
                        <td>{$vo.type}</td>
                        <td>
                            <div class="thumb">
                                <a layer-title="0" layer-area="414px,779px"
                                   layer-open="{:url('video/tcplayer',['id'=>$vo.video_info.id])}" href="javascript:;"
                                   class="thumb_img" style="display: inline-block;max-width: 100px;">
                                    <img src="{:img_url($vo['video_info']['animate_url']?$vo['video_info']['animate_url']:$vo['video_info']['cover_url'],'120_68','film_cover')}"/>
                                </a>
                            </div>
                        </td>
                        <td>
                            <include file="recharge_app/user_info"/>
                        </td>
                        <td>{$vo.digg_total}</td>
                        <td>{$vo.total}</td>
                        <td>
                            {$vo.last_update_time|time_format}
                        </td>
                        <td>
                            {$vo.create_time|time_format}
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
            new SearchList('.filter_box',myConfig);
        });
    </script>

</block>