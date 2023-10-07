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

                <auth rules="admin:lottery_gift:del">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 10%;">等级序号</td>
                    <td style="width: 15%;">等级名称</td>
                    <td style="width: 15%;">一级{$distribute_name}比例</td>
                    <td style="width: 15%;">二级{$distribute_name}比例</td>
                    <td style="width: 15%;">三级{$distribute_name}比例</td>
                    <!--<td style="width: 15%;">设为默认</td>-->
                    <td style="width: 15%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <tr data-id="{$vo.id}" style="height: 45px;">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            1
                        </td>

                        <td>
                            默认分销商
                        </td>

                        <td>
                            {$_info.one_rate}
                        </td>

                        <td>
                            {$_info.two_rate}
                        </td>

                        <td>
                            {$_info.three_rate}
                        </td>
                        <td>
                            <a href="/giftdistribute/config/index.html">编辑</a>
                        </td>
                        </td>
                    </tr>
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.id}" style="height: 45px;">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>
                                {$vo.id}
                            </td>

                            <td>
                                {$vo.level_name}
                            </td>

                            <td>
                                {$vo.one_rate}
                            </td>

                            <td>
                                {$vo.two_rate}
                            </td>

                            <td>
                                {$vo.three_rate}
                            </td>
                           <!-- <td>
                                <div  tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio-on-name="是" tgradio-off-name="否" tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                            </td>-->
                            <td>
                            <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
                            </td>
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