<style type="text/css">

    .tip_prize {
        width:6.6rem
    }

    .tip_prize .close_stop_tip {
        margin-top:-.8rem;
        margin-right:-.2rem
    }

    .tip,.tip_zj {
        position:fixed;
        width:6rem;
        z-index:12;
        display:none;
        top:50%;
        left:50%;
        -webkit-transform:translate(-50%,-50%);
        transform:translate(-50%,-50%);
        border-radius:8px
    }

    .showIn {
        -webkit-animation:showModal .5s ease-in-out;
        animation:showModal .5s ease-in-out
    }
    @-webkit-keyframes showModal {
        0% {
            -webkit-transform:scale(.1);
            transform:scale(.1)
        }
        to {
            -webkit-transform:scale(1);
            transform:scale(1)
        }
    }
    @keyframes showModal {
        0% {
            -webkit-transform:scale(.1);
            transform:scale(.1)
        }
        to {
            -webkit-transform:scale(1);
            transform:scale(1)
        }
    }

    .rotate {
        position:relative;
        top:-1rem
    }
    .rotate>img {
        width:100%;
        position:absolute;
        animation:circle 10s linear infinite;
        -webkit-animation:circle 10s linear infinite
    }

    @keyframes circle {
        0% {
            -webkit-transform:rotate(0);
            transform:rotate(0)
        }
        to {
            -webkit-transform:rotate(1turn);
            transform:rotate(1turn)
        }
    }@-webkit-keyframes circle {
         0% {
             -webkit-transform:rotate(0)
         }
         to {
             -webkit-transform:rotate(1turn)
         }
     }

    .close_stop_tip {
        position:absolute;
        right:.15rem;
        top:.1rem;
        font-size:.5rem;
        color:#fff;
        text-align:center;
        z-index:34
    }
    .close_stop_tip>img {
        width:.5rem
    }


    .prizeTop {
        font-size:0;
        text-align:center;
        height:4.6rem;
        position:relative;
        z-index:2;
        transform:translateY(2.4rem);
        -webkit-transform:translateY(2.4rem);
        animation:move .5s linear .4s forwards;
        -webkit-animation:move .5s linear .4s forwards
    }

    @keyframes move {
        0% {
            -webkit-transform:translateY(2.4rem);
            transform:translateY(2.4rem)
        }
        to {
            -webkit-transform:translate(0);
            transform:translate(0)
        }
    }

    @-webkit-keyframes move {
        0% {
            -webkit-transform:translateY(2.4rem);
            transform:translateY(2.4rem)
        }
        to {
            -webkit-transform:translate(0);
            transform:translate(0)
        }
    }


    .prizeTop.imgShow {
        background-image:url(__IMAGES__/activity_component/18bdbff778193e895.png);
        background-size:cover;
        background-repeat:no-repeat
    }

    .prizeTop>img {
        width:5rem;
        margin-top:1.91rem;
        height:2.53rem
    }

    .prizeMain {
        width:6rem;
        margin:0 auto;
        height:3rem;
        position:relative;
        z-index:2;
        overflow:hidden;
        font-size:0
    }
    .prizeMain.imgShow {
        background-image:url(data:image/png;base64,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);
        background-size:cover;
        background-repeat:no-repeat
    }


    .btngo>a>strong,.prizeMain>p {
        font-size:.3rem;
        text-align:center
    }
    .prizeMain>p {
        width:4.15rem;
        height:.7rem;
        background:#ec3333;
        line-height:.7rem;
        margin:0 auto;
        border-radius:5px;
        color:#fff;
        margin-top:.15rem;
        margin-bottom:.15rem
    }
    .prizeMain>h3 {
        overflow:hidden;
        font-size:.5rem;
        color:#fff;
        margin-top:.2rem;
        text-align:center
    }


    .btngo>a>img {
        width:4.6rem;
        display:block;
        margin:0 auto
    }
    .btngo>a>strong {
        color:#637d92;
        position:absolute;
        top:.19rem;
        width:100%
    }


    .btngo>a {
        display:block;
        position:relative;
        -webkit-animation:skip .8s linear infinite alternate;
        animation:skip .8s linear infinite alternate
    }

    @-webkit-keyframes skip {
        0% {
            -webkit-transform:scale(.95);
            transform:scale(.9)
        }
        to {
            -webkit-transform:scale(1);
            transform:scale(1)
        }
    }

    @keyframes skip {
        0% {
            -webkit-transform:scale(.95);
            transform:scale(.98)
        }
        to {
            -webkit-transform:scale(1);
            transform:scale(1)
        }
    }

</style>

<div class="tip_prize tip" style="display: block;">
    <div class="showIn">
        <span></span>
        <div class="rotate">
            <img src="__IMAGES__/activity_component/e0c49e5616ba9c025.png" alt="">
        </div>
        <div class="close_stop_tip">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAABFCAMAAAArU9sbAAAAgVBMVEUAAAD///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////9d3yJTAAAAKnRSTlMACvnX0zMR8fbtx1IuKdHidhd+SmbckbTNvmFc57pDI5uGbjxXHqqiizjHs2mxAAADS0lEQVRYw62Y6XaqMBCAExCiIIuAgChVqXXh/R/wdoZoxJAR6p0fPTUzfIdZScLMskrjQz1b78RuPasPcbpik+V23MzbvohN4kxBuE3ZDouX8ZGM1XHRmmWXjPLsaD2esOs83vq+v43zev1YnSdvGV/2PQrnLOhpgqwQUjf7piFXaVdm7lC8snu8EioiXmdT+0aT00aauCaL7y6qa2SYORFaRUtDSFqUnL+rgwPaWY4ZInz2XrYWYm4D7nTR/2FjJIgQE2iBxXr3+NjqtrECX81xtZzQJFiFXn/xgMnhU3oNE5pokbWmtf6yBXkODXK/2DTZYqs9NSDWCZsqZ3gse+QHa1FpY7vgpmgUdvz4wQUUmAptz58U6mYYwyGV6eNnBQ/Gkq9ypvwL+RBk9pIXSPdCOgAqR6kCq8MYIIun8vZhoeuZ6LV8HIkZhuyZEnx2gw2keBqGgig/4OVy6ZuOmXEKotLbyAgdtMJ8xXBbg6DA6CsY2+sOIWbew7g6RLkkODuBeqCDHImhIezWJfgKtrpWxUa5EwyVkMAuKH7/XhiFkYEVANEFPhtXVmI1EhjP8QgIu/zqzlg3FaMwLQVhR6w7gf1FYGgIJqmkKWxvIyTUktjr6wgpxDeoRIpn1GNDChyWJ/KDITNlkBM2EEaXhKDYJkyGHoFhTEF2maDeJgYdtlNuHI+YHcciMDlm+oJNaRyP86V5bKHUWPsJ+EVCaMwOI5KCfq9DQgWhMAGsp920qog3oTEZLLuMhdBOw5Dg/UgvZE1ewZ6/ZEeDsOFMubCYgBaUWwOEmMXKITSEZ0qt2CAm9Cy+Pxvifw3ovg3Z0WZxLzapSg23ei9zMkBUbE69VxFcFbGaMTeAONTYeii3zx9/t79/yWxvad6HeXam5n8vvckHe6nmZV+X/mVft9b2mD9/2GM62n43mr7fPf6nvffn54CF+9GZZBnJjbcm/vjzUWW1anOrY0bVjXtBO0tCNKcEqu2ULpMdcW4E+ZFn2CI1MzzyDKsaE8SrhqxWja3O05SkkbRbnKugn5emmEtdeGNvhF+f7hmK7p6hSvLNlHsGjM5VtGaJ4tXo+5fwo/sXlfWjp90F1YnDJsvKjy+b7l4qLA5NSuT2H5eCtXQ/L2YFAAAAAElFTkSuQmCC" alt="">
        </div>
        <div class="prizeTop imgShow">
            <img src="https://source.77cola.com/zjd/dlb_image/1000000075168101.png" alt="">
        </div>
        <div class="prizeMain imgShow">
            <h3>恭喜您中奖</h3>
            <p>POS机免费领</p>
            <div class="btngo">
                <a href="javascript:void(0);">
                    <img src="data:image/png;base64,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" alt="点击按钮">
                    <strong>立即领取</strong>
                </a>
            </div>
        </div>
    </div>
</div>
