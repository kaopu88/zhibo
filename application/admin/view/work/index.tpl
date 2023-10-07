<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'type',
                    title: '工作类型',
                    get: '{:url("common/get_work_types")}'
                }
            ]
        };
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
    </script>
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
                    <div class="filter_search">
                        <input placeholder="任务人ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="type" value="{:input('type')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 7%;">ID</td>
                <td style="width: 10%;">工作人员</td>
                <td style="width: 15%;">工作项</td>
                <td style="width: 9%;">本月接手数量</td>
                <td style="width: 10%;">工作状态</td>
                <td style="width: 10%;">短信提醒</td>
                <td style="width: 10%;">离线时间</td>
                <td style="width: 10%;">最后一次分配任务时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            ID:{$vo.aid}<br>
                            realname:{$vo.admin_info.realname}
                        </td>
                        <td>{$vo.type_name|default=$vo.type}</td>
                        <td>{$vo.unread_num}/{$vo.task_num}</td>
                        <td>
                            <div tgradio-not="0"
                                 tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status"
                                 tgradio-on-name="在线" tgradio-off-name="离线"
                                 tgradio="{:url('change_status',['aid'=> $vo.aid, 'id'=>$vo.type])}"></div>
                        </td>
                        <td>
                            <div tgradio-not="0"
                                 tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.sms_status}"
                                 tgradio-name="sms_status"
                                 tgradio-on-name="开启"
                                 tgradio-off-name="关闭"
                                 tgradio="{:url('change_sms_status',['aid'=>$vo.aid, 'id'=>$vo.type])}"></div>
                        </td>
                        <td>
                            {$vo.offline_time|time_format='','datetime'}
                        </td>
                        <td>
                            {$vo.last_time|time_format='','datetime'}
                        </td>
                        <td>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
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