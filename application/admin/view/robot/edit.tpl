<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:url('edit')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">昵称</td>
                    <td>
                        <input class="base_text" name="nickname" value="{$_info.nickname}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">头像</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="avatar_{$i}" value="{$_info.avatar}" type="text" class="base_text border_left_radius"/>
                            <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="robot_avatar"
                               uploader-field="avatar_{$i}">上传</a>
                        </div>
                        <div imgview="[name=avatar_{$i}]" style="width: 120px;margin-top: 10px;"><img src="{$_info.avatar}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">性别</td>
                    <td>
                        <label class="base_label2"><input value="1" <if condition="$_info['gender'] == '1'">checked</if> type="radio" name="gender"/>男</label>
                        <label class="base_label2"><input value="2" <if condition="$_info['gender'] == '2'">checked</if> type="radio" name="gender"/>女</label>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">生日</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="birthday"
                               value="{$_info.birthday|time_format='','Y-m-d'}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">所在地区</td>
                    <td>
                        <input placeholder="请选择地区" data-fill-path="1" data-min-level="3" data-max-num="1"
                               url="{:url('common/get_region')}" region="[name=area_id]" type="text" readonly
                               class="base_text area_name" value="{$_info.area_id|region_name}">
                        <input type="hidden" name="area_id" value="{$_info.area_id}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['user_id']">
                            <input name="user_id" type="hidden" value="{$_info.user_id}"/>
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
    <script>
        $("[name=birthday]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });
    </script>
</block>