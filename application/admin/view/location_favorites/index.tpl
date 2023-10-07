<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb{width: 50%;}
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {

        };
    </script>
    <script src="__JS__/location/index.js?v=__RV__"></script>
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
                        <input placeholder="地点ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 xs_width">
            <thead>
            <tr>
                <td style="width: 15%;">ID</td>
                <td style="width: 35%;">地点</td>
                <td style="width: 35%;">用户</td>
                <td style="width: 15%;">收藏时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   target="_blank"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['cover'],'200_200','cover')}"/>
                                </a>
                                <p class="thumb_info">
                                    {$vo.name}<br/>
                                    <a href="javascript:;" target="_blank">
                                        {$vo.street_address}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <include file="recharge_app/user_info"/>
                        </td>
                        <td>
                            {$vo.create_time|time_format='无','date'}
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