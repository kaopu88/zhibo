<extend name="public:base_nav"/>
<block name="css">
    <style>
        .distribute_reward{
            display:none;
        }
    </style>
</block>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('shop')}">
            <table class="content_info2 mt_10 tab_flex table_fixed" style="min-width:1157px;">
                <tr>
                    <td class="field_name">开店功能</td>
                    <td>
                        <select class="base_select" name="user_shop" selectedval="{$_info.user_shop ? '1' : '0'}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">审核模式</td>
                    <td>
                        <select class="base_select" name="audit_model" selectedval="{$_info.audit_model ? '1' : '0'}">
                            <option value="1">手动审核</option>
                            <option value="0">免审核</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">页面标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}" />
                    </td>
                </tr>

                <tr>
                    <td class="field_name">申请条件</td>
                </tr>

                <tr>
                    <td class="field_name">实名认证</td>
                    <td class="flex_layout">
                        <div class="base_group flex_layout" style="float: left;width: 25%;">
                            <span  style="float: left;">icon：</span>
                            <input style="width: 60%;float: left;" name="real_name_img" value="{$_info.real_name_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="real_name_img">上传</a>
                        </div>
                        <div imgview="[name=real_name_img]" style="width: 20px;"><img src="{$_info.real_name_img}" class="preview"/></div>
                        <select class="base_select mr_20" name="real_name_verify_status" selectedval="{$_info.real_name_verify_status ? '1' : '0'}" style="width: 100px;">
                            <option value="1">开启</option>
                            <option value="0">不开启</option>
                        </select>
                        <select class="base_select mr_20" name="real_name_type" selectedval="{$_info.real_name_type ? '1' : '0'}" style="width: 100px;">
                            <option value="1">基础条件</option>
                            <option value="0">进阶条件</option>
                        </select>

                        重命名：<input class="base_text mr_20" name="real_name_verify_name" value="{$_info.real_name_verify_name}" style="width: 150px;"/>
                        条件：
                        <select class="base_select mr_20 last_width" name="real_name_verify" selectedval="{$_info.real_name_verify ? '1' : '0'}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">粉丝数量</td>
                    <td class="flex_layout">
                        <div class="base_group flex_layout" style="float: left;width: 25%;">
                            <span  style="float: left;">icon：</span>
                            <input style="width: 60%;float: left;" name="fans_num_img" value="{$_info.fans_num_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="fans_num_img">上传</a>
                        </div>
                        <div imgview="[name=fans_num_img]" style="width: 20px;"><img src="{$_info.fans_num_img}" class="preview"/></div>
                        <select class="base_select mr_20" name="fans_num_status" selectedval="{$_info.fans_num_status ? '1' : '0'}" style="width: 100px;">
                            <option value="1">开启</option>
                            <option value="0">不开启</option>
                        </select>
                        <select class="base_select mr_20" name="fans_num_type" selectedval="{$_info.fans_num_type ? '1' : '0'}" style="width: 100px;">
                            <option value="1">基础条件</option>
                            <option value="0">进阶条件</option>
                        </select>

                        重命名：<input class="base_text mr_20" name="fans_num_name" value="{$_info.fans_num_name}" style="width: 150px;"/>
                        条件：<input class="base_text mr_20 last_width" name="fans_num" value="{$_info.fans_num}"/>
                    </td>
                </tr>
                <tr class="flex_layout">
                    <td class="field_name">短视频数量</td>
                    <td class="flex_layout">
                        <div class="base_group flex_layout" style="float: left;width: 25%;">
                            <span  style="float: left;">icon：</span>
                            <input style="width: 60%;float: left;" name="short_video_num_img" value="{$_info.short_video_num_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="short_video_num_img">上传</a>
                        </div>
                        <div imgview="[name=short_video_num_img]" style="width: 20px;"><img src="{$_info.short_video_num_img}" class="preview"/></div>
                        <select class="base_select mr_20" name="short_video_num_status" selectedval="{$_info.short_video_num_status ? '1' : '0'}" style="width: 100px;">
                            <option value="1">开启</option>
                            <option value="0">不开启</option>
                        </select>
                        <select class="base_select mr_20" name="short_video_num_type" selectedval="{$_info.short_video_num_type ? '1' : '0'}" style="width: 100px;">
                            <option value="1">基础条件</option>
                            <option value="0">进阶条件</option>
                        </select>

                        重命名：<input class="base_text mr_20" name="short_video_num_name" value="{$_info.short_video_num_name}" style="width: 150px;"/>
                        条件：<input class="base_text mr_20 last_width" name="short_video_num" value="{$_info.short_video_num}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">主播等级</td>
                    <td class="flex_layout">
                        <div class="base_group flex_layout" style="float: left;width: 25%;">
                            <span  style="float: left;">icon：</span>
                            <input style="width: 60%;float: left;" name="anchor_level_img" value="{$_info.anchor_level_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="anchor_level_img">上传</a>
                        </div>
                        <div imgview="[name=anchor_level_img]" style="width: 20px;"><img src="{$_info.anchor_level_img}" class="preview"/></div>

                        <select class="base_select mr_20" name="anchor_level_status" selectedval="{$_info.anchor_level_status ? '1' : '0'}" style="width: 100px;">
                            <option value="1">开启</option>
                            <option value="0">不开启</option>
                        </select>
                        <select class="base_select mr_20" name="anchor_level_type" selectedval="{$_info.anchor_level_type ? '1' : '0'}" style="width: 100px;">
                            <option value="1">基础条件</option>
                            <option value="0">进阶条件</option>
                        </select>

                        重命名：<input class="base_text mr_20" name="anchor_level_name" value="{$_info.anchor_level_name}" style="width: 150px;"/>
                        条件：<select class="base_select mr_20 last_width" name="anchor_level" selectedval="{$_info.anchor_level ? $_info.anchor_level : '0'}">
                            <option value="0">无</option>
                            <volist name="archor_level_list" id="level">
                                <option value="{$level.levelid}">{$level.levelname}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">开通费用</td>
                    <td class="flex_layout">
                        <div class="base_group flex_layout" style="float: left;width: 25%;">
                            <span  style="float: left;">icon：</span>
                            <input style="width: 60%;float: left;" name="open_fee_img" value="{$_info.open_fee_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="open_fee_img">上传</a>
                        </div>
                        <div imgview="[name=open_fee_img]" style="width: 20px;"><img src="{$_info.open_fee_img}" class="preview"/></div>
                        <select class="base_select mr_20" name="fee_status" selectedval="{$_info.fee_status ? '1' : '0'}" style="width: 100px;">
                            <option value="1">开启</option>
                            <option value="0">不开启</option>
                        </select>
                        <select class="base_select mr_20" name="open_fee_type" selectedval="{$_info.open_fee_type ? '1' : '0'}" style="width: 100px;">
                            <option value="1">基础条件</option>
                            <option value="0">进阶条件</option>
                        </select>

                        重命名：<input class="base_text mr_20" name="open_fee_name" value="{$_info.open_fee_name}" style="width: 150px;"/>
                        条件：<input class="base_text mr_20 last_width" name="open_fee" value="{$_info.open_fee}"/> 元
                    </td>
                </tr>
                <tr>
                    <td class="field_name">保证金</td>
                    <td class="flex_layout">
                        <div class="base_group flex_layout" style="float: left;width: 25%;">
                            <span  style="float: left;">icon：</span>
                            <input style="width: 60%;float: left;" name="bond_fee_img" value="{$_info.bond_fee_img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="bond_fee_img">上传</a>
                        </div>
                        <div imgview="[name=bond_fee_img]" style="width: 20px;"><img src="{$_info.bond_fee_img}" class="preview"/></div>
                        <select class="base_select mr_20" name="bond_status" selectedval="{$_info.bond_status ? '1' : '0'}" style="width: 100px;">
                            <option value="1">开启</option>
                            <option value="0">不开启</option>
                        </select>
                        <select class="base_select mr_20" name="bond_fee_type" selectedval="{$_info.bond_fee_type ? '1' : '0'}" style="width: 100px;">
                            <option value="1">基础条件</option>
                            <option value="0">进阶条件</option>
                        </select>

                        重命名：<input class="base_text mr_20" name="bond_fee_name" value="{$_info.bond_fee_name}" style="width: 150px;"/>
                        条件：<input class="base_text mr_20 last_width" name="bond_fee" value="{$_info.bond_fee}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">权益说明</td>
                    <td>
                        <textarea style="width:600px;height:350px;" name="rights">{$_info.rights}</textarea>
                    </td>
                </tr>

                <!--<tr>
                    <td class="field_name">开通费用分销</td>
                    <td>
                        <select class="base_select" name="fee_retail" selectedval="{$_info.fee_retail ? '1' : '0'}" id="fee_retail">
                            <option value="1">支持</option>
                            <option value="0">不支持</option>
                        </select>
                    </td>
                </tr>

                <tr class="distribute_reward" style="display: table-row;">
                    <td class="field_name">奖励类型</td>
                    <td>
                        <select class="base_select" name="retail[reward_type]" selectedval="{$_info.retail.reward_type ? '1' : '0'}">
                            <option value="1">百分比</option>
                            <option value="0">固定金额</option>
                        </select>
                    </td>
                </tr>

                <tr class="distribute_reward" style="display: table-row;">
                    <td class="field_name">一级奖励</td>
                    <td>
                        <input class="base_text" name="retail[first][reward]" value="{$_info.retail.first.reward}" style="width: 148px;"/>
                        用户等级大于等于
                        <select class="base_select" name="retail[first][limit_level]" selectedval="{$_info.retail.first.limit_level ? $_info.retail.first.limit_level : '0'}" style="width: 149px;">
                            <option value="0">不限制</option>
                            <volist name="user_level_list" id="level">
                                <option value="{$level.levelid}">{$level.levelname}</option>
                            </volist>
                        </select>
                        可得到奖励
                    </td>
                </tr>

                <tr class="distribute_reward" style="display: table-row;">
                    <td class="field_name">二级奖励</td>
                    <td>
                        <input class="base_text" name="retail[second][reward]" value="{$_info.retail.second.reward}" style="width: 148px;"/>
                        用户等级大于等于
                        <select class="base_select" name="retail[second][limit_level]" selectedval="{$_info.retail.second.limit_level ? $_info.retail.second.limit_level : '0'}" style="width: 149px;">
                            <option value="0">不限制</option>
                            <volist name="user_level_list" id="level">
                                <option value="{$level.levelid}">{$level.levelname}</option>
                            </volist>
                        </select>
                        可得到奖励
                    </td>
                </tr>

                <tr class="distribute_reward" style="display: table-row;">
                    <td class="field_name">三级奖励</td>
                    <td>
                        <input class="base_text" name="retail[third][reward]" value="{$_info.retail.third.reward}" style="width: 148px;"/>
                        用户等级大于等于
                        <select class="base_select" name="retail[third][limit_level]" selectedval="{$_info.retail.third.limit_level ? $_info.retail.third.limit_level : '0'}" style="width: 149px;">
                            <option value="0">不限制</option>
                            <volist name="user_level_list" id="level">
                                <option value="{$level.levelid}">{$level.levelname}</option>
                            </volist>
                        </select>
                        可得到奖励
                    </td>
                </tr>

                <tr class="distribute_reward" style="display: table-row;">
                    <td class="field_name">团队奖励</td>
                    <td>
                        <input class="base_text" name="retail[team][reward]" value="{$_info.retail.team.reward}" style="width: 148px;"/>
                        用户等级大于等于
                        <select class="base_select" name="retail[team][limit_level]" selectedval="{$_info.retail.team.limit_level ? $_info.retail.team.limit_level : '0'}" style="width: 149px;">
                            <option value="0">不限制</option>
                            <volist name="user_level_list" id="level">
                                <option value="{$level.levelid}">{$level.levelname}</option>
                            </volist>
                        </select>
                        可得到奖励
                    </td>
                </tr>

                <tr class="distribute_reward" style="display: table-row;">
                    <td class="field_name">平级奖励</td>
                    <td>
                        <select class="base_select" name="retail[level_reward_type]" selectedval="{$_info.retail.level_reward_type ? '1' : '0'}"  id="level_reward">
                            <option value="1">开启</option>
                            <option value="0">关闭</option>
                        </select>
                    </td>
                </tr>

                <tr class="level_reward" style="display: none;">
                    <td class="field_name"></td>
                    <td>
                        <input class="base_text" name="retail[level][reward]" value="{$_info.retail.level.reward}" style="width: 148px;"/>
                        用户等级大于等于
                        <select class="base_select" name="retail[level][limit_level]" selectedval="{$_info.retail.level.limit_level ? $_info.retail.level.limit_level : '0'}" style="width: 149px;">
                            <option value="0">不限制</option>
                            <volist name="user_level_list" id="level">
                                <option value="{$level.levelid}">{$level.levelname}</option>
                            </volist>
                        </select>
                        可得到奖励
                    </td>
                </tr>-->

            </table>
            <div class="base_button_div max_w_auto">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
        $(function(){
            if({$_info.fee_retail} == 1){
                $(".distribute_reward").show();

                if({$_info.retail.level_reward_type} == 1){
                    $(".level_reward").show();
                }
            }
        });

        $("#fee_retail").change(function(){
            var checkValue = $("#fee_retail").val();
            if(checkValue == 1){
                $(".distribute_reward").show();
            }else{
                $(".distribute_reward").hide();
            }
        });

        $("#level_reward").change(function(){
            var checkValue = $("#level_reward").val();
            if(checkValue == 1){
                $(".level_reward").show();
            }else{
                $(".level_reward").hide();
            }
        });

    </script>
</block>