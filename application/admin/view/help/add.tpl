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
                    <td class="field_name">所属类目</td>
                    <td>
                        <div typecl="{:url('help/get_tree')}">
                            <input type="hidden" class="type_val_0" name="pcat_id" value="{$_info.pcat_id}">
                            <input type="hidden" class="type_val_1" name="cat_id" value="{$_info.cat_id}">
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">文章标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">标识符</td>
                    <td>
                        <input class="base_text" name="mark" value="{$_info.mark}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">内容</td>
                    <td>
                        <textarea style="width:900px;height:400px;" name="content" ueditor>{$_info.content}</textarea>
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
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>

</block>