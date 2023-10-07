<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <style>
        td{
            border: solid 1px #dcdcdc;
            padding: 5px;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <td>ID</td>
        <td>名称</td>
        <td>当前显示</td>
        <td>实际数量(数据库)</td>
        <td>所有日相加</td>
        <td>所有推广员相加</td>
    </tr>
    <volist name="details" id="item">
        <tr>
            <td>{$item.id}</td>
            <td>{$item.name}</td>
            <td>{$item.current}</td>
            <td>
                {$item.real_total}<br/>
                current-real:{$item.current_real}<br/>
                折合：{:$item['current_real']/100}
            </td>
            <td>
                {$item.days_total}<br/>
                current-days_total:{$item.current_days_total}<br/>
                折合：{:$item['current_days_total']/100}
            </td>
            <td>
                {$item.promoters_total}<br/>
                current-promoters_total:{:$item.current_promoters_total}<br/>
                折合：{:$item['current_promoters_total']/100}
            </td>
        </tr>
    </volist>
</table>

</body>
</html>