<div class="table_slide">
    <table class="content_list mt_10">
        <thead>
        <tr>
            <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
            <td style="width: 10%;">ID</td>
            <td style="width: 15%;">赠送用户</td>
            <td style="width: 10%;">赠送类型</td>
            <td style="width: 15%;">赠送内容</td>
            <td style="width: 15%;">主播信息</td>
            <td style="width: 15%;">业绩归属</td>
            <td style="width: 10%;">赠送日期</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="millet_list">
            <volist name="millet_list" id="vo">
                <tr data-id="{$vo.user_id}">
                    <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                    <td>{$vo.id}</td>
                    <td>
                        <div class="thumb">
                            <a href="{:url('user/detail',['user_id'=>$vo.cont_info.user_id])}"
                            class="thumb_img thumb_img_avatar" style="max-width: 50px;">
                                <img src="{:img_url($vo['cont_info']['avatar'],'200_200','avatar')}"/>
                                <div class="thumb_level_box">
                                    <img title="{$vo.cont_info.level_name}" src="{$vo.cont_info.level_icon}"/>
                                </div>
                            </a>
                            <p class="thumb_info">
                                <a href="{:url('user/detail',['user_id'=>$vo.cont_info.user_id])}">
                                    [{$vo.cont_info.user_id}]<br/>
                                    {$vo.cont_info|user_name}
                                </a>
                            </p>
                        </div>
                    </td>
                    <td> {$vo.trade_type|enum_name='millet_trade_types'}</td>
                    <td>
                        {$vo.trade_info.gift_no}<br/>
                        <a href="javascript:;">{$vo.trade_info.name}</a>&nbsp;+{$vo.trade_info.conv_millet}
                    </td>
                    <td>
                        <div class="thumb">
                            <a target="_blank" href="{:url('anchor/detail',['user_id'=>$vo.anchor_info.user_id])}" class="thumb_img thumb_img_avatar" style="max-width: 50px;">
                                <img src="{:img_url($vo.anchor_info.avatar,'200_200','avatar')}"/>
                                <div class="thumb_level_box">
                                    <img title="{$vo.anchor_info.level_name}" src="{$vo.anchor_info.level_icon}"/>
                                </div>
                            </a>
                            <p class="thumb_info">
                                <a target="_blank" href="{:url('anchor/detail',['user_id'=>$vo.anchor_info.user_id])}">{$vo.anchor_info.nickname}</a>
                            </p>
                        </div>
                    </td>
                    <td>
                        <include file="public/vo_agent" />
                    </td>
                    <td>{$vo.create_time|time_format}</td>
                </tr>
            </volist>
            <else/>
            <tr>
                <td>
                    <div class="content_empty">
                        <div class="content_empty_icon"></div>
                        <p class="content_empty_text">暂未查询到相关数据</p>
                    </div>
                </td>
            </tr>
        </notempty>
        </tbody>
    </table>
</div>