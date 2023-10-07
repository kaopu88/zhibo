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
        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">模块名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">图片</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="img" value="{$_info.img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="img">上传</a>
                        </div>
                        <div imgview="[name=img]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">描述</td>
                    <td>
                        <input class="base_text" name="desc" value="{$_info.desc}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">使用平台</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="all">全平台</option>
                            <option value="app">APP</option>
                            <option value="wap">Wap</option>
                            <option value="web">Web</option>
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
                    <td class="field_name">启用状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">应用模块</td>
                    <td>
                        <select class="base_select" name="application_index" selectedval="{$_info.application_index}">
                            <option value="0">淘客</option>
                            <option value="1">拼多多</option>
                            <option value="2">京东</option>
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
</block>