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
            <h1>{$admin_last.name} 【{$_info.title}】</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:url('subscription')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">认购状态</td>
                    <td>
                        <select class="base_select" name="rec_status" selectedval="{$_info.rec_status}">
                            <option value="">请选择</option>
                            <option value="1">开启</option>
                            <option value="0">关闭</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">认购总额</td>
                    <td>
                        <input readonly class="base_text total_fee" value="0"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">总份额</td>
                    <td>
                        <input class="base_text" name="total" value="{$_info.total}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">每份单价</td>
                    <td>
                        <input class="base_text" name="price" value="{$_info.price}"/>
                        <p class="field_tip">单位：元</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">起步份数</td>
                    <td>
                        <input class="base_text" name="start_num" value="{$_info.start_num}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">截止时间</td>
                    <td>
                        <input readonly placeholder="请选择时间,默认为待定" class="base_text" name="deadline"
                               value="{$_info.deadline|time_format='','Y-m-d H:i'}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">虚拟销售量</td>
                    <td>
                        <input class="base_text" name="v_sales" value="{$_info.v_sales}"/>
                        <p class="field_tip">当前真实销售量：{$_info.sales},设置为-1则不使用虚拟值</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">福利介绍</td>
                    <td>
                        <textarea placeholder="" class="base_textarea" name="welfare">{:br2nl($_info['welfare'])}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">设置</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>
        $("[name=deadline]").flatpickr({
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
        });
    </script>

</block>