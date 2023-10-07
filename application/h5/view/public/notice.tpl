<include file="public/head" />
    <title>访问受限</title>
    <style>
        body{
            padding: 0 20px;
            padding-top: 20px;
            box-sizing: border-box;
            background-color: #f5f5f5;
        }
        body *{
            box-sizing: border-box;
        }
        .main{
            margin: 0 auto;
            max-width: 480px;
        }

        .logo{
            max-width: 100%;
            display: block;
            margin: 0 auto;
            border-radius: 5%;
        }

        .tip{
            text-align: center;
            font-size: 16px;
            display: block;
        }

        .download_btn{
            display: block;
            background-color: #58BFBD;
            color: #fff;
            text-align: center;
            line-height: 40px;
            text-decoration: none;
            font-size: 14px;
            border-radius: 5px;
            margin-top: 20px;
        }

    </style>
</head>
<body>
<div class="main">
    <img class="logo" src="{$logo}" />
    <p class="tip">请使用{$product_name}浏览本页面</p>
    <a class="download_btn" href="{$download_url}">立即下载</a>
    <p style="margin-top: 30px;font-size: 12px;text-align: center;color: #777777">
        {:date('Y')} @ www.ihuanyu.vip
    </p>
</div>
</body>
</html>