<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <table class="content_info2 mt_10">
            <tr>
                <td class="field_name">检测类型</td>
                <td class="field_value">
                    <select name="type" class="base_select">
                        <option value="agent">按{:config('app.agent_setting.agent_name')}查找</option>
                        <option value="promoter">按{:config('app.agent_setting.promoter_name')}查找</option>
                        <option value="user">直接查找用户</option>
                    </select>
                </td>
            </tr>
            <!--<tr>
                <td class="field_name">{:config('app.agent_setting.promoter_name')}ID</td>
                <td class="field_value">
                    <div class="base_group">
                        <input placeholder="" suggest-value="[name=promoter_uid]" suggest="/live_film/get_suggests.html" style="width: 309px;" value="" type="text" class="base_text promoter_uid">
                        <input type="hidden" name="promoter_uid" value="">
                        <a fill-value="[name=promoter_uid]" fill-name=".promoter_uid" layer-open="/live_film/find.html" href="javascript:;" class="base_button base_button_gray select_film_btn">选择{:config('app.agent_setting.promoter_name')}</a>
                    </div>
                </td>
            </tr>-->

            <tr class="agent_id_tr">
                <td class="field_name">{:config('app.agent_setting.agent_name')}ID</td>
                <td class="field_value">
                    <input name="agent_id" class="base_text" value=""/>
                </td>
            </tr>

            <tr class="promoter_uid_tr">
                <td class="field_name">{:config('app.agent_setting.promoter_name')}ID</td>
                <td class="field_value">
                    <input name="promoter_uid" class="base_text" value=""/>
                </td>
            </tr>

            <tr class="user_ids_tr">
                <td class="field_name">用户ID</td>
                <td class="field_value">
                    <textarea name="user_ids" placeholder="多个用户ID，请用换行符隔开" style="height: 100px;"
                              class="base_textarea"></textarea>
                </td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td class="field_value">
                    <div class="base_button start_btn">开始查找</div>
                </td>
            </tr>
        </table>
    </div>

    <script>
        $(function () {
            checkType();
            $('[name=type]').change(function () {
                checkType();
            });
            $('.start_btn').click(function () {
                var type = $('[name=type] option:selected').val();
                var data = {type: type};
                if (type == 'agent') {
                    data['agent_id'] = $('[name=agent_id]').val();
                } else if (type == 'promoter') {
                    data['promoter_uid'] = $('[name=promoter_uid]').val();
                } else {
                    data['user_ids'] = $('[name=user_ids]').val();
                }
                $s.post('{:url("check")}', data, function (result, next) {
                    result['reload']=false;
                    if (result['status'] == 0) {
                        next();
                    } else {
                        next();
                    }
                });
            });

        });

        function checkType() {
            var type = $('[name=type] option:selected').val();
            $('.agent_id_tr,.promoter_uid_tr,.user_ids_tr').hide();
            if (type == 'agent') {
                $('.agent_id_tr').show();
            } else if (type == 'promoter') {
                $('.promoter_uid_tr').show();
            } else {
                $('.user_ids_tr').show();
            }
        }


    </script>


</block>

<block name="layer">
</block>