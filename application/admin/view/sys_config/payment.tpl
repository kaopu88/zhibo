<extend name="public:base_nav"/>

<block name="body">

    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('payment')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">支付宝</li>
                        <li>微信支付</li>
                        <li>苹果支付</li>

                        <li>公众号</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APPID</td>
                                    <td>
                                        <input class="base_text" name="alipay[app_id]" value="{$_info.alipay.app_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">支付公钥</td>
                                    <td>
                                        <textarea name="alipay[alipay_public_key]" class="base_text" style="height: 120px;">{$_info.alipay.alipay_public_key}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">支付私钥</td>
                                    <td>
                                        <textarea name="alipay[alipay_private_key]" class="base_text" style="height: 120px;">{$_info.alipay.alipay_private_key}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公钥证书</td>
                                    <td>
                                        <div class="flex_start">
                                            <input class="base_text w_307" name="alipay[alipay_public_cert]" value="{$_info.alipay.alipay_public_cert}"/>
                                            <a uploader-type="" href="javascript:;" class="base_button base_button_a uploader_m_r" style="margin-left:1px !important;" uploader="admin_cert" uploader-field="alipay[alipay_public_cert]">上传</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">私钥证书</td>
                                    <td>
                                        <div class="flex_start">
                                            <input class="base_text w_307" name="alipay[alipay_private_cert]" value="{$_info.alipay.alipay_private_cert}"/>
                                            <a uploader-type="" href="javascript:;" class="base_button base_button_a uploader_m_r" style="margin-left:1px !important;" uploader="admin_cert" uploader-field="alipay[alipay_private_cert]">上传</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">编码格式</td>
                                    <td>
                                        <input class="base_text" name="alipay[charset]" value="{$_info.alipay.charset}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">签名算法类型</td>
                                    <td>
                                        <input class="base_text" name="alipay[sign_type]" value="{$_info.alipay.sign_type}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">请求地址</td>
                                    <td>
                                        <input class="base_text" name="alipay[gateway_url]" value="{$_info.alipay.gateway_url}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APPID</td>
                                    <td>
                                        <input class="base_text" name="wxpay[app_id]" value="{$_info.wxpay.app_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">商户号</td>
                                    <td>
                                        <input class="base_text" name="wxpay[mch_id]" value="{$_info.wxpay.mch_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">KEY</td>
                                    <td>
                                        <input class="base_text" name="wxpay[mch_key]" value="{$_info.wxpay.mch_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">APPSECRET</td>
                                    <td>
                                        <input class="base_text" name="wxpay[app_secret]" value="{$_info.wxpay.app_secret}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">支付证书</td>
                                    <td>
                                        <div class="flex_start">
                                            <input class="base_text w_307" name="wxpay[wxpay_public_cert]" value="{$_info.wxpay.wxpay_public_cert}"/>
                                            <a uploader-type="" href="javascript:;" class="base_button base_button_a uploader_m_r" style="margin-left:1px !important;" uploader="admin_cert" uploader-field="wxpay[wxpay_public_cert]" style="margin-right:66%;">上传</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">证书密钥</td>
                                    <td>
                                        <div class="flex_start">
                                            <input class="base_text w_307" name="wxpay[wxpay_private_cert]" value="{$_info.wxpay.wxpay_private_cert}"/>
                                            <a uploader-type="" href="javascript:;" class="base_button base_button_a uploader_m_r" style="margin-left:1px !important;" uploader="admin_cert" uploader-field="wxpay[wxpay_private_cert]" style="margin-right:66%;">上传</a>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">上报数据</td>
                                    <td>
                                        <input class="base_text" name="wxpay[report_levenl]" value="{$_info.wxpay.report_levenl}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">代理服务器IP</td>
                                    <td>
                                        <input class="base_text" name="wxpay[wxpay_proxy_host]" value="{$_info.wxpay.wxpay_proxy_host}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">代理服务器端口</td>
                                    <td>
                                        <input class="base_text" name="wxpay[wxpay_proxy_port]" value="{$_info.wxpay.wxpay_proxy_port}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">沙盒</td>
                                    <td>
                                        <select class="base_select" name="applepay[sandbox]" selectedval="{$_info.applepay.sandbox ? '1' : '0'}">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">沙盒验证地址</td>
                                    <td>
                                        <input class="base_text" name="applepay[sandbox_verify_receipt]" value="{$_info.applepay.sandbox_verify_receipt}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">验证地址</td>
                                    <td>
                                        <input class="base_text" name="applepay[verify_receipt_url]" value="{$_info.applepay.verify_receipt_url}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APPID</td>
                                    <td>
                                        <input class="base_text" name="wxpay_wap[app_id]" value="{$_info.wxpay_wap.app_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">商户号</td>
                                    <td>
                                        <input class="base_text" name="wxpay_wap[mch_id]" value="{$_info.wxpay_wap.mch_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">KEY</td>
                                    <td>
                                        <input class="base_text" name="wxpay_wap[mch_key]" value="{$_info.wxpay_wap.mch_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">APPSECRET</td>
                                    <td>
                                        <input class="base_text" name="wxpay_wap[app_secret]" value="{$_info.wxpay_wap.app_secret}"/>
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
        
    </script>
</block>