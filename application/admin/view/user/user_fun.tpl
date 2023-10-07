直播：<switch name="vo['live_status']"><case value="0"><span class="fc_red">关闭</span></case><case value="1"><span class="fc_green">开启</span></case></switch>
&nbsp;
<auth rules="admin:user:change_upload_status">
    视频：<a fun-name="film_status" fun-value="{$vo.film_status}" href="{:url('user/change_film_status',['id'=>$vo.user_id])}"></a>
    <else/>
    视频：<switch name="vo['film_status']"><case value="0"><span class="fc_red">关闭</span></case><case value="1"><span class="fc_green">开启</span></case></switch>
</auth>
<br/>
<auth rules="admin:user:change_comment_status">
    评论：<a fun-name="comment_status" fun-value="{$vo.comment_status}" href="{:url('user/change_comment_status',['id'=>$vo.user_id])}"></a>&nbsp;
    <else/>
    评论：<switch name="vo['comment_status']"><case value="0"><span class="fc_red">关闭</span></case><case value="1"><span class="fc_green">开启</span></case></switch>&nbsp;
</auth>
<auth rules="admin:user:change_contact_status">
    私信：<a fun-name="contact_status" fun-value="{$vo.contact_status}" href="{:url('user/change_contact_status',['id'=>$vo.user_id])}"></a>
    <else/>
    私信：<switch name="vo['contact_status']"><case value="0"><span class="fc_red">关闭</span></case><case value="1"><span class="fc_green">开启</span></case></switch>
</auth>