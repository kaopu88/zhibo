<link rel="stylesheet" href="__H5__/css/share/common.css">
<style type="text/css">
    html {
        -webkit-text-size-adjust:none
    }
    .fadeInDown {
        -webkit-animation-duration:1.5s;
        animation-duration:1.5s;
        -webkit-animation-fill-mode:both;
        animation-fill-mode:both;
        -webkit-animation-name:fadeInDown;
        animation-name:fadeInDown
    }
    @-webkit-keyframes fadeInDown {
        0% {
            opacity:0;
            -webkit-transform:translate3d(0,-10%,0);
            transform:translate3d(0,-10%,0)
        }
        to {
            opacity:1;
            -webkit-transform:none;
            transform:none
        }
    }@keyframes fadeInDown {
         0% {
             opacity:0;
             -webkit-transform:translate3d(0,-10%,0);
             transform:translate3d(0,-10%,0)
         }
         to {
             opacity:1;
             -webkit-transform:none;
             transform:none
         }
     }b,strong {
          font-weight:400
      }
    .waytop {
        font-size:0
    }
    .waytop>img {
        width:100%
    }
    .wayMain {
        overflow:hidden;
        overflow-y:auto;
        -webkit-overflow-scrolling:touch;
        height:5rem;
        padding:.2rem;
        font-size:.26rem;
        padding-top:0;
        border-bottom:.2rem solid #e74647;
        border-top:.2rem solid #e74647
    }
    .wayMain>div>.br {
        height:1px;
        border-top:1px dashed #fff;
        margin:.2rem .1rem
    }
    .way,.wayMain p {
        color:#fff
    }
    .way {
        position:absolute;
        left:.15rem;
        top:.15rem;
        font-size:.26rem;
        padding:.04rem .2rem;
        background:rgba(0,0,0,.3);
        border-radius:15px
    }
    .way>img {
        width:.2rem;
        width:.3rem;
        vertical-align:middle;
        margin-top:-.05rem
    }
    .way>span {
        margin-left:.05rem
    }
    .headerBanner>a {
        position:absolute;
        right:0;
        top:0
    }
    .headerBanner>a>img {
        width:1.2rem
    }
    .btnMore {
        background:#cb800a;
        display:block;
        margin:.2rem 0;
        padding:.05rem;
        text-align:center;
        color:#fff;
        font-size:.26rem;
        border-radius:5px
    }
    .showMore {
        display:none
    }
    .close_stop_tip {
        position:absolute;
        right:.15rem;
        font-size:0;
        top:.1rem;
        font-size:.5rem;
        color:#fff;
        text-align:center;
        z-index:34
    }
    .close_stop_tip>img {
        width:.59rem
    }
    .headerBanner>h3 {
        width:2.4rem;
        height:.44rem;
        line-height:.44rem;
        text-align:center;
        color:#fff;
        font-size:.26rem;
        border-radius:1rem;
        left:50%;
        margin-left:-1.2rem;
        position:absolute;
        top:1.95rem
    }
    .headerBanner>h3>span {
        color:#ffec01;
        margin:0 .05rem
    }
    .tip_prize .close_stop_tip {
        margin-top:.45rem;
        margin-right:.1rem
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
    .tip_zj>div {
        overflow:hidden;
        background:#e74647
    }
    .tip_zj {
        overflow:hidden;
        border-radius:8px
    }
    .tip_prize {
        width:7rem
    }
    .topGo {
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
    }@-webkit-keyframes move {
         0% {
             -webkit-transform:translateY(2.4rem);
             transform:translateY(2.4rem)
         }
         to {
             -webkit-transform:translate(0);
             transform:translate(0)
         }
     }.showIn {
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
    }@keyframes showModal {
         0% {
             -webkit-transform:scale(.1);
             transform:scale(.1)
         }
         to {
             -webkit-transform:scale(1);
             transform:scale(1)
         }
     }.prizeTop>img {
          width:100%;
          height:100%
      }
    .rotate {
        position:relative;
        top:-1rem
    }
    .rotate>img {
        width:100%;
        position:absolute
    }
    .prizeMain {
        width:6.16rem;
        background:url(__IMAGES__/activity_component/40021545c2d270d56.png) no-repeat;
        margin:0 auto;
        background-size:100%;
        height:6.97rem;
        position:relative;
        z-index:2;
        overflow:hidden;
        font-size:0;
        left:.1rem
    }
    .shadow {
        opacity:.85
    }
    .btngo>a {
        display:block;
        position:relative;
        -webkit-animation:skip .8s linear infinite alternate;
        animation:skip .8s linear infinite alternate
    }
    .rotate>img {
        animation:circle 10s linear infinite;
        -webkit-animation:circle 10s linear infinite
    }
    .prizeTop {
        width:5.2rem;
        height:2.65rem;
        top:1.6rem;
        z-index:3;
        left:.9rem;
        border:2px solid #fff;
        border-radius:4px;
        -webkit-box-sizing:border-box;
        box-sizing:border-box;
        overflow:hidden
    }
    .prizeMain>p,.prizeTop {
        text-align:center;
        position:absolute
    }
    .prizeMain>p {
        border-radius:3px;
        font-size:.3rem;
        color:#fff;
        background:#c02926;
        width:77.7%;
        left:10%;
        bottom:1.8rem;
        line-height:.55rem
    }
    .close_stop_tip.close_tip1 {
        margin-top:-.55rem;
        margin-right:-.1rem
    }
    .btngo {
        width:5.61rem;
        position:absolute;
        bottom:.4rem;
        left:50%;
        margin-left:-2.91rem
    }
    .btngo>a>img {
        width:5.61rem;
        display:block;
        margin:0 auto
    }
    .btngo>a>strong {
        font-size:.3rem;
        color:#fff;
        position:absolute;
        top:.15rem;
        width:100%;
        text-align:center
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
    }@keyframes skip {
         0% {
             -webkit-transform:scale(.95);
             transform:scale(.95)
         }
         to {
             -webkit-transform:scale(1);
             transform:scale(1)
         }
     }@keyframes circle {
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
       }.rocket {
            position:fixed;
            left:5rem;
            top:9rem;
            z-index:8;
            display:none
        }
    .rocket>img {
        width:1.6rem
    }
    .rocketFadeIn {
        display:block;
        -webkit-animation:rocketMove ease-in-out 2s forwards;
        animation:rocketMove ease-in-out 2s forwards
    }
    @-webkit-keyframes rocketMove {
        0% {
            transform:translate(0);
            -webkit-transform:translate(0)
        }
        to {
            transform:translate(-4.5rem,-7.6rem);
            -webkit-transform:translate(-4.5rem,-7.6rem)
        }
    }@keyframes rocketMove {
         0% {
             transform:translate(0);
             -webkit-transform:translate(0)
         }
         to {
             transform:translate(-4.5rem,-7.6rem);
             -webkit-transform:translate(-4.5rem,-7.6rem)
         }
     }.rocketRight {
          position:fixed;
          right:5rem;
          bottom:5.5rem;
          z-index:8;
          display:none
      }
    .rocketRight>img {
        width:1.6rem
    }
    .rocketRightin {
        display:block;
        -webkit-animation:rocketMove1 ease-in-out 1.5s forwards;
        animation:rocketMove1 ease-in-out 1.5s forwards
    }
    @-webkit-keyframes rocketMove1 {
        0% {
            right:5rem;
            bottom:5.5rem
        }
        to {
            right:.2rem;
            bottom:.6rem
        }
    }@keyframes rocketMove1 {
         0% {
             right:5rem;
             bottom:5.5rem
         }
         to {
             right:.2rem;
             bottom:.6rem
         }
     }.animated {
          -webkit-animation-duration:1s;
          animation-duration:1s;
          -webkit-animation-fill-mode:both;
          animation-fill-mode:both
      }
    .animated.infinite {
        -webkit-animation-iteration-count:infinite;
        animation-iteration-count:infinite
    }
    .animated.hinge {
        -webkit-animation-duration:2s;
        animation-duration:2s
    }
    @-webkit-keyframes bounce {
        0%,20%,50%,80%,to {
            -webkit-transform:translateY(0);
            transform:translateY(0)
        }
        40% {
            -webkit-transform:translateY(-30px);
            transform:translateY(-30px)
        }
        60% {
            -webkit-transform:translateY(-15px);
            transform:translateY(-15px)
        }
    }@keyframes bounce {
         0%,20%,50%,80%,to {
             -webkit-transform:translateY(0);
             transform:translateY(0)
         }
         40% {
             -webkit-transform:translateY(-30px);
             transform:translateY(-30px)
         }
         60% {
             -webkit-transform:translateY(-15px);
             transform:translateY(-15px)
         }
     }.bounce {
          -webkit-animation-name:bounce;
          animation-name:bounce
      }
    @-webkit-keyframes bounceIn {
        0% {
            opacity:0;
            -webkit-transform:scale(.3);
            transform:scale(.3)
        }
        50% {
            opacity:1;
            -webkit-transform:scale(1.05);
            transform:scale(1.05)
        }
        70% {
            -webkit-transform:scale(.9);
            transform:scale(.9)
        }
        to {
            opacity:1;
            -webkit-transform:scale(1);
            transform:scale(1)
        }
    }@keyframes bounceIn {
         0% {
             opacity:0;
             -webkit-transform:scale(.3);
             transform:scale(.3)
         }
         50% {
             opacity:1;
             -webkit-transform:scale(1.05);
             transform:scale(1.05)
         }
         70% {
             -webkit-transform:scale(.9);
             transform:scale(.9)
         }
         to {
             opacity:1;
             -webkit-transform:scale(1);
             transform:scale(1)
         }
     }.bounceIn {
          -webkit-animation-name:bounceIn;
          animation-name:bounceIn
      }
    .body,body {
        background:#33767a
    }
    .headerBanner {
        font-size:0;
        width:100%;
        position:relative;
        z-index:2
    }
    .prize-modal-mask {
        position:absolute
    }
    .prize-modal {
        width:5.9800000078124995rem;
        height:6.9499999921875rem;
        position:absolute;
        top:2.34rem;
        left:.75rem;
        z-index:121
    }
    .prize-modal .prize-modal-inner,.prize-modal .prize-modal-redpack {
        width:5.9800000078124995rem;
        height:6.9499999921875rem;
        top:0;
        left:0;
        position:absolute
    }
    .prize-modal .prize-modal-redpack {
        z-index:2
    }

    .modal-inner,.prize-modal .prize-modal-redpack {
        width:5.9800000078124995rem;
        height:6.9499999921875rem;
        top:0;
        left:0;
        position:absolute
    }
    .prize-modal .prize-modal-light {
        width:7.5rem;
        height:12.06rem;
        position:absolute;
        top:-2.34rem;
        left:-.75rem;
        z-index:1
    }

    .prize-modal .prize-modal-redpack .prize-modal-title {
        width:1.53rem;
        height:.6rem;
        position:absolute;
        top:1.2rem;
        left:2.2000000078125rem;
        background-size:cover;
        background-repeat:no-repeat;
        background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJkAAAA8BAMAAACHowXUAAAAMFBMVEUAAAD///////////////////////////////////////////////////////////87TQQwAAAAD3RSTlMAd0S7Ee5mMyLdmcyqiFWYngqKAAADdklEQVRYw8WXPWhTURTHT2NMSmy1KRWtH7QNFcWlhS4OSlJLC34sguhSSfED3BqHDkWlXZxErYsFB+tQoVsRQcQlol2cdBHFJVFwEcG2IZU0KX9z7su7992+j5j3Cv6WvJvk/vLuO/fcc0Imt/CQBJNYoMDMAd382pxGcZoC0gygjS9awFrfNI3y5JBp6wOQoTqEE3Gd9l4y6EIlZbElgWLCYNpVtggbS3KJs8oWgaLstuJtsFMmhjUYk7ZhWFgjZ6bgQN6IIU+Ttg+wUEyRI12wwbOZcXFzNVsTNLobsPUTExE3V7P1BbXRXb6+LGzRNBTuW4XjHrfySdhUvC8I2wSAjYTgF4ASudoKepCVjWIAVtgWTatANvGlHxstVieybdiyD+8AmPFli9Wem/F0SxlefpYj7ctGV96KmObUpo2BZ/ixMcLWpzYiR+aPX1voIEsiKNxk2zrFhNTDViYrZ3Rbzph9P2NczSc9FqrST6HZYirTQmlOUB6eJ6bRXOAAKhsNwaCYacz2hmocgrJxAAQXyZVxtzOEOa2Nw9fq3RoNw4FpEgymLbZwTxKCV+ROBHbWrLKKEdMDczBYT5EH9wagUz7RTUw0DXkitcKklCdfnAIzK3JBPt1iB/mjT6wzJWxdQWU0xbPzZLWVr5JfOGE7iCwrfV6nDdm5b0BwPC6+GE0Y9NYq4DGq2Xgfvb5O3kTnYLJBRJ1yVBjjaO+RXU3oa7x+Q7NDq2kxKDa0jitDrdo+q1/on9IDPR1UN/iSKMdfkNRP0n79AMjbmrhl8kabv6xG9uM1FNx2MiHpAbCaUEy72Erfqx8elbYXA1WywhaBOwVnm/F+yLStG6EWtu3wYOxfbCsyC9o44O60/V/bri21tcKdYqpRG32Lm+yG4Jx8o4MatSnCyVpJIEkA2ySvjY1LW2AbRJVV7k/KmcC2yJzYrqKEraQC2JRsjUQjjUepQLYRISuOmfX1kmdLUxZttmmrGJVU2X4KBWb5egJM6bPf823kGQQVsb5oFoLR9pQP2433m7qFs7K5+FLPNrPZNmTvFn44HPSK3x51QQ0XVFLkVL9oQ6tSH2lKr1nJ2p3tJUXzovxtO63aqdCi19OkU+sR7jR/24H9si2blyNmNE+Uc249bmc9/p82J6ocViPmiLnrHju1HuGed+kn1DDc3GwVfwEpUJN5u9WNtgAAAABJRU5ErkJggg==)
    }
    .prize-modal .prize-modal-redpack .prize-modal-close {
        width:3.96rem;
        height:.9400000078125rem;
        position:absolute;
        top:5.6500000078124994rem;
        left:1.0099999921875rem;
        background-size:cover;
        background-repeat:no-repeat;
        background-image:url(data:image/png;base64,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)
    }
    .prize-modal .prize-modal-redpack .prize-list {
        position:absolute;
        top:2.22rem;
        left:.5800000078125rem;
        display:table;
        clear:both;
        content:""
    }
    .prize-modal .prize-modal-redpack .prize-list:after,.prize-modal .prize-modal-redpack .prize-list:before {
        display:table;
        content:""
    }
    .prize-modal .prize-modal-redpack .prize-list .prize-item {
        float:left;
        display:block;
        width:1.38rem;
        height:1.38rem;
        margin-right:.36rem;
        margin-bottom:.3rem;
        background-size:cover;
        background-repeat:no-repeat;
        background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIoAAACKCAMAAABCWSJWAAAAjVBMVEW6ExMAAAC6ExO6ExO6ExPglpa6ExP56+v13NzYeXrEMTG6ExO6ExO6ExO6ExP45ubQXFy8GBi6ExO6ExP78vLz19fwycnsu7zjm5zchIXUamu6ExO6ExO6ExO6ExP++/v99vbXdXXAJia6ExO6ExO6ExO6ExO6ExPqtbXlp6fHPj/xz8/fj5DLS0v///+uu9PxAAAALnRSTlOzAFSeQNaQ9vDOu5iKRR71xrSAcfru6eTZ0sp5Kg0G/fvMuK5fXDUX4dy+69XBCRO8JAAAActJREFUeNrt3Mly20AMRVGgpTgJJ0mkRmu2pthJ+v8/z2iWbNqutzWxeWcBbG+xsGWLdnabMvRruNXOe8o2FOIgP1+/poSBeGk2H1O2tXgKXUpVSDIZr3706t8xk1bzlrJrS7Lnaezf7FGS8z1lLebxEH3Mn8SUbUoQs4puRpmI5LuUkqdvEh3NJu3pipa2n6bR09ISHiylsb2Mrg7pXCrRga1R9LVKhys3m1l0trCIRjY2x9HZzCIKGdo8Rmcji8jblN/R2U+LGDCFKUxhClM+YwrCFIQpCFMQpiBMQZiCMAVhCsIUhCkIUxCmIExBmIIwBWEKwhSEKQhTEKYgTEGYgjAFYQrCFIQpCFMQpiBMQZiCMAVhCsIUhCkIUxCmIExBmIIwBWEKwhSEKQhTEKYgTEGYgjAFYQryTSlXjz/f8Z+7lc3/0dncImpRm5ND9LW0iCBa2PoTfb1Yw0Y02HrZR0+LdLUqejt5v04wzazgYil6ETOPbvZjETlVKWWXp8t1O5fpWEzQlKJDSY6j6GC/yMSsNaWYcH8J5nk++9Wnv4tVJkndPZBTnsTTWrsUva3FT9AuJSkLcZFfKu1S7rbXc1089KluykrfvQJBf5PgXjbVZQAAAABJRU5ErkJggg==);
        text-align:center
    }
    .prize-modal .prize-modal-redpack .prize-list .prize-item img {
        margin-top:.21rem;
        width:.9600000000000001rem;
        height:.9600000000000001rem
    }
    .prize-modal-container.ashow .prize-modal-mask {
        -webkit-animation:prizeModalMaskShow .2s 1 linear forwards;
        animation:prizeModalMaskShow .2s 1 linear forwards
    }
    .prize-modal-container.ashow .prize-modal {
        -webkit-animation:prizeModalShow .24s 1 linear forwards;
        animation:prizeModalShow .24s 1 linear forwards
    }
    .prize-modal-container.ahide .prize-modal-mask {
        -webkit-animation:prizeModalMaskHide .2s 1 linear forwards;
        animation:prizeModalMaskHide .2s 1 linear forwards
    }
    .prize-modal-container.ahide .prize-modal {
        -webkit-transform-origin:left top;
        transform-origin:left top;
        -webkit-animation:prizeModalHide .32s 1 linear forwards;
        animation:prizeModalHide .32s 1 linear forwards
    }
    .prize-modal-container.ahide .prize-modal .prize-modal-inner {
        -webkit-transform-origin:50% 50%;
        transform-origin:50% 50%;
        -webkit-animation:prizeModalRotate .32s 1 linear forwards;
        animation:prizeModalRotate .32s 1 linear forwards
    }
    @-webkit-keyframes prizeModalMaskShow {
        0% {
            opacity:0
        }
        to {
            opacity:1
        }
    }@keyframes prizeModalMaskShow {
         0% {
             opacity:0
         }
         to {
             opacity:1
         }
     }@-webkit-keyframes prizeModalMaskHide {
          0% {
              opacity:1
          }
          to {
              opacity:0
          }
      }@keyframes prizeModalMaskHide {
           0% {
               opacity:1
           }
           to {
               opacity:0
           }
       }.prize-modal-container {
            display:none
        }
    .prize-modal-mask {
        position:fixed;
        left:0;
        top:0;
        right:0;
        bottom:0;
        background-color:rgba(0,0,0,.6);
        z-index:120;
        height:100%
    }
    @-webkit-keyframes prizeModalShow {
        0% {
            -webkit-transform:scale(0);
            transform:scale(0)
        }
        to {
            -webkit-transform:scale(1);
            transform:scale(1)
        }
    }@keyframes prizeModalShow {
         0% {
             -webkit-transform:scale(0);
             transform:scale(0)
         }
         to {
             -webkit-transform:scale(1);
             transform:scale(1)
         }
     }@-webkit-keyframes prizeModalHide {
          0% {
              -webkit-transform:scale(1);
              transform:scale(1)
          }
          to {
              -webkit-transform:scale(.1);
              transform:scale(.1);
              top:1.9rem;
              left:6.3rem
          }
      }@keyframes prizeModalHide {
           0% {
               -webkit-transform:scale(1);
               transform:scale(1)
           }
           to {
               -webkit-transform:scale(.1);
               transform:scale(.1);
               top:1.9rem;
               left:6.3rem
           }
       }@-webkit-keyframes prizeModalRotate {
            0% {
                -webkit-transform:rotate(0);
                transform:rotate(0)
            }
            to {
                -webkit-transform:rotate(1turn);
                transform:rotate(1turn)
            }
        }@keyframes prizeModalRotate {
             0% {
                 -webkit-transform:rotate(0);
                 transform:rotate(0)
             }
             to {
                 -webkit-transform:rotate(1turn);
                 transform:rotate(1turn)
             }
         }.giftMain {
              position:absolute;
              top:1.9rem;
              right:.6rem;
              height:.75rem;
              overflow:hidden;
              z-index:5
          }
    .giftMain,.giftMain>img {
        width:.6rem
    }
    .progressbar-wrap .giftMain.shake {
        background-position:0 0;
        -webkit-animation:giftShake .96s linear 1 forwards;
        animation:giftShake .96s linear 1 forwards
    }
    .clickStart {
        position:absolute;
        top:0;
        left:0;
        right:0;
        bottom:0;
        opacity:0;
        background:red;
        z-index:7
    }
    .giftMain.shake {
        background-position:0 0;
        -webkit-animation:giftShake .96s linear 1 forwards;
        animation:giftShake .96s linear 1 forwards
    }
    @-webkit-keyframes giftShake {
        0% {
            -webkit-transform:rotate(0) scale(1);
            transform:rotate(0) scale(1)
        }
        12.5% {
            -webkit-transform:rotate(-15deg) scale(1.2);
            transform:rotate(-15deg) scale(1.2)
        }
        25% {
            -webkit-transform:rotate(0) scale(1.2);
            transform:rotate(0) scale(1.2)
        }
        37.5% {
            -webkit-transform:rotate(15deg) scale(1.2);
            transform:rotate(15deg) scale(1.2)
        }
        50% {
            -webkit-transform:rotate(0) scale(1.2);
            transform:rotate(0) scale(1.2)
        }
        62.5% {
            -webkit-transform:rotate(-15deg) scale(1.2);
            transform:rotate(-15deg) scale(1.2)
        }
        75% {
            -webkit-transform:rotate(0) scale(1.2);
            transform:rotate(0) scale(1.2)
        }
        87.5% {
            -webkit-transform:rotate(15deg) scale(1.2);
            transform:rotate(15deg) scale(1.2)
        }
        to {
            -webkit-transform:rotate(0) scale(1);
            transform:rotate(0) scale(1)
        }
    }@keyframes giftShake {
         0% {
             -webkit-transform:rotate(0) scale(1);
             transform:rotate(0) scale(1)
         }
         12.5% {
             -webkit-transform:rotate(-15deg) scale(1.2);
             transform:rotate(-15deg) scale(1.2)
         }
         25% {
             -webkit-transform:rotate(0) scale(1.2);
             transform:rotate(0) scale(1.2)
         }
         37.5% {
             -webkit-transform:rotate(15deg) scale(1.2);
             transform:rotate(15deg) scale(1.2)
         }
         50% {
             -webkit-transform:rotate(0) scale(1.2);
             transform:rotate(0) scale(1.2)
         }
         62.5% {
             -webkit-transform:rotate(-15deg) scale(1.2);
             transform:rotate(-15deg) scale(1.2)
         }
         75% {
             -webkit-transform:rotate(0) scale(1.2);
             transform:rotate(0) scale(1.2)
         }
         87.5% {
             -webkit-transform:rotate(15deg) scale(1.2);
             transform:rotate(15deg) scale(1.2)
         }
         to {
             -webkit-transform:rotate(0) scale(1);
             transform:rotate(0) scale(1)
         }
     }@-webkit-keyframes leftImg {
          0% {
              -webkit-transform:translate3d(.11rem,0,0);
              transform:translate3d(.11rem,0,0)
          }
          76% {
              -webkit-transform:translate3d(-2.73rem,0,0);
              transform:translate3d(-2.73rem,0,0)
          }
          90% {
              -webkit-transform:translate3d(-2.73rem,0,0);
              transform:translate3d(-2.73rem,0,0)
          }
          to {
              -webkit-transform:translate3d(.11rem,0,0);
              transform:translate3d(.11rem,0,0)
          }
      }@keyframes leftImg {
           0% {
               -webkit-transform:translate3d(.11rem,0,0);
               transform:translate3d(.11rem,0,0)
           }
           76% {
               -webkit-transform:translate3d(-2.73rem,0,0);
               transform:translate3d(-2.73rem,0,0)
           }
           90% {
               -webkit-transform:translate3d(-2.73rem,0,0);
               transform:translate3d(-2.73rem,0,0)
           }
           to {
               -webkit-transform:translate3d(.11rem,0,0);
               transform:translate3d(.11rem,0,0)
           }
       }
    .user {
        position:absolute;
        top:3.15rem;
        z-index:3;
        width:.7rem;
        height:.7rem;
        font-size:0;
        left:.57rem;
        z-index:6
    }
    .user>img {
        width:.8rem;
        margin-top:-.25rem;
        margin-left:-.12rem
    }
    .startBtn {
        width:3rem;
        position:absolute;
        bottom:1.18rem;
        left:50%;
        margin-left:-1.5rem;
        -webkit-animation:skip .8s linear infinite alternate;
        animation:skip .8s linear infinite alternate
    }
    .dfwcontent>div {
        width:.8rem;
        height:.8rem;
        background:#ccc;
        position:absolute;
        opacity:0
    }
    .dfwcontent>div>i {
        display:block;
        width:.2rem;
        height:.2rem;
        background:red;
        position:absolute;
        left:50%;
        top:50%;
        margin-left:-.1rem;
        margin-top:-.1rem
    }
    .dfwcontent>div:first-child {
        top:2.61rem;
        left:.45rem
    }
    .dfwcontent>div:nth-child(2) {
        top:2.55rem;
        left:1.45rem
    }
    .dfwcontent>div:nth-child(3) {
        top:2.55rem;
        left:2.32rem
    }
    .dfwcontent>div:nth-child(4) {
        top:2.55rem;
        left:3.305rem
    }
    .dfwcontent>div:nth-child(5) {
        top:2.55rem;
        left:4.27rem
    }
    .dfwcontent>div:nth-child(6) {
        top:2.55rem;
        left:5.25rem
    }
    .dfwcontent>div:nth-child(7) {
        top:2.55rem;
        left:6.24rem
    }
    .dfwcontent>div:nth-child(8) {
        top:3.75rem;
        left:6.24rem
    }
    .dfwcontent>div:nth-child(9) {
        top:4.69rem;
        left:6.24rem
    }
    .dfwcontent>div:nth-child(10) {
        top:5.95rem;
        left:6.24rem
    }
    .dfwcontent>div:nth-child(11) {
        top:6.9rem;
        left:6.24rem
    }
    .dfwcontent>div:nth-child(12) {
        top:8.1rem;
        left:6.24rem
    }
    .dfwcontent>div:nth-child(13) {
        top:8.1rem;
        left:4.99rem
    }
    .dfwcontent>div:nth-child(14) {
        top:8.1rem;
        left:3.99rem
    }
    .dfwcontent>div:nth-child(15) {
        top:8.1rem;
        left:3.1rem
    }
    .dfwcontent>div:nth-child(16) {
        top:8.1rem;
        left:2.1rem
    }
    .dfwcontent>div:nth-child(17) {
        top:8.1rem;
        left:.68rem
    }
    .dfwcontent>div:nth-child(18) {
        top:6.69rem;
        left:.6rem
    }
    .dfwcontent>div:nth-child(19) {
        top:6.69rem;
        left:1.65rem
    }
    .dfwcontent>div:nth-child(20) {
        top:6.69rem;
        left:2.625rem
    }
    .dfwcontent>div:nth-child(21) {
        top:6.69rem;
        left:3.6rem
    }
    .dfwcontent>div:nth-child(22) {
        top:6.69rem;
        left:4.57rem
    }
    .dfwcontent>div:nth-child(23) {
        top:5.45rem;
        left:4.57rem
    }
    .dfwcontent>div:nth-child(24) {
        top:4.45rem;
        left:4.57rem
    }
    .dfwcontent>div:nth-child(25) {
        top:4.45rem;
        left:3.67rem
    }
    .dfwcontent>div:nth-child(26) {
        top:4.49rem;
        left:2.1rem
    }
    .dfwcontent>div:nth-child(27) {
        top:4.45rem;
        left:.56rem
    }
    .dfwcontent>div:nth-child(28) {
        top:3.6rem;
        left:.5rem
    }
    .dfwcontent>div:first-child>i,.dfwcontent>div:nth-child(18)>i,.dfwcontent>div:nth-child(28)>i {
        top:-.2rem;
        margin-top:0
    }
    .dfwcontent>div:nth-child(2)>i,.dfwcontent>div:nth-child(3)>i,.dfwcontent>div:nth-child(4)>i,.dfwcontent>div:nth-child(5)>i,.dfwcontent>div:nth-child(6)>i,.dfwcontent>div:nth-child(7)>i,.dfwcontent>div:nth-child(19)>i,.dfwcontent>div:nth-child(20)>i,.dfwcontent>div:nth-child(21)>i,.dfwcontent>div:nth-child(22)>i {
        left:100%;
        margin-left:0
    }
    .dfwcontent>div:nth-child(8)>i,.dfwcontent>div:nth-child(9)>i,.dfwcontent>div:nth-child(10)>i,.dfwcontent>div:nth-child(11)>i,.dfwcontent>div:nth-child(12)>i {
        top:100%;
        margin-top:0
    }
    .dfwcontent>div:nth-child(13)>i,.dfwcontent>div:nth-child(14)>i,.dfwcontent>div:nth-child(15)>i,.dfwcontent>div:nth-child(16)>i,.dfwcontent>div:nth-child(17)>i,.dfwcontent>div:nth-child(25)>i,.dfwcontent>div:nth-child(26)>i,.dfwcontent>div:nth-child(27)>i {
        left:-.2rem;
        margin-left:0
    }
    .dfwcontent>div:nth-child(23)>i,.dfwcontent>div:nth-child(24)>i {
        top:-.2rem;
        margin-top:0
    }
    .dfwcontent>div:nth-child(24)>i:nth-child(2),.dfwcontent>div:nth-child(25)>i:nth-child(2) {
        left:100%;
        margin-left:0;
        top:50%;
        margin-top:-.1rem
    }
    .dfwcontent>div:nth-child(23)>i:nth-child(2) {
        top:100%;
        margin-top:-.33rem
    }
    .sz,.sz1 {
        position:absolute;
        height:3rem;
        overflow:hidden;
        top:50%;
        margin-top:-1.5rem;
        left:50%;
        margin-left:-1.5rem;
        z-index:12;
        display:none
    }
    .sz,.sz1,.sz1>img,.sz>img {
        width:3rem
    }
    .sz.ac>img {
        -webkit-animation:go1 steps(1) .4s infinite;
        animation:go1 steps(1) .4s infinite
    }
    @-webkit-keyframes go1 {
        0% {
            -webkit-transform:translate3d(0,-40%,0);
            transform:translate3d(0,-40%,0)
        }
        20% {
            -webkit-transform:translate3d(0,-20%,0);
            transform:translate3d(0,-20%,0)
        }
        40% {
            -webkit-transform:translate3d(0,-40%,0);
            transform:translate3d(0,-40%,0)
        }
        60% {
            -webkit-transform:translate3d(0,-60%,0);
            transform:translate3d(0,-60%,0)
        }
        80% {
            -webkit-transform:translate3d(0,-80%,0);
            transform:translate3d(0,-80%,0)
        }
        to {
            -webkit-transform:translate3d(0,-60%,0);
            transform:translate3d(0,-60%,0)
        }
    }@keyframes go1 {
         0% {
             -webkit-transform:translate3d(0,-40%,0);
             transform:translate3d(0,-40%,0)
         }
         20% {
             -webkit-transform:translate3d(0,-20%,0);
             transform:translate3d(0,-20%,0)
         }
         40% {
             -webkit-transform:translate3d(0,-40%,0);
             transform:translate3d(0,-40%,0)
         }
         60% {
             -webkit-transform:translate3d(0,-60%,0);
             transform:translate3d(0,-60%,0)
         }
         80% {
             -webkit-transform:translate3d(0,-80%,0);
             transform:translate3d(0,-80%,0)
         }
         to {
             -webkit-transform:translate3d(0,-60%,0);
             transform:translate3d(0,-60%,0)
         }
     }.ac1.sz1>img {
          -webkit-transform:translate3d(0,-.4%,0);
          transform:translate3d(0,-.4%,0)
      }
    .ac2.sz1>img {
        -webkit-transform:translate3d(0,-16.7%,0);
        transform:translate3d(0,-16.7%,0)
    }
    .ac3.sz1>img {
        -webkit-transform:translate3d(0,-33%,0);
        transform:translate3d(0,-33%,0)
    }
    .ac4.sz1>img {
        -webkit-transform:translate3d(0,-49.2%,0);
        transform:translate3d(0,-49.2%,0)
    }
    .ac5.sz1>img {
        -webkit-transform:translate3d(0,-65.4%,0);
        transform:translate3d(0,-65.4%,0)
    }
    .ac6.sz1>img {
        -webkit-transform:translate3d(0,-81.65%,0);
        transform:translate3d(0,-81.65%,0)
    }
    .jpImg {
        position:absolute;
        top:0;
        left:0;
        z-index:5
    }
    .jpImg>img {
        position:absolute;
        height:.73rem;
        width:.73rem
    }
    .jpImg>img:first-child {
        top:7.11rem;
        left:1.7rem
    }
    .jpImg>img:nth-child(2) {
        top:6.84rem;
        left:4.5rem;
        width:.9rem;
        height:.9rem
    }
    .jpImg>img:nth-child(3) {
        top:5.28rem;
        left:6.26rem
    }
    .jpImg>img:nth-child(4) {
        top:3.07rem;
        left:2.37rem
    }
    .jpImg>img:nth-child(5) {
        top:8.45rem;
        left:1.81rem;
        width:1rem;
        height:1rem
    }
    .goHome.user {
        -webkit-animation:gohome .3s ease-in forwards;
        animation:gohome .3s ease-in forwards
    }
    .goHome1.user {
        -webkit-animation:gohome1 .3s ease-in forwards;
        animation:gohome1 .3s ease-in forwards
    }
    @-webkit-keyframes gohome {
        0% {
            -webkit-transform:scale(1) skew(0) rotate(0);
            transform:scale(1) skew(0) rotate(0)
        }
        to {
            -webkit-transform:scale(0) skew(40deg) rotate(-1turn);
            transform:scale(0) skew(40deg) rotate(-1turn)
        }
    }@keyframes gohome {
         0% {
             -webkit-transform:scale(1) skew(0) rotate(0);
             transform:scale(1) skew(0) rotate(0)
         }
         to {
             -webkit-transform:scale(0) skew(40deg) rotate(-1turn);
             transform:scale(0) skew(40deg) rotate(-1turn)
         }
     }@-webkit-keyframes gohome1 {
          0% {
              -webkit-transform:scale(0) skew(40deg) rotate(-1turn);
              transform:scale(0) skew(40deg) rotate(-1turn)
          }
          to {
              -webkit-transform:scale(1) skew(0) rotate(0);
              transform:scale(1) skew(0) rotate(0)
          }
      }@keyframes gohome1 {
           0% {
               -webkit-transform:scale(0) skew(40deg) rotate(-1turn);
               transform:scale(0) skew(40deg) rotate(-1turn)
           }
           to {
               -webkit-transform:scale(1) skew(0) rotate(0);
               transform:scale(1) skew(0) rotate(0)
           }
       }
</style>

<script src="__H5__/js/css-base.js"></script>
<div class="tip_prize tip jqTip jqTipCli" style="display: block;">
    <div class="showIn">
        <span class="ribbon"></span>
        <div class="rotate">
            <img src="/vue/static/images/e0c49e5616ba9c025.png" alt="">
        </div>
        <div class="close_stop_tip close_tip close_tip1">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEUAAABFCAMAAAArU9sbAAAAgVBMVEUAAAD///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////9d3yJTAAAAKnRSTlMACvnX0zMR8fbtx1IuKdHidhd+SmbckbTNvmFc57pDI5uGbjxXHqqiizjHs2mxAAADS0lEQVRYw62Y6XaqMBCAExCiIIuAgChVqXXh/R/wdoZoxJAR6p0fPTUzfIdZScLMskrjQz1b78RuPasPcbpik+V23MzbvohN4kxBuE3ZDouX8ZGM1XHRmmWXjPLsaD2esOs83vq+v43zev1YnSdvGV/2PQrnLOhpgqwQUjf7piFXaVdm7lC8snu8EioiXmdT+0aT00aauCaL7y6qa2SYORFaRUtDSFqUnL+rgwPaWY4ZInz2XrYWYm4D7nTR/2FjJIgQE2iBxXr3+NjqtrECX81xtZzQJFiFXn/xgMnhU3oNE5pokbWmtf6yBXkODXK/2DTZYqs9NSDWCZsqZ3gse+QHa1FpY7vgpmgUdvz4wQUUmAptz58U6mYYwyGV6eNnBQ/Gkq9ypvwL+RBk9pIXSPdCOgAqR6kCq8MYIIun8vZhoeuZ6LV8HIkZhuyZEnx2gw2keBqGgig/4OVy6ZuOmXEKotLbyAgdtMJ8xXBbg6DA6CsY2+sOIWbew7g6RLkkODuBeqCDHImhIezWJfgKtrpWxUa5EwyVkMAuKH7/XhiFkYEVANEFPhtXVmI1EhjP8QgIu/zqzlg3FaMwLQVhR6w7gf1FYGgIJqmkKWxvIyTUktjr6wgpxDeoRIpn1GNDChyWJ/KDITNlkBM2EEaXhKDYJkyGHoFhTEF2maDeJgYdtlNuHI+YHcciMDlm+oJNaRyP86V5bKHUWPsJ+EVCaMwOI5KCfq9DQgWhMAGsp920qog3oTEZLLuMhdBOw5Dg/UgvZE1ewZ6/ZEeDsOFMubCYgBaUWwOEmMXKITSEZ0qt2CAm9Cy+Pxvifw3ovg3Z0WZxLzapSg23ei9zMkBUbE69VxFcFbGaMTeAONTYeii3zx9/t79/yWxvad6HeXam5n8vvckHe6nmZV+X/mVft9b2mD9/2GM62n43mr7fPf6nvffn54CF+9GZZBnJjbcm/vjzUWW1anOrY0bVjXtBO0tCNKcEqu2ULpMdcW4E+ZFn2CI1MzzyDKsaE8SrhqxWja3O05SkkbRbnKugn5emmEtdeGNvhF+f7hmK7p6hSvLNlHsGjM5VtGaJ4tXo+5fwo/sXlfWjp90F1YnDJsvKjy+b7l4qLA5NSuT2H5eCtXQ/L2YFAAAAAElFTkSuQmCC" alt="">
        </div>
        <div class="prizeTop">
            <img src="https://source.77cola.com/zjd/dlb_image/1000000025599035.png" alt="">
        </div>
        <div class="prizeMain">
            <p>香港皇家对戒免费领</p>
            <div class="btngo">
                <a href="javascript:void(0);">
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAjEAAAB0CAMAAABDu5MwAAABoVBMVEV8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQR8FQTjeAh8FQTouyrnhw58FQTnsCN8FQTnkBTntifnqB7nmBl8FQR8FQTuxi393jLkeQjZpiXuxy3yiAqXMAXMZAnrgQnXbgiwRwb72zLdcgjJjR/+4DP+3jH8lw38kQr+1iz+0yr8oBL+wB/+uhv+tRj8lAz8mxD+xCL+zyj+2y/+zSf+vh7+yCT+yiX+2S5sWwD+sBX8oxX+shf8mQ/8lQz+wiD9pxb+0Cn+2C3+xiP+yyb+vB38nRD+txn+qxP8qRj////+rRP8qxr9rxz9sR39rRqYfwr9pBSYggz+qBO+oxr/862+nhaYfQnfsh3fuSG+mhT9tx7frxuDbQO+pBvfwiW+oRnPpxl3ZAL3wyLfvSTnuh+ihQyYgQyNcwX30yurkBKriw73ySaNdwfvyyjTrx21kxH/8Z7+5lj/+dTGphr+40PvviC1mha+lhJ3YwL//ez+7IPXtiHGnxZ3ZQL+63bnyCjvwyR3YwH+6Gj/9bwKm3RBAAAAK3RSTlMGAg8cJBQ5RQsXL1NrdV9/2iy3u0q3P7u3t7tkWq/s2EqP5Gea0bWKz8sumV8/fQAACcZJREFUeNrs2L2SgjAUhuHs8qPQSWEHA51jbiY9F0IYGigo4Ko34SBElyCWyPeMNtTvnHOA/QB8gi09AyAbimHkDMDIWjFjK78AZOzGUgxThlo8JYCj85ShGqYsFTP2kma3hANoyS1Lx2b+F0O93GMOYIrv1MxcjBGMl2K8wKsk9cxkGIKBt8kERjJsDiZwEAwsSVwjGWYEk3GAJZlDyTwV4zkuRgzYhoyjbhmjGAom5QDLUp0MDZnhz/ROcrGUwCZz9V6iYqYRc0ExYBNfaMjMxQSqGHy8g7VigrEYWkoqmHC9GNk2TSsrDkcUhyoZWktUjF5K/koxVVELUhaSG7oiLwaN6qntuk5qVYWyvkvsT2tJ/87vipG1mNVGMq1YUWul1vd93nDYr9gPH2uJPc6YcKWYQphKPinFdj2H3Ypei9FnzDXiNrl4Mq+cUnwAm2q/oqs+ZB7F0OHrrxTTiyd80qKYY4iuPp2+VAydMSdrMfatxGWRb1RIDrsVnYbTl4ph74tphKnlcDRTMWxbMVVtTBi89ByQrRirrqZa8gbHyCFZi7GTbYdYjstWDPyxVz47agJxAH6KJu07mOix9dDv2H0CYgxbN2FFou4yC+iBhIsl+trlNwOdKZW02UMPA9+B3/+5+CVOTMZMTMZM/GeGjZlNTNxnMmZiMuavbK+zifcxSmPSI1H6jrvr83MxmxihMbMcqlmfrcN9n45wbcKqT791mnnNfWM+znzmCmz7TRy2rgSdXAVQNy7Rp9+KVg7+/f99HKExswryXqtvjC1W7YKi1mfPhgjaTFqwklRHfn/KNwaM+eI1aU2V9nqwNskWtlKu1kLEyrSvsF6D6vZl0L82cS23GiVP+cZojNmuHXLlVieZ4xpjy5WIYZI6bT6cTJlCZR93jZHV9qkTFF98Y8CYr94RM8iLzCE2i3vY2/KlmQoX3Shq6mu3dflqgdhGudHE1F+9YzLmn4xJI6KiTEWcvHxpyCF/ESq9Bmov6LNYbrpj7xgw5mF04LCXMtbtgEBCBZca9fBwJKhwCXrXzVkJ5s26qbxjwJi5l6Sl+r2R52XapjjspYx1OyBovheaoOA6T6t5TBJ0JAS96+ZsD4U0C7jMvWNUxuRQuHWBbYDaa8o/jSkS2SswesQ6nOJTOxYgtjFtTTlBOveO+8Z8mHtJCMqtS8i7HEKTbGBjS61EGulKyUReCdqBa0zoxIhKCkU0948PYzImhSR16hzKLh82RnparCIJ5frvxigjYtKI4x8Dxiz8REFpqwOQdQWEJhFjbBkQ2AOzHOpWYL/m2oknKPTzl4V/jMuYDUS2OoP6VUAUaPI7xmywDBhjrs1ZocVUJAsPGZcxi0h06EjcAsu7jDG0Zzm5PK8WHjIyY0I4dvkFIjuBINSoO8Zkm5aEaMAYpeftWQmbHRwWHjJgzDdPuQG3Nj9CaScQNt/s+NbYkZlSCAic8w3svoUEehA6YwjdmCUEEfk3Hxkw5rOvHOGHyW6QZHYAT833LEHo4iOPzvWjnDzxaBaccf/sDWD32UfGZswOIpOd4c322986e4WTLR1jzjeRTLramIPs3TVms2tMyRKaiZcMGLP0lQz4rpMEbs4AniQcEpKDU8JRj195zZZvJNly2RizXJ5B1hpjfl0fvj+dAaSVRXLoJfeN+bT0lp/skrGOozAURb9j5zOYIhJjdKstJpSb1giGwrFAllxAhUJBMfnsfQ97EpINaGvj0/jd+2xXxwKWzwmQ9/amiDqD3eAIIyVcPRvSfrugZD/7du5oLlWafnclFpz7NG1BqDREfr025hgs3VgOfNZAseyB8qinGkw7RwlmHNy+pRGjPv4kpnfh5ouh2nJjATkC6hggK8ZkoaMA+VAA1o4AamWAiWLZS8I2fq9rUOcDG6FGQGpOBQ22LIb5l9KtjaYWKguPvRiji0dqoL+FgQ2akV2WNSOgvR0LGgCF+6sFRj3fqxvOQ+ZwxmhLa//llAXHijEiNApscBGiBSELwahrJ7h8YgKMntfAtaFBGxqUWMDPGjOviZ6/HERgRGOcMQowhfAM4pUxbFUrOgnAOA80NZjEAlrxunHJ8iwCY8WYz9DQXxsMdGG6PNwHKD+hr6QKG9Hrn8oC5vFZDcjbesJVfwbGijGHvQNc/i2/qNO1bBaVNfrpmb0sXzSH0FgxJjlEIi9I8reXxrydqqrKk+R3JOJJkryqTqfTmjEe8iZPInuHXfFsG3P3pore7JO8usvy/8bcxXnPP5LILvjI359U2TbmzypenEi4zK6sCrBtTBTnL3t1cwIhDIRhuIg9WIc4BUQPcxET/Ani0f6bWGMWwoKBGM2POE8Jw8s3r9K262pSOVeMdDROsIVDHq9aYRqli+NiiuEcXQ55IlCpDO4Kr2Ls4VA5j7HaUvEqRlyh0mkAuorkqNOrIjwFKMbQ5UBHsgDQMJ9SnIrBOwkhJWuApKJKkQLvEaMYY0+H2rHJuhQtbjGGnh3anUAapr4PhmAtpo5kxq0e3m/xkMsY6zkiznVQaYsxdDycS4rndCeSC9VJFLZiPnVauNfTM2LVc70n0X2OiykzoaZnWRZO+fwq2Y6hMinTybyYf/W894PvCejLft2sJgxEYRimUczPJLZCmE0CQhIVFwoudF9aupJuQ+7/RnrOjJOJJi4EoSb5Hq9AeDnfhBr5/eFIys/Di+hVMVc4IFIRLmgo6L9UpHyhRpr6XUxbWTfUIyoQduiFYRXTpbyo/r2kSrfRozra7hZzHraDUV6pmh4JwSotm8V5SO4Uc4KWs3YaORQDTynmC6DbnWJ2AN1QDDyjmPUO4JFiih1At6JZzJspJv8G6JabYt50MQ4VE8g4OwJ0yWIZUDGOKcZzfFXM5gjQpVDF+I5ni5lMg5krVnuAtpVwZ8F00ixGP2REnu0BbmW50M+Yuhh++qpZEjmuDNxa5YJGST9862LMLKVJgTMDTVmRpGaUTDH1LMmYkgk36+2SfMC4Lcl2vQkpmFjaUaJizCzxkVHJhFEUvQNQB6EKhk7MbTH6yOhk5klIIhi7kCRzE4z6UjLF8Cw5vkmGm0mSEEYuIdRLHYw+MVxMfWRUMpKbEWk6h7FLU8G9SA5Gb5Ithl8yJhlqhqOJBYxazD+XejHBOCoYW4x3SWYRcDTSBZCcS7CgSeJgPFuMTYbeMtQMRwOgc+ED49tgVDE2GcenM0MWSgB/7dQxCsAwDARBYiOw///h6HDgrgups1tJqgf9t0NAGKbBhBgN583Unq2GSM3m8ngxlCvJrHHQEKldNdqLwVhMmFFFJC32kmJyU6vZEAmLtNhLiMkDUXa5FIMberFiMURfugH2BXxNgcQdrwAAAABJRU5ErkJggg==" alt="点击按钮">
                </a>
            </div>
        </div>
    </div>
</div>

        
