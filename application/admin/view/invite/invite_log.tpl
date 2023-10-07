<extend name="public:base_nav"/>
<block name="css">
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
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div class="filter_search">
                        <input placeholder="用户ID、昵称、手机号" type="text" name="keyword" value="{:input('keyword')}"/>
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
                <td style="width: 5%">ID</td>
                <td style="width: 12%">发起人信息</td>
                <td style="width: 9%">被邀请人信息</td>
                <td style="width: 9%">时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr>
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb" style="width: 50%">
                                <a href="{:url('user/detail',['user_id'=>$vo.user_id])}" class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('user/detail',['user_id'=>$vo.user_id])}">
                                        {$vo.nickname|user_name}<br/>
                                        {$vo.phone|str_hide=3,4|default='未绑定'}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <p>好友:</p>
                            <div class="thumb" style="width: 50%">
                                <a href="javascript:;" class="thumb_img">
                                    <img src="{:img_url($vo.user_info.avatar,'200_200','logo')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.user_info.nickname}
                                        <br/>
                                        {$vo.user_info.phone|str_hide=3,4|default='未绑定'}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.create_time|time_format}</td>
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
        new SearchList('.filter_box', myConfig);
    </script>
</block>