<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('search')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">优惠券</td>
                    <td>
                        <select class="base_select" name="is_coupon" selectedval="{$_info.is_coupon ? '1' : '0'}">
                            <option value="1">有</option>
                            <option value="0">无</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">天猫商品</td>
                    <td>
                        <select class="base_select" name="is_tmall" selectedval="{$_info.is_tmall ? '1' : '0'}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">海淘商品</td>
                    <td>
                        <select class="base_select" name="is_overseas" selectedval="{$_info.is_overseas ? '1' : '0'}">
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">佣金比率范围</td>
                    <td>
                        <input class="base_text" name="tk_rate_start" value="{$_info.tk_rate_start}" style="width: 189px;"/> -
                        <input class="base_text" name="tk_rate_end" value="{$_info.tk_rate_end}" style="width: 188px;"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">价格范围</td>
                    <td>
                        <input class="base_text" name="price_start" value="{$_info.price_start}" style="width: 189px;"/> -
                        <input class="base_text" name="price_end" value="{$_info.price_end}" style="width: 188px;"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">默认排序</td>
                    <td>
                        <select class="base_select" name="sort_type" selectedval="{$_info.sort_type}">
                            <option value="0">默认</option>
                            <option value="1">价格由高到低</option>
                            <option value="2">价格由低到高</option>
                            <option value="3">销量由高到低</option>
                            <option value="4">销量由低到高</option>
                            <option value="5">佣金比率由高到低</option>
                            <option value="6">佣金比率由低到高</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>
</block>