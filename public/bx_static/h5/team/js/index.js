$(document).ready(function () {
  var isSearch = false;
  // 切换tab页
  $(".item").click(function () {
    $(this).siblings().toggleClass('active');
    $(this).toggleClass('active');
    $('.tabs').toggleClass('active');
    $('.tabs_content_item').toggleClass('active');
  });

  // 搜索
  $('#isSearch').click(function () {
    isSearch = !isSearch;
    if (isSearch) {
        $('#userInfo').hide();
      $(this).removeClass('search').text('取消');
    } else {
        $('#userInfo').show();
      $(this).addClass('search').text('');
    }
    $('.my_team_title').toggle();
    $('.search_input').toggle();
  })


    $.getUrlParam = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return decodeURI(r[2]); // decodeURI(r[2]); 解决参数是中文时的乱码问题

        return null;
    }
    var mytoken = $.getUrlParam("token");
    page = 1;
    is_loading = true;
    is_append = false;
    is_finish = false;
    is_scroll = true;
    keyword = '';
    var length;
    loading_list(page, is_append, is_loading, keyword);
    var info = $('#newAdd').height();
    var tabs = $('.tabs').height();
    var mainAll = $('.main').outerHeight(true);
    var main = $('.main').height();
    var mainMarginTop = mainAll - main;
    // var header =  $('header').height();
    var contentAll = $('.content').outerHeight(true);
    var content = $('.content').height();
    var contentMarginTop = contentAll - content;
    var nav = $('nav').outerHeight(true);
    var maxHeight = $(window).height() - info -tabs-mainMarginTop-contentMarginTop-nav;
    $('#first').css('max-height',maxHeight);
    $('#all').css('max-height',maxHeight);

    $('#first').scroll(function () {
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
            if (page > 1) {
                layui.use('layer', function () {
                    var $ = layui.jquery;
                    layer.ready(function () {
                        layui.layer.load();
                        $(".layui-layer-shade").css('background', '#000000');
                        $(".layui-layer-shade").css('opacity', '0.2');
                    })
                });
            }

            $.ajax({
                url: '/h5/team/get_list',
                type: 'POST',
                dataType: 'json',
                data: {'token': mytoken, 'page': page, 'level' : 1, 'keyword' : keyword},
                success: function (res) {
                    if (res.status == 0) {
                        var appjson = res.data;
                        length = appjson.length;
                        if (appjson != "" || appjson != null) {
                            for (var i = 0; i<appjson.length; i++) {
                                html += ' <a href="/h5/team/team_fans.html?token='+ mytoken + '&user_id=' + appjson[i].user_id +'" style="text-decoration-line: unset;"><div class="list_item">' +
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
                                    '            </div></a>'
                            }
                            if (keyword != '') {
                                $("#first").html('');
                                $("#all").html('');
                                $("#all").append(html);
                            }
                            $("#first").append(html);

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
                    return false;
                }
            })
        }
    }

    $('#all').scroll(function () {
        var scrollTop = $(this).scrollTop();
        var windowHeight = $(this).height();
        var scrollHeight =$(this)[0].scrollHeight; //可滚动的高度
        if (scrollTop + windowHeight == scrollHeight - 1 || scrollTop + windowHeight == scrollHeight) {
            if (allis_finish) {
                return;
            }
            allpage++;
            allis_append = true;
            all_loading_list(allpage, allis_append, allis_loading, allkeyword);
        }
    });
    allpage = 1;
    allis_loading = true;
    allis_append = false;
    allis_finish = false;
    allis_scroll = true;
    allkeyword = '';
    all_loading_list(allpage, allis_append, allis_loading, allkeyword);
    function all_loading_list(page, is_append, is_loading, keyword) {
        var pageSize = 8;
        var html = '';
        if (is_loading) {
            if (page > 1) {
                layui.use('layer', function () {
                    var $ = layui.jquery;
                    layer.ready(function () {
                        layui.layer.load();
                        $(".layui-layer-shade").css('background', '#000000');
                        $(".layui-layer-shade").css('opacity', '0.2');
                    })
                });
            }

            $.ajax({
                url: '/h5/team/get_list',
                type: 'POST',
                dataType: 'json',
                data: {'token': mytoken, 'page': page, 'level' : 'all'},
                success: function (res) {
                    if (res.status == 0) {
                        var appjson = res.data;
                        length = appjson.length;
                        if (appjson != "" || appjson != null) {
                            for (var i = 0; i<appjson.length; i++) {
                                html += '<a href="/h5/team/team_fans.html?token='+ mytoken + '&user_id=' + appjson[i].user_id +'" style="text-decoration-line: unset;"> <div class="list_item">' +
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
                                    '            </div></a>'
                            }
                            $("#all").append(html);
                            if (appjson.length - pageSize < 0) {
                                allis_loading = false;
                                allis_finish = true;
                            }
                        } else {
                            allis_loading = false;
                            allis_finish = true;
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
                    return false;
                }
            })
        }
    }

    $('.searchuser').click(function () {
        var context = $('#generalSearch').val();
        if (context != '') {
            keyword = context;
            searchlist(keyword)
        } else{
            keyword = ''
            searchlist(keyword);
            allsearchlist(keyword);
        }
    });

    $('#generalSearch').on('search',function(){
        var context = $('#generalSearch').val();
        if (context != '') {
            keyword = context;
            searchlist(keyword)
        } else{
            keyword = ''
            searchlist(keyword);
            allsearchlist(keyword);
        }
        return false;
    });

    function searchlist(keyword) {
        $("#first").html('');
        //$(".no-more").css("display","none")
        page = 1;
        is_loading = true;
        is_append = false;
        is_finish = false;
        loading_list(page, is_append, is_loading, keyword);
    }

     function allsearchlist(keyword) {
        $("#all").html('');
        //$(".no-more").css("display","none")
        allpage = 1;
        allis_loading = true;
        allis_append = false;
        allis_finish = false;
        all_loading_list(page, is_append, is_loading, keyword);
    }
});


