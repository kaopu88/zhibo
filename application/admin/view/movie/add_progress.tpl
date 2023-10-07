<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name} 【{$movie.title}】</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('edit_progress'):url('add_progress')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text"
                                   class="base_text"/>
                            <a uploader-type="image" href="javascript:;" class="base_button" uploader="movie_thumb"
                               uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img
                                src="{:img_url('','450_253','movie_thumb')}"/></div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="请选择时间,默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.release_time|time_format='','Y-m-d H:i'}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">摘要</td>
                    <td>
                        <textarea class="base_textarea" name="summary">{$_info.summary}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">PC内容</td>
                    <td>
                        <textarea style="width:900px;height:400px;" name="content" ueditor>{$_info.content}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">手机内容</td>
                    <td>
                        <textarea style="height: 200px;" class="base_textarea" name="mobile_content">{$_info.mobile_content}</textarea>
                        <p class="field_tip">可以通过第三方微信编辑器，编辑后复制到这里</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <input name="mid" type="hidden" value="{$movie.id}"/>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
        $("[name=release_time]").flatpickr({
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
        });
    </script>

</block>