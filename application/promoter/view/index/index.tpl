<extend name="public:base_nav"/>
<block name="css">
    <style>
        .data_tab td {
            height: 25px;
        }

        .top_banner img {
            max-width: 100%;
            margin: 0;
        }
    </style>
</block>
<block name="js">
    <script src="__VENDOR__/echarts/echarts.min.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/shine.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/dataTool.js?v=__RV__"></script>
    <script src="__JS__/index/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">

        <div class="data_block mt_10 cons_trend">
            <div class="data_title">客消收入趋势图</div>
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
            <div style="width: 100%;height:500px;" class="mt_10 my_container">
            </div>
        </div>

    </div>

</block>