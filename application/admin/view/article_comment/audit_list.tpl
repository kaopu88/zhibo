<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <include file="components/tab_nav"/>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <li>
                    <empty name=":input('audit_status')">
                        <div ajax="post" ajax-url="{:url('article_comment/audit',['audit_status'=>'1'])}"
                             ajax-target="list_id"
                             class="base_button base_button_s">批量通过
                        </div>
                    </empty>
                </li>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索用户ID、用户昵称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">评论对象</td>
                <td style="width: 15%;">发表用户</td>
                <td style="width: 23%;">评论内容</td>
                <td style="width: 10%;">相关时间</td>
                <td style="width: 10%;">审核状态</td>
                <td style="width: 12%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <include file="article_comment/rel_info"/>
                        </td>
                        <td>
                            <include file="article_comment/user"/>
                        </td>
                        <td>{$vo.content}</td>
                        <td>
                            发表：{$vo.create_time|time_format}<br/>
                            审核：{$vo.audit_time|time_format='未审核'}<br/>
                        </td>
                        <td>
                            <include file="components/audit_status"/>
                        </td>
                        <td>
                            <switch name="vo['audit_status']">
                                <case value="0">
                                    <a data-id="id:{$vo.id}" poplink="article_comment_handler" href="javascript:;">审核</a><br/>
                                    <a data-query="id={$vo.id}&type=audit_wxapp_comment" poplink="task_transfer_box"
                                       href="javascript:;">转交</a>
                                </case>
                                <case value="1">
                                </case>
                                <case value="2">
                                    原因：{$vo.reason}
                                </case>
                            </switch>
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
    <include file="article_comment/article_comment_handler"/>
    <include file="components/task_transfer_box"/>
</block>