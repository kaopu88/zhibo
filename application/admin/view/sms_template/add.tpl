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
                    <td class="field_name">模板名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">模板ID</td>
                    <td>
                        <input class="base_text" name="code" value="{$_info.code}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">模板类型</td>
                    <td>
                        <input class="base_text" name="type" value="{$_info.type}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">模板内容</td>
                    <td>
                        <textarea name="content" class="base_textarea">{$_info.content}</textarea>
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
</block>