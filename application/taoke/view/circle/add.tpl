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
                    <td class="field_name">分类</td>
                    <td>
                        <select class="base_select" name="cid" selectedval="{$_info.cid}" id="type">
                            <volist name="cate_list" id="vo">
                                <option value="{$vo.id}">{$vo.name}</option>
                            </volist>
                        </select>
                    </td>
                </tr>

                <tr class="goods">
                    <td class="field_name">类型</td>
                    <td>
                        <select class="base_select" name="type" selectedval="{$_info.type}" id="shop_type">
                            <option value="1">淘宝</option>
                            <option value="2">拼多多</option>
                            <option value="3">京东</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>

                <tr class="text">
                    <td class="field_name">展示图</td>
                    <td>
                        <ul class="json_list exhibition_img"></ul>
                        <input name="images" type="hidden" value="{$_info.images}" required/>
                    </td>
                </tr>

                <notempty name="_info.goods_info">
                    <volist name="_info.goods_info" id="good">
                        <tr class="goods goods_info">
                            <td class="field_name">商品id</td>
                            <td>
                                <input class="base_text" name="goods_id[]" value="{$good.goods_id}" style="width: 172.5px;" placeholder="填入商品id"/>
                                <input type="button" class="base_button" value="获取商品信息" onclick="getDetail(this)">
                                <img style="width: 36px;vertical-align: middle;" src="{$good.image}" class="preview"/>
                                <input type="hidden" name="image[]" value="{$good.image}" class="image"/>
                                <input class="base_text" name="discount_price[]" value="{$good.discount_price}" style="width: 172.5px;"/>
                                <input type="hidden" name="shop_type[]" value="{$good.shop_type}"/>
                                <input type="hidden" name="goods_title[]" value="{$good.title}"/>
                                <input type="hidden" name="price[]" value="{$good.price}"/>
                                <input type="hidden" name="coupon_price[]" value="{$good.coupon_price}"/>
                                <input type="hidden" name="commission_rate[]" value="{$good.commission_rate}"/>
                                <input type="hidden" name="volume[]" value="{$good.volume}"/>
                                <input type="hidden" name="gallery_imgs[]" value="{$good.gallery_imgs}"/>
                                <input type="hidden" name="image[]" value="{$good.image}"/>

                                <span class="add" style="font-size: 24px;vertical-align: middle;display: none;" onclick="add(this)">+</span>
                                <span class="reduce" style="font-size: 24px;vertical-align: middle;" onclick="reduce(this)">-</span>
                            </td>
                        </tr>
                    </volist>
                    <else/>
                    <tr class="goods goods_info">
                        <td class="field_name">商品</td>
                        <td>
                            <input class="base_text" name="goods_id[]" value="" style="width: 172.5px;" placeholder="填入商品id"/>
                            <input type="button" class="base_button" value="获取商品信息" onclick="getDetail(this)">
                            <img style="width: 36px;vertical-align: middle;" src="" class="preview"/>
                            <input type="hidden" name="image[]" value="" class="image"/>
                            <input class="base_text price" name="discount_price[]" value="" style="width: 172.5px;display: none;"/>
                            <input type="hidden" name="shop_type[]" value=""/>
                            <input type="hidden" name="goods_title[]" value=""/>
                            <input type="hidden" name="price[]" value=""/>
                            <input type="hidden" name="coupon_price[]" value=""/>
                            <input type="hidden" name="commission_rate[]" value=""/>
                            <input type="hidden" name="volume[]" value=""/>
                            <input type="hidden" name="gallery_imgs[]" value=""/>
                            <span class="add" style="font-size: 24px;vertical-align: middle;" onclick="add(this)">+</span>
                            <span class="reduce" style="font-size: 24px;vertical-align: middle;display: none;" onclick="reduce(this)">-</span>
                        </td>
                    </tr>
                </notempty>

                <tr>
                    <td class="field_name">作者</td>
                    <td>
                        <input class="base_text" name="author" value="{$_info.author|default='admin'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">作者头像</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="thumb_image" value="{$_info.author_avatar}" type="text" class="base_text border_left_radius"/>
                            <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="author_avatar">上传</a>
                        </div>
                        <div imgview="[name=author_avatar]" style="width: 120px;margin-top: 10px;"><img src="{$_info.author_avatar}" class="preview"/></div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">文案</td>
                    <td>
                        <textarea style="width:600px;height:300px;" name="showwriting" ueditor>{$_info.showwriting}</textarea>
                    </td>
                </tr>

                <tr class="goods">
                    <td class="field_name">评论</td>
                    <td>
                        <textarea style="width:600px;height:200px;" name="comment" ueditor>{$_info.comment}</textarea>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort|default=999}"/>
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

                <input type="hidden" value="" name="ctype" id="ctype"/>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <div class="base_button_div max_w_auto">
                            <a href="javascript:;" class="base_button" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>
        $(function(){
            var id = $("#type").val();
            getType(id);
        });

        $("#type").change(function(){
            var checkValue = $("#type").val();
            getType(checkValue);
        });

        function getType(id) {
            $.ajax({
                url: "{:url('getType')}",
                dataType: "json",
                type: "POST",
                data: {id: id},
                success: function (data) {
                    if(data.status == 0){
                        var ctype = data.data;
                        $("#ctype").val(ctype);
                        if(ctype == 1){
                            $(".text").show();
                            $(".goods").hide();
                            $(".add").hide();
                        }else{
                            $(".text").hide();
                            $(".goods").show();
                            $(".add").hide();
                            if(ctype == 3){
                                $(".add:last").show();
                            }
                        }
                    }else{
                        layer.msg(data.msg);
                    }
                }
            });
        }

        var myJsonList = new JsonList('.json_list', {
            input: '[name=images]',
            btns: ['up', 'down', 'add', 'remove'],
            max: 9,
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

        function add(e){
            $htmlContent = $(e).parent().parent().html();
            var html = "";
            html += "<tr class='goods goods_info'>";
            html += "<td class='field_name'>商品</td>";
            html += "<td>";
            html += "<input class='base_text' name='goods_id[]' value='' style='width: 172.5px;' placeholder='填入商品id'/>";
            html += "<input type='button' class='base_button'' value='获取商品信息' onclick='getDetail(this)'>";
            html += "<img style='width: 36px;vertical-align: middle;'' src='' class='preview'/>";
            html += "<input type='hidden' name='image[]' value='' class='image'/>";
            html += "<input class='base_text price' name='discount_price[]' value='' style='width: 172.5px;display: none;'/>";
            html += "<input type='hidden' name='shop_type[]' value='' style='display: none;'/>";
            html += "<input type='hidden' name='goods_title[]' value='' style='display: none;'/>";
            html += "<input type='hidden' name='price[]' value='' style='display: none;'/>";
            html += "<input type='hidden' name='coupon_price[]' value='' style='display: none;'/>";
            html += "<input type='hidden' name='commission_rate[]' value='' style='display: none;'/>";
            html += "<input type='hidden' name='volume[]' value='' style='display: none;'/>";
            html += "<input type='hidden' name='gallery_imgs[]' value='' style='display: none;'/>";
            html += "<span class='add' style='font-size: 24px;vertical-align: middle;' onclick='add(this)'>+</span>";
            html += "<span class='reduce' style='font-size: 24px;vertical-align: middle;' onclick='reduce(this)'>-</span>";
            html += "</td></tr>";
            $(".goods_info:last").after(html);
            $(e).hide();
            $(e).next(".reduce").show();
        }

        function reduce(e) {
            $(e).parent().parent().remove();
            var len = $(".goods_info").length;
           if(len == 1){
               $(".add").show();
               $(".reduce").hide();
           }else{
               $(".goods_info").eq(len-1).find(".add").show();
           }
        }

        function getDetail(e) {
            var type = $("#shop_type").val();
            var goods_id = $(e).prev().val();
            if(goods_id == ""){
                layer.msg("商品id不能为空");
                return;
            }
            if(type == 1){
                var shop_type = "B";
            }else if(type == 2){
                var shop_type = "P";
            }else if(type == 3){
                var shop_type = "J";
            }
            $.ajax({
                url: "{:url('getItem')}",
                dataType: "json",
                type: "POST",
                data: {shop_type: shop_type, goods_id: goods_id},
                success: function (data) {
                    if(data.status == 0){
                        var detail = data.data;
                        $(e).next().attr("src", detail.img);
                        $(e).next().next().val(detail.img);
                        $(e).next().next().next().val(detail.discount_price);
                        $(e).next().next().next().next().val(detail.shop_type);
                        $(e).next().next().next().next().next().val(detail.title);
                        $(e).next().next().next().next().next().next().val(detail.price);
                        $(e).next().next().next().next().next().next().next().val(detail.coupon_price);
                        $(e).next().next().next().next().next().next().next().next().val(detail.commission_rate);
                        $(e).next().next().next().next().next().next().next().next().next().val(detail.volume);
                        $(e).next().next().next().next().next().next().next().next().next().next().val(detail.gallery_imgs);
                        $(e).next().next().next().show();
                    }else{
                        layer.msg("查询不到商品");
                    }
                }
            });
        }

    </script>
</block>