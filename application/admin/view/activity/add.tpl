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
        <form action="{:isset($_info['key'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">活动名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name|default=''}"/>
                        <p class="field_tip">h5页面展示的名称</p>
                    </td>
                </tr>


                <tr>
                    <td class="field_name">活动标识</td>
                    <td>
                        <input class="base_text" name="mark" value="{$_info.mark|default=''}"/>
                        <p class="field_tip">唯一标识</p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">活动时间</td>
                    <td>
                        <input placeholder="开始时间" class="base_text" name="start_time" value="{$_info.start_time}" style="width:192px;"/>
                        <input placeholder="结束时间" class="base_text" name="end_time" value="{$_info.end_time}" style="width:192px;"/>
                        <p class="field_tip">默认为长期活动</p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">访问域名</td>
                    <td>
                        <select class="base_select" name="host" selectedval="{$_info.host}">
                            <option value="">自定义（在链接地址中填写完整的网址）</option>
                            <volist name="urls" id="url">
                                <option value="{$url.value}">{$url.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">H5链接</td>
                    <td>
                        <input class="base_text" name="link" value="{$_info.link|default=''}"/>
                        <p class="field_tip">如：/test/check</p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">规则配置</td>
                    <td>
                        <textarea name="data" class="rule">{$_info.rule|default=''}</textarea>
                        <p class="field_tip">JSON格式字符串</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['key']">
                            <input name="key" type="hidden" value="{$_info.key}"/>
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
        $("[name=trigger_time]").flatpickr({
            dateFormat: 'Y-m-d H:i:S',
            enableTime: true,
            enableSeconds: true,
            time_24hr:true
        });

        $(function () {
            checkCycle();
        });

        $('[name=cycle]').changeup(function () {
            checkCycle();
        });

        function checkCycle() {
            var cycle = $('[name=cycle]').val();
            if (cycle != '0' && cycle != '') {
                $('.interval_tr').show();
            } else {
                $('.interval_tr').hide();
            }
        }

    </script>

</block>