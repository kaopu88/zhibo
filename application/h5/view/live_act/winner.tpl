<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>《一路春光 感恩有你》获取名单</title>
    <link rel="stylesheet" href="__VENDOR__/layer/layui/css/layui.css" media="all">
    <style>
        #paged li{
            float: left;
            margin: 0 6px;
        }
        #paged{
            font-size: 20px;
        }
    </style>
</head>
<body>
<div style="font-size: 33px;text-align: center;margin: 20px auto;">《一路春光 感恩有你》获取名单</div>
<table class="layui-table">
    <colgroup>
        <col width="150">
        <col width="200">
        <col>
    </colgroup>
    <thead>
    <tr>
        <th>用户ID</th>
        <th>用户昵称</th>
        <th>联系号码</th>
        <th>奖项</th>
        <th>数量</th>
        <th>收件人</th>
        <th>收件号码</th>
        <th>收件地址</th>
        <th>中奖时间</th>
    </tr>
    </thead>
    <tbody>
    <volist name="data" id="item">
        <tr>
            <td>{$item.user_id}</td>
            <td>{$item.nickname}</td>
            <td>{$item.phone}</td>
            <td>{$item.prize_name}</td>
            <td>{$item.num}</td>
            <td>{$item.name}</td>
            <td>{$item.mobile}</td>
            <td>{$item.address}</td>
            <td>{$item.create_time}</td>
        </tr>
    </volist>
    </tbody>
</table>
<div style="width: 100%;margin: 0 auto;text-align: center;">
    <div id="paged">{:$data->render()}</div>
</div>
</body>
</html>