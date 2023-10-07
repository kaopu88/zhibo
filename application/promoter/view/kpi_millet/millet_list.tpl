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
                    <include link="{:url('user/detail',['user_id'=>$vo.cont_info.user_id])}" file="user/user_info"/>
                </td>
                <td> {$vo.trade_type|enum_name='millet_trade_types'}</td>
                <td>
                    {$vo.trade_info.gift_no}<br/>
                    <a href="javascript:;">{$vo.trade_info.name}</a>&nbsp;+{$vo.trade_info.conv_millet}
                </td>
                <td>
                    <include link="{:url('user/detail',['user_id'=>$vo.anchor_info.user_id])}" file="user/user_info"/>
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