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
                    <td class="field_name">所属类目</td>
                    <td>
                        <div typecl="{:url('resources/get_tree')}">
                            <input type="hidden" class="type_val_0" name="pcat_id" value="{$_info.pcat_id}">
                            <input type="hidden" class="type_val_1" name="cat_id" value="{$_info.cat_id}">
                            <input type="hidden" name="mark" value="">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">资源名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}" onBlur="checkName(this.value)"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text"
                                                                    class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_resources_images"
                               uploader-field="image" uploader-before="imageUploadBefore">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img
                                    src="{:img_url('','','image')}"/></div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">资源包</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="file_url" value="{$_info.file_url}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="" uploader-size="524288000" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_resources_packages"
                               uploader-field="file_url" uploader-before="packageUploadBefore">上传</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">热门</td>
                    <td>
                        <select class="base_select" name="hot" selectedval="{$_info.hot}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">最新</td>
                    <td>
                        <select class="base_select" name="new" selectedval="{$_info.new}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">排序权重</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort}"/>
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
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
        function checkName(name){
            var reg = /^\w+$/;
            if(!reg.test(name)){
                $s.error('资源名称不符合要求');
            }
            var pcat_id = $("input[name='pcat_id']").val();
            $s.post('{:url("check_name")}', {
                pcat_id: pcat_id,
                name: name,
            }, function (result) {
                if(result.status==1){
                    $s.error(result.message);
                }else{
                    $("input[name='mark']").val(result.data);
                }
            });
        }

        function imageUploadBefore(file) {
            var name = $('[name=name]').val();
            var pcat_id = $('[name=pcat_id]').val();
            var mark = $('[name=mark]').val();
            if (isEmpty(pcat_id)) {
                $s.error('请先选择所属类目');
                return false;
            }
            if (isEmpty(name)) {
                $s.error('请先填写资源名称');
                return false;
            }
            $('[uploader-field=image]').attr('uploader-query', 'resource_type=' + mark + '&resource_name=' + name);
            return true;
        }

        function packageUploadBefore(file) {
            var name = $('[name=name]').val();
            var pcat_id = $('[name=pcat_id]').val();
            var mark = $('[name=mark]').val();
            if (isEmpty(pcat_id)) {
                $s.error('请先选择所属类目');
                return false;
            }
            if (isEmpty(name)) {
                $s.error('请先填写资源名称');
                return false;
            }
            $('[uploader-field=file_url]').attr('uploader-query', 'resource_type=' + mark + '&resource_name=' + name);
            return true;
        }
    </script>

</block>