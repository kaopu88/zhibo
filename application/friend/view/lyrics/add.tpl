<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
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
                    <td class="field_name">文字类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <option value="">请选择</option>
                            <volist name="_erarry" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">标题</td>
                    <td>
                        <ul >
                        <input class="base_text" name="title" value="{$_info.title}"/>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">作者</td>
                    <td>
                        <select class="base_select" name="author_id" selectedval="{$_info.author_id}">
                            <option value="">请选择</option>
                            <volist name="restAuthor" id="type">
                                <option value="{$type.id}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>




                <tr>
                    <td class="field_name">歌词内容</td>
                    <td>
                        <button class='base_button aa' type='button' onclick="addConsumeItem()">添加一段歌词</button>
                        <ul id="content">
                            <li class="recharge-item" style="padding-top: 10px">
                            <input style="width: 500px" class="base_text" name="lyrics[]" value=""/>
                            </li>
                        </ul>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="release_time"
                               value="{$_info.create_time|time_format='','Y-m-d H:i'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">状态</td>
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
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>

        function addConsumeItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';

            html+='<input style="width: 500px" class="base_text" name="lyrics[]" value=""/>';

            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#content').append(html);
        }

        function removeConsumeItem(obj){
            $(obj).closest('.recharge-item').remove();
        }


        $("[name=release_time]").flatpickr({
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
        });

        new JsonList('.json_list', {
            input: '[name=images]',
            btns: ['add', 'remove'],
            max: 10,
            format: 'separate',
            fields: [
                {
                    name: 'img',
                    title: '歌词内容',
                    type: 'file',
                    width: 250,
                }
            ]
        });
        $(function(){
            // 添加选项
            $("#opbtn").click(function(){
                if($("#opts>li").size() < 6){
				$("#opts").append("<li><input /></li>");
			}else{
                    // 提示选项个数已经达到最大
				$("#optips").html("选项个数已经达到最大,不能再添加!");
				$("#optips").css({"color":"red"});
        }

        });

        // 删除选项
        $("#delbtn").click(function(){
            if($("#opts>li").size() <= 0){
                $("#optips").html("已经没有选项可以删除了!");
                $("#optips").css({"color":"red"});
            } else{
                // 删除选项,每次删除最后一个
                $("#opts>li").last().remove();
            }

        });
        });

    </script>

</block>