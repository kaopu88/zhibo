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

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:lottery_gift:add">
                    <li>
                        <a href="{:url('add',array('activity_id'=>$activity_id))}?__JUMP__"
                           class="base_button base_button_s">新增</a>
                    </li>
                </auth>

                <!--<auth rules="admin:lottery_gift:del">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>-->
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
                           value="{:input('keyword')}" placeholder="搜索ID、标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 3%;">ID</td>
                    <td style="width: 15%;">奖品名称</td>
                    <td style="width: 15%;">图片</td>
                    <td style="width: 15%;">中奖概率</td>
                    <td style="width: 15%;">创建时间</td>
                    <td style="width: 10%;">活动状态</td>
                    <td style="width: 15%;">操作</td>
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
                                {$vo.name}

                            </td>

                            <td>
                                <img src="{$vo.image}" width="60" alt="">
                            </td>

                            <td>
                                {$vo.probability}
                            </td>

                            <td>
                                {$vo.create_time|time_format}
                            </td>

                            <td>
                                <div tgradio-not="{:check_auth('admin:lottery_gift:edit')?'0':'1'}" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                            </td>

                            <td>
                            <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>

                             <br>
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

    <script>
        new FinderController('.finder', '');
    </script>

</block>