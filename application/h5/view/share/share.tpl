    <include file="public/head" />
    <title>{$share_name|default=APP_NAME}</title>

    <link rel="stylesheet" href="__H5__/css/share/common.css?{:date('YmdHis')}">
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
    <script src="__H5__/js/css-base.js?v=__RV__"></script>
    <script src="__VENDOR__/callapp/index.umd.js?v=__RV__"></script>
    <if condition="$type != 'user'">
        <link rel="stylesheet" href="__H5__/css/share/index.css?{:date('YmdHis')}">
        <script src="__VENDOR__/TcPlayer/TcPlayer-2.3.1.js" charset="utf-8"></script>
        <style>
            .vcp-bigplay {width: .98rem; height: .98rem; z-index: 9998; left: 50%; top: 50%; margin-left: -.5rem; margin-top: -.5rem; display: block; background-image: url("__H5__/images/share/video_play_btn.png"); opacity: 1 !important; background-size:100%; }
        </style>
    </if>
    <link rel="stylesheet" href="__H5__/css/share/{$type}.css?{:date('YmdHis')}">
</head>

<body>

{__CONTENT__}

</body>
    <script>
        $(function () {
            const option = {
                scheme: {
                    protocol: 'bxVideo',
                },
                intent: {
                    package: '',
                    scheme: 'bx',
                },
                appstore: '{$down_url}',
                yingyongbao: '{$down_url}?pkgname=com.live.bingxin',
                fallback: '{$down_url}',
                timeout: 1000,
            };
            const lib = new CallApp(option);
            $('#download, .focus-btn span, .pagelet-worklist .list li, .banner-bottom .banner-btn, .video-list li, #videoUser, #videoInfo .info-right .info-item').click(function(){
                lib.open({
                    path: '',
                });
            });

            <if condition="$type == 'live'">
            setTimeout(function(){
                layer.open({
                    content: '下载APP,查看更多有趣视频',
                    area: ['4.778rem', '2.116rem'],
                    btn: ['立即下载', '忽略'],
                    yes : function (index, lay) {
                        layer.close(index);
                        lib.open({
                            path: '',
                        });
                    },
                    no : function (index, laye) {
                        layer.close(index);
                    },
                    success:function (layero, index){

                    }
                });
            },60000);
            </if>
        });
    </script>
</html>