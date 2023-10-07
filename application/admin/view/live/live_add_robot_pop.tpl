<div class="live_add_robot pa_10 layer_box" title="分配机器人" popbox-action="{:url('admin/live/addRobot')}"
     popbox-area="350px,250px">
    <table class="content_info2">
        <tr>
            <td>
                数量：<input name="num" type="text" class="base_text" style="width: 120px;">
            </td>
        </tr>
        <tr>
            <td style="padding-left: 40px;">
                <span>请注意变动1个机器人APP端相应放大20倍</span><br />
                <span>为正值时增加机器人,为负值时移除机器人。如(-20)</span>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 40px;">
                <input type="hidden" name="room_id" value=""/>
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>
