<div dom-key="work_box" popbox="work_box" class="work_box layer_box" title="任务设置"
           popbox-action="{:url('/admin/personal/work')}" popbox-get-data="{:url('/admin/personal/work')}" popbox-area="640px,450px">
    <div class="pa_10">
        <p>我的工作编号：{$admin.id}</p>
        <table class="content_list work_list mt_5">
            <thead>
            <tr>
                <td>工作项</td>
                <td>本月接手数量</td>
                <td>短信提醒</td>
                <td>工作状态</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>