<extend name="public:base_nav" />
<block name="js">
</block>
<block name="css">
    <style>
        .rule_item {
            display: inline-block;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 0px 5px;
            border-radius: 5px;
            margin: 0 3px 3px 0;
            cursor: pointer;
            font-size: 12px;
            width: 140px;
            text-align: left;
        }

        .rule_item .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
            float: right;
            margin-right: 3px;
            margin-top: 8px;
        }

        .rule_item:hover {
            color: #e60012;
        }
    </style>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('agent_admin/edit'):url('agent_admin/add')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">绑定{:config('app.agent_setting.promoter_name')}账号</td>
                    <td>
                        <div class="box">
                            <notempty name="promoter_arr">
                                <volist name="promoter_arr" id="vo">
                                <div class="rule_item rule_item_{$vo.user_id}" rule-id="{$vo.user_id}">
                                    <span class="rule_item_name">{$vo.user_name}</span>
                                    <span class="icon-remove" onclick="remove('{$vo.user_id}')"></span>
                                </div>
                                </volist>
                            </notempty>
                        </div>
                        <div>
                            <span class="icon-plus"></span>
                            <input type="hidden" value="{$_info.promoter_uid}" name="promoter_uid"/>
                            <input type="hidden" value="{$_info.promoter_arr}" name="arr"/>
                        </div>
                    </td>
                </tr>
                <empty name="_info['id']">
                    <tr>
                        <td class="field_name">用户名</td>
                        <td>
                            <input placeholder="4-30个英文或数字组合" class="base_text" name="username" value="" />
                        </td>
                    </tr>
                    <else/>
                    <tr>
                        <td class="field_name">用户ID</td>
                        <td>{$_info.id}</td>
                    </tr>
                    <tr>
                        <td class="field_name">用户名</td>
                        <td>
                            <input disabled class="base_text" value="{$_info.username}" />
                        </td>
                    </tr>
                </empty>

                <empty name="_info['id']">
                    <tr>
                        <td class="field_name">真实姓名</td>
                        <td>
                            <input class="base_text" name="realname" value="{$_info.realname}" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">创建密码</td>
                        <td>
                            <input type="password" class="base_text" name="password" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">确认密码</td>
                        <td>
                            <input type="password" class="base_text" name="confirm_password" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">手机号</td>
                        <td>
                            <input class="base_text" name="phone" value="{$_info.phone}" />
                        </td>
                    </tr>
                    <else/>
                    <tr>
                        <td class="field_name">真实姓名</td>
                        <td>
                            <input class="base_text"  disabled value="{$_info.realname}" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">手机号</td>
                        <td>
                            <input disabled class="base_text" value="{$_info.phone}" />
                        </td>
                    </tr>
                </empty>
                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" selected="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}" />
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
        var findUidsUrl = "{:url('promoter/find')}";
        var params = {source:'video_user'};
        var liData = {};
        var ids  = [];
        var selectedList = [];
        var promoter_uid = $('[name=promoter_uid]').val();

        if (promoter_uid && promoter_uid != '') {
            params['selected'] = promoter_uid;
        }

        var obj = {
            type: 2,
            scrollbar: false,
            title: '选择{:config('app.agent_setting.promoter_name')}',
            shadeClose: true,
            shade: 0.75,
            area: ['800px', '600px'],
            content: $s.buildUrl(findUidsUrl, params)
        };

        $('.icon-plus').click(function(){
            var arr = $("input[name='arr']").val();

            if(arr){
                arr = JSON.parse(arr);
            }else{
                arr = [];
            }

            layerIframe.open(obj,function(win){
                win['getFillValue']=function(){
                    if(arr){
                        return selectedList.concat(arr);
                    }else{
                        return selectedList;
                    }
                };

                win.WinEve.on('select',function(eve){
                    var selectedList = arr;
                    liData = eve.data;
                    selectedList.push(liData);
                    ids = $("input[name='promoter_uid']").val();
                    if(ids){
                        ids = $("input[name='promoter_uid']").val().split(',');
                    }else{
                        ids = [];
                    }
                    ids.push(liData.user_id);
                    promoter_uid = ids.join(',');
                    $("input[name='promoter_uid']").val(promoter_uid);
                    var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.user_name+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                    $('.box').append(str);
                    $("input[name='arr']").val(JSON.stringify(selectedList));
                });

                win.WinEve.on('remove', function (eve) {
                    ids = $("input[name='promoter_uid']").val();
                    if(ids){
                        ids = $("input[name='promoter_uid']").val().split(',');
                    }else{
                        ids = [];
                    }

                    var selectedList = arr;
                    for (var i = 0; i < selectedList.length; i++) {
                        if (selectedList[i]['user_id'] == eve.data) {
                            selectedList.splice(i, 1);
                            ids.splice(i, 1);
                            $('.rule_item_'+eve.data).remove();
                            break;
                        }
                    }
                    promoter_uid = ids.join(',');
                    $("input[name='promoter_uid']").val(promoter_uid);
                    $("input[name='arr']").val(JSON.stringify(selectedList));
                });
            })
        })

        function remove(id){
            var arr = $("input[name='arr']").val();
            if(arr){
                arr = JSON.parse(arr);
            }else{
                arr = [];
            }
            ids = $("input[name='promoter_uid']").val();
            if(ids){
                ids = $("input[name='promoter_uid']").val().split(',');
            }else{
                ids = [];
            }
            var selectedList = arr;
            for (var i = 0; i < selectedList.length; i++) {
                if (selectedList[i]['user_id'] == id) {
                    selectedList.splice(i, 1);
                    ids.splice(i, 1);
                    $('.rule_item_'+id).remove();
                    break;
                }
            }
            promoter_uid = ids.join(',');
            $("input[name='promoter_uid']").val(promoter_uid);
            $("input[name='arr']").val(JSON.stringify(selectedList));
        }
    </script>
</block>