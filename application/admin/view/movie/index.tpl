<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:movie:add">
                    <li><a href="{:url('movie/add')}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="admin:movie:delete">
                    <li><a href="{:url('movie/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder"
                               value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">启用</li>
                            <li class="modal_select_option" value="0">禁用</li>
                        </ul>
                    </div>

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="mv_status" type="hidden" class="modal_select_value finder"
                               value="{:input('mv_status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="0">筹划中</li>
                            <li class="modal_select_option" value="1">预告片</li>
                            <li class="modal_select_option" value="2">热映中</li>
                            <li class="modal_select_option" value="3">已下线</li>
                        </ul>
                    </div>

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、片名"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 25%;">片名</td>
                <td style="width: 10%;">标签</td>
                <td style="width: 6%;">认购</td>
                <td style="width: 6%;">进展</td>
                <td style="width: 8%;">效果</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 6%;">电影状态</td>
                <td style="width: 8%;">启用状态</td>
                <td style="width: 8%;">创建时间</td>
                <td style="width: 8%;">操作</td>
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
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__" class="thumb_img">
                                    <img src="{:img_url($vo['image'],'200_200','thumb')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">
                                        {$vo.title}<br/>{$vo.director}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.tags}</td>
                        <td>
                            <eq name="vo['rec_status']" value="1">
                                <span class="fc_green">已开启</span><br/>
                                {$vo.sales}/{$vo.total}
                                <else/>
                                <span class="fc_red">已关闭</span>
                            </eq>
                        </td>
                        <td>
                            <auth rules="admin:movie_progress:select">
                                <a href="{:url('admin/movie/progress',['mid'=>$vo['id']])}">{$vo.progress_num}&nbsp;></a>
                                <else/>
                                {$vo.progress_num}
                            </auth>
                        </td>
                        <td>
                            点击量: {$vo.pv}<br/>
                            评论量:
                            <auth rules="admin:article_comment:select">
                                <a target="_blank" href="{:url('article_comment/index',['rel_type'=>'movie','rel_id'=>$vo['id']])}">{$vo.comment_num}</a>
                                <else/>
                                {$vo.comment_num}
                            </auth>
                            <br/>
                            点赞量: {$vo.like_num}
                        </td>
                        <td>{$vo.sort}</td>
                        <td>
                            <switch name="vo['mv_status']">
                                <case value="0">
                                    <span class="fc_green">筹划中</span>
                                </case>
                                <case value="1">
                                    <span class="fc_orange">预告片</span>
                                </case>
                                <case value="2">
                                    <span class="fc_red">热映中</span>
                                </case>
                                <case value="3">
                                    <span class="fc_gray">已下线</span>
                                </case>
                            </switch>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:movie:update')?'0':'1'}" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.create_time|time_format}</td>
                        <td>
                            <auth rules="admin:movie:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑资料</a><br/>
                            </auth>
                            <auth rules="admin:movie:raise">
                                <a href="{:url('subscription',array('id'=>$vo['id']))}?__JUMP__">认购设置</a><br/>
                            </auth>
                            <auth rules="admin:movie:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}">删除项目</a>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>