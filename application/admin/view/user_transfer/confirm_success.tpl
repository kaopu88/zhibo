<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20"  style="width: 100%">
        <eq name="sync" value="1">
            <h1 class="fc_green" style="font-size: 18px;line-height: 36px;margin: 0;padding: 0;text-align: center">
                转移成功，共计转移了{$total}个用户</h1>
            <else/>
            <h1 class="fc_green" style="font-size: 18px;line-height: 36px;margin: 0;padding: 0;text-align: center">
                转移任务已提交，请稍后~</h1>
        </eq>
        <p style="margin-top: 10px;font-size: 12px;color: #888;text-align: center;">即将自动关闭本页面</p>
    </div>
    <script>
        setTimeout(function () {
            if (typeof _closeSelf == 'function') {
                _closeSelf();
            }
            if (parent) {
                parent.location.reload();
            }
        }, 2000);
    </script>
</block>

<block name="layer">
</block>