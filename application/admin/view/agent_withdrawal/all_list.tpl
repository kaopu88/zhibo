<extend name="public:base_nav"/>
<block name="css">
    <style>
        .invalid {
            opacity: 0.6;
        }
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
            ]
        };
    </script>
    <script src="__JS__/loss/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <include file="components/tab_nav"/>

        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">

                    <div class="filter_search">
                        <input placeholder="{:config('app.agent_setting.agent_name')}ID" type="text" name="agent_id" value="{:input('agent_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">公会信息</td>
                <td style="width: 20%;">订单详情</td>
                <td style="width: 10%;">提现金额</td>
                <td style="width: 10%;">打款金额</td>
                <td style="width: 5%;">手续费</td>
                <td style="width: 5%;">税费</td>
                <td style="width: 8%;">提现账户</td>
                <td style="width: 8%;">审核状态</td>
                <td style="width: 10%;">相关时间</td>
                <td style="width: 8%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr class="{$vo.id== '1'? 'id':''}" data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.id}
                        </td>

                        <td>
                            <div class="thumb">
                                <a href="javascript:;" class="thumb_img">
                                    <img src="{:img_url($vo['logo'],'200_200','logo')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.name}<br/>
                                        {:$vo['level']+1}级&nbsp;&nbsp;
                                    </a>
                                </p>
                            </div>
                        </td>

                        <td>订单号：{$vo.descr}</td>
                        <td>{$vo.millet}</td>
                        <td>{$vo.rmb}</td>
                        <td>{$vo.cash_fee}</td>
                        <td>{$vo.cash_taxes}</td>
                        <td>
                            <switch name="vo['casy_type']">
                                <case value="0">
                                    <span class="fc_blue">支付宝</span><br/>
                                </case>
                                <case value="1">
                                    <span class="fc_blue">微信</span><br/>
                                </case>
                                <case value="2">
                                    <span class="fc_blue">银行卡</span><br/>
                                </case>
                            </switch>
                            姓名:{$vo.name}<br/>
                            联系方式:{$vo.contact_phone}<br/>
                            账户:{$vo.account}<br/>
                            <eq name="vo['casy_type']" value="2">
                                开户行:{$vo.card_name}<br/>
                            </eq>
                        </td>

                        <td>
                            <switch name="vo['audit_status']">
                                <case value="2">
                                    <span class="fc_red">已拒绝 (原因：{$vo.admin_remark})</span>
                                </case>
                                <case value="1">
                                    <span class="fc_green" style="color: #32ad35">已打款</span><br/>
                                </case>
                                <case value="0">
                                    <span class="fc_blue">申请中</span><br/>
                                </case>
                            </switch>
                        </td>

                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            <notempty name="$vo.handler_time">处理：{$vo.handler_time|time_format='未处理'}</notempty>
                        </td>
                        <td>
                            <if condition="$vo['audit_status'] == '0'">
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
    </script>
</block>

<block name="layer">
    <include file="agent_withdrawal/millet_audit_update"/>
</block>