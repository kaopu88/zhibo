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
                    <td class="field_name">ip地址</td>
                    <td>
                        <input class="base_text" name="ip_adress" value="{$_info.ip_adress}"/>
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
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="0">可用</option>
                            <option value="-1">禁用</option>
                            <option value="1">永久有效</option>
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