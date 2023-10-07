<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>每月用户充值梯度报告</title>
</head>
<body>
<h1>每月用户充值梯度报告</h1>
<volist name="details" id="detail">
    <h2>{$detail.moth}月份</h2>
    <table>
        <tr>
            <td>梯度</td>
            <td>总人数</td>
            <td>用户列表</td>
        </tr>
        <volist name="detail['gradient']" id="gradient">
            <tr>
                <td>{$gradient.title}</td>
                <td>{$gradient.num}</td>
                <td>
                    <volist name="gradient['list']" id="user">
                        {$user.user_id}&nbsp;:&nbsp;{$user.total_fee}元
                    </volist>
                </td>
            </tr>
        </volist>
    </table>
</volist>
</body>
</html>