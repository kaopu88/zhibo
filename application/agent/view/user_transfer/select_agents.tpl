<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="js">
    <script>
        var userItemUrl = '{:url("user/detail")}', selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/user_transfer/select_agents.js?v=__RV__"></script>
    <script>
        var myConfig = {
            list: []
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <include file="user_transfer/selected"/>
        <div class="content_title2">移至以下{:config('app.agent_setting.agent_name')}</div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div class="filter_search">
                        <input placeholder="上级{:config('app.agent_setting.agent_name')}ID" type="text" name="pid" value="{:input('pid')}"/>
                        <input placeholder="手机号、名称、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="level" value="{:input('level')}"/>
            <input type="hidden" name="grade" value="{:input('grade')}"/>
            <input type="hidden" name="province" value="{:input('province')}"/>
            <input type="hidden" name="city" value="{:input('city')}"/>
            <input type="hidden" name="district" value="{:input('district')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 8%">ID</td>
                    <td style="width: 15%">{:config('app.agent_setting.agent_name')}</td>
                    <td style="width: 9%">联系方式</td>
                    <td style="width: 10%">团队规模</td>
                    <td style="width: 8%">所在地区</td>
                    <td style="width: 9%">状态</td>
                    <td style="width: 8%">相关时间</td>
                    <td style="width: 10%">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <div class="thumb">
                                    <a href="javascript:;" class="thumb_img">
                                        <img src="{:img_url($vo['logo'],'200_200','logo')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.name}<br/>
                                            {:$vo['level']+1}级&nbsp;&nbsp;
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td>
                                {$vo.contact_name} <span class="fc_green">v</span><br/>
                                {$vo.contact_phone}
                            </td>
                            <td>
                                {:config('app.agent_setting.promoter_name')}：{$vo.promoter_num}<br/>
                                主播：{$vo.anchor_num}
                            </td>
                            <td>
                                {$vo.province_name}<br>
                                {$vo.city_name}
                            </td>
                            <td>
                                <div tgradio-not="1" tgradio-on="1"
                                    tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status"
                                    tgradio="{:url('agent/change_status',array('id'=>$vo['id']))}"></div>
                            </td>
                            <td>
                                <switch name="vo['expire_status']">
                                    <case value="0">
                                        <span class="fc_green">合作中</span>
                                    </case>
                                    <case value="1">
                                        <span class="fc_orange">即将过期</span>
                                    </case>
                                    <case value="2">
                                        <span class="fc_red">已过期</span>
                                    </case>
                                </switch>
                                <br/>
                                到期：{$vo.expire_time|time_format='','date'}<br/>
                                注册：{$vo.create_time|time_format='','date'}
                            </td>
                            <td>
                                <a href="{:url('user_transfer/select_promoter',['agent_level'=>$vo.level,'agent_id'=>$vo.id])}">选择</a>
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