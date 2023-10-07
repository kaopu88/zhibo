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
                    <td class="field_name">定时时间</td>
                    <td>
                        <input placeholder="请选择时间" class="base_text" name="trigger_time" value="{$_info.trigger_time}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">循环次数</td>
                    <td>
                        <input class="base_text" name="cycle" value="{$_info.cycle|default=0}"/>
                        <p class="field_tip">整数值，其中：0表示不循环,-1表示无限循环</p>
                    </td>
                </tr>

                <tr style="display: none;" class="interval_tr">
                    <td class="field_name">循环间隔</td>
                    <td>
                        <input class="base_text" name="interval" value="{$_info.interval|default=600}"/>
                        <p class="field_tip">单位：秒，间隔时间不能小于5s</p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">回调类型</td>
                    <td>
                        <select class="base_select" name="method" selectedval="{$_info.method}">
                            <option value="get">GET</option>
                            <option value="post">POST</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">回调域名</td>
                    <td>
                        <select class="base_select" name="host" selectedval="{$_info.host}">
                            <option value="">自定义（在回调地址中填写完整的网址）</option>
                            <volist name="urls" id="url">
                                <option value="{$url.value}">{$url.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">回调地址</td>
                    <td>
                        <input class="base_text" name="url" value="{$_info.url}"/>
                        <p class="field_tip">如：/test/check</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">回调数据</td>
                    <td>
                        <textarea name="data" class="base_textarea">{$_info.data}</textarea>
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