<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>
<block name="body">
    <style>
        .content_info2 td {
            padding: 10px;
            font-size: 12px;
            line-height: 20px;
            word-break: break-all;
            word-wrap: break-word;
        }
        .my-btn {
            height: 34px;
            line-height: 32px;
            margin-left: 20px;
        }
        .btn-primary {
            border: 1px solid #d1d1d1;
            background-color: #fff;
            text-align: center;
            color: #555;
            width: 150px;
            border-radius: 10px;
        }
        .border-color{
            border-color: #2f74eb !important;
        }
        .ns-text-color {
            color: #4685FD !important;
            font-weight: bolder;
            font-size: 14px;
        }
        .hide{
            display: none;
        }

    </style>
        <div class="panel-body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <div class="panel mt_10">
                <div class="panel-heading">等级{$distribute_name}比例</div>
                <div class="panel-body">
                    <table class="content_info2 mt_10">
                        <tr>
                            <td class="field_name">等级名称</td>
                            <td>
                                <input class="base_text" name="level_name" value="{$_info.level_name}"/>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">一级{$distribute_name}比例</td>
                            <td>
                                <input class="base_text" type="number" name="one_rate" value="{$_info.one_rate}"/> %
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">二级{$distribute_name}比例</td>
                            <td>
                                <input class="base_text" type="number" name="two_rate" value="{$_info.two_rate}"/> %
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">三级{$distribute_name}比例</td>
                            <td>
                                <input class="base_text" type="number" name="three_rate" value="{$_info.three_rate}"/> %
                            </td>
                        </tr>

                    </table>
                </div>
            </div>

            <div class="panel mt_10">
                <div class="panel-heading">升级条件</div>
                <div class="panel-body">
                    <table class="content_info2 mt_10">
                        <tr>
                            <td>
                                &nbsp;&nbsp;&nbsp;<label>
                                <input  style="width: 20px;height: 20px;" type="radio" name="upgrade_type" value="1" <if condition="$_info.upgrade_type eq 1">checked</if>>&nbsp;满足以下任意条件                                </label>&nbsp;&nbsp;&nbsp;
                                <label>
                                <input  style="width: 20px;height: 20px;" type="radio" name="upgrade_type" value="2" <if condition="$_info.upgrade_type eq 2">checked</if>>&nbsp;满足以下全部条件                                </label>
                            </td>
                        </tr>
                        <tr style="padding-top: 25px;">
                            <td>

                            <label class="my-btn btn-primary level-btn <if condition="$_info.fenxiao_reward_num gt 0">border-color</if>" >打赏总次数</label>
                            <label class="my-btn btn-primary level-btn <if condition="$_info.fenxiao_reward_money gt 0">border-color</if>">打赏总金额</label>
                            <label class="my-btn btn-primary level-btn <if condition="$_info.one_fenxiao_reward_num gt 0">border-color</if>">一级打赏次数</label>
                            <label class="my-btn btn-primary level-btn <if condition="$_info.one_fenxiao_reward_money gt 0">border-color</if>">一级打赏总额</label>
                            <label class="my-btn btn-primary level-btn <if condition="$_info.child_num gt 0">border-color</if>">下线人数</label>
                           <!-- <label class="my-btn btn-primary level-btn <if condition="$_info.one_child_num gt 0">border-color</if>">一级下线人数</label>-->
                            </td>
                        </tr>
                    </table>
                </div>
            </div>


            <div class="panel mt_10">
                <div class="panel-heading">升级条件限制</div>
                <div class="panel-body">
                    <table class="content_info2 mt_10 level-term">
                        <tr class="form-item <if condition="$_info.fenxiao_reward_num elt 0">hide</if>">
                            <td class="field_name">打赏总次数</td>
                            <td>
                                <input class="base_text" type="number" name="fenxiao_reward_num" value="{$_info.fenxiao_reward_num}"/> 次  &nbsp;&nbsp;<a href="#" class="ns-text-color" onclick="delDiv(this)">删除</a>
                            </td>
                        </tr>

                        <tr class="form-item <if condition="$_info.fenxiao_reward_money elt 0">hide</if>">
                            <td class="field_name">打赏总金额</td>
                            <td>
                                <input class="base_text" type="number" name="fenxiao_reward_money" value="{$_info.fenxiao_reward_money}"/> 钻  &nbsp;&nbsp;<a href="#" class="ns-text-color" onclick="delDiv(this)">删除</a>
                            </td>
                        </tr>

                        <tr class="form-item <if condition="$_info.one_fenxiao_reward_num elt 0">hide</if>">
                            <td class="field_name">一级打赏次数</td>
                            <td>
                                <input class="base_text" type="number" name="one_fenxiao_reward_num" value="{$_info.one_fenxiao_reward_num}"/> 次   &nbsp;&nbsp;<a href="#" class="ns-text-color" onclick="delDiv(this)">删除</a>
                            </td>
                        </tr>


                        <tr class="form-item <if condition="$_info.one_fenxiao_reward_money elt 0">hide</if>">
                            <td class="field_name">一级打赏总额</td>
                            <td>
                                <input class="base_text" type="number" name="one_fenxiao_reward_money" value="{$_info.one_fenxiao_reward_money}"/> 钻   &nbsp;&nbsp;<a href="#" class="ns-text-color" onclick="delDiv(this)">删除</a>
                            </td>
                        </tr>

                        <tr class="form-item <if condition="$_info.child_num elt 0">hide</if>">
                            <td class="field_name">下线人数</td>
                            <td>
                                <input class="base_text" type="number" name="child_num" value="{$_info.child_num}"/> 人   &nbsp;&nbsp;<a href="#" class="ns-text-color" onclick="delDiv(this)">删除</a>
                            </td>
                        </tr>

                       <!-- <tr class="form-item <if condition="$_info.one_child_num elt 0">hide</if>">
                            <td class="field_name">一级下线人数</td>
                            <td>
                                <input class="base_text" name="one_child_num" value="{$_info.one_child_num}"/> 人   &nbsp;&nbsp;<a href="#" class="ns-text-color" onclick="delDiv(this)">删除</a>
                            </td>
                        </tr>-->

                    </table>
                </div>
            </div>
            <div class="mt_10">
                <present name="_info['id']">
                    <input name="id" type="hidden" value="{$_info.id}"/>
                </present>
                __BOUNCE__
                <div class="base_button_div max_w_auto">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </div>
        </form>
        <script>
            $(".level-btn").click(function() {
                var _index = $(this).index();
                if (!$(this).hasClass("border-color")) {
                    $(this).addClass("border-color");
                    $(".level-term tr").eq(_index).removeClass("hide");
                }
            })
            function delDiv(e) {
                var _len = $(e).parents(".form-item").index();
                $(e).parents(".form-item").addClass("hide");
                $(e).parents(".form-item").find("input").val("");
                $(".level-btn").eq(_len).removeClass("border-color");
            }
        </script>
</block>