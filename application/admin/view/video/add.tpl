<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/tencentyun/ugcUploader.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart_region/region.js?v=__RV__"></script>
    <script src="__JS__/video/add.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>
    <script src="__JS__/video/es6-promise.auto.js"></script>
    <script src="__JS__/vue.js"></script>
    <script src="__JS__/axios.js"></script>
    <script src="//unpkg.com/vod-js-sdk-v6"></script>
    <script src="https://webapi.amap.com/maps?v=1.4.8&key=0d29625c9a07fbc35067cc31b0b30489"></script>
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
        <form action="{:isset($_info['id'])?url('edit'):url('addpost')}">
            <div class="panel mt_10">
                <div class="panel-heading">视频信息</div>
                <div class="panel-body">
                    <table class="content_info2 mt_10">
                        <tr>
                            <td class="field_name">上传视频</td>
                            <td>
                                <div class="base_group">
                                    <input data-server_key="{$_info.video_id}" readonly style="width: 309px;"
                                           name="video_url"
                                           value="{$_info.video_url}"
                                           type="text"
                                           class="base_text"/>
                                    <a uploader-storer="tencent" uploader-type="video" href="javascript:;"
                                       class="base_button"
                                       uploader="qcloud_erp"
                                       uploader-field="video_url">上传</a>
                                </div>
                                <p class="field_tip">
                                    <a href="javascript:;" class="clear_cookie">清除上传信息缓存</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">视频描述</td>
                            <td>
                                <input class="base_text" name="describe" value="{$_info.describe}"/>
                                <div class="mt_5">
                                    <a class="topic_btn" href="javascript:;">#话题</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">视频封面</td>
                            <td>
                                <div class="base_group">
                                    <input style="width: 309px;" name="cover_url" value="{$_info.cover_url}" type="text" class="base_text"/>
                                    <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                       class="base_button"
                                       uploader="cover"
                                       uploader-field="cover_url">上传</a>
                                </div>
                                <p class="field_tip">默认为视频第一帧</p>
                                <div imgview="[name=cover_url]" style="width: 120px;margin-top: 10px;"><img src=""/>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">发布用户</td>
                            <td>
                                <div class="base_group">
                                    <input placeholder="" suggest-value="[name=user_id]" suggest="{:url('user/get_suggests')}"
                               style="width: 309px;" value="" type="text" class="base_text user_name" disabled>
                                    <input type="hidden" name="user_id" value="">
                                    <a fill-value="[name=user_id]" fill-name=".user_name" layer-open="{:url('user/find')}"
                                   href="javascript:;" class="base_button select_film_btn">选择用户</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">视频标签</td>
                            <td>
                                <div class="base_group">
                                    <input style="width: 309px;" name="tags" value="{$_info.tags}" type="text" class="base_text"/>
                                    <a href="javascript:;" class="base_button">选择标签</a>
                                </div>
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
                        <tr>
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
                                <label class="base_label2"><input value="1" checked type="radio" name="audit_status" />免审核</label>
                                <label class="base_label2"><input value="0" type="radio" name="audit_status" />需审核</label>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">上架状态</td>
                            <td>
                                <select class="base_select" name="status" selectedval="{$_info.status}">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">官方版权</td>
                            <td>
                                <label class="base_label2"><input value="0" checked type="radio" name=""/>无官方版权</label>
                                <label class="base_label2"><input value="1" type="radio" name=""/>有官方版权</label>
                            </td>
                        </tr>
                        <tr>
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

            <div class="mt_10">
                <present name="_info['id']">
                    <input name="id" type="hidden" value="{$_info.id}"/>
                </present>
                __BOUNCE__
                <a ajax-before="ajaxBefore" ajax-after="ajaxAfter" href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>

        </form>
    </div>

    <script>
        var myLocation = new MapSelector();
        new ListItem($('.panel'));
        function ListItem($selector){
            $selector.find('.open_map').click(function () {
                var area_name = $('.area_name').val().split("-");
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
                    $("input[name='location_lng']").val(lnglat.lng);
                    $("input[name='location_lat']").val(lnglat.lat);
                    $("input[name='city']").val(lnglat.city);
                    $("input[name='address']").val(lnglat.address);
                    $('.location_lng_str').text(lnglat.lng);
                    $('.location_lat_str').text(lnglat.lat);
                    $s.post("{:url('findCity')}",{city:lnglat.city},function (res) {
                        if(res.status==0){
                            $('.area_name').val(res.data.name);
                            $("input[name='area_id']").val(res.data.id);
                        }
                    });
                });
            });
            var mystar=new StarBox($selector.find('.star_item'));
        }
        function StarBox($box){
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
                    $box.find('.star_tip').html('没有评分');
                } else {
                    var index = parseInt(score);
                    var text = '评分：' + parseFloat(score).toFixed(1) + '&nbsp;&nbsp;' + hints[index];
                    $box.find('.star_tip').html(text);
                }
            };
        }
    </script>

</block>