$(document).ready(function () {
  var isSearch = false;
  // 切换tab页
  $(".itemall").click(function () {
    $(this).siblings().toggleClass('active');
    $(this).toggleClass('active');
    $('.tabs').toggleClass('active');
    $('.tabs_content_item').toggleClass('active');
  });

  // 搜索
  $('#isSearch').click(function () {
    isSearch = !isSearch;
    if (isSearch) {
      $(this).removeClass('search').text('取消');
    } else {
      $(this).addClass('search').text('');
    }
    $('.my_team_title').toggle();
    $('.search_input').toggle();
  })

    var listHeight ,
        tabs_contentHeight =$('.tabs_content_item.active').height(),
        tabsHeight = $('.tabs').height(),
        user_profile = $('.user_profile').outerHeight(true),
        mainAll = $('.main').outerHeight(true),
        main = $('.main').height(),
        mainMarginTop = mainAll - main,
        header =  $('header').height(),
        contentAll = $('.content.fans').outerHeight(true),
        content = $('.content.fans').height(),
        contentMarginTop = contentAll - content,
        nav = $('nav').outerHeight(true);
    listHeight = $(window).height() - tabs_contentHeight -tabsHeight-user_profile-mainMarginTop-header-contentMarginTop-nav;

    $('#list_fans').css('max-height',listHeight);

    $.getUrlParam = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return decodeURI(r[2]); // decodeURI(r[2]); 解决参数是中文时的乱码问题

        return null;
    }
    var mytoken = $.getUrlParam("token");
    var user_id = $.getUrlParam("user_id");

    page = 1;
    is_loading = true;
    is_append = false;
    is_finish = false;
    is_scroll = true;
    keyword = '';

    loading_list(page, is_append, is_loading, keyword);

    $('#list_fans').scroll(function () {
        var scrollTop = $(this).scrollTop();
        var windowHeight = $(this).height();
        var scrollHeight =$(this)[0].scrollHeight; //可滚动的高度
        if (scrollTop + windowHeight == scrollHeight - 1 || scrollTop + windowHeight == scrollHeight) {
            if (is_finish) {
                return;
            }
            page++;
            is_append = true;
            loading_list(page, is_append, is_loading, keyword);
        }
    });

    function loading_list(page, is_append, is_loading, keyword = '') {
        var pageSize = 10;
        var html = '';
        if (is_loading) {

            layui.use('layer', function () {
                var $ = layui.jquery;
                layer.ready(function() {
                    layui.layer.load();
                    $(".layui-layer-shade").css('background', '#000000');
                    $(".layui-layer-shade").css('opacity', '0.2');
                })
            });

            $.ajax({
                url: '/h5/team/get_list',
                type: 'POST',
                dataType: 'json',
                data: {'token': mytoken, 'user_id': user_id, 'page': page, 'level' : 1, 'keyword' : keyword},
                success: function (res) {
                    if (res.status == 0) {
                        var appjson = res.data;
                        length = appjson.length;
                        if (appjson != "" || appjson != null) {
                            for (var i = 0; i<appjson.length; i++) {
                                html += ' <div class="list_item">' +
                                    '              <div class="user_img" style="background-image:url('+ appjson[i].avatar +') ;background-size:1.28rem 1.28rem;"></div>' +
                                    '              <div class="user_info">' +
                                    '                <div class="title">' +
                                    '                  <div class="name">'+appjson[i].nickname+'</div>' +
                                    '                </div>' +
                                    '                <div class="join_tiem">' +
                                    '                  加入时间 : '+appjson[i].create_time +
                                    '                </div>' +
                                    '              </div>' +
                                    '              <div class="user_count">' +
                                    '                <div><span>下级粉丝</span><span>'+appjson[i].zt_son_total+'</span></div>' +
                                    '              </div>' +
                                    '            </div>'
                            }
                            if (keyword != '') {
                                $("#list_fans").html('');
                            }
                            $("#list_fans").append(html);

                            if (appjson.length - pageSize < 0) {
                                is_loading = false;
                                is_finish = true;
                            }
                        } else {
                            is_loading = false;
                            is_finish = true;
                        }

                        layui.use('layer', function () {
                            layui.layer.closeAll();
                        });
                    } else {
                        layui.use('layer', function () {
                            layui.layer.closeAll();
                        });
                        return false;
                    }

                }
            })

        }
    }

    layui.use('layer', function () {
        var layer = layui.layer;
        var $ = layui.jquery;
        $('.copy').click(function () {
            new ClipboardJS('.copy', {
                text: function(trigger) {
                    return $('.code').html();
                }
            });
            layer.msg('复制成功');
        })
    });

    /*$('#generalSearch').bind('input onchange',function(event){

    })*/
    $('.icon').click(function () {
        var context = $('#generalSearch').val();
        if (context != '') {
            keyword = context;
            searchlist(keyword)
        } else{
            keyword = ''
            searchlist(keyword)
        }
    })

    $('#generalSearch').on('search',function(){
        var context = $('#generalSearch').val();
        if (context != '') {
            keyword = context;
            searchlist(keyword)
        } else{
            keyword = ''
            searchlist(keyword)
        }
        return false;
    });

    function searchlist(keyword) {
        $("#list_fans").html('');
        //$(".no-more").css("display","none")
        page = 1;
        is_loading = true;
        is_append = false;
        is_finish = false;
        loading_list(page, is_append, is_loading, keyword);
    }


});

