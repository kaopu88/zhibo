<script>
    var WEB_CONFIG = {
        static_path: '__STATIC__',
        vendor_path: '__VENDOR__',
        'runtime_enviroment': '{:RUNTIME_ENVIROMENT}',
        ueditor_controller: '{:url("admin/ueditor/controller")}',
        get_qiniu_token: '{:url("admin/common/get_qiniu_token")}',//上传文件签名地址
        img_crop_url: '{:url("admin/common/img_crop")}',
        page_var: 'page',
        update_data_version: '{:url("admin/setting/update_data_version")}',
        exchange_bean_quan: '{:url("admin/common/exchange_bean_quan")}',
        select_agents: '{:url("admin/user_transfer/select_agents")}',//选择{:config('app.agent_setting.agent_name')}
        send_sms_url: '{:url("admin/common/send_sms")}',
        user_reg: '{:url("admin/user/reg")}',
        user_role: '{:url("admin/user_roler/setting")}',
        change_work_status: '{:url("admin/personal/change_work_status")}',
        change_work_sms_status: '{:url("admin/personal/change_work_sms_status")}',
        get_unread_num: '{:url("admin/personal/get_unread_num")}',
        get_promoter_cons_trend: '{:url("admin/promoter/get_cons_trend")}',
        get_recharge_consume_trend: '{:url("admin/index/get_recharge_consume_trend")}',
        get_video_info: '{:url("admin/common/get_video_info")}'
    };
    var _domConfig = {};
    var bean_name = '{:APP_BEAN_NAME}';
</script>