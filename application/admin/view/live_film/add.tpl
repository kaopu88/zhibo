<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/tencentyun/ugcUploader.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
    <script>
        var collectingUrl = '{:url("collecting")}';
    </script>
    <script src="__JS__/live_film/add.js?v=__RV__"></script>
</block>

<block name="css">
    <style>
        .collecting_box {
            display: none;
        }

        .video_info_tr,.video_info2_tr {
            display: none;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <form action="{:isset($_info['id'])?url('edit'):url('add')}">
            <div class="panel mt_10">
                <div class="panel-heading">视频信息</div>
                <div class="panel-body">
                    <table class="content_info2 mt_10">
                        <tr>
                            <td class="field_name">视频标题</td>
                            <td>
                                <input class="base_text" name="video_title" value="{$_info.video_title}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">视频封面</td>
                            <td>
                                <div class="base_group">
                                    <input style="width: 309px;" name="video_cover" value="{$_info.video_cover}"
                                           type="text"
                                           class="base_text"/>
                                    <a uploader-size="2147483648" uploader-type="image" href="javascript:;" class="base_button"
                                       uploader="live_film_cover"
                                       uploader-field="video_cover">上传</a>
                                </div>
                                <div imgview="[name=video_cover]" style="width: 120px;margin-top: 10px;"><img src=""/>
                                </div>
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
                                <p style="clear: both;" class="field_tip">本地上传视频后会自动解析，但是网络采集的需要手动填写，<b class="fc_red">必须精确到秒</b></p>
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
                                <p style="clear: both;" class="field_tip">
                                    本地上传视频后会自动解析，但是网络采集的需要手动填写，单位：像素<br/>
                                    常见尺寸：
                                    <a input-wh="720*480" href="javascript:;">MP4标清480P(720*480)</a>&nbsp;
                                    <a input-wh="800*480" href="javascript:;">MP4高清(800*480)</a>&nbsp;
                                    <a input-wh="1280*720" href="javascript:;">高清720P(1280*720)</a>&nbsp;
                                    <a input-wh="1920*1080" href="javascript:;">全高清1080P(1920*1080)</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">视频介绍</td>
                            <td>
                                <textarea class="base_textarea" style="height: 150px;"
                                          name="descr">{$_info.descr}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel mt_10">
                <div class="panel-heading">上传视频</div>
                <div class="panel-body">

                    <ul class="tab_nav mt_10 collecting_type">
                        <li data-to="upload"><a class="" href="javascript:;">本地上传</a></li>
                        <li data-to="internet"><a class="current" href="javascript:;">网络采集</a></li>
                    </ul>

                    <div class="collecting_box" type="upload">
                        <table class="content_info2 mt_10">
                            <tr>
                                <td class="field_name">上传视频</td>
                                <td>
                                    <div class="base_group">
                                        <input data-server_key="{$_info.video_id}" readonly style="width: 309px;" name="video_url"
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
                            <tr class="video_info_tr">
                                <td class="field_name">视频ID</td>
                                <td><input readonly class="base_text" name="video_id"/></td>
                            </tr>
                            <tr class="video_info_tr">
                                <td class="field_name">视频分类</td>
                                <td>
                                    <input readonly class="base_text" name="video_class_name"/>
                                    <input type="hidden" readonly class="base_text" name="video_class_id"/>
                                </td>
                            </tr>
                            <tr class="video_info_tr">
                                <td class="field_name">视频大小</td>
                                <td><input readonly class="base_text" name="video_size"/></td>
                            </tr>
                        </table>
                    </div>
                    <div class="collecting_box" type="internet">
                        <table class="content_info2 mt_10">
                            <tr>
                                <td class="field_name">播放地址</td>
                                <td>
                                    <div class="base_group">
                                        <input style="width: 309px;" name="third_url" value="{$_info.third_url}"
                                               type="text"
                                               class="base_text"/>
                                        <a href="javascript:;" class="base_button collecting_btn">测试源</a>
                                    </div>
                                    <p class="field_tip"><b style="color:#f00;">请优先使用<a target="_blank" href="http://yskk.la">yskk</a></b>，目前支持<a target="_blank" href="http://v.qq.com">腾讯视频</a> 、<a target="_blank" href="http://www.iqiyi.com">爱奇艺</a>、<a target="_blank" href="http://v.sohu.com">搜狐视频</a> （免费、VIP和付费用劵视频均可）</p>
                                </td>
                            </tr>
                            <tr class="video_info2_tr">
                                <td class="field_name">视频网站</td>
                                <td><input readonly class="base_text" name="third_source"/></td>
                            </tr>
                            <tr class="video_info2_tr">
                                <td class="field_name">播放格式</td>
                                <td>
                                    <input readonly class="base_text" name="third_play"/>
                                </td>
                            </tr>
                        </table>
                    </div>
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
    </script>

</block>