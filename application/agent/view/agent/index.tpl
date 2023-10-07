<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'grade',
                    title: '{:config('app.agent_setting.agent_name')}等级',
                    opts: JSON.parse('{:json_encode(enum_array("agent_grades"))}')
                },
                {
                    name: 'province',
                    title: '所在省份',
                    data: {country: 0},
                    auto_sub: false,
                    get: '{:url("common/get_area")}'
                },
                {
                    name: 'city',
                    parent: 'province',
                    title: '所在城市',
                    get: '{:url("common/get_area")}'
                },
                {
                    name: 'district',
                    parent: 'city',
                    title: '所在区县',
                    get: '{:url("common/get_area")}'
                }
            ]
        };
    </script>
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
                    <a href="{:url('agent/add')}?__JUMP__" class="base_button base_button_s">新增</a>
                    <!--   <a href="{:url('agent/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>-->
                    <div class="filter_search">
                        <input placeholder="手机号、名称、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="grade" value="{:input('grade')}"/>
            <input type="hidden" name="province" value="{:input('province')}"/>
            <input type="hidden" name="city" value="{:input('city')}"/>
            <input type="hidden" name="district" value="{:input('district')}"/>
        </div>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 10%">ID</td>
                    <td style="width: 15%">{:config('app.agent_setting.agent_name')}</td>
                    <td style="width: 10%">联系方式</td>
                    <td style="width: 10%">团队规模</td>
                    <td style="width: 10%">累计业绩</td>
                    <td style="width: 10%">所在地区</td>
                    <td style="width: 10%">状态</td>
                    <td style="width: 10%">相关时间</td>
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
                                客消：{$vo.total_cons}<br/>
                                {:APP_MILLET_NAME}：{$vo.total_millet}<br/>
                                拉新：{$vo.total_fans}
                            </td>
                            <td>
                                {$vo.province_name}<br>
                                {$vo.city_name}
                            </td>
                            <td>
                                <div tgradio-not="0" tgradio-on="1"
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
                                <notempty name="vo['root_id']">
                                    <a href="{:url('agent/edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a> <br/>
                                    <else/>
                                    <a href="{:url('agent/set_root',array('id'=>$vo['id']))}?__JUMP__">设置主账号</a> <br/>
                                </notempty>
                                <a target="_blank" href="{:url('agent/transfer',array('id'=>$vo['id']))}">传送</a><br/>
                                <!--<a class="fc_red" ajax-confirm ajax="get" href="{:url('agent/del',array('id'=>$vo['id']))}">删除</a>-->
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
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>