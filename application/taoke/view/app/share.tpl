<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>

        <form action="{:url('share')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">分享二维码类型</td>
                    <td>
                        <select class="base_select" name="qrcode_type" selectedval="{$_info.qrcode_type}" id="qrcode-type">
                            <option value="1">纯文字</option>
                            <option value="2">本站页面</option>
                            <option value="3">快站页面</option>
                        </select>
                    </td>
                </tr>

                <tr class="kouling-text">
                    <td class="field_name">分享口令文案</td>
                    <td>
                        <textarea name="kouling_text" style="width:300px;height:100px;">{$_info.kouling_text}</textarea>
                        $淘口令$为替换文本
                    </td>
                </tr>

                <tr>
                    <td class="field_name">分享图片类型</td>
                    <td>
                        <input type="radio" name="share_type" {if codition="$_info.share_type eq 1"} checked="checked"{/if} value="1">
                        <img src="__NEWSTATIC__/taoke/img/share1.png" style="width: 100px;"/>
                    </td>
                </tr>

            </table>

            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>
    <script>
        $(function(){
            var type = "{$_info.qrcode_type ? $_info.qrcode_type : 1}";
            if(type == 1){
                $(".kouling-text").show();
            }else{
                $(".kouling-text").hide();
            }
        });

        $("#qrcode-type").change(function(){
            var value = $("#qrcode-type").val();
            if(value == 1){
                $(".kouling-text").show();
            }else{
                $(".kouling-text").hide();
            }
        });
    </script>
</block>