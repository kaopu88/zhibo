<extend name="public:base_nav"/>
<block name="js">
    <script src="__VENDOR__/cropper/cropper.min.js?v=__RV__"></script>
    <script src="__VENDOR__/Tdrag.js?v=__RV__"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .one{
            width: 100px;
            height: 100px;
            background:transparent url(/bx_static/admin/assets/erweima.jpg) no-repeat;
        }
        .boxList{
            border: 1px solid #ff0033;
            height: 504px;
            position: relative;
            width: 320px;
        }
    </style>
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
                    <td class="field_name">背景图片(尺寸: 640 * 1008)</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="bg_url" value="{$_info.bg_url}" type="text" class="base_text border_left_radius"/>
                            <a class="base_button border_right_radius" onclick="GetFile();" >上传</a>
                            <input type="file" value="" id="file" style="display: none"/>

                          <!--  <a uploader-size="2147483648" uploader-type="image" href="javascript:;"
                               class="base_button border_right_radius"
                               uploader="admin_images"
                               uploader-field="bg_url">上传</a>-->
                        </div>

                        <div class="boxList" style="width: 320px;height: 504px;margin-top: 10px;border: 1px solid #ccc; background: url({$_info.bg_url}) no-repeat; background-size:320px 504px;" >
                            <div class="one div3" style="position: absolute;margin: 0px;z-index: 888;left: {$_info.data.width}px;top: {$_info.data.height}px;">
                                <input id="x3" class="base_text"  style="display: none" name="width" value="{$_info.data.width}"/>
                                <input id="y3" class="base_text" style="display: none" name="height" value="{$_info.data.height}"/>
                            </div>

                            <div class="div4" style="position: absolute;width: 48px;height: 22px;border: 1px solid #0a0a0a;;color: white;font-size: 16px;font-weight:bold;z-index: 1001;left:{$_info.data.fontwidth}px;top: {$_info.data.fontheight}px;">
                                <input id="x4" class="base_text" style="display: none"  name="fontwidth" value="{$_info.data.fontwidth}"/>
                                <input id="y4" class="base_text" style="display: none" name="fontheight" value="{$_info.data.fontheight}"/>
                                邀请码
                            </div>


                        </div>

                    </td>
                </tr>

                <tr>
                    <td class="field_name">状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">排序</td>
                    <td>
                        <input class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <div class="base_button_div max_w_412">
                            <a href="javascript:;" class="base_button" id="upload" ajax="post">提交</a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>

jQuery(function(){

    //第二个例子的拖拽
    $(".div3").Tdrag({
        scope:".boxList",
    });

     $(".div4").Tdrag({
        scope:".boxList",
    });
    //禁止
    $(".disable_cloose").on("click",function(){
        $.disable_cloose()
    });
    //开启
    $(".disable_open").on("click",function(){
        $.disable_open()
    });
})

 function GetFile() {
    $("#file").click();
 }

 $("#file").change(function(){
    var objUrl = getObjectURL(this.files[0]) ;
    if (objUrl) {
        $('.boxList').css("background","url("+objUrl+") no-repeat");
        $('.boxList').css("background-size","320px 504px");
    }

     if($("#file").val() != "") {
            var file=$("#file").val();
            var filename=file.substr(file.lastIndexOf("."));
            if(filename !='.png' && filename !='.jpg'){
                return;
            }
        }
         var formData = new FormData();
         formData.append('erweima_img', document.getElementById('file').files[0]);
          $.ajax({
                    type: "POST",
                    url:"/admin/poster/upload",//后台接口
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    data : formData,  // 文件的id
                    success: function(d){
                        if(d.status == 0) {
                        $("input[name='bg_url']").val(d.data);
                            layer.msg(d.message);
                        } else {
                            layer.msg(d.message)
                        }
                    },
                    error: function () {

                    },
                });

});

//建立一個可存取到该file的url
function getObjectURL(file) {
    var url = null ;
    if (window.createObjectURL!=undefined) { // basic
        url = window.createObjectURL(file) ;
    } else if (window.URL!=undefined) { // mozilla(firefox)
        url = window.URL.createObjectURL(file) ;
    } else if (window.webkitURL!=undefined) { // webkit or chrome
        url = window.webkitURL.createObjectURL(file) ;
    }
    return url ;
}


</script>
</block>