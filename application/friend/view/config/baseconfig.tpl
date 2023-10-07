<extend name="public:base_nav"/>
<block name="css">
    <style>
        .distribute_reward {
            display: none;
        }
        .content_info2 td.field_name {
            text-align: right;
            padding-right: 10px;
            padding-left: 0;
            font-size: 14px;
            width: 350px;
        }
    </style>
</block>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
            <form action="{:url('baseconfig')}">
            <div class="table_slide">
                <table class="content_info2 mt_10 font_normal table_fixed sm_width">
                    <tr>
                        <td class="field_name" style="width:110px;">是否启用</td>
                        <td>
                            <select class="base_select" name="is_open" selectedval="{$_info.is_open ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">发布图片长度</td>
                        <td>
                            <input class="base_text" name="msg_img_length" value="{$_info.msg_img_length}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">评论图片长度</td>
                        <td>
                            <input class="base_text" name="comment_img_length" value="{$_info.comment_img_length}"/>
                        </td>
                    </tr>
                    <tr hidden>
                        <td class="field_name">评论间隔时间(如果可以进行重复评论)</td>
                        <td>
                            <input class="base_text" name="comment_interval" value="{$_info.comment_interval}"/> 秒
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">单条可以评论的次数</td>
                        <td>
                            <input class="base_text" name="comment_total_num" value="{$_info.comment_total_num}"/>
                        </td>
                    </tr>
                    <tr hidden >
                        <td class="field_name">对评论进行留言的间隔时间(如果可以进行重复留言)</td>
                        <td>
                            <input class="base_text" name="comment_evaluate_interval"
                                value="{$_info.comment_evaluate_interval}"/> 秒
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">单条可以留言的次数</td>
                        <td>
                            <input class="base_text" name="comment_evaluate_total_num"
                                value="{$_info.comment_evaluate_total_num}"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">推荐话题展示数量</td>
                        <td>
                            <input class="base_text" name="recommend_topic_num"
                                value="{$_info.recommend_topic_num}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">热门话题展示数量</td>
                        <td>
                            <input class="base_text" name="hot_topic_num"
                                value="{$_info.hot_topic_num}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">最新话题展示数量</td>
                        <td>
                            <input class="base_text" name="new_topic_num"
                                value="{$_info.new_topic_num}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">历史话题展示数量</td>
                        <td>
                            <input class="base_text" name="history_topic_num"
                                value="{$_info.history_topic_num}"/>
                        </td>
                    </tr>


                    <tr>
                        <td class="field_name">非好友可发信息数量</td>
                        <td>
                            <input class="base_text" name="chat_num"
                                value="{$_info.chat_num}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">每日可创建发圈的数量</td>
                        <td>
                            <input class="base_text" name="create_circle_num"
                                value="{$_info.create_circle_num}"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">圈子多长时间修改一次</td>
                        <td>
                            <input class="base_text" name="circle_update_day"
                                value="{$_info.circle_update_day}"/> 天
                        </td>

                    </tr>

                    <tr>
                        <td class="field_name">发布动态话题最高数量</td>
                        <td>
                            <input class="base_text" name="create_dynamic_num"
                                value="{$_info.create_dynamic_num}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">用户动态是否需要审核</td>
                        <td>
                            <select class="base_select" name="msg_examine" selectedval="{$_info.msg_examine ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">关闭</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">用户回复是否需要审核</td>
                        <td>
                            <select class="base_select" name="msg_commment_examine"
                                    selectedval="{$_info.msg_commment_examine ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">关闭</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">用户回复评论是否需要审核</td>
                        <td>
                            <select class="base_select" name="msg_commment_evaluate_examine"
                                    selectedval="{$_info.msg_commment_evaluate_examine ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">关闭</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">附近的人最大距离</td>
                        <td>
                            <input class="base_text" name="friend_near_max"
                                value="{$_info.friend_near_max}"/>千米
                        </td>

                    </tr>
                    <tr>
                        <td class="field_name">圈子默认背景图</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_back" value="{$_info.citcle_defaut_back}" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_back">上传</a>
                            </div>
                            <a rel="thumb" href="{:img_url($_info.citcle_defaut_back,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                            <div imgview="[name=citcle_defaut_back]" style="width: 20px;"><img src="{$_info.citcle_defaut_back}" class="preview"/></div>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">圈子默认封面</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_cover" value="{$_info.citcle_defaut_cover}" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_cover">上传</a>
                            </div>
                            <a rel="thumb" href="{:img_url($_info.citcle_defaut_cover,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_cover]" style="width: 20px;"><img src="{$_info.citcle_defaut_cover}" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">用户图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_user" value="{$_info.citcle_defaut_user}" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_user">上传</a>
                            </div>
                            <a rel="thumb" href="{:img_url($_info.citcle_defaut_user,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_user]" style="width: 20px;"><img src="{$_info.citcle_defaut_user}" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">动态图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_dynamic" value="{$_info.citcle_defaut_dynamic}" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_dynamic">上传</a>
                            </div>
                            <a rel="thumb" href="{:img_url($_info.citcle_defaut_dynamic,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_dynamic]" style="width: 20px;"><img src="{$_info.citcle_defaut_dynamic}" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">直播图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_live" value="{$_info.citcle_defaut_live}" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_live">上传</a>
                            </div>
                            <a rel="thumb" href="{:img_url($_info.citcle_defaut_live,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_live]" style="width: 20px;"><img src="{$_info.citcle_defaut_live}" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">小视频图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_video" value="{$_info.citcle_defaut_video}" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_video">上传</a>
                            </div>
                            <a rel="thumb" href="{:img_url($_info.citcle_defaut_video,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_video]" style="width: 20px;"><img src="{$_info.citcle_defaut_video}" class="preview"/></div>

                    </tr>

                </table>
                <div class="base_button_div" style="max-width:547px;">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
                </div>
            </form>
        
    </div>

    <script>
        $(function () {

        });


    </script>
</block>