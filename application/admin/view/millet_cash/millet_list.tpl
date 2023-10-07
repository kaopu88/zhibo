        <div class="filter_box mt_10">
                <div class="filter_nav">
                    已选择&nbsp;>&nbsp;
                    <p class="filter_selected"></p>
                </div>

                <div class="filter_options">
                    <ul class="filter_list"></ul>
                    <div class="filter_order">
                        <auth rules="admin:anchorApply:reviews">
                            <div ajax="post" ajax-url="{:url('millet_cash/tx',['pay_status'=>'0'])}" ajax-target="list_id"
                                 class="base_button base_button_s base_button_gray">批量审核
                            </div>
                        </auth>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
<div class="table_slide">
    <table class="content_list mt_10 table_fixed">
    <thead>
    <tr>
        <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
        <td style="width: 5%;">ID</td>
        <td style="width: 13%;">提现用户</td>
        <td style="width: 23%;">订单详情</td>
        <td style="width: 10%;">打款金额</td>
        <td style="width: 13%;">提现账户</td>
        <td style="width: 5%;">提现状态</td>
        <td style="width: 10%;">提现时间</td>
        <td style="width: 8%;">结算方式</td>
        <td style="width: 10%;">操作</td>
    </tr>
    </thead>
    <tbody>
    <notempty name="cash_list">
        <volist name="cash_list" id="vo">
            <tr data-id="{$vo.user_id}">
                <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                <td>{$vo.id}</td>
                <td>
                    <div class="thumb">
                        <a target="_blank" href="{:url('user/detail',['user_id'=>$vo.user_id])}"
                           class="thumb_img thumb_img_avatar">
                            <img src="{:img_url($vo.avatar,'200_200','avatar')}"/>
                            <div class="thumb_level_box">
                                <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
                            </div>
                        </a>
                        <p class="thumb_info">
                            <a target="_blank" href="{:url('user/detail',['user_id'=>$vo.user_id])}">
                                [{$vo.user_id}]<br/>
                                {$vo|user_name}<br/>
                                <notempty name="$vo.anchor">
                                    公会:{$vo.anchor.agent_name}
                                </notempty>
                            </a>
                        </p>
                    </div>
                </td>
                <td>
                    订单号：{$vo.descr}
                </td>
                <td>
                    {$vo.rmb}
                </td>
                <td>
                    {$vo.title}<Br>
                    账户:{$vo.accout_num}
                </td>
                <td>
                    <switch name="vo['status']">
                        <case value="failed">
                            <span class="fc_red">已拒绝</span>
                        </case>
                        <case value="success">
                            <span class="fc_green">已打款</span><br/>
                        </case>
                        <case value="wait">
                            <span class="fc_blue">申请中</span><br/>
                        </case>
                    </switch>
                </td>
                <td>{$vo.create_time}</td>
                <td>
                    <switch name="vo['cash_type']">
                        <case value="2">
                            <span class="fc_orange">平台用户结算</span>
                        </case>
                        <case value="1">
                            <span class="fc_orange">平台结算</span><br/>
                        </case>
                        <case value="0">
                            <span class="fc_red">公会结算</span><br/>
                        </case>
                    </switch>
                </td>
                <td>
                    <if condition="$vo['status'] == 'wait'">
                        <a data-id="id:{$vo.id}" poplink="millet_audit_update" href="javascript:;">编辑</a>
                        <br/>
                    </if>
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
