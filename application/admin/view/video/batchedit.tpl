<extend name="public:base_nav"/>
<block name="js">
    <script type="text/javascript">
        window._AMapSecurityConfig = {
            securityJsCode:'{:config('app.map_setting.js_service_secret')}',
        }
    </script>

    <script src="__VENDOR__/tencentyun/ugcUploader.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__JS__/video/add.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>
    <script src="__JS__/video/es6-promise.auto.js"></script>
    <script src="__JS__/vue.js"></script>
    <script src="__JS__/axios.js"></script>
    <script src="//unpkg.com/vod-js-sdk-v6"></script>
    <script src="https://webapi.amap.com/maps?v=1.4.8&key={:config('app.map_setting.js_service_key')}"></script>
    <script src="__JS__/location.js?v=__RV__"></script>
    <script src="__VENDOR__/raty/jquery.raty.min.js?v=__RV__"></script>
    <script src="__JS__/video/audit.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/layer/layui/css/layui.css"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <ul class="tab_nav mt_10">
            <include file="components/tab_nav"/>
        </ul>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <li><a id="batchUp" ajax-confirm href="javascript:;" class="base_button base_button_s add_btn">批量提交
                    </a></li>
                <li><a id="batchDel" ajax-confirm href="javascript:;" class="base_button base_button_s base_button_red add_btn">批量清空
                    </a></li>
            </ul>
            <div style="float: right;font-size: 12px;line-height: 30px;" class="fc_orange">已选择{:count($data)}个视频</div>
        </div>
        <div class="panel mt_10">
            <div class="panel-heading">批量信息</div>
            <div class="panel-body">
                <table class="content_info2">
                    <tr>
                        <td class="field_name">发布用户</td>
                        <td>
                            <label class="base_label2"><input value="1" checked type="radio" name="publish_status"/>系统分配</label>
                            <p class="field_tip" style="color:red;">选择系统分配需在用户管理-虚拟创作人 新增虚拟创作人为系统分配的用户</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">所在城市</td>
                        <td>
                            <input placeholder="请选择地区" data-fill-path="1" data-min-level="2" data-max-num="1"
                                   url="{:url('common/get_region')}" region="[name=area_id]" type="text" readonly
                                   class="base_text area_name" value="">
                            <input type="hidden" name="area_id" value=""/>
                        </td>
                    </tr>
                    <tr class="each_map">
                        <td class="field_name">选择位置</td>
                        <td>
                            <div><span class="location_city_str"></span>[<span class="location_lng_str"></span>,<span class="location_lat_str"></span>]</div>
                            <div class="mt_10">
                                <input name="city" type="hidden" value=""/>
                                <input name="location_lat" type="hidden" value=""/>
                                <input name="location_lng" type="hidden" value=""/>
                                <input name="address" type="hidden" value=""/>
                                <div class="base_button base_button_gray open_map">打开地图选择位置</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">审核状态</td>
                        <td>
                            <label class="base_label2"><input value="2" checked type="radio" name="default_audit_status" onclick="no_check_all()"/>免审核</label>
                            <label class="base_label2"><input value="1" type="radio" name="default_audit_status" onclick="need_check_all()"/>需审核</label>
                        </td>
                    </tr>
                    <tr class="copy_right">
                        <td class="field_name">版权标识</td>
                        <td>
                            <label class="base_label2"><input value="0" checked type="radio" name="copy_right"/>无标识</label>
                            <label class="base_label2"><input value="1" type="radio" name="copy_right"/>有标识</label>
                        </td>
                    </tr>
                    <tr class="rating">
                        <td class="field_name">视频评分</td>
                        <td>
                            <div class="star_item">
                                <div class="star_box"></div>
                                <div>
                                    <span class="star_tip">没有评分</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <notempty name="data">
            <volist name="data" id="vo">
                <div class="panel mt_10 each_panel" id="each_panel_{$vo.videoid}" data-video="{$vo.videoid}">
                    <div class="panel-heading">ID:{$vo.videoid}
                        <div style="float: right;line-height: 30px;" class="fc_red"><span class="icon-remove" data-videoid="{$vo.videoid}" data-id="{$key}"></span></div>
                    </div>
                    <div class="panel-body">
                        <table class="content_info2 mt_10">
                            <tr>
                                <td></td>
                                <td>
                                    <video class="video" id="video" muted="true" poster="" loop="loop" autoplay="autoplay"  controls="" style="width:100px; object-fit: fill">
                                        <source type="video/mp4" src="{$vo.videourl}"> 您的浏览器不支持video标签
                                    </video>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_name">视频描述</td>
                                <td>
                                    <input class="base_text" name="describe_{$vo.videoid}" value="{$vo.videoname}"/>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_name">封面图</td>
                                <td>
                                    <div class="base_group">
                                        <input style="width: 309px;" name="cover_url_{$vo.videoid}" value="" type="text" class="base_text border_left_radius"/>
                                        <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                           class="base_button border_right_radius"
                                           uploader="vip_thumb"
                                           uploader-field="cover_url_{$vo.videoid}" style="float: right">上传</a>
                                    </div>
                                    <div imgview="[name=cover_url_{$vo.videoid}]" style="width: 120px;margin-top: 10px;"><img src=""/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_name">所在城市</td>
                                <td>
                                    <input placeholder="请选择地区" data-fill-path="1" data-min-level="2" data-max-num="1"
                                           url="{:url('common/get_region')}" region="[name=area_id_{$vo.videoid}]" type="text" readonly
                                           class="base_text area_name_{$vo.videoid}" value="">
                                    <input type="hidden" name="area_id_{$vo.videoid}" value=""/>
                                </td>
                            </tr>
                            <tr class="each_map">
                                <td class="field_name">选择位置</td>
                                <td>
                                    <div><span class="location_city_str"></span>[<span class="location_lng_str_{$vo.videoid}"></span>,<span class="location_lat_str_{$vo.videoid}"></span>]</div>
                                    <div class="mt_10">
                                        <input name="city_{$vo.videoid}" type="hidden" value=""/>
                                        <input name="location_lat_{$vo.videoid}" type="hidden" value=""/>
                                        <input name="location_lng_{$vo.videoid}" type="hidden" value=""/>
                                        <input name="address_{$vo.videoid}" type="hidden" value=""/>
                                        <div class="base_button base_button_gray open_map" data-videoid="{$vo.videoid}">打开地图选择位置</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_name">审核状态</td>
                                <td>
                                    <label class="base_label2"><input value="2" checked type="radio" name="default_audit_status_{$vo.videoid}" onclick="no_check('{$vo.videoid}')"/>免审核</label>
                                    <label class="base_label2"><input value="1" type="radio" name="default_audit_status_{$vo.videoid}" onclick="need_check('{$vo.videoid}')"/>需审核</label>
                                </td>
                            </tr>
                            <tr class="copy_right_{$vo.videoid}">
                                <td class="field_name">版权标识</td>
                                <td>
                                    <label class="base_label2"><input value="0" checked type="radio" name="copy_right_{$vo.videoid}"/>无标识</label>
                                    <label class="base_label2"><input value="1" type="radio" name="copy_right_{$vo.videoid}"/>有标识</label>
                                </td>
                            </tr>
                            <tr class="rating_{$vo.videoid}">
                                <td class="field_name">视频评分</td>
                                <td>
                                    <div class="star_item" data-videoid="{$vo.videoid}">
                                        <div class="star_box"></div>
                                        <div>
                                            <span class="star_tip">没有评分</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_name"></td>
                                <td>
                                    <div class="base_button submit" data-videoid="{$vo.videoid}" data-url="{$vo.videourl}" data-id="{$key}">提交</div>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
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
        </notempty>
    </div>
    <script>
        new StarBoxAll($('.rating .star_item'));
        function need_check_all(){
            $('.copy_right').hide();
            $('.rating').hide();
            $('.each_panel').each(function (index,element) {
                var that = $(element).find('.submit');
                var videoid = $(that).attr('data-videoid');
                $('.copy_right_'+videoid).hide();
                $('.rating_'+videoid).hide();
                $(":radio[name='default_audit_status_" + videoid + "'][value='1']").prop("checked", "checked");
            })
        }
        function no_check_all(){
            $('.copy_right').show();
            $('.rating').show();
            $('.each_panel').each(function (index,element) {
                var that = $(element).find('.submit');
                var videoid = $(that).attr('data-videoid');
                $('.copy_right_'+videoid).show();
                $('.rating_'+videoid).show();
                $(":radio[name='default_audit_status_" + videoid + "'][value='2']").prop("checked", "checked");
            })
        }
        $("input[type='radio'][name='copy_right']").click(function(){
            var copy_right = $(this).val();
            $('.each_panel').each(function (index,element) {
                var that = $(element).find('.submit');
                var videoid = $(that).attr('data-videoid');
                $(":radio[name='copy_right_" + videoid + "'][value='" + copy_right + "']").prop("checked", "checked");
            })
        })
        function need_check(videoid){
            $('.copy_right_'+videoid).hide();
            $('.rating_'+videoid).hide();
        }
        function no_check(videoid){
            $('.copy_right_'+videoid).show();
            $('.rating_'+videoid).show();
        }
        var myLocation = new MapSelector();

        $('.each_map').each(function (index,element) {
            new ListItem($(element));
        });
        function ListItem($selector){
            $selector.find('.open_map').click(function () {
                var videoId = $(this).attr('data-videoid');
                if (videoId){
                    var area_name = $('.area_name_'+videoId).val().split("-");
                }else{
                    var area_name = $('.area_name').val().split("-");
                }
                var params = {};
                var city = area_name[1] ? area_name[1] : '';
                var lng = '';
                var lat = '';
                if (!isEmpty(lng) && !isEmpty(lat)) {
                    params['lng'] = lng;
                    params['lat'] = lat;
                } else if (!isEmpty(city)) {
                    params['city'] = city;
                }
                myLocation.open(params, function (lnglat) {
                    if (videoId) {
                        $("input[name='location_lng_" + videoId + "']").val(lnglat.lng);
                        $("input[name='location_lat_" + videoId + "']").val(lnglat.lat);
                        $("input[name='city_" + videoId + "']").val(lnglat.city);
                        $("input[name='address_" + videoId + "']").val(lnglat.address);
                        $('.location_lng_str_' + videoId).text(lnglat.lng);
                        $('.location_lat_str_' + videoId).text(lnglat.lat);
                    }else{
                        $("input[name='location_lng']").val(lnglat.lng);
                        $("input[name='location_lat']").val(lnglat.lat);
                        $("input[name='city']").val(lnglat.city);
                        $("input[name='address']").val(lnglat.address);
                        $('.location_lng_str').text(lnglat.lng);
                        $('.location_lat_str').text(lnglat.lat);
                        $('.each_panel').each(function (index,element) {
                            var that = $(element).find('.submit');
                            var videoid = $(that).attr('data-videoid');
                            $("input[name='location_lng_" + videoid + "']").val(lnglat.lng);
                            $("input[name='location_lat_" + videoid + "']").val(lnglat.lat);
                            $("input[name='city_" + videoid + "']").val(lnglat.city);
                            $("input[name='address_" + videoid + "']").val(lnglat.address);
                            $('.location_lng_str_' + videoid).text(lnglat.lng);
                            $('.location_lat_str_' + videoid).text(lnglat.lat);
                        })
                    }
                    $s.post("{:url('findCity')}",{city:lnglat.city},function (res) {
                        if(res.status==0){
                            if (videoId) {
                                $('.area_name_'+videoId).val(res.data.name);
                                $("input[name='area_id_"+videoId+"']").val(res.data.id);
                            }else{
                                $('.area_name').val(res.data.name);
                                $("input[name='area_id']").val(res.data.id);
                                $('.each_panel').each(function (index,element) {
                                    var that = $(element).find('.submit');
                                    var videoid = $(that).attr('data-videoid');
                                    $('.area_name_'+videoid).val(res.data.name);
                                    $("input[name='area_id_"+videoid+"']").val(res.data.id);
                                })
                            }
                        }
                    });
                });
            });
            var mystar=new StarBox($selector.find('.star_item'));
        }

        function StarBox($box){
            var videoId=$box.attr('data-videoid');
            var that=this;
            var hints = ['非常垃圾', '很差劲', '无聊、无意义', '老套、不好看', '一般', '不错哟', '很好看', '有亮点、有创意，顶！', '优质内容、各方面完美，赞！', '极力推荐，膜拜！'];
            $box.find('.star_box').raty({
                halfShow: true,
                half: true,
                score: 0,
                number: 10,
                starHalf: WEB_CONFIG.vendor_path + '/raty/img/star-half-big.png',
                starOn: WEB_CONFIG.vendor_path + '/raty/img/star-on-big.png',
                starOff: WEB_CONFIG.vendor_path + '/raty/img/star-off-big.png',
                size: 24,
                precision: true,
                hints: hints,
                scoreName: 'rating_'+videoId,
                mouseover: function (score, eve) {
                    that.setScore(score, eve);
                },
                mouseout: function (score, eve) {
                    that.setScore(score, eve);
                },
                click: function (score, eve) {
                    that.setScore(score, eve);
                }
            });

            this.setScore= function (score) {
                if (typeof score == 'undefined') {
                    $box.find('.star_tip').html('没有评分');
                } else {
                    var index = parseInt(score);
                    var text = '评分：' + parseFloat(score).toFixed(1) + '&nbsp;&nbsp;' + hints[index];
                    $box.find('.star_tip').html(text);
                }
            };
        }

        function StarBoxAll($box){
            var that=this;
            var hints = ['非常垃圾', '很差劲', '无聊、无意义', '老套、不好看', '一般', '不错哟', '很好看', '有亮点、有创意，顶！', '优质内容、各方面完美，赞！', '极力推荐，膜拜！'];
            $box.find('.star_box').raty({
                halfShow: true,
                half: true,
                score: 0,
                number: 10,
                starHalf: WEB_CONFIG.vendor_path + '/raty/img/star-half-big.png',
                starOn: WEB_CONFIG.vendor_path + '/raty/img/star-on-big.png',
                starOff: WEB_CONFIG.vendor_path + '/raty/img/star-off-big.png',
                size: 24,
                precision: true,
                hints: hints,
                scoreName: 'rating',
                mouseover: function (score, eve) {
                    that.setScore(score, eve);
                },
                mouseout: function (score, eve) {
                    that.setScore(score, eve);
                },
                click: function (score, eve) {
                    that.setScore(score, eve);
                }
            });

            this.setScore= function (score) {
                if (typeof score == 'undefined') {
                    var text = '没有评分';
                    $box.find('.star_tip').html(text);
                } else {
                    var index = parseInt(score);
                    var text = '评分：' + parseFloat(score).toFixed(1) + '&nbsp;&nbsp;' + hints[index];
                    $box.find('.star_tip').html(text);
                }
                $('.each_panel').each(function (index,element) {
                    var that = $(element).find('.submit');
                    var videoid = $(that).attr('data-videoid');
                    $(element).find('.star_tip').html(text);
                    $("input[name='rating_"+videoid+"']").val($("input[name='rating']").val());
                    $(element).find('.star_box').raty('score', $("input[name='rating']").val());
                })
            };
        }

        $('.submit').click(function(){
            var index = $(this).attr('data-videoid');
            var key = $(this).attr('data-id');
            var video_id = index;
            var video_url = $(this).attr('data-url');
            var describe = $("input[name='describe_"+index+"']").val();
            var publish_status = $("input[type='radio'][name='publish_status']:checked").val();
            var cover_url = $("input[name='cover_url_"+index+"']").val();

            var area_id = $("input[name='area_id_"+index+"']").val();
            if(!area_id){
                layer.msg('请选择所在城市');
                return false;
            }
            var location_lng = $("input[name='location_lng_"+index+"']").val();
            var location_lat = $("input[name='location_lat_"+index+"']").val();
            var default_audit_status = $("input[type='radio'][name='default_audit_status_"+index+"']:checked").val();

            batchFile = {
                "video_id":video_id,
                "video_url":video_url,
                "describe":describe,
                "area_id":area_id,
                "location_lng":location_lng,
                "location_lat":location_lat,
                "default_audit_status":default_audit_status,
                "publish_status":publish_status,
                "key":key,
                "cover_url":cover_url
            };

            if (default_audit_status == 2){
                var copy_right = $("input[type='radio'][name='copy_right_"+index+"']:checked").val();
                var rating = $("input[name='rating_"+index+"']").val();
                batchFile['copy_right'] = copy_right;
                batchFile['rating'] = rating;
            }

            $s.post("{:url('upfilmnext')}",batchFile,function (res,next) {
                if(res.status==0){
                    $('#each_panel_'+index).remove();
                }
                next();
            });
        })
        $(".area_name").change(function(){
            var area_id = $("input[name='area_id']").val();
            var area_name = $(this).val();
            $('.each_panel').each(function(i,v){
                var videoid = $(v).attr('data-video');
                $('.area_name_'+videoid).val(area_name);
                $("input[name='area_id_"+videoid+"']").val(area_id);
            })
        })
        $('.icon-remove').click(function(){
            var videoid = $(this).attr('data-videoid');
            var id = $(this).attr('data-id');
            $s.post("{:url('upfilmdel')}",{videoid:videoid,id:id},function (res,next) {
                if(res.status==0){
                    $('#each_panel_'+videoid).remove();
                }
                next();
            });
        })
        $('#batchUp').click(function(){
            $('.each_panel').each(function (index,element) {
                var that = $(element).find('.submit');
                var index = $(that).attr('data-videoid');
                var key = $(that).attr('data-id');
                var video_id = index;
                var video_url = $(that).attr('data-url');
                var describe = $("input[name='describe_"+index+"']").val();
                var publish_status = $("input[type='radio'][name='publish_status']:checked").val();
                var cover_url = $("input[name='cover_url_"+index+"']").val();

                var area_id = $("input[name='area_id_"+index+"']").val();
                if(!area_id){
                    layer.msg('请选择所在城市');
                    return false;
                }
                var location_lng = $("input[name='location_lng_"+index+"']").val();
                var location_lat = $("input[name='location_lat_"+index+"']").val();
                var default_audit_status = $("input[type='radio'][name='default_audit_status_"+index+"']:checked").val();

                batchFile = {
                    "video_id":video_id,
                    "video_url":video_url,
                    "describe":describe,
                    "area_id":area_id,
                    "location_lng":location_lng,
                    "location_lat":location_lat,
                    "default_audit_status":default_audit_status,
                    "publish_status":publish_status,
                    "key":key,
                    "cover_url":cover_url
                };
                if (default_audit_status==2) {
                    var copy_right = $("input[type='radio'][name='copy_right_"+index+"']:checked").val();
                    var rating = $("input[name='rating_"+index+"']").val();
                    batchFile['copy_right'] = copy_right;
                    batchFile['rating'] = rating;
                }

                $.ajax({
                    type: "POST",
                    url: "{:url('upfilmnext')}",
                    dataType: 'JSON',
                    async: false,
                    data: batchFile,
                    success: function (res) {
                        if(res.status==0){
                            $('#each_panel_'+index).remove();
                        }
                    },
                });
            });
            if($(".each_panel").length==0){
                window.location.href=window.location.href;
            }
        })
        $('#batchDel').click(function(){
            $s.post("{:url('upfilmbatchdel')}",{},function (res,next) {
                next();
            });
        })
        $("input[type='radio'][name='publish_status']").click(function(){
            var publish_status = $("input[type='radio'][name='publish_status']:checked").val();
            if(publish_status==0){
                $('.choose_user').show();
            }
            if(publish_status==1){
                $('.choose_user').hide();
            }
        })
    </script>
</block>