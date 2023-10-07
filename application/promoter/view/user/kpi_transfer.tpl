<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>移交用户业绩</title>
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
<p>将用户业绩转移给新的{:config('app.agent_setting.promoter_name')}</p>
业绩开始时间:<input placeholder="业绩开始时间" type="text" class="start_time"/><br/>
业绩结束时间:<input placeholder="业绩结束时间" type="text" class="end_time"/><br/>
用户ID:<input placeholder="用户ID" type="text" class="user_id"/><br/>
原{:config('app.agent_setting.promoter_name')}ID:<input placeholder="原{:config('app.agent_setting.promoter_name')}ID" type="text" class="old_promoter_uid"/><br/>
新{:config('app.agent_setting.promoter_name')}ID:<input placeholder="新{:config('app.agent_setting.promoter_name')}ID" type="text" class="new_promoter_uid"/><br/>
<button class="query_btn" style="margin-top: 10px;">开始转移</button>
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
        var old_promoter_uid = $('.old_promoter_uid').val();
        var new_promoter_uid = $('.new_promoter_uid').val();
        if (startTime == '') {
            return alert('请选择开始时间');
        }
        if (endTime == '') {
            return alert('请选择结束时间');
        }
        if (userId == '') {
            return alert('请选择用户ID');
        }
        if (old_promoter_uid == '') {
            return alert('请选择原{:config('app.agent_setting.promoter_name')}');
        }
        if (new_promoter_uid == '') {
            return alert('请选择新{:config('app.agent_setting.promoter_name')}');
        }
        $.post('{:url("kpi_transfer")}', {
            start_time: startTime,
            end_time: endTime,
            user_id: userId,
            old_promoter_uid: old_promoter_uid,
            new_promoter_uid: new_promoter_uid
        }, function (result) {
            alert(result['message']);
        });
    });

</script>


</body>
</html>