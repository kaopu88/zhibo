<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <input class="base_text" name="id" value="{$_info.id}" style="display: none"/>
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">用户ID</td>
                    <td>
                        <input class="base_text" name="user_id" value="{$_info.user_id}" readonly/>

                    </td>
                </tr>

                <tr>
                    <td class="field_name">封面</td>
                    <td>
                        <div class="base_group" style="float: left;width: 25%;">

                            <input style="width: 309px;" name="cover_url" value="{$_info.cover_url}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images" uploader-field="cover_url" style=" float: initial;">上传</a> </div>
                        <div imgview="[name=cover_url]" style="width: 120px;margin-left: 5px;"><img style="height: 40px" src="{$_info.cover_url}" class="preview"/></div>
                </tr>

                <tr>
                    <td class="field_name">直播分类</td>
                    <td>
                        <select class="base_select" name="room_channel" selectedval="{$_info.room_channel}">
                            <volist name="channel" id="vo">
                                <option value="{$vo.id}">{$vo.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name" >房间类型</td>
                    <td>
                        <select class="base_select" id="select_type" name="type" selectedval="{$_info.type}" onchange="func()">
                            <option value="0">普通房间</option>
                            <option value="1">私密房间</option>
                            <option value="2">收费房间</option>
                            <option value="3">计费房间</option>
                            <option value="4">VIP房间</option>
                        </select>
                    </td>
                </tr>

                <tr  <if condition="$_info.type eq 0"> style="display: none" </if>  id="type">
                    <td class="field_name" >密码或价格</td>
                    <td>
                        <input class="base_text" name="type_val" value="{$_info.type_val}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">视频地址(mp4)</td>
                    <td>
                        <input class="base_text" name="pull" value="{$_info.pull}" style="float: left"/>
                        <div   style="float: left">
                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="video" href="javascript:;"
                               class="base_button"
                               uploader="admin_videos"
                               uploader-field="pull">上传
                            </a>

                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name"></td>
                    <td>
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>

            </table>
        </form>
    </div>

    <script>
        function func(){
            var vs = $('#select_type  option:selected').val();
            if (vs == 0) {
                $("#type").hide();
            } else {
                $("#type").show();
            }
        }
    </script>
</block>