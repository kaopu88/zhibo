<extend name="public:base_iframe"/>
<block name="css">

</block>

<block name="body">

    <table class="content_list mt_10 find_list">
        <thead>
        <tr>
            <td style="width: 2%;"><input type="checkbox" checkall="list_id"/></td>
            <td style="width: 5%;">ID</td>
            <td style="width: 5%;">排名</td>
            <td style="width: 10%;">主播呢称</td>
            <td style="width: 8%;">积分</td>
            <td style="width: 8%;">入驻次数</td>
            <td style="width: 10%;">最新入驻时间</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="rank">
            <volist name="rank" id="vo">
                <tr class="find_list_li" data-id="{$vo.user_id}">
                    <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                    <td>{$vo.id}</td>
                    <td>{$key+1}</td>
                    <td>
                        <include file="user/user_info"/>
                    </td>
                    <td>
                        {$vo.points}<br/>
                    </td>

                    <td>
                        {$vo.num}<br/>
                    </td>

                    <td>{$vo.create_time|time_format='无','date'}</td>
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
    <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
</block>

<block name="layer">
    <include file="user/remark_pop"/>
</block>