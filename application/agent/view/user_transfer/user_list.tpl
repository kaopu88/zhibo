<div class="table_slide">
    <table class="content_list mt_10">
        <thead>
        <tr>
            <td style="width: 7%;">ID</td>
            <td style="width: 15%;">用户信息</td>
            <td style="width: 9%;">用户类型</td>
            <td style="width: 10%;">用户属性</td>
            <td style="width: 13%;">{$transfer_name}信息</td>
            <td style="width: 8%;">注册时间</td>
            <td style="width: 9%;">状态</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="user_list">
            <volist name="user_list" id="vo">
                <tr data-id="{$vo.user_id}">
                    <td>{$vo.user_id}</td>
                    <td>
                        <include file="user/user_info"/>
                    </td>
                    <td>
                        <include file="user/user_type"/>
                    </td>
                    <td>
                        <b>{$vo.city_info.name|default='未知'}</b><br/>
                        <include file="user/user_vip_status"/>
                        <br/>
                        <eq name="vo['verified']" value="1">
                            <span class="fc_green">已认证</span>
                            <else/>
                            <span class="fc_gray">未认证</span>
                        </eq>
                    </td>
                    <td>
                        <include file="public/vo_agent"/>
                    </td>
                    <td>{$vo.create_time}</td>
                    <td>
                        <div tgradio-not="1" tgradio-on="1" tgradio-off="0"
                            tgradio-value="{$vo.status}"
                            tgradio-name="status" tgradio=""></div>
                    </td>
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

<block name="layer">
    <include file="user/remark_pop"/>
</block>