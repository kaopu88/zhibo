<extend name="public:base_nav"/>
<block name="js">
</block>

<block name="css">
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
                    <td class="field_name">安装包名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                        <p class="field_tip">如：bx、 bxkj</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">外部版本号</td>
                    <td>
                        <input placeholder="" class="base_text" name="version" value="{$_info.version}"/>
                        <p class="field_tip">如：1.0.2</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">内部版本号</td>
                    <td>
                        <input placeholder="" class="base_text" name="code" value="{$_info.code}"/>
                        <p class="field_tip">如：70，正整数值</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">运行平台</td>
                    <td>
                        <select name="os" class="base_select" selectedval="{$_info.os}">
                            <option value="">请选择</option>
                            <volist name=":enum_array('packages_os')" id="os">
                                <option value="{$os.value}">{$os.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发布渠道</td>
                    <td>
                        <select name="channel" class="base_select" selectedval="{$_info.channel}">
                            <volist name=":enum_array('packages_channel')" id="channel">
                                <option value="{$channel.value}">{$channel.name}</option>
                            </volist>
                        </select>
                        <p class="field_tip">IOS平台请选择通用渠道</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">第三方地址</td>
                    <td>
                        <input class="base_text" name="url" value="{$_info.url}"/>
                        <p class="field_tip">下载时将跳转到该地址下载，IOS平台请填写应用商店地址</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">更新类型</td>
                    <td>
                        <select name="update_type" class="base_select" selectedval="{$_info.update_type}">
                            <volist name=":enum_array('packages_update_types')" id="update_type">
                                <option value="{$update_type.value}">{$update_type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">安装包</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="file_path" value="{$_info.file_path}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="package" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_packages"
                               uploader-field="file_path" uploader-before="uploadBefore">上传</a>
                        </div>
                        <p class="field_tip">文件大小限制在200MB以内</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">更新描述</td>
                    <td>
                        <textarea placeholder="" class="base_textarea" name="descr">{$_info.descr}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">启用状态</td>
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
                        <div class="base_button_div" style="max-width: 413px;">
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
            var version = $('[name=version]').val();
            var channel = $('[name=channel] option:selected').val();
            if (isEmpty(name)) {
                $s.error('请先填写安装包名称');
                return false;
            }
            if (isEmpty(version)) {
                $s.error('请先填写外部版本号');
                return false;
            }
            if (isEmpty(channel)) {
                $s.error('请先选择发布渠道');
                return false;
            }
            $('[uploader-field=file_path]').attr('uploader-query', 'package_name=' + name + '&package_version=' + version + '&package_channel=' + channel);
            return true;
        }
    </script>
</block>