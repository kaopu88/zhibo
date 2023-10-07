<include file="public/head" />
<title>我的战绩</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">
<link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
<style>
    .circleChart_canvas {
        width: 1.501rem;
        height: 1.501rem;
    }
</style>

</head>
<body>
    <div class="pk_record">
        <div class="title">
            <div class="my_record">我的战绩</div>
        </div>
        <div class="pk_record_container">
            <div class="circleChart" id="circle1">
                <div class="content">
                    <p>66%</p>
                    <p>胜率</p>
                </div>
            </div>
            <div
            class="circleChart"
            id="circle2"
            data-color="#00FFF5"
            data-value='50'
            data-background-color="rgba(0, 255, 245,0.2)"
            >
                <div class="content">
                    <p>50</p>
                    <p>场次</p>
                </div>
            </div>
            <div
            class="circleChart"
            id="circle3"
            data-color="#F6DEFB"
            data-value='20'
            data-background-color="rgba(246, 222, 251, 0.2)"
            >
                <div class="content">
                    <p>20</p>
                    <p>收益</p>
                </div>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="/bx_static/circleChart.js"></script>

<script>
    $('#circle1').circleChart()
    $('#circle2').circleChart()
    $('#circle3').circleChart()
</script>

</html>