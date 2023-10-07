<div class="layer_box anchor_location_box" dom-key="anchor_location_box" title="设置直播位置"
     popbox-action="{:url('anchor/location')}" popbox-get-data="{:url('anchor/location')}">
    <div class="pa_20">
        <table class="content_info2" style="width: 100%">
            <tr>
                <td>定位类型</td>
                <td>
                    <select name="location_type" class="base_select">
                        <option value="">请选择</option>
                        <option value="unknown">始终未知</option>
                        <option value="auto">自动定位</option>
                        <option value="static">指定位置</option>
                    </select>
                </td>
            </tr>
            <tr class="static_tr">
                <td>指定位置</td>
                <td>
                    <div><span class="location_city_str"></span> [<span class="location_lng_str"></span>,<span class="location_lat_str"></span>]</div>
                    <div class="mt_10">
                        <input name="city" type="hidden" value=""/>
                        <input name="lat" type="hidden" value=""/>
                        <input name="lng" type="hidden" value=""/>
                        <div class="base_button base_button_gray open_map">打开地图选择位置</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="hidden" name="user_id" value="" />
                    <div class="base_button_div" style="max-width:480px;">
                        <div class="base_button sub_btn">保存设置</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>