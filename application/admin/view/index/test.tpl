<extend name="public:base_nav2"/>
<block name="css">
</block>
<block name="js">
    <script src="__VENDOR__/echarts/echarts.min.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/shine.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/dataTool.js?v=__RV__"></script>
</block>

<block name="body">
    <div id="container" style="width: 100%;height: 500px;margin: 10px;">
    </div>
    <script>
        var trendChart = echarts.init($('#container').get(0), 'shine');
        var json='{:htmlspecialchars_decode($json)}';
        var obj=JSON.parse(json);
        var option = {
            xAxis: {
                type: 'category',
                data: obj.x
            },
            tooltip: {
                trigger: 'axis'
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '分值',
                data: obj.y,
                type: 'line',
                smooth: true
            }]
        };
        trendChart.setOption(option);
    </script>
</block>