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
<div title="短视频编辑" class="layer_box film_audit_update pa_10" dom-key="film_audit_update"
     popbox-action="{:url('video/audit_update')}" popbox-get-data="{:url('video/audit_update')}" popbox-area="700px,550px">
    <table class="content_info2">
        <tr class="edit_tr">
            <td class="field_name">视频描述</td>
            <td>
                <textarea name="describe" style="height: 70px;" class="base_text"></textarea>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">标签列表</td>
            <td>
                <div class="box">
                    
                </div>
                <div>
                    <span class="icon-plus"></span>
                    <input type="hidden" value="" name="tags"/>
                    <input type="hidden" value="" name="tag_names"/>
                    <input type="hidden" value="" name="arr"/>
                </div>
                <script>
                var findTagsUrl = "{:url('film_tags/selector')}";
                var params = {};
                var liData = {};
                var tags = '';
                var tag_names = '';
                var ids  = [];
                var names  = [];
                var selectedList = [];
                var tags = $('[name=tags]').val();
                if (tags && tags != '') {
                    params['selected'] = tags;
                }

                var obj = {
                    type: 2,
                    scrollbar: false,
                    title: '选择标签',
                    shadeClose: true,
                    shade: 0.75,
                    area: ['800px', '600px'],
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
                            ids = $("input[name='tags']").val();
                            if(ids){
                                ids = $("input[name='tags']").val().split(',');
                            }else{
                                ids = [];
                            }
                            names = $("input[name='tag_names']").val();
                            if(names){
                                names = $("input[name='tag_names']").val().split(',');
                            }else{
                                names = [];
                            }
                            ids.push(liData.id);
                            names.push(liData.name);
                            tags = ids.join(',');
                            tag_names = names.join(',');
                            $("input[name='tags']").val(tags);
                            $("input[name='tag_names']").val(tag_names);
                            var str = '<div class="rule_item rule_item_'+liData.id+'" rule-id="'+liData.id+'"><span class="rule_item_name">'+liData.name+'</span><span class="icon-remove" onclick="remove(\''+liData.id+'\')"></span></div>';
                            $('.box').append(str);
                            $("input[name='arr']").val(JSON.stringify(selectedList));
                        });
                        win.WinEve.on('remove', function (eve) {
                            ids = $("input[name='tags']").val();
                            if(ids){
                                ids = $("input[name='tags']").val().split(',');
                            }else{
                                ids = [];
                            }
                            names = $("input[name='tag_names']").val();
                            if(names){
                                names = $("input[name='tag_names']").val().split(',');
                            }else{
                                names = [];
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
                            $("input[name='tags']").val(tags);
                            tag_names = names.join(',');
                            $("input[name='tag_names']").val(tag_names);
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
                    ids = $("input[name='tags']").val();
                    if(ids){
                        ids = $("input[name='tags']").val().split(',');
                    }else{
                        ids = [];
                    }
                    names = $("input[name='tag_names']").val();
                    if(names){
                        names = $("input[name='tag_names']").val().split(',');
                    }else{
                        names = [];
                    }
                    var selectedList = arr;
                    for (var i = 0; i < selectedList.length; i++) {
                        if (selectedList[i]['id'] == id) {
                            selectedList.splice(i, 1);
                            ids.splice(i, 1);
                            names.splice(i, 1);
                            $('.rule_item_'+id).remove();
                            break;
                        }
                    }
                    tags = ids.join(',');
                    $("input[name='tags']").val(tags);
                    tag_names = names.join(',');
                    $("input[name='tag_names']").val(tag_names);
                    $("input[name='arr']").val(JSON.stringify(selectedList));
                }
                
                </script>

            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">版权标识</td>
            <td>
                <label class="base_label2"><input name="copy_right" value="1" type="radio"/>有标识</label>
                <label class="base_label2"><input name="copy_right" value="0" type="radio"/>无标识</label>
                <div class="field_tip">是否有第三方平台的水印LOGO或者包含侵权内容</div>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频评分</td>
            <td>
                <div class="star_box"></div>
                <div>
                    <span class="star_tip">没有评分</span>
                </div>
            </td>
        </tr>

        <tr class="edit_tr">
            <td class="field_name">推荐权重</td>
            <td>
                <input class="base_text" type="number" name="weight" value=""/>
                <div class="field_tip">权重为1到9之间数值</div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value=""/>
                <div class="base_button_div max_w_412">
                    <div data-next="0" class="base_button sub_btn2 mt_10">提交</div>
                </div>
            </td>
        </tr>
    </table>
    <div style="height: 30px"></div>
</div>