<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script src="https://webapi.amap.com/maps?v=1.4.8&key=0d29625c9a07fbc35067cc31b0b30489"></script>
    <script>
        var myConfig = {
            list: [
                {
                    name: 'live_status',
                    title: '直播状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/location.js?v=__RV__"></script>
    <script src="__JS__/anchor/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
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
                    <auth rules="admin:live_film_anchor:add">
                        <a class="base_button base_button_s add_btn" href="javascript:;">新增电影主播</a>
                    </auth>
                    <div class="filter_search">
                        <input placeholder="主播ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="live_status" value="{:input('live_status')}"/>
        </div>

        <p style="color: #fe0000;line-height: 25px;font-size: 12px;margin-top: 10px;">
            电影主播是指有电影直播权限的主播，新增电影主播：给普通主播添加电影直播权限。
        </p>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">主播信息</td>
                <td style="width: 10%;">{:APP_MILLET_NAME}余额</td>
                <td style="width: 10%;">累计获得{:APP_MILLET_NAME}</td>
                <td style="width: 10%;">累计直播时长</td>
                <td style="width: 8%;">直播状态</td>
                <td style="width: 10%;">直播位置</td>
                <td style="width: 10%;">所属{:config('app.agent_setting.agent_name')}</td>
                <td style="width: 8%;">加入时间</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <include file="anchor/user_info"/>
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
                            <div tgradio-not="{:check_auth('admin:user:change_live_status')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.live_status}"
                                 tgradio-name="live_status"
                                 tgradio="{:url('user/change_live_status',['id'=>$vo['user_id']])}"></div>
                        </td>
                        <td>
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
                            <auth rules="admin:anchor:change_location">
                                &nbsp;<a data-id="user_id:{$vo.user_id}" poplink="anchor_location_box" title="修改主播位置"
                                         href="javascript:;"><span class="icon-location2"></span></a>
                            </auth>
                        </td>
                        <td>
                            <include file="user/user_agent2"/>
                        </td>
                        <td>{$vo.create_time|time_format='','date'}</td>
                        <td>
                            <a href="{:url('anchor/detail',['user_id'=>$vo['user_id']])}">主播详情</a><br/>
                            <auth rules="admin:live_film_anchor:delete">
                                <a ajax="get" ajax-confirm="是否确认取消主播的电影直播权限？" class="fc_red"
                                   href="{:url('del_anchor',['user_id'=>$vo['user_id']])}">取消权限</a>
                            </auth>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        $(function () {
            var layerIndex, $addBox = $('.add_live_film_anchor');
            $('.add_btn').click(function () {
                layerIndex = layer.open({
                    scrollbar: false,
                    shadeClose: true,
                    type: 1,
                    content: $addBox,
                    title: '新增电影主播',
                    area: ['520px', '250px'],
                    success: function () {
                        $(window).resize();
                    }
                });
            });
            $('.sub_btn').click(function () {
                var anchorUid = $('[name=anchor_uid]').val();
                if (isEmpty(anchorUid)) {
                    return $s.error('请选择主播');
                }
                $s.post('{:url("add_anchor")}', {anchor_uid: anchorUid}, function (result, next) {
                    if (result['status'] == 0) {
                        next();
                    } else {
                        next();
                    }
                });
            });
        })
    </script>
</block>

<block name="layer">
    <include file="anchor/location_pop"/>
    <div class="layer_box add_live_film_anchor pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">主播ID</td>
                <td>
                    <div class="base_group">
                        <input placeholder="" suggest-value="[name=anchor_uid]" suggest="{:url('anchor/get_suggests')}"
                               style="width: 309px;" value="" type="text" class="base_text film_name">
                        <input type="hidden" name="anchor_uid" value="">
                        <a fill-value="[name=anchor_uid]" fill-name=".film_name" layer-open="{:url('anchor/find')}"
                           href="javascript:;" class="base_button base_button_gray select_film_btn">选择主播</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <div class="base_button sub_btn">设为电影主播</div>
                </td>
            </tr>
        </table>
    </div>
</block>