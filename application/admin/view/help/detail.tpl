<extend name="public:base_nav2"/>
<block name="css">
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="__ADMIN__/css/help/index.css?v=__RV__"/>
    <style>
        .act_btns {
            position: absolute;
            right: 0;
            top: 0;
        }

        .act_btns a {
            font-size: 14px;
            display: inline-block;
        }

        .media-body {
            font-size: 14px;
            line-height: 26px;
            color: #555;
        }

        .media-body img {
            max-width: 100%;
        }

        .art_main {
            width: 1100px;
            margin: 0 auto;
        }
    </style>
</block>

<block name="js">
</block>

<block name="body">
    <ol class="breadcrumb">
        <li><a href="{:url('help/index')}">文档首页</a></li>
        <li><a href="#">{$pcat_name['name']}</a></li>
        <li><a href="{:url('more',['pcat_id'=>$_info['pcat_id'],'cat_id'=>$_info['cat_id']])}">{$cat_name['name']}</a></li>
    </ol>
    <div class="art_main">
        <div class="page-header" style="position: relative;">
            <h1 style="font-size: 18px;display: block;text-align: center;">{$_info['title']}<br/>
                <small style="margin-top: 10px;display: inline-block">{$_info['create_time']|time_format}</small>
            </h1>

            <div class="act_btns">
                <auth rules="admin:help:update">
                    <a href="{:url('edit',['id'=>$_info['id']])}?__JUMP__">
                        <span class="glyphicon glyphicon-edit"></span>
                    </a>
                </auth>
                <auth rules="admin:help:delete">
                    <a title="删除文档" style="margin-left: 10px;" class="fc_red" ajax-confirm="是否确认删除本文档？" ajax="get" href="{:url('delete',['id'=>$_info['id']])}">
                        <span class="glyphicon glyphicon-trash"></span>
                    </a>
                </auth>
            </div>
        </div>
        <div class="media">
            <div class="media-body">
                {:htmlspecialchars_decode($_info['content']);}
            </div>
        </div>
    </div>
    <div style="height: 50px"></div>
</block>
<block name="layer">
</block>