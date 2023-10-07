<notempty name="vo['video_id']">
    云点播：{$vo.video_id}<a style="margin-left: 5px;color: #f57941;" href="javascript:;"><span class="icon-play"></span></a><br/>
</notempty>
<notempty name="vo['third_url']">
    {$vo.source}：<a target="_blank" href="{$vo.third_url}">{$vo.third_url|short=15}</a>
    <a style="margin-left: 5px;color: #f57941;" href="javascript:;"><span class="icon-play"></span></a>
</notempty>