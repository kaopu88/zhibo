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
        <form action="{:url('add')}">
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
                                    <input class="base_text" name="name" value=""/>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">等级</td>
                                <td>
                                    <input class="base_text" name="level" value="" type="number"/>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">等级图片</td>
                                <td>
                                    <div class="base_group">
                                        <input style="width: 309px;" name="img" value="" type="text" class="base_text border_left_radius"/>
                                        <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="img" style=" float: initial;">上传</a>
                                    </div>
                                    <div imgview="[name=img]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">等级描述</td>
                                <td>
                                    <textarea style="height: 200px;" class="base_textarea" name="desc"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">是否启用</td>
                                <td>
                                    <select class="base_select" name="status" selectedval="1">
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
                                    <input class="base_text" name="purchase" value="0"/> %
                                </td>
                            </tr>

                            <input type="hidden" name="promotion_level" value="3"/>

                            <tr class="promotion">
                                <td class="field_name">第1级佣金比率</td>
                                <td>
                                    <input class="base_text" name="promotion[0]" value="0"/> %
                                </td>
                            </tr>

                            <tr class="promotion">
                                <td class="field_name">第2级佣金比率</td>
                                <td>
                                    <input class="base_text" name="promotion[1]" value="0"/> %
                                </td>
                            </tr>

                            <tr class="promotion">
                                <td class="field_name">第3级佣金比率</td>
                                <td>
                                    <input class="base_text" name="promotion[2]" value="0"/> %
                                </td>
                            </tr>

                            <!--<tr>
                                <td class="field_name">团队奖励</td>
                                <td>
                                    <input class="base_text" name="team_reward" value="0"/> %
                                </td>
                            </tr>

                            <tr>
                                <td class="field_name">平级奖励</td>
                                <td>
                                    <input class="base_text" name="level_reward" value="0"/> %
                                </td>
                            </tr>

                            <tr id="distribute">
                                <td class="field_name">分销层级</td>
                                <td>
                                    <select class="base_select" name="promotion_level" id="promotion_level"></select>
                                </td>
                            </tr>

                            <tr class="promotion">
                                <td class="field_name">第1级佣金比率</td>
                                <td>
                                     <input class="base_text" name="promotion[]" value="0"/> %
                                </td>
                            </tr>-->

                        </table>
                    </div>

                    <div class="layui-tab-item">
                        <table class="content_info2 mt_10">

                            <tr>
                                <td class="field_name">是否需要审核</td>
                                <td>
                                    <select class="base_select" name="upgrade_type" selectedval="1">
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
                                    <input class="base_text" name="order_condition[order_num][self]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="order" id="order_num">
                                <td class="field_name">团队订单数量</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][team]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="order_num order">
                                <td class="field_name">1级分销订单数</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][distri][0]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="order_num order">
                                <td class="field_name">2级分销订单数</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][distri][1]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="order_num order">
                                <td class="field_name">3级分销订单数</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_num][distri][2]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="order">
                                <td class="field_name">自购订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][self]" value="0"/> 个
                                </td>
                            </tr>

                            <tr id="order_money" class="order">
                                <td class="field_name">团队订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][team]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="order_money order">
                                <td class="field_name">1级分销订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][distri][0]" value="0"/> 个
                                </td>
                            </tr>
                            <tr class="order_money order">
                                <td class="field_name">2级分销订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][distri][1]" value="0"/> 个
                                </td>
                            </tr>
                            <tr class="order_money order">
                                <td class="field_name">3级分销订单金额</td>
                                <td>
                                    <input class="base_text" name="order_condition[order_money][distri][2]" value="0"/> 个
                                </td>
                            </tr>


                            <tr class="people">
                                <td class="field_name">人数</td>
                                <td></td>
                            </tr>

                            <tr id="people_num" class="people">
                                <td class="field_name">团队总人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[team]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="people_num people">
                                <td class="field_name">1级分销人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[distri][0]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="people_num people">
                                <td class="field_name">2级分销人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[distri][1]" value="0"/> 个
                                </td>
                            </tr>

                            <tr class="people_num people">
                                <td class="field_name">3级分销人数</td>
                                <td>
                                    <input class="base_text" name="people_condition[distri][2]" value="0"/> 个
                                </td>
                            </tr>

                            <!--<tr class="good">
                                <td class="field_name">购买指定商品</td>
                                <td></td>
                            </tr>

                            <tr class="good">
                                <td class="field_name">商品平台</td>
                                <td>
                                    <select class="base_select" name="good_condition[platform]" selectedval="1" disabled>
                                        <option value="1">第三方商品</option>
                                        <option value="2">自营商品</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="good">
                                <td class="field_name">商品平台</td>
                                <td>
                                    <select class="base_select" name="good_condition[type]" selectedval="0">
                                        <option value="0">淘宝</option>
                                        <option value="1">拼多多</option>
                                        <option value="2">京东</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="good">
                                <td class="field_name">商品id</td>
                                <td>
                                    <input class="base_text" name="good_condition" value=""/>
                                </td>
                            </tr>-->

                            <tr class="money">
                                <td class="field_name">佣金</td>
                                <td></td>
                            </tr>

                            <tr class="money">
                                <td class="field_name">自购佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[self]" value="0"/> 元
                                </td>
                            </tr>

                            <tr id="commission" class="money">
                                <td class="field_name">团队佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[team]" value="0"/> 元
                                </td>
                            </tr>

                            <tr class="commission money">
                                <td class="field_name">1级分销佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[distri][0]" value="0"/> 元
                                </td>
                            </tr>

                            <tr class="commission money">
                                <td class="field_name">2级分销佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[distri][1]" value="0"/> 元
                                </td>
                            </tr>

                            <tr class="commission money">
                                <td class="field_name">3级分销佣金</td>
                                <td>
                                    <input class="base_text" name="commission_condition[distri][2]" value="0"/> 元
                                </td>
                            </tr>

                        </table>
                    </div>

                </div>
            </div>
            <div class="base_button_div" style="max-width:550px;">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
        $(function() {
            // var html = "";
            // for (var i=1; i<=100; i++){
            //     html += "<option value='"+i+"'> "+i+" </option>";
            // }
            // $("#promotion_level").html(html);
        });

        // $("#promotion_level").change(function(){
        //     var checkValue = $("#promotion_level").val();
        //     if(checkValue > 0){
        //         $(".promotion").remove();
        //         var phtml = "";
        //         for (var i=1; i<=checkValue; i++){
        //             phtml += "<tr class='promotion'>";
        //             phtml += "<td class='field_name'>第"+i+"级佣金比率</td>";
        //             phtml += "<td>";
        //             phtml += "<input class='base_text' name='promotion[]' value='0'/> %";
        //             phtml += "</td>";
        //             phtml += "</tr>";
        //         }
        //         $(".content_info2").eq(1).append(phtml);
        //
        //         $(".order_num").remove();
        //         var onhtml = "";
        //         for (var i=1; i<=checkValue; i++){
        //             onhtml += "<tr class='order_num order'>";
        //             onhtml += "<td class='field_name'>"+i+"级分销订单数</td>";
        //             onhtml += "<td>";
        //             onhtml += "<input class='base_text' name='order_condition[order_num][distri][]' value='0'/> 个";
        //             onhtml += "</td>";
        //             onhtml += "</tr>";
        //         }
        //         $("#order_num").after(onhtml);
        //
        //         $(".order_money").remove();
        //         var omhtml = "";
        //         for (var i=1; i<=checkValue; i++){
        //             omhtml += "<tr class='order_money order'>";
        //             omhtml += "<td class='field_name'>"+i+"级分销订单金额</td>";
        //             omhtml += "<td>";
        //             omhtml += "<input class='base_text' name='order_condition[order_money][distri][]' value='0'/> 个";
        //             omhtml += "</td>";
        //             omhtml += "</tr>";
        //         }
        //         $("#order_money").after(omhtml);
        //
        //         $(".people_num").remove();
        //         var pnhtml = "";
        //         for (var i=1; i<=checkValue; i++){
        //             pnhtml += "<tr class='people_num people'>";
        //             pnhtml += "<td class='field_name'>"+i+"级分销人数</td>";
        //             pnhtml += "<td>";
        //             pnhtml += "<input class='base_text' name='people_condition[distri][]' value='0'/> 个";
        //             pnhtml += "</td>";
        //             pnhtml += "</tr>";
        //         }
        //         $("#people_num").after(pnhtml);
        //
        //         $(".commission").remove();
        //         var cmhtml = "";
        //         for (var i=1; i<=checkValue; i++){
        //             cmhtml += "<tr class='commission money'>";
        //             cmhtml += "<td class='field_name'>"+i+"级分销佣金</td>";
        //             cmhtml += "<td>";
        //             cmhtml += "<input class='base_text' name='commission_condition[distri][]' value='0'/> 元";
        //             cmhtml += "</td>";
        //             cmhtml += "</tr>";
        //         }
        //         $("#commission").after(cmhtml);
        //
        //         showCondition();
        //     }
        // });

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
                        $(".good").css("display","table-row");
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