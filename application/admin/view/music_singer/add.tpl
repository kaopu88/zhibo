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
                    <td class="field_name">歌手名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="avatar" value="{$_info.avatar}" type="text" class="base_text border_left_radius"/>
                            <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="music_singer_avatar"
                               uploader-field="avatar">上传</a>
                        </div>
                        <div imgview="[name=avatar]" style="width: 120px;margin-top: 10px;"><img src="{$_info.avatar}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">性别</td>
                    <td>
                        <select class="base_select" name="gender" selectedval="{$_info.gender}">
                            <option value="1">男</option>
                            <option value="0">女</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">分类</td>
                    <td>
                        <select class="base_select" name="classify" selectedval="{$_info.classify}">
                            <option value="1">男歌手</option>
                            <option value="2">女歌手</option>
                            <option value="0">组合</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">生日</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="birth"
                               value="{$_info.birth}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">地区</td>
                    <td>
                        <input class="base_text" name="country" value="{$_info.country}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">歌手简介</td>
                    <td>
                        <textarea name="intro" class="base_textarea">{$_info.intro}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">语种</td>
                    <td>
                        <select class="base_select" name="languages" selectedval="{$_info.languages}">
                            <option value="华语">华语</option>
                            <option value="欧美">欧美</option>
                            <option value="日语">日语</option>
                            <option value="韩语">韩语</option>
                            <option value="粤语">粤语</option>
                            <option value="东南亚">东南亚</option>
                            <option value="其它">其它</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">第三方id</td>
                    <td>
                        <input class="base_text" name="channel_singer_id" value="{$_info.channel_singer_id}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">歌曲数量</td>
                    <td>
                        <input class="base_text" name="songs_total" value="{$_info.songs_total}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">mv数量</td>
                    <td>
                        <input class="base_text" name="mv_total" value="{$_info.mv_total}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">专辑数量</td>
                    <td>
                        <input class="base_text" name="albums_total" value="{$_info.albums_total}"/>
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
        $("[name=birth]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });
    </script>
</block>