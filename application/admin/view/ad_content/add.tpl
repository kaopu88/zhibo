<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
    <script src="__JS__/ad_content/add.js?v=__RV__"></script>
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
                    <td class="field_name">所属广告位</td>
                    <td>
                        <select name="space_id" class="base_select" selectedval="{$_info.space_id}">
                            <option value="">请选择</option>
                            <volist name="spaces" id="space">
                                <option data-img="{$space.img_config}" data-platform="{$space.platform}"
                                        value="{$space.id}">{$space.name}
                                </option>
                            </volist>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">广告标题</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">广告图片</td>
                    <td>
                        <ul class="json_list img_list ad_img_list"></ul>
                        <input name="image" type="hidden" value="{$_info.image}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">广告视频</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="video" value="{$_info.video}" type="text"
                                   class="base_text border_left_radius"/>
                            <a uploader-size="524288000" uploader-type="video" href="javascript:;" class="base_button border_right_radius"
                               uploader="admin_videos"
                               uploader-field="video">上传</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">广告链接</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="url" value="{$_info.url}" type="text" class="base_text border_left_radius"/>
                            <a href="javascript:;" class="base_button border_right_radius">生成器</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">投放时间</td>
                    <td>
                        <div class="base_row">
                            <input readonly placeholder="开始时间" style="width: 45%;float: left;" name="start_time"
                                   value="{$_info.start_time|time_format='','Y-m-d H:i'}"
                                   type="text" class="base_text"/>
                            <input readonly placeholder="结束时间" style="width: 45%;float: right" name="end_time"
                                   value="{$_info.end_time|time_format='','Y-m-d H:i'}"
                                   type="text" class="base_text"/>
                            <div class="clear"></div>
                            <p class="field_tip">默认为从现在开始到1年后结束</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">投放平台</td>
                    <td>
                        <div class="os_box">请先选择所属广告位</div>
                        <input type="hidden" value="{$_info.os}" class="os_val"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">版本范围</td>
                    <td>
                        <div class="base_row">
                            <input placeholder="开始版本" style="width: 45%;float: left;" name="code_min"
                                   value="{$_info.code_min|default=0}"
                                   type="text" class="base_text"/>
                            <input placeholder="结束版本" style="width: 45%;float: right" name="code_max"
                                   value="{$_info.code_max|default=1000}"
                                   type="text" class="base_text"/>
                            <div class="clear"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">浏览权限</td>
                    <td>
                        <label class="base_label2">
                            <input checkedval="{$_info.purview_login}" name="purview_login" value="" type="radio"
                                   style="margin-right: 5px"/>不限
                        </label>
                        <label class="base_label2">
                            <input checkedval="{$_info.purview_login}" name="purview_login" value="login" type="radio"
                                   style="margin-right: 5px"/>登录可见
                        </label>
                        <label class="base_label2">
                            <input checkedval="{$_info.purview_login}" name="purview_login" value="not_login"
                                   type="radio" style="margin-right: 5px"/>未登录可见
                        </label>
                        <br/>
                        <label class="base_label2">
                            <input checkedval="{$_info.purview_vip}" name="purview_vip" value="" type="radio"
                                   style="margin-right: 5px"/>不限
                        </label>
                        <label class="base_label2">
                            <input checkedval="{$_info.purview_vip}" name="purview_vip" value="vip" type="radio"
                                   style="margin-right: 5px"/>会员可见
                        </label>
                        <label class="base_label2">
                            <input checkedval="{$_info.purview_vip}" name="purview_vip" value="not_vip" type="radio"
                                   style="margin-right: 5px"/>非会员可见
                        </label>
                        <p class="field_tip">默认为全部可见</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">排序权重</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">允许关闭</td>
                    <td>
                        <select class="base_select" name="allow_close" selectedval="{$_info.allow_close}">
                            <option value="1">允许</option>
                            <option value="0">禁止</option>
                        </select>
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

</block>