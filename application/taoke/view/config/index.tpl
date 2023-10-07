<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('index')}">

            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">淘客功能</td>
                    <td>
                        <select class="base_select" name="taoke_swicth" selectedval="{$_info.taoke_swicth ? '1' : '0'}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">淘宝联盟key</td>
                    <td>
                        <input class="base_text" name="taobao_appkey" value="{$_info.taobao_appkey}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">淘宝联盟secret</td>
                    <td>
                        <input class="base_text" name="taobao_appsecret" value="{$_info.taobao_appsecret}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">默认淘宝pid</td>
                    <td>
                        <input class="base_text" name="taobao_pid" value="{$_info.taobao_pid}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">淘宝授权</td>
                    <td>
                        <a uploader-type="" href="javascript:;" class="base_button taobao-auth" style="margin-left: 0;">点击授权</a>
                        <div class="line_height_center">
                            <empty name="taobao_auth">
                                未授权
                                <else/>
                                {$tb_auth_desc}
                            </empty>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">拼多多client</td>
                    <td>
                        <input class="base_text" name="pinduoduo_client" value="{$_info.pinduoduo_client}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">拼多多secret</td>
                    <td>
                        <input class="base_text" name="pinduoduo_secret" value="{$_info.pinduoduo_secret}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">默认拼多多pid</td>
                    <td>
                        <input class="base_text" name="pinduoduo_pid" value="{$_info.pinduoduo_pid}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">拼多多媒体id</td>
                    <td>
                        <input class="base_text" name="pdd_media_id" value="{$_info.pdd_media_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">拼多多授权</td>
                    <td>
                        <a uploader-type="" href="javascript:;" class="base_button pdd-auth" style="margin-left: 0;">点击授权</a>
                        <div class="line_height_center">
                            <empty name="pdd_auth">
                                未授权
                                <else/>
                                {$pdd_auth_desc}
                            </empty>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东联盟key</td>
                    <td>
                        <input class="base_text" name="jingdong_appkey" value="{$_info.jingdong_appkey}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东联盟secret</td>
                    <td>
                        <input class="base_text" name="jingdong_appsecret" value="{$_info.jingdong_appsecret}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东联盟授权key</td>
                    <td>
                        <input class="base_text" name="jingdong_apikey" value="{$_info.jingdong_apikey}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东联盟站点类型</td>
                    <td>
                        <select class="base_select" name="jingdong_site_type" selectedval="{$_info.jingdong_site_type}">
                            <option value="1">网站</option>
                            <option value="2">APP</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">默认京东pid</td>
                    <td>
                        <input class="base_text" name="jingdong_pid" value="{$_info.jingdong_pid}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东联盟账户ID</td>
                    <td>
                        <input class="base_text" name="jingdong_account_id" value="{$_info.jingdong_account_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东联盟站点ID</td>
                    <td>
                        <input class="base_text" name="jingdong_site_id" value="{$_info.jingdong_site_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">好京客key</td>
                    <td>
                        <input class="base_text" name="haojingke_apikey" value="{$_info.haojingke_apikey}"/>
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
            window.addEventListener('message',function(e){
                var access_token = e.data.access_token;
                var refresh_token = e.data.refresh_token;
                var expires_in = e.data.expires_in;
                if(access_token != undefined && refresh_token != undefined && expires_in != undefined) {
                    $.ajax({
                        cache: false,
                        url: "{:url('config/setTaobaoAuth')}",
                        type: "post",
                        data: {
                            'access_token': access_token,
                            'refresh_token': refresh_token,
                            'expires_in': expires_in
                        },
                        success: function (data) {
                            if (data.status == 0) {
                                layer.msg(data.message, {icon: 1});
                                layer.closeAll('iframe');
                            }
                        }
                    });
                }
            },false);
        });

        $('.taobao-auth').click(function() {
            var urlprotocol = document.location.protocol;
            var urlname = "{$api_service_url}";
            var redirect_uri = urlname+"/"+"{:url('home/index/auth')}";
            var url = "http://itaoke.cnibx.com/index.php?g=api&m=bx&a=taobaoAuth&account=113815725&redirect_uri="+redirect_uri;
            layer.open({
                type: 2,
                title: '淘宝高佣授权',
                fixed: false,
                skin: 'layui-layer-rim', //加上边框
                shadeClose: true,
                shade: 0.5,
                maxmin: true, //开启最大化最小化按钮
                area: ['850px', '500px'],
                content: url, //iframe的url
                success: function(layero, index) {
                    var body = layer.getChildFrame('body', index);
                    var access_token = body.find('#access_token').val();
                    var refresh_token = body.find('#refresh_token').val();
                    var expires_in = body.find('#expires_in').val();
                    /*if(access_token != undefined && refresh_token != undefined && expires_in != undefined) {
                        $.ajax({
                            cache: false,
                            url: urlname + "{:url('config/setTaobaoAuth')}",
                            type: "post",
                            data: {
                                'access_token': access_token,
                                'refresh_token': refresh_token,
                                'expires_in': expires_in
                            },
                            success: function (data) {
                                if (data.status == 0) {
                                    layer.msg(data.message, {icon: 1});
                                    layer.close(index);
                                }
                            }
                        });
                    }*/
                }
            });
        });


        $('.pdd-auth').click(function() {
            var urlprotocol = document.location.protocol;
            var urlname = "{$api_service_url}";
            var redirect_uri = urlname+"/"+"{:url('home/index/pddauth')}";
            var url = "http://itaoke.cnibx.com/index.php?g=api&m=bx_pdd&a=ddjbAuth&account=113815725&redirect_uri="+redirect_uri;
            window.open(url);
            /*layer.open({
                type: 2,
                title: '多多进宝授权',
                fixed: false,
                skin: 'layui-layer-rim', //加上边框
                shadeClose: true,
                shade: 0.5,
                maxmin: true, //开启最大化最小化按钮
                area: ['900px', '700px'],
                content: [url, 'no'], //iframe的url
                success: function(layero, index) {
                    var body = layer.getChildFrame('body', index);
                    var access_token = body.find('.access_token').val();
                    var refresh_token = body.find('.refresh_token').val();
                    var expires_in = body.find('.expires_in').val();
                    if(access_token != undefined && refresh_token != undefined && expires_in != undefined) {
                        $.ajax({
                            cache: false,
                            url: urlname + "{:url('config/setPinduoduoAuth')}",
                            type: "post",
                            data: {
                                'access_token': access_token,
                                'refresh_token': refresh_token,
                                'expires_in': expires_in
                            },
                            success: function (data) {
                                if (data.status == 0) {
                                    layer.msg(data.message, {icon: 1});
                                    layer.close(index);
                                }
                            }
                        });
                    }
                }
            });*/
        });

    </script>
</block>