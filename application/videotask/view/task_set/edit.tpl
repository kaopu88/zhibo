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
                    <td class="field_name">任务类型</td>
                    <td>
                        <select class="base_select" name="mission_type" selectedval="{$_info.mission_type}">
                            <option value="">请选择</option>
                            <volist name="_erarry" id="type">
                                <option value="{$type.value}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">任务名称</td>
                    <td>
                        <ul >
                            <input class="base_text" name="title" value="{$_info.title}"/>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">任务标识</td>
                    <td>
                        <ul >
                            <input class="base_text" name="task_type" value="{$_info.task_type}" readonly/>
                        </ul>
                    </td>
                </tr>
                <tr>

                    <td class="field_name">任务类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}">
                            <volist name="resttype" id="type">
                                <option value="{$type.id}">{$type.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">奖励</td>
                    <td>
                        <label class="base_label2"><input value="1" type="radio" name="reward_type" {$_info.reward_type==1? 'checked' : ''}/>积分</label>
                        <label class="base_label2"><input value="2" type="radio" name="reward_type" {$_info.reward_type==2? 'checked' : ''}/>金币</label>

                    </td>
                </tr>


                <tr>
                    <td class="field_name">完成数量</td>
                    <td>
                        <ul id="content">

                            <li class="recharge-item" style="padding-top: 10px">
                                <input style="width: 500px" class="base_text" name="finish" value="{$finish}"/>  ( 多重递进奖励请用','分割)
                            </li>

                        </ul>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">任务奖励</td>
                    <td>
                        <ul id="content">

                            <li class="recharge-item" style="padding-top: 10px">
                                <input style="width: 500px" class="base_text" name="reward" value="{$reward}"/>  ( 多重递进奖励请用','分割)

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


    </script>

</block>