<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .type {
            display: none;
        }
    </style>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['module_id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">模块位</td>
                    <td>
                        <select class="base_select" name="position_id" selectedval="{$_info.position_id}">
                            <volist name="position" id="vo">
                                <option value="{$vo.id}">{$vo.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">名称</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">图片</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">选中后图片</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="img" value="{$_info.selected_image}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="selected_image">上传</a>
                        </div>
                        <div imgview="[name=selected_image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">描述</td>
                    <td>
                        <input class="base_text" name="desc" value="{$_info.desc}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">背景色</td>
                    <td>
                        <input class="base_text" style="width:188px;" name="bg_color[]" value="{$_info.bg_color[0]|default='#ffffff'}" placeholder="起始色"/> -
                        <input class="base_text" style="width:188px;" name="bg_color[]" value="{$_info.bg_color[1]|default='#ffffff'}" placeholder="终止色"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">打开方式</td>
                    <td>
                        <select class="base_select" name="open_type" selectedval="{$_info.open_type}" id="open_type">
                            <option value="0"> 无 </option>
                            <option value="1">APP内部链接</option>
                            <option value="2">外部链接</option>
                        </select>
                    </td>
                </tr>

                <tr class="app_link" style="display: none;">
                    <td class="field_name">链接类型</td>
                    <td>
                        <select class="base_select" id="link_type" selectedval="{$_info.page_cid}">
                            <option value="5">内部链接</option>
                            <option value="6">内部专题</option>
                        </select>
                    </td>
                </tr>

                <tr id="page-list" style="display: none;">
                    <td class="field_name">页面</td>
                    <td>
                        <select class="base_select" name="page_id" id="page-id" selectedval="{$_info.page_id}">
                            <notempty name="page_list">
                                <volist name="page_list" id="vo">
                                    <option value="{$vo.id}">{$vo.name}</option>
                                </volist>
                            </notempty>
                        </select>
                    </td>
                </tr>

                <tr class="type">
                    <td class="field_name">url</td>
                    <td>
                        <input class="base_text" name="open_url" value="{$_info.open_url}"/>
                    </td>
                </tr>

                <tr class="type">
                    <td class="field_name">传递参数</td>
                    <td>
                        <input class="base_text" name="params" value="{$_info.params}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort|default=9999}"/>
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
                        <present name="_info['module_id']">
                            <input name="module_id" type="hidden" value="{$_info.module_id}"/>
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
        $(function(){
            var id = "{$_info.open_type}";
            if(id == 2){
                $(".type").css({'display': 'table-row'});
                $(".app_link").hide();
                $("#page-list").hide();
            }else if (id == 1){
                $(".type").hide();
                $(".app_link").css({'display': 'table-row'});
                $("#page-list").css({'display': 'table-row'});
            }else{
                $(".type").hide();
                $(".app_link").hide();
                $("#page-list").hide();
            }
        });

        $("#link_type").on("change", function () {
            getPage();
        });

        function getPage() {
            var link_type = $("#link_type").val();
            $.ajax({
                url: "{:url('getPage')}",
                dataType: "json",
                type: "POST",
                data: {type: link_type},
                success:function(data){
                    if(data.status == 0){
                        var list = data.data;
                        var html = "";
                        if(list.length > 0){
                            for (var i=0;i<list.length;i++){
                                html += '<option value="'+list[i].id+'">'+list[i].name+'</option>';
                            }
                        }
                        $("#page-id").html(html);
                        $("#page-list").css({'display': 'table-row'});
                        $(".app_link").css({'display': 'table-row'});
                    }else{
                        layer.msg(data.message);
                    }
                }
            });
        }


        $("#open_type").change(function(){
            var value = $("#open_type").val();
            if(value == 2){
                $(".type").css({'display': 'table-row'});
                $(".app_link").hide();
                $("#page-list").hide();
            }else if (value == 1){
                $(".type").hide();
                getPage();
            }else{
                $(".type").hide();
                $(".app_link").hide();
                $("#page-list").hide();
            }
        });
    </script>
</block>