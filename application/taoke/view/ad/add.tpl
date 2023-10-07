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
        <form action="{:isset($_info['ad_id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">广告位</td>
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
                            <input style="width: 309px;" name="img" value="{$_info.image}" type="text" class="base_text"/>
                            <a uploader-type="image" href="javascript:;" class="base_button" uploader="taoke_images" uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
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
                        <input class="base_text" name="bg_color[]" value="{$_info.bg_color[0]|default='#ffffff'}" placeholder="起始色"/> -
                        <input class="base_text" name="bg_color[]" value="{$_info.bg_color[1]|default='#ffffff'}" placeholder="终止色"/>
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
                        <present name="_info['ad_id']">
                            <input name="ad_id" type="hidden" value="{$_info.ad_id}"/>
                        </present>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>
        $(function(){
            if("{$_info.open_type}" == 1 || "{$_info.open_type}" == 2){
                $(".type").show();
            }
        });

        $("#open_type").change(function(){
            var value = $("#open_type").val();
            if(value == 0){
                $(".type").hide();
            }else{
                $(".type").show();
            }
        });
    </script>
</block>