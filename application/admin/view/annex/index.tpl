<extend name="public:base_nav"/>
<block name="css">
    <link rel="stylesheet" type="text/css" href="__CSS__/annex/index.css?v=__RV__"/>
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>
<block name="js">
    <script>
        var myConfig = {
            list: [

            ]
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <a href="{:url('index')}" class="base_button base_button_s base_button_gray" title="首页"><i class="icon-home"></i></a>
                    {$prefix}
                    <div class="clear"></div>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 sm_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 7%;">名称</td>
                <td style="width: 15%;">类型 / 大小</td>
                <td style="width: 9%;">最后修改时间</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <if condition=" !empty($_list.prefixes) ||  !empty($_list.fileList)">
                <volist name="_list.prefixes" id="vo" >
                    <tr>
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo}"/></td>
                        <td><a href="{:url('index',['prefix'=>$key])}">{$vo}</a></td>
                        <td>目录</td>
                        <td>-</td>
                        <td></td>
                    </tr>
                </volist>

                <volist name="_list.fileList" id="v" >
                    <tr>
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$v.key}"/></td>
                        <td>
                            <if condition="$v.is_img == 1">
                                <div class="thumb">
                                    <a rel="thumb" href="{:img_url($_list.base.'/'.$v.key,'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($_list.base.'/'.$v.key,'200_200','thumb')}"/>
                                    </a>
                                </div>
                                <else />
                                <a href="{$_list.base}/{$v.key}" target="_blank">{$v.key|basename}</a>
                            </if>
                        </td>
                        <td>{$v.fsize|format_bytes}</td>
                        <td>{$v.putTime/10000000|intval|time_format}</td>
                        <td>
                            <a ajax="get" href="{:url('delFile',['file'=>urlencode($v.key)])}">删除</a>
                            <a href="{:url('downfile',['file'=>urlencode($v.key),'size'=>$v.fsize])}" target="_blank">下载</a>
                        </td>
                    </tr>
                </volist>
                <else/>
                <tr>
                    <td>
                        <div class="content_empty">
                            <div class="content_empty_icon"></div>
                            <p class="content_empty_text">暂未查询到相关数据</p>
                        </div>
                    </td>
                </tr>
            </if>
            </tbody>
        </table>
        </div>
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>