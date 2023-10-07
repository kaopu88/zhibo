<include file="public/head" />
<title>参加活动</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">
<style type="text/css">

        html{
            background: transparent !important;
        }
        body{
            background: url("__H5__/images/live_act/sweetBattle/bj.png") no-repeat center;
            background-size: 100% 100%;
            position: relative;
        }
        .group-item{
            background: url("__H5__/images/live_act/sweetBattle/z.png") no-repeat center;
            background-size: 100% 100%;
            width: 100%;
            height: 1rem;
            margin: 0.1rem auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .group-item-entry>img{
            width: 100%;
        }
        .body-item{
            left: 0;
            position: absolute;
            top: 36%;
            width: 58%;
            right: 0;
            margin: 0 auto;
        }
        .group-item-name{
            display: flex;
            justify-content: center;
            align-items: center;
            width: 25%;
        }

        .group-item-desc{
            width: 28%;
            text-align: center;
        }

        .group-item-desc p{
            margin: 0;
        }

        .group-item-name span{
            color: #FF205F;
            font-weight: bold;
            font-size: 0.3rem;
        }
        .group-item-entry{
            width: 1.2rem;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 0.1rem;
        }

        .entry-ok{
            filter:grayscale(100%);
            -webkit-filter: grayscale(100%);
            -moz-filter:grayscale(100%);
            -khtml-filter: grayscale(100%);
        }

    </style>
</head>

<body>
    <div class="body-item">
        <volist name="group" id="vo">
            <div class="group-item">
                <div class="group-item-name">
                    <span>{$vo.name}</span>
                </div>
                <div class="group-item-desc">
                    <p style="color:#373737;font-size: 0.3rem">人数</p>
                    <p style="color: #B0B0B0;font-size: 0.25rem"><span class="entry_num">{$vo.entry}</span>/{$vo.limit}</p>
                </div>
                <div class="group-item-entry">
                    <if condition="$is_entry and ($vo.group_key eq $keys)" />
                        <img src="__H5__/images/live_act/sweetBattle/entry_ok.png" alt="">
                    <else />
                        <if condition="($vo.entry lt $vo.limit) and $is_entry neq 1" />
                            <img src="__H5__/images/live_act/sweetBattle/entry.png" onclick="entry({$vo.group_key}, this)" alt="">
                        <elseif condition="$vo.entry egt $vo.limit" />
                            <img src="__H5__/images/live_act/sweetBattle/number_full.png" alt="">
                        <else />
                            <img src="__H5__/images/live_act/sweetBattle/entry.png" class="entry-ok" alt="">
                        </if>
                    </if>
                </div>
            </div>
        </volist>
    </div>
</body>


<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<script>

    var uid = '{$user_id}';

    var entry = function ($group_key, $this) {

        $.ajax({
            type: 'post',
            url: 'entry',
            data: {group_name: $group_key, user_id:uid},
            success: function(data){
                if (data.status != 0)
                {
                    layer.open({
                        content: data.message
                        ,skin: 'msg'
                        ,time: 2 //2秒后自动关闭
                    });
                    return false;
                }
                var $path = $($this).attr('src');
                var nowimg = $path.split('/');
                nowimg.pop();

                $($this).attr('src', nowimg.join('/')+'/'+'entry_ok.png');
                var entry_num = $($this).parents('.group-item').find('.entry_num');
                entry_num.text((entry_num.text()*1)+1);
            }
        })

    };

</script>

</html>