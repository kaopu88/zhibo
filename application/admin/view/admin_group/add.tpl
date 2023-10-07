<extend name="public:base_nav"/>
<block name="js">
    <script>
        $(function () {
            var openValue;
            $('.tree_box').on('click', '.rule_item', function () {
                var that = this;
                $s.confirm('删除提示', '是否确认删除该权限？', function () {
                    openValue = $('[name=rules]').val();
                    var id = $(that).attr('rule-id');
                    var rulesStr = $('[name=rules]').val();
                    var rules = rulesStr == '' ? [] : rulesStr.split(',');
                    rules.splice(rules.indexOf(id), 1);
                    $('[name=rules]').val(rules.length <= 0 ? '' : rules.join(','));
                    $(that).remove();
                    saveRules();
                    checkDisplay();
                });
            });
            var $fill = $('[fill-value=\'[name=rules]\']');
            $fill.on('open', function () {
                openValue = $('[name=rules]').val();
                $(this).data('childWin').WinEve.on('selector', function (value) {
                    $('.child_content').empty();
                    for (var i = 0; i < value.length; i++) {
                        var $jq = $('<div class="rule_item"><span class="rule_item_name"></span><span class="icon-remove"></span></div>');
                        $jq.find('.rule_item_name').text(value[i]['text']);
                        $jq.attr('rule-id', value[i]['value']);
                        $jq.attr('title', value[i]['value']);
                        $('.child_box[cat-id=' + value[i]['cat_id'] + '] .child_content').append($jq);
                    }
                    checkDisplay();
                });
            });
            checkDisplay();
            $fill.on('layer_end', function () {
                saveRules();
            });

            function saveRules() {
                var tmp = $('[name=rules]').val();
                var id = $('[name=id]').val();
                if (openValue == tmp || id == '' || !id) {
                    return false;
                }
                $s.post('{:url("admin_group/save_rules")}', {id: id, rules: tmp}, function (result, next) {
                    next(false);
                });
            }

            function checkDisplay() {
                var num = 0;
                $('.tree_box .panel').each(function (index, element) {
                    if (!checkPanelDisplay($(element))) {
                        $(element).hide();
                    } else {
                        $(element).show();
                        num++;
                    }
                });
                return num > 0;
            }

            function checkPanelDisplay($panel) {
                var num = 0;
                $panel.find('.child_box').each(function (index, element) {
                    if (!checkBoxDisplay($(element))) {
                        $(element).hide();
                    } else {
                        $(element).show();
                        num++;
                    }
                });
                return num > 0;
            }

            function checkBoxDisplay($box) {
                return $box.find('.child_content .rule_item').length > 0;
            }
        });
    </script>
</block>
<block name="css">
    <style>
        .rule_item {
            display: inline-block;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 0px 5px;
            border-radius: 5px;
            margin: 0 3px 3px 0;
            cursor: pointer;
            font-size: 12px;
            width: 140px;
            text-align: left;
        }

        .rule_item .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
            float: right;
            margin-right: 3px;
            margin-top: 8px;
        }

        .rule_item:hover {
            color: #e60012;
        }
    </style>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('admin_group/edit'):url('admin_group/add')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">角色名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">角色描述</td>
                    <td>
                        <input class="base_text" name="descr" value="{$_info.descr}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">工作内容</td>
                    <td>
                       <div style="min-width: 1000px">
                           <volist name="work_types" id="work_type">
                               <label>
                                   <input checkedval="{$_info.works}" style="vertical-align: -1px;" type="checkbox" name="works[]" value="{$work_type.value}" />
                                   &nbsp;{$work_type.name} &nbsp;
                               </label>
                           </volist>
                       </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">启用状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        <notempty name="_info['id']">
                            <a style="margin-right: 10px;" class="base_button" href="javascript:;" fill-close="0"
                               fill-value="[name=rules]" d-key="rule_selector"
                               layer-open="{:url('admin_rule/selector')}" layer-area="750px,500px" layer-title="选择权限">选择权限</a>
                        </notempty>
                    </td>
                </tr>
                <notempty name="_info['id']">
                    <tr>
                        <td class="field_name">所有权限</td>
                        <td>
                            <input type="hidden" value="{$_info.rules}" name="rules"/>
                            <div class="tree_box" style="max-width: 1000px;min-width: 750px;">
                                <volist name="tree" id="tr">
                                    <div class="panel panel-default mt_10">
                                        <div class="panel-heading">{$tr.name}</div>
                                        <div class="panel-body">
                                            <volist name="tr['children']" id="child">
                                                <div class="child_box mt_10" cat-id="{$child.id}">
                                                    <div class="content_title2">{$child.name}</div>
                                                    <div class="mt_10 child_content">
                                                        <volist name="child['children']" id="vo">
                                                            <div class="rule_item" rule-id="{$vo.id}">
                                                                <span class="rule_item_name">{$vo.title}</span><span
                                                                    class="icon-remove"></span>
                                                            </div>
                                                        </volist>
                                                    </div>
                                                </div>
                                            </volist>
                                        </div>
                                    </div>
                                </volist>
                            </div>
                        </td>
                    </tr>
                </notempty>
            </table>
        </form>
    </div>
</block>