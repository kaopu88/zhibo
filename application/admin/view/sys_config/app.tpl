<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>
<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('app')}">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">调试模式</li>
                        <li>系统配置</li>
                        <li>服务部署</li>
                        <li>提现配置</li>
                        <li>创作号申请</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">系统调试模式</td>
                                    <td>
                                        <select class="base_select" name="app_debug" selectedval="{$_info.app_debug ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">系统追踪调试</td>
                                    <td>
                                        <select class="base_select" name="app_trace" selectedval="{$_info.app_trace ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>

                                    </td>

                                </tr>
                                <tr>
                                    <td class="field_name">短信调试模式</td>
                                    <td>
                                        <select class="base_select" name="sms_debug" selectedval="{$_info.sms_debug ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">推送调试模式</td>
                                    <td>
                                        <select class="base_select" name="push_debug" selectedval="{$_info.push_debug ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">ios审核状态</td>
                                    <td>
                                        <select class="base_select" name="ios_debug" selectedval="{$_info.ios_debug}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <span>注：苹果上架涉敏感信息隐藏开关</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">登录权限检查</td>
                                    <td>
                                        <select class="base_select" name="auth_on" selectedval="{$_info.auth_on}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">帐号简称</td>
                                    <td>
                                        <input class="base_text" name="app_setting[account_name]" value="{$_info.app_setting.account_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">帐号前缀</td>
                                    <td>
                                        <input class="base_text" name="app_setting[account_prefix]" value="{$_info.app_setting['account_prefix']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">昵称前缀</td>
                                    <td>
                                        <input class="base_text" name="app_setting[nickname_prefix]" value="{$_info.app_setting['nickname_prefix']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">默认分页条数</td>
                                    <td>
                                        <input class="base_text" name="app_setting[page_limit]" value="{$_info.app_setting.page_limit}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">权限加密TOKEN</td>
                                    <td>
                                        <input class="base_text" name="app_setting[data_auth]" value="{$_info.app_setting.data_auth}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">数据加密TOKEN</td>
                                    <td>
                                        <input class="base_text" name="app_setting[data_token]" value="{$_info.app_setting.data_token}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">定时任务签名TOKEN</td>
                                    <td>
                                        <input class="base_text" name="app_setting[timer_token]" value="{$_info.app_setting.timer_token}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">APP初始化KEY</td>
                                    <td>
                                        <input class="base_text" name="app_setting[app_secret_key]" value="{$_info.app_setting.app_secret_key}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">经验值转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[exp_rate]" value="{$_info.app_setting.exp_rate}"/>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">{:APP_MILLET_NAME}转为{:APP_BEAN_NAME}转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[millet_rate]" value="{$_info.app_setting.millet_rate}"/>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>

                                <tr style="display: none">
                                    <td class="field_name">收费视频时长</td>
                                    <td>
                                        <input class="base_text" name="app_setting[charge_video_duration]" value="{$_info.app_setting.charge_video_duration}"/>
                                        <span>注：单位(秒)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">昵称修改间隔</td>
                                    <td>
                                        <input class="base_text" name="app_setting[renick_limit_time]" value="{$_info.app_setting.renick_limit_time}"/>
                                        <span>注：单位(秒)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">默认用户信用分</td>
                                    <td>
                                        <input class="base_text" name="app_setting[default_credit_score]" value="{$_info.app_setting.default_credit_score}"/>
                                        <span>用户注册初始信用分</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">直播有效时长</td>
                                    <td>
                                        <input class="base_text" name="app_setting[live_effective_time]" value="{$_info.app_setting.live_effective_time}"/>
                                        <span>注：单位(秒)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播失效时长</td>
                                    <td>
                                        <input class="base_text" name="app_setting[loss_after_months]" value="{$_info.app_setting.loss_after_months}"/>
                                        <span>注：单位(月)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播失效{:APP_BEAN_NAME}最小值</td>
                                    <td>
                                        <input class="base_text" name="app_setting[loss_min_bean]" value="{$_info.app_setting.loss_min_bean}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">官方帐号UID</td>
                                    <td>
                                        <input class="base_text"  name="app_setting[helper_id]" value="{$_info.app_setting.helper_id}" style="width: 210px;float: left;margin-right: 15px;"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">测试帐号</td>
                                    <td>
                                        <ul class="json_list test_list"></ul>
                                        <input name="test_user" type="hidden" value="{$_info.test_user|implode=','}"/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">部署方式</td>
                                    <td>
                                        <select class="base_select" name="system_deploy[deploy_mode]" selectedval="{$_info.system_deploy.deploy_mode}">
                                            <option value="single">单机</option>
                                            <option value="colony">集群</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[core_service_url]" value="{$_info.system_deploy.core_service_url}"/>
                                        <span>单机部署尽量配置回环地址127.0.0.1提供服务</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">接口网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[api_service_url]" value="{$_info.system_deploy.api_service_url}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">推送网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[push_service_url]" value="{$_info.system_deploy.push_service_url}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">分享网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[fx_service_url]" value="{$_info.system_deploy.fx_service_url}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">H5 地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[h5_service_url]" value="{$_info.system_deploy.h5_service_url}"/>
                                        <span>APP内H5页面访问地址</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">合作商地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[agent_service_url]" value="{$_info.system_deploy.agent_service_url}"/>
                                        <span>合作商管理地址</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">业务员地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[promoter_service_url]" value="{$_info.system_deploy.promoter_service_url}"/>
                                        <span>业务员管理地址</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">充值服务地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[recharge_service_url]" value="{$_info.system_deploy.recharge_service_url}"/>
                                        <span>用户充值回调服务地址</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">后台地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[erp_service_url]" value="{$_info.system_deploy.erp_service_url}"/>
                                        <span>管理后台</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">淘客API地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[taoke_api_url]" value="{$_info.system_deploy.taoke_api_url}"/>
                                        <span>淘客功能代理接口</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">商城链接地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[mall_url]" value="{$_info.system_deploy.mall_url}"/>
                                        <span>商城链接地址</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">淘客API授权key</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[taoke_api_key]" value="{$_info.system_deploy.taoke_api_key}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">

                                <tr>
                                    <td class="field_name">平台提现</td>
                                    <td>
                                        <select class="base_select" name="cash_setting[cash_on]" selectedval="{$_info.cash_setting.cash_on}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会提现</td>
                                    <td>
                                        <select class="base_select" name="cash_setting[agent_cash_on]" selectedval="{$_info.cash_setting.agent_cash_on}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会结算方式</td>
                                    <td>
                                        <select class="base_select cashtype" name="cash_setting[cash_type]" selectedval="{$_info.cash_setting.cash_type}">
                                            <option value="0">公会结算</option>
                                            <option value="1">平台结算(收益{:APP_MILLET_NAME}结算)</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr id="agentcash" <eq name="_info['cash_setting']['cash_type']" value="1">style ="display:none"</eq>>
                                    <td class="field_name">公会提现类型</td>
                                    <td>
                                        <select class="base_select" name="cash_setting[cash_millet_type]" selectedval="{$_info.cash_setting.cash_millet_type}">
                                            <option value="0">客消{:APP_BEAN_NAME}</option>
                                            <option value="1">收益{:APP_MILLET_NAME}</option>
                                        </select>
                                        <span>只针对公会结算方式为公会结算的时候 以那种收益为准</span>
                                    </td>
                                </tr>


                                <tr>
                                    <td class="field_name">公会结算比例</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_proportion]" value="{$_info.cash_setting.cash_proportion}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会提现单笔手续费</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_fee]" value="{$_info.cash_setting.agent_cash_fee}"/>
                                        <span>RMB后扣除的金额</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会提现单笔税率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_taxes]" value="{$_info.cash_setting.agent_cash_taxes}"/>
                                        <span>扣除税率 如100元 税率0.01 那么扣除 1 RMB</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公会提现最低额度</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_min]" value="{$_info.cash_setting.agent_cash_min}"/>
                                        <span>元</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公会月提现次数</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_monthlimit]" value="{$_info.cash_setting.agent_cash_monthlimit}"/>
                                        <span>次</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">主播提现比率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_rate]" value="{$_info.cash_setting.cash_rate}"/>
                                        <span>主播{:APP_MILLET_NAME}转RMB比率</span>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">用户提现比率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_user_rate]" value="{$_info.cash_setting.cash_user_rate}"/>
                                        <span>用户{:APP_MILLET_NAME}转RMB比率</span>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">单笔手续费</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_fee]" value="{$_info.cash_setting.cash_fee}"/>
                                        <span>{:APP_MILLET_NAME}转RMB后扣除的金额</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">提现税率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_taxes]" value="{$_info.cash_setting.cash_taxes}"/>
                                        <span>扣除{:APP_MILLET_NAME}税率 如100金币 税率0.01 那么扣除 1 RMB</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">提现最低额度</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_min]" value="{$_info.cash_setting.cash_min}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">月提现次数</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_monthlimit]" value="{$_info.cash_setting.cash_monthlimit}"/>
                                    </td>
                                </tr>
                               <tr>
                                    <td class="field_name">积分兑换比率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[exchange_percent]" value="{$_info.cash_setting.exchange_percent}"/>
                                        <span>兑换比率就是多少积分兑换一个金币</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">兑换积分展示</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[exchange_integral]" value="{$_info.cash_setting.exchange_integral}"/>
                                        <span>数字使用‘,’分割</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">认证粉丝数</td>
                                    <td>
                                        <input class="base_text" name="app_setting[creation_fans_num]" value="{$_info.app_setting.creation_fans_num}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">认证原创视频数</td>
                                    <td>
                                        <input class="base_text" name="app_setting[creation_film_num]" value="{$_info.app_setting.creation_film_num}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">认证是否检查举报记录</td>
                                    <td>
                                        <select class="base_select" name="app_setting[creation_report_record]" selectedval="{$_info.app_setting.creation_report_record}">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
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

        new JsonList('.test_list', {
            input: '[name=test_user]',
            btns: ['add', 'remove'],
            max: 10,
            format: 'separate',
            fields: [
                {
                    title: '测试帐号ID',
                    name: 'name',
                    type: 'text',
                    width: 100
                }
            ]
        });


        $(".cashtype").change(function(){
            var type = $('.cashtype option:selected').val();
            if (type == 0) {
                $('#agentcash').show();

            }
            if (type == 1) {
                $('#agentcash').hide();
            }
        });
    </script>

</block>