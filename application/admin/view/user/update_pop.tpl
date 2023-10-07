<block name="js">
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .field_name {
            width: 100px;
        }
    </style>
</block>
<div dom-key="user_update_box" class="layer_box user_update_box pa_10" title="用户信息" popbox-action="{:url('user/update')}"
     popbox-area="520px,480px" popbox-get-data="{:url('user/update')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">头像</td>
            <td>
                <div class="base_group">
                    <input style="width: 308px;" name="avatar" value="" type="text" class="base_text border_left_radius"/>
                    <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                       class="base_button border_right_radius"
                       uploader="avatar"
                       uploader-before="imageUploadBefore"
                       uploader-field="avatar">上传</a>
                </div>
                <div imgview="[name=avatar]" style="width: 120px;margin-top: 10px;"><img src=""/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name">昵称</td>
            <td>
                <input placeholder="" name="nickname" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">性别</td>
            <td>
                <label class="base_label2"><input value="1" type="radio" name="gender" />男</label>
                <label class="base_label2"><input value="2" type="radio" name="gender" />女</label>
            </td>
        </tr>
        <tr>
            <td class="field_name">生日</td>
            <td>
                <input readonly placeholder="默认为当前时间" class="base_text" name="birthday"
                       value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">所在地</td>
            <td>
                <input placeholder="请选择地区" data-fill-path="1" data-min-level="3" data-max-num="1"
                       url="{:url('common/get_region')}" region="[name=area_id]" type="text" readonly
                       class="base_text area_name" value="">
                <input type="hidden" name="area_id" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">签名</td>
            <td>
                <textarea name="sign" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value="" class="user_id"/>
                <div class="base_button sub_btn">保存</div>
            </td>
        </tr>
    </table>
</div>

    <script>
        $("[name=birthday]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });

        function imageUploadBefore(file) {
            var user_id = $('.user_id').val();

            $('[uploader-field=avatar]').attr('uploader-query', 'user_id=' + user_id);
            return true;
        }
    </script>