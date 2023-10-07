<extend name="public:base_nav"/>
<block name="css"></block>
<block name="js"></block>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:activity:add">
                    <li><a href="{:url('add')}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="admin:activity:delete">
                    <li><a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder"
                               value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">启用</li>
                            <li class="modal_select_option" value="0">禁用</li>
                        </ul>
                    </div>

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、名称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 8%;">名称</td>
                <td style="width: 10%;">封面图</td>
                <td style="width: 20%;">描述</td>
                <td style="width: 8%;">标识符</td>
                <td style="width: 5%;">状态</td>
                <td style="width: 10%;">有效期</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 40%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.id}
                        </td>
                        <td>
                            <a href="{:url('edit',['id'=>$vo['space_info']['id']])}?__JUMP__">{$vo.name}</a>
                            <notempty name="vo['link']">
                                <a title="{$vo['link']}" target="_blank" href="{$vo['link']}"><span class="icon-link"></span></a>
                            </notempty>
                        </td>
                        <td>
                            <img src="{$vo.icon}" alt="" style="width: 55px;">
                        </td>
                        <td>
                            <span class="fc_green">
                                {$vo.desc|default='未设置'}
                            </span>
                        </td>
                        <td>
                            <span class="fc_green">
                                {$vo.mark|default='未设置'}
                            </span>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:complaint:category_edit')?'0':'1'}" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <if condition="($vo.start_time eq '') or($vo.end_time eq '')">
                                长期
                            <else />
                                开始时间:{$vo.start_time|time_format}<br/>
                                结束时间:{$vo.end_time|time_format}
                            </if>
                        </td>
                        <td>
                            {$vo.create_time|time_format}
                        </td>
                        <td>
                            <auth rules="admin:activity:select">
                                <a href="{:url('details',array('id'=>$vo['id'], 'mark' => $vo['mark']))}?__JUMP__">详情</a><br/>
                            </auth>
                            <auth rules="admin:activity:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a><br/>
                            </auth>
                            <auth rules="admin:activity:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('delete',array('id'=>$vo['id']))}?__JUMP__">删除</a>
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
</block>