<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('third')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">地图配置</li>
                        <!-- <li>搜索服务</li>-->
                        <li>自媒体配置</li>
                        <li>身份认证配置</li>
                        <!-- <li>Node配置</li>
                         <li>MQ配置</li>-->
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="map_setting[platform]" selectedval="{$_info.map_setting.platform}">
                                            <option value="amap">高德地图</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用WEB_KEY</td>
                                    <td>
                                        <input class="base_text" name="map_setting[web_service_key]" value="{$_info.map_setting.web_service_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用JS_KEY</td>
                                    <td>
                                        <input class="base_text" name="map_setting[js_service_key]" value="{$_info.map_setting.js_service_key}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">应用JS秘钥</td>
                                    <td>
                                        <input class="base_text" name="map_setting[js_service_secret]" value="{$_info.map_setting.js_service_secret}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="aomy_search[platform]" selectedval="{$_info.aomy_search.platform}">
                                            <option value="aliyun">阿里开放搜索</option>
                                            <option value="local">自建搜索</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用KEY</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[access_key]" value="{$_info.aomy_search.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用secret</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[secret_key]" value="{$_info.aomy_search.secret_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用region</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[region]" value="{$_info.aomy_search.region}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用host</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[host]" value="{$_info.aomy_search.host}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用key_type</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[key_type]" value="{$_info.aomy_search.key_type}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用debug</td>
                                    <td>
                                        <select class="base_select" name="aomy_search[debug]" selectedval="{$_info.aomy_search.debug ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">搜索应用</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[search_app]" value="{$_info.aomy_search.search_app}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">搜索建议</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[search_suggest]" value="{$_info.aomy_search.search_suggest}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">公众号ID</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_ID" name="media_platform[wx_wap][app_id]" value="{$_info.media_platform['wx_wap']['app_id']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公众号Secret</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_SECRET" name="media_platform[wx_wap][secret_key]" value="{$_info.media_platform['wx_wap']['secret_key']}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">小程序Id</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_ID" name="media_platform[wx_app][app_id]" value="{$_info.media_platform['wx_app']['app_id']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">小程序Secret</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_SECRET" name="media_platform[wx_app][secret_key]" value="{$_info.media_platform['wx_app']['secret_key']}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ/ID</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_ID" name="media_platform[qq][app_id]" value="{$_info.media_platform['qq']['app_id']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">QQ/Key</td>
                                    <td>
                                        <input class="base_text" placeholder="KEY" name="media_platform[qq][secret_key]" value="{$_info.media_platform['qq']['secret_key']}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">认证开关</td>
                                    <td>
                                        <select class="base_select" name="certification_setting[cert_on]" selectedval="{$_info.certification_setting.cert_on}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="certification_setting[platform]" selectedval="{$_info.certification_setting.platform}">
                                            <!--<option value="jd">京东</option>-->
                                            <option value="tx">腾讯</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用secretId</td>
                                    <td>
                                        <input class="base_text" name="certification_setting[app_key]" value="{$_info.certification_setting.app_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用secretKey</td>
                                    <td>
                                        <input class="base_text" name="certification_setting[app_secret]" value="{$_info.certification_setting.app_secret}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务网关地址</td>
                                    <td>
                                        <input class="base_text" name="certification_setting[service_gateway]" value="{$_info.certification_setting.service_gateway}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!--<div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">微信公众号App_id</td>
                                    <td>
                                        <input class="base_text" name="wx_appid" value="{$_info.wx_appid}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信公众号App_secret</td>
                                    <td>
                                        <input class="base_text" name="wx_appsecret" value="{$_info.wx_appsecret}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">微信小程序App_id</td>
                                    <td>
                                        <input class="base_text" name="wxapp_appid" value="{$_info.wxapp_appid}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信小程序App_secret</td>
                                    <td>
                                        <input class="base_text" name="wxapp_secret" value="{$_info.wxapp_secret}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ应用App_id</td>
                                    <td>
                                        <input class="base_text" name="qq_appid" value="{$_info.qq_appid}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">QQ应用Key</td>
                                    <td>
                                        <input class="base_text" name="qq_key" value="{$_info.qq_key}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">微信公众号App_id</td>
                                    <td>
                                        <input class="base_text" name="wx_appid" value="{$_info.wx_appid}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信公众号App_secret</td>
                                    <td>
                                        <input class="base_text" name="wx_appsecret" value="{$_info.wx_appsecret}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">微信小程序App_id</td>
                                    <td>
                                        <input class="base_text" name="wxapp_appid" value="{$_info.wxapp_appid}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信小程序App_secret</td>
                                    <td>
                                        <input class="base_text" name="wxapp_secret" value="{$_info.wxapp_secret}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ应用App_id</td>
                                    <td>
                                        <input class="base_text" name="qq_appid" value="{$_info.qq_appid}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">QQ应用Key</td>
                                    <td>
                                        <input class="base_text" name="qq_key" value="{$_info.qq_key}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->

                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>

            </form>
        </div>
    </div>

</block>