<block name="js">
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .field_name {
            width: 100px;
        }
    </style>
</block>
<div dom-key="badge_box" class="layer_box badge_box pa_10" title="礼物角标" popbox-action="{:url('gift/update_badge')}"
     popbox-area="520px,400px" popbox-get-data="{:url('gift/update_badge')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">角标</td>
            <td>
                <div class="base_group">
                    <input style="width: 260px;" name="icon" value="" type="text" class="base_text"/>
                    <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                       class="base_button"
                       uploader="gift_icon"
                       uploader-field="icon">上传</a>
                </div>
                <div imgview="[name=icon]" style="width: 120px;margin-top: 10px;"><img src=""/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="gift_id" value="" class="gift_id"/>
                <div class="base_button sub_btn" style="float: left">保存</div>
            </td>
        </tr>
    </table>
</div>