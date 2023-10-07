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
                            <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                <img src="{:img_url($vo.cons_avatar,'200_200','avatar')}"/>
                                <div class="thumb_level_box">
                                    <img title="{$vo.cons_level_name}" src="{$vo.cons_level_icon}"/>
                                </div>
                            </a>
                            <p class="thumb_info">
                                <a href="javascript:;">{$vo.cons_nickname}</a>
                            </p>
                        </div>
                    </td>
                    <td> {$vo.rel_type|enum_name='bean_trade_types'}</td>
                    <td>
                        <switch name="vo['rel_type']">
                            <case value="live_gift">
                                {$vo.rel_info.gift_no}<br/>
                                赠送{$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="video_gift">
                                {$vo.rel_info.gift_no}<br/>
                                赠送{$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="vip">
                                {$vo.rel_info.order_no}<br/>
                                {$vo.rel_info.name}
                            </case>
                            <case value="barrage">
                                {$vo.rel_info.order_no}<br/>
                                发送{$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="loss">
                                {$vo.rel_info.order_no}<br/>
                                用户超过{$vo.rel_info.name},已清算{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="props">
                                {$vo.rel_info.order_no}<br/>
                                发送{$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="liudanji">
                                {$vo.rel_info.order_no}<br/>
                                {$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="cover_star_vote">
                                {$vo.rel_info.order_no}<br/>
                                {$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="buyprops">
                                {$vo.rel_info.order_no}<br/>
                                {$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                            <case value="live">
                                {$vo.rel_info.order_no}<br/>
                                {$vo.rel_info.name}，消费{$vo.rel_info.price}{:APP_BEAN_NAME}
                            </case>
                        </switch>
                    </td>
                    <td>{$vo.total_fee|bean_to_rmb}元</td>
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