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
        <form action="{:isset($_info['id'])?url('edit'):url('release')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">定时时间</td>
                    <td>
                        <input placeholder="请选择时间" class="base_text" name="trigger_time" value="{$_info.trigger_time}"/>
                        <p class="field_tip">默认为立即推送</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">目标用户</td>
                    <td>
                        <select class="base_select" name="group_id" selectedval="{$_info.group_id}">
                            <option value="all">所有用户</option>
                            <option value="all">男性用户</option>
                            <option value="all">女性用户</option>
                            <option value="all">最近登录用户</option>
                            <option value="all">长时间未登录用户</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">推送类型</td>
                    <td>
                        <select class="base_select" name="directly" selectedval="{$_info.directly}">
                            <option value="0">系统通知</option>
                            <option value="1">状态栏push</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">推送标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">内容类型</td>
                    <td>
                        <label><input checkedval="{$_info.content_type}" name="content_type" type="radio" value="url">链接</label>
                        <label><input checkedval="{$_info.content_type}" name="content_type" type="radio" value="text">文本</label>
                    </td>
                </tr>
                <tr class="">
                    <td class="field_name">推送描述</td>
                    <td>
                        <textarea  name="summary" class="base_textarea">{$_info.summary}</textarea>
                    </td>
                </tr>
                <tr class="push_content_text push_content">
                    <td class="field_name">推送文本</td>
                    <td>
                        <textarea style="height: 150px;" name="text" class="base_textarea">{$_info.text}</textarea>
                        <p class="field_tip">支持HTML文本</p>
                    </td>
                </tr>
                <tr class="push_content_url push_content">
                    <td class="field_name">推送链接</td>
                    <td>
                        <input class="base_text" name="url" value="{$_info.url}"/>
                        <p class="field_tip">支持本地协议，如：<br/>
                        视频：{:LOCAL_PROTOCOL_DOMAIN}video_detail?id=10000<br/>
                            直播：{:LOCAL_PROTOCOL_DOMAIN}live_detail?room_id=10000<br/>
                            用户：{:LOCAL_PROTOCOL_DOMAIN}personal?user_id=10000
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <div class="base_button_div" style="max-width: 417px;">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
        $("[name=trigger_time]").flatpickr({
            dateFormat: 'Y-m-d H:i:S',
            enableTime: true,
            enableSeconds: true,
            time_24hr:true
        });
        $('.push_content').hide();
        $(function () {
            checkContentType();
            $('[name=content_type]').change(function () {
                checkContentType();
            });
        });

        function checkContentType(){
            $('.push_content').hide();
            var content_type=$('[name=content_type]:checked').val();
            $('.push_content_'+content_type).show();
        }


    </script>

</block>