<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .field_name {
            width: 100px;
        }
    </style>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:isset($_info['id'])?url('edit'):url('add')}">

            <div class="panel mt_10">
                <div class="panel-heading">基本资料</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <notempty name="_info['id']">
                            <tr>
                                <td class="field_name">LOGO</td>
                                <td>
                                    <a rel="logo" href="{:img_url($_info['logo'],'','logo')}" class="user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($_info['logo'],'200_200','logo')}"/>
                                        <div class="thumb_level_box">
                                            <img title="{$user.level_name}" src="{$user.level_icon}"/>
                                        </div>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_name">身份证正面</td>
                                <td>
                                    <a rel="img_idcardP" href="{:img_url($_info['img_idcardP'],'','avatar')}" class="user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($_info['img_idcardP'],'200_200','avatar')}"/>
                                        <div class="thumb_level_box">
                                            <img title="{$user.level_name}" src="{$user.level_icon}"/>
                                        </div>
                                    </a>

                                </td>
                            </tr>
                            <tr>
                                <td class="field_name">身份证反面</td>
                                <td>
                                    <a rel="img_idcardB" href="{:img_url($_info['img_idcardB'],'','avatar')}" class="user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($_info['img_idcardB'],'200_200','avatar')}"/>
                                        <div class="thumb_level_box">
                                            <img title="{$user.level_name}" src="{$user.level_icon}"/>
                                        </div>
                                    </a>
                                </td>
                            </tr>
                        </notempty>
                        <tr>
                            <td class="field_name">{:config('app.agent_setting.agent_name')}名称</td>
                            <td>
                                <input class="base_text" name="name" value="{$_info.name}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">{:config('app.agent_setting.agent_name')}级别</td>
                            <td>
                                <select name="grade" class="base_select" selectedval="{$_info.grade}">
                                    <option value="">请选择</option>
                                    <volist name=":enum_array('agent_grades')" id="grade">
                                        <option value="{$grade.value}">{$grade.name}</option>
                                    </volist>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">所在地区</td>
                            <td>
                                <input placeholder="请选择地区" data-fill-path="1" data-min-level="3" data-max-num="1"
                                       url="{:url('common/get_region')}" region="[name=area_id]" type="text" readonly
                                       class="base_text area_name" value="{$_info.area_id|region_name}">
                                <input type="hidden" name="area_id" value="{$_info.area_id}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">管理员</td>
                            <td>
                                <auth rules="admin:agent:set_admin">
                                <select name="aid" class="base_select" selectedval="{$_info.aid}">
                                    <option value="">请选择</option>
                                    <volist name="admins" id="admin">
                                        <option value="{$admin.id}">{$admin.username}</option>
                                    </volist>
                                </select>
                                <else/>
                                <select name="aid" class="base_select" selectedval="{$_info.aid}" disabled>
                                    <option value="">请选择</option>
                                    <volist name="admins" id="admin">
                                        <option value="{$admin.id}">{$admin.username}</option>
                                    </volist>
                                </select>
                                </auth>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">后台备注</td>
                            <td>
                                <textarea placeholder="" class="base_textarea" name="remark">{$_info.remark}</textarea>
                                <notempty name="_info['id']">
                                    <div class="mt_10">
                                        <div class="base_button base_button_s base_button_gray save_remark">返回</div>
                                    </div>
                                </notempty>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel mt_10">
                <div class="panel-heading">认证资料</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td class="field_name">主体类型</td>
                            <td>
                                <select class="base_select" name="subject_type" selectedval="{$_info.subject_type}">
                                    <option value="">请选择</option>
                                    <volist name=":enum_array('agent_subject_types')" id="type">
                                        <option value="{$type.value}">{$type.name}</option>
                                    </volist>
                                </select>
                            </td>
                        </tr>
                        <tr class="personal_hide">
                            <td class="field_name">营业执照编号</td>
                            <td>
                                <input class="base_text" name="bus_license" value="{$_info.bus_license}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name personal_legal_name">法人姓名</td>
                            <td>
                                <input class="base_text" name="legal_name" value="{$_info.legal_name}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name personal_legal_id">法人身份证号</td>
                            <td>
                                <input class="base_text" name="legal_id" value="{$_info.legal_id}"/>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <empty name="_info['id']">
            <div class="panel mt_10">
                <div class="panel-heading">联系信息</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td class="field_name">联系人</td>
                            <td>
                                <input class="base_text" name="contact_name" value="{$_info.contact_name}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">联系电话</td>
                            <td>
                                <input class="base_text" name="contact_phone" value="{$_info.contact_phone}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">联系QQ</td>
                            <td>
                                <input class="base_text" name="contact_qq" value="{$_info.contact_qq}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">联系邮箱</td>
                            <td>
                                <input class="base_text" name="contact_email" value="{$_info.contact_email}"/>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            </empty>

            <div class="panel mt_10">
                <div class="panel-heading">高级权限</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td class="field_name">到期时间</td>
                            <td>
                                <input readonly placeholder="请选择到期时间" class="base_text" name="expire_time"
                                       value="{$_info.expire_time|time_format='','Y-m-d'}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">新增二级{:config('app.agent_setting.agent_name')}</td>
                            <td>
                                <select name="add_sec" class="base_select" selectedval="{$_info.add_sec}">
                                    <option value="0">不允许</option>
                                    <option value="1">允许</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">二级{:config('app.agent_setting.agent_name')}限额</td>
                            <td>
                                <input class="base_text" name="max_sec_num" value="{$_info.max_sec_num|default='0'}"/>
                                <p class="field_tip">最多允许新增的二级{:config('app.agent_setting.agent_name')}数量</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">新增{:config('app.agent_setting.promoter_name')}</td>
                            <td>
                                <select name="add_promoter" class="base_select" selectedval="{$_info.add_promoter}">
                                    <option value="1">允许</option>
                                    <option value="0">不允许</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">{:config('app.agent_setting.promoter_name')}限额</td>
                            <td>
                                <input class="base_text" name="max_promoter_num"
                                       value="{$_info.max_promoter_num|default=500}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">新增主播</td>
                            <td>
                                <select name="add_anchor" class="base_select" selectedval="{$_info.add_anchor}">
                                    <option value="1">允许</option>
                                    <option value="0">不允许</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">主播限额</td>
                            <td>
                                <input class="base_text" name="max_anchor_num"
                                       value="{$_info.max_anchor_num|default=500}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">新增虚拟号</td>
                            <td>
                                <select name="add_virtual" class="base_select" selectedval="{$_info.add_virtual}">
                                    <option value="1">允许</option>
                                    <option value="0">不允许</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">虚拟号限额</td>
                            <td>
                                <input class="base_text" name="max_virtual_num"
                                       value="{$_info.max_virtual_num|default=500}"/>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">主播结算方式</td>
                            <td>
                                <select class="base_select" name="cash_type" selectedval="{$_info.cash_type}">
                                    <option value="2">公会结算</option>
                                    <option value="1">平台结算</option>
                                    <option value="0" selected>默认</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">公会结算比例</td>
                            <td>
                                <input class="base_text" name="cash_proportion" value="{$_info.cash_proportion}" placeholder="零或者不填默认平台设置比例"/>
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


                    </table>
                </div>
            </div>
            <div class="mt_10">
                <present name="_info['id']">
                    <input name="id" type="hidden" value="{$_info.id}"/>
                </present>
                __BOUNCE__

            </div>
        </form>
    </div>

    <script>
        function checkSubjectType() {
            var subjectType = $('[name=subject_type] option:selected').val();
            if (!subjectType || subjectType == '') {
                $('.personal_hide').hide();
                $('.personal_legal_name').parents('tr').hide();
                $('.personal_legal_id').parents('tr').hide();
            } else {
                $('.personal_legal_name').parents('tr').show();
                $('.personal_legal_id').parents('tr').show();
                if (subjectType == 'personal') {
                    $('.personal_hide').hide();
                    $('.personal_legal_name').text('个人姓名');
                    $('.personal_legal_id').text('个人身份证号');
                } else if (subjectType == 'company') {
                    $('.personal_hide').show();
                    $('.personal_legal_name').text('法人姓名');
                    $('.personal_legal_id').text('法人身份证号');
                }
            }

        }

        $(function () {
            $('[name=subject_type]').change(function () {
                checkSubjectType();
            });

            checkSubjectType();

            $("[name=expire_time]").flatpickr({
                dateFormat: 'Y-m-d',
                enableTime: false,
                minDate: new Date()
            });

            $('.save_remark').click(function () {
                window.history.go(-1);
            });

        });

        function logoCallback(src) {
            $s.confirm('保存LOGO', '是否确认保存LOGO？', function (close) {
                close();
                $s.post('{:url("agent/save_logo")}', {logo: src, id: '{$_info.id}'}, function (result, next) {
                    if (result['status'] == 0) {
                        $('[name=logo]').changeVal(src);
                    } else {
                        next();
                    }
                });
            });
        }

    </script>

</block>