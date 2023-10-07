<extend name="public:base_iframe"/>
<block name="css">
    <style>
        span {
            display: inline-block;
        }
    </style>
</block>

<block name="body">
    <div class="pa_10">
        <table class="content_info2">
            <notempty name="data">
                <volist name="data" id="vo">
                    <div style="width: 100%;">
                        <span>用户id：{$vo.user_id}</span>
                        <span>昵称：{$vo.user_id}</span>
                        <span>佣率：{$vo.promotion_rate} %</span>
                        <span>佣金：{$vo.money / 10000} 元</span>
                    </div>
                </volist>
                <else/>
                暂无分佣
            </notempty>
        </table>
    </div>
</block>

<block name="layer">
</block>