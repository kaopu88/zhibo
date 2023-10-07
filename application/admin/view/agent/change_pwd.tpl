<extend name="public:base_nav" />
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
        <form action="{:url('admin/change_pwd')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">旧密码</td>
                    <td><input type="password" class="base_text" name="old_password" value="" /></td>
                </tr>
                <tr>
                    <td class="field_name">新密码</td>
                    <td><input type="password" class="base_text" name="password" value="" /></td>
                </tr>
                <tr>
                    <td class="field_name">确认密码</td>
                    <td><input type="password" class="base_text" name="confirm_password" value="" /></td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        __BOUNCE__
                        <div ajax="post" class="base_button">修改</div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</block>