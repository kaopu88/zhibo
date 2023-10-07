<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb {
            width: 50%;
        }
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            time_ranger_opts: '{:htmlspecialchars_decode($time_ranger_json)}',
        };
    </script>
</block>

<block name="body">
    <div class="pa_20 p-0" style="padding-bottom: 1px !important;">

        <ul class="tab_nav mt_10">
            <li><a target="_self" class="<if condition="input('interval') == 'his'">current</if>" href="{:url('rank/contr',['interval'=>'his','user_id'=>input('user_id')])}">历史</a></li>
            <li><a target="_self" class="<if condition="input('interval') == 'd'">current</if>" href="{:url('rank/contr',['interval'=>'d','user_id'=>input('user_id')])}">日榜</a></li>
            <li><a target="_self" class="<if condition="input('interval') == 'w'">current</if>" href="{:url('rank/contr',['interval'=>'w','user_id'=>input('user_id')])}">周榜</a></li>
            <li><a target="_self" class="<if condition="input('interval') == 'm'">current</if>" href="{:url('rank/contr',['interval'=>'m','user_id'=>input('user_id')])}">月榜</a></li>
        </ul>
        <div class="bg_container">
            <div class="filter_box mt_10">
                <div class="filter_nav">
                    已选择&nbsp;>&nbsp;
                    <p class="filter_selected"></p>
                </div>
                <if condition="input('interval') != 'his'">
                <div class="filter_options">
                    <ul class="filter_list"></ul>
                    <div class="filter_order">
                        <div style="float: left">
                            <input type="hidden" name="user_id" value=""/>
                        </div>
                        <div class="time_ranger" style="margin-left: 10px;">
                            <select class="base_select range_unit"></select>
                            <select class="base_select range_num"></select>
                            <input value="" readonly placeholder="请选择起始日期" type="text" class="base_text range_custom"/>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                </if>
                <input type="hidden" name="runit" class="range_unit_text" value="{$get.runit}"/>
                <input type="hidden" name="rnum" class="range_num_text" value="{$get.rnum}"/>
            </div>

            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 10%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 10%;">排名</td>
                    <td style="width: 10%;">用户ID</td>
                    <td style="width: 25%;">用户信息</td>
                    <td style="width: 10%;">{:APP_MILLET_NAME}</td>
                    <td style="width: 15%;">所属{:config('app.agent_setting.agent_name')}</td>
                    <td style="width: 20%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="contr_rank">
                    <volist name="contr_rank" id="vo">
                        <tr data-id="{$vo.user_id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                            <td>{$vo.num}</td>
                            <td>{$vo.user_id}</td>
                            <td>
                                <include file="user/user_info"/>
                            </td>
                            <td>
                                总：{$vo.millet}<br/>
                                <span class="fc_green">真实：{$vo.real_millet}<br/></span>
                          <!--      <span class="fc_red">虚拟：{$vo.virtual_millet}</span>-->
                            </td>
                            <td>
                                <include file="user/user_agent2"/>
                            </td>
                        <!--    <td>
                                <a data-query="user_id={$vo.user_id}&interval={:input('interval')}&millet={$vo.real_millet}&name=contr:real:{:input('user_id')}<if condition="$get.rnum != ''">&rnum={$get.rnum}</if>" poplink="millet_handler"
                                href="javascript:;">真实{:APP_MILLET_NAME}变更</a><br/>
                                <a data-query="user_id={$vo.user_id}&interval={:input('interval')}&millet={$vo.virtual_millet}&name=contr:isvirtual:{:input('user_id')}<if condition="$get.rnum != ''">&rnum={$get.rnum}</if>" poplink="millet_handler"
                                href="javascript:;">虚拟{:APP_MILLET_NAME}变更</a>
                            </td>-->
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
    </div>

    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>

<block name="layer">
    <include file="rank/millet_handler"/>
    <include file="user/remark_pop"/>
</block>