<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>

        <form action="{:url('index')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">商品默认排版</td>
                    <td>
                        <select class="base_select" name="quene_type" selectedval="{$_info.quene_type}">
                            <option value="1">横版</option>
                            <option value="2">竖版</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">优惠头条</td>
                    <td>
                        <select class="base_select" name="headlines[type]" selectedval="{$_info.headlines.type}" id="headlines-type">
                            <option value="1">佣金轮播</option>
                            <option value="2">公告</option>
                            <option value="3">商品</option>
                        </select>
                    </td>
                </tr>

                <tr class="headlines_goods">
                    <td class="field_name">商品类型</td>
                    <td>
                        <select class="base_select " name="headlines[goods_type]" selectedval="{$_info.headlines.goods_type}">
                            <option value="tb">淘宝</option>
                            <option value="pdd">拼多多</option>
                            <option value="jd">京东</option>
                        </select>
                        <input type="text" placeholder="请输入商品id，多个用逗号连接" class="base_text" value="{$_info.headlines.goods_ids}" name="headlines[goods_ids]">
                    </td>
                </tr>

                <tr>
                    <td class="field_name">淘客直播商品</td>
                    <td>
                        <select class="base_select" name="taoke_live" selectedval="{$_info.taoke_live}">
                            <option value="0">关闭</option>
                            <option value="1">开启</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">一键添加商品</td>
                    <td>
                        <select class="base_select" name="add_goods" selectedval="{$_info.add_goods}">
                            <option value="0">关闭</option>
                            <option value="1">开启</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">一键添加商品袋数量限制</td>
                    <td>
                        <input class="base_text" name="goods_num" value="{$_info.goods_num}" style="width: 150px;"/>
                        0为不限制
                    </td>
                </tr>

            </table>

            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
        $(function(){

            var type = "{$_info.headlines.type ? $_info.headlines.type : 1}";
            if(type == 3){
                $(".headlines_goods").show();
            }else{
                $(".headlines_goods").hide();
            }
        });

        $("#headlines-type").change(function(){
            var value = $("#headlines-type").val();
            if(value == 3){
                $(".headlines_goods").show();
            }else{
                $(".headlines_goods").hide();
            }
        });
    </script>
</block>