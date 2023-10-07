<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('add')}">
                <div class="layui-tab-content">
                    <table class="content_info2 mt_10">
                        <tr>
                            <td class="field_name">目标房间</td>
                            <td>
                                <select class="base_select" name="room_id">
                                    <option value="1">全部</option>
                                    <volist name="liveList" id="vo">
                                        <option value="{$vo.id}">{$vo.id}----{$vo.nickname}</option>
                                    </volist>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">内容</td>
                            <td>
                                <textarea name="content" class="base_text" style="height:120px;"></textarea>
                                <span style="color: #f00">发送的系统消息将显示到所有直播间的聊天公屏区域</span>
                            </td>
                        </tr>

                    </table>
            </div>
            <div class="base_button_div">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>

    </script>
</block>