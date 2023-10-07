<div class="thumb">
    <a href="{:url('user/detail',['user_id'=>$vo.user.user_id])}"
       class="thumb_img thumb_img_avatar">
        <img src="{:img_url($vo['user']['avatar'],'200_200','avatar')}"/>
        <div class="thumb_level_box">
            <img title="{$vo.user.level_name}" src="{$vo.user.level_icon}"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="{:url('user/detail',['user_id'=>$vo.user.user_id])}">
            <eq name="vo['user']['isvirtual']" value="1">
                <span class="fc_red">[虚拟号]</span><br/>
            </eq>
            {$vo.user|user_name}<br/>
            {$vo.user.phone|str_hide=3,4|default='未绑定'}
        </a>
    </p>
</div>