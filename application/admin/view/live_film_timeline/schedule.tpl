<extend name="public:base_nav"/>
<block name="js">
    <script>
        var initUrl = '{:url("live_film_timeline/init")}';
        var getFilmInfoUrl = '{:url("live_film/get_film_info")}';
        var getAnchorInfoUrl = '{:url("live_film/get_anchor_info")}';
        var selectAdUrl = '{:url("live_film_ad/find")}';
        var suggestAdUrl = '{:url("live_film_ad/get_suggests")}';
        var getAdsDurationUrl = '{:url("live_film_ad/get_ads_duration")}';
        var insertUrl = '{:url("live_film_timeline/add")}';
        var getTimelineUrl = '{:url("live_film_timeline/get_timeline")}';
        var getDetailUrl = '{:url("live_film_timeline/get_detail")}';
        var adDetailUrl = '{:url("live_film_ad/edit")}';
        var userDetailUrl = '{:url("user/detail")}';
        var cancelUrl = '{:url("live_film_timeline/cancel")}';
    </script>
    <script src="__VENDOR__/jquery.mousewheel.min.js?v=__RV__"></script>
    <script src="__JS__/live_film_timeline/schedule.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__CSS__/live_film_timeline/schedule.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20 sch_main">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="schedule_box">
            <div class="schedule_top">
                <div style="float: left" class="base_button top_add_btn"><span class="icon-plus"></span>排片</div>
                <div style="float: right;" class="base_group">
                    <input placeholder="请选择时间" readonly style="width: 150px;" name="goto_time" value="" type="text"
                           class="base_text"/>
                    <a href="javascript:;" class="base_button goto_btn">转到</a>
                </div>
                <div class="clear"></div>
            </div>
            <div class="schedule_container">
                <div class="schedule_content"></div>
                <div class="schedule_loading"><span class="icon-arrow-up"></span>加载数据中...</div>
            </div>
        </div>
        <div class="rank_box">
            <div class="panel">
                <div class="panel-heading">本周票房排名</div>
                <div class="panel-body">
                    <ul class="sch_right_list rank_list"></ul>
                </div>
            </div>
            <div class="panel mt_10">
                <div class="panel-heading">正在直播中</div>
                <div class="panel-body">
                    <ul class="sch_right_list playing_list"></ul>
                </div>
            </div>
        </div>
        <div class="timeline_show">
        </div>
        <div class="clear"></div>
    </div>
</block>

<block name="layer">
    <include file="live_film_timeline/add_pop"/>
    <include file="live_film_timeline/detail_pop"/>
</block>