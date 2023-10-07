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

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="wait">等待审核</li>
                            <li class="modal_select_option" value="success">审核成功</li>
                            <li class="modal_select_option" value="failed">审核失败</li>
                        </ul>
                    </div>

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
                <td style="width: 10%;">id</td>
                <td style="width: 10%;">提现用户</td>
                <td style="width: 5%;">订单详情</td>
                <td style="width: 5%;">提现金额</td>
                <td style="width: 5%;">提现状态</td>
                <td style="width: 10%;">申请时间</td>
                <td style="width: 10%;">备注</td>
                <td style="width: 10%;">处理时间</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.nickname}</td>
                        <td>{$vo.title}</td>
                        <td>{$vo.money}</td>
                        <td>
                            <switch name="vo['status']">
                                <case value="failed">
                                    <span class="fc_red">已拒绝</span>
                                </case>
                                <case value="success">
                                    <span class="fc_green">已打款</span><br/>
                                </case>
                                <case value="wait">
                                    <span class="fc_blue">申请中</span><br/>
                                </case>
                            </switch>
                        </td>
                        <td>{$vo.create_time}</td>
                        <td>{$vo.admin_remark}</td>
                        <td>{$vo.handler_time}</td>
                        <td>
                            <if condition="$vo['status'] == 'wait'">
                                <a data-id="id:{$vo.id}" poplink="audit" href="javascript:;">操作</a>
                                <br/>
                            </if>
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
    <include file="withdraw/audit" />
</block>