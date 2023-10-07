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
                    <td class="field_name">音乐链接</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="link" value="{$_info.link}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="music"
                               uploader-field="link">上传</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">歌词链接</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="lrc_link" value="{$_info.lrc_link}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="music_lrc"
                               uploader-field="lrc_link">上传</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">音乐标题</td>
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
                               uploader="music_image"
                               uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img src="{$_info.image}"/>
                        </div>
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
                    <td class="field_name">音乐描述</td>
                    <td>
                        <textarea name="desc" class="base_textarea">{$_info.desc}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">原创</td>
                    <td>
                        <label class="base_label2"><input value="0" type="radio" name="is_original" <if condition="$_info['is_original'] == 0">checked</if> />否</label>
                        <label class="base_label2"><input value="1" type="radio" name="is_original" <if condition="$_info['is_original'] == 1">checked</if> />是</label>
                    </td>
                </tr>
                <tr class="choose_user" style="display: {$display}">
                    <td class="field_name"></td>
                    <td class="">
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=user_id]" suggest="{:url('user/get_suggests')}"
                                   style="width: 309px;" value="{$_info.user_name}" type="text" class="base_text user_name border_left_radius">
                            <input type="hidden" name="user_id" value="{$_info.user_id}">
                            <a fill-value="[name=user_id]" fill-name=".user_name" layer-open="{:url('user/find')}"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择用户</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">所属歌手</td>
                    <td>
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=singer_id]" suggest="{:url('music_singer/get_suggests')}"
                                   style="width: 309px;" value="{$_info.singer}" type="text" class="base_text singer border_left_radius" name="singer">
                            <input type="hidden" name="singer_id" value="{$_info.singer_id}">
                            <a fill-value="[name=singer_id]" fill-name=".singer" layer-open="{:url('music_singer/find')}"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择歌手</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">所属专辑</td>
                    <td>
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=album_id]" suggest="{:url('music_album/get_suggests')}"
                                   style="width: 309px;" value="{$_info.singer}" type="text" class="base_text album border_left_radius" name="album">
                            <input type="hidden" name="album_id" value="{$_info.album_id}">
                            <a fill-value="[name=album_id]" fill-name=".album" layer-open="{:url('music_album/find')}"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择专辑</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">所属分类</td>
                    <td>
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=category_id]" suggest="{:url('music_category/get_suggests')}"
                                   style="width: 309px;" value="{$_info.singer}" type="text" class="base_text category border_left_radius">
                            <input type="hidden" name="category_id" value="{$_info.category_id}">
                            <a fill-value="[name=category_id]" fill-name=".category" layer-open="{:url('music_category/find')}"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择分类</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布公司</td>
                    <td>
                        <input class="base_text" name="company" value="{$_info.company}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">歌曲标签</td>
                    <td>
                        <input class="base_text" name="tag" value="{$_info.tag}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">第三方id</td>
                    <td>
                        <input class="base_text" name="channel_file_id" value="{$_info.channel_file_id}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">使用量</td>
                    <td>
                        <input class="base_text" name="use_num" value="{$_info.use_num}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">收藏量</td>
                    <td>
                        <input class="base_text" name="collect_num" value="{$_info.collect_num}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">歌词举报次数</td>
                    <td>
                        <input class="base_text" name="lrc_report" value="{$_info.lrc_report}"/>
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
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">上架</option>
                            <option value="0">下架</option>
                        </select>
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
        $("input[type='radio'][name='is_original']").click(function(){
            var is_original = $("input[type='radio'][name='is_original']:checked").val();
            if(is_original==1){
                $('.choose_user').show();
            }
            if(is_original==0){
                $('.choose_user').hide();
            }
        })
        $("[name=release_time]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });
    </script>

</block>