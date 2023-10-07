<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script src="https://webapi.amap.com/maps?v=1.4.8&key=0d29625c9a07fbc35067cc31b0b30489"></script>
    <script>
        var list = [
            {
                name: 'live_status',
                title: '直播状态',
                opts: [
                    {name: '禁用', value: '0'},
                    {name: '启用', value: '1'}
                ]
            }
        ];
        var myConfig = {
            list: list
        };
    </script>
    <script src="__JS__/location.js?v=__RV__"></script>
    <script src="__JS__/anchor/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div ajax="post" ajax-url="{:url('user/change_live_status',['live_status'=>'1'])}"
                         ajax-target="list_id"
                         class="base_button base_button_s base_button_gray">直播启用
                    </div>
                    <div ajax="post" ajax-url="{:url('user/change_live_status',['live_status'=>'0'])}"
                         ajax-target="list_id"
                         class="base_button base_button_s base_button_gray">直播禁用
                    </div>
                    <div class="filter_search">
                        <input placeholder="主播ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="live_status" value="{$get.live_status}"/>
            <input type="hidden" name="agent_id" value="{$get.agent_id}"/>
        </div>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 12%;">主播信息</td>
                    <td style="width: 8%;">{:APP_MILLET_NAME}余额</td>
                    <td style="width: 8%;">累计获得{:APP_MILLET_NAME}</td>
                    <td style="width: 10%;">累计直播时长</td>
                    <td style="width: 10%;">直播状态</td>
                    <td style="width: 10%;display: none">直播位置</td>
                    <td style="width: 7%;">提现比例</td>
                    <td style="width: 10%;">加入时间</td>
                    <td style="width: 15%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.user_id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                            <td>{$vo.user_id}</td>
                            <td>
                                <include link="{:url('anchor/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                            </td>
                            <td>
                                <eq name="vo['millet_status']" value="1">
                                    <span class="icon-credit"></span>&nbsp;<span>{$vo.millet}</span><br/>
                                    <else/>
                                    <span class="icon-credit"></span>&nbsp;<span title="提现功能已禁用"
                                                                                class="fc_red">{$vo.millet}</span><br/>
                                </eq>
                                <span class="fc_gray">  <span class="icon-lock"></span>&nbsp;{$vo.fre_millet}</span>
                            </td>
                            <td>{$vo.total_millet}</td>
                            <td>{$vo.total_duration_str}</td>
                            <td>
                                <div tgradio-not="0" tgradio-on="1"
                                    tgradio-off="0" tgradio-value="{$vo.live_status}"
                                    tgradio-name="live_status"
                                    tgradio="{:url('user/change_live_status',['id'=>$vo['user_id']])}"></div>
                            </td>
                            <td style="display: none">
                                <switch name="vo['location']['location_type']">
                                    <case value="auto">
                                        自动定位
                                    </case>
                                    <case value="unknown">
                                        始终未知
                                    </case>
                                    <case value="static">
                                        {$vo.location.city}
                                    </case>
                                </switch>
                                <a data-id="user_id:{$vo.user_id}" poplink="anchor_location_box" title="修改主播位置"
                                href="javascript:;"><span class="icon-location2"></span></a>
                            </td>

                            <td>
                                <a data-id="user_id:{$vo.user_id}" poplink="anchor_cash_rate" title="修改主播提现比例" href="javascript:;">
                                    <if condition="$vo.cash_rate eq 0">
                                        默认
                                        <else/>
                                        {$vo.cash_rate}
                                    </if>
                                </a>
                            </td>

                            <td>{$vo.create_time|time_format='','date'}</td>
                            <td>
                                <a href="{:url('anchor/detail',['user_id'=>$vo['user_id']])}">主播详情</a>
                                <eq name="agent.add_sec" value="1">
                                    <br/>
                                    <a ajax="get" ajax-confirm="是否确认取消主播？" class="fc_red" href="{:url('cancel',['user_id'=>$vo['user_id']])}">取消主播</a>
                                </eq>
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
    <include file="anchor/location_pop"/>
    <include file="anchor/cash_rate"/>
</block>