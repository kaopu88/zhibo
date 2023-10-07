<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/tencentyun/ugcUploader.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <form action="{:isset($_info['id'])?url('edit'):url('add')}">

            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">广告标题</td>
                    <td>
                        <input class="base_text" name="ad_title" value="{$_info.ad_title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">广告链接</td>
                    <td>
                        <input class="base_text" name="ad_link" value="{$_info.ad_link}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">视频封面</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="video_cover" value="{$_info.video_cover}"
                                   type="text"
                                   class="base_text"/>
                            <a uploader-size="524288000" uploader-type="image" href="javascript:;" class="base_button"
                               uploader="live_ad_video_cover"
                               uploader-field="video_cover">上传</a>
                        </div>
                        <div imgview="[name=video_cover]" style="width: 120px;margin-top: 10px;"><img src="{$_info.video_cover}"/>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">上传视频</td>
                    <td>
                        <div class="base_group">
                            <input readonly style="width: 309px;" name="video_url"
                                   value="{$_info.video_url}"
                                   type="text"
                                   class="base_text"/>
                            <a uploader-storer="tencent" uploader-type="video" href="javascript:;"
                               class="base_button"
                               uploader="qcloud_ad"
                               uploader-field="video_url">上传</a>
                        </div>
                        <p class="field_tip">
                            <a class="clear_cookie" href="javascript:;">清除上传信息缓存</a>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">视频时长</td>
                    <td>
                        <div class="base_row">
                            <input style="width: 22%" class="base_text" name="video_duration_h"
                                   value="{$_info.video_duration_h|default=0}"/>&nbsp;&nbsp;小时
                            <input style="width: 22%;margin: 0 10px;" class="base_text" name="video_duration_i"
                                   value="{$_info.video_duration_i|default=0}"/>分钟
                            <input style="width: 22%" class="base_text" name="video_duration_s"
                                   value="{$_info.video_duration_s|default=0}"/>&nbsp;&nbsp;秒
                        </div>
                        <p style="clear: both;" class="field_tip">本地上传视频后会自动解析,<b
                                class="fc_red">必须精确到秒</b></p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">视频宽高</td>
                    <td>
                        <div class="base_row">
                            <input style="width: 45%;float: left;" placeholder="宽度" class="base_text"
                                   name="video_width" value="{$_info.video_width}"/>
                            <input style="width: 45%;float: right;" placeholder="高度" class="base_text"
                                   name="video_height" value="{$_info.video_height}"/>
                        </div>
                        <p style="clear: both;" class="field_tip">本地上传视频后会自动解析，单位：像素</p>
                    </td>
                </tr>

                <tr class="video_info_tr">
                    <td class="field_name">视频ID</td>
                    <td><input placeholder="请先上传视频" readonly class="base_text" name="video_id"/></td>
                </tr>

                <tr class="video_info_tr">
                    <td class="field_name">视频分类</td>
                    <td>
                        <input placeholder="请先上传视频" readonly class="base_text" name="video_class_name"/>
                        <input type="hidden" readonly class="base_text" name="video_class_id"/>
                    </td>
                </tr>
                <tr class="video_info_tr">
                    <td class="field_name">视频大小</td>
                    <td><input placeholder="请先上传视频" readonly class="base_text" name="video_size"/></td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <a ajax-before="ajaxBefore" ajax-after="ajaxAfter" href="javascript:;" class="base_button"
                           ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>

        //从cookie中恢复未提交的视频信息
        var cookieJson = $.cookie('live_film_ad_video_info');
        if (!isEmpty(cookieJson) && cookieJson != 'null') {
            var cookieInfo = JSON.parse(cookieJson);
            $('[name=video_url]').val(cookieInfo.video_url);
            $('[name=video_cover]').val(cookieInfo.video_cover);
            $('[name=video_url]').data('server_key', cookieInfo.video_id);
            getVideoInfo(cookieInfo.video_id);
        }

        $('.clear_cookie').click(function () {
            $.cookie('live_film_ad_video_info', null);
            $s.success('清除成功');
            location.reload();
        });

        $('[name=video_url]').change(function () {
            var video_url = $(this).val();
            var video_id = $(this).data('server_key');
            $.cookie('live_film_ad_video_info', JSON.stringify({
                video_id: video_id,
                video_url: video_url,
                video_cover: $('[name=video_cover]').val()
            }), {expires: 7});
            getVideoInfo(video_id);
        });

        function getVideoInfo(video_id) {
            $s.post(WEB_CONFIG.get_video_info, {video_id: video_id}, function (result, next) {
                if (result.status == 0) {
                    $('[name=video_id]').val(video_id);
                    $('[name=video_class_name]').val(result.data.basicInfo.ClassName);
                    $('[name=video_class_id]').val(result.data.basicInfo.ClassId);
                    $('[name=video_size]').val(result.data.metaData.Size);
                    $('[name=video_duration_s]').val(result.data.metaData.Duration);
                    $('[name=video_width]').val(result.data.metaData.Width);
                    $('[name=video_height]').val(result.data.metaData.Height);
                    $s.success('视频信息已自动解析完成');
                    $('.video_info_tr').show();
                } else {
                    next();
                }
            });
        }

        function ajaxBefore(data, next) {
            var video_url = $('[name=video_url]').val();
            if (!isEmpty(video_url)) {
                var video_id = $('[name=video_id]').val();
                if (isEmpty(video_id)) {
                    $s.error('自动解析尚未完成');
                } else {
                    next();
                }
            } else {
                next();
            }
        }

        function ajaxAfter(data, next) {
            if (data['status'] == 0) {
                $.cookie('live_film_ad_video_info', null);
            }
            next();
        }

    </script>

</block>