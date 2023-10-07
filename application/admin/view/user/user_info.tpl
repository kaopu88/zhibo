<div class="thumb">
    <a href="{:url('user/detail',['user_id'=>$vo.user_id])}" class="thumb_img thumb_img_avatar">
        <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
        <div class="thumb_level_box">
            <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="{:url('user/detail',['user_id'=>$vo.user_id])}">
            {$vo|user_name}
            <auth rules="admin:user:remark">
                &nbsp;<a data-id="user_id:{$vo.user_id}" poplink="user_remark_box" href="javascript:;"><span class="icon-pencil"></span></a>
            </auth>
            <br/>
            {$vo.username}
            
        </a>
        <if condition="$vo.agent_num != '0'">
            <br/>{:config('app.agent_setting.agent_name')}：{$vo.agent_name}
        </if>
    </p>
</div>