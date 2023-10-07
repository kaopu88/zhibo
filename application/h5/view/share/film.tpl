
<div class="body-content">
    <div class="banner-top">
        <div class="app-download" id="download"  style="background-image:url({$h5_image.download_logo})">
            <div class="download-btn"><span class="txt">打开看看</span></div>
        </div>
    </div>
    <if condition="!empty($video_info) and !empty($user_info)">
        <div class="video-wrap">
            <div class="player-wrap horizen-video" id="video"></div>
            <div class="video-mask"></div>
            <div id="videoUser" class="video-user">
                <div class="user-title">{$video_info.describe}</div>
                <div class="user-avator" style="background-image:url({$user_info.avatar})"></div>
                <div class="user-info">
                    <p class="user-info-name">{$user_info.nickname}</p>
                    <p class="user-info-id">用户号:{$user_info.user_id}</p>
                </div>
                <div class="user-follow-btn">关注</div>
                <div class="clearfix"></div>
            </div>

            <div class="video-info" id="videoInfo">
                <div class="info-right">
                    <div class="info-item info-avator" data-item="avator">
                        <img class="img-avator" src="{$user_info.avatar}" onerror="this.src='__H5__/images/share/avatar.png'">
                        <img class="img-follow" src="__H5__/images/share/icon_home_follow.png">
                    </div>
                    <div class="info-item info-like" data-item="like">
                        <img class="icon" src="__H5__/images/share/like_ico.png">
                        <p class="count">{$video_info.zan_sum + $video_info.zan_sum2}</p>
                    </div>
                    <div class="info-item" data-item="comment">
                        <img class="icon" src="__H5__/images/share/comment_ico.png">
                        <p class="count">{$video_info.comment_sum}</p>
                    </div>
                    <div class="info-item" data-item="share">
                        <img class="icon" src="__H5__/images/share/share_ico.png">
                    </div>

                    <if condition="!empty($video_info.music)">
                    <!-- 音乐 -->
                    <div class="info-item info-music" id="infoMusic" data-item="music">
                        <div class="music-cover animate">
                            <img class="icon" src="{$video_info.music.image}">
                        </div>
                        <div class="music-notes">
                            <img class="note-item item-1 animate" src="__H5__/images/share/music_note.png">
                            <img class="note-item item-2 animate" src="__H5__/images/share/music_note.png">
                            <img class="note-item item-3 animate" src="__H5__/images/share/music_note2.png">
                        </div>
                    </div>
                    <!-- 音乐 -->
                    </if>

                </div>
                <div class="info-item info-bottom" data-item="detail">
                    <p class="bottom-user">{$user_info.nickname}</p>
                    <p class="bottom-desc">{$video_info.describe}</p>
                    <if condition="!empty($video_info.music)">
                    <div class="bottom-music">
                        <div class="music-name">{$video_info.music.title}</div>
                    </div>
                    </if>
                </div>
            </div>

        </div>
    <else />
        <div class="stop-video">
            <p>视频已下架或删除</p>
        </div>
    </if>
    <div class="list-wrap">
        <div class="top-head">
            <span class="title">今日热门推荐</span>
        </div>
        <ul class="video-list clearfix">
            <volist name='more' id='item'>
            <li>
                <a href="javascript:;" class="hot-video-item">
                    <div class="cover" style="background-image: url({$item.cover_url});">
                        <div class="mask"></div>
                        <span class="play-btn"></span>
                        <div class="music-info">
                            <span class="icon" style="background-image: url({$item.avatar})"></span>
                            <div class="info">
                                <p class="name">{$item.nickname}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            </volist>
        </ul>
    </div>
    <div class="more-video" id="moreVideo"><p class="txt">更多精彩，请前往客户端体验</p><div class="arrow"></div></div>
</div>
<div class="banner-bottom">
    <div class="banner-bottom-side">
        <img src="{$h5_image.download_logo}" class="banner-img">
    </div>
    <span class="banner-btn">立即加入</span>
</div>

<if condition="!empty($video_info) and !empty($user_info)">
<script>
$(function () {
    var player = new TcPlayer('video', {
        "autoplay" : false,
        "controls": "none",
        "mp4": "{$video_info.video_url}",
        "poster": "{$video_info.cover_url}",
        "listener": function (msg) {
            if(msg.type == 'ended'){
                $(".vcp-bigplay").toggle();
            }
        }
    });

    $("#video").on("touchend", function(e) {
        e.preventDefault();
    });

    $(".video-mask").on("touchend", function() {
        $("#videoUser").hide();
        $("#videoInfo").show();
        $(".vcp-bigplay").toggle();
        player.togglePlay();
    });


});
</script>
</if>