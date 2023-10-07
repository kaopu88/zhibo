<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('product')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">产品信息</li>
                        <li>其它配置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">产品简称</td>
                                    <td>
                                        <input class="base_text" name="product_setting[prefix_name]" value="{$_info.prefix_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">产品全称</td>
                                    <td>
                                        <input class="base_text" name="product_setting[name]" value="{$_info.name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">产品Slogan</td>
                                    <td>
                                        <input class="base_text" name="product_setting[slogan]" value="{$_info.slogan}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">产品logo</td>
                                    <td>
                                        <input class="base_text" name="product_setting[logo]" value="{$_info.logo}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务电话</td>
                                    <td>
                                        <input class="base_text" name="product_setting[service_tel]" value="{$_info.service_tel}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">产品简介</td>
                                    <td>
                                        <textarea class="base_text" name="product_setting[descr]" style="height: 120px;">{$_info.descr}</textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">APP弹框标题</td>
                                    <td>
                                        <input class="base_text" name="product_setting[alert_title]" value="{$_info.alert_title}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">弹框内容</td>
                                    <td>
                                        <textarea class="base_text" name="product_setting[alert_content]" style="height: 120px;">{$_info.alert_content}</textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">隐私政策名称</td>
                                    <td>
                                        <input class="base_text" name="product_setting[login_private_title]" value="{$_info.login_private_title}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">隐私政策跳转链接</td>
                                    <td>
                                        <input class="base_text" name="product_setting[login_private_url]" value="{$_info.login_private_url}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">服务协议名称</td>
                                    <td>
                                        <input class="base_text" name="product_setting[login_service_title]" value="{$_info.login_service_title}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">服务协议跳转链接</td>
                                    <td>
                                        <input class="base_text" name="product_setting[login_service_url]" value="{$_info.login_service_url}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">撤回协议内容</td>
                                    <td>
                                        <textarea class="base_text" name="product_setting[cancel_protocol]" style="height: 120px;">{$_info.cancel_protocol}</textarea>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">邀请人{:APP_BEAN_NAME}奖励</td>
                                    <td>
                                        <input class="base_text" name="product_setting[invite_bean]" value="{$_info.invite_bean}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">邀请人{:APP_MILLET_NAME}奖励</td>
                                    <td>
                                        <input class="base_text" name="product_setting[invite_millet]" value="{$_info.invite_millet}"/>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">邀请人积份奖励</td>
                                    <td>
                                        <input class="base_text" name="product_setting[invite_exp]" value="{$_info.invite_exp}"/>
                                    </td>
                                </tr>-->
                                <tr>
                                    <td class="field_name">邀请注册{:APP_BEAN_NAME}奖奖励</td>
                                    <td>
                                        <input class="base_text" name="product_setting[reg_bean]" value="{$_info.reg_bean}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">邀请注册{:APP_MILLET_NAME}奖奖励</td>
                                    <td>
                                        <input class="base_text" name="product_setting[reg_millet]" value="{$_info.reg_millet}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">货币单位</td>
                                    <td>
                                        <input class="base_text" name="product_setting[bean_name]" value="{$_info.bean_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">收益单位</td>
                                    <td>
                                        <input class="base_text" name="product_setting[millet_name]" value="{$_info.millet_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">积分别名</td>
                                    <td>
                                        <input class="base_text" name="product_setting[reward_name]" value="{$_info.reward_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">现金别名</td>
                                    <td>
                                        <input class="base_text" name="product_setting[cash_name]" value="{$_info.cash_name}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">余额单位</td>
                                    <td>
                                        <input class="base_text" name="product_setting[balance_name]" value="{$_info.balance_name}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">结算</td>
                                    <td>
                                        <input class="base_text" name="product_setting[settlement_name]" value="{$_info.settlement_name}"/>
                                    </td>
                                </tr>


                                <tr>
                                    <td class="field_name">刷新提示语<br>(换行隔开)</td>
                                    <td>
                                        <textarea class="base_text" name="product_setting[refresh_text]" style="height: 220px;">{$_info.refresh_text}</textarea>
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