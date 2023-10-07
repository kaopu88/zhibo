<ul class="tab_nav">
    <volist name="admin_tree[3]['children']" id="menu4">
        <li><a target="{$menu4.target}" class="{$menu4.current}" href="{$menu4.menu_url}">{$menu4.name}<span unread-types="{$menu4.badge}" class="badge_unread">0</span></a></li>
    </volist>
</ul>

<style>
    .pa_20 > ul{
        position: fixed;
        background: #fff;
        margin-top: -20px;
        z-index: 999;
        width: 100%;
    }
    .pa_20 > form {
        margin-top: 40px;
    }
    .base_label {
        padding: 0;
        border: 0;
        background-color: transparent;
    }
    .base_button{
        float: left;
        margin-left: 445px;
    }

    .base_button_a{
        margin-right: 65.5%;
        float: right;
        margin-left: 0;
    }

</style>
<link rel="stylesheet" href="__VENDOR__/layer/layui/css/layui.css">
<script src="__VENDOR__/layer/layui/layui.js"></script>
<script>
    layui.use(['element', 'layer'], function(){
        var element = layui.element,layer = layui.layer;
    });
</script>