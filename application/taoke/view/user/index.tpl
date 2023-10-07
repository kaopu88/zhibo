<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <div class="content_toolbar_search">
                <div class="base_group">

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索用户昵称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">用户ID</td>
                <td style="width: 10%;">用户昵称</td>
                <td style="width: 5%;">淘客余额</td>
                <td style="width: 10%;">淘客余额提现状态</td>
                <td style="width: 10%;">会员等级</td>
                <td style="width: 5%;">渠道ID</td>
                <td style="width: 5%;">会员运营ID</td>
                <td style="width: 5%;">拼多多PID</td>
                <td style="width: 5%;">京东PID</td>
                <td style="width: 5%;">邀请码</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>{$vo.nickname}</td>
                        <td>{$vo.taoke_money}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:user:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.taoke_money_status}" tgradio-name="taoke_money_status" tgradio="{:url('changeStatus',array('user_id'=>$vo['user_id']))}"></div>
                        </td>
                        <td>{$vo.name}</td>
                        <td>{$vo.relation_id}</td>
                        <td>{$vo.special_id}</td>
                        <td>{$vo.pdd_pid}</td>
                        <td>{$vo.jd_pid}</td>
                        <td>{$vo.invite_code}</td>
                        <td>
                            <auth rules="taoke:user:update">
                                <a href="{:url('edit',array("user_id"=>$vo['user_id']))}?__JUMP__">编辑</a>
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="components/recommend_pop" />
</block>