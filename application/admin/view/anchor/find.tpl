<extend name="public:base_iframe"/>
<block name="css">
    <style>
        .select_btn {
            color: #555;
        }

        .selected, .selected:visited, .selected:hover, .selected:active {
            color: #eb6100;
        }
    </style>
</block>

<block name="js">
    <script>
        var anchorItemUrl = '{:url("live_film/detail")}',
            selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/live_film/find_anchor.js?v=__RV__"></script>
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
</block>

<block name="body">
    <div class="pa_20">
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left;line-height: 30px;font-size: 12px;">
                        <a class="show_selected" href="javascript:;">已选中主播(<span class="selected_num">0</span>)</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="主播ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="available_status" value="{:input('available_status')}"/>
        </div>


        <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">主播信息</td>
                <td style="width: 12%;">{:APP_MILLET_NAME}余额</td>
                <td style="width: 9%;">累计获得{:APP_MILLET_NAME}</td>
                <td style="width: 10%;">累计直播时长</td>
                <td style="width: 8%;">直播状态</td>
                <td style="width: 10%;">直播位置</td>
                <td style="width: 10%;">所属{:config('app.agent_setting.agent_name')}</td>
                <td style="width: 8%;">加入时间</td>
                <td style="width: 8%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}" class="find_list_li">
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
                            <div tgradio-not="1" tgradio-on="1"
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
                        </td>
                        <td>
                            <include file="user/user_agent2"/>
                        </td>
                        <td>{$vo.create_time|time_format='','date'}</td>
                        <td>
                            <input class="find_params" type="hidden" name="user_id" value="{$vo.user_id}"/>
                            <input class="find_params" type="hidden" name="nickname" value="{$vo.nickname}"/>
                            <input class="find_params" type="hidden" name="phone" value="{$vo.phone}"/>
                            <input class="find_params" type="hidden" name="avatar"
                                   value="{$vo.avatar}"/>
                            <input class="find_params" type="hidden" name="gender" value="{$vo.gender}"/>
                            <input class="find_params" type="hidden" name="level" value="{$vo.level}"/>
                            <a data-id="{$vo.user_id}" class="select_btn" href="javascript:;">选择</a>
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
</block>

<block name="layer">
    <div class="selected_box" style="padding: 10px;display: none;">
        <table class="table" style="width: 100%;box-sizing: border-box;">
            <thead>
            <tr>
                <td>用户ID</td>
                <td>昵称</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</block>