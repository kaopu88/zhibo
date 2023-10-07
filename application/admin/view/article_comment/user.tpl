<div class="thumb">
    <a href="javascript:;" class="thumb_img">
        <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
    </a>
    <p class="thumb_info">
        <a target="_blank" href="{:url('user/detail',['user_id'=>$vo.user_id])}">
            {$vo|user_name}<br/>{$vo.phone}
            <eq name="vo['is_author']" value="1">
                <span class="badge">官方回复</span>
            </eq>
        </a>
    </p>
</div>