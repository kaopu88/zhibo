<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/tencentyun/ugcUploader.js?v=__RV__"></script>
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
    <script src="__JS__/video/add.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>
    <script src="__JS__/video/es6-promise.auto.js"></script>
    <script src="__JS__/vue.js"></script>
    <script src="__JS__/axios.js"></script>
    <script src="__JS__/video/vod-js-sdk-v6.js"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/layer/layui/css/layui.css"/>
</block>

<block name="body">
    <div class="pa_20 p_nav p_b_60">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        
        <ul class="tab_nav mt_10" style="margin-top: -10px;">
            <include file="components/tab_nav"/>
        </ul>
            <div class="content_toolbar mt_10">
                <ul class="content_toolbar_btns">
                    <auth rules="admin:film:add">
                        <li><a id="batchUp" href="javascript:;" class="base_button base_button_s base_button_gray add_btn">
                                <span class="icon-plus"></span> 选择视频
                            </a></li>
                    </auth>
                </ul>
                <div style="float: right;font-size: 12px;line-height: 30px;" class="fc_orange">已选择<span class="video_num">0</span>个视频</div>
            </div>

            <table class="content_list mt_10 audit_list mt_10">
                <thead>
                <tr>
                    <td>标题</td>
                    <td>大小</td>
                    <td>进度</td>
                    <td>状态</td>
                    <td>操作</td>
                </tr>
                </thead>
                <tbody id="batchUpBody">

                </tbody>
            </table>

            <div class="mt_10">
                <div class="base_button layui-btn-disabled layui_upload" id="beforeBatchAction">开始上传</div>
                <a class="layui-btn next" style="float:right;display:none;" href="{:url('batchedit')}">下一步</a>
            </div>
    </div>

    <script>
        
        function getSignature() {
          return axios.post('{:url('video/getsignature')}').then(function (response) {
            return response.data;
          })
        };

        var self = '';

        var batchFile = {};

        layui.use(['form', 'layer', 'element', 'upload'], function () {
            
            var form = layui.form, layer = layui.layer, element = layui.element, upload = layui.upload;

            var batchUpView = $('#batchUpBody'),
                batchUpRender = upload.render({
                elem: '#batchUp',
                url: '',
                accept: 'video',
                multiple: true,
                auto: false,
                bindAction: '',

                choose: function(obj){
                    $('.layui_upload').removeClass('layui-btn-disabled').attr('id','beforeBatchAction');
                    this.files = obj.pushFile();
                    self = this;
                    obj.preview(function(index, files, result){

                        batchFile[index] = {"videourl":'', "videoid":'', "videoname":files.name};

                        var tr = $(
                            [
                                '<tr id='+index+'>',
                                '<td>'+files.name+'</td>',
                                '<td>'+ (files.size/1048576).toFixed(2) +'MB</td>',
                                '<td class="progress_'+index+'" style="color:green">0.00%</td>',
                                '<td>等待上传</td>',
                                '<td>',
                                '<button class="layui-btn layui-btn-sm batch-reload layui-hide">重传</button>',
                                '<button class="layui-btn layui-btn-sm layui-btn-danger batch-delete">删除</button>',
                                '</td>',
                                '</tr>'].join('')
                        );


                        tr.find('.batch-reload').on('click', function(){
                            obj.upload(index, files);//单个重传
                        });

                        //删除
                        tr.find('.batch-delete').on('click', function(){
                            delete self.files[index]; //删除对应的文件
                            tr.remove();
                            batchUpRender.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                            delete batchFile[index];
                            var video_num = $('#batchUpBody tr').length;
                            if(video_num==0){
                                $('.layui_upload').addClass('layui-btn-disabled').attr('id','');
                            }
                            $('.video_num').text(video_num);
                        });

                        batchUpView.append(tr);
                        var video_num = $('#batchUpBody tr').length;
                        if(video_num==0){
                            $('.layui_upload').addClass('layui-btn-disabled').attr('id','');
                        }
                        $('.video_num').text(video_num);
                        form.render();
                    });

                },

                done: function(res, index, upload){
                
                    if(res.status == 0){
                        let tr = batchUpView.find('tr#'+ index), tds = tr.children();
                        tds.eq(3).html('<span style="color: #5FB878;">上传成功</span>');
                        delete self.files[index];
                        delete batchFile[index];
                        $('.next').show();
                    }else {
                        this.error(index, upload);
                    }

                },

                error: function(index, upload){
                    let tr = batchUpView.find('tr#'+ index), tds = tr.children();
                    tds.eq(3).html('<span style="color: #FF5722;">上传失败</span>');
                    tds.last().find('.batch-reload').removeClass('layui-hide'); //显示重传
                }

            });

            //批量上传-视频上传
            $('#beforeBatchAction').on('click', function () {

                if(self.files == '' || self.files == 'undefined')
                {
                    layer.msg('暂未添加视频源');
                    return false;
                }


                let load = layer.load(1, {shade: [0.4,'#fff']});

                let tag = true;

                $.each(self.files, function (index, item) {
                    const tcVod = new TcVod.default({
                      getSignature: getSignature
                    })
                    const uploader = tcVod.upload({
                      videoFile: item,
                    })
                    uploader.on('video_progress', function(info) {
                      $('.progress_'+index).text((info.percent * 100).toFixed(2)+'%');
                      uploaderInfo.progress = info.percent;
                    })
                    uploader.on('video_upload', function(info) {
                      uploaderInfo.isVideoUploadSuccess = true;
                    })
                    console.log(uploader, 'uploader')
                    var uploaderInfo = {
                      videoInfo: uploader.videoInfo,
                      isVideoUploadSuccess: false,
                      isVideoUploadCancel: false,
                      progress: 0,
                      fileId: '',
                      videoUrl: '',
                      cancel: function() {
                        uploaderInfo.isVideoUploadCancel = true;
                        uploader.cancel()
                      },
                    }
                    uploader.done().then(function(res) {
                        batchFile[index].videourl = res.video.url;

                        batchFile[index].videoid = res.fileId;

                        $.ajax({
                            type: "POST",
                            url: "{:url('upfilm')}",
                            dataType: 'JSON',
                            async: false,
                            data: batchFile[index],
                            success: function (res) {
                                batchUpRender.config.done(res, index, batchFile[index]);
                            },
                        });
                    }).then(function (videoUrl) {
                    })

                });
                if (!tag)
                {
                    layer.close(load);
                    return false;
                }
                layer.close(load);
                $(this).addClass('layui-btn-disabled').attr('id','');
            });
        });
    </script>

</block>