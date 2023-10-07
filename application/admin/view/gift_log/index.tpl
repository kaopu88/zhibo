<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'msg_status',
                    title: '状态',
                    opts: [
                        {name: '未查看', value: '0'},
                        {name: '已查看', value: '1'},
                        {name: '已回复', value: '2'},
                    ]
                },
                {
                    name: 'isvirtual',
                    title: '虚拟用户',
                    opts: [
                        {name: '是', value: '1'},
                        {name: '否', value: '0'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/gift_log/index.js?v=__RV__"></script>
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
                        <input placeholder="赠送者ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <input placeholder="接收者ID" type="text" name="to_uid" value="{:input('to_uid')}"/>
                        <input placeholder="ID、赠送单号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="msg_status" value="{$get.msg_status}"/>
            <input type="hidden" name="isvirtual" value="{$get.isvirtual}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">赠送用户</td>
                <td style="width: 15%;">接收用户</td>
                <td style="width: 20%;">赠送内容</td>
                <td style="width: 10%;">赠送场景</td>
                <td style="width: 15%;">回复内容</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">赠送时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <include file="recharge_app/user_info_a"/></td>
                        </td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['picture_url'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.gift_no}<br/>
                                        赠送{$vo.num}个{$vo.name}，消费{$vo.price}{:config('app.product_setting.bean_name')}
                                        <br/>留言：{$vo.leave_msg}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <switch name="vo['scene']">
                                <case value="video">
                                    <div class="thumb">
                                        <a layer-title="0" layer-area="414px,779px"
                                           layer-open="{:url('video/tcplayer',['id'=>$vo.video_id])}" href="javascript:;"
                                           class="thumb_img" style="display: inline-block;max-width: 100px;">
                                            <img src="{:img_url($vo['animate_url']?$vo['animate_url']:$vo['cover_url'],'120_68','film_cover')}"/>
                                        </a>
                                    </div>
                                </case>
                                <case value="live">
                                    【直播间】
                                </case>
                            </switch>
                        </td>
                        <td>
                            {$vo.reply_msg}
                            <br/>
                            {$vo.reply_time|time_format='无','date'}
                        </td>
                        <td>
                            <switch name="vo['msg_status']">
                                <case value="0">
                                    未查看
                                </case>
                                <case value="1">
                                    已查看
                                </case>
                                <case value="2">
                                    已回复
                                </case>
                            </switch>
                        </td>
                        <td>
                            {$vo.create_time|time_format='无'}
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
    <include file="recharge_app/recharge_app_handler"/>
    <include file="components/task_transfer_box"/>
</block>