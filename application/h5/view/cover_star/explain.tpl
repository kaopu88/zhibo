<include file="public/head" />
<title>{$explain.title}</title>
<link rel="stylesheet" href="__H5__/css/cover_star/common.css?{:date('YmdHis')}">
<script src="__H5__/js/css-base.js?v=__RV__"></script>
</head>
<body>

<div class="page-wrap">
    <div class="container">
        <div class="page-content">
            {:html_entity_decode($explain.content)}
        </div>
    </div>
</div>

</body>
</html>