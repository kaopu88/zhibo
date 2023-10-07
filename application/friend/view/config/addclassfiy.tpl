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
        <form action="{:isset($_info['id'])?url('editclassfiy'):url('addclassfiy')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name"> 上级分类选择(不选就是顶级分类)</td>
                    <td>
                        <select class="base_select" name="masterid" selectedval="{$_info.masterid}">
                            <option value="">请选择</option>
                            <volist name="_classfiyone" id="classmasterid">
                                <option value="{$classmasterid.value}">{$classmasterid.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">分类名称</td>
                    <td>
                        <input class="base_text" name="child_name" value="{$_info.child_name|default=''}"/>
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

    </script>

</block>