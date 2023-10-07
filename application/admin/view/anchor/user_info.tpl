<div class="thumb">
    <a href="{:url('anchor/detail',['user_id'=>$vo.user_id])}" class="thumb_img thumb_img_avatar">
        <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
        <div class="thumb_level_box">
            <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="{:url('anchor/detail',['user_id'=>$vo.user_id])}">
            {$vo|user_name}
            <br/>
            {$vo.phone|str_hide=3,4|default='未绑定'}
        </a>
    </p>
</div>