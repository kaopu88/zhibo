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
                    <td class="field_name">作者名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">作者简介</td>
                    <td>
                        <textarea name="content" style="height: 50px;" class="base_textarea"
                                  rows="3">{$_info.content}</textarea>
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
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.ctime|time_format='','Y-m-d H:i'}"/>
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