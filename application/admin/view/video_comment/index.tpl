<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '违规', value: '0'},
                        {name: '正常', value: '1'},
                    ]
                },
                {
                    name: 'province',
                    title: '所在省份',
                    data: {country: 0},
                    auto_sub: false,
                    get: '{:url("common/get_area")}'
                },
                {
                    name: 'city',
                    parent: 'province',
                    title: '所在城市',
                    get: '{:url("common/get_area")}'
                }
            ]
        };
    </script>
    <script src="__JS__/video_comment/index.js?v=__RV__"></script>
    <script src="__VENDOR__/raty/jquery.raty.min.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>
                {$admin_last.name}
            </h1>
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
                    <ul class="content_toolbar_btns">
                        <auth rules="admin:film_comment:delete">
                            <li>
                                <a href="{:url('video_comment/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_red base_button_s">批量删除</a>
                            </li>
                        </auth>
                    </ul>
                    <ul class="filter_complex" style="margin-left: 10px;float: left">
                        <li>排序：</li>
                        <li sort-name="complex" sort-by="">
                            <span>综合</span>
                        </li>
                        <li sort-name="reply_count" sort-by="desc">
                            <span>回复量</span>
                            <em></em>
                        </li>
                        <li sort-name="like_count" sort-by="desc">
                            <span>点赞量</span>
                            <em></em>
                        </li>
                        <li sort-name="create_time" sort-by="desc">
                            <span>发布时间</span>
                            <em></em>
                        </li>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="发布者ID、昵称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="发布ID、内容" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
            <input type="hidden" name="status" value="{$get.status}"/>
            <input type="hidden" name="province" value="{$get.province}"/>
            <input type="hidden" name="city" value="{$get.city}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 4%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 4%;">ID</td>
                <td style="width: 9%;">视频</td>
                <td style="width: 10%;">发布用户</td>
                <td style="width: 5%;">城市</td>
                <notempty name="get['master_id']">
                <td style="width: 10%;">回复用户</td>
                </notempty>
                <td style="width: 16%;">发布内容</td>
                <td style="width: 4%;">回复数</td>
                <td style="width: 4%;">点赞数</td>
                <td style="width: {$raty};">热门</td>
                <td style="width: {$raty};">精选</td>
                <td style="width: {$raty};">置顶</td>
                <td style="width: {$raty};">敏感词</td>
                <td style="width: 5%;">状态</td>
                <td style="width: 8%;">发布时间</td>
                <td style="width: 9%;">操作</td>
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
                                <a layer-title="0" layer-area="414px,779px"
                                   layer-open="{:url('video/tcplayer',['id'=>$vo.video_id])}" href="javascript:;"
                                   class="thumb_img" style="display: inline-block;max-width: 100px;">
                                    <img src="{:img_url($vo['animate_url']?$vo['animate_url']:$vo['cover_url'],'120_68','film_cover')}"/>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="thumb">
                                <a href="{:url('user/detail',['user_id'=>$vo.user.user_id])}" class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['user']['avatar'],'200_200','avatar')}"/>
                                    <div class="thumb_level_box">
                                        <img title="{$vo.user.level_name}" src="{$vo.user.level_icon}"/>
                                    </div>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('user/detail',['user_id'=>$vo.user.user_id])}">
                                        {$vo.user|user_name}&nbsp;<if condition="$vo.is_anchor == '1'"><span class="badge" style="line-height:12px;">作者</span></if><br/>
                                        {$vo.user.phone|str_hide=3,4|default='未绑定'}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.city_name}</td>
                        <notempty name="vo['master_id']">
                        <td>
                            <notempty name="vo['reply']">
                            <div class="thumb">
                                <a href="{:url('user/detail',['user_id'=>$vo.reply.user_id])}" class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['reply']['avatar'],'200_200','avatar')}"/>
                                    <div class="thumb_level_box">
                                        <img title="{$vo.reply.level_name}" src="{$vo.reply.level_icon}"/>
                                    </div>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('user/detail',['user_id'=>$vo.reply.user_id])}">
                                        {$vo.reply|user_name}&nbsp;<if condition="$vo.is_anchor == '1'"><span class="badge" style="line-height:12px;">作者</span></if><br/>
                                        {$vo.reply.phone|str_hide=3,4|default='未绑定'}
                                    </a>
                                </p>
                            </div>
                            </notempty>
                        </td>
                        </notempty>
                        <td>{$vo.content}</td>
                        <td>
                            <if condition="$vo.master_id == '0'">
                            <a href="{:url('video_comment/index',['video_id'=>$vo['video_id'],'master_id'=>$vo['id']])}">{$vo.reply_count}</a><br/>
                            <else/>
                            {$vo.reply_count}
                            </if>
                        </td>
                        <td>{$vo.like_count}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:film_comment:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.is_hot}"
                                 tgradio-on-name="热门" tgradio-off-name="普通"
                                 tgradio-name="is_hot"
                                 tgradio="{:url('video_comment/change_hot_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:film_comment:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.is_delicate}"
                                 tgradio-on-name="精选" tgradio-off-name="普通"
                                 tgradio-name="is_delicate"
                                 tgradio="{:url('video_comment/change_delicate_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:film_comment:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.is_top}"
                                 tgradio-on-name="置顶" tgradio-off-name="普通"
                                 tgradio-name="is_top"
                                 tgradio="{:url('video_comment/change_top_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:film_comment:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.is_sensitive}"
                                 tgradio-on-name="敏感" tgradio-off-name="普通"
                                 tgradio-name="is_sensitive"
                                 tgradio="{:url('video_comment/change_sensitive_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            <if condition="$vo.status == '1'">
                            正常
                            <else/>
                            违规
                            </if>
                        </td>
                        <td>
                            {$vo.create_time|time_format='无','date'}
                        </td>
                        <td>
                            <auth rules="admin:film_comment:update">
                            <a data-id="id:{$vo.id}" poplink="update_box" href="javascript:;">
                            编辑
                            </a><br/>
                            </auth>
                            <auth rules="admin:film_comment:delete">
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
</block>

<block name="layer">
    <include file="video_comment/update"/>
</block>