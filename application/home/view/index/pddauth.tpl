<include file="public/head" />
<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
</head>
<body>

    <input type="hidden" value="" id="access_token">
    <input type="hidden" value="" id="refresh_token">
    <input type="hidden" value="" id="expires_in">

    <script>
        var access_token = "{$Think.Request.access_token}";
        $('#access_token').val(access_token);
        var refresh_token = "{$Think.Request.refresh_token}";
        $('#refresh_token').val(refresh_token);
        var expires_in = "{$Think.Request.expires_in}";
        $('#expires_in').val(expires_in);
        $.ajax({
            cache: false,
            url: "{:url('index/pddauth')}",
            type: "post",
            data: {
                'access_token': access_token,
                'refresh_token': refresh_token,
                'expires_in': expires_in
            },
            success: function (data) {
                if (data.status == 0) {
                    window.close();
                }
            }
        });
    </script>
</body>
</html>