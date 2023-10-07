<div class="layer_box user_role_box" dom-key="user_role_box" title="设置用户角色" popbox-action="{:url('user_roler/setting')}" popbox-get-data="{:url('user_roler/get_role_list')}" >
    <div class="pa_10">
        <p style="padding: 10px;">用户：【<span class="role_user_id"></span>】<span class="role_nickname"></span>，请选择下列角色</p>
        <ul class="user_role_list"></ul>
        <input type="hidden" name="user_id" />
    </div>
</div>