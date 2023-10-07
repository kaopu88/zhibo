<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="../../../bx_static/layui.css"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">标题</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">颜色</td>
                    <td>
                        <input id="test-form-input" class="base_text" name="color" value="{$_info.color}"/>
                        <div id="test-form" class="layui-inline"><div class="layui-unselect layui-colorpicker"><span><span class="layui-colorpicker-trigger-span" lay-type="" style="background: rgb(10, 143, 244);"><i class="layui-icon layui-colorpicker-trigger-i layui-icon-down"></i></span></span></div></div>
                        
                    </td>
                </tr>
                <tr>
                    <td class="field_name">标签类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="1">用户</option>
                            <option value="0">主播</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script src="__NEWSTATIC__/layui.js"></script>
    <script>
        layui.use('colorpicker', function(){
            var colorpicker = layui.colorpicker;
            colorpicker.render({
                elem: '#test-form'
                ,color: '#1c97f5'
                ,done: function(color){
                    var color = color.toUpperCase();
                    $('#test-form-input').val(color);
                }
            });
        });
    </script>
</block>