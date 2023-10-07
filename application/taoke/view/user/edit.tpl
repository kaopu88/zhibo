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
        <form action="{:url('edit')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">USERID</td>
                    <td>{$_info.user_id}</td>
                </tr>

                <tr>
                    <td class="field_name">用户昵称</td>
                    <td>{$_info.nickname}</td>
                </tr>

                <tr>
                    <td class="field_name">淘客余额</td>
                    <td>{$_info.taoke_money}</td>
                </tr>

                <tr>
                    <td class="field_name">用户等级</td>
                    <td>
                        <select class="base_select" name="taoke_level" selectedval="{$_info.taoke_level}">
                            <option value="0">无</option>
                            <notempty name="level_list">
                                <volist name="level_list" id="vo">
                                    <option value="{$vo.id}">{$vo.name}</option>
                                </volist>
                            </notempty>
                        </select>
                    </td>
                </tr>

                <tr class="type">
                    <td class="field_name">渠道ID</td>
                    <td>
                        <input class="base_text" name="relation_id" value="{$_info.relation_id}"/>
                    </td>
                </tr>

                <tr class="type">
                    <td class="field_name">会员运营ID</td>
                    <td>
                        <input class="base_text" name="special_id" value="{$_info.special_id}"/>
                    </td>
                </tr>

                <tr class="type">
                    <td class="field_name">拼多多PID</td>
                    <td>
                        <input class="base_text" name="pdd_pid" value="{$_info.pdd_pid}"/>
                    </td>
                </tr>

                <tr class="type">
                    <td class="field_name">京东PID</td>
                    <td>
                        <input class="base_text" name="jd_pid" value="{$_info.jd_pid}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">淘客提现状态</td>
                    <td>
                        <select class="base_select" name="taoke_money_status" selectedval="{$_info.taoke_money_status}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['user_id']">
                            <input name="user_id" type="hidden" value="{$_info.user_id}"/>
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