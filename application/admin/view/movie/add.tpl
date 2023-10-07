<extend name="public:base_nav"/>
<block name="js">
    <script charset="utf-8" src="__JS__/ueditor.config.js?v=__RV__" type="text/javascript"></script>
    <script src="__VENDOR__/ueditor/ueditor.all.min.js?v=__RV__"></script>
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
                    <td class="field_name">影片名称</td>
                    <td>
                        <input class="base_text" name="title" value="{$_info.title}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">电影简介</td>
                    <td>
                        <textarea placeholder="" class="base_textarea" name="descr">{$_info.descr}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">缩略图</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="image" value="{$_info.image}" type="text"
                                   class="base_text"/>
                            <a uploader-type="image" href="javascript:;" class="base_button" uploader="movie_thumb"
                               uploader-field="image">上传</a>
                        </div>
                        <div imgview="[name=image]" style="width: 120px;margin-top: 10px;"><img
                                src="{:img_url('','450_253','movie_thumb')}"/></div>
                        <p class="field_tip">最佳尺寸:800px*450px</p>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">导演</td>
                    <td>
                        <input class="base_text" name="director" value="{$_info.director}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">影片状态</td>
                    <td>
                        <select class="base_select" name="mv_status" selectedval="{$_info.mv_status}">
                            <option value="">请选择</option>
                            <option value="0">筹划中</option>
                            <option value="1">预告片</option>
                            <option value="2">热映中</option>
                            <option value="3">已下线</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">影片标签</td>
                    <td>
                        <ul class="json_list tags_list"></ul>
                        <input name="tags" type="hidden" value="{$_info.tags}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">出品方</td>
                    <td>
                        <ul class="json_list produced_list"></ul>
                        <input name="produced_company" type="hidden" value="{$_info.produced_company}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">发行方</td>
                    <td>
                        <input class="base_text" name="issued_company" value="{$_info.issued_company}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">影片时长</td>
                    <td>
                        <input placeholder="单位：分钟" class="base_text" name="length" value="{$_info.length}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">上映时间</td>
                    <td>
                        <input readonly placeholder="请选择时间,默认为待定" class="base_text" name="release_time"
                               value="{$_info.release_time|time_format='','Y-m-d'}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">影片语言</td>
                    <td>
                        <ul class="json_list language_list"></ul>
                        <input name="language" type="hidden" value="{$_info.language}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">PC内容</td>
                    <td>
                        <textarea style="width:900px;height:400px;" name="content" ueditor>{$_info.content}</textarea>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">手机内容</td>
                    <td>
                        <textarea style="height: 200px;" class="base_textarea" name="mobile_content">{$_info.mobile_content}</textarea>
                        <p class="field_tip">可以通过第三方微信编辑器，编辑后复制到这里</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">影片海报</td>
                    <td>
                        <ul class="json_list images_list"></ul>
                        <input name="images" type="hidden" value="{$_info.images}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">演员列表</td>
                    <td>
                        <ul class="json_list actors_list"></ul>
                        <input name="actors" type="hidden" value="{$_info.actors}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">项目公示函</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="letter_file" value="{$_info.letter_file}" type="text"
                                   class="base_text"/>
                            <a uploader-type="attachment" href="javascript:;" class="base_button" uploader="movie_attachment"
                               uploader-field="letter_file" uploader-size="104857600">上传</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">合规文件</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 309px;" name="policy_file" value="{$_info.policy_file}" type="text"
                                   class="base_text"/>
                            <a uploader-type="attachment" href="javascript:;" class="base_button" uploader="movie_attachment"
                               uploader-field="policy_file" uploader-size="104857600">上传</a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">排序权重</td>
                    <td>
                        <input placeholder="数值越大越靠前" class="base_text" name="sort" value="{$_info.sort}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">浏览量</td>
                    <td>
                        <input class="base_text" name="pv" value="{$_info.pv|default=0}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">点赞量</td>
                    <td>
                        <input class="base_text" name="like_num" value="{$_info.like_num|default=0}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">启用状态</td>
                    <td>
                        <select class="base_select" name="status" selectedval="{$_info.status}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <present name="_info['id']">
                            <input name="id" type="hidden" value="{$_info.id}"/>
                        </present>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">提交</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script>
        $("[name=release_time]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });


        new JsonList('.tags_list', {
            input: '[name=tags]',
            btns: ['friend.view.friend.add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    title: '标签',
                    type: 'text',
                    width: 150
                }
            ]
        });

        new JsonList('.language_list', {
            input: '[name=language]',
            btns: ['friend.view.friend.add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    title: '语言',
                    type: 'select',
                    options: [
                        {name: '请选择', value: ''},
                        {name: '国语', value: '国语'},
                        {name: '英语', value: '英语'},
                        {name: '粤语', value: '粤语'},
                        {name: '日语', value: '日语'},
                        {name: '韩语', value: '韩语'},
                        {name: '法语', value: '法语'},
                    ],
                    width: 150
                }
            ]
        });


        new JsonList('.images_list', {
            input: '[name=images]',
            btns: ['up', 'down', 'friend.view.friend.add', 'remove'],
            max: 5,
            fields: [
                {
                    name: 'title',
                    title: '标题',
                    type: 'text',
                    width: 200
                },
                {
                    name: 'img',
                    title: '图片(1920px*300px)',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'char_cover',
                    }
                }
            ]
        });


        new JsonList('.actors_list', {
            input: '[name=actors]',
            btns: ['up', 'down', 'friend.view.friend.add', 'remove'],
            max: 50,
            fields: [
                {
                    name: 'name',
                    title: '姓名',
                    type: 'text',
                    width: 200
                },
                {
                    name: 'role',
                    title: '角色',
                    type: 'text',
                    width: 200
                },
                {
                    name: 'avatar',
                    title: '头像',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'char_avatar',
                        'crop': 1
                    }
                }
            ]
        });


        new JsonList('.produced_list', {
            input: '[name=produced_company]',
            btns: ['up', 'down', 'friend.view.friend.add', 'remove'],
            max: 50,
            fields: [
                {
                    name: 'name',
                    title: '出品方',
                    type: 'text',
                    width: 200
                },
                {
                    name: 'url',
                    title: '官网，如http://www.bingxin.com',
                    type: 'text',
                    width: 200
                },
                {
                    name: 'logo',
                    title: 'LOGO',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'movie_thumb',
                        'crop': 1
                    }
                }
            ]
        });


    </script>

</block>