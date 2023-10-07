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
<div title="添加场控" class="layer_box add_live_manage pa_10" dom-key="add_live_manage"
     popbox-action="{:url('anchor/add_live_manage')}" popbox-get-data="{:url('anchor/add_live_manage')}" popbox-area="480px,320px">
    <table class="content_info2">
        <tr>
            <td class="field_name">场控列表</td>
            <td>
                <div class="box manage_box">

                </div>
                <div>
                    <span class="icon-plus icon-plus-manage"></span>
                    <input type="hidden" value="" name="live_manage_uids"/>
                    <input type="hidden" value="" name="live_manage_arr"/>
                    <input type="hidden" value="" name="anchor_uid"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn">设为场控</div>
            </td>
        </tr>
    </table>
</div>
<script>
    var findUidsUrl = "{:url('user/find')}";
    var liData = {};
    var params = {};
    var ids  = [];
    var selectedList = [];
    var live_manage_uids = $('[name=live_manage_uids]').val();

    if (live_manage_uids && live_manage_uids != '') {
        params['selected'] = live_manage_uids;
    }

    var obj = {
        type: 2,
        scrollbar: false,
        title: '选择用户',
        shadeClose: true,
        shade: 0.75,
        area: ['800px', '600px'],
        content: $s.buildUrl(findUidsUrl, params)
    };

    $('.icon-plus-manage').click(function(){
        var live_manage_arr = $("input[name='live_manage_arr']").val();

        if(live_manage_arr){
            live_manage_arr = JSON.parse(live_manage_arr);
        }else{
            live_manage_arr = [];
        }

        layerIframe.open(obj,function(win){
            win['getFillValue']=function(){
                if(live_manage_arr){
                    return selectedList.concat(live_manage_arr);
                }else{
                    return selectedList;
                }
            };

            win.WinEve.on('select',function(eve){
                var selectedList = live_manage_arr;
                liData = eve.data;
                selectedList.push(liData);
                ids = $("input[name='live_manage_uids']").val();
                if(ids){
                    ids = $("input[name='live_manage_uids']").val().split(',');
                }else{
                    ids = [];
                }
                ids.push(liData.user_id);
                live_manage_uids = ids.join(',');
                $("input[name='live_manage_uids']").val(live_manage_uids);
                var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.nickname+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                $('.manage_box').append(str);
                $("input[name='live_manage_arr']").val(JSON.stringify(selectedList));
            });

            win.WinEve.on('remove', function (eve) {
                ids = $("input[name='live_manage_uids']").val();
                if(ids){
                    ids = $("input[name='live_manage_uids']").val().split(',');
                }else{
                    ids = [];
                }

                var selectedList = live_manage_arr;
                for (var i = 0; i < selectedList.length; i++) {
                    if (selectedList[i]['user_id'] == eve.data) {
                        selectedList.splice(i, 1);
                        ids.splice(i, 1);
                        $('.rule_item_'+eve.data).remove();
                        break;
                    }
                }
                live_manage_uids = ids.join(',');
                $("input[name='live_manage_uids']").val(live_manage_uids);
                $("input[name='live_manage_arr']").val(JSON.stringify(selectedList));
            });
        })
    })

    function remove(id){
        var live_manage_arr = $("input[name='live_manage_arr']").val();
        if(live_manage_arr){
            live_manage_arr = JSON.parse(live_manage_arr);
        }else{
            live_manage_arr = [];
        }
        ids = $("input[name='live_manage_uids']").val();
        if(ids){
            ids = $("input[name='live_manage_uids']").val().split(',');
        }else{
            ids = [];
        }
        var selectedList = live_manage_arr;
        for (var i = 0; i < selectedList.length; i++) {
            if (selectedList[i]['user_id'] == id) {
                selectedList.splice(i, 1);
                ids.splice(i, 1);
                $('.rule_item_'+id).remove();
                break;
            }
        }
        live_manage_uids = ids.join(',');
        $("input[name='live_manage_uids']").val(live_manage_uids);
        $("input[name='live_manage_arr']").val(JSON.stringify(selectedList));
    }

</script>