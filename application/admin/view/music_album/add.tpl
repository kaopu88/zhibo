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
                    <td class="field_name">专辑名称</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text" class="base_text border_left_radius"/>
                            <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="music_album_image"
                               uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img src="{$_info.image}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">专辑描述</td>
                    <td>
                        <textarea name="desc" class="base_textarea">{$_info.desc}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">所属歌手</td>
                    <td>
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=singer_id]" suggest="{:url('music_singer/get_suggests')}"
                                   style="width: 309px;" value="{$_info.singer_name}" type="text" class="base_text singer_name border_left_radius">
                            <input type="hidden" name="singer_id" value="{$_info.singer_id}">
                            <a fill-value="[name=singer_id]" fill-name=".singer_name" layer-open="{:url('music_singer/find')}"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择歌手</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">第三方id</td>
                    <td>
                        <input class="base_text" name="channel_album_id" value="{$_info.channel_album_id}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布公司</td>
                    <td>
                        <input class="base_text" name="company" value="{$_info.company}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发表时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.release_time|time_format='','Y-m-d H:i'}"/>
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
        $("[name=release_time]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });
    </script>

</block>