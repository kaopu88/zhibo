<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taoke:duomai_order:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
        </div>

        <div class="content_toolbar_search">
            <div class="base_group">

                <div class="modal_select modal_select_s">
                    <span class="modal_select_text"></span>
                    <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                    <ul class="modal_select_list">
                        <li class="modal_select_option" value="">全部</li>
                        <li class="modal_select_option" value="-1">无效</li>
                        <li class="modal_select_option" value="0">未确认</li>
                        <li class="modal_select_option" value="1">确认</li>
                        <li class="modal_select_option" value="2">结算</li>
                    </ul>
                </div>

                <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索广告id或名称"/>
                <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
            </div>
        </div>

        <div class="table_slide">
        <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">计划名称</td>
                <td style="width: 5%;">媒体id</td>
                <td style="width: 7%;">推广计划id</td>
                <td style="width: 7%;">下单用户id</td>
                <td style="width: 10%;">订单号</td>
                <td style="width: 10%;">下单时间</td>
                <td style="width: 5%;">订单金额</td>
                <td style="width: 5%;">订单佣金</td>
                <td style="width: 8%;">订单结算金额</td>
                <td style="width: 8%;">订单结算佣金</td>
                <td style="width: 5%;">订单状态</td>
                <td style="width: 10%;">结算时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.ads_name}</td>
                        <td>{$vo.site_id}</td>
                        <td>{$vo.link_id}</td>
                        <td>{$vo.euid}</td>
                        <td>{$vo.order_sn}</td>
                        <td>{$vo.order_time|time_format='','Y-m-d H:i:s'}</td>
                        <td>{$vo.orders_price}</td>
                        <td>{$vo.siter_commission}</td>
                        <td>{$vo.confirm_price}</td>
                        <td>{$vo.confirm_siter_commission}</td>
                        <td>
                            <switch name="vo['status']">
                                <case value="-1">
                                    无效
                                </case>
                                <case value="0">
                                    未确认
                                </case>
                                <case value="1">
                                    确认
                                </case>
                                <case value="2">
                                    结算
                                </case>
                            </switch>
                        </td>
                        <td>
                            <notempty name="vo.charge_time">{$vo.charge_time|time_format='','Y-m-d H:i:s'}</notempty>
                        </td>
                        <td>
                            <auth rules="taoke:duomai_order:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
                            </auth>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="components/recommend_pop" />
</block>