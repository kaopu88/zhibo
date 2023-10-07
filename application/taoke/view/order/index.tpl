<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taoke:order:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="order_status" type="hidden" class="modal_select_value finder" value="{:input('order_status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="13">已失效</li>
                            <li class="modal_select_option" value="12">已付款</li>
                            <li class="modal_select_option" value="3">已结算</li>
                        </ul>
                    </div>

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="rebate" type="hidden" class="modal_select_value finder" value="{:input('rebate')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="0">未返利</li>
                            <li class="modal_select_option" value="1">已返利</li>
                        </ul>
                    </div>

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索商品标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">匹配用户</td>
                <td style="width: 10%;">标题</td>
                <td style="width: 10%;">父订单号</td>
                <td style="width: 10%;">子订单号</td>
                <td style="width: 5%;">下单时间</td>
                <td style="width: 5%;">图片</td>
                <td style="width: 5%;">店铺类型</td>
                <td style="width: 5%;">下单金额</td>
                <td style="width: 5%;">佣率</td>
                <td style="width: 5%;">佣金</td>
                <td style="width: 5%;">结算时间</td>
                <td style="width: 5%;">返利状态</td>
                <td style="width: 5%;">分佣详情</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            <notempty name="vo.nickname">
                                {$vo.nickname}
                                <else/>
                                无
                            </notempty>
                        </td>
                        <td>{$vo.title}</td>
                        <td>{$vo.goods_order}</td>
                        <td>{$vo.goods_sonorder}</td>
                        <td>
                            {$vo.click_time|time_format='','Y-m-d H:i:s'}
                        </td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['img'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['img'],'200_200','thumb')}"/>
                                </a>
                            </div>
                        </td>
                        <td>{$vo.shop_type}</td>
                        <td>{$vo.pay_price}</td>
                        <td>{$vo.commission_rate}%</td>
                        <td>{$vo.commission}</td>
                        <td>
                            <notempty name="vo.earning_time">{$vo.earning_time|time_format='','Y-m-d H:i:s'}</notempty>
                        </td>
                        <td>
                            <if condition="vo.rebate eq 1">
                                已返利
                                <else/>
                                未返利
                            </if>
                        </td>
                        <td>
                            <a layer-area="500px,200px" layer-open="{:url('order/getCommission', array("goods_order"=>$vo['goods_order'],"goods_sonorder"=>$vo['goods_sonorder']))}">
                                查看分佣
                            </a>
                        </td>
                        <td>
                            <auth rules="taoke:order:delete">
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