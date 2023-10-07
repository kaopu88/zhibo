<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <link rel="stylesheet" href="__NEWSTATIC__/h5/agent/style/index.min.css">
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__NEWSTATIC__/h5/agent/js/media_auto.js"></script>
    <title>{$agent_name}申请</title>
</head>
<style>
    *{
        -webkit-touch-callout:none; /*系统默认菜单被禁用*/
        -webkit-user-select:none; /*webkit浏览器*/
        -khtml-user-select:none; /*早期浏览器*/
        -moz-user-select:none;/*火狐*/
        -ms-user-select:none; /*IE10*/
        user-select:none;
    }
    input {
        -webkit-user-select:auto; /*webkit浏览器*/
    }
</style>

<body>

<div class="container" id="app">
    <header></header>
    <div class="search" >
        <form id="myform" action="#" onsubmit="return false;">
            <div class="icon"></div>
            <input type="search" name="" id="keyword" placeholder="请输入{$agent_name}会长ID或者{$agent_name}名称搜索" style="width: 9.2rem">
        </form>

    </div>

    <div class="recommend_guild" id="wrapper">
        <h4>推荐{$agent_name}</h4>
    </div>

</div>


<div class="shade">
    <div class="message">最多只能申请一个{$agent_name}</div>
    <div class="btn">
        <div class="cancel">取消</div>
        <div class="confirm">确定</div>
    </div>
</div>

<div id="tip" style="position: absolute; width: 8.56rem; height: 1.52667rem; z-index: 999; background: #FFFFFF;border-radius: 0.13333rem;display: none; left: 27px;top: 250.5px;">
    <div id="tipmessage" style="font-size: 0.42667rem; color: #8C8C8C;letter-spacing: 0;padding-top: 0.46667rem;text-align: center;">最多只能申请一个{$agent_name}</div>
</div>

<script>
    $(document).ready(function () {
        var applyState = false;
        $('body').on('click','.guild-btn',function(){

        });

        $(".cancel").click(function () {
            Onclick();
        });
        $(".confirm").click(function () {
            Onclick();
        });

        // 确认和取消事件
        function Onclick() {
            $('.shade').fadeOut();
            // 还原滚动：
            $('body').css({
                "overflow-x": "auto",
                "overflow-y": "auto"
            });
        }

        // 弹窗居中
        function center(obj) {
            var screenWidth = $(window).width();
            screenHeight = $(window).height(); //当前浏览器窗口的 宽高
            var scrolltop = $(document).scrollTop();//获取当前窗口距离页面顶部高度
            var objLeft = (screenWidth - obj.width()) / 2;
            var objTop = (screenHeight - obj.height()) / 2 + scrolltop;
            obj.css({left: objLeft + 'px', top: objTop + 'px'});
        }

        $('.icon').click(function () {
            var keyword = $('#keyword').val();
            if (keyword == '' || keyword == undefined || keyword == null) {
                $('.message').html('请输入搜索的关键词');
                $('.shade').fadeIn();
                center($('.shade'));
                // 禁止浏览器滚动条滚动：
                $('body').css({
                    "overflow-x": "hidden",
                    "overflow-y": "hidden"
                });
                return false;
            }

            $.ajax({
                url: '/h5/Agent/get_list',
                type: 'POST',
                dataType: 'json',
                data: {'token': mytoken,'keyword' :keyword, 'page': page},
                success: function (res) {
                    if (res.status == 0) {
                        var html = '';
                        var appjson = res.data;
                        if (appjson != "" || appjson != null) {
                            $.each(appjson, function(k,v) {
                                if (appjson[k].logo == '' || appjson[k].logo == null || appjson[k].logo == undefined) {
                                    img = '{:img_url('','200_200','logo')}';
                                } else {
                                    img = appjson[k].logo;
                                }
                                if (appjson[k].apply_status == 1) {
                                    classstyle = '<div class="guild-btn-apply">申请中</div>'
                                }if (appjson[k].apply_status == 2) {
                                    classstyle = '<div class="guild-btn-apply">已加入</div>'
                                } else{
                                    classstyle = '<div class="guild-btn" onclick="apply('+appjson[k].id+', this)">申请</div>';
                                }

                                var url = '{:url('Agent/detail')}?token='+mytoken + '&id='+appjson[k].id;
                                html+='<div class="guild_list">' +
                                    '       <a href="'+ url +'" style="text-decoration:none"> ' +
                                    '            <div class="guild">' +
                                    '                <div class="guild-img" style="background-image:url( '+img+') ;background-size:1.97333rem 1.97333rem;"></div>' +
                                    '                <div class="guild-info">' +
                                    '                    <div class="title">'+appjson[k].name+'</div>' +
                                    '                    <div class="chairman-name">{$agent_name}管理：'+appjson[k].agent_info.username+'</div>' +
                                    '                    <div class="chairman-id">{$agent_name}管理ID：'+appjson[k].id+'</div>' +
                                    '                </div>' +
                                    '            </div></a>' + classstyle +
                                    '        </div>'
                            });
                            $("#wrapper").html(html);

                            is_loading = false;
                            is_finish = true;
                        } else {
                            is_loading = false;
                            is_finish = true;
                        }
                    } else {
                        return false;
                    }
                    return false;
                }
            })
        })
    });

    $('#keyword').on('search',function(){
        var context = $('#keyword').val();
        if (context != '') {
            keyword = context;
            searchlist(keyword)
        } else{
            keyword = ''
            searchlist(keyword)
        }
        return false;
    });

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
    keyword = '';

    loading_list(page, is_append, is_loading, keyword);

    var timers = null;
    $(window).scroll(function () {
        if (is_finish) {
            return;
        }
        clearTimeout(timers);
        timers = setTimeout(function() {
            page++;
            is_append = true;
            loading_list(page, is_append, is_loading, keyword);
        }, 200);
    });

    function loading_list(page, is_append, is_loading, keyword = '') {
        var pageSize = 10;
        var html = '';
        if (is_loading) {
            $.ajax({
                url: '/h5/Agent/get_list',
                type: 'POST',
                dataType: 'json',
                data: {'token': mytoken, 'page': page, 'keyword' : keyword},
                success: function (res) {
                    if (res.status == 0) {
                        var appjson = res.data;
                        if (appjson != "" || appjson != null) {
                            $.each(appjson, function(k,v) {
                                if (appjson[k].logo == '' || appjson[k].logo == null || appjson[k].logo == undefined) {
                                    img = '{:img_url('','200_200','logo')}';
                                } else {
                                    img = appjson[k].logo;
                                }
                                classstyle = '';
                                if (appjson[k].apply_status == 1) {
                                    classstyle = '<div class="guild-btn-apply">申请中</div>'
                                }else if (appjson[k].apply_status == 2) {
                                    classstyle = '<div class="guild-btn-apply">已加入</div>'
                                } else {
                                    classstyle = '<div class="guild-btn" onclick="apply('+appjson[k].id+', this)">申请</div>';
                                }

                                var url = '{:url('Agent/detail')}?token='+mytoken + '&id='+appjson[k].id;
                                html+='<div class="guild_list">' +
                                    '       <a href="'+ url +'" style="text-decoration:none"> ' +
                                    '           <div class="guild">' +
                                    '                <div class="guild-img" style="background-image:url( '+img+') ;background-size:1.97333rem 1.97333rem;"></div>' +
                                    '                <div class="guild-info">' +
                                    '                    <div class="title">'+appjson[k].name+'</div>' +
                                    '                    <div class="chairman-name">{$agent_name}管理：'+appjson[k].agent_info.username+'</div>' +
                                    '                    <div class="chairman-id">{$agent_name}管理ID：'+appjson[k].id+'</div>' +
                                    '                </div>' +
                                    '            </div></a>' + classstyle +
                                    '        </div>'
                            });
                            $("#wrapper").append(html);

                            if (appjson.length - pageSize < 0) {
                                is_loading = false;
                                is_finish = true;
                            }
                        } else {
                            is_loading = false;
                            is_finish = true;
                        }
                    } else {
                        return false;
                    }
                    return false;
                }
            })
        }
    }

    function apply(id, ele) {
        $.ajax({
            url: '/h5/Agent/applyAgent',
            type: 'POST',
            dataType: 'json',
            data: {'token': mytoken, 'agent_id': id},
            success: function (res) {
                $('#tipmessage').html(res.message);
                $("#tip").fadeIn();
                if (res.status == 0) {
                    $(ele).removeClass('guild-btn').addClass('guild-btn-apply').text('申请中');
                } else {
                }
                $("#tip").fadeOut(1500);
            }
        })
    }

    function searchlist(keyword) {
        $("#wrapper").html('');
        //$(".no-more").css("display","none")
        page = 1;
        is_loading = true;
        is_append = false;
        is_finish = false;
        loading_list(page, is_append, is_loading, keyword);
    }
</script>
</body>
</html>