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
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="icon" value="{$_info.icon}" type="text" class="base_text border_left_radius"/>
                            <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="music_icon"
                               uploader-field="icon">上传</a>
                        </div>
                        <div imgview="[name=icon]" style="width: 120px;margin-top: 10px;"><img src="{$_info.icon}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">推荐</td>
                    <td>
                        <select class="base_select" name="is_recommend" selectedval="{$_info.is_recommend}">
                            <option value="1">是</option>
                            <option value="0">否</option>
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