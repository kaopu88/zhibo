<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'audit_status',
                    title: '状态',
                    opts: [
                        {name: '待审核', value: '0'},
                        {name: '已通过', value: '1'},
                        {name: '未通过', value: '2'}
                    ]
                },
                {
                    name: 'target_type',
                    title: '举报对象',
                    auto_sub: false,
                    opts: [
                        {name: '用户', value: 'user'},
                        {name: '短视频', value: 'film'},
                        {name: '评论', value: 'comment'}
                    ]
                },
                {
                    name: 'cid',
                    parent: 'target_type',
                    title: '举报类型',
                    get: '{:url("complaint/get_category")}'
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
                    <div class="filter_search">
                        <input type="text" name="user_id" value="{:input('user_id')}" placeholder="举报人ID"/>
                        <input type="text" name="to_uid" value="{:input('to_uid')}" placeholder="被举报人ID"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="audit_status" value="{$get.audit_status}" />
            <input type="hidden" name="target_type" value="{$get.target_type}" />
            <input type="hidden" name="cid" value="{$get.cid}" />
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">举报人</td>
                <td style="width: 15%;">被举报人</td>
                <td style="width: 15%;">举报对象</td>
                <td style="width: 20%;">举报类型</td>
                <td style="width: 15%;">处理描述</td>
                <td style="width: 15%;">审核状态</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <div class="thumb">
                                <a href="{:url('user/detail',['user_id'=>$vo.to_user.user_id])}"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['to_user']['avatar'],'200_200','avatar')}"/>
                                    <div class="thumb_level_box">
                                        <img title="{$vo.to_user.level_name}" src="{$vo.to_user.level_icon}"/>
                                    </div>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('user/detail',['user_id'=>$vo.to_user.user_id])}">
                                        <eq name="vo['to_user']['isvirtual']" value="1">
                                            <span class="fc_red">[虚拟号]</span><br/>
                                        </eq>
                                        {$vo.to_user|user_name}<br/>
                                        {$vo.to_user.phone|default='未绑定'}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            【{$vo.target_type_name}】
                            <switch name="vo['target_type']">
                                <case value="user">
                                    <a href="{:url('user/detail',['user_id'=>$vo.target_info.user_id])}">{$vo.target_info|user_name}</a>
                                </case>
                                <case value="comment"><a href=""><if condition="$vo.target_info.content != ''">{$vo.target_info.content}<else/>该评论已删除</if></a>
                                </case>
                                <case value="film">
                                    <a href="">
                                        <div class="thumb">
                                            <a layer-title="0" layer-area="414px,779px"
                                               layer-open="{:url('video/tcplayer',['id'=>$vo.target_info.id])}" href="javascript:;"
                                               class="thumb_img">
                                                <img src="{:img_url($vo.target_info['cover_url'],'120_68','film_cover')}"/>
                                            </a>
                                            <p class="thumb_info">
                                                <a target="_blank" href="{:url('video/detail',['id'=>$vo.target_info.id])}">{$vo.target_info.title}</a>
                                            </p>
                                        </div>
                                    </a>
                                </case>
                                <case value="music"><a href="javascript:;"><span class="icon-music" style="margin-right: 3px;"></span>{$vo.target_info.title|short=15}</a>
                                </case>
                            </switch>
                        </td>
                        <td>
                            <if condition="$vo.cinfo.name != ''">
                                {$vo.cinfo.name}
                            <else/>
                                {$vo.content}
                            </if>
                        </td>
                        <td>
                            <switch name="vo['audit_status']">
                                <case value="0">
                                    待审核
                                </case>
                                <case value="1"><a href="javascript:;" class="fc_green">已通过</a>
                                </case>
                                <case value="2">
                                    <a href="javascript:;" class="fc_red">未通过</a>
                                </case>
                            </switch>
                            <br/><notempty name="vo['audit_admin']">
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                <else/>
                                未分配
                            </notempty>
                            <if condition="$vo.audit_status != '0'">
                                <br/>处理详情：{$vo.handle_desc}
                            </if>
                        </td>
                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            处理：<if condition="$vo.handle_time != '0'">{$vo.handle_time|time_format='未处理'}<else/>未处理</if>
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
        $(function () {
            new SearchList('.filter_box',myConfig);

        });
    </script>

</block>

<block name="layer">
    <include file="recharge_app/recharge_app_handler"/>
    <include file="components/task_transfer_box"/>
</block>