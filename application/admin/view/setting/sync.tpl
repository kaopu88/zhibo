<extend name="public/base_nav"/>
<block name="css">
    <style>
        .sync_tip {
            border: solid 1px #DCDCDC;
            background-color: #fff;
            border-radius: 5px;
            padding: 10px;
            font-size: 12px;
            display: none;
        }

        .sync_tip_close {
            float: right;
            display: inline-block;
        }
    </style>
</block>

<block name="js">
    <script>
        $(function () {
            $('.sync_tip_close').click(function () {
                $('.sync_tip').hide();
                $('.sync_tip_text').text('');
            });
            $('.target_change').click(function () {
                var from = $('[name=from] option:selected').val();
                var target = $('[name=target] option:selected').val();
                $('[name=from] option[value=' + target + ']').prop('selected', true);
                $('[name=target] option[value=' + from + ']').prop('selected', true);
            });
        });

        function tip(data) {
            $('.sync_tip').show();
            $('.sync_tip_text').text('同步结果：' + data.from + '>' + data.target + '(新增数据：' + data.addNum + '条,删除数据：' + data.deleteNum + '条,更新数据：' + data.updateNum + '条)');
        }

        function ajaxAfter(result, next) {
            if (result['status'] == '1') {
                tip(result.data);
            }
            next(false);
        }
    </script>
</block>

<block name="body">

    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin_type_add">
                    <li><a href="javascript:;" class="base_button base_button_s add_btn">新增</a></li>
                </auth>
                <auth rules="admin_type_delete">
                    <li>
                        <a href="{:url('category/delete')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a>
                    </li>
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

        <div>
            <div>当前数据库：{$database}</div>
            <form>
                <table class="content_info2">
                    <tr>
                        <td class="field_name">数据表</td>
                        <td>
                            <select name="tab" class="base_select">
                                <option value="">请选择</option>
                                <volist name="tables" id="tab">
                                    <option value="{$tab}">{$tab}</option>
                                </volist>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">方向</td>
                        <td>
                            <ul>
                                <li>
                                    <select name="from" class="base_select">
                                        <option value="">请选择</option>
                                        <option value="production">正式环境</option>
                                        <option value="development">测试环境</option>
                                    </select>
                                </li>
                                <li>
                                    <span class="icon-arrow-right"></span><br/>
                                    <a href="javascript:;" class="fc_gray target_change">
                                        <span class="icon-loop"></span>
                                    </a>
                                </li>
                                <li>
                                    <select name="target" class="base_select">
                                        <option value="">请选择</option>
                                        <option value="production">正式环境</option>
                                        <option value="development">测试环境</option>
                                    </select>
                                </li>
                            </ul>
                            <div class="clear"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">模式</td>
                        <td>
                            <select name="mode" class="base_select">
                                <option value="">请选择</option>
                                <option value="mirror">全量镜像</option>
                                <option value="incr">仅增量</option>
                                <option value="replace">增量和更新</option>
                                <option value="update">仅更新</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">结构</td>
                        <td>

                            <ul>
                                <li>
                                    <input type="checkbox" />全选
                                </li>
                                <li class="fc_green">
                                    <input type="checkbox" />新增字段:name
                                </li>
                                <li class="fc_red">
                                    <input type="checkbox" />删除字段:name
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name"></td>
                        <td>
                            <div ajax-after="ajaxAfter" class="base_button" ajax="post" ajax-confirm>开始同步</div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

    </div>


</block>