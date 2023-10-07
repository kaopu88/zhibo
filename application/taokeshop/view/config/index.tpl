<extend name="public:base_nav"/>
<block name="css">
    <style>
        .distribute_reward{
            display:none;
        }
    </style>
</block>
<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('index')}">
                <table class="content_info2 mt_10 tab_flex">
                    <tr>
                        <td class="field_name">小店功能</td>
                        <td>
                            <select class="base_select" name="user_shop" selectedval="{$_info.user_shop ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">直播商品数量限制</td>
                        <td>
                            <input class="base_text" name="goods_num" value="{$_info.goods_num}"/>
                            0为不限制
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
                        <td class="field_name">成为主播</td>
                        <td class="flex_layout">
                            <div class="base_group flex_layout" style="float: left;width: 25%;">
                                <span  style="float: left;">icon：</span>
                                <input style="width: 60%;float: left;" name="live_img" value="{$_info.live_img}" type="text" class="base_text mr_20 border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="live_img">上传</a>
                            </div>
                            <div imgview="[name=live_img]" style="width: 20px;"><img src="{$_info.live_img}" class="preview"/></div>

                            <select class="base_select mr_20" name="live_type" selectedval="{$_info.live_type ? '1' : '0'}" style="width: 100px;">
                                <option value="1">基础条件</option>
                                <option value="0">进阶条件</option>
                            </select>

                            重命名：<input class="base_text mr_20" name="live_name" value="{$_info.live_name}" style="width: 150px;"/>
                            条件：
                            <select class="base_select mr_20 last_width" name="live_verify" selectedval="{$_info.live_verify ? '1' : '0'}">
                                <option value="1">是</option>
                                <option value="0">否</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">实名认证</td>
                        <td class="flex_layout">
                            <div class="base_group flex_layout" style="float: left;width: 25%;">
                                <span  style="float: left;">icon：</span>
                                <input style="width: 60%;float: left;" name="real_name_img" value="{$_info.real_name_img}" type="text" class="base_text mr_20 border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="real_name_img">上传</a>
                            </div>
                            <div imgview="[name=real_name_img]" style="width: 20px;"><img src="{$_info.real_name_img}" class="preview"/></div>

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
                                <input style="width: 60%;float: left;" name="fans_num_img" value="{$_info.fans_num_img}" type="text" class="base_text mr_20 border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="fans_num_img">上传</a>
                            </div>
                            <div imgview="[name=fans_num_img]" style="width: 20px;"><img src="{$_info.fans_num_img}" class="preview"/></div>

                            <select class="base_select mr_20" name="fans_num_type" selectedval="{$_info.fans_num_type ? '1' : '0'}" style="width: 100px;">
                                <option value="1">基础条件</option>
                                <option value="0">进阶条件</option>
                            </select>

                            重命名：<input class="base_text mr_20" name="fans_num_name" value="{$_info.fans_num_name}" style="width: 150px;"/>
                            条件：<input class="base_text mr_20 last_width" name="fans_num" value="{$_info.fans_num}"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">短视频数量</td>
                        <td class="flex_layout">
                            <div class="base_group flex_layout" style="float: left;width: 25%;">
                                <span  style="float: left;">icon：</span>
                                <input style="width: 60%;float: left;" name="short_video_num_img" value="{$_info.short_video_num_img}" type="text" class="base_text mr_20 border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="short_video_num_img">上传</a>
                            </div>
                            <div imgview="[name=short_video_num_img]" style="width: 20px;"><img src="{$_info.short_video_num_img}" class="preview"/></div>

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
                                <input style="width: 60%;float: left;" name="anchor_level_img" value="{$_info.anchor_level_img}" type="text" class="base_text mr_20 border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="anchor_level_img">上传</a>
                            </div>
                            <div imgview="[name=anchor_level_img]" style="width: 20px;"><img src="{$_info.anchor_level_img}" class="preview"/></div>

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
                                <input style="width: 60%;float: left;" name="open_fee_img" value="{$_info.open_fee_img}" type="text" class="base_text mr_20 border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="open_fee_img">上传</a>
                            </div>
                            <div imgview="[name=open_fee_img]" style="width: 20px;"><img src="{$_info.open_fee_img}" class="preview"/></div>

                            <select class="base_select mr_20 last_width" name="open_fee_type" selectedval="{$_info.open_fee_type ? '1' : '0'}" style="width: 100px;">
                                <option value="1">基础条件</option>
                                <option value="0">进阶条件</option>
                            </select>

                            重命名：<input class="base_text mr_20" name="open_fee_name" value="{$_info.open_fee_name}" style="width: 150px;"/>
                            条件：<input class="base_text mr_20 last_width" name="open_fee" value="{$_info.open_fee}"/> 元
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">权益说明</td>
                        <td>
                            <textarea style="width:600px;height:350px;" name="rights">{$_info.rights}</textarea>
                        </td>
                    </tr>



                </table>
                <div class="base_button_div p_b_20 p_r_20" style="max-width:969px;">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function(){
            if({$_info.fee_retail} == 1){
                $(".distribute_reward").css("display","table-row");

                if({$_info.retail.level_reward_type} == 1){
                    $(".level_reward").css("display","table-row");
                }
            }
        });

        $("#fee_retail").change(function(){
            var checkValue = $("#fee_retail").val();
            if(checkValue == 1){
                $(".distribute_reward").css("display","table-row");
            }else{
                $(".distribute_reward").hide();
            }
        });

        $("#level_reward").change(function(){
            var checkValue = $("#level_reward").val();
            if(checkValue == 1){
                $(".level_reward").css("display","table-row");
            }else{
                $(".level_reward").hide();
            }
        });

    </script>
</block>