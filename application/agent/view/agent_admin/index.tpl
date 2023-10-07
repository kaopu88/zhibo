<extend name="public:base_nav" />
<block name="css">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <li><a href="{:url('agent_admin/add')}?__JUMP__" class="base_button base_button_s">新增</a> </li>
                <li><a href="{:url('agent_admin/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_red base_button_s">删除</a></li>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索ID、手机号、姓名" />
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td><input type="checkbox" checkall="list_id" /></td>
                    <td>ID</td>
                    <td>用户名</td>
                    <td>真实姓名</td>
                    <td>角色</td>
                    <td>状态</td>
                    <td>最近登录</td>
                    <td>操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}" /></td>
                            <td>{$vo.id}</td>
                            <td>
                                {$vo.username}（{$vo.phone|default='未填写'}）
                            </td>
                            <td>{$vo.realname}</td>
                            <td>
                                <eq name="vo['is_root']" value="1">
                                    <span style="color: orange;">超级管理员</span>
                                    <else/>
                                    <volist name="vo['group_list']" id="group">
                                        <span>{$group.name}</span>&nbsp;
                                    </volist>
                                </eq>
                            </td>
                            <td>
                                <eq name="vo['is_root']" value="1">
                                    <div tgradio="" tgradio-not="1"   tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status"></div>
                                    <else/>
                                    <div tgradio-not="0" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('agent_admin/change_status',array('id'=>$vo['id']))}"></div>
                                </eq>
                            </td>
                            <td>{$vo.login_time|time_before='前'}</td>
                            <td >
                                <eq name="vo['is_root']" value="1">
                                    <else/>
                                    <a href="{:url('agent_admin/edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>
                                    <a class="fc_red" ajax-confirm ajax="get" href="{:url('agent_admin/del',array('id'=>$vo['id']))}">删除</a>
                                </eq>
                            </td>
                        </tr>
                    </volist>
                    <else />
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
        new FinderController('.finder', '');
    </script>
</block>