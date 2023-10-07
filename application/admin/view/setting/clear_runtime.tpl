<extend name="public:base_nav"/>
<block name="js">
</block>

<block name="css">
    <style>
        .detail_box {
            border: solid 1px #dcdcdc;
            background-color: #F7F7F7;
            width: 600px;
            padding: 10px;
            min-height: 100px;
            margin-top: 10px;
        }

        .energy_balls {
            display: none;
            justify-content: left;
            max-width: 622px;
            flex-wrap: wrap;
            background-color: #f5f5f5;
            margin-top: 10px;
        }

        .energy_ball {
            flex: 0 0 auto;
            width: 120px;
            height: 120px;
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            margin: 15px;
            border: solid 1px orange;
            background-color: #fff;
            cursor: pointer;
        }

        .energy_ball_bg {
            background-color: orange;
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
        }

        .energy_ball_text {
            z-index: 10;
            color: #383838;
            position: absolute;
            width: 100%;
            text-align: center;
            left: 0;
            top: 41px;
        }

        .energy_ball_text .energy_ball_name {
            font-size: 12px;
            line-height: 18px;
        }

        .energy_ball_text .energy_ball_num {
            font-size: 14px;
            line-height: 20px;
            display: block;
        }


    </style>
</block>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="mt_20">
            <div class="panel">
                <div class="panel-heading">
                    文件缓存
                </div>
                <div class="panel-body">
                    <div class="base_button clear_file_btn">立即清空</div>
                    <p style="margin: 10px 0px;">缓存位置：{$RUNTIME_PATH}&nbsp;&nbsp;&nbsp;缓存大小：{$_info.size}&nbsp;&nbsp;&nbsp;文件数量：{$_info.count}&nbsp;&nbsp;&nbsp;
                        文件夹数量：{$_info.dircount}
                    </p>
                    <div class="detail_box">
                        清空详情
                    </div>
                </div>
            </div>

            <div class="panel mt_10">
                <div class="panel-heading">redis缓存</div>
                <div class="panel-body">
                    <div class="clear_redis_box cache_redis">
                        <div>
                            <div class="base_button find_btn">一键检测</div>
                            <div style="margin-left: 10px;" class="base_button clear_btn">一键清空</div>
                        </div>
                        <ul class="energy_balls"></ul>
                    </div>
                </div>
            </div>

            <div class="panel mt_10">
                <div class="panel-heading">用户缓存(非开发人员请勿操作)</div>
                <div class="panel-body">
                    <div class="clear_redis_box user_redis">
                        <div>
                            <div class="base_button find_btn">一键检测</div>
                            <div style="margin-left: 10px;" class="base_button clear_btn">一键清空</div>
                        </div>
                        <ul class="energy_balls"></ul>
                    </div>
                </div>
            </div>

            <div class="panel mt_10" style="display: none">
                <div class="panel-heading">重新生成配置(非开发人员请勿操作)</div>
                <div class="panel-body">
                    <div class="clear_conf_box">
                        <div>
                            <div class="base_button clear_conf_btn">一键生成</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        var start = 0, timer, clearStatus = '', isCheck = false;
        //清空文件缓存
        $('.clear_file_btn').click(function () {
            $('.detail_box').text('正在清空...  已耗时' + start + '秒');
            timer = setInterval(handler, 1000);
            $s.post('{:url("setting/clear_runtime")}', {}, function (result) {
                clearInterval(timer);
                start = 0;
                console.log(result);
                $('.detail_box').html(result.data);
            })
        });

        //重新生成配置
      //  $('.clear_conf_btn').click(function () {
    //        $s.post('{:url("setting/clear_conf")}', {}, function (result) {
    //            console.log(result);
   //             if (result['status'] == 0) {
      //              return $s.success('生成成功');
      //          } else {
        //            return $s.error('操作失败，请稍后');
        //        }
      //      })
     //   });

        function handler() {
            start++;
            $('.detail_box').text('正在清空...  已耗时' + start + '秒');
        }

        function ClearRedis(selector, config) {

            var $selector = $(selector), $findBtn = $selector.find('.find_btn');
            var $clearBtn = $selector.find('.clear_btn'), clearStatus = '', isCheck = false;
            var $balls = $selector.find('.energy_balls');

            $findBtn.click(function () {
                if (clearStatus == 'start') {
                    return $s.error('正在清空，请稍后');
                }
                checkRedis(true);
            });

            $clearBtn.click(function () {
                if (clearStatus == 'start') {
                    return $s.error('正在清空，请稍后');
                }
                clearRedis('');
            });

            $balls.on('click', '.energy_ball', function () {
                var key = $(this).data('key');
                clearRedis(key ? key : '');
            });

            function clearRedis(type) {
                if (clearStatus == '') {
                    return $s.error('请先一键检测');
                }
                if (clearStatus == 'start') {
                    return $s.error('正在清空，请稍后');
                }
                $s.post('{:url("setting/clear_redis_runtime")}', {
                    type: type,
                    prefix: config.prefix
                }, function (result, next) {
                    if (result['status'] == 0) {
                        checkRedis(false);
                    } else {
                        next();
                    }
                });
            }

            function checkRedis(first) {
                if (isCheck) {
                    return false;
                }
                var opts = {};
                if (!first) {
                    opts['loading'] = false;
                }
                isCheck = true;
                $s.post('{:url("setting/find_redis_runtime")}', {prefix: config.prefix}, function (result, next) {
                    isCheck = false;
                    if (result['status'] == 0) {
                        var data = result['data'];
                        clearStatus = data['status'];
                        updateDetails(data['details']);
                        if (clearStatus == 'start') {
                            setTimeout(function () {
                                checkRedis(false);
                            }, 1000);
                        }
                    } else {
                        if (first) {
                            next();
                        }
                    }
                }, opts);
            }

            function updateDetails(details) {
                var keys = [];
                for (var i = 0; i < details.length; i++) {
                    var $jq = $balls.find('li[data-key=' + details[i]['key'] + ']');
                    if ($jq.length <= 0) {
                        $jq = $('<li data-key="' + details[i]['key'] + '" class="energy_ball"><div class="energy_ball_text">' +
                            ' <span class="energy_ball_name"></span><span class="energy_ball_num"></span></div>' +
                            '<div class="energy_ball_bg"></div></li>');
                        $balls.append($jq);
                    }
                    $jq.find('.energy_ball_name').text(details[i]['name']);
                    $jq.data('total', details[i]['total']);
                    $jq.data('cleared', details[i]['cleared']);
                    keys.push(details[i]['key']);
                    updateEnergy($jq);
                }
                var $list = $balls.find('li');
                $list.each(function (index, element) {
                    var key = $(element).data('key');
                    if (keys.indexOf(key) <= -1) {
                        $(element).remove();
                    }
                });
                if ($list.length > 0) {
                    $balls.css({display: 'flex'});
                } else {
                    $balls.css({display: 'none'});
                }
            }

            function updateEnergy($jq) {
                var total = parseInt($jq.data('total'));
                var cleared = parseInt($jq.data('cleared'));
                var num = total > 0 ? (Math.round(((total - cleared) / total) * 100)) : 0;
                num = num > 100 ? 100 : (num < 0 ? 0 : num);
                $jq.find('.energy_ball_bg').css({height: num + '%'});
                var has = total - cleared;
                has = has < 0 ? 0 : has;
                $jq.find('.energy_ball_num').text(has);
            }

        }

        new ClearRedis('.cache_redis', {prefix: 'cache'});
        new ClearRedis('.user_redis', {prefix: 'user'});

    </script>
</block>
