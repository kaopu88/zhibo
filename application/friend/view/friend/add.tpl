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
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">消息类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="">请选择</option>
                            <volist name="friend_msg_types" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">扩展标签</td>
                    <td>
                        <select class="base_select" name="extend_type" selectedval="{$_info.extend_type}">
                            <option value="">请选择</option>
                            <volist name="friend_msg_extend_type" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布者id</td>
                    <td>
                        <input class="base_text" name="uid" value="{$_info.uid}" {:isset($_info['id'])?'readonly':''}/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">文章话题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">文章标题</td>
                    <td>
                        <input class="base_text" name="dynamic_title" value="{$_info.dynamic_title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">声频</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="audio" value="{$_info.video}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="video" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_videos"
                               uploader-field="audio">上传</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">接收着</td>
                    <td>
                        <select class="base_select" name="msg_type" selectedval="{$_info.msg_type}">
                            <option value="">请选择</option>
                            <volist name=":enum_array('friend_msg_accept_types')" id="msg_type">
                                <option value="{$msg_type.value}">{$msg_type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">地点坐标</td>
                    <td>
                        <input class="base_text" name="location" value="{$_info.location}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">视频短片</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="video" value="{$_info.video}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="video" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_videos"
                               uploader-field="video">上传</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">文章内容</td>
                    <td>
                        <textarea name="content" style="height: 50px;" class="base_textarea"
                                  rows="3">{$_info.content}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">文章配图</td>
                    <td>
                        <ul class="json_list exhibition_img"></ul>
                        <input name="images" type="hidden" value="{$_info.picture}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">评论量</td>
                    <td>
                        <input class="base_text" name="comment_num" value="{$_info.comment_num|default=0}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">点赞量</td>
                    <td>
                        <input class="base_text" name="like_num" value="{$_info.like|default=0}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.create_time|time_format='','Y-m-d H:i'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">是否推荐</td>
                    <td>
                        <select class="base_select" name="is_recommend" selectedval="{$_info.is_recommend}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
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
                    <td class="field_name"></td>
                    <td>
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

        new JsonList('.json_list', {
            input: '[name=images]',
            btns: ['up', 'down', 'add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    name: 'img',
                    title: '图片',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'admin_images'
                    }
                }
            ]
        });
    </script>

</block>