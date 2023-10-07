<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="2">通过</li>
                            <li class="modal_select_option" value="1">拒绝</li>
                        </ul>
                    </div>

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索手机号、姓名、用户ID"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">userId</td>
                <td style="width: 5%;">真实姓名</td>
                <td style="width: 10%;">身份证号码</td>
                <td style="width: 8%;">身份证正面照</td>
                <td style="width: 8%;">身份证反面照</td>
                <td style="width: 8%;">手持身份证照</td>
                <td style="width: 5%;">申请时间</td>
                <td style="width: 5%;">开通费用</td>
                <td style="width: 6%;">支付状态</td>
                <td style="width: 10%;">支付订单号</td>
                <td style="width: 5%;">审核状态</td>
                <td style="width: 5%;">审核时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>{$vo.name}</td>
                        <td>{$vo.card_num}</td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['front_idcard'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['front_idcard'],'200_200','thumb')}"/>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['back_idcard'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['back_idcard'],'200_200','thumb')}"/>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['hand_idcard'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['hand_idcard'],'200_200','thumb')}"/>
                                </a>
                            </div>
                        </td>
                        <td>{$vo.create_time|time_format='','Y-m-d H:i:s'}</td>
                        <td>
                            <if condition="vo.open_fee != 0">
                                {$vo.open_fee}
                            </if>
                        </td>
                        <td>
                            <if condition="vo.open_fee != 0">
                                <switch name="vo['pay_status']">
                                    <case value="0">
                                        未支付
                                    </case>
                                    <case value="1">
                                        已支付
                                    </case>
                                </switch>
                            </if>
                        </td>
                        <td>{$vo.pay_order}</td>
                        <td>
                            <switch name="vo['status']">
                                <case value="0">
                                    <auth rules="taoke:audit:update">
                                        <a layer-area="700px,400px" layer-open="{:url('audit/audit', array("id"=>$vo['id']))}">
                                            审核
                                        </a>
                                    </auth>
                                </case>
                                <case value="1">
                                    审核拒绝
                                </case>
                                <case value="2">
                                    审核通过
                                </case>
                            </switch>
                        </td>
                        <td>{$vo.check_time|time_format='','Y-m-d H:i:s'}</td>
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