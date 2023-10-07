<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <li><a href="#" class="base_button base_button_s" onclick="syncGoods()">一键同步云端商品</a></li>
                <auth rules="taokegoods:goods:add">
                    <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="taokegoods:goods:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">启用</li>
                            <li class="modal_select_option" value="0">禁用</li>
                        </ul>
                    </div>

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="cate_id" type="hidden" class="modal_select_value finder" value="{:input('pcat_id')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">一级类目（全部）</li>
                            <notempty name="cate_list">
                                <volist name="cate_list" id="cat">
                                    <li class="modal_select_option" value="{$cat.cate_id}">{$cat.name}</li>
                                </volist>
                            </notempty>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索商品ID、标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">GOODS_ID</td>
                <td style="width: 5%;">img</td>
                <td style="width: 15%;">商品标题</td>
                <td style="width: 5%;">所属类目</td>
                <td style="width: 5%;">原价</td>
                <td style="width: 5%;">券额</td>
                <td style="width: 5%;">折后价</td>
                <td style="width: 5%;">平台</td>
                <td style="width: 5%;">销量</td>
                <td style="width: 10%;">优惠券时间</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 5%;">状态</td>
                <td style="width: 5%;">置顶</td>
                <td style="width: 5%;">添加时间</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.goods_id}
                        </td>
                        <td>
                            <div class="thumb">
                                    <img src="{:img_url($vo['img'],'200_200','thumb')}"/>
                            </div>
                        </td>
                        <td>{$vo.title}</td>
                        <td>
                            <span>{$data[$vo['cate_id']]}</span>
                        </td>
                        <td>{$vo.price}</td>
                        <td>{$vo.coupon_price}</td>
                        <td>{$vo.discount_price}</td>
                        <td>
                            <switch name="vo['shop_type']">
                                <case value="B">
                                    天猫
                                </case>
                                <case value="C">
                                    淘宝
                                </case>
                                <case value="P">
                                    拼多多
                                </case>
                                <case value="J">
                                    京东
                                </case>
                            </switch>
                        </td>
                        <td>{$vo.volume}</td>
                        <td>
                            {$vo.coupon_start_time|time_format='','Y-m-d H:i:s'}<br/>
                            {$vo.coupon_end_time|time_format='','Y-m-d H:i:s'}
                        </td>
                        <td>{$vo.sort}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taokegoods:goods:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('changeStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('taokegoods:goods:recommand')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.is_recommand}" tgradio-name="is_recommand" tgradio="{:url('changeRecommandStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            {$vo.create_time|time_format='','Y-m-d H:i:s'}
                        </td>
                        <td>
                            <auth rules="taokegoods:goods:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a><br/>
                            </auth>
                            <if condition="$vo.is_top eq 1">
                                <auth rules="taokegoods:goods:cancle_top">
                                    <a ajax-confirm ajax="get" href="{:url('cancleTop',array('id'=>$vo['id']))}?__JUMP__">取消置顶</a><br/>
                                </auth>
                                <else/>
                                <auth rules="taokegoods:goods:set_top">
                                    <a ajax-confirm ajax="get" href="{:url('top',array('id'=>$vo['id']))}?__JUMP__">置顶</a><br/>
                                </auth>
                            </if>
                            <auth rules="taokegoods:goods:delete">
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

        function syncGoods(){
            $.ajax({
                url: "{:url('syncGoods')}",
                dataType: "json",
                type: "POST",
                data: {},
                success:function(data){
                    layer.msg(data.message);
                }
            });
        }
    </script>

</block>

<block name="layer">
    <include file="components/recommend_pop" />
</block>