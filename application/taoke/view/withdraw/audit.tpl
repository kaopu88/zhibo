<block name="css">
    <style>
        .rule_item {
            display: inline-block;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 0px 5px;
            border-radius: 5px;
            margin: 0 3px 3px 0;
            cursor: pointer;
            font-size: 12px;
            width: 140px;
            text-align: left;
        }

        .rule_item .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
            float: right;
            margin-right: 3px;
            margin-top: 8px;
        }

        .rule_item:hover {
            color: #e60012;
        }
    </style>
</block>
<div title="提现编辑" class="layer_box audit pa_10" dom-key="audit" popbox-action="{:url('withdraw/update')}" popbox-get-data="{:url('withdraw/update')}" popbox-area="500px,350px">
    <table class="content_info2">
        <tr class="edit_tr">
            <td class="field_name">标注</td>
            <td>
                <textarea name="describe" style="height: 70px;" class="admin_remark"></textarea>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">审核</td>
            <td>
                <label class="base_label2"><input name="status" value="1" type="radio"/>打款</label>
                <label class="base_label2"><input name="status" value="0" type="radio"/>拒绝</label>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value=""/>
                <div class="base_button sub_btn mt_10">提交</div>
            </td>
        </tr>
    </table>
    <div style="height: 30px"></div>
</div>

