/*
 * @Descripttion:
 * @version:
 * @Author: sueRimn
 * @Date: 2020-07-04 16:59:45
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-07-06 10:54:07
 */
function initFontSize(baseFontSize, baseWidth) {
  // 获取当前屏幕宽度
  var clientWidth = document.documentElement.clientWidth || window.innerWidth
  // 根据宽度计算根节点字体大小
  var size = ((clientWidth / baseWidth) * baseFontSize * 2).toFixed(3)

  document.querySelector('html').style.fontSize = size + 'px'
}

function isDevice() {
  var isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/) //ios终端
  var isAndroid =
    navigator.userAgent.indexOf('Android') > -1 ||
    navigator.userAgent.indexOf('Linux') > -1 //安卓
  if (isIOS || isAndroid) {
    initFontSize(100, 750)
    $('.help-wrap').css('height', '100vh')
  } else {
    document.querySelector('html').style.fontSize = 200 + 'px'
    $('.help-wrap').css('height', 'auto')
  }
}
isDevice()
window.onresize = function () {
  isDevice()
}
$(document).ready(function () {
  var trigger_status = true
  $('.trigger').click(function () {
    if (trigger_status === true) {
      $(this).removeClass('trigger_down').addClass('trigger_up')
      trigger_status = false
    } else {
      $(this).removeClass('trigger_up').addClass('trigger_down')
      trigger_status = true
    }
  })
})
