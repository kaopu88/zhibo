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
            <h1>活动添加</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('lottery_edit'):url('lottery_add')}">

            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">活动类型</td>
                    <td>
                        <select name="lottery_type" class="base_select" selectedval="{$_info.lottery_type}">
                            <option value="">请选择</option>
                            <volist name="type_list" id="vo">
                                <option value="{$vo.id}">{$vo.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr id="total">
                    <td class="field_name">免费抽奖次数</td>
                    <td>
                        <input class="base_text" name="total" value="{$_info.total}"/> <span style="color: #f00;font-weight: bolder"> *设置新手区每日登陆可免费抽奖次数 其它类型转盘不用设置 设置就是免费抽</span>
                    </td>
                </tr>

                <tr id="total">
                    <td class="field_name">幸运值(抽次数)</td>
                    <td>
                        <input  class="base_text" style="width: 148px;" name="lucky" value="{$_info.lucky}"/>- 幸运颜色 <input  class="base_text" style="width: 148px;" name="lucky_color" value="{$_info.lucky_color}"/> <span style="color: #f00;font-weight: bolder"> *幸运值是累计抽奖到一定次数必中奖品 设置值大于等于抽奖最高次数</span>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">活动名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>


                <tr>
                    <td class="field_name"></td>
                    <td>
                        <ul id="content">
                            <volist name="items" id="vo">
                                <li class="recharge-item" style="padding-top: 10px">
                                    <span class="input-group-addon">抽奖次数&nbsp&nbsp&nbsp</span>
                                    <input style="width: 100px" class="base_text" name="pay_num[]" value="{$vo.pay_num}"/>
                                    <span class="input-group-addon"> &nbsp&nbsp&nbsp需要钻石&nbsp&nbsp</span>
                                    <input style="width: 160px" class="base_text" name="pay_money[]" value="{$vo.pay_money}"/>
                                    <button class="base_button base_button_delete" onclick="removeConsumeItem(this)">删除</button>
                                </li>
                            </volist>
                        </ul>
                    </td>
                </tr>

                <tr>
                    <td class="field_name"></td>
                    <td>
                        <button style="float: left" class='base_button aa' type='button' onclick="addConsumeItem()">设置抽奖次数</button>
                    </td>
                </tr>

                <!--<tr>
                    <td class="field_name">付费抽奖花费</td>
                    <td>
                        <input class="base_text" name="price" value="{$_info.price}"/> 抽奖一次需要花费多少金币
                    </td>
                </tr>-->

                <tr>
                    <td class="field_name">背景图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="admin_images"
                               uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img src=""/></div>
                    </td>
                </tr>

                <!--<tr>
                    <td class="field_name">开始时间</td>
                    <td>
                        <input class="base_text" name="start_time" value="{$_info.start_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">结束时间</td>
                    <td>
                        <input class="base_text" name="end_time" value="{$_info.end_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>-->

                <tr>
                    <td class="field_name">抽奖规则</td>
                    <td>
                        <textarea style="width:900px;height:100px;" name="rules" >{$_info.rules}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">活动说明</td>
                    <td>
                        <textarea style="width:900px;height:100px;" name="description" >{$_info.description}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">活动开关</td>
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
        function addConsumeItem(){
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">抽奖次数&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="pay_num[]" value=""/>';
            html+=' <span class="input-group-addon"> &nbsp&nbsp&nbsp需要钻石&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="pay_money[]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#content').append(html);
        }

        function removeConsumeItem(obj){
            $(obj).closest('.recharge-item').remove();
        }

        $(".base_select").change(function(){
            if($(this).val()==1){
                $("#total").show();
            }else{
                $("#total").hide()
            }
        });

        $("[name=start_time]").flatpickr({
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
        });
        $("[name=end_time]").flatpickr({
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
        });
        new JsonList('.json_list', {
            input: '[name=images]',
            btns: ['up', 'down', 'add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    name: 'img',
                    title: '图片',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'admin_images'
                    }
                }
            ]
        });
    </script>
</block>