<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0">
        <include file="components/tab_nav"/>
        <div class="bg_form min_w_unset">
            <form action="{:url('set')}">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">新人特权</li>
                        <li>签到任务</li>
                        <li>拍摄短视频</li>
                        <li>观看短视频</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_status]" selectedval="{$_info.new_people_task_config.is_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_withdraw_brief]" value="{$_info.new_people_task_config.is_withdraw_brief}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">首次最低提现</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[new_first_withdraw]" value="{$_info.new_people_task_config.new_first_withdraw}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_sign_status]" selectedval="{$_info.new_people_task_config.is_sign_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_brief]" value="{$_info.new_people_task_config.sign_brief}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">签到</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">已签</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">文字自定义</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_text][textsign]" style="width: 80px;" value="{$_info.new_people_task_config.sign_text.textsign}"/>
                                        <input class="base_text" name="new_people_task_config[sign_text][textsigned]" style="width: 80px;" value="{$_info.new_people_task_config.sign_text.textsigned}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到周期(天)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_sign_circle]" style="width: 80px;" value="{$_info.new_people_task_config.is_sign_circle}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">{:APP_MILLET_NAME}</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">{:APP_BEAN_NAME}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到提醒奖励奖励</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_warn_reward][millet]" style="width: 80px;" value="{$_info.new_people_task_config.sign_warn_reward.millet}"/>
                                        <input class="base_text" name="new_people_task_config[sign_warn_reward][bean]" style="width: 80px;" value="{$_info.new_people_task_config.sign_warn_reward.bean}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">普通奖励 <br>(自动发放)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_reward][millet]" style="width: 80px;" value="{$_info.new_people_task_config.sign_reward.millet}"/>
                                        <input class="base_text" name="new_people_task_config[sign_reward][bean]" style="width: 80px;" value="{$_info.new_people_task_config.sign_reward.bean}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">奖励递增 <br>(连续签到递增)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_continuity_reward][millet]" style="width: 80px;" value="{$_info.new_people_task_config.sign_continuity_reward.millet}"/>
                                        <input class="base_text" name="new_people_task_config[sign_continuity_reward][bean]" style="width: 80px;" value="{$_info.new_people_task_config.sign_continuity_reward.bean}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <ul id="content">
                                            <volist name="items" id="vo">
                                                <li class="recharge-item" style="padding-top: 10px">
                                                    <span class="input-group-addon">连续签到&nbsp&nbsp&nbsp</span>
                                                    <input style="width: 100px" class="base_text" name="new_people_task_config[sign_day][]" value="{$vo.sign_day}"/>
                                                    <span class="input-group-addon">天&nbsp&nbsp&nbsp奖励{:APP_MILLET_NAME}&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[sign_millet][]" value="{$vo.sign_millet}"/>
                                                    <span class="input-group-addon">&nbsp&nbsp&nbsp奖励{:APP_BEAN_NAME}&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[sign_bean][]" value="{$vo.sign_bean}"/>
                                                    <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>
                                                </li>
                                            </volist>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        
                                            <button class='base_button aa' type='button' onclick="addConsumeItem()">添加一个连续签到奖励</button>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到规则</td>
                                    <td>
                                        <textarea style="width:900px;height:100px;" name="new_people_task_config[rules]" ueditor>{$_info.new_people_task_config.rules}</textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_video_status]" selectedval="{$_info.new_people_task_config.is_video_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_video_brief]" value="{$_info.new_people_task_config.is_video_brief}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">{:APP_MILLET_NAME}</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">{:APP_BEAN_NAME}</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">每日首拍奖励 <br>(审核通过发放)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[vedio_upload_reward][millet]" style="width: 80px;" value="{$_info.new_people_task_config.vedio_upload_reward.millet}"/>
                                        <input class="base_text" name="new_people_task_config[vedio_upload_reward][bean]" style="width: 80px;" value="{$_info.new_people_task_config.vedio_upload_reward.bean}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">累计方式</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[video_add_type]" selectedval="{$_info.new_people_task_config.video_add_type}">
                                            <option value="2">单日</option>
                                            <option selected value="1">永久</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <ul id="video_content">
                                            <volist name="video_items" id="vo">
                                                <li class="recharge-item" style="padding-top: 10px">
                                                    <span class="input-group-addon">累计拍摄&nbsp&nbsp&nbsp</span>
                                                    <input style="width: 100px" class="base_text" name="new_people_task_config[video_num][]" value="{$vo.video_num}"/>
                                                    <span class="input-group-addon">个&nbsp&nbsp&nbsp奖励{:APP_MILLET_NAME}&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[video_millet][]" value="{$vo.video_millet}"/>
                                                    <span class="input-group-addon">&nbsp&nbsp&nbsp奖励{:APP_BEAN_NAME}&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[video_bean][]" value="{$vo.video_bean}"/>
                                                    <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>
                                                </li>
                                            </volist>
                                        </ul>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <button class='base_button aa' type='button' onclick="addVideoItem()" style="margin-left: 0;">添加一个累计拍摄奖励</button>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_watch_video_status]" selectedval="{$_info.new_people_task_config.is_watch_video_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_watch_video_brief]" value="{$_info.new_people_task_config.is_watch_video_brief}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <ul id="watch_video_content">
                                            <volist name="watch_video_items" id="vo">
                                                <li class="recharge-item" style="padding-top: 10px">
                                                    <span class="input-group-addon">累计观看&nbsp&nbsp&nbsp</span>
                                                    <input style="width: 100px" class="base_text" name="new_people_task_config[watch_video_num][]" value="{$vo.watch_video_num}"/>
                                                    <span class="input-group-addon">分钟&nbsp&nbsp&nbsp奖励{:APP_MILLET_NAME}&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_millet][]" value="{$vo.watch_video_millet}"/>
                                                    <span class="input-group-addon">&nbsp&nbsp&nbsp奖励{:APP_BEAN_NAME}&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_bean][]" value="{$vo.watch_video_bean}"/>
                                                    <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>
                                                </li>
                                            </volist>
                                        </ul>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <button class='base_button aa' type='button' onclick="addWatchVideoItem()" style="margin-left: 0;">添加一个时段</button>
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
                                            <span class="base_label" style="width: 92px;text-align: center;">关注数量</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">关注好友</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;"  name="new_people_task_config[followFriends][status]" selectedval="{$_info.new_people_task_config.followFriends.status ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[followFriends][follow]" style="width: 80px;" value="{$_info.new_people_task_config.followFriends.follow}" />
                                        <input class="base_text" name="new_people_task_config[followFriends][money]" style="width: 95px;" value="{$_info.new_people_task_config.followFriends.money}"/>
                                        <input class="base_text" name="new_people_task_config[followFriends][title]" style="width: 208px;" value="{$_info.new_people_task_config.followFriends.title|default='关注数量'}"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[followFriends][type]" selectedval="{$_info.new_people_task_config.followFriends.type  ? '1' : '0'}">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text" hidden name="new_people_task_config[followFriends][task_type]" style="width: 208px;" value="{$_info.new_people_task_config.followFriends.task_type|default='followFriends' }"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">发布数量</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>


                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">发布视频</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[postVideo][status]" selectedval="{$_info.new_people_task_config.postVideo.status  ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[postVideo][attention]" style="width: 80px;" value="{$_info.new_people_task_config.postVideo.attention |default =1}" readonly/>
                                        <input class="base_text" name="new_people_task_config[postVideo][money]" style="width: 95px;" value="{$_info.new_people_task_config.postVideo.money }"/>
                                        <input class="base_text" name="new_people_task_config[postVideo][title]" style="width: 208px;" value="{$_info.new_people_task_config.postVideo.title|default='发布视频' }"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[postVideo][type]" selectedval="{$_info.new_people_task_config.postVideo.type  ? '1' : '0'}">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text"  hidden name="new_people_task_config[postVideo][task_type]" style="width: 208px;" value="{$_info.new_people_task_config.postVideo.task_type|default='postVideo' }"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">观看时长</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">观看视频</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[watchVideo][status]" selectedval="{$_info.new_people_task_config.watchVideo.status  ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[watchVideo][seenum]" style="width: 80px;" value="{$_info.new_people_task_config.watchVideo.seenum }"/>
                                        <input class="base_text" name="new_people_task_config[watchVideo][money]" style="width: 95px;" value="{$_info.new_people_task_config.watchVideo.money }"/>
                                        <input class="base_text" name="new_people_task_config[watchVideo][title]" style="width: 208px;" value="{$_info.new_people_task_config.watchVideo.title |default='观看视频'}"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[watchVideo][type]" selectedval="{$_info.new_people_task_config.watchVideo.type  ? '1' : '0'}">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text" hidden  name="new_people_task_config[watchVideo][task_type]" style="width: 208px;" value="{$_info.new_people_task_config.watchVideo.task_type|default='watchVideo' }"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">分享数量</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">分享视频</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[shareVideo][status]" selectedval="{$_info.new_people_task_config.shareVideo.status  ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[shareVideo][sharenum]" style="width: 80px;" value="{$_info.new_people_task_config.shareVideo.sharenum }"/>
                                        <input class="base_text" name="new_people_task_config[shareVideo][money]" style="width: 95px;" value="{$_info.new_people_task_config.shareVideo.money }"/>
                                        <input class="base_text" name="new_people_task_config[shareVideo][title]" style="width: 208px;" value="{$_info.new_people_task_config.shareVideo.title |default='分享视频'}"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[shareVideo][type]" selectedval="{$_info.new_people_task_config.shareVideo.type  ? '1' : '0'}">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text"  hidden name="new_people_task_config[shareVideo][task_type]" style="width: 208px;" value="{$_info.new_people_task_config.shareVideo.task_type|default='shareVideo' }"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">签到次数</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">多重奖励（','分割)</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到任务</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[dailyLogin][status]" selectedval="{$_info.new_people_task_config.dailyLogin.status  ? '1' : '0'}">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[dailyLogin][sign]" style="width: 80px;" value="{$_info.new_people_task_config.dailyLogin.sign|default =1 }"  readonly/>
                                        <input class="base_text" name="new_people_task_config[dailyLogin][Rewards]" style="width: 95px;" value="{$_info.new_people_task_config.dailyLogin.Rewards }"/>
                                        <input class="base_text" name="new_people_task_config[dailyLogin][title]" style="width: 208px;" value="{$_info.new_people_task_config.dailyLogin.title |default='每日登录'}"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[dailyLogin][type]" selectedval="{$_info.new_people_task_config.dailyLogin.type  ? '1' : '0'}">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>
                                        <input class="base_text" hidden name="new_people_task_config[dailyLogin][task_type]" style="width: 208px;" value="{$_info.new_people_task_config.dailyLogin.task_type|default='dailyLogin' }"/>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>


                </div>
                <div class="base_button_div p_b_20" style="max-width: none;width: 1058px;">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </form>
        </div>
    </div>

    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
    <script>

        function addConsumeItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">连续签到&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="new_people_task_config[sign_day][]" value=""/>';
            html+=' <span class="input-group-addon">天&nbsp&nbsp&nbsp奖励{:APP_MILLET_NAME}&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[sign_millet][]" value=""/>';
            html+=' <span class="input-group-addon">&nbsp&nbsp&nbsp奖励{:APP_BEAN_NAME}&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[sign_bean][]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#content').append(html);
        }

        function removeConsumeItem(obj){
            $(obj).closest('.recharge-item').remove();
        }
        
        function addVideoItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">累计拍摄&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="new_people_task_config[video_num][]" value=""/>';
            html+=' <span class="input-group-addon">个&nbsp&nbsp&nbsp奖励{:APP_MILLET_NAME}&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[video_millet][]" value=""/>';
            html+=' <span class="input-group-addon">&nbsp&nbsp&nbsp奖励{:APP_BEAN_NAME}&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[video_bean][]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#video_content').append(html);
        }
        function addWatchVideoItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">累计观看&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="new_people_task_config[watch_video_num][]" value=""/>';
            html+=' <span class="input-group-addon">分钟&nbsp&nbsp&nbsp奖励{:APP_MILLET_NAME}&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_millet][]" value=""/>';
            html+=' <span class="input-group-addon">&nbsp&nbsp&nbsp奖励{:APP_BEAN_NAME}&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_bean][]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#watch_video_content').append(html);
        }


    </script>
</block>

