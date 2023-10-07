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
                    <td class="field_name">广告id</td>
                    <td>
                        <input class="base_text" name="ads_id" value="{$_info.ads_id}"/>
                        <a href="javascript:;" class="base_button search">一键查询</a>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">广告名称</td>
                    <td>
                        <input class="base_text" name="ads_name" value="{$_info.ads_name}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">分类id</td>
                    <td>
                        <input class="base_text" name="cate_id" value="{$_info.cate_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">分类名称</td>
                    <td>
                        <input class="base_text" name="cate_name" value="{$_info.cate_name}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">下线时间</td>
                    <td>
                        <input class="base_text" name="ads_endtime" value="{$_info.ads_endtime|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">佣金</td>
                    <td>
                        <input class="base_text" name="ads_commission" value="{$_info.ads_commission}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">目标链接</td>
                    <td>
                        <input class="base_text" name="site_url" value="{$_info.site_url}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="site_logo" value="{$_info.site_logo}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="site_logo">上传</a>
                        </div>
                        <div imgview="[name=site_logo]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">说明</td>
                    <td>
                        <textarea style="height: 200px;" class="base_textarea" name="site_description">
                            {$_info.site_description|raw|htmlspecialchars_decode}
                        </textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">广告主</td>
                    <td>
                        <input class="base_text" name="adser" value="{$_info.adser}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">结算周期</td>
                    <td>
                        <input class="base_text" name="charge_period" value="{$_info.charge_period}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">深度链接</td>
                    <td>
                        <input class="base_text" name="deep_link" value="{$_info.deep_link}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">置顶</td>
                    <td>
                        <select class="base_select" name="is_top" selectedval="{$_info.is_top}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">显示</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">是</option>
                            <option value="0">否</option>
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
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>

        $("[name=ads_endtime]").flatpickr({
            dateFormat: 'Y-m-d H:i:s',
            enableTime: true,
        });

        $(".search").click(function(){
            var ads_id = $("input[name='ads_id']").val();
            $.ajax({
                url: "{:url('getAdInfo')}",
                dataType: "json",
                type: "POST",
                data: {ads_id: ads_id},
                success:function(data){
                    if(data.status == 0){
                        var detail = data.data;
                        $("input[name='ads_name']").val(detail.title);
                        $("input[name='cate_name']").val(detail.cate_name);
                        var etime = timestampToTime(detail.etime);
                        $("input[name='ads_endtime']").val(etime);
                        $("input[name='site_description']").val(detail.info);
                        $("input[name='site_url']").val(detail.url);
                        $("input[name='site_logo']").val(detail.logo);
                        $("input[name='charge_period']").val(detail.order_return);
                        $("input[name='ads_commission']").val(detail.rate_sites);
                    }else{
                        layer.msg(data.message);
                    }
                }
            });
        });

        function timestampToTime(timestamp) {
            var date = new Date(timestamp*1000);
            var Y = date.getFullYear() + '-';
            var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
            var D = (date.getDate() < 10 ? '0'+date.getDate() : date.getDate()) + ' ';
            var h = (date.getHours() < 10 ? '0'+date.getHours() : date.getHours()) + ':';
            var m = (date.getMinutes() < 10 ? '0'+date.getMinutes() : date.getMinutes()) + ':';
            var s = (date.getSeconds() < 10 ? '0'+date.getSeconds() : date.getSeconds());
            strDate = Y+M+D+h+m+s;
            return strDate;
        }
    </script>
</block>