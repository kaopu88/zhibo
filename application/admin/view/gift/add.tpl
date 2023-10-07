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
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">类型</td>
                    <td>
                        <select class="base_select" name="cid" selectedval="{$_info.cid}">
                            <option value="0">直播间礼物</option>
                            <option value="1">视频礼物</option>
                            <option value="10">道具礼物</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">性质</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="1">小礼物</option>
                            <option value="0">大礼物</option>
                            <option value="2">动画礼物</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">礼物标题</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="picture_url" value="{$_info.picture_url}" type="text" class="base_text border_left_radius"/>
                            <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="gift_icon"
                               uploader-field="picture_url">上传</a>
                        </div>
                        <div imgview="[name=picture_url]" style="width: 120px;margin-top: 10px;"><img src="{$_info.picture_url}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">在线资源包</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="file_path" value="{$_info.file}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="gift_packages"
                               uploader-field="file_path" uploader-before="uploadBefore">上传</a>
                        </div>
                        <p class="field_tip">文件大小限制在200MB以内</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">单价</td>
                    <td>
                        <input class="base_text" name="price" value="{$_info.price}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">折扣</td>
                    <td>
                        <input class="base_text" name="discount" value="{$_info.discount}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">等值{:APP_BEAN_NAME}</td>
                    <td>
                        <input class="base_text" name="conv_millet" value="{$_info.conv_millet}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">守护天数(针对守护礼物)</td>
                    <td>
                        <input class="base_text" name="guard_day" value="{$_info.guard_day}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">点击提示信息</td>
                    <td>
                        <input class="base_text" name="tips" value="{$_info.tips}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">总销量</td>
                    <td>
                        <input class="base_text" name="sales" value="{$_info.sales}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">参数</td>
                    <td>
                        <textarea name="show_params" class="base_textarea">{$_info.show_params}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">特权</td>
                    <td>
                        <select class="base_select" name="privileges" selectedval="{$_info.privileges}">
                            <option value="">无</option>
                            <option value="leave_msg">留言</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">VIP礼物</td>
                    <td>
                        <select class="base_select" name="is_vip" selectedval="{$_info.is_vip ? '1' : '0'}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>
        function uploadBefore(file) {
            var name = $('[name=name]').val();
            if (isEmpty(name)) {
                $s.error('请先填写礼物标题');
                return false;
            }
            $('[uploader-field=file_path]').attr('uploader-query', 'package_name=' + name);
            return true;
        }
    </script>
</block>