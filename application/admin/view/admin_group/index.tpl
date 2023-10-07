<extend name="public:base_nav" />
<block name="js">
</block>
<block name="css">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:admin_group:add">
                    <li><a href="{:url('admin_group/add')}?__JUMP__" class="base_button base_button_s">新增</a> </li>
                </auth>
                <auth rules="admin:admin_group:delete">
                    <li><a href="{:url('admin_group/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
                <auth rules="admin:setting:data_update">
                    <li style="line-height: 30px;font-size: 12px;padding-left: 10px;">
                        <a data-to="admin_group:{$data_v}" class="update_data_version" href="javascript:;">{:$data_v=='last'?'已更新至最新版本':'更新至:'.$data_v}</a>
                    </li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">启用</li>
                            <li class="modal_select_option" value="0">禁用</li>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索ID、名称" />
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
                <td>角色名称</td>
                <td>描述</td>
                <td>拥有权限</td>
                <td>工作内容</td>
                <td>人数</td>
                <td>状态</td>
                <td>创建时间</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td style="width: 5%;"><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}" /></td>
                        <td style="width: 10%;">{$vo.id}</td>
                        <td style="width: 12%;">{$vo.name}</td>
                        <td style="width: 17%;">{$vo.descr}</td>
                        <td style="width: 8%;">{$vo.rules_num}</td>
                        <td style="width: 8%;">{$vo.works_num}</td>
                        <td style="width: 10%;">{$vo.num}</td>
                        <td style="width: 10%;">
                            <div tgradio-not="{:check_auth('admin:admin_group:update')?'0':'1'}" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('admin_group/change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td style="width: 10%;">{$vo.create_time|time_format}</td>
                        <td style="width: 10%;">
                            <auth rules="admin:admin_group:update"><a href="{:url('admin_group/edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a></auth>
                            <auth rules="admin:admin_group:delete"> <a class="fc_red" ajax-confirm ajax="get" href="{:url('admin_group/del',array('id'=>$vo['id']))}">删除</a></auth>
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