<include file="public/head" />
<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
</head>
<body>

    <input type="hidden" value="" id="access_token">
    <input type="hidden" value="" id="refresh_token">
    <input type="hidden" value="" id="expires_in">

    <script>
        var data = [];
        var access_token = "{$Think.Request.access_token}";
        data['access_token'] = access_token;
        $('#access_token').val(access_token);
        var refresh_token = "{$Think.Request.refresh_token}";
        data['refresh_token'] = refresh_token;
        $('#refresh_token').val(refresh_token);
        var expires_in = "{$Think.Request.expires_in}";
        data['expires_in'] = expires_in;
        $('#expires_in').val(expires_in);
        var url = "{$host}"+"{:url('home/index/auth')}";
        window.parent.postMessage(data, url);
    </script>
</body>
</html>