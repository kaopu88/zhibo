<div title="短视频审核" class="layer_box film_audit_handler pa_10" dom-key="film_audit_handler"
     popbox-action="{:url('video/audit')}" popbox-get-data="{:url('video/audit')}" popbox-area="800px,700px">
    <table class="content_info2">
        <tr>
            <td class="tcplayer" colspan="2"></td>
        </tr>
        <tr>
            <td class="field_name">审核状态</td>
            <td>
                <label class="base_label2"><input name="audit_status" value="2" type="radio"/>通过</label>
                <label class="base_label2"><input name="audit_status" value="3" type="radio"/>驳回</label>
                <label class="base_label2"><input name="audit_status" value="13" type="radio"/>驳回并删除</label>
            </td>
        </tr>
        <tr class="reason_tr">
            <td class="field_name">驳回原因</td>
            <td>
                <textarea name="reason" style="height: 100px;" class="base_text"></textarea>
                <div class="mt_5">
                    <a class="reason_link" href="javascript:;">内容违规，可能有涉黄信息或者低俗信息</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，可能有涉暴恐信息</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，可能有涉政治敏感信息</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，侵犯他人版权</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，可能有违规广告或者谣言信息</a><br/>
                    <a class="reason_link" href="javascript:;">视频内容不符合平台规范</a><br/>
                    <a class="reason_link" href="javascript:;">视频画面模糊不清等原因、质量不符合平台标准</a><br/>
                    <a class="reason_link" href="javascript:;">请勿重复上传视频</a>
                </div>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频描述</td>
            <td>
                <textarea name="describe" style="height: 70px;" class="base_text"></textarea>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">话题列表</td>
            <td>
                <ul class="label_ul">
                    <li class="label_li">
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">标签列表</td>
            <td>
                <ul class="label_ul">
                    <li class="label_li">
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">版权标识</td>
            <td>
                <label class="base_label2"><input name="copy_right" value="1" type="radio"/>有标识</label>
                <label class="base_label2"><input name="copy_right" value="0" type="radio"/>无标识</label>
                <div class="field_tip">是否有第三方平台的水印LOGO或者包含侵权内容</div>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频备注</td>
            <td><input class="base_text" name="source" readonly value=""/></td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">作者信息</td>
            <td><input class="base_text" name="author" readonly value=""/></td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频评分</td>
            <td>
                <div class="star_box"></div>
                <div>
                    <span class="star_tip">没有评分</span>
                </div>
            </td>
        </tr>

        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value=""/>
                <div class="flex_end">
                    <div data-next="0" class="base_button sub_btn2 mt_10" style="margin-right:10px;">提交</div>
                    <div data-next="1" class="base_button base_button_orange sub_btn2 mt_10" style="margin-left: 10px;">
                        提交并审核下一个
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div style="height: 30px"></div>
</div>