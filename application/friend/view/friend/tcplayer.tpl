<extend name="public:base_iframe"/>
<block name="js">
    <script src="__VENDOR__/TcPlayer/TcPlayer-2.2.2.js?v=__RV__" charset="utf-8"></script>
</block>
<block name="css">
    <style>
        .video_title {
            line-height: 42px;
            height: 42px;
            border-bottom: solid 1px #DCDCDC;
            background-color: #f5f5f5;
            color: #333;
            padding: 0 15px;
            font-size: 14px;
        }

        .video_text {
            overflow: hidden;
            word-break: keep-all;
            width: 100%;
        }
    </style>
</block>
<block name="body">
    <div class="video_title">
        <div class="video_text">{$film.title|short=20}</div>
    </div>
    <div id="my_video" style="width:100%; height:auto;"></div>
    <script>
        var player = new TcPlayer('my_video', {
            "mp4": "{$film.video}",
            "autoplay": true,
            "coverpic": "{$film.cover_url}",
            "width": '414',
            "height": '736'
        });
    </script>
</block>