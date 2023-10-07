<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('sms')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">短信配置</li>
                        <li>线路配置</li>
                        <li>私信配置</li>
                        <li>推送配置</li>
                        <li>客服配置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="aomy_sms[platform]" selectedval="{$_info.aomy_sms.platform}">
                                            <option value="aliyun">阿里云通信</option>
                                            <option value="tencloud">腾讯云通信</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">验证码有效期</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[sms_code_expire]" value="{$_info.aomy_sms.sms_code_expire}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">发送时间间隔</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[sms_code_limit]" value="{$_info.aomy_sms.sms_code_limit}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <div class="content_title2">
                                区域线路
                            </div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Access_id</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][access_id]" value="{$_info.aomy_sms.regional.access_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_secret</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][access_secret]" value="{$_info.aomy_sms.regional.access_secret}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">应用SDK AppID</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][sdk_app_id]" value="{$_info.aomy_sms.regional.sdk_app_id}"/>
                                        <span>如不是腾讯云通信  可不填</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">应用Region</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][region]" value="{$_info.aomy_sms.regional.region}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Endpoint_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][endpoint_name]" value="{$_info.aomy_sms.regional.endpoint_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Sign_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][sign_name]" value="{$_info.aomy_sms.regional.sign_name}"/>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">短信模板</td>
                                    <td>
                                        <ul class="json_list sms_code_scenes_list"></ul>
                                        <input name="sms_code_scenes" type="hidden" value="{$_info.sms_code_scenes}"/>
                                    </td>
                                </tr>-->
                            </table>

                            <div class="content_title2">
                                国际线路
                            </div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Access_id</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][access_id]" value="{$_info.aomy_sms.global.access_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_secret</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][access_secret]" value="{$_info.aomy_sms.global.access_secret}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用SDK AppID</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][sdk_app_id]" value="{$_info.aomy_sms.global.sdk_app_id}"/>
                                        <span>如不是腾讯云通信  可不填</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Region</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][region]" value="{$_info.aomy_sms.global.region}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Endpoint_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][endpoint_name]" value="{$_info.aomy_sms.global.endpoint_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Sign_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][sign_name]" value="{$_info.aomy_sms.global.sign_name}"/>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">短信模板</td>
                                    <td>
                                        <ul class="json_list sms_code_scenes_list"></ul>
                                        <input name="sms_code_scenes" type="hidden" value="{$_info.sms_code_scenes}"/>
                                    </td>
                                </tr>-->
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">安卓私信</td>
                                    <td>
                                        <select class="base_select" name="aomy_private_letter[private_letter_status]" selectedval="{$_info.aomy_private_letter.private_letter_status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">ios私信</td>
                                    <td>
                                        <select class="base_select" name="aomy_private_letter[private_ios_letter_status]" selectedval="{$_info.aomy_private_letter.private_ios_letter_status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="aomy_private_letter[platform]" selectedval="{$_info.aomy_private_letter.platform}">
                                            <option value="yunxin">网易云信</option>
                                            <!--<option value="rongcloud">融云</option>-->
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">APP_KEY</td>
                                    <td>
                                        <input class="base_text" name="aomy_private_letter[app_key]" value="{$_info.aomy_private_letter.app_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">APP_SECRET</td>
                                    <td>
                                        <input class="base_text" name="aomy_private_letter[app_secret]" value="{$_info.aomy_private_letter.app_secret}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <div class="content_title2">服务商选择</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="bxkj_push[platform]" selectedval="{$_info.bxkj_push.platform}">
                                            <option value="umeng">友盟</option>
                                            <!--<option value="jiguang">极光</option>-->
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">Android</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APP_kEY</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][app_key]" value="{$_info.bxkj_push.android['app_key']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">Message_secret</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][message_secret]" value="{$_info.bxkj_push.android['message_secret']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">Master_secret</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][app_master_secret]" value="{$_info.bxkj_push.android['app_master_secret']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">Default_activity</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][default_activity]" value="{$_info.bxkj_push.android['default_activity']}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">IOS</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APP_KEY</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[ios][app_key]" value="{$_info.bxkj_push.ios['app_key']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">App_secret</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[ios][app_master_secret]" value="{$_info.bxkj_push.ios['app_master_secret']}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">统一配置</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">每秒延迟率</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_delay_rate]" value="{$_info.bxkj_push.push_delay_rate}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">延迟最大范围</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_delay_range]" value="{$_info.bxkj_push.push_delay_range}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">最大延迟时间</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_max_delay]" value="{$_info.bxkj_push.push_max_delay}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">默认分片长度</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_section_length]" value="{$_info.bxkj_push.push_section_length}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">撤回时间</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_receipt_period]" value="{$_info.bxkj_push.push_receipt_period}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">客服</td>
                                    <td>
                                        <select class="base_select" id="service"  name="bxkj_customer_service[type]" selectedval="{$_info.bxkj_customer_service.type}" onchange="func(this)">
                                            <option value="0">默认</option>
                                            <option value="1">链接</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr  <if condition="$_info.bxkj_customer_service.type == 0"> style="display: none" </if>  id="type">
                                <td class="field_name">链接</td>
                                <td>
                                    <input class="base_text" name="bxkj_customer_service[link]" value="{$_info.bxkj_customer_service.link}"/>
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
        // new JsonList('.sms_code_scenes_list', {
        //     input: '[name=sms_code_scenes]',
        //     btns: ['add', 'remove'],
        //     max: 50,
        //     fields: [
        //         {
        //             name: 'name',
        //             title: '名称',
        //             type: 'text',
        //             width: 160
        //         },
        //         {
        //             name: 'value',
        //             title: '代码',
        //             type: 'text',
        //             width: 150
        //         },
        //         {
        //             name: 'exists',
        //             title: '手机号是否存在',
        //             type: 'text',
        //             width: 80
        //         },
        //         {
        //             name: 'bind',
        //             title: '是否登录且绑定',
        //             type: 'text',
        //             width: 80
        //         },
        //         {
        //             name: 'main',
        //             title: 'main',
        //             type: 'text',
        //             width: 80
        //         },
        //         {
        //             name: 'sms_tpl',
        //             title: '国内短信模板',
        //             type: 'text',
        //             width: 200
        //         },
        //         {
        //             name: 'g_sms_tpl',
        //             title: '国际短信模板',
        //             type: 'text',
        //             width: 200
        //         }
        //     ]
        // });

        function func(e){
            var vs = $('#service').val();

            if (vs == 0) {
                $("#type").hide();
            } else {
                $("#type").show();
            }
        }

    </script>
</block>