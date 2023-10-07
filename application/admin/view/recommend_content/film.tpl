<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'rec_id',
                    title: '推荐位',
                    get: '{:url("recommend_content/get_rec",array('type'=>'film'))}'
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
                        <a href="{:url('del_recommend')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">取消推荐</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="发布者ID、昵称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="视频标题ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="rec_id" value="{$get.rec_id}" />
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">推荐位</td>
                <td style="width: 10%;">视频封面</td>
                <td style="width: 15%;">视频描述</td>
                <td style="width: 8%;">视频属性</td>
                <td style="width: 15%;">发布用户</td>
                <td style="width: 7%;">热度</td>
                <td style="width: 8%;">标签</td>
                <td style="width: 7%;">排序</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.rc_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.rc_id}"/></td>
                        <td>{$vo.rc_id}</td>
                        <td>{$vo.rs_name}</td>
                        <td>
                            <include file="video/video_info"/>
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
                            <include file="video/source"/>
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
                            <include file="video/vo_user"/>
                        </td>
                        <td>
                            播放：{$vo.play_sum}<br/>
                            点赞：{$vo.zan_sum}<br/>
                            评论：{$vo.comment_sum}<br/>
                            人次：{$vo.watch_sum}<br/>
                        </td>
                        <td>
                            <include file="video/total_fee"/>
                        </td>
                        <td>{$vo.sort}</td>
                        <td>
                            <a data-query="id={$vo.rc_id}&sort={$vo.sort}" poplink="sort_handler"
                               href="javascript:;">修改排序</a><br/>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('del_recommend',array('id'=>$vo['rc_id']))}?__JUMP__">取消推荐</a>
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
    <include file="recommend_content/sort_handler"/>
    <include file="video/film_audit_update"/>
    <include file="components/recommend_pop"/>
</block>