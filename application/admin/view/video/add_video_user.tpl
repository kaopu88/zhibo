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
<div title="添加视频用户" class="layer_box add_video_user pa_10" dom-key="add_video_user"
     popbox-action="{:url('video/add_user')}" popbox-get-data="{:url('video/add_user')}" popbox-area="700px,550px">
    <table class="content_info2">
        <tr>
            <td class="field_name">用户列表</td>
            <td>
                <div class="box">
                    
                </div>
                <div>
                    <span class="icon-plus"></span>
                    <input type="hidden" value="" name="uids"/>
                    <input type="hidden" value="" name="arr"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn">设为视频用户</div>
            </td>
        </tr>
    </table>
</div>
<script>
    var findUidsUrl = "{:url('user/find')}";
    var params = {source:'video_user'};
    var liData = {};
    var ids  = [];
    var selectedList = [];
    var uids = $('[name=uids]').val();
    
    if (uids && uids != '') {
        params['selected'] = uids;
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
                ids = $("input[name='uids']").val();
                if(ids){
                    ids = $("input[name='uids']").val().split(',');
                }else{
                    ids = [];
                }
                ids.push(liData.user_id);
                uids = ids.join(',');
                $("input[name='uids']").val(uids);
                var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.nickname+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                $('.box').append(str);
                $("input[name='arr']").val(JSON.stringify(selectedList));
            });

            win.WinEve.on('remove', function (eve) {
                ids = $("input[name='uids']").val();
                if(ids){
                    ids = $("input[name='uids']").val().split(',');
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
                uids = ids.join(',');
                $("input[name='uids']").val(uids);
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
        ids = $("input[name='uids']").val();
        if(ids){
            ids = $("input[name='uids']").val().split(',');
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
        uids = ids.join(',');
        $("input[name='uids']").val(uids);
        $("input[name='arr']").val(JSON.stringify(selectedList));
    }
    
</script>