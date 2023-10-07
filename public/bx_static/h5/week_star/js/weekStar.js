// var appendNumber = 3;
// var prependNumber = 1;

/* 上周闪亮周星 */
var swiper_last = new Swiper('.swiper-container-last', {
  slidesPerView: 1,
  centeredSlides: true,
  spaceBetween: 30,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  navigation: {
    nextEl: '.right',
    prevEl: '.left',
  },
    on: {
        slideChangeTransitionEnd: function(){
            getLastWeekRank();
        },
    },
});

/* 本周礼物周星榜 */
var swiper = new Swiper('.swiper-container', {
  slidesPerView: 3,
  centeredSlides: true,
  spaceBetween: 30,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  observer:true,//修改swiper自己或子元素时，自动初始化swiper
  observeParents:true,
  on: {
      slideChangeTransitionEnd: function(){
          getWeekRank();
      },
  },
});

/* 周星角逐 */
$(document).ready(function () {
  function togglePage(name, removeName) {
    var i;
    $(name).click(function () {
      for (i = 0; i < removeName.length; i++) {
        $(removeName[i]).removeClass('active');
        $(removeName[i] + '_content').removeClass('active');
      }
      $(name).addClass('active');
      $(name + '_content').addClass('active');
      if(name == ".week_rich"){
          type = 2;
      }else if(name == ".week_anchor"){
          type = 1;
      }else if(name == ".active_rule"){
          type = 3;
      }
        getWeekRank();
    })
  }
  // 点击周星富豪
  togglePage('.week_rich', ['.week_anchor', '.active_rule']);
  // 点击周星主播
  togglePage('.week_anchor', ['.week_rich', '.active_rule']);
  // 点击活动规则
  togglePage('.active_rule', ['.week_anchor', '.week_rich']);
});