<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb{width: 50%;}
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'scene',
                    title: '使用场合',
                    opts: [
                        {name: '短视频', value: 'video'},
                        {name: '直播间', value: 'live'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/music/index.js?v=__RV__"></script>
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
                        <input placeholder="用户ID、昵称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="歌曲ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="scene" value="{:input('scene')}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 25%;">音乐</td>
                <td style="width: 25%;">用户</td>
                <td style="width: 15%;">使用场合</td>
                <td style="width: 15%;">更新时间</td>
                <td style="width: 15%;">添加时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a href="{$vo.link}"
                                   target="_blank"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['image'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="{$vo.link}" target="_blank">
                                        <eq name="vo['is_original']" value="1">
                                            <span class="fc_red">[原创]</span><br/>
                                        </eq>
                                        {$vo.title}<br/>
                                        <a href="{$vo.lrc_link}">歌词链接</a>
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <include file="recharge_app/user_info"/>
                        </td>
                        <td>{$vo.scene_str}</td>
                        <td>
                            {$vo.update_time|time_format}
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

</block>