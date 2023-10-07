<div class="page-user">
    <if condition="!empty($user_info)">
        <div class="user-info">
            <div class="bg" style="background-image: url({$user_info.avatar})"></div>
            <div class="personal-card">
                <div class="info1">
                    <span class="author"><img class="avatar" src="{$user_info.avatar}" onerror="this.src='__H5__/images/share/avatar.png'"> </span>
                    <span class="focus-btn go-author">
                        <span>关注</span>
                    </span>
                    <p class="nickname">{$user_info.nickname}</p>
                    <p class="shortid">用户号：{$user_info.user_id}</p>
                </div>
                <div class="info2">
                    <p class="signature">{$user_info.sign}</p>
                    <p class="follow-info">
                        <span class="focus block">
                            <span class="num">{$user_info.follow_num}</span>
                            <span class="text">关注</span>
                        </span>
                        <span class="follower block">
                            <span class="num">{$user_info.fans_num}</span>
                            <span class="text">粉丝</span>
                        </span>
                        <span class="liked-num block">
                            <span class="num">{$user_info.like_num}</span>
                            <span class="text">赞</span>
                        </span>
                    </p>
                </div>
            </div>
            <div class="video-tab" height="40px">
                <div class="tab-wrap">
                    <div class="user-tab active tab get-list" data-type="post">作品<span class="num">{$user_info.film_num_str}</span></div>
                    <div class="like-tab tab get-list" data-type="like">喜欢<span class="num">{$user_info.like_num_str}</span></div>
                </div>
            </div>
        </div>

        <div class="pagelet-worklist">
            <ul class="list js-list" id="user-video">
                <volist name='user_videos' id='video'>
                    <li class="item goWork">
                        <div class="cover" style="background-image: url({$video.cover_url});">
                            <div class="digg">
                                <span class="digg-icon"></span>
                                <span class="digg-num">{$video.zan_sum}</span>
                            </div>
                        </div>
                    </li>
                </volist>
            </ul>
            <ul class="list js-list" id="user-like">
                <volist name='user_likes' id='like'>
                    <li class="item goWork">
                        <div class="cover" style="background-image: url({$like.cover_url});">
                            <div class="digg">
                                <span class="digg-icon"></span>
                                <span class="digg-num">{$like.zan_sum}</span>
                            </div>
                        </div>
                    </li>
                </volist>
            </ul>
        </div>
    <else />
        <div class="stop-user">
            <p>未找到用户信息</p>
        </div>
    </if>
    <div id="pagelet-banner" class="pagelet-banner">
        <div class="app-download" id="download">
            <div class="download-btn"><span class="txt">打开看看</span></div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $("#user-like").hide();
        $("#user-video").show();
        $(".tab-wrap .tab").click(function(){
            $(this).addClass("active").siblings().removeClass("active");
            if( $(this).hasClass("user-tab") ){
                $("#user-like").hide();
                $("#user-video").show();
            } else {
                $("#user-video").hide();
                $("#user-like").show();
            }
        });
    });
</script>
