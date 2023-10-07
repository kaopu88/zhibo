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
                    <td class="field_name">名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="thumb" value="{$_info.thumb}" type="text" class="base_text border_left_radius"/>
                            <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="vip_thumb"
                               uploader-field="thumb">上传</a>
                        </div>
                        <div imgview="[name=thumb]" style="width: 120px;margin-top: 10px;"><img src="{$_info.thumb}"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">apple 内购ID</td>
                    <td>
                        <input class="base_text" name="apple_id" value="{$_info.apple_id}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">单价</td>
                    <td>
                        <input class="base_text" name="rmb" value="{$_info.rmb}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">等值{:config('app.product_info.bean_name')}</td>
                    <td>
                        <input class="base_text" name="price" value="{$_info.price}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">有效时长</td>
                    <td>
                        <input class="base_text" name="length" value="{$_info.length}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">单位</td>
                    <td>
                        <select class="base_select" name="unit" selectedval="{$_info.unit}">
                            <option value="d">日</option>
                            <option value="w">周</option>
                            <option value="m">月</option>
                            <option value="y">年</option>
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
</block>