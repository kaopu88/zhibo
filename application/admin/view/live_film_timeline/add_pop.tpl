<div class="add_box pa_10">
    <table class="content_info2">
        <tr>
            <td class="field_name">播放影片</td>
            <td>
                <div class="base_group">
                    <input placeholder="" suggest-value="[name=film_id]"
                           suggest="{:url('live_film/get_suggests')}" style="width: 309px;" value="" type="text"
                           class="base_text film_name"/>
                    <input type="hidden" name="film_id" value=""/>
                    <a fill-value="[name=film_id]" fill-name=".film_name"
                       layer-open="{:url('live_film/find')}" href="javascript:;"
                       class="base_button base_button_gray select_film_btn">选择影片</a>
                </div>
                <div class="field_tip">
                    开播前<a href="javascript:;" class="pilot_btn">试播</a>，播放不了？标记为<a class="fc_red"
                                                                                  href="javascript:;">已失效</a>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name">影片总时长</td>
            <td>
                <input duration="0" readonly class="base_text video_duration_str" value=""/>
            </td>
        </tr>

        <tr>
            <td class="field_name">播放位置</td>
            <td>
                <input name="play_position" class="base_text" value="0"/>
                <p class="field_tip">上次已播出位置，格式：时:分:秒，如 01:05:00，默认从头开始播</p>
            </td>
        </tr>
        <tr>
            <td class="field_name">直播标题</td>
            <td>
                <input placeholder="请先选择影片" name="live_title" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">直播封面</td>
            <td>
                <div class="base_group">
                    <input placeholder="请先选择影片" style="width: 309px;" name="live_cover" value=""
                           type="text"
                           class="base_text"/>
                    <a uploader-type="image" href="javascript:;" class="base_button"
                       uploader="live_film_cover"
                       uploader-field="live_cover">上传</a>
                </div>
                <div imgview="[name=live_cover]" style="width: 120px;margin-top: 10px;"><img src=""/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name">直播类型</td>
            <td>
                <select name="type" class="base_select"></select>
            </td>
        </tr>
        <tr class="type_val_tr">
            <td class="field_name">类型设置</td>
            <td>
                <input name="type_val" class="base_text" value=""/>
                <p class="field_tip"></p>
            </td>
        </tr>

        <tr>
            <td class="field_name">片头广告</td>
            <td>
                <ul class="json_list"></ul>
                <input name="ad_ids" type="hidden" value="{$_info.ad_ids}"/>
            </td>
        </tr>

        <tr>
            <td class="field_name">广告时长</td>
            <td>
                <input name="ad_duration" class="base_text" value="0"/>
                <p class="field_tip">广告会在广告时长内循环播放,格式：00:00:00 (时:分:秒)</p>
            </td>
        </tr>

        <tr>
            <td class="field_name">播出时间</td>
            <td>
                <div>
                    <input name="start_time" class="base_text" value=""/>到
                    <input readonly placeholder="系统自动计算" name="end_time" class="base_text" value=""/>
                </div>
                <div class="field_tip">
                    <a class="start_time_now" href="javascript:;">现在时间</a>&nbsp;<a class="start_time_next" href="javascript:;">推迟一天</a>&nbsp;定时器可能会有细微误差，所以系统可能会有30秒容差
                </div>
            </td>
        </tr>

        <tr class="anchor_tr">
            <td class="field_name">所属主播</td>
            <td>
                <div class="base_group">
                    <input placeholder="可选项" suggest-value="[name=anchor_uid]"
                           suggest="{:url('anchor/get_suggests')}" style="width: 309px;" value="" type="text"
                           class="base_text anchor_name"/>
                    <input type="hidden" name="anchor_uid" value=""/>
                    <a fill-value="[name=anchor_uid]" fill-name=".anchor_name"
                       layer-open="{:url('live_film/find_anchor')}" href="javascript:;"
                       class="base_button base_button_gray select_anchor_btn">选择主播</a>
                </div>
                <p class="field_tip anchor_tip">同一时段主播不能同时开多个直播间</p>
            </td>
        </tr>


        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button insert_btn">创建任务</div>
            </td>
        </tr>
    </table>
</div>