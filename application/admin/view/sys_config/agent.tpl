<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('agent')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">相关配置</li>
                        <!--<li>其它配置</li>-->
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">{:config('app.agent_setting.agent_name')}管理后台</td>
                                    <td>
                                        <select class="base_select" name="agent_setting[agent_status]" selectedval="{$_info.agent_setting.agent_status ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">{:config('app.agent_setting.agent_name')}前台</td>
                                    <td>
                                        <select class="base_select" name="agent_setting[agent_front_status]" selectedval="{$_info.agent_setting.agent_front_status ? '1' : '0'}">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">合作商名称</td>
                                    <td>
                                        <input class="base_text" name="agent_setting[agent_name]" value="{$_info.agent_setting.agent_name}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">业务员名称</td>
                                    <td>
                                        <input class="base_text" name="agent_setting[promoter_name]" value="{$_info.agent_setting.promoter_name}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">关闭说明</td>
                                    <td>
                                        <textarea name="agent_setting[close_info]" class="base_text" style="height:120px;">{$_info.agent_setting.close_info}</textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!--   <div class="layui-tab-item">
                               <table class="content_info2 mt_10">
                               </table>
                           </div>-->

                       </div>
                   </div>
                   <div class="base_button_div p_b_20">
                       <a href="javascript:;" class="base_button" ajax="post">提交</a>
                   </div>

               </form>
           </div>
       </div>
   </block>