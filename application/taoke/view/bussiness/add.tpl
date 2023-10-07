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
                    <td class="field_name">分类</td>
                    <td>
                        <select class="base_select" name="cate_id" selectedval="{$_info.cate_id}">
                            <option value="0">无</option>
                            <volist name="cate_list" id="vo">
                                <option value="{$vo.id}">{$vo.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

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
                            <input style="width: 309px;" name="thumb_image" value="{$_info.thumb_image}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="thumb_image">上传</a>
                        </div>
                        <div imgview="[name=thumb_image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">视频</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="video_url" value="{$_info.video_url}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="video" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius" uploader="admin_videos" uploader-field="video_url">上传</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">作者</td>
                    <td>
                        <input class="base_text" name="author" value="{$_info.author|default='admin'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">内容</td>
                    <td>
                        <textarea style="width:900px;height:400px;" name="content" ueditor>{$_info.content}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">浏览次数</td>
                    <td>
                        <input class="base_text" name="view_num" value="{$_info.view_num|default=0}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">点赞次数</td>
                    <td>
                        <input class="base_text" name="like_num" value="{$_info.like_num|default=0}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort|default=999}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">置顶</td>
                    <td>
                        <select class="base_select" name="is_top" selectedval="{$_info.is_top}">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">热门</td>
                    <td>
                        <select class="base_select" name="is_hot" selectedval="{$_info.is_hot}">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
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
                        <div class="base_button_div max_w_auto">
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
