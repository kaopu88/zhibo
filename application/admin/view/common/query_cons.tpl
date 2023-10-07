<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>查询客户消费{:config('app.product_info.bean_name')}（临时工具）</title>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/flatpickr/flatpickr.min.css?v=__RV__"/>
    <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
    <script src="__VENDOR__/flatpickr/flatpickr.min.js?v=__RV__"></script>
    <style>
        input {
            border: solid 1px #DCDCDC;
            background-color: #fff;
            box-sizing: border-box;
            height: 36px;
            padding: 2px 5px;
            line-height: 30px;
        }
    </style>
</head>
<body>
<p>查询速度可能比较慢请耐心等待，查询结果单位为{:config('app.product_info.bean_name')}</p>
<input placeholder="开始时间" type="text" class="start_time"/>
<input placeholder="结束时间" type="text" class="end_time"/>
<input placeholder="用户ID" type="text" class="user_id"/>
<br/>
<button class="query_btn" style="margin-top: 10px;">查询</button>
<script>
    $(".start_time,.end_time").flatpickr({
        dateFormat: 'Y-m-d H:i:S',
        enableTime: true,
        enableSeconds: true,
        time_24hr: true
    });

    $('.query_btn').click(function () {
        var startTime = $('.start_time').val();
        var endTime = $('.end_time').val();
        var userId = $('.user_id').val();
        if (startTime == '') {
            return alert('请选择开始时间');
        }
        if (endTime == '') {
            return alert('请选择结束时间');
        }
        if (userId == '') {
            return alert('请选择用户ID');
        }
        $.post('{:url("query_cons")}', {
            start_time: startTime,
            end_time: endTime,
            user_id: userId
        }, function (result) {
            alert(result['message']);
        });

    });

</script>


</body>
</html>