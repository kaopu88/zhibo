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
<div dom-key="update_box" class="layer_box update_box pa_10" title="评论信息" popbox-action="{:url('video_comment/update')}"
     popbox-area="520px,400px" popbox-get-data="{:url('video_comment/update')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">内容</td>
            <td>
                <textarea name="content" class="base_textarea" style="height:250px;"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value=""/>
                <div class="base_button sub_btn">保存</div>
            </td>
        </tr>
    </table>
</div>