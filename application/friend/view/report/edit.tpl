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
                    <td class="field_name">发布者id</td>
                    <td>
                        <input class="base_text" name="uid" value="{$_info.uid}" {:isset($_info['id'])?'readonly':''}/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">举报的id</td>
                    <td>
                        <input class="base_text" name="report_msg_id" value="{$_info.report_msg_id}" {:isset($_info['id'])?'readonly':''}/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">举报类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="">请选择</option>
                            <volist name="_report_type" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">举报的图片链接</td>
                    <td>
                        <input class="base_text" name="report_img" value="{$_info.report_img}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">举报类别</td>
                    <td>
                        <select class="base_select" name="report_type" selectedval="{$_info.report_type}">
                            <option value="">请选择</option>
                            <volist name=":enum_array('friend_msg_report_type')" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">举报内容</td>
                    <td>
                        <textarea name="report_msg" style="height: 50px;" class="base_textarea"
                                  rows="3">{$_info.report_msg}</textarea>
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