<extend name="public:base_nav" />
<block name="css">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="mt_10">
            <h2>同一个浏览器不支持同时打开两个{:config('app.agent_setting.agent_name')}后台，请复制下面的链接用其他浏览器打开，链接有效期5分钟</h2>
            <p class="mt_10">{$url}</p>
        </div>
    </div>
</block>