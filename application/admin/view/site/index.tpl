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
            <ul class="content_toolbar_btns menu_btns">
                <li><a href="javascript:;" class="base_button base_button_s refresh_btn">刷新</a></li>
                <li><a href="javascript:;" class="base_button base_button_s checkall_btn">全选</a></li>
                <auth rules="admin_menu_add">
                    <li><a href="javascript:;" class="base_button base_button_s add_btn">新增</a></li>
                </auth>
                <auth rules="admin_menu_delete">
                    <li><a href="javascript:;" class="base_button base_button_s del_btn base_button_red">删除</a></li>
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
    </div>
</block>