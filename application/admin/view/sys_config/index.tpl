<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('index')}">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">网站设置</li>
                        <li>注册设置</li>
                        <li>版权设置</li>
                        <li>对公业务</li>
                        <li>其它设置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">公司简称</td>
                                    <td>
                                        <input class="base_text" name="company_name" value="{$_info.company_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公司全称</td>
                                    <td>
                                        <input class="base_text" name="company_full_name" value="{$_info.company_full_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">网站关键词</td>
                                    <td>
                                        <input class="base_text" name="company_keyword" value="{$_info.company_keyword}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">网站描述</td>
                                    <td>
                                        <input class="base_text" name="company_description" value="{$_info.company_description}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">站点标语</td>
                                    <td>
                                        <input class="base_text" name="site_slogan" value="{$_info.site_slogan}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">联系QQ</td>
                                    <td>
                                        <input class="base_text" name="contact_qq" value="{$_info.contact_qq}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">联系地址</td>
                                    <td>
                                        <input class="base_text" name="contact_address" value="{$_info.contact_address}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">联系电话</td>
                                    <td>
                                        <input class="base_text" name="contact_tel" value="{$_info.contact_tel}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">网站ico</td>
                                    <td>
                                        <input class="base_text default_img" type="file" id="site_ico" name="site_ico" accept=".ico,.png" value="" onchange="uploadImg();"  style="float: left"/>
                                        <div class="default_img" style="float: left">
                                            <img src="{$Think.server.host}/favicon.ico"/>
                                        </div>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">

                                <tr>
                                    <td class="field_name">邀请码</td>
                                    <td>
                                        <select class="base_select" name="invite_code" selectedval="{$_info.invite_code ? $_info.invite_code : '0'}">
                                            <option value="2">必填</option>
                                            <option value="1">选填</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">注册方式</td>
                                    <td>
                                        <select class="base_select" name="register_type" selectedval="{$_info.register_type ? $_info.register_type : '0'}">
                                            <option value="2">邀请码注册</option>
                                            <option value="1">微信授权注册</option>
                                            <option value="0">手机微信注册</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">我的团队</td>
                                    <td>
                                        <select class="base_select" name="team_status" selectedval="{$_info.team_status ? $_info.team_status : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">IDC备案号</td>
                                    <td>
                                        <input class="base_text" name="idc_num" value="{$_info.idc_num}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">文网文备案号</td>
                                    <td>
                                        <input class="base_text" name="nc_num" value="{$_info.nc_num}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">文网文授权链接</td>
                                    <td>
                                        <input class="base_text" name="nc_link" value="{$_info.nc_link}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">网安备案号</td>
                                    <td>
                                        <input class="base_text" name="netc_num" value="{$_info.netc_num}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">开户抬头</td>
                                    <td>
                                        <input class="base_text" name="receipt_name" value="{$_info.receipt_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">开户行卡号</td>
                                    <td>
                                        <input class="base_text" name="receipt_account" value="{$_info.receipt_account}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">银行名称</td>
                                    <td>
                                        <input class="base_text" name="receipt_bank" value="{$_info.receipt_bank}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">微博地址</td>
                                    <td>
                                        <input class="base_text" name="weibo_url" value="{$_info.weibo_url}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">位置纬度</td>
                                    <td>
                                        <input class="base_text" name="location_latitude" value="{$_info.location_latitude}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">位置经度</td>
                                    <td>
                                        <input class="base_text" name="location_longitude" value="{$_info.location_longitude}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">统计代码</td>
                                    <td>
                                        <textarea name="tongji_code" class="base_text" style="height:120px;">{$_info.tongji_code}</textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">小程序二维码</td>
                                    <td>
                                        <div class="base_group" style="float: left;width: 25%;">

                                            <input style="width: 309px;" name="qrcode_wxapp" value="{$_info.qrcode_wxapp}" type="text" class="base_text border_left_radius"/>
                                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images" uploader-field="qrcode_download" style=" float: initial;">上传</a> </div>
                                        <div imgview="[name=qrcode_wxapp]" style="width: 120px;margin-left: 5px;"><img style="height: 40px" src="{$_info.qrcode_wxapp}" class="preview"/></div>
                                </tr>

                                <tr>
                                    <td class="field_name">微信二维码</td>
                                    <td>
                                        <div class="base_group" style="float: left;width: 25%;">
                                            <input style="width: 309px;" name="qrcode_wx" value="{$_info.qrcode_wx}" type="text" class="base_text border_left_radius"/>
                                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images" uploader-field="qrcode_wx" style=" float: initial;">上传</a> </div>
                                        <div imgview="[name=qrcode_wx]" style="width: 120px;margin-left: 5px;"><img style="height: 40px" src="{$_info.qrcode_wx}" class="preview"/></div>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ二维码</td>
                                    <td>
                                        <div class="base_group" style="float: left;width: 25%;">
                                            <input style="width: 309px;" name="qrcode_qq" value="{$_info.qrcode_qq}" type="text" class="base_text border_left_radius"/>
                                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images" uploader-field="qrcode_qq" style=" float: initial;">上传</a> </div>
                                        <div imgview="[name=qrcode_qq]" style="width: 120px;margin-left: 5px;"><img style="height: 40px" src="{$_info.qrcode_qq}" class="preview"/></div>
                                </tr>

                                <tr>
                                    <td class="field_name">苹果商店地址</td>
                                    <td>
                                        <input class="base_text" name="apple_store" value="{$_info.apple_store}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用宝地址</td>
                                    <td>
                                        <input class="base_text" name="qq_store" value="{$_info.qq_store}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">下载二维码</td>
                                    <td>
                                        <div class="base_group" style="float: left;width: 25%;">

                                            <input style="width: 309px;" name="qrcode_download" value="{$_info.qrcode_download}" type="text" class="base_text border_left_radius"/>
                                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images" uploader-field="qrcode_download" style=" float: initial;">上传</a> </div>
                                        <div imgview="[name=qrcode_download]" style="width: 120px;margin-left: 5px;"><img style="height: 40px" src="{$_info.qrcode_download}" class="preview"/></div>
                                </tr>

                                <tr>
                                    <td class="field_name">上架隐藏</td>
                                    <td>
                                        <select class="base_select" name="ios_app_hidden" selectedval="{$_info.ios_app_hidden ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">上架隐藏版本号</td>
                                    <td>
                                        <input class="base_text" name="ios_app_hidden_version" value="{$_info.ios_app_hidden_version}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">android上架隐藏</td>
                                    <td>
                                        <select class="base_select" name="android_app_hidden" selectedval="{$_info.android_app_hidden ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">青少年模式开启/关闭</td>
                                    <td>
                                        <select class="base_select" name="teenager_model_switch" selectedval="{$_info.teenager_model_switch ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">违禁词</td>
                                    <td>
                                        <textarea name="forbidden_words" class="base_text" style="height:120px;">{$forbidden_words}</textarea>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </form>
        </div>
    </div>


    <script>
        function uploadImg() {
            if($("#site_ico").val() != "") {
                var file=$("#site_ico").val();
                var filename=file.substr(file.lastIndexOf("."));
                if(filename !='.png' && filename !='.ico'){
                    alert(filename);
                    return;
                }

                var formData = new FormData();
                formData.append('ico_img', document.getElementById('site_ico').files[0]);

                $.ajax({
                    type: "POST",
                    url:"/admin/sys_config/upload_ico",//后台接口
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    data : formData,  // 文件的id
                    success: function(d){
                        if(d.status == 0) {
                            layer.msg(d.message);
                        } else {
                            layer.msg(d.message)
                        }
                    },
                    error: function () {

                    },
                });
            } else {

            }
        }
    </script>

</block>