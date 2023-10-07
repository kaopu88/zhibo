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
                    <td class="field_name">道具</td>
                    <td>
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=props_id]" suggest="{:url('props/get_suggests')}"
                                   style="width: 309px;" value="{$_info.prop}" type="text" class="base_text prop border_left_radius" name="prop">
                            <input type="hidden" name="props_id" value="{$_info.props_id}">
                            <a fill-value="[name=props_id]" fill-name=".prop" layer-open="{:url('props/find')}"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择道具</a>
                        </div>
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
                    <td class="field_name">等值{:APP_BEAN_NAME}</td>
                    <td>
                        <input class="base_text" name="conv_millet" value="{$_info.conv_millet}"/>
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
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
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