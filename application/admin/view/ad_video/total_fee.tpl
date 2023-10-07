{$vo.tag_names|default='无标签'}<br/>
<notempty name="vo['music']">
    <a href="javascript:;"><span class="icon-music" style="margin-right: 3px;"></span>{$vo.music.title|short=15}</a> <br/>
    <else/>
    无音乐<br/>
</notempty>