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
                    <td class="field_name">奖品名称</td>
                    <td>
                        <div class="base_group">
                            <input placeholder="" suggest-value="[name=gift_id]" suggest="/admin/gift/get_suggests.html"
                                   style="width: 309px;" value="{$_info.name}" type="text" class="base_text gift border_left_radius">
                            <input type="hidden" name="gift_id" value="{$_info.gift_id}">
                            <a fill-value="[name=gift_id]" fill-name=".gift" layer-open="/admin/gift/find.html"
                               href="javascript:;" class="base_button select_film_btn border_right_radius">选择奖品</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">中奖概率</td>
                    <td>
                        <input class="base_text" name="probability" value="{$_info.probability}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">奖品说明</td>
                    <td>
                        <textarea name="desc" class="base_text" style="height:120px;">{$_info.desc}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">奖品状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>

                <input class="find_params" type="hidden" name="activity_id" value="{$activity_id}"/>

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