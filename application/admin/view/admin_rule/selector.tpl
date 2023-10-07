<extend name="public/base_iframe" />

<block name="js">
    <script src="__JS__/admin_rule/selector.js?v=__RV__"></script>
</block>

<block name="css">
    <style>
        .main2{
            padding: 10px;
        }
        .rule_label{
            display: inline-block;
            cursor: pointer;
            width: 150px;
        }
        .rule_label input{
            vertical-align: -1px;
            margin-right: 4px;
            display: inline-block;
        }
        .child_content .rule_label{
            font-weight: normal;
            margin: 3px;
            font-size: 14px;
        }
        .rule_label.checked{
            color: #00a0e9;
        }
        .content_title2{
            color: #555;
        }
        .child_content{
            padding-left: 17px;
        }
        .panel{
            border: solid 1px #DCDCDC;
            margin-top: 10px;
        }

        .panel:first-child{
            margin-top: 0;
        }

        .panel-heading{
            background-color: #f5f5f5;
            border-bottom: solid 1px #DCDCDC;
            padding: 0 5px;
        }

        .panel-body{
            padding: 10px;
        }

        .panel .content_title2{
            padding-left: 10px;
        }

    </style>
</block>

<block name="body">
    <div class="main2">
        <div class="check_all" style="margin-bottom: 10px;">
            <label class="rule_label">
                <input type="checkbox" class="checkbox checkbox_all"/>全选
            </label>
            &nbsp;&nbsp;已选中规则数量：<span class="check_num">0</span>
        </div>
        <volist name="tree" id="tr">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <label class="rule_label">
                        <input type="checkbox" class="checkbox checkbox_panel"/>
                        {$tr.name}
                    </label>
                </div>
                <div class="panel-body">
                    <volist name="tr['children']" id="child">
                        <div class="child_box mt_10" cat-id="{$child.id}">
                            <div class="content_title2">
                                <label class="rule_label">
                                    <input type="checkbox" class="checkbox checkbox_box"/>
                                    {$child.name}
                                </label>
                            </div>
                            <div class="mt_10 child_content">
                                <volist name="child['children']" id="vo">
                                    <label class="rule_label">
                                        <input text="{$vo.title}" type="checkbox" value="{$vo.id}" class="checkbox_item" />
                                        {$vo.title}
                                    </label>
                                </volist>
                            </div>
                        </div>
                    </volist>
                </div>
            </div>
        </volist>
    </div>
    <div class="clear"></div>
</block>