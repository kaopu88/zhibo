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
                    <td class="field_name">所属类目</td>
                    <td>
                        <div typecl="{:url('article/get_tree')}">
                            <input type="hidden" class="type_val_0" name="pcat_id" value="{$_info.pcat_id}">
                            <input type="hidden" class="type_val_1" name="cat_id" value="{$_info.cat_id}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">文章标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">标识符</td>
                    <td>
                        <input class="base_text" name="mark" value="{$_info.mark}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">外链地址</td>
                    <td>
                        <input class="base_text" name="url" value="{$_info.url}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">文章摘要</td>
                    <td>
                        <textarea placeholder="默认会自动截取文章内容" class="base_textarea"
                                  name="summary">{$_info.summary}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images"
                               uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img src=""/></div>
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
                    <td class="field_name">文章配图</td>
                    <td>
                        <ul class="json_list exhibition_img"></ul>
                        <input name="images" type="hidden" value="{$_info.images}"/>
                        <p class="field_tip">默认从文章内容中提取，优先提取PC版的内容</p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序权重</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">浏览量</td>
                    <td>
                        <input class="base_text" name="pv" value="{$_info.pv|default=0}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">点赞量</td>
                    <td>
                        <input class="base_text" name="like_num" value="{$_info.like_num|default=0}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.release_time|time_format='','Y-m-d H:i'}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">启用状态</td>
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