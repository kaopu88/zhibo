<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>充值查询</title>
    <link rel="stylesheet" type="text/css"
          href="/static/vendor/flatpickr/flatpickr.min.css?v=__RV__"/>
    <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
    <script src="/static/vendor/flatpickr/flatpickr.min.js?v=__RV__"></script>
    <script src="h/static/vendor/layer/layer.js?v=201811061619"></script>
    <script src="/static/vendor/smart/smart.bundle.js?v=201811061619"></script>
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

<table>
    <tr>
        <td>开始时间</td>
        <td>
            <input class="start_time"/>
        </td>
    </tr>
    <tr>
        <td>结束时间</td>
        <td>
            <input class="end_time"/>
        </td>
    </tr>
    <!--  <tr>
          <td>最小值</td>
          <td>
              <input class="min"/>
          </td>
      </tr>
      <tr>
          <td>最大值</td>
          <td>
              <input class="max"/>
          </td>
      </tr>-->
</table>
<button class="query_btn">查询人数</button>

<script>
    $(".start_time,.end_time").flatpickr({
        dateFormat: 'Y-m-d',
        enableTime: false,
        enableSeconds: false
    });
    $('.query_btn').click(function () {
        var startTime = $('.start_time').val();
        var endTime = $('.end_time').val();
        var min = $('.min').val();
        var max = $('.max').val();
        if (startTime == '') {
            return alert('请选择开始时间');
        }
        if (endTime == '') {
            return alert('请选择结束时间');
        }
        /* if (min == '') {
             return alert('请选择最小值');
         }
         if (max == '') {
             return alert('请选择最大值');
         }*/

        $s.post('{:url("query_sum")}', {
            start_time: startTime + ' 00:00:00',
            end_time: endTime + ' 23:59:59',
            min: min,
            max: max
        }, function (result, next) {
            if (result['status'] == 0) {
                alert(result['message']);
            } else {
                next();
            }
        });
    });

</script>
</body>
</html>