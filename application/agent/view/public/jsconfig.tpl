<script>
    var WEB_CONFIG = {
        static_path: '__STATIC__',
        vendor_path: '__VENDOR__',
        'runtime_enviroment': '{:RUNTIME_ENVIROMENT}',
        ueditor_controller: '{:url("agent/ueditor/controller")}',
        get_qiniu_token: '{:url("agent/common/get_qiniu_token")}',//上传文件签名地址
        img_crop_url: '{:url("agent/common/img_crop")}',
        page_var: 'page',
        update_data_version: '{:url("agent/setting/update_data_version")}',
        send_sms_code: '{:url("agent/common/send_sms_code")}',
        get_summary_data: '{:url("agent/index/get_summary_data")}',
        get_cons_trend: '{:url("agent/index/get_cons_trend")}',
        get_promoter_cons_trend: '{:url("agent/promoter/get_cons_trend")}',
        get_anchor_millet_trend: '{:url("agent/anchor/get_millet_trend")}',
        promoter_transfer: '{:url("agent/promoter/transfer")}',
        user_find: '{:url("agent/user/find")}',
        get_unread_num: '{:url("agent/user/get_unread_num")}',
        user_role: '{:url("agent/user_roler/setting")}',
        select_agents: '{:url("agent/user_transfer/select_agents")}',
    };
    var _domConfig = {};
    var promoter_name = '{:config("app.agent_setting.promoter_name")}';
</script>