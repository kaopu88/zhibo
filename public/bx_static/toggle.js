$(document).ready(function () {
  /**
   * @name: 初始加载
   * @test: test font
   * @msg: 页面加载完成需要调用的函数
   * @param {type}
   * @return:
   */
  window.onload = function () {
    toggleSidebar()
    toggleMenu()
    screen()
    getFromData()
    getUlData()
  }
  /**
   * @name: 监听界面变化
   * @test: test font
   * @msg: 界面宽高改变时触发的函数
   * @param {type}
   * @return:
   */
  window.onresize = function () {
    toggleSidebar()
    toggleMenu()
    //初始化 字体100px , 设计稿宽750px
    initFontSize(100, 750)
  }
  // 封装toast方法
  function toast(params) {
    /*设置信息框停留的默认时间*/
    var time = params.time
    if (time == undefined || time == '') {
      time = 1500
    }
    var el = document.createElement('div')
    el.setAttribute('class', 'web-toast')
    el.innerHTML = params.message
    document.body.appendChild(el)
    el.classList.add('fadeIn')
    setTimeout(function () {
      el.classList.remove('fadeIn')
      el.classList.add('fadeOut')
      /*监听动画结束，移除提示信息元素*/
      el.addEventListener('animationend', function () {
        document.body.removeChild(el)
      })
      el.addEventListener('webkitAnimationEnd', function () {
        document.body.removeChild(el)
      })
    }, time)
  }
  // 给一级菜单添加左边框
  $('.pitch')
    .find('> .current')
    .parent()
    .css('border-left', '3px solid #2f74eb')
  // 点击切换导航栏
  function screen() {
    let icon_more_status = true
    let icon_side_status = true
    $('.icon_more').click(function () {
      switch (true) {
        case icon_more_status === true && icon_side_status === true:
          $('.main_top_content').addClass('hide')
          $('.main_left').addClass('left0')
          $('.main_right').css('width', 'calc(100% - 230px)')
          $('.icon_side').css('left', '170px')
          $('.index_main').css({ width: 'auto', 'margin-left': '0' })
          icon_more_status = false
          break
        case icon_more_status === false && icon_side_status === true:
          $('.main_top_content').removeClass('hide')
          $('.main_left').removeClass('left0')
          $('.main_right').css('width', 'calc(100% - 450px)')
          $('.icon_side').css('left', '390px')
          $('.index_main').css({
            width: 'calc(100% - 250px)',
            'margin-left': '210px',
          })
          icon_more_status = true
          break
        case icon_more_status === true && icon_side_status === false:
          $('.main_top_content').addClass('hide')
          $('.icon_side').css({
            left: '10px',
            'background-color': 'rgba(0, 0, 0, 0)',
          })
          $('.main_right').css('width', 'calc(100% - 60px)')
          icon_more_status = false
          break
        case icon_more_status === false && icon_side_status === false:
          $('.main_top_content').removeClass('hide')
          $('.main_right').css('width', 'calc(100% - 270px)')
          $('.icon_side').css('left', '220px')
          icon_more_status = true
          break
      }
    })
    $('.icon_side').click(function () {
      switch (true) {
        case icon_more_status === true && icon_side_status === true:
          $('.main_left').addClass('hide')
          $('.main_right').css('width', 'calc(100% - 270px)')
          $('.icon_side').css({
            left: '220px',
            'background-color': 'rgba(0, 0, 0, 0.059)',
            transform: 'rotate(180deg)',
          })
          icon_side_status = false
          break
        case icon_more_status === true && icon_side_status === false:
          $('.main_left').removeClass('hide')
          $('.main_left').removeClass('left0')
          $('.main_right').css('width', 'calc(100% - 450px)')
          $('.icon_side').css({
            left: '390px',
            'background-color': 'rgba(0, 0, 0, 0)',
            transform: 'rotate(0deg)',
          })
          icon_side_status = true
          break
        case icon_more_status === false && icon_side_status === true:
          $('.main_left').addClass('hide')
          $('.icon_side').css({
            left: '10px',
            'background-color': 'rgba(0, 0, 0, 0.059)',
            transform: 'rotate(180deg)',
          })
          $('.main_right').css('width', 'calc(100% - 60px)')
          icon_side_status = false
          break
        case icon_more_status === false && icon_side_status === false:
          $('.main_left').removeClass('hide')
          $('.main_left').addClass('left0')
          $('.icon_side').css({
            left: '170px',
            'background-color': 'rgba(0, 0, 0, 0)',
            transform: 'rotate(0deg)',
          })
          $('.main_right').css('width', 'calc(100% - 230px)')
          icon_side_status = true
          break
      }
    })
  }
  // 添加模态框灰色背景
  function addMaskLayer() {
    $('.main_right').css({ opacity: '0.6', filter: 'blur(1px)' })
    $('.index_main').css({ opacity: '0.6', filter: 'blur(1px)' })
  }
  // 移除模态框灰色背景
  function removeMaskLayer() {
    $('.main_right, .index_main').css({ opacity: '1', filter: 'none' })
  }
  // 点击显示/隐藏一级菜单
  function clickMenu() {
    $('.top_catalog').click(function () {
      $('.main_top_content').addClass('show').css('z-index', '2')
      addMaskLayer()
    })
    $('.index_main').click(function () {
      $('.main_left').removeClass('hide')
      $('.main_top_content').removeClass('show').css('z-index', '0')
      removeMaskLayer()
    })
  }
  // 根据当前不同的屏幕宽度更换菜单按钮的功能
  function toggleSidebar() {
    if ($(window).width() < 768) {
      clickMenu()
      $('.sub_catalog').click(function () {
        $('.main_left').addClass('show')
        addMaskLayer()
      })
      $('.pa_20').click(function () {
        $('.main_left').removeClass('show')
        $('.main_top_content').removeClass('show').css('z-index', '0')
        removeMaskLayer()
      })
    } else if ($(window).width() < 992) {
      clickMenu()
    } else {
      $('.main_left').removeClass('hide')
      $('.main_top_content').removeClass('show')
    }
  }
  // 点击主菜单图标切换主菜单
  function toggleMenu() {
    let i = false
    if ($(window).width() > 768 && $(window).width() < 1200) {
      $('.menu_icon').click(function () {
        if (i === false) {
          $('.main_left').addClass('hide')
          $('.main_top_content').addClass('show')
          i = true
          return i
        } else {
          $('.main_left').removeClass('hide')
          $('.main_top_content').removeClass('show')
          i = false
          return i
        }
      })
    } else {
      $('.main_left').removeClass('hide')
      $('.main_top_content').removeClass('show')
    }
  }
  // 判断表单有无数据
  function getFromData() {
    $pageshow = $('.pageshow')
    if ($pageshow.text() === '') {
      $pageshow.css({ padding: '0 10px 1px', height: '0' })
    }
  }
  // 判断.tab_nav类有无内容
  function getUlData() {
    $tab_nav = $('.tab_nav')
    if ($tab_nav.find('li').length === 0) {
      $tab_nav.css({ height: '0' })
    }
  }

  // h5充值界面点击输入框确定变换字体
  $('.account_input').bind('input propertychange', function () {
    if ($(this).val().length >= 5) {
      $('.verify').css({
        color: 'red',
        cursor: 'pointer',
        'pointer-events': 'unset',
      })
    } else {
      $('.verify').css({ color: '#d0d0d0' })
    }
  })
  // 动态增加html字体大小
  function initFontSize(baseFontSize, baseWidth) {
    // 获取当前屏幕宽度
    var clientWidth = document.documentElement.clientWidth || window.innerWidth
    // 根据宽度计算根节点字体大小
    var size = ((clientWidth / baseWidth) * baseFontSize * 2).toFixed(3)

    document.querySelector('html').style.fontSize = size + 'px'
  }
})
