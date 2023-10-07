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
<div title="添加守护" class="layer_box add_anchor_guard pa_10" dom-key="add_anchor_guard"
     popbox-action="{:url('anchor/add_guard')}" popbox-get-data="{:url('anchor/add_guard')}" popbox-area="480px,320px">
    <table class="content_info2">
        <tr>
            <td class="field_name">守护列表</td>
            <td>
                <div class="box anchor_box">

                </div>
                <div>
                    <span class="icon-plus icon-plus-guard"></span>
                    <input type="hidden" value="" name="anchor_guard_uids"/>
                    <input type="hidden" value="" name="anchor_guard_arr"/>
                    <input type="hidden" value="" name="anchor_uid"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn">设为守护</div>
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
    var anchor_guard_uids = $('[name=anchor_guard_uids]').val();

    if (anchor_guard_uids && anchor_guard_uids != '') {
        params['selected'] = anchor_guard_uids;
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

    $('.icon-plus-guard').click(function(){
        var anchor_guard_arr = $("input[name='anchor_guard_arr']").val();

        if(anchor_guard_arr){
            anchor_guard_arr = JSON.parse(anchor_guard_arr);
        }else{
            anchor_guard_arr = [];
        }

        layerIframe.open(obj,function(win){
            win['getFillValue']=function(){
                if(anchor_guard_arr){
                    return selectedList.concat(anchor_guard_arr);
                }else{
                    return selectedList;
                }
            };

            win.WinEve.on('select',function(eve){
                var selectedList = anchor_guard_arr;
                liData = eve.data;
                selectedList.push(liData);
                ids = $("input[name='anchor_guard_uids']").val();
                if(ids){
                    ids = $("input[name='anchor_guard_uids']").val().split(',');
                }else{
                    ids = [];
                }
                ids.push(liData.user_id);
                anchor_guard_uids = ids.join(',');
                $("input[name='anchor_guard_uids']").val(anchor_guard_uids);
                var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.nickname+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                $('.anchor_box').append(str);
                $("input[name='anchor_guard_arr']").val(JSON.stringify(selectedList));
            });

            win.WinEve.on('remove', function (eve) {
                ids = $("input[name='anchor_guard_uids']").val();
                if(ids){
                    ids = $("input[name='anchor_guard_uids']").val().split(',');
                }else{
                    ids = [];
                }

                var selectedList = anchor_guard_arr;
                for (var i = 0; i < selectedList.length; i++) {
                    if (selectedList[i]['user_id'] == eve.data) {
                        selectedList.splice(i, 1);
                        ids.splice(i, 1);
                        $('.rule_item_'+eve.data).remove();
                        break;
                    }
                }
                anchor_guard_uids = ids.join(',');
                $("input[name='anchor_guard_uids']").val(anchor_guard_uids);
                $("input[name='anchor_guard_arr']").val(JSON.stringify(selectedList));
            });
        })
    })

    function remove(id){
        var anchor_guard_arr = $("input[name='anchor_guard_arr']").val();
        if(anchor_guard_arr){
            anchor_guard_arr = JSON.parse(anchor_guard_arr);
        }else{
            anchor_guard_arr = [];
        }
        ids = $("input[name='anchor_guard_uids']").val();
        if(ids){
            ids = $("input[name='anchor_guard_uids']").val().split(',');
        }else{
            ids = [];
        }
        var selectedList = anchor_guard_arr;
        for (var i = 0; i < selectedList.length; i++) {
            if (selectedList[i]['user_id'] == id) {
                selectedList.splice(i, 1);
                ids.splice(i, 1);
                $('.rule_item_'+id).remove();
                break;
            }
        }
        anchor_guard_uids = ids.join(',');
        $("input[name='anchor_guard_uids']").val(anchor_guard_uids);
        $("input[name='anchor_guard_arr']").val(JSON.stringify(selectedList));
    }

</script>