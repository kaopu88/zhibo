<extend name="public:base_nav"/>
<block name="js">
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
        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">标题</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">道具封面</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="cover_icon" value="{$_info.cover_icon}" type="text" class="base_text border_left_radius"/>
                            <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="props_cover_icon"
                               uploader-field="cover_icon">上传</a>
                        </div>
                        <div imgview="[name=cover_icon]" style="width: 120px;margin-top: 10px;"><img src="{$_info.cover_icon}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">道具展示</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="user_icon" value="{$_info.user_icon}" type="text" class="base_text border_left_radius"/>
                            <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="props_user_icon"
                               uploader-field="user_icon">上传</a>
                        </div>
                        <div imgview="[name=user_icon]" style="width: 120px;margin-top: 10px;"><img src="{$_info.user_icon}"/>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">资源包</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="file_url" value="{$_info.file_url}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_resources_props_packages"
                               uploader-field="file_url" uploader-before="packageUploadBefore">上传</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">性质</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="1">小礼物</option>
                            <option value="0">大礼物</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">提示语</td>
                    <td>
                        <textarea name="action_desc" class="base_textarea">{$_info.action_desc}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">描述</td>
                    <td>
                        <textarea name="describe" class="base_textarea">{$_info.describe}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
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