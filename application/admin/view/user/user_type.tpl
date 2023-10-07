<if condition="$vo['is_promoter']!='1' && $vo['is_anchor']!='1'">
    <span class="fc_black">普通用户</span>
    <else/>
    <eq name="vo['is_promoter']" value="1">
        <span class="fc_orange">[{$vo.promoter_info.agent_name}] {:config('app.agent_setting.promoter_name')}</span>
    </eq>
    <if condition="$vo['is_promoter'] eq 1 and $vo['is_anchor'] eq 1">
        <br/>
    </if>
    <eq name="vo['is_anchor']" value="1">
        <span class="fc_orange">[ <if condition="$vo['anchor_info']['agent_name']">  {$vo.anchor_info.agent_name}<else/>平台 </if>]主播</span>
    </eq>
</if>
<eq name="vo['isvirtual']" value="1">
    <br/><span class="fc_red">虚拟用户</span>
</eq>