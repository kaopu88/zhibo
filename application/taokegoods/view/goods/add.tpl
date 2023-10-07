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

                <empty name="_info.id">
                    <tr>
                        <td class="field_name">商品链接</td>
                        <td>
                            <input class="base_text" name="url" value=""/>
                            <a href="javascript:;" class="base_button search-url">一键获取</a>
                        </td>
                    </tr>
                </empty>

                <tr>
                    <td class="field_name">所属类目</td>
                    <td>
                        <select class="base_select" name="cate_id" selectedval="{$_info.cate_id}">
                            <option value="">无</option>
                            <notempty name="cate_list">
                                <volist name="cate_list" id="cate">
                                    <option value="{$cate.cate_id}">{$cate.name}</option>
                                </volist>
                            </notempty>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品id</td>
                    <td>
                        <input class="base_text" name="goods_id" value="{$_info.goods_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}" style="width: 700px;"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品短标题</td>
                    <td>
                        <input class="base_text" name="short_title" value="{$_info.short_title}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品主图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="img" value="{$_info.img}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="img">上传</a>
                        </div>
                        <div imgview="[name=img]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品轮播图</td>
                    <td>
                        <ul class="json_list exhibition_img"></ul>
                        <input name="gallery_imgs" type="hidden" value="{$_info.gallery_imgs}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品描述</td>
                    <td>
                        <input class="base_text" name="desc" value="{$_info.desc}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">平台</td>
                    <td>
                        <span class="platform">{$_info.platform}</span>
                        <input class="base_text" name="shop_type" value="{$_info.shop_type}" type="hidden"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">商品原价</td>
                    <td>
                        <input class="base_text" name="price" value="{$_info.price}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">券后价</td>
                    <td>
                        <input class="base_text" name="discount_price" value="{$_info.discount_price}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">佣金比率</td>
                    <td>
                        <input class="base_text" name="commission_rate" value="{$_info.commission_rate}"/>%
                    </td>
                </tr>

                <tr>
                    <td class="field_name">佣金</td>
                    <td>
                        <input class="base_text" name="commission" value="{$_info.commission}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券面额</td>
                    <td>
                        <input class="base_text" name="coupon_price" value="{$_info.coupon_price}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券使用门槛金额</td>
                    <td>
                        <input class="base_text" name="coupon_condition" value="{$_info.coupon_condition}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券开始时间</td>
                    <td>
                        <input class="base_text" name="coupon_start_time" value="{$_info.coupon_start_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券结束时间</td>
                    <td>
                        <input class="base_text" name="coupon_end_time" value="{$_info.coupon_end_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">销量</td>
                    <td>
                        <input class="base_text" name="volume" value="{$_info.volume}"/> 件
                    </td>
                </tr>

                <tr>
                    <td class="field_name">有无优惠券</td>
                    <td>
                        <select class="base_select" name="has_coupon" selectedval="{$_info.has_coupon}">
                            <option value="1">有</option>
                            <option value="0">无</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券总量</td>
                    <td>
                        <input class="base_text" name="coupon_total" value="{$_info.coupon_total}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券剩余量</td>
                    <td>
                        <input class="base_text" name="coupon_surplus" value="{$_info.coupon_surplus}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠券链接</td>
                    <td>
                        <input class="base_text" name="coupon_url" value="{$_info.coupon_url}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">店铺名称</td>
                    <td>
                        <input class="base_text" name="shop_name" value="{$_info.shop_name}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">店铺id</td>
                    <td>
                        <input class="base_text" name="shop_id" value="{$_info.shop_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">seller_id</td>
                    <td>
                        <input class="base_text" name="seller_id" value="{$_info.seller_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">收藏人数</td>
                    <td>
                        <input class="base_text" name="collect_num" value="{$_info.collect_num|default=0}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">推荐</td>
                    <td>
                        <select class="base_select" name="is_recommand" selectedval="{$_info.is_recommand}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
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
                    <td class="field_name">新品</td>
                    <td>
                        <select class="base_select" name="is_new" selectedval="{$_info.is_new}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort|default=9999}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">发布时间</td>
                    <td>
                        <input readonly placeholder="默认为当前时间" class="base_text" name="create_time" value="{$_info.create_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">启用状态</td>
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
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
        $("[name=create_time]").flatpickr({
            dateFormat: 'Y-m-d H:i:s',
            enableTime: true,
        });

        $("[name=coupon_start_time]").flatpickr({
            dateFormat: 'Y-m-d H:i:s',
            enableTime: true,
        });

        $("[name=coupon_end_time]").flatpickr({
            dateFormat: 'Y-m-d H:i:s',
            enableTime: true,
        });

        var myJsonList = new JsonList('.json_list', {
            input: '[name=gallery_imgs]',
            btns: ['up', 'down', 'add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    name: 'image',
                    title: '图片',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'taoke_images'
                    }
                }
            ]
        });

        $(".search-url").click(function(){
            var url = $("input[name='url']").val();
            $.ajax({
                url: "{:url('getItem')}",
                dataType: "json",
                type: "POST",
                data: {item_url: url},
                success:function(data){
                    if(data.status == 0){
                        var detail = data.data;
                        $("input[name='goods_id']").val(detail.goods_id);
                        $("input[name='title']").val(detail.title);
                        $("input[name='img']").val(detail.img);
                        $(".preview").attr("src", detail.img);
                        if(detail.gallery_imgs){
                            $("input[name='gallery_imgs']").val(detail.gallery_imgs);
                            myJsonList.update();
                        }
                        $("input[name='short_title']").val(detail.short_title);
                        $("input[name='price']").val(detail.price);
                        $("input[name='coupon_price']").val(detail.coupon_price);
                        $("input[name='discount_price']").val(detail.discount_price);
                        $("input[name='desc']").val(detail.desc);
                        $("input[name='shop_type']").val(detail.shop_type);
                        if(detail.shop_type == "C"){
                            $(".platform").text("淘宝");
                        }else if(detail.shop_type == "B" ){
                            $(".platform").text("天猫");
                        }else if(detail.shop_type == "P" ){
                            $(".platform").text("拼多多");
                        }else if(detail.shop_type == "J" ){
                            $(".platform").text("京东");
                        }
                        $("input[name='commission_rate']").val(detail.commission_rate);
                        $("input[name='commission']").val(detail.commission);
                        $("input[name='coupon_condition']").val(detail.coupon_condition);
                        $("input[name='volume']").val(detail.volume);
                        if(detail.coupon_start_time && detail.coupon_start_time != undefined) {
                            var start_time = timestampToTime(detail.coupon_start_time);
                            $("input[name='coupon_start_time']").val(start_time);
                            var end_time = timestampToTime(detail.coupon_end_time);
                            $("input[name='coupon_end_time']").val(end_time);
                        }
                        $("input[name='coupon_total']").val(detail.coupon_total);
                        $("input[name='coupon_surplus']").val(detail.coupon_surplus);
                        $("input[name='coupon_url']").val(detail.coupon_url);
                        $("input[name='shop_name']").val(detail.shop_name);
                        $("input[name='shop_id']").val(detail.shop_id);
                        $("input[name='seller_id']").val(detail.seller_id);
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