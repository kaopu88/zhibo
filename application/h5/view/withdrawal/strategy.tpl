<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <meta name="renderer" content="webkit" />
    <meta name="applicable-device" content="pc" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="application-name" content="" />
    <meta name="renderer" content="webkit" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <link rel="stylesheet" href="/bx_static/layui.css" />
    <link rel="stylesheet" href="/bx_static/withdrawal_activities.css" />
    <script src="/bx_static/layui.js"></script>
    <script src="/bx_static/media_auto.js"></script>
    <title>赚钱攻略</title>
</head>

<body>
    <div class="container strategy">
        <header>
            <div class="back"></div>
            <div class="title">赚钱攻略</div>
        </header>
        <div class="content">
            <volist name="_list" id="vo">
                <div class="list">
                    <div class="title">{$vo.name}</div>
                    <ul>
                        <volist name="vo['children']" id="detail">
                            <li  onclick="gotodetail({$detail.id})" class="{$detail.id}">{$detail.title}</li>
                        </volist>

                    </ul>
                </div>
            </volist>
        </div>
    </div>
    <script>
        layui.use(['element', 'layer'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var $ = layui.jquery;
            $('.back').click(function () {
                window.history.back(-1);
            })
        });

        function gotodetail(atricleid) {
            var domain = window.location.host;
            window.location.href= "{$h5_service_url}/h5/app_article/show/id/"+atricleid;
        }
    </script>
</body>

</html>