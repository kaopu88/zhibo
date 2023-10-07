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
                <auth rules="admin:article_comment:update">
                    <li>
                        <div ajax="post" ajax-url="{:url('article_comment/topping',['is_top'=>'1'])}"
                             ajax-target="list_id"
                             class="base_button base_button_s base_button_gray">置顶
                        </div>
                    </li>
                    <li>
                        <div ajax="post" ajax-url="{:url('article_comment/topping',['is_top'=>'0'])}"
                             ajax-target="list_id"
                             class="base_button base_button_s base_button_gray">取消置顶
                        </div>
                    </li>
                </auth>
                <auth rules="admin:article_comment:delete">
                    <li><a href="{:url('article_comment/del')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="is_top" type="hidden" class="modal_select_value finder"
                               value="{:input('is_top')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">置顶</li>
                            <li class="modal_select_option" value="0">普通</li>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索用户ID、用户昵称"/>
                    <input name="content" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('content')}" placeholder="评论内容、ID"/>
                    <input name="reply_id" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('reply_id')}" placeholder="回复评论ID"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">评论对象</td>
                <td style="width: 14%;">发表用户</td>
                <td style="width: 18%;">评论内容</td>
                <td style="width: 7%;">点赞</td>
                <td style="width: 6%;">回复</td>
                <td style="width: 8%;">置顶</td>
                <td style="width: 7%;">审核</td>
                <td style="width: 8%;">发表时间</td>
                <td style="width: 7%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td> <include file="article_comment/rel_info"/></td>
                        <td><include file="article_comment/user"/></td>
                        <td>{$vo.content}</td>
                        <td>{$vo.like_num}</td>
                        <td>
                            <a title="查看所有回复"
                               href="{:url('article_comment/index',['reply_id'=>$vo.id,'rel_type'=>$vo.rel_type,'rel_id'=>$vo.rel_id])}">
                                {$vo.reply_num}
                            </a>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:article_comment:update')?'0':'1'}"
                                 tgradio-on-name="置顶" tgradio-off-name="普通" tgradio-value="{$vo.is_top}"
                                 tgradio-name="is_top" tgradio="{:url('topping',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <include file="components/audit_status"/><br/>
                            <notempty name="vo['audit_admin']">
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                <else/>
                                未分配
                            </notempty>
                        </td>
                        <td>{$vo.create_time|time_format}</td>
                        <td>
                            <neq name="vo['is_author']" value="1">
                                <auth rules="admin:article_comment:reply">
                                    <a data-id="reply_id:{$vo.id}" poplink="article_reply_box"
                                       href="javascript:;">回复</a><br/>
                                </auth>
                            </neq>
                            <auth rules="admin:article_comment:delete">
                                <a class="fc_red" ajax-confirm ajax="get"
                                   href="{:url('del',array('id'=>$vo['id']))}">删除</a>
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

<block name="layer">
    <div class="layer_box article_reply_box" title="作者回复" popbox-area="500px,380px"
         popbox-action="{:url('article_comment/reply')}">
        <div class="pa_20">
            <h1 style="font-size: 16px;font-weight: normal;">回复内容:</h1>
            <div class="mt_10">
                <textarea name="content" style="height: 150px;" class="base_textarea"></textarea>
            </div>
            <input type="hidden" name="reply_id" value=""/>
            <div class="base_button mt_10 sub_btn">回复</div>
        </div>
    </div>
</block>