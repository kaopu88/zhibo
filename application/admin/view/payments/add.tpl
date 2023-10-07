<extend name="public:base_nav"/>
<block name="js">
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
                    <td class="field_name">类型名称</td>
                    <td>
                        <input class="base_text" name="class_name" value="{$_info.class_name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">名称</td>
                    <td>
                        <input class="base_text" name="name" value="{$_info.name}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">别名</td>
                    <td>
                        <input class="base_text" name="alias" value="{$_info.alias}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="thumb" value="{$_info.thumb}" type="text" class="base_text border_left_radius"/>
                            <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="vip_thumb"
                               uploader-field="thumb">上传</a>
                        </div>
                        <div imgview="[name=thumb]" style="width: 120px;margin-top: 10px;"><img src="{$_info.thumb}"/>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="list_order" value="{$_info.list_order}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                
                  <tr>
                    <td class="field_name">充值套餐</td>
                    <td>
                         <volist name="rechargeList" id="recharg">
                               <label>
                            
                                   <input checkedval="{$_info.coin_type}" style="vertical-align: -1px;" type="checkbox" name="coin_type[]" value="{$recharg.id}" />
                                   &nbsp;{$recharg.bean_num}{$recharg.name}- ¥{$recharg.bean_num}.00&nbsp;
                                   
                               </label>
                           </volist>
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
</block>