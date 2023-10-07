<eq name="vo['type']" value="robot">
    <span class="fc_gray">机器人</span>
    <else/>
    <if condition="$vo['is_promoter']!='1' && $vo['is_anchor']!='1'">
        <span class="fc_black">普通用户</span>
        <else/>
        <eq name="vo['is_promoter']" value="1">
            <span class="fc_orange">{:config('app.agent_setting.promoter_name')}</span><br/>
        </eq>
        <eq name="vo['is_anchor']" value="1">
            <span class="fc_orange">主播</span>
        </eq>
    </if>
</eq>
<eq name="vo['isvirtual']" value="1">
    <br/><span class="fc_red">协议用户</span>
</eq>