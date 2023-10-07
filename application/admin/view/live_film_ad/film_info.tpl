<div class="thumb">
    <a layer-title="0" layer-area="750px,465px" layer-open="{:url('film/tcplayer',['id'=>$vo.id,'type'=>'film_ad'])}" href="javascript:;"
       class="thumb_img">
        <img src="{:img_url($vo['video_cover'],'120_68','film_cover')}"/>
    </a>
    <p class="thumb_info">
        <a  href="{:url('live_film_ad/edit',['id'=>$vo.id])}?__JUMP__">{$vo.ad_title}</a>
    </p>
</div>