<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <li><a id="batchUp" ajax-confirm href="javascript:;" class="base_button base_button_s add_btn">提交
                    </a></li>
            </ul>
            <div style="float: right;font-size: 12px;line-height: 30px;" class="fc_orange">总{$robot_num}个记录</div>
        </div>
        <php>
            for ($i=1;$i<=$robot_num;$i+=1){
        </php>
        <div class="panel mt_10 each_panel" id="each_panel_{$i}" data-id="{$i}">
            <div class="panel-heading">ID:{$i}

            </div>
            <div class="panel-body">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">昵称</td>
                    <td>
                        <input class="base_text" name="nickname_{$i}" value=""/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">头像</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="avatar_{$i}" value="" type="text" class="base_text border_left_radius"/>
                            <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="robot_avatar"
                               uploader-field="avatar_{$i}">上传</a>
                        </div>
                        <div imgview="[name=avatar_{$i}]" style="width: 120px;margin-top: 10px;"><img src=""/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">性别</td>
                    <td>
                        <label class="base_label2"><input value="1" checked type="radio" name="gender_{$i}"/>男</label>
                        <label class="base_label2"><input value="2" type="radio" name="gender_{$i}"/>女</label>
                    </td>
                </tr>
                <tr class="each_birthday" data-id="{$i}">
                    <td class="field_name">生日</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="birthday_{$i}"
                               value=""/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">所在地区</td>
                    <td>
                        <input placeholder="请选择地区" data-fill-path="1" data-min-level="3" data-max-num="1"
                               url="{:url('common/get_region')}" region="[name=area_id_{$i}]" type="text" readonly
                               class="base_text area_name_{$i}" value="">
                        <input type="hidden" name="area_id_{$i}" value=""/>
                    </td>
                </tr>
            </table>
            </div>
        </div>
        <php>
            }
        </php>
    </div>
    <script>
        function ListItem($selector)
        {
            var id = $selector.attr('data-id');
            $("[name='birthday_"+id+"']").flatpickr({
                dateFormat: 'Y-m-d',
                enableTime: false,
            });
        }

        $('.each_birthday').each(function (index,element) {
            new ListItem($(element));
        });

        $('#batchUp').click(function(){
            $('.each_panel').each(function (index,element) {
                var that = $(this);
                var index = $(that).attr('data-id');
                var nickname = $("input[name='nickname_"+index+"']").val();
                var avatar = $("input[name='avatar_"+index+"']").val();
                var birthday = $("input[name='birthday_"+index+"']").val();
                var gender = $("input[type='radio'][name='gender_"+index+"']:checked").val();
                var area_id = $("input[name='area_id_"+index+"']").val();

                batchFile = {
                    "nickname":nickname,
                    "avatar":avatar,
                    "birthday":birthday,
                    "gender":gender,
                    "area_id":area_id
                };

                $.ajax({
                    type: "POST",
                    url: "{:url('batch_up')}",
                    dataType: 'JSON',
                    async: false,
                    data: batchFile,
                    success: function (res) {
                        if(res.status==0){
                            $('#each_panel_'+index).remove();
                        }
                    },
                });
            });
            if($(".each_panel").length==0){
                layer.msg('添加成功！即将跳转...');
                window.location.href = "{:url('index')}";
            }
        })
    </script>
</block>