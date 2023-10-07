<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('template')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">注意：</td>
                    <td style="color:red;">APP名称: {APPANME};  会员姓名: {NAME};  粉丝姓名: {FANSNAME};  时间: {TIME};  商品名称: {GOODNAME}; 订单金额: {ORDERMONEY};  佣金: {COMMISSION}; 订单类型: {ORDERTYPE}; 金额: {MONEY}; 订单号: {ORDER};</td>
                </tr>
                <tr>
                    <td class="field_name">用户自购</td>
                    <td>
                        标题：<input class="base_text" name="buySelf_title" value="{$_info.buySelf_title}"/>
                        <br/>
                        内容：<textarea name="buySelf_content" style="width: 450px;height: 100px;">{$_info.buySelf_content}</textarea>
                        <!--<br/>
                        图片：
                        <input style="width: 309px;" name="buySelf_image" value="" type="text" class="base_text border_left_radius"/>
                        <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="buySelf_image" style=" float: initial;">上传</a>
                        <div imgview="[name=buySelf_image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>-->
                    </td>
                </tr>

                <tr>
                    <td class="field_name">粉丝购买</td>
                    <td>
                        标题：<input class="base_text" name="buyFans_title" value="{$_info.buyFans_title}"/>
                        <br/>
                        内容：<textarea name="buyFans_content" style="width: 450px;height: 100px;">{$_info.buyFans_content}</textarea>
                        <!--<br/>
                        图片：
                        <input style="width: 309px;" name="buyFans_image" value="" type="text" class="base_text border_left_radius"/>
                        <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="buyFans_image" style=" float: initial;">上传</a>
                        <div imgview="[name=buyFans_image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>-->
                    </td>
                </tr>

                <tr>
                    <td class="field_name">粉丝升级</td>
                    <td>
                        标题：<input class="base_text" name="upgrade_title" value="{$_info.upgrade_title}"/>
                        <br/>
                        内容：<textarea name="upgrade_content" style="width: 450px;height: 100px;">{$_info.upgrade_content}</textarea>
                        <!--<br/>
                        图片：
                        <input style="width: 309px;" name="upgrade_image" value="" type="text" class="base_text border_left_radius"/>
                        <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="upgrade_image" style=" float: initial;">上传</a>
                        <div imgview="[name=upgrade_image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>-->
                    </td>
                </tr>

                <tr>
                    <td class="field_name">提现</td>
                    <td>
                        标题：<input class="base_text" name="withdraw_title" value="{$_info.withdraw_title}"/>
                        <br/>
                        内容：<textarea name="withdraw_content" style="width: 450px;height: 100px;">{$_info.withdraw_content}</textarea>
                        <!--<br/>
                        图片：
                        <input style="width: 309px;" name="withdraw_image" value="" type="text" class="base_text border_left_radius"/>
                        <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" uploader="taoke_images" uploader-field="withdraw_image" style=" float: initial;">上传</a>
                        <div imgview="[name=withdraw_image]" style="width: 120px;margin-top: 10px;"><img src="" class="preview"/></div>-->
                    </td>
                </tr>

            </table>

            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

</block>