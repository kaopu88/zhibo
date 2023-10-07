<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
    <script>
        var myConfig = {
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
            list: []
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <span> &nbsp;&nbsp;&nbsp;&nbsp;<if condition="$aid == 1"> 充值总计：<span style="color: red">{$total}</span> <else/> 当前页充值总计:<span style="color: red">{$total}</span> </if></span>
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
                    <div class="filter_search">
                        <input placeholder="上级{:config('app.agent_setting.agent_name')}ID" type="text" name="pid" value="{:input('pid')}"/>
                        <input placeholder="手机号、名称、ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
            <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 10%">ID</td>
                    <td style="width: 10%">{:config('app.agent_setting.agent_name')}</td>
                    <!--                <td style="width: 10%">联系方式</td>-->
                    <td style="width: 10%">用户消费（{:APP_BEAN_NAME}）</td>
                    <!--                <td style="width: 10%">折合RMB（元）</td>-->
                    <td style="width: 10%">用户消费(未分配)</td>
                    <td style="width: 8%">用户充值</td>
                    <td style="width: 8%">苹果充值</td>
                    <td style="width: 15%">收获{:APP_MILLET_NAME}</td>
                    <td style="width: 8%">操作</td>
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
                            <!--                        <td>-->
                            <!--                            {$vo.contact_name} <span class="fc_green">v</span><br/>-->
                            <!--                            {$vo.contact_phone}-->
                            <!--                        </td>-->
                            <td>{$vo.cons}</td>
                            <!--                        <td>{$vo.cons|equ_rmb}</td>-->
                            <td>{$vo.unallocated_cons}</td>
                            <td>
                                {$vo.recharge_num}
                            </td>
                            <td>
                                {$vo.apple_recharge_num}
                            </td>
                            <td>
                                {$vo.millet}
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>