<div class="thumb">
    <a href="{:url('admin/user/detail',['user_id'=>$vo.cont_user.user_id])}" class="thumb_img thumb_img_avatar">
        <img src="{:img_url($vo['cont_user']['avatar'],'200_200','avatar')}"/>
        <div class="thumb_level_box">
            <img title="{$vo.cont_user.level_name}" src="{$vo.cont_user.level_icon}"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="{:url('admin/user/detail',['user_id'=>$vo.cont_user.user_id])}">
            {$vo.cont_user|user_name}<br/>
            {$vo.cont_user.phone|str_hide=3,4|default='未绑定'}
        </a>
    </p>
</div>