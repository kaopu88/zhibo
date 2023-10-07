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
        <form action="{:isset($_info['id'])?url('admin/edit'):url('admin/add')}">
            <table class="content_info2 mt_10">
                <empty name="_info['id']">
                    <tr>
                        <td class="field_name">用户名</td>
                        <td>
                            <input placeholder="4-30个英文或数字组合" class="base_text" name="username" value="" />
                        </td>
                    </tr>
                    <else/>
                    <tr>
                        <td class="field_name">用户ID</td>
                        <td>{$_info.id}</td>
                    </tr>
                    <tr>
                        <td class="field_name">用户名</td>
                        <td>
                            <input disabled class="base_text" value="{$_info.username}" />
                        </td>
                    </tr>
                </empty>

                <empty name="_info['id']">
                    <tr>
                        <td class="field_name">真实姓名</td>
                        <td>
                            <input class="base_text" name="realname" value="{$_info.realname}" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">创建密码</td>
                        <td>
                            <input type="password" class="base_text" name="password" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">确认密码</td>
                        <td>
                            <input type="password" class="base_text" name="confirm_password" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">手机号</td>
                        <td>
                            <input class="base_text" name="phone" value="{$_info.phone}" />
                        </td>
                    </tr>
                    <else/>
                    <tr>
                        <td class="field_name">真实姓名</td>
                        <td>
                            <input class="base_text"  disabled value="{$_info.realname}" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">手机号</td>
                        <td>
                            <input disabled class="base_text" value="{$_info.phone}" />
                        </td>
                    </tr>
                </empty>
                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" selected="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">角色分组</td>
                    <td>
                       <div style="max-width: 1000px">
                           <volist name="group_list" id="gp">
                               <label style="display: inline-block;line-height: 30px;">
                                   <input checkedval="{$_info.group_ids}" style="vertical-align: -2px;" type="checkbox" name="group_ids[]" value="{$gp.id}" />&nbsp;{$gp.name}
                               </label>
                           </volist>
                       </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}" />
                        </present>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</block>