<div class="thumb">
    <if condition="$vo.to_user.user_id neq ''">
    <a href="{:url('user/detail',['user_id'=>$vo.to_user.user_id])}"
       class="thumb_img thumb_img_avatar">
        <img src="{:img_url($vo['to_user']['avatar'],'200_200','avatar')}"/>
        <div class="thumb_level_box">
            <img title="{$vo.to_user.level_name}" src="{$vo.to_user.level_icon}"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="{:url('user/detail',['user_id'=>$vo.to_user.user_id])}">
            <eq name="vo['to_user']['isvirtual']" value="1">
                <span class="fc_red">[虚拟号]</span><br/>
            </eq>
            {$vo.to_user|user_name}<br/>
            {$vo.to_user.phone|default='未绑定'}
        </a>
    </p>
    </if>
</div>