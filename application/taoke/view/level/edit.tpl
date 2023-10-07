<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .order, .good, .people, .money{
            display: none;
        }
    </style>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <include file="components/tab_nav"/>
        <form action="{:url('edit')}">
            <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                <ul class="layui-tab-title">
                    <li class="layui-this">基本设置</li>
                    <li>分销设置</li>
                    <li>升级条件设置</li>
                </ul>
                <div class="layui-tab-content">

                    <div class="layui-tab-item layui-show">
                        <table class="content_info2 mt_10">

                            <tr>
                                <td class="field_name">等级名称</td>
                                <td>
                                    <input class="base_text" name="name" value="{$_info.name}"/>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">等级</td>
                                <td>
                                    <input class="base_text" name="level" value="{$_info.level}" type="number"/>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">等级图片</td>
                                <td>
                                    <div class="base_group">
                                        <input style="width: 309px;" name="img" value="{$_info.img}" type="text" class="base_text"/>
                                        <a uploader-type="image" href="javascript:;" class="base_button" uploader="taoke_images" uploader-field="img" style=" float: initial;">上传</a>
                                    </div>
                                    <div imgview="[name=img]" style="width: 120px;margin-top: 10px;"><img src="{$_info.img}" class="preview"/></div>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">等级描述</td>
                                <td>
                                    <textarea style="height: 200px;" class="base_textarea" name="desc">{$_info.desc}</textarea>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">是否启用</td>
                                <td>
                                    <select class="base_select" name="status" selectedval="{$_info.status}">
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </td>
                            </tr>

                        </table>
                    </div>

                    <div class="layui-tab-item">
                        <table class="content_info2 mt_10">

                            <tr>
                                <td class="field_name">自购佣金比例</td>
                                <td>
                                    <input class="base_text" name="purchase" value="{$_info.purchase}"/> %
                                </td>
                            </tr>

                            <input type="hidden" name="promotion_level" value="{$_info.promotion_level}"/>

                            <tr class="promotion">
                                <td class="field_name">第1级佣金比率</td>
                                <td>
                                    <input class="base_text" name="promotion[]" value="0" id="pro-one"/> %
                                </td>
                            </tr>

                            <tr class="promotion">
                                <td class="field_name">第2级佣金比率</td>
                                <td>
                                    <input class="base_text" name="promotion[]" value="0" id="pro-two"/> %
                                </td>
                            </tr>

                            <tr class="promotion">
                                <td class="field_name">第3级佣金比率</td>
                                <td>
                                    <input class="base_text" name="promotion[]" value="0" id="pro-thr"/> %
                                </td>
                            </tr>

                            <!--<tr>
                                <td class="field_name">团队奖励</td>
                                <td>
                                    <input class="base_text" name="team_reward" value="{$_info.team_reward}"/> %
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">平级奖励</td>
                                <td>
                                    <input class="base_text" name="level_reward" value="{$_info.level_reward}"/> %
                                </td>
                            </tr>-->

                        </table>
                    </div>

                    <div class="layui-tab-item">
                        <table class="content_info2 mt_10">

                            <tr>
                                <td class="field_name">是否需要审核</td>
                                <td>
                                    <select class="base_select" name="upgrade_type" selectedval="{$_info.upgrade_type}">
                                        <option value="1">是</option>
                                        <option value="0">否</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">升级条件</td>
                                <td>
                                    <div style="max-width: 1000px">
                                        <label style="display: inline-block;line-height: 30px;">
                                            <input style="vertical-align: -2px;" type="checkbox" name="upgrade_condition[]" value="order" class="upgrade_condition"/>&nbsp;订单
                                        </label>
                                        <label style="display: inline-block;line-height: 30px;">
                                            <input style="vertical-align: -2px;" type="checkbox" name="upgrade_condition[]" value="people" class="upgrade_condition"/>&nbsp;人数
                                        </label>
                                        <!--<label style="display: inline-block;line-height: 30px;">
                                            <input style="vertical-align: -2px;" type="checkbox" name="upgrade_condition[]" value="good" class="upgrade_condition"/>&nbsp;商品
                                        </label>-->
                                        <label style="display: inline-block;line-height: 30px;">
                                            <input style="vertical-align: -2px;" type="checkbox" name="upgrade_condition[]" value="commission" class="upgrade_condition"/>&nbsp;佣金
                                        </label>
                                    </div>
                                </td>
                            </tr>

                            <tr class="order">
                                <td class="field_name">订单</td>
                                <td></td>
                            </tr>

                            <tr class="order">
                                <td class="field_name">自购订单数量</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][self]" value=""/> 个
                                </td>
                            </tr>

                            <tr class="order" id="order_num">
                                <td class="field_name">团队订单数量</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][team]" value=""/> 个
                                </td>
                            </tr>

                            <tr class="order_num order">
                                <td class="field_name">1级分销订单数</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][distri][]" value="" id="num-one"/> 个
                                </td>
                            </tr>

                            <tr class="order_num order">
                                <td class="field_name">2级分销订单数</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][distri][]" value="" id="num-two"/> 个
                                </td>
                            </tr>

                            <tr class="order_num order">
                                <td class="field_name">3级分销订单数</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][distri][]" value="" id="num-thr"/> 个
                                </td>
                            </tr>

                            <tr class="order">
                                <td class="field_name">自购订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][self]" value=""/> 元
                                </td>
                            </tr>

                            <tr id="order_money" class="order">
                                <td class="field_name">团队订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][team]" value=""/> 元
                                </td>
                            </tr>

                            <tr class="order_money order">
                                <td class="field_name">1级分销订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][distri][]" value="" id="mon-one"/> 元
                                </td>
                            </tr>

                            <tr class="order_money order">
                                <td class="field_name">2级分销订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][distri][]" value="" id="mon-two"/> 元
                                </td>
                            </tr>

                            <tr class="order_money order">
                                <td class="field_name">3级分销订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][distri][]" value="" id="mon-thr"/> 元
                                </td>
                            </tr>


                            <tr class="people">
                                <td class="field_name">人数</td>
                                <td></td>
                            </tr>

                            <tr id="people_num" class="people">
                                <td class="field_name">团队总人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[team]" value=""/> 个
                                </td>
                            </tr>

                            <tr class="people_num people">
                                <td class="field_name">1级分销人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[distri][]" value="" id="peo-one"/> 个
                                </td>
                            </tr>

                            <tr class="people_num people">
                                <td class="field_name">2级分销人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[distri][]" value="" id="peo-two"/> 个
                                </td>
                            </tr>

                            <tr class="people_num people">
                                <td class="field_name">3级分销人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[distri][]" value="" id="peo-thr"/> 个
                                </td>
                            </tr>

                            <tr class="good">
                                <td class="field_name">购买指定商品</td>
                                <td></td>
                            </tr>

                            <tr class="good">
                                <td class="field_name">商品id</td>
                                <td>
                                    <input class="base_text" name="good_condition" value="{$_info.good_condition}"/>
                                </td>
                            </tr>

                            <tr class="money">
                                <td class="field_name">佣金</td>
                                <td></td>
                            </tr>

                            <tr class="money">
                                <td class="field_name">自购佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[self]" value=""/> 元
                                </td>
                            </tr>

                            <tr id="commission" class="money">
                                <td class="field_name">团队佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[team]" value=""/> 元
                                </td>
                            </tr>

                            <tr class="commission money">
                                <td class="field_name">1级分销佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[distri][]" value="" id="com-one"/> 元
                                </td>
                            </tr>

                            <tr class="commission money">
                                <td class="field_name">2级分销佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[distri][]" value="" id="com-two"/> 元
                                </td>
                            </tr>

                            <tr class="commission money">
                                <td class="field_name">3级分销佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[distri][]" value=""  id="com-thr"/> 元
                                </td>
                            </tr>

                        </table>
                    </div>

                </div>
            </div>
            <input type="hidden" name="id" value="{$_info.id}"/>
            <div class="base_button_div">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
        $(function() {
            var promotion = '{:isset($_info.promotion) ? htmlspecialchars_decode($_info.promotion) : ""}';
            if(promotion != "") {
                promotion = JSON.parse(promotion);
                $('#pro-one').val(promotion[0]);
                $('#pro-two').val(promotion[1]);
                $('#pro-thr').val(promotion[2]);
            }
            var order_condition = '{:isset($_info.order_condition) ? htmlspecialchars_decode($_info.order_condition) : ""}';
            if(order_condition != "") {
                order_condition = JSON.parse(order_condition);

                order_distri = order_condition.order_num.distri;
                $("input[name='order_condition[order_num][self]']").val(order_condition.order_num.self);
                $("input[name='order_condition[order_num][team]']").val(order_condition.order_num.team);
                $('#num-one').val(order_distri[0]);
                $('#num-two').val(order_distri[1]);
                $('#num-thr').val(order_distri[2]);

                distri = order_condition.order_money.distri;
                $("input[name='order_condition[order_money][self]']").val(order_condition.order_money.self);
                $("input[name='order_condition[order_money][team]']").val(order_condition.order_money.team);
                $('#mon-one').val(distri[0]);
                $('#mon-two').val(distri[1]);
                $('#mon-thr').val(distri[2]);
            }

            var people_condition = '{:isset($_info.people_condition) ? htmlspecialchars_decode($_info.people_condition) : ""}';
            if(people_condition != "") {
                people_condition = JSON.parse(people_condition);
                people_distri = people_condition.distri;
                $("input[name='people_condition[team]']").val(people_condition.team);

                $('#peo-one').val(people_distri[0]);
                $('#peo-two').val(people_distri[1]);
                $('#peo-thr').val(people_distri[2]);
            }

            var commission_condition = '{:isset($_info.commission_condition) ? htmlspecialchars_decode($_info.commission_condition) : ""}';
            if(commission_condition != "") {
                commission_condition = JSON.parse(commission_condition);
                commission_distri = commission_condition.distri;
                $("input[name='commission_condition[self]']").val(commission_condition.self);
                $("input[name='commission_condition[team]']").val(commission_condition.team);

                $('#com-one').val(commission_distri[0]);
                $('#com-two').val(commission_distri[1]);
                $('#com-thr').val(commission_distri[2]);
            }

            var upgrade_condition = '{:empty($_info.upgrade_condition) ? "" : htmlspecialchars_decode($_info.upgrade_condition)}';
            if(upgrade_condition) {
                upgrade_condition = JSON.parse(upgrade_condition);
                $("input[name='upgrade_condition[]']").each(function (i) {
                    for (i = 0; i < upgrade_condition.length; i++) {
                        if ($(this).val() == upgrade_condition[i]) {
                            $(this).prop("checked", true);
                        }
                    }
                });
            }
            showCondition();
        });

        $("input[name='upgrade_condition[]']").on("change", function () {
            showCondition();
        });

        function showCondition(){
            $("input[name='upgrade_condition[]']").each(function(i){
                var type = $(this).val();
                if ($(this).is(":checked")) {
                    if(type == "order"){
                        $(".order").css("display","table-row");
                    }else if (type == "people") {
                        $(".people").css("display","table-row");
                    }/*else if (type == "good") {
                        $(".good").show();
                    }*/else if (type == "commission") {
                        $(".money").css("display","table-row");
                    }
                } else {
                    if(type == "order"){
                        $(".order").hide();
                    }else if (type == "people") {
                        $(".people").hide();
                    }/*else if (type == "good") {
                        $(".good").hide();
                    }*/else if (type == "commission") {
                        $(".money").hide();
                    }
                }
            });
        }


    </script>
</block>