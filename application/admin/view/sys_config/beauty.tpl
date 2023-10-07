<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('beauty')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">美颜配置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">美颜类型</td>
                                    <td>
                                        <select class="base_select" name="beauty_setting[beauty_status]" selectedval="{$_info.beauty_setting.beauty_status ? $_info.beauty_setting.beauty_status  : '0'}">
                                            <option value="2">秉信至尊版</option>
                                            <option value="1">秉信基础版</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="xiangxinkey" style="display: none">
                                    <td class="field_name">IOS KEY</td>
                                    <td>
                                        <input class="base_text" name="beauty_setting[beauty_ios_key]" value=""/>
                                    </td>
                                </tr>

                                <tr class="xiangxinkey"  style="display: none">
                                    <td class="field_name">Android KEY</td>
                                    <td>
                                        <input class="base_text" name="beauty_setting[beauty_android_key]" value=""/>
                                    </td>
                                </tr>

                                <tr class="tuohuankey"  style="display: none">
                                    <td class="field_name">IOS KEY</td>
                                    <td>
                                        <input class="base_text" name="beauty_setting[tuohuan_beauty_ios_key]" value=""/>
                                    </td>
                                </tr>

                                <tr class="tuohuankey" style="display: none" >
                                    <td class="field_name">Android KEY</td>
                                    <td>
                                        <input class="base_text" name="beauty_setting[tuohuan_beauty_android_key]" value=""/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                            </table>
                        </div>

                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" id="modify">修改</a>
                    <a href="javascript:;" class="base_button" ajax="post" id="modify_submit" style="display: none">提交</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        $(".base_select").change(function(){
            var type = $('.base_select option:selected').val();
            if (type == 0) {
                $('.xiangxinkey').hide();
                $('.tuohuankey').hide();
            }
            if (type == 1) {
                $('.xiangxinkey').hide();
                $('.tuohuankey').show();
            }
            if (type == 2) {
                $('.xiangxinkey').show();
                $('.tuohuankey').hide();
            }

            $('#modify').hide();
            $('#modify_submit').show();
        });
        $('#modify').click(function () {
            var type = $('.base_select option:selected').val();
            if (type == 0) {
                $('.xiangxinkey').hide();
                $('.tuohuankey').hide();
            }
            if (type == 1) {
                $('.xiangxinkey').hide();
                $('.tuohuankey').show();
            }
            if (type == 2) {
                $('.xiangxinkey').show();
                $('.tuohuankey').hide();
            }
            $(this).hide();
            $('#modify_submit').show();
        })
    </script>
</block>