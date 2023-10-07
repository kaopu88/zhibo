<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['cate_id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">分类名称</td>
                    <td>
                        <input class="base_text" name="cate_name" value="{$_info.cate_name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">绑定用户</td>
                    <td>
                        <div class="base_group">
                            <input suggester-value="[name=user_id]" suggester="{:url('admin/user/get_suggests',['taoke_shop'=>1])}" style="width: 309px;" value="{$_info.nickname}" type="text" class="base_text user_id border_left_radius"/>
                            <input type="hidden" name="user_id" value="{$_info.user_id}"/>
                            <a fill-value="[name=user_id]" fill-name=".user_id" layer-open="{:url('admin/user/find', ['taoke_shop'=>1])}" href="javascript:;" class="base_button base_button_gray border_right_radius">选择</a>
                        </div>
                        <div class="clear"></div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort|default=999}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">启用状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['cate_id']">
                            <input name="cate_id" type="hidden" value="{$_info.cate_id}"/>
                            <input name="shop_id" type="hidden" value="{$_info.shop_id}"/>
                        </present>
                        __BOUNCE__
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</block>