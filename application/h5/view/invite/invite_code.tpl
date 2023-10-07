<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{$product_slogan} 邀请您体验{$product_name}服务~</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/bx_static/css/layui.css" />
    <link rel="stylesheet" href="/bx_static/swiper.min.css">
    <script src="/bx_static/layui.js"></script>
    <script src="/bx_static/media_auto.js"></script>
    <script src="/bx_static/clipboard.min.js"></script>

    <style>
        body {
            background: #fff;
            font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
            font-size: 0.373rem;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .swiper-container {
            width: 100%;
            padding-top: 0.533rem;
            padding-bottom: 0.533rem;
        }

        .swiper-slide {
            background-position: center;
            background-size: cover;
            width: 8rem;
            height: 8.8rem;

        }

        .swiper-container-horizontal>.swiper-pagination-bullets,
        .swiper-pagination-custom,
        .swiper-pagination-fraction {
            bottom: 0.8rem;
        }

        .dcc {
            width: 100%;
            height: 3.46rem;
        }

        .copy-box {
            display: -ms-flexbox;
            display: flex;
            width: 80%;
            margin: 1.06rem auto 0;
            -webkit-box-pack: justify;
        }

        .code-out {
            -ms-flex: 1;
            flex: 1;
            text-align: center;
            font-size: 0.48rem;
            line-height: 1.2rem;
            -webkit-box-flex: 1;
            background: #fff0f1;
            border-top-left-radius: 0.533rem;
            border-bottom-left-radius: 0.533rem;
            color: #ff5959;
        }

        .copy,
        .download {
            font-size: 0.373rem;
            color: #fff;
            line-height: 1.2rem;
            text-align: center;
        }

        .copy {
            width: 2.133rem;
            height: 1.2rem;
            background: linear-gradient(90deg, #ff8a6c, #fe6181);
            border-radius: 0 0.8rem 0.8rem 0;

        }

        .download {
            width: 80%;
            background: #bfbfbf;
            border-radius: 1.06rem;
            margin: 1.06rem auto 0;
            background: #999;
        }

        .active {
            background: #ff5959;
        }
    </style>
</head>

<body>
    <!-- Swiper -->
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <foreach name="invite_imgs" id="vo" key="i">
                <div class="swiper-slide" style="background-image:url({$vo})"></div>
            </foreach>
        </div>

        <div class="swiper-pagination"></div>
    </div>
    <div class="dcc">

        <div class="copy-box">
            <if condition="$user.invite_code neq ''" /><div class="code-out">邀請碼:<span class="code">{$user.invite_code}</span></div>
            <div class="copy icode-copy">复制邀請碼</div>
            </if>
        </div>

        <a id="downApp" href="<if condition="$user.invite_code neq ''" /> # <else /> /h5/download/index</if>"><div class="download <if condition="$user.invite_code eq ''" />active </if>" disabled="disabled">
        下载APP<if condition="$user.invite_code neq ''" />(请先复制邀請碼) </if>
    </div></a>
    </div>
    <script src="/bx_static/swiper.min.js"></script>
    <script>
        var swiper = new Swiper('.swiper-container', {
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            pagination: {
                el: '.swiper-pagination',
            },
        });
        layui.use('layer', function () {
            var layer = layui.layer;
            var $ = layui.jquery;
            var copy = false;

            $('.download').click(function () {
                if (copy === false) {
                    layer.msg('请先复制二维码');
                    return;
                }
            });
            $('.copy').click(function () {
                $('.download').addClass('active');
                copy = true;

                new ClipboardJS('.copy', {
                    text: function(trigger) {
                        return $('.code').html();
                    }
                });
                layer.msg('复制成功');
                $("#downApp").attr('href', '/h5/download/index');
            })
        });
    </script>
</body>
</html>
