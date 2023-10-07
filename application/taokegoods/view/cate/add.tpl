<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
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
        <form action="{:isset($_info['cate_id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">分类名</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">icon</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="img" value="{$_info.img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="img">上传</a>
                        </div>
                        <div imgview="[name=img]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">分类描述</td>
                    <td>
                        <input class="base_text" name="desc" value="{$_info.desc}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort|default=9999}"/>
                    </td>
                </tr>

                <!--<tr>
                    <td class="field_name">对应大淘客分类</td>
                    <td>
                        <select class="base_select" name="dtk_cate_id" selectedval="{$_info.dtk_cate_id}">
                            <volist name="cate_list" id="vo">
                                <option value="{$vo.cid}">{$vo.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>-->

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
                        <present name="_info['cate_id']">
                            <input name="id" type="hidden" value="{$_info.cate_id}"/>
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
</block>