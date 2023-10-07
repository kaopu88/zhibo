<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <style>
            .default_img{
                float: left;
            }
            .default_img>img{
                width: 35px;
                margin-left: 9px;
            }
        </style>
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('upload')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">上传设置</li>
                        <li>服务配置</li>
                        <li>默认图片</li>
                        <!--<li>提现配置</li>-->
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">存储服务商</td>
                                    <td>
                                        <select class="base_select" name="platform" selectedval="{$_info.platform}">
                                            <option value="qiniu">七牛云</option>
                                            <option value="aliyun">阿里云</option>
                                            <option value="wsyun">网宿云</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">静态资源CDN</td>
                                    <td>
                                        <input class="base_text" name="resource_cdn" value="{$_info.resource_cdn}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">静态资源版本</td>
                                    <td>
                                        <input class="base_text" name="resource_version" value="{$_info.resource_version}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">上传最小限制</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][fsizeMin]" value="{$_info.upload_config._default['fsizeMin']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">上传最大限制</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][fsizeLimit]" value="{$_info.upload_config._default['fsizeLimit']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">类型限制</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][mimeLimit]" value="{$_info.upload_config._default['mimeLimit']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">文件后缀名</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][allowExts]" value="{$_info.upload_config._default['allowExts']}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">保存路径规则</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][path]" value="{$_info.upload_config._default['path']}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="platform_config[access_key]" value="{$_info.platform_config.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="platform_config[secret_key]" value="{$_info.platform_config.secret_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用bucket</td>
                                    <td>
                                        <input class="base_text" name="platform_config[bucket]" value="{$_info.platform_config.bucket}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Root_path</td>
                                    <td>
                                        <input class="base_text" name="platform_config[root_path]" value="{$_info.platform_config.root_path}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Base_url组</td>
                                    <td>
                                        <input class="base_text" name="platform_config[base_url]" value="{$_info.platform_config.base_url}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <div class="content_title2">默认注册头像</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">注册头像1</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[0]" value="{$_info.reg_avatar[0]}"/>
                                        <div class="default_img">
                                            <img src="{$_info.reg_avatar[0]}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[0]">上传
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">注册头像2</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[1]" value="{$_info.reg_avatar[1]}"/>
                                        <div class="default_img">
                                            <img src="{$_info.reg_avatar[1]}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[1]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">注册头像3</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[2]" value="{$_info.reg_avatar[2]}"/>
                                        <div class="default_img">
                                            <img src="{$_info.reg_avatar[2]}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[2]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">注册头像4</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[3]" value="{$_info.reg_avatar[3]}"/>
                                        <div class="default_img">
                                            <img src="{$_info.reg_avatar[3]}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[3]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">默认资源图片</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">通用默认头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[avatar]" value="{$_info.image_defaults.avatar}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.avatar}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">小助手头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[helper_avatar]" value="{$_info.image_defaults.helper_avatar}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.helper_avatar}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[helper_avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">官方通知头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[official_avatar]" value="{$_info.image_defaults.official_avatar}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.official_avatar}"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[official_avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">后端默认头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[admin_avatar]" value="{$_info.image_defaults.admin_avatar}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.admin_avatar}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[admin_avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">缩略图(长方形)</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[thumb]" value="{$_info.image_defaults.thumb}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.thumb}"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[thumb]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">缩略图(正方形)</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[thumb2]" value="{$_info.image_defaults.thumb2}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.thumb2}"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[thumb2]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">全局logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[logo]" value="{$_info.image_defaults.logo}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.logo}"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主站logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[home_logo]" value="{$_info.image_defaults.home_logo}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.home_logo}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[home_logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">下载站logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[download_logo]" value="{$_info.image_defaults.download_logo}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.download_logo}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[download_logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">wap网站logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[wap_logo]" value="{$_info.image_defaults.wap_logo}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.wap_logo}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[wap_logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">短视频封面</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[film_cover]" value="{$_info.image_defaults.film_cover}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.film_cover}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[film_cover]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播直播间封面</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[live_anchor_cover]" value="{$_info.image_defaults.live_anchor_cover}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.live_anchor_cover}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[live_anchor_cover]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">小店背景默认图</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[shop_bg]" value="{$_info.image_defaults.shop_bg}"/>
                                        <div class="default_img">
                                            <img src="{$_info.image_defaults.shop_bg}"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[shop_bg]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div class="content_title2">邀请码注册轮播图</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">轮播图</td>
                                    <td>
                                        <ul class="json_list exhibition_img"></ul>
                                        <input name="invite_imgs" type="hidden" value="<if condition="$_info.invite_imgs">{$_info.invite_imgs|implode=','}</if>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--<div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">提现比率</td>
                                    <td>
                                        <input class="base_text" name="cash_rate" value="{$_info.cash_rate}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">每笔提现手续费</td>
                                    <td>
                                        <input class="base_text" name="cash_fee" value="{$_info.cash_fee}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">户提现收取的税费</td>
                                    <td>
                                        <input class="base_text" name="cash_taxes" value="{$_info.cash_taxes}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">申请提现所需满足最低货币量</td>
                                    <td>
                                        <input class="base_text" name="cash_min" value="{$_info.cash_min}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">每月最多可提现次数</td>
                                    <td>
                                        <input class="base_text" name="cash_monthlimit" value="{$_info.cash_monthlimit}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">{::APP_BEAN_NAME}转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[exp_rate]" value="{$_info.app_setting.exp_rate}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">{:APP_MILLET_NAME}转为{:APP_BEAN_NAME}转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[millet_rate]" value="{$_info.app_setting.millet_rate}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->
                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>

            </form>
        </div>
    </div>
    <script>
        var myJsonList = new JsonList('.json_list', {
            input: '[name=invite_imgs]',
            btns: ['up', 'down', 'add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    name: 'image',
                    title: '图片',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'friend_images'
                    }
                }
            ]
        });
    </script>
</block>