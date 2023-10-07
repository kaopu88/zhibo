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

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 7%;">备份名称</td>
                <td style="width: 15%;">备份卷数</td>
                <td style="width: 9%;">备份压缩</td>
                <td style="width: 10%;">备份大小</td>
                <td style="width: 13%;">备份时间</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="data_list">
                <volist name="data_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td>{:date('Ymd-His', $vo['time'])}</td>
                        <td>{$vo['part']}</td>
                        <td>{$vo['compress']}</td>
                        <td>{:round($vo['size']/1024, 2)} K</td>
                        <td>{:date('Y-m-d H:i:s', $vo['time'])}</td>
                        <td>
                            <div class="layui-btn-group">
                                <a ajax="get" ajax-confirm="是否确认以此备份恢复数据库？" href="{:url('restore?id='.strtotime($key))}">恢复</a>
                                <a ajax="get" ajax-confirm="是否确认删除此次备份？" href="{:url('del?id='.strtotime($key))}">删除</a>
                            </div>
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
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>