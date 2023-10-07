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
                    <td class="field_name">规则名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">规则类型</td>
                    <td>
                        <input class="base_text" name="type" value="{$_info.type}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">变更类型</td>
                    <td>
                        <select class="base_select" name="change_type" selectedval="{$_info.change_type}">
                            <option value="inc">增加</option>
                            <option value="exp">减少</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">最大值</td>
                    <td>
                        <input class="base_text" name="full_value" value="{$_info.full_value}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">最高分</td>
                    <td>
                        <input class="base_text" name="full_score" value="{$_info.full_score}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">后台管理</td>
                    <td>
                        <select class="base_select" name="admin" selectedval="{$_info.admin}">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">当日最大值</td>
                    <td>
                        <input class="base_text" name="day_max" value="{$_info.day_max}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">规则模板</td>
                    <td>
                        <textarea name="tpl" class="base_textarea">{$_info.tpl}</textarea>
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