<div class="table_slide">
    <table class="content_list mt_10 md_width">
        <thead>
        <tr>
            <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
            <td style="width: 10%;">ID</td>
            <td style="width: 15%;">消费用户</td>
            <td style="width: 10%;">消费类型</td>
            <td style="width: 20%;">消费内容</td>
            <td style="width: 10%;">折合RMB</td>
            <td style="width: 15%;">业绩归属</td>
            <td style="width: 10%;">消费日期</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="cons_list">
            <volist name="cons_list" id="vo">
                <tr data-id="{$vo.user_id}">
                    <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                    <td>{$vo.id}</td>
                    <td>
                        <div class="thumb">
                            <a href="{:url('user/detail',['user_id'=>$vo.user.user_id])}"
                            class="thumb_img thumb_img_avatar" style="max-width: 50px;">
                                <img src="{:img_url($vo['user']['avatar'],'200_200','avatar')}"/>
                                <div class="thumb_level_box">
                                    <img title="{$vo.user.level_name}" src="{$vo.user.level_icon}"/>
                                </div>
                            </a>
                            <p class="thumb_info">
                                <a href="{:url('user/detail',['user_id'=>$vo.user.user_id])}">
                                    [{$vo.user.user_id}]<br/>
                                    {$vo.user|user_name}
                                </a>
                            </p>
                        </div>
                    </td>
                    <td> {$vo.rel_type|enum_name='bean_trade_types'}</td>
                    <td>
                        <gt name="vo['loss_total']" value="0">
                            <span class="fc_red">不计入额：{$vo.loss_total}<br/></span>
                        </gt>

                        <switch name="vo['rel_type']">
                            <case value="live_gift">
                                {$vo.rel_info.gift_no}<br/>
                                赠送<a href="javascript:;">{$vo.rel_info.name}</a>，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="barrage">
                                {$vo.rel_info.order_no}<br/>
                                发送<a href="javascript:;">{$vo.rel_info.name}</a>，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="cover_star_vote">
                                {$vo.rel_info.order_no}<br/>
                                <a href="javascript:;">{$vo.rel_info.name}</a>，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="live_payment">
                                {$vo.rel_info.order_no}<br/>
                                <a href="javascript:;">{$vo.rel_info.name}</a>，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="user_package">
                                {$vo.rel_info.order_no}<br/>
                                <a href="javascript:;">{$vo.rel_info.name}</a>，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                        </switch>
                    </td>
                    <td>{$vo.total_fee|bean_to_rmb}元</td>
                    <td>
                        <include file="public/vo_agent"/>
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