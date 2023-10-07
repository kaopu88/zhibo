<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('live')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">直播配置</li>
                        <li>直播流配置</li>
                        <li>开播配置</li>
                        <li>主播任务</li>
                        <li>申请主播配置</li>
                        <li>开播商城配置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">直播云服务商</td>
                                    <td>
                                        <select class="base_select" name="live_setting[platform]" selectedval="{$_info.platform}">
                                            <option value="tencent">腾讯云</option>
                                            <option value="qiniu">七牛云</option>
                                            <option value="aliyun">阿里云</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">设置头像为封面</td>
                                    <td>
                                        <select class="base_select" name="live_setting[avatar_set_cover]" selectedval="{$_info.avatar_set_cover}">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                        <span>主播开播是否将头像作为直播间封面</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">场控人数</td>
                                    <td>
                                        <input class="base_text" name="live_setting[live_manage_sum]" value="{$_info.live_manage_sum}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">禁言时长</td>
                                    <td>
                                        <input class="base_text" name="live_setting[shutspeak_expire_time]" value="{$_info.shutspeak_expire_time}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 211px;">地址</span>
                                            <span class="base_label" style="width: 201px;">端口</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">消息服务器地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[message_server][host]" style="width: 193px;" value="{$_info.message_server.host}"/>
                                        <input class="base_text" name="live_setting[message_server][port]" style="width: 193px;" value="{$_info.message_server.port}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">游戏服务器地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[game_server][host]" style="width: 193px;" value="{$_info.game_server.host}"/>
                                        <input class="base_text" name="live_setting[game_server][port]" style="width: 193px;" value="{$_info.game_server.port}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">配置服务地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[service_host]" value="{$_info.service_host}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 211px;">最大</span>
                                            <span class="base_label" style="width: 201px;">最小</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">机器人数量</td>
                                    <td>
                                        <input class="base_text" name="live_setting[robot][max]" style="width: 193px;" value="{$_info.robot.max}"/>
                                        <input class="base_text" name="live_setting[robot][min]" style="width: 193px;" value="{$_info.robot.min}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">弹幕价格</td>
                                    <td>
                                        <input class="base_text" name="live_setting[barrage_fee]"  value="{$_info.barrage_fee}"/>
                                        <span>单位：{:APP_BEAN_NAME}</span>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">发言信用分</td>
                                    <td>
                                        <input class="base_text" name="live_setting[credit_score]"  value="{$_info.credit_score}"/>
                                        <span>用户发言低于此分时只有自已和主播可见</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">全区弹幕价格</td>
                                    <td>
                                        <input class="base_text" name="live_setting[horn_fee]"  value="{$_info.horn_fee}"/>
                                        <span>单位：{:APP_BEAN_NAME}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">入房金光等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[rank_golden_light]"  value="{$_info.rank_golden_light}"/>

                                    </td>
                                </tr>-->
                                <tr>
                                    <td class="field_name">发送弹幕等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[barrage_level]"  value="{$_info.barrage_level}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">发送消息等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[message_level]"  value="{$_info.message_level}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">连麦等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[mike_level]"  value="{$_info.mike_level}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">活动背包礼物是否算业绩</td>
                                    <td>
                                        <select class="base_select" name="live_setting[bag_prifit_status]" selectedval="{$_info.bag_prifit_status ? '1' : '0'}">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">是否显示休息主播列表</td>
                                    <td>
                                        <select class="base_select" name="live_setting[is_rest_display]" selectedval="{$_info.is_rest_display ? '1' : '0'}">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">房间主播uid前缀名称</td>
                                    <td>
                                        <input class="base_text" name="live_setting[live_room_name]"  value="{$_info.live_room_name}"/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][secret_id]" value="{$_info.platform_config.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][access_key]" value="{$_info.platform_config.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][secret_key]" value="{$_info.platform_config.secret_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">推流地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][push]" value="{$_info.platform_config.push}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">播流地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][pull]" value="{$_info.platform_config.pull}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">图片Snapshort</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][snapshort]" value="{$_info.platform_config.snapshort}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">流空间名</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][live_space_name]" value="{$_info.platform_config.live_space_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">图片空间名</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][img_space_name]" value="{$_info.platform_config.img_space_name}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">流前缀</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][stream_prefix]" value="{$_info.platform_config.stream_prefix}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">有效期</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][ext]" value="{$_info.platform_config.ext}"/>
                                    </td>
                                </tr>
                                <tr>

                                    <td class="field_name">播流协议</td>
                                    <td>
                                        <select class="base_select" name="live_setting[platform_config][pull_protocol]"
                                                selectedval="{$_info.platform_config.pull_protocol}">
                                            <option value="rtmp">RTMP</option>
                                            <option value="hls">M3U8</option>
                                            <option value="hdl">FLV</option>

                                        </select>
                                    </td>

                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">等级检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_level]" selectedval="{$_info.validate_level ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">等级条件</td>
                                    <td>
                                        <input class="base_text" name="live_setting[validate_level_value]" value="{$_info.validate_level_value}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">黑名单检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_black]" selectedval="{$_info.validate_black ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">禁播检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_banned]" selectedval="{$_info.validate_banned ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">禁播天数</td>
                                    <td>
                                        <input class="base_text" name="live_setting[validate_banned_value]" value="{$_info.validate_banned_value}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">实名认证检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_verified]" selectedval="{$_info.validate_verified ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">开播权限检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_live_status]" selectedval="{$_info.validate_live_status ? '1' : '0'}">
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
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">直播时长</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;"  name="task_setting[live_duration][status]" selectedval="{$_task.live_duration.status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[live_duration][max]" style="width: 80px;" value="{$_task.live_duration.max}"/>
                                        <input class="base_text" name="task_setting[live_duration][min]" style="width: 80px;" value="{$_task.live_duration.min}"/>
                                        <input class="base_text" name="task_setting[live_duration][title]" style="width: 208px;" value="{$_task.live_duration.title}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">点亮次数</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="task_setting[light_num][status]" selectedval="{$_task.light_num.status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[light_num][max]" style="width: 80px;" value="{$_task.light_num.max}"/>
                                        <input class="base_text" name="task_setting[light_num][min]" style="width: 80px;" value="{$_task.light_num.min}"/>
                                        <input class="base_text" name="task_setting[light_num][title]" style="width: 208px;" value="{$_task.light_num.title}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">直播收益</td>
                                    <td>
                                        <select class="base_select"   style="width: 92px;text-align: center;" name="task_setting[gift_profit][status]" selectedval="{$_task.gift_profit.status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[gift_profit][max]" style="width: 80px;" value="{$_task.gift_profit.max}"/>
                                        <input class="base_text" name="task_setting[gift_profit][min]" style="width: 80px;" value="{$_task.gift_profit.min}"/>
                                        <input class="base_text" name="task_setting[gift_profit][title]" style="width: 208px;" value="{$_task.gift_profit.title}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">新增粉丝</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="task_setting[new_fans][status]" selectedval="{$_task.new_fans.status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[new_fans][max]" style="width: 80px;" value="{$_task.new_fans.max}"/>
                                        <input class="base_text" name="task_setting[new_fans][min]" style="width: 80px;" value="{$_task.new_fans.min}"/>
                                        <input class="base_text" name="task_setting[new_fans][title]" style="width: 208px;" value="{$_task.new_fans.title}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">PK胜场</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="task_setting[pk_win_num][status]" selectedval="{$_task.pk_win_num.status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[pk_win_num][max]" style="width: 80px;" value="{$_task.pk_win_num.max}"/>
                                        <input class="base_text" name="task_setting[pk_win_num][min]" style="width: 80px;" value="{$_task.pk_win_num.min}"/>
                                        <input class="base_text" name="task_setting[pk_win_num][title]" style="width: 208px;" value="{$_task.pk_win_num.title}"/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">主播申请</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][front_status]" selectedval="{$_info.user_live.front_status ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">是否审核</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][verify]" selectedval="{$_info.user_live.verify ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播开通方式</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][open_anchor_type]" selectedval="{$_info.user_live.open_anchor_type ? $_info.user_live.open_anchor_type : '0'}">
                                            <option value="0">默认后台开通</option>
                                            <option value="1">带货权限中开通</option>
                                            <option value="2">用户主动申请</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">个人主播申请</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][person_apply]" selectedval="{$_info.user_live.person_apply ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公会主播申请</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][agent_apply]" selectedval="{$_info.user_live.agent_apply ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">开启商城</td>
                                    <td>
                                        <select class="base_select" name="live_setting[is_shop_open]" selectedval="{$_info.is_shop_open ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                        <span>开启后才会有带自营商城的权限</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">开播前添加商品</td>
                                    <td>
                                        <select class="base_select" name="live_setting[is_goods_open]" selectedval="{$_info.is_goods_open ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">最多添加数量</td>
                                    <td>
                                        <input class="base_text" name="live_setting[goods_max_num]" value="{$_info.goods_max_num}"/>
                                    </td>
                                </tr>
                                <tr style="display: none">
                                    <td class="field_name">保存时间</td>
                                    <td>
                                        <input class="base_text" name="live_setting[goods_save_time]"  value="{$_info.goods_save_time}"/>
                                        <span>单位：分钟(添加完没立即开播保存的时间)</span>
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