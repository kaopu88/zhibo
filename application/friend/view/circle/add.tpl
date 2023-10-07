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
        <form action="{:isset($_info['circle_id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">发布者id</td>
                    <td>
                        <input class="base_text" name="uid" value="{$_info.uid}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">圈子名称</td>
                    <td>
                        <input class="base_text" name="circle_name" value="{$_info.circle_name}"/>
                    </td>

                </tr>
                <tr>
                    <td class="field_name">圈子描述</td>
                    <td>
                        <textarea name="circle_describe" style="height: 50px;" class="base_textarea"
                                  rows="3">{$_info.circle_describe}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">封面图</td>
                    <td>
                        <div class="base_group" style="float: left;width: 50%;">
                            <input style="width: 60%;float: left;" name="circle_cover_img" value="{$_info.circle_cover_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="circle_cover_img">上传</a>
                        </div>
                        <a rel="thumb" href="{:img_url($_info.circle_cover_img,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                        <div imgview="[name=circle_cover_img]" style="width: 20px;"><img src="{$_info.circle_cover_img}" class="preview"/></div>

                </tr>
                <tr>
                    <td class="field_name">背景图</td>
                    <td>
                        <div class="base_group" style="float: left;width: 50%;">
                            <input style="width: 60%;float: left;" name="circle_background_img" value="{$_info.circle_background_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="circle_background_img">上传</a>
                        </div>
                        <a rel="thumb" href="{:img_url($_info.circle_background_img,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                        <div imgview="[name=circle_background_img]" style="width: 20px;"><img src="{$_info.circle_background_img}" class="preview"/></div>

                </tr>
                <tr>
                    <td class="field_name">审核状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">通过</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">是否推荐</td>
                    <td>
                        <select class="base_select" name="is_recom" selectedval="{$_info.is_recom}">
                            <option value="1">推荐</option>
                            <option value="0">普通</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.ctime|time_format='','Y-m-d H:i'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['circle_id']">
                            <input name="id" type="hidden" value="{$_info.circle_id}"/>
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