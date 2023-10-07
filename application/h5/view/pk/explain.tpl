<include file="public/head" />
<title>{$explain.title}</title>
</head>
<style>
    *{
        background: transparent !important;
        color: #fff;
    }
    p{
        margin: 5px 0;
        font-size: 14px;
    }
    .page-content{
        padding-right: .9rem;
        text-align: justify;
    }
</style>
<body>

<div class="container">
    <div class="page-content">
        {:htmlspecialchars_decode($explain.content)}
    </div>
</div>

</body>
</html>