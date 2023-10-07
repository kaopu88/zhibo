<div class="table_slide">
    <table class="content_list mt_10 md_width">
        <thead>
        <tr>
            <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
            <td style="width: 10%;">ID</td>
            <td style="width: 15%;">新用户</td>
            <td style="width: 10%;">性别</td>
            <td style="width: 10%;">手机号</td>
            <td style="width: 20%;">业绩归属</td>
            <td style="width: 10%;">注册日期</td>
            <td style="width: 15%;">兴趣主播</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="fans_list">
            <volist name="fans_list" id="vo">
                <tr data-id="{$vo.user_id}">
                    <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                    <td>{$vo.id}</td>
                    <td>
                        <div class="thumb">
                            <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                <img src="{:img_url($vo.avatar,'200_200','avatar')}"/>
                                <!-- <div class="thumb_level_box">
                                    <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
                                </div>-->
                            </a>
                            <p class="thumb_info">
                                <a href="javascript:;">
                                    [{$vo.user_id}]<br/>
                                    {$vo.nickname}
                                </a>
                            </p>
                        </div>
                    </td>
                    <td>
                        <switch name="vo['gender']">
                            <case value="0">保密</case>
                            <case value="1"><span class="fc_blue">男</span></case>
                            <case value="2"><span class="fc_magenta">女</span></case>
                        </switch>
                    </td>
                    <td>{$vo.phone|str_hide=3,4}</td>
                    <td>
                        <include file="user/user_agent2"/>
                    </td>
                    <td>{$vo.create_time|time_format}</td>
                    <td></td>
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