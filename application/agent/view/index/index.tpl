<extend name="public:base_nav"/>
<block name="css">
    <style>
        .data_rally_td {
            position: relative;
        }

        .rally_num {
            font-size: 12px;
            display: block;
            position: absolute;
            right: 5px;
            bottom: 0px;
        }

        .rally_num.rally_up {
            color: #f00;
        }

        .rally_num.rally_down {
            color: #008000;
        }

        .data_num {
            font-size: 18px;
            font-weight: bold;
        }

        .data_tab td {
            height: 25px;
        }

        .top_banner {
            cursor: pointer;
            display: block;
            text-decoration: none;
            margin: 0;
            padding: 0;
            width: 100%;
            overflow: hidden;
        }

        .top_banner img {
            max-width: 100%;
            margin: 0;
        }

        .his_data {
            display: none;
        }

    </style>
</block>
<block name="js">
    <script src="__VENDOR__/echarts/echarts.min.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/shine.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/dataTool.js?v=__RV__"></script>
    <script>
        var myConfig = {
            action: '{:url("get_history_data")}',
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
            list: [],
            async: {
                load: function (url) {
                    $s.post(url, {}, function (result, next) {
                        if (result['status'] == 0) {
                            $('.his_data').show();
                            for (var key in result['data']) {
                                $('.his_data').find('.' + key + '_num').html(result['data'][key]);
                            }
                        } else {
                            $('.his_data').hide();
                            next();
                        }
                    });
                }
            }
        };
    </script>
    <script src="__JS__/index/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">

        <p style="padding: 20px;border: solid 1px #DCDCDC;color: #555;font-size: 14px;color: #d00;">
             关于数据时效性说明：<br/>
             为了保证数据及时性且提高查询速度，故对部分数据进行了缓存，缓存时间进行了调整，调整为当日数据(如：客消、收获{:config('app.product_info.millet_name')}等)延迟6秒更新，当月数据延迟6分钟更新，所有趋势图延迟10分钟更新，
             充值记录、客消明细、{:APP_MILLET_NAME}明细、拉新明细实时更新。<br/>
             {:config('app.agent_setting.agent_name')}的以往业绩汇总数据在哪查询？<br/>
             运营概况》历史查询》选择日期查询
         </p>

        <div class="data_block mt_10">
            <div class="data_title">主要数据</div>
            <table class="data_tab mt_10">
                <thead>
                <tr>
                    <td style="width: 3%;">日期</td>
                    <td style="width: 14%;">客户消费({:APP_BEAN_NAME})</td>
                    <td style="width: 12%;">收获{:APP_MILLET_NAME}</td>
                    <td style="width: 12%;">充值金额(元)</td>
                    <td style="width: 12%;">拉新用户</td>
                    <td style="width: 12%;">活跃用户</td>
                    <td style="width: 12%;">直播时长</td>
                    <if condition="(($cash_type eq 0) && ($agent_info.cash_type eq 1)) || (($cash_type eq 1) && ($agent_info.cash_type neq 2))">
                        <td style="width: 13%;">主播提现(已打款)</td>
                        <td style="width: 12%;">主播提现(未打款)</td>
                    </if>
                </tr>
                </thead>
                <tbody>
                <tr class="today_data_tr">
                    <td>今日</td>
                    <td class="data_rally_td cons_num">
                        <span class="data_num"></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                    </td>
                    <td class="data_rally_td millet_num">
                        <span class="data_num"></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                    </td>
                    <td class="data_rally_td recharge_num">
                        <span class="data_num"></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                    </td>
                    <td class="data_rally_td pull_user_num">
                        <span class="data_num "></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                    </td>
                    <td class="data_rally_td active_num">
                        <span class="data_num"></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                    </td>
                    <td class="data_rally_td duration_num">
                        <span class="data_num"></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                    </td>
                    <if condition="(($cash_type eq 0) && ($agent_info.cash_type eq 1)) || (($cash_type eq 1) && ($agent_info.cash_type neq 2))">
                        <td class="data_rally_td cash_millet_num">
                        <span class="data_num"></span>
                        <div class="rally_num"><span class="rally_per"></span></div>
                        </td>
                        <td class="data_rally_td notcash_millet_num">
                            <span class="data_num"></span>
                            <div class="rally_num"><span class="rally_per"></span></div>
                        </td>
                    </if>

                </tr>
                <tr class="yesterday_data_tr">
                    <td>昨日</td>
                    <td class="cons_num"><span class="data_num"></span></td>
                    <td class="millet_num"><span class="data_num"></span></td>
                    <td class="recharge_num"><span class="data_num"></span></td>
                    <td class="pull_user_num"><span class="data_num"></span></td>
                    <td class="active_num"><span class="data_num"></span></td>
                    <td class="duration_num"><span class="data_num"></span></td>
                    <if condition="(($cash_type eq 0) && ($agent_info.cash_type eq 1)) || (($cash_type eq 1) && ($agent_info.cash_type neq 2))">
                        <td class="cash_millet_num"><span class="data_num"></span></td>
                        <td class="notcash_millet_num"><span class="data_num"></span></td>
                    </if>
                </tr>
                <tr class="month_data_tr">
                    <td>本月</td>
                    <td class="cons_num"><span class="data_num"></span></td>
                    <td class="millet_num"><span class="data_num"></span></td>
                    <td class="recharge_num"><span class="data_num"></span></td>
                    <td class="pull_user_num"><span class="data_num"></span></td>
                    <td class="active_num"><span class="data_num"></span></td>
                    <td class="duration_num"><span class="data_num"></span></td>
                    <if condition="(($cash_type eq 0) && ($agent_info.cash_type eq 1)) || (($cash_type eq 1) && ($agent_info.cash_type neq 2))">
                        <td class="cash_millet_num"><span class="data_num"></span></td>
                        <td class="notcash_millet_num"><span class="data_num"></span></td>
                    </if>
                </tr>
                <tr style="display: none" class="history_data_tr">
                    <td>历史</td>
                    <td class="cons_num"><span class="data_num"></span></td>
                    <td class="millet_num"><span class="data_num"></span></td>
                    <td class="recharge_num"><span class="data_num"></span></td>
                    <td class="pull_user_num"><span class="data_num"></span></td>
                    <td class="active_num"><span class="data_num"></span></td>
                    <td class="duration_num"><span class="data_num"></span></td>
                    <if condition="(($cash_type eq 0) && ($agent_info.cash_type eq 1)) || (($cash_type eq 1) && ($agent_info.cash_type neq 2))">
                        <td class="cash_millet_num"><span class="data_num"></span></td>
                        <td class="notcash_millet_num"><span class="data_num"></span></td>
                    </if>
                </tr>
                </tbody>
            </table>
        </div>

        <!--<div class="data_block mt_10">
            <div class="data_title">数据查询</div>
            <div class="filter_box mt_10">
                <div class="filter_options">
                    <ul class="filter_list"></ul>
                    <div class="filter_order">
                        <div class="time_ranger" style="margin-left: 10px;">
                            <select class="base_select range_unit"></select>
                            <select class="base_select range_num"></select>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
                <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
            </div>
            <table class="data_tab mt_10 his_data">
                <thead>
                <tr>
                    <td style="width: 16.6%;">客户消费({:APP_BEAN_NAME})</td>
                    <td style="width: 16.6%;">收获{:APP_MILLET_NAME}</td>
                    <td style="width: 16.6%;">充值金额(元)</td>
                    <td style="width: 16.6%;">拉新用户</td>
                    <td style="width: 16.6%;">活跃用户</td>
                    <td style="width: 16.6%;">直播时长(分钟)</td>
                </tr>
                </thead>
                <tbody>
                <tr class="today_data_tr">
                    <td class="cons_num"></td>
                    <td class="millet_num"></td>
                    <td class="recharge_num"></td>
                    <td class="pull_user_num"></td>
                    <td class="active_num"></td>
                    <td class="duration_num"></td>
                </tr>
                </tbody>
            </table>
        </div>

        <notempty name="admin_notice">
            <div class="data_block mt_10">
                <div class="data_title">最新公告</div>
                <table class="content_list mt_10">
                    <thead>
                    <tr>
                        <td style="width: 33%;">标题</td>
                        <td style="width: 33%;">发布人</td>
                        <td style="width: 34%;">时间</td>
                    </tr>
                    </thead>
                    <tbody>
                    <notempty name="admin_notice">
                        <volist name="admin_notice" id="an">
                            <tr>
                                <td><a href="{:url('user/notice_detail',['id'=>$an['id']])}"
                                       target="_blank">{$an.title}</a>
                                </td>
                                <td>官方</td>
                                <td>
                                    {$an.create_time|time_format}
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
        </notempty>-->

        <div class="data_block mt_10 cons_trend">
            <div class="data_title">客消和{:APP_MILLET_NAME}收入趋势图</div>
            <div class="data_toolbar">
                <div class="data_date">
                    <div class="data_date_line">
                        <!--  <a href="javascript:;" class="date_range" range-unit="d" range-num="0" range-default>今日</a>
                          <a href="javascript:;" class="date_range" range-unit="d" range-num="-1">昨日</a>-->
                        <a href="javascript:;" class="date_range" range-unit="w" range-num="0">本周</a>
                        <a href="javascript:;" class="date_range" range-unit="w" range-num="-1">上周</a>
                        <a href="javascript:;" class="date_range" range-unit="m" range-num="0" range-default>本月</a>
                        <a href="javascript:;" class="date_range" range-unit="m" range-num="-1">上月</a>
                    </div>
                    <input class="data_date_input" readonly/>
                    <input type="hidden" class="data_date_unit"/>
                    <input type="hidden" class="data_date_start"/>
                    <input type="hidden" class="data_date_end"/>
                </div>
            </div>
            <div style="width: 100%;height:450px;" class="mt_10 my_container">
            </div>
        </div>

    </div>

    <script>
//        new SearchList('.filter_box', myConfig);
    </script>

</block>