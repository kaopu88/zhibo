<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="body">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">账号模式</td>
                <td>
                    <select class="base_select" name="status">
                        <option value="1">拒绝</option>
                        <option value="2">通过</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_name">备注</td>
                <td>
                    <input name="memo" type="text" class="base_text" value=""/>
                </td>
            </tr>

            <input type="hidden" name="id" value="{$id}">

        </table>

        <div class="mt_10" style="text-align: center;">
            <div class="base_button reg_btn">确定</div>
        </div>
    </div>
    <script>
        $('.reg_btn').click(function () {
            var status = $('[name=status] option:selected').val();
            var memo = $('[name=memo]').val();
            var id = $('[name=id]').val();
            $s.post("{:url('audit/audit')}", {
                'status': status,
                'memo': memo,
                'id': id,
            }, function (result) {
                if (result.status == 0) {
                    $s.success('操作成功!');
                } else {
                    $s.error('操作失败!');
                }
                setTimeout(function () {
                    if (hasParentWindow() && !isEmpty(result['url'])) {
                        parent.window.open(result['url'], '_self');
                    }
                }, 2000);
            });

        });
    </script>
</block>

<block name="layer">
</block>