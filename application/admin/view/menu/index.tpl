<extend name="public:base_nav"/>
<block name="css">
    <link rel="stylesheet" href="__VENDOR__/jsTree/themes/bugu/style.css?v=__RV__"/>
    <style>
        .menu_form {
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
        var config = {
            item_name: '菜单',
            parent_key: 'pid',
            get_tree: '{:url("get_tree")}',
            del_url: '{:url("del")}',
            add_url: '{:url("add")}',
            edit_url: '{:url("edit")}',
            tip_selector: '.dd_content_tip',
            form_selector: '.menu_form',
            root_selector: '.menu_btns',
            map: ['id', 'name:text', 'children'],
            initData: {
                pid: 0,
                id: '',
                name: '',
                url: '',
                descr: '',
                sort: 0,
                mark: '',
                status: '1',
                display: '1',
                target: '_self',
                badge: '',
                rules: '',
                icon: ''
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
            <ul class="content_toolbar_btns menu_btns">
                <li><a href="javascript:;" class="base_button base_button_s refresh_btn">刷新</a></li>
                <li><a href="javascript:;" class="base_button base_button_s checkall_btn">全选</a></li>
                <auth rules="admin:menu:add">
                    <li><a href="javascript:;" class="base_button base_button_s add_btn">新增</a></li>
                </auth>
                <auth rules="admin:menu:delete">
                    <li><a href="javascript:;" class="base_button base_button_s del_btn base_button_red">删除</a></li>
                </auth>
                <auth rules="admin:setting:data_update">
                    <li style="line-height: 30px;font-size: 12px;padding-left: 10px;">
                        <a data-to="menu:{$data_v}" class="update_data_version" href="javascript:;">{:$data_v=='last'?'已更新至最新版本':'更新至:'.$data_v}</a>
                    </li>
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
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、名称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="menu_box mt_10"></div>
    </div>
    <script>
        $(function () {
            new TableTree('.menu_box', config);
        });
    </script>

</block>

<block name="layer">
    <div class="menu_form">
        <table class="content_info2">
            <tr>
                <td class="field_name">上级菜单</td>
                <td><input class="parent_name base_text" type="text" readonly value=""/></td>
            </tr>
            <tr>
                <td class="field_name">菜单名称</td>
                <td><input name="name" type="text" class="base_text" placeholder="菜单名称"></td>
            </tr>
            <tr>
                <td class="field_name">URL地址</td>
                <td><input name="url" type="text" class="base_text" placeholder="URL地址"></td>
            </tr>
            <tr>
                <td class="field_name">URL参数</td>
                <td><input name="param" type="text" class="base_text" placeholder="URL参数"></td>
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
                <td class="field_name">打开目标</td>
                <td><input name="target" type="text" class="base_text" placeholder="打开目标" value="_self"></td>
            </tr>
            <tr>
                <td class="field_name">标识符</td>
                <td><input name="mark" type="text" class="base_text" placeholder="标识符"></td>
            </tr>
            <tr>
                <td class="field_name">权限规则</td>
                <td><input name="rules" type="text" class="base_text" placeholder="权限规则"></td>
            </tr>
            <tr>
                <td class="field_name">描述</td>
                <td>
                    <textarea name="descr" style="height: 50px;" class="base_textarea" rows="3"></textarea>
                </td>
            </tr>
            <tr>
                <td class="field_name">排序</td>
                <td>
                    <input name="sort" type="text" class="base_text" placeholder="排序">
                </td>
            </tr>
            <tr>
                <td class="field_name">图标样式</td>
                <td>
                    <input name="icon" type="text" class="base_text" placeholder="图标样式">
                </td>
            </tr>
            <tr>
                <td class="field_name">徽章名称</td>
                <td>
                    <input name="badge" type="text" class="base_text" placeholder="徽章名称">
                </td>
            </tr>
            <tr>
                <td class="field_name">显示状态</td>
                <td>
                    <select name="display" class="base_select">
                        <option value="1">显示</option>
                        <option value="0">隐藏</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <input type="hidden" name="id" value=""/>
                    <input type="hidden" name="pid" value=""/>
                    <div class="base_button_div max_w_412">
                        <button type="button" class="base_button sub_btn">保存</button>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</block>