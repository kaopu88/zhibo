<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/tencentyun/ugcUploader.js?v=__RV__"></script>
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
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">图标</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="icon" value="{$_info.icon}" type="text" class="base_text border_left_radius"/>
                            <a uploader-size="2147483648" uploader-type="image" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_images"
                               uploader-field="icon">上传</a>
                        </div>
                        <div imgview="[name=icon]" style="width: 120px;margin-top: 10px;"><img src=""/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">描述</td>
                    <td>
                                <textarea class="base_textarea" style="height: 150px;"
                                          name="descr">{$_info.descr}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">权重</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort|default=0}"/>
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

    <script>
    </script>

</block>