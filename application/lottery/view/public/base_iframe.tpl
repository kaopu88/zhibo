<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <notempty name="page_app_name">
        <meta name="application-name" content="{$page_app_name}">
    </notempty>
    <notempty name="page_description">
        <meta name="description" content="{$page_description}">
    </notempty>
    <notempty name="page_keywords">
        <meta name="keywords" content="{$page_keywords}">
    </notempty>
    <block name="meta"></block>
    <title>{$page_title}</title>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/icomoon/style.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/webuploader/webuploader.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/flatpickr/flatpickr.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/fancybox/jquery.fancybox.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__STATIC__/smart_admin/css/smart_admin.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__STATIC__/common/css/public.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__CSS__/public.css?v=__RV__"/>
    <block name="css"></block>
    <include file="public:jsconfig"/>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
    <script src="__VENDOR__/jquery.nicescroll.min.js?v=__RV__"></script>
    <script src="__VENDOR__/flatpickr/flatpickr.min.js?v=__RV__"></script>
    <script type="text/javascript" src="__VENDOR__/webuploader/webuploader.js?v=__RV__"></script>
    <script src="__VENDOR__/qiniu.min.js?v=__RV__"></script>
    <script src="__VENDOR__/fancybox/jquery.fancybox.pack.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart.bundle.js?v=__RV__"></script>
    <script src="__STATIC__/smart_admin/js/smart_admin.bundle.js?v=__RV__"></script>
    <script src="__STATIC__/common/js/public.js?v=__RV__"></script>
    <script src="__JS__/public.js?v=__RV__"></script>
    <script>
        if (!hasParentWindow()) {
            window.location = '/';
        }
    </script>
    <block name="js"></block>
</head>
<body>
<block name="body"></block>
<block name="script"></block>
<block name="layer"></block>
</body>
</html>