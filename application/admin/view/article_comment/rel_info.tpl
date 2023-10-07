<notempty name="vo['reply_id']">
    <a target="_blank" title="查看评论"
       href="{:url('article_comment/index',['id'=>$vo.reply_info.id,'rel_type'=>$vo.rel_type,'rel_id'=>$vo.rel_id])}">【评论】{$vo.reply_user|user_name}&nbsp;：<br/>[{$vo.reply_info.id}]
        {$vo.reply_info.content}</a>
    <else/>
    <switch name="vo['rel_type']">
        <case value="art">
            <a target="_blank" href="{:url('article/edit',['id'=>$vo.rel_info.id])}">【文章】{$vo.rel_info.title}</a>
        </case>
        <case value="movie">
            <a target="_blank" href="{:url('movie/edit',['id'=>$vo.rel_info.id])}">【电影】{$vo.rel_info.title}</a>
        </case>
    </switch>
</notempty>