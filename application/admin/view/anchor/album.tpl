<extend name="public:base_nav"/>
<block name="css">
    <style>
        .artists_list {
            margin-bottom: 15px;
        }

        .artists_list li {
            display: block;
            float: left;
            background-color: #fff;
            border: solid 1px #dcdcdc;
            padding: 10px;
            margin: 15px;
            margin-bottom: 0;
            border-radius: 5px;
            width: 120px;
            box-shadow: 0 0 8px #d0d0d0;
            position: relative;
        }

        .artists_list .list_avatar {
            width: 120px;
            display: block;
        }

        .artists_list .list_avatar img {
            max-width: 100%;
        }

        .artists_list li .list_name {
            display: block;
            font-size: 12px;
            font-weight: normal;
            text-align: center;
            color: #383838;
        }

        .artists_list li .list_btns {
            text-align: center;
        }

        .artists_list li .list_btns a {
            font-size: 12px;
        }

        .artists_list li .list_btns > a:first-child {
            margin-right: 10px;
        }

    </style>
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <notempty name="_list">
            <ul class="artists_list">
                <volist name="_list" id="vo">
                    <li>
                        <a href="javascript:;" class="list_avatar">
                            <img src="{:img_url($vo['image'],'200_200')}"/>
                        </a>
                        <a class="list_name" href="javascript:;"></a>
                        <div style="position: absolute;left: 5px;font-size: 12px;top: 3px;line-height: 20px;color: #777;">
                        </div>
                    </li>
                </volist>
                <div class="clear"></div>
            </ul>
            <else/>
            <div style="margin-top: 100px;" class="content_empty">
                <div class="content_empty_icon"></div>
                <p class="content_empty_text">暂未查询到相关数据</p>
            </div>
        </notempty>
    </div>

</block>