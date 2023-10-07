<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {};
        $(function () {
            new SearchList('.filter_box', myConfig);
        })
    </script>
</block>

<block name="body">
    <div class="pa_20" style="width:100%">
        <div class="content_title">
            <h1>
                【{$_user.user_id}-{$_user.nickname}】 所属{:config('app.agent_setting.agent_name')}列表
            </h1>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 7%;">ID</td>
                    <td style="width: 15%;">所属{:config('app.agent_setting.agent_name')}</td>
                    <td style="width: 10%;">所属{:config('app.agent_setting.promoter_name')}</td>
                    <td style="width: 13%;">是否{:config('app.agent_setting.promoter_name')}</td>
                    <td style="width: 8%;">是否主播</td>
                    <td style="width: 7%;">绑定时间</td>
                    <td style="width: 9%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.user_id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                {$vo.agent_id}<br/>
                                {$vo.agent_name}
                            </td>
                            <td>
                                <empty name="vo['promoter']">
                                    直属
                                    <else/>
                                    {$vo.promoter['user_id']}<br/>
                                    {$vo.promoter['nickname']}
                                </empty>
                            </td>
                            <td>
                                <eq name="vo['promoter_current']" value="0">
                                    <span >否</span>
                                    <else/>
                                    <span class="fc_orange">{:config('app.agent_setting.promoter_name')}</span>
                                </eq>
                            </td>
                            <td>
                                <eq name="vo['anchor_current']" value="0">
                                    <span >否</span>
                                    <else/>
                                    <span class="fc_orange">主播</span>
                                </eq>
                            </td>
                            <td>
                            </td>
                            <td>
                                <eq name="vo['promoter_current']" value="0">
                                    <a ajax="get" href="{:url('promoter/create',['user_id'=>$_user.user_id,'agent_id'=>$vo.agent_id])}">设为{:config('app.agent_setting.promoter_name')}</a><br/>
                                </eq>
                                <eq name="vo['promoter_current']" value="1">
                                    <a ajax="get" ajax-confirm="是否确认取消{:config('app.agent_setting.promoter_name')}？" class="fc_red" href="{:url('promoter/cancel',['user_id'=>$_user.user_id])}">取消{:config('app.agent_setting.promoter_name')}</a><br/>
                                 </eq>
                                <eq name="vo['promoter_current']" value="2">
                                    <a ajax="get" ajax-confirm="是否转移{:config('app.agent_setting.promoter_name')}？" class="fc_red" href="{:url('promoter/tranfer',['user_id'=>$_user.user_id])}">{:config('app.agent_setting.promoter_name')}移至当前公会</a><br/>
                                </eq>


                                <eq name="vo['anchor_current']" value="0">
                                    <a ajax="get" href="{:url('anchor/create',['user_id'=>$_user.user_id,'agent_id'=>$vo.agent_id])}">设为主播</a><br/>
                                </eq>

                                <eq name="vo['anchor_current']" value="1">
                                    <a ajax="get" ajax-confirm="是否确认取消主播？" class="fc_red" href="{:url('anchor/cancel',['user_id'=>$_user.user_id])}">取消主播</a><br/>
                                </eq>

                                <eq name="vo['anchor_current']" value="2">
                                    <a ajax="get" ajax-confirm="是否转移主播？" class="fc_red" href="{:url('anchor/tranfer',['user_id'=>$_user.user_id])}">主播移至当前公会</a><br/>
                                </eq>

                                <a ajax="get" ajax-confirm="是否取消绑定？取消绑定后主播会归于平台!" href="{:url('promotion_relation/unbind_agent',['user_id'=>$_user.user_id,'agent_id'=>$vo.agent_id])}">取消绑定{:config('app.agent_setting.agent_name')}</a>
                            </td>
                        </tr>
                    </volist>
                    <else/>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                </notempty>
                </tbody>
            </table>
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
</block>

<block name="layer">
</block>