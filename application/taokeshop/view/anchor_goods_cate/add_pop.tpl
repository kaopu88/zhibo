<div class="layer_box reg_box">
    <div class="pa_10">
        <ul class="reg_list">
            <li class="reg_li">
                <label>分类名称：</label>
                <input type="text" name="cate_name" class="base_text"/>
            </li>

            <li class="reg_li">
                <label>绑定用户</label>
                <div class="base_group">
                    <input suggester-value="[name=user_id]" suggester="{:url('admin/user/get_suggests')}" style="width: 209px;" value="" type="text" class="base_text"/>
                    <input type="hidden" name="user_id" value=""/>
                    <a href="javascript:;" class="base_button base_button_gray">选择</a>
                </div>
                <div class="clear"></div>
            </li>

        </ul>
        <div>
            <div class="base_button reg_sub_btn">确定</div>
        </div>
    </div>
</div>