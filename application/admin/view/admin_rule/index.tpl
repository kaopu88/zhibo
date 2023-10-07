<extend name="public:base_nav" />
<block name="css">
    <link rel="stylesheet" href="__VENDOR__/jsTree/themes/bugu/style.css?v=__RV__" />
    <style>
        .rule_form{
            display: none;
            padding: 15px;
            overflow-x: hidden;
        }
    </style>
</block>

<block name="js">
    <script src="__VENDOR__/jsTree/jstree.min.js?v=__RV__"></script>
    <script src="__ADMIN__/js/tree_table.js?v=__RV__"></script>
    <script>
        var config={
            item_name:'规则',
            parent_key:'cid',
            get_tree:'{:url("admin_rule/get_tree")}',
            del_url:'{:url("admin_rule/del")}',
            add_url:'{:url("admin_rule/add")}',
            edit_url:'{:url("admin_rule/edit")}',
            sort_url:'{:url("admin_rule/sort")}',
            form_selector:'.rule_form',
            root_selector:'.rule_btns',
            map:['_id:id','name:text','children','state','type'],
            initData:{
                cid:0,
                id:'',
                name:'',
                title:'',
                status:'1',
                type:'1',
                condition:''
            }
        };
    </script>
</block>

<block name="body">

    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns rule_btns">
                <li><a href="javascript:;" class="base_button base_button_s refresh_btn">刷新</a></li>
                <li><a href="javascript:;" class="base_button base_button_s checkall_btn">全选</a></li>
                <auth rules="admin:admin_rule:delete">
                    <li><a href="javascript:;" class="base_button base_button_s del_btn base_button_red">删除</a></li>
                </auth>
                <auth rules="admin:setting:data_update">
                    <li style="line-height: 30px;font-size: 12px;padding-left: 10px;">
                        <a data-to="admin_rule:{$data_v}" class="update_data_version" href="javascript:;">{:$data_v=='last'?'已更新至最新版本':'更新至:'.$data_v}</a>
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
        <div class="rule_box mt_10"></div>
        <script>
            $(function () {
                new TableTreeRule('.rule_box',config);
            });
        </script>
    </div>
</block>


<block name="layer">
    <div class="rule_form">
        <table class="content_info2">
            <tr>
                <td class="field_name">所属分组</td>
                <td><input class="parent_name base_text" type="text" readonly value="" /></td>
            </tr>
            <tr>
                <td class="field_name">规则标题</td>
                <td><input name="title" type="text" class="base_text" id="inputTitle" placeholder="规则标题"></td>
            </tr>
            <tr>
                <td class="field_name">规则名称</td>
                <td><input name="name" type="text" class="base_text" id="inputName" placeholder="规则名称"></td>
            </tr>
            <tr>
                <td class="field_name">启用状态</td>
                <td>
                    <select name="status" class="base_select">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_name">类型</td>
                <td>
                    <select name="type" class="base_select" id="inputType">
                        <option value="1">默认类型</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_name">附加规则</td>
                <td><textarea name="condition" id="inputCondition" style="height: 50px;" class="base_textarea" rows="3"></textarea></td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <input type="hidden" name="id" value="" />
                    <button type="button" class="base_button sub_btn">保存</button>
                </td>
            </tr>
        </table>
    </div>
</block>