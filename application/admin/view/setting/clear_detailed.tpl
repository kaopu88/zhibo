{:date('Y-m-d H:i:s',time())}
<br/>
<volist name="_list" id="vo">
    <empty name="vo['result']">
        <span style="color: #f00;">删除失败</span>
        <else/>
        <span style="color: green;">删除成功</span>
    </empty>
    &nbsp;&nbsp;
    <eq name="vo['type']" value="file">
        <span>文件：{$vo.path}</span>
        <else/>
        <span style="color: #1483d8;">目录：{$vo.path}</span>
    </eq>
    <br/>
</volist>
清空完成