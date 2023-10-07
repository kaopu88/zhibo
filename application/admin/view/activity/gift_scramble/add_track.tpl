
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

<div title="赛道编辑" class="layer_box add_track pa_10" dom-key="add_track"
     popbox-action="{:url('giftScrambleActivity/update')}" popbox-get-data="{:url('giftScrambleActivity/update')}"  popbox-area="550px,500px">

    <table class="content_info2">
        <tr class="edit_tr">
            <td class="field_name">礼物选择:</td>
            <td>
                <div class="box">

                </div>
                <div>
                    <span class="icon-plus"></span>
                    <input type="hidden" value="" name="gift_ids"/>
                    <input type="hidden" value="" name="arr"/>
                </div>

                <script>
                    var findTagsUrl = "{:url('gift/find')}";
                    var params = {};
                    var liData = {};
                    var tags = '';
                    var ids  = [];
                    var names  = [];
                    var selectedList = [];
                    var tags = $('[name=gift_ids]').val();
                    if (tags && tags != '') {
                        params['selected'] = tags;
                    }

                    var obj = {
                        type: 2,
                        scrollbar: false,
                        title: '选择标签',
                        shadeClose: true,
                        shade: 0.75,
                        area: ['1200px', '680px'],
                        content: $s.buildUrl(findTagsUrl, params)
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
                                if (selectedList.length > 4)
                                {
                                    win._closeSelf();
                                    return;
                                }
                                ids = $("input[name='gift_ids']").val();
                                if(ids){
                                    ids = $("input[name='gift_ids']").val().split(',');
                                }else{
                                    ids = [];
                                }

                                ids.push(liData.id);
                                tags = ids.join(',');
                                $("input[name='gift_ids']").val(tags);

                                var str = '<div class="rule_item rule_item_'+liData.id+'" rule-id="'+liData.id+'"><span class="rule_item_name">'+liData.name+'</span><span class="icon-remove" onclick="remove(\''+liData.id+'\')"></span></div>';
                                $('.box').append(str);
                                $("input[name='arr']").val(JSON.stringify(selectedList));
                            });
                            win.WinEve.on('remove', function (eve) {
                                ids = $("input[name='gift_ids']").val();
                                if(ids){
                                    ids = $("input[name='gift_ids']").val().split(',');
                                }else{
                                    ids = [];
                                }

                                var selectedList = arr;
                                for (var i = 0; i < selectedList.length; i++) {
                                    if (selectedList[i]['id'] == eve.data) {
                                        selectedList.splice(i, 1);
                                        ids.splice(i, 1);
                                        names.splice(i, 1);
                                        $('.rule_item_'+eve.data).remove();
                                        break;
                                    }
                                }
                                tags = ids.join(',');
                                $("input[name='gift_ids']").val(tags);
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
                        ids = $("input[name='gift_ids']").val();
                        if(ids){
                            ids = $("input[name='gift_ids']").val().split(',');
                        }else{
                            ids = [];
                        }

                        var selectedList = arr;
                        for (var i = 0; i < selectedList.length; i++) {
                            if (selectedList[i]['id'] == id) {
                                selectedList.splice(i, 1);
                                ids.splice(i, 1);
                                $('.rule_item_'+id).remove();
                                break;
                            }
                        }
                        tags = ids.join(',');
                        $("input[name='gift_ids']").val(tags);
                        $("input[name='arr']").val(JSON.stringify(selectedList));
                    }


                    _domConfig['add_track']={
                        open:function (popupBox, data, next) {

                            var liData = JSON.parse(data.arr);
                            let extra_data = data.extra_data;
                            if (extra_data.type == 'edit')
                            {
                                var str = '';
                                for (var i = 0; i < liData.length; i++) {
                                    str += '<div class="rule_item rule_item_'+liData[i]['id']+'" rule-id="'+liData[i]['id']+'"><span class="rule_item_name">'+liData[i]['name']+'</span><span class="icon-remove" onclick="remove(\''+liData[i]['id']+'\')"></span></div>';
                                }
                                popupBox.find("input[name='period']").val(extra_data.period);
                                popupBox.find('.box').html(str);
                            }

                            popupBox.find('.start_time').text(extra_data.start_time_str);
                            popupBox.find('.end_time').text(extra_data.end_time_str);
                            popupBox.find('.period_str').text(extra_data.period_str);
                            popupBox.find("input[name='type']").val(extra_data.type);

                            next(data);
                        }
                    }




                </script>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">比赛时间:</td>
            <td>
                开始时间：<span class="start_time"></span><br/>
                结束时间：<span class="end_time"></span><br/>
                活动周期：<span class="period_str"></span>
            </td>
        </tr>
        
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="type" value=""/>
                <input type="hidden" name="period" value=""/>
                <div data-next="0" class="base_button sub_btn mt_10">提交</div>
            </td>
        </tr>
    </table>
    <div style="height: 30px"></div>

</div>