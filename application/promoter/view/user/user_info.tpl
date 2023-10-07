<div class="thumb">
    <a href="[link]" class="thumb_img thumb_img_avatar" style="max-width: 50px;">
        <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
        <div class="thumb_level_box">
            <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="[link]">{$vo|user_name}</a>
    </p>
</div>