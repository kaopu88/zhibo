/*
 * @Descripttion:
 * @version:
 * @Author: sueRimn
 * @Date: 2020-07-04 09:56:37
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-07-07 18:54:10
 */
// 动态增加html字体大小
function initFontSize(baseFontSize, baseWidth) {
  // 获取当前屏幕宽度
  var clientWidth = document.documentElement.clientWidth || window.innerWidth
  // 根据宽度计算根节点字体大小
  var size = ((clientWidth / baseWidth) * baseFontSize * 2).toFixed(3)

  document.querySelector('html').style.fontSize = size + 'px'
}
//初始化 字体100px , 设计稿宽750px
initFontSize(100, 750)
