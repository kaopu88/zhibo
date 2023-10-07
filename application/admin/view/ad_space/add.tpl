<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
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
                    <td class="field_name">名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">标识符</td>
                    <td>
                        <input class="base_text" name="mark" value="{$_info.mark}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="">请选择</option>
                            <volist name=":enum_array('ad_space_types')" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">最大条数</td>
                    <td>
                        <input class="base_text" name="length" value="{$_info.length|default=1}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">适配平台</td>
                    <td>
                        <ul class="json_list platform_list"></ul>
                        <input name="platform" type="hidden" value="{$_info.platform}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">图片配置</td>
                    <td>
                        <ul class="json_list img_list"></ul>
                        <input name="img_config" type="hidden" value="{$_info.img_config}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">广告配置</td>
                    <td>
                        <textarea placeholder="JSON格式" class="base_textarea" name="config">{$_info.config}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">启用状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
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
    <script>
        var platformOptions = JSON.parse('{:json_encode(config("enum.ad_os"))}');
        platformOptions.unshift({name: '请选择', value: ''});
        new JsonList('.platform_list', {
            input: '[name=platform]',
            btns: ['add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    type: 'select',
                    options: platformOptions,
                    width: 150
                }
            ]
        });


        new JsonList('.img_list', {
            input: '[name=img_config]',
            btns: ['add', 'remove'],
            max: 5,
            fields: [
                {
                    title: '版本名称',
                    name: 'name',
                    type: 'text',
                    width: 100
                },
                {
                    title: '最佳宽度',
                    name: 'width',
                    type: 'text',
                    width: 100
                },
                {
                    title: '最佳高度',
                    name: 'height',
                    type: 'text',
                    width: 100
                },
                {
                    type: 'select',
                    name: 'crop',
                    width: 100,
                    options: [
                        {name: '关闭裁切', value: '0'},
                        {name: '开启裁切', value: '1'}
                    ]
                }
            ]
        });

    </script>
</block>