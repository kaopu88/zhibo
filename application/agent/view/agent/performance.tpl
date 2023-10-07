<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
    <script>
        var myConfig = {
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
            list: [
                {
                    name: 'level',
                    title: '{:config('app.agent_setting.agent_name')}级别',
                    opts: [
                        {name: '一级{:config('app.agent_setting.agent_name')}', value: '0'},
                        {name: '所属{:config('app.agent_setting.agent_name')}', value: '1'},
                    ]
                },
                {
                    name: 'grade',
                    title: '{:config('app.agent_setting.agent_name')}等级',
                    opts: JSON.parse('{:json_encode(enum_array("agent_grades"))}')
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
                    <div class="time_ranger">
                        <select class="base_select range_unit"></select>
                        <select class="base_select range_num"></select>
                        <input value="" readonly placeholder="请选择起始日期" type="text" class="base_text range_custom"/>
                    </div>
                    <ul class="filter_complex">
                        <li>排序：</li>
                        <li sort-name="complex" sort-by="">
                            <span>综合</span>
                        </li>
                        <li sort-name="fans" sort-by="desc">
                            <span>拉新</span>
                            <em></em>
                        </li>
                        <li sort-name="millet" sort-by="desc">
                            <span>{:config('app.product_setting.millet_name')}</span>
                            <em></em>
                        </li>
                        <li sort-name="cons" sort-by="desc">
                            <span>客消</span>
                            <em></em>
                        </li>
                        <li sort-name="active" sort-by="desc">
                            <span>活跃</span>
                            <em></em>
                        </li>
                        <li sort-name="duration" sort-by="desc">
                            <span>时长</span>
                            <em></em>
                        </li>
                    </ul>
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
            <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
            <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%">ID</td>
                <td style="width: 15%">{:config('app.agent_setting.agent_name')}</td>
                <td style="width: 10%">联系方式</td>
                <td style="width: 10%">拉新人数</td>
                <td style="width: 10%">收获{:APP_MILLET_NAME}</td>
                <td style="width: 10%">客户消费</td>
                <td style="width: 10%">活跃人数</td>
                <td style="width: 10%">直播时长</td>
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
                                <a href="{:url('agent/detail',['id'=>$vo.id])}" class="thumb_img">
                                    <img src="{:img_url($vo['logo'],'200_200','logo')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('agent/detail',['id'=>$vo.id])}">
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
                            <eq name=":input('runit')" value="total">
                                {$vo.total_fans}
                                <else/>
                                {$vo.fans}
                            </eq>
                        </td>
                        <td>
                            <eq name=":input('runit')" value="total">
                                {$vo.total_millet}
                                <else/>
                                {$vo.millet}
                            </eq>
                        </td>
                        <td>
                            <eq name=":input('runit')" value="total">
                                {$vo.total_cons}
                                <else/>
                                {$vo.cons}
                            </eq>
                        </td>
                        <td>
                            <in name=":input('runit')" value="total,y">
                                --
                                <else/>
                                {$vo.active}
                            </in>
                        </td>
                        <td>
                            <eq name=":input('runit')" value="total">
                                {$vo.total_duration_str}
                                <else/>
                                {$vo.duration_str}
                            </eq>
                        </td>
                        <td></td>
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
        new SearchList('.filter_box', myConfig);
    </script>
</block>