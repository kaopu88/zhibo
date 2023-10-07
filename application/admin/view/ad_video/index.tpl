<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '上架状态',
                    opts: [
                        {name: '下架', value: '0'},
                        {name: '上架', value: '1'}
                    ]
                },
                {
                    name: 'is_ad',
                    title: '广告类型',
                    opts: [
                        {name: 'app广告', value: '1'},
                        {name: '网页广告', value: '2'}
                    ]
                },
                {
                    name: 'audit_status',
                    title: '审核状态',
                    opts: [
                        {name: '处理中', value: '0'},
                        {name: '审核中', value: '1'},
                        {name: '已通过', value: '2'},
                        {name: '未通过', value: '3'},
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
                },
                {
                    name: 'source',
                    title: '视频来源',
                    opts: [
                        {name: '后台上传', value: 'erp'},
                        {name: '用户上传', value: 'user'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/video/index.js?v=__RV__"></script>
    <script src="__VENDOR__/raty/jquery.raty.min.js?v=__RV__"></script>
    <script src="__JS__/video/update.js?v=__RV__"></script>
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
                        <auth rules="admin:film:add">
                            <a class="base_button base_button_s" target="_blank" href="{:url('ad_video/batchadd')}">上传视频</a>
                        </auth>
                        <auth rules="admin:film:change_status">
                            <div ajax="post" ajax-url="{:url('ad_video/change_status',['status'=>'1'])}"
                                 ajax-target="list_id"
                                 class="base_button base_button_s base_button_gray">上架
                            </div>
                            <div ajax="post" ajax-url="{:url('ad_video/change_status',['status'=>'0'])}"
                                 ajax-target="list_id"
                                 class="base_button base_button_s base_button_gray">下架
                            </div>
                            <auth rules="admin:film:delete">
                                <a href="{:url('ad_video/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                            </auth>
                        </auth>
                    </div>
                    <ul class="filter_complex" style="margin-left: 10px;float: left">
                        <li>排序：</li>
                        <li sort-name="complex" sort-by="">
                            <span>综合</span>
                        </li>
                        <li sort-name="play" sort-by="desc">
                            <span>播放量</span>
                            <em></em>
                        </li>
                        <li sort-name="zan" sort-by="desc">
                            <span>点赞量</span>
                            <em></em>
                        </li>
                        <li sort-name="score" sort-by="desc">
                            <span>总评分</span>
                            <em></em>
                        </li>
                        <li sort-name="comment" sort-by="desc">
                            <span>评论量</span>
                            <em></em>
                        </li>
                        <li sort-name="time" sort-by="desc">
                            <span>发布时间</span>
                            <em></em>
                        </li>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="发布者ID、昵称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="视频标题ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="free_status" value="{:input('free_status')}"/>
            <input type="hidden" name="audit_status" value="{:input('audit_status')}"/>
            <input type="hidden" name="visible" value="{:input('visible')}"/>
            <input type="hidden" name="source" value="{:input('source')}"/>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 9%;">视频封面</td>
                <td style="width: 9%;">视频描述</td>
                <td style="width: 9%;">视频属性</td>
                <td style="width: 8%;">商品</td>
                <td style="width: 9%;">发布用户</td>
                <td style="width: 5%;">广告类型</td>
                <td style="width: 5%;">热度</td>
                <td style="width: 9%;">标签</td>
                <td style="width: 8%;">上架状态</td>
                <td style="width: 7%;">审核状态</td>
                <td style="width: 7%;">上传时间</td>
                <td style="width: 7%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <include file="ad_video/advideo_info"/>
                        </td>
                        <td>
                            <notempty name="vo['city_name']">
                                <span class="fc_orange">【{$vo.city_name}】</span>
                            </notempty>
                            <span class="">{$vo.describe}</span>
                            <switch name="vo['visible']">
                                <case value="0"></case>
                                <case value="1"><span title="互关可见" class="icon-users3 fc_gray"></span> </case>
                                <case value="2"><span title="私密视频" class="icon-eye-blocked fc_gray"></span> </case>
                            </switch>
                        </td>
                        <td>
                            宽高：{$vo.width}*{$vo.height}<br/>
                            时长：{$vo.duration_str}<br/>
                            来源：
                            <include file="ad_video/adsource"/>
                            <br/>
                            版权：
                            <eq name="vo['copy_right']" value="0">
                                <span class="fc_green">无标识</span>
                                <else/>
                                <span class="fc_red">有标识</span>
                            </eq>
                            <br/>
                            人评：{$vo.rating}<br/>
                            总分：{$vo.score}
                        </td>
                        <td>
                            <notempty name="vo['goods']">
                                <img src="{$vo['goods']['img']}" style="width: 80px;height: 80px"/>
                                <Br>
                                {$vo['goods']['short_title']}
                                <else/>
                                暂无
                            </notempty>

                        </td>
                        <td>
                            <include file="ad_video/vo_user"/>
                        </td>
                        <td>
                            <if condition="$vo.is_ad == 1">
                                app广告
                            </if>
                            <if condition="$vo.is_ad == 2">
                                网页广告
                            </if>
                        </td>
                        <td>
                            播放：{$vo.play_sum}<br/>
                            下载：{$vo.down_sum}<br/>
                            点赞：{$vo.zan_sum} <span class="fc_gray">({$vo.sco_zan_sum})</span> <br/>
                            评论：{$vo.comment_sum} <span class="fc_gray">({$vo.sco_comment_sum})</span> <br/>
                            人次：{$vo.watch_sum}<br/>
                            完播：{$vo.played_out_sum}<br/>
                            切换：{$vo.switch_sum}
                        </td>
                        <td>
                            <include file="ad_video/total_fee"/>
                        </td>
                        <td>
                            <eq name="vo['visible']" value="2">
                                私密视频
                                <else/>
                                <eq name="vo['status_abnormal']" value="1">
                                    <span class="fc_red">状态异常</span>
                                </eq>
                                <div tgradio-not="{:check_auth('admin:film:change_status')?'0':'1'}" tgradio-on="1"
                                     tgradio-off="0" tgradio-on-name="上架" tgradio-off-name="下架"
                                     tgradio-value="{$vo.status}" tgradio-name="status"
                                     tgradio="{:url('ad_video/change_status',array('id'=>$vo['id']))}"></div>
                            </eq>

                        </td>
                        <td>
                            <include file="components/vo_audit_status"/>
                            <br/>
                            <notempty name="vo['audit_admin']">
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                <else/>
                                未分配
                            </notempty>
                        </td>
                        <td>
                            {$vo.create_time|time_format='无','date'}
                        </td>
                        <td>
                            <auth rules="admin:film_comment:select">
                                <a href="{:url('video_comment/_list',['video_id'=>$vo['id']])}">评论(<notempty name="vo['comment_sum']">{$vo.comment_sum}<else/>0</notempty>)</a><br/>
                            </auth>
                            <auth rules="admin:recommend_content:rec_film">
                                <a poplink="recommend_box" data-query="id={$vo.id}&type=film" href="javascript:;">推荐({$vo.rec_num})</a><br/>
                            </auth>
                            <auth rules="admin:film:update">
                                <a data-id="id:{$vo.id}" poplink="film_audit_update" href="javascript:;">编辑</a><br/>
                            </auth>
                            <auth rules="admin:film:delete">
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
    <include file="ad_video/film_audit_update"/>
    <include file="components/recommend_pop"/>
</block>