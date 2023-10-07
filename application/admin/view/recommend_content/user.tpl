<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var countdown = parseInt('{$countdown}');
        var regUrl = '{:url("user/reg")}';
        var myConfig = {
            list: [
                {
                    name: 'rec_id',
                    title: '推荐位',
                    get: '{:url("recommend_content/get_rec",array('type'=>'user'))}'
                }
            ]
        };
    </script>
    <script src="__JS__/user/index.js?v=__RV__"></script>
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
                    <div ajax="post" ajax-url="{:url('del_recommend')}" ajax-target="list_id" ajax-confirm
                         class="base_button base_button_s base_button_red">取消推荐
                    </div>
                    <div class="filter_search">
                        <input placeholder="用户ID、手机号、昵称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="rec_id" value="{$get.rec_id}" />
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">推荐位</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 14%;">用户类型</td>
                <td style="width: 10%;">用户属性</td>
                <td style="width: 5%;">{:config('app.agent_setting.agent_name')}信息</td>
                <td style="width: 7%;">{:APP_BEAN_NAME}</td>
                <td style="width: 14%;">功能</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.rc_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.rc_id}"/></td>
                        <td>{$vo.rc_id}</td>
                        <td>{$vo.rs_name}</td>
                        <td>
                            <include file="user/user_info"/>
                        </td>
                        <td>
                            <include file="user/user_type"/>
                        </td>
                        <td>
                            <b>{$vo.city_info.name|default='未知'}</b><br/>
                            <include file="user/user_vip_status"/>
                            <br/>
                            <eq name="vo['verified']" value="1">
                                <span class="fc_green">已认证</span>
                                <else/>
                                <span class="fc_gray">未认证</span>
                            </eq>
                        </td>
                        <td>
                            <include file="user/user_agent"/>
                        </td>
                        <td>
                            <eq name="vo['pay_status']" value="1">
                                <span class="icon-credit"></span>&nbsp;<span>{$vo.bean}</span><br/>
                                <else/>
                                <span class="icon-credit"></span>&nbsp;<span title="支付功能已禁用"
                                                                             class="fc_red">{$vo.bean}</span><br/>
                            </eq>
                            <span class="fc_gray">  <span class="icon-lock"></span>&nbsp;{$vo.fre_bean}</span>
                        </td>
                        <td>
                            <include file="user/user_fun"/>
                        </td>
                        <td>{$vo.sort}</td>
                        <td>
                            <a data-query="id={$vo.rc_id}&sort={$vo.sort}" poplink="sort_handler"
                               href="javascript:;">修改排序</a><br/>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('del_recommend',array('id'=>$vo['rc_id']))}?__JUMP__">取消推荐</a>
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
    <include file="recommend_content/sort_handler"/>
    <include file="user/reg_pop"/>
    <include file="user/remark_pop"/>
    <include file="components/recommend_pop"/>
    <include file="user/role_pop"/>
    <include file="user/disable_pop"/>
</block>