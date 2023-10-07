<extend name="public:base_nav"/>
<block name="css">
</block>
<block name="js">
    <script>
        var myConfig = {
            list: [

            ]
        };
    </script>
</block>

<block name="body">
    <div class="pa_20  p-0">

        <include file="components/tab_nav"/>
        <div class="filter_box mt_10">
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div ajax="post" ajax-url="{:url('export')}" ajax-target="list_id" class="base_button base_button_s base_button_gray">备份数据库</div>
                    <div ajax="post" ajax-url="{:url('optimize')}" ajax-target="list_id" class="base_button base_button_s base_button_gray">优化数据库</div>
                    <div ajax="post" ajax-url="{:url('repair')}" ajax-target="list_id" class="base_button base_button_s base_button_gray">修复数据库</div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10 sm_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 7%;">表名</td>
                <td style="width: 15%;">数据量</td>
                <td style="width: 9%;">大小</td>
                <td style="width: 10%;">冗余</td>
                <td style="width: 13%;">备注</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="data_list">
                <volist name="data_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo['name']}"/></td>
                        <td>{$vo['name']}</td>
                        <td>{$vo['rows']}</td>
                        <td>{$vo['data_length']/1024} kb</td>
                        <td>{$vo['data_free']/1024} kb</td>
                        <td>{$vo['comment']}</td>
                        <td>
                            <a ajax="get" href="{:url('optimize?ids='.$vo['name'])}" class="optimize_font">优化</a>
                            <a ajax="get" href="{:url('repair?ids='.$vo['name'])}" class="repair_font">修复</a>
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
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>