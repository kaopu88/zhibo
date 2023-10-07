<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <h4 class="mt_10">{:config('app.agent_setting.agent_name')}名下所有人员集中批量管理工具</h4>
        <table class="content_info2 mt_10">
            <tr>
                <td class="field_name">{:config('app.agent_setting.agent_name')}信息</td>
                <td class="field_value">[{$_info.id}] {$_info.name}</td>
            </tr>
            <tr>
                <td class="field_name">所有客户</td>
                <td class="field_value">
                    共计：<span class="fc_orange">{$_info.client_num}</span> 人
                    <auth rules="admin:user:assign_agent">
                        <a data-transfer="user" class="transfer_btn" data-rel="agent" data-id="{$_info.id}"
                           data-condition="sync=1"
                           href="javascript:;">转移(同步{:config('app.agent_setting.promoter_name')})</a>
                        &nbsp;&nbsp;
                        <a data-transfer="user" class="transfer_btn" data-rel="agent" data-id="{$_info.id}"
                           data-condition="sync=0"
                           href="javascript:;">转移(不同步{:config('app.agent_setting.promoter_name')})</a>
                    </auth>
                    <auth rules="admin:user:clear_agent">
                        &nbsp;&nbsp;
                        <a ajax="get" ajax-confirm="将所有用户的{:config('app.agent_setting.agent_name')}信息清空，即转为总后台直属用户?" class="clear_ua_btn"
                           href="{:url('user_transfer/clear_ua',['user_id'=>$_info['id'],'rel'=>'agent'])}">批量清空</a>
                    </auth>
                    <p class="field_tip">
                        1、转移（同步{:config('app.agent_setting.promoter_name')}）：将{:config('app.agent_setting.agent_name')}{$_info.name}的所有用户转移给{:config('app.agent_setting.agent_name')}XXX，并且将用户所属的{:config('app.agent_setting.promoter_name')}也转移给XXX，用户所属的{:config('app.agent_setting.promoter_name')}不变。<br/>
                        2、转移（不同步{:config('app.agent_setting.promoter_name')}）：将{:config('app.agent_setting.agent_name')}{$_info.name}的所有用户转移给{:config('app.agent_setting.agent_name')}XXX，并且重新指定用户所属{:config('app.agent_setting.promoter_name')}为XXX名下的{:config('app.agent_setting.promoter_name')}。<br/>
                        3、批量清空：将所有用户的{:config('app.agent_setting.agent_name')}信息清空，即转为总后台直属用户。
                    </p>
                </td>
            </tr>
            <tr>
                <td class="field_name">所有{:config('app.agent_setting.promoter_name')}</td>
                <td class="field_value">
                    共计：<span class="fc_orange">{$_info.promoter_num}</span> 人
                    <auth rules="admin:promoter:assign_agent">
                        <a class="transfer_btn" data-transfer="promoter" data-rel="agent" data-id="{$_info.id}"
                           href="javascript:;">转移</a>
                    </auth>
                    <auth rules="admin:promoter:cancel">
                        &nbsp;&nbsp;
                        <a ajax="get" ajax-confirm="取消{:config('app.agent_setting.agent_name')}{$_info.name}的所有{:config('app.agent_setting.promoter_name')}?" class="clear_ua_btn"
                           href="{:url('user_transfer/clear_pa',['user_id'=>$_info['id'],'rel'=>'agent'])}">批量取消</a>
                    </auth>
                    <p class="field_tip">
                        1、转移：将{$_info.name}的所有{:config('app.agent_setting.promoter_name')}转移给{:config('app.agent_setting.agent_name')}XXX,且{:config('app.agent_setting.promoter_name')}名下的用户一并转移给XXX。<br/>
                        2、取消{:config('app.agent_setting.agent_name')}{$_info.name}的所有{:config('app.agent_setting.promoter_name')}(取消前需要确认{:config('app.agent_setting.promoter_name')}的客户是否已经转交给他人，如果未转交给他人则会取消失败)。
                    </p>
                </td>
            </tr>
            <tr>
                <td class="field_name">所有主播</td>
                <td class="field_value">
                    共计：<span class="fc_orange">{$_info.anchor_num}</span> 人
                    <auth rules="admin:anchor:assign_agent">
                        <a class="transfer_btn" data-transfer="anchor" data-rel="agent" data-id="{$_info.id}"
                           href="javascript:;">转移</a>
                    </auth>
                    <auth rules="admin:anchor:cancel">
                        <!-- <a ajax="get" ajax-confirm="取消{:config('app.agent_setting.agent_name')}{$_info.name}的所有主播?" class="clear_ua_btn"
                            href="{:url('user_transfer/clear_aa',['user_id'=>$_info['id'],'rel'=>'agent'])}">批量取消</a>-->
                    </auth>
                    <p class="field_tip">
                        1、转移：将{$_info.name}的所有主播转移给{:config('app.agent_setting.agent_name')}XXX。<br/>
                        2、取消{:config('app.agent_setting.agent_name')}{$_info.name}的所有主播。
                    </p>
                </td>
            </tr>
            <tr>
                <td class="field_name">所有虚拟号</td>
                <td class="field_value">
                    共计：<span class="fc_orange">{$_info.isvirtual_num}</span> 人
                    <a ajax="get" ajax-confirm="回收{:config('app.agent_setting.agent_name')}{$_info.name}的所有虚拟号?"
                       href="{:url('agent/recycling_isvirtual',['id'=>$_info['id']])}">回收</a>
                    <p class="field_tip">
                        1、回收：将{$_info.name}的所有虚拟号冻结，且昵称、手机号、绑定关系、头像、密码、{:config('app.product_info.bean_name')}等重置（ID保持不变），以备下次重新分配。
                    </p>
                </td>
            </tr>
        </table>
    </div>
</block>