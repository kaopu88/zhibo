<script>
    var WEB_CONFIG = {
        static_path: '__STATIC__',
        vendor_path: '__VENDOR__',
        'runtime_enviroment': '{:RUNTIME_ENVIROMENT}',
        ueditor_controller: '{:url("promoter/ueditor/controller")}',
        get_qiniu_token: '{:url("promoter/common/get_qiniu_token")}',//上传文件签名地址
        img_crop_url: '{:url("promoter/common/img_crop")}',
        page_var: 'page',
        update_data_version: '{:url("promoter/setting/update_data_version")}',
        send_sms_code: '{:url("promoter/common/send_sms_code")}',
        get_summary_data: '{:url("promoter/index/get_summary_data")}',
        get_cons_trend: '{:url("promoter/index/get_cons_trend")}',
        get_promoter_cons_trend: '{:url("promoter/promoter/get_cons_trend")}',
        get_anchor_millet_trend: '{:url("promoter/anchor/get_millet_trend")}',
        promoter_transfer: '{:url("promoter/promoter/transfer")}',
        user_find: '{:url("promoter/user/find")}',
        get_unread_num: '{:url("promoter/user/get_unread_num")}',
        user_role: '{:url("promoter/user_roler/setting")}',
        select_agents: '{:url("promoter/user_transfer/select_agents")}',
    };
    var _domConfig = {};
    var promoter_name = '{:config("app.agent_setting.promoter_name")}';
</script>