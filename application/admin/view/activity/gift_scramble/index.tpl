<extend name="public:base_nav"/>
<block name="css">
    <style>
        .left{
            float: left;
        }
    </style>
</block>

<block name="body">

    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="filter_box mt_10">
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left">
                        <auth rules="admin:activity:select">
                            <a class="base_button base_button_s" poplink="add_track" data-id="" href="javascript:;">增加赛道</a>
                        </auth>
                        <auth rules="admin:activity:select">
                            <a class="base_button base_button_s" id="hof" href="javascript:;">名人堂</a>
                        </auth>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="swiper-slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;">活动周期</td>
                    <td style="width: 5%;">赛道ID</td>
                    <td style="width: 12%;">赛道礼物</td>
                    <td style="width: 10%;">赛道积分</td>
                    <td style="width: 7%;">比赛时间</td>
                    <td style="width: 10%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo" key="s">
                            <tr>
                                <td rowspan="4">第{$vo.period}期</td>
                                <td>{$vo['data'][0]['id']}</td>
                                <td>
                                    <div class="left" style="margin-right: 25px;"><img style="vertical-align: middle;" src="{$vo['data'][0]['icon']}?imageView2/1/w/50/h/50" alt=""></div>
                                    <div class="left">
                                        礼物ID：<span>{$vo['data'][0]['gift_id']}</span><br/>
                                        礼物名称：<span>{$vo['data'][0]['name']|short='20'}</span>
                                    </div>
                                </td>
                                <td>
                                    累计积分：{$vo['data'][0]['total_score']|default=0}<br/>
                                    昨日积分：{$vo['data'][0]['yesterday_score']|default=0}
                                </td>
                                <td rowspan="4">
                                    开始时间：{$vo['data'][0]['start_time']|time_format}<br />
                                    结束时间：{$vo['data'][0]['end_time']|time_format}
                                </td>
                                <td rowspan="4">

                                    <if condition="time() < $vo['data'][0]['start_time']">
                                        <auth rules="admin:activity:select">
                                            <a poplink="add_track" data-id="period:{$vo['period']}" href="javascript:;">编辑</a><br/>
                                        </auth>
                                    </if>
                                    <auth rules="admin:activity:select">
                                        <a id="anchor_rank" href="javascript:;">主播排名</a><br/>
                                    </auth>
                                    <auth rules="admin:activity:select">
                                        <a id="consumer_rank" href="javascript:;">用户排名</a>
                                    </auth>
                                </td>
                            </tr>
                            <volist name="vo['data']" id="item" offset="1">
                                <tr>
                                    <td>{$item.id}</td>
                                    <td>
                                        <div class="left" style="margin-right: 25px;"><img style="vertical-align: middle;" src="{$item['icon']}?imageView2/1/w/50/h/50" alt=""></div>
                                        <div class="left">
                                            礼物ID：<span>{$item.gift_id}</span><br/>
                                            礼物名称：<span>{$item.name|short='20'}</span>
                                        </div>
                                    </td>
                                    <td>
                                        累计积分：{$item['total_score']|default=0}<br/>
                                        昨日积分：{$item['yesterday_score']|default=0}
                                    </td>
                                </tr>
                            </volist>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        $('#hof').click(function () {
            layer.open({
                type: 2,
                title: '名人堂',
                shadeClose: true,
                shade: 0.8,
                area: ['50%', '80%'],
                content: "{:url('GiftScrambleActivity/getHof')}"
            });
        });

        $('#anchor_rank').click(function () {
            layer.open({
                type: 2,
                title: '主播排名',
                shadeClose: true,
                shade: 0.8,
                area: ['50%', '80%'],
                content: "{:url('GiftScrambleActivity/getAnchorRank')}"
            });
        });

        $('#consumer_rank').click(function () {
            layer.open({
                type: 2,
                title: '用户排名',
                shadeClose: true,
                shade: 0.8,
                area: ['50%', '80%'],
                content: "{:url('GiftScrambleActivity/getConsumerRank')}"
            });
        });
    </script>

</block>

<block name="layer">
    <include file="activity/gift_scramble/add_track"/>
</block>

