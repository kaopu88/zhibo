<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 bg_normal">
        <include file="components/tab_nav"/>
        <div class="bg_form">
            <form action="{:url('video')}">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">点播服务</li>
                        <!--<li>视频处理任务</li>-->
                        <li>统一配置</li>

                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="vod[platform]" selectedval="{$_info.platform}">
                                            <option value="tencent">腾讯云</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用ID</td>
                                    <td>
                                        <input class="base_text" name="vod[platform_config][secret_id]" value="{$_info.platform_config.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Key</td>
                                    <td>
                                        <input class="base_text" name="vod[platform_config][secret_key]" value="{$_info.platform_config.secret_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用End_point</td>
                                    <td>
                                        <input class="base_text" name="vod[platform_config][end_point]" value="{$_info.platform_config.end_point}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Region</td>
                                    <td>
                                        <input class="base_text" name="vod[platform_config][region]" value="{$_info.platform_config.region}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Token</td>
                                    <td>
                                        <input class="base_text" name="vod[platform_config][token_url]" value="{$_info.platform_config.token_url}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签名单次有效</td>
                                    <td>
                                        <select class="base_select" name="vod[platform_config][one_time_valid]" selectedval="{$_info.platform_config.one_time_valid}">
                                            <option value="0">否</option>
                                            <option value="1">是</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签名算法</td>
                                    <td>
                                        <select class="base_select" name="vod[platform_config][sign_method]" selectedval="{$_info.platform_config.sign_method}">
                                            <option value="HmacSHA256">HmacSHA256</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用标识</td>
                                    <td>
                                        <select class="base_select" name="vod[platform_config][source_context]" selectedval="{$_info.platform_config.source_context}">
                                            <option value="app">app</option>
                                            <option value="erp">erp</option>
                                            <option value="h5">h5</option>
                                        </select>
                                        <span>这里设置为APP端上传标识</span>
                                    </td>
                                </tr>

                                <tr class="edit_tr">
                                    <td class="field_name">转码设置</td>
                                    <td>
                                        <select class="base_select" name="vod[platform_config][ProcessMedia]" selectedval="{$_info.platform_config.ProcessMedia}">
                                            <option value="0">关闭</option>
                                            <option value="100010">流畅</option>
                                            <option value="100020">标清</option>
                                            <option value="100030">高清</option>
                                            <option value="100040">全高清</option>
                                            <option value="100070">2K</option>
                                            <option value="100080">4k</option>
                                        </select>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <!--<div class="layui-tab-item">
                            <div class="content_title2">转码任务</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_id]" value="{$_live_config.qiniu_live.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][access_key]" value="{$_live_config.qiniu_live.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_key]" value="{$_live_config.qiniu_live.secret_key}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">解析音乐</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_id]" value="{$_live_config.qiniu_live.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][access_key]" value="{$_live_config.qiniu_live.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_key]" value="{$_live_config.qiniu_live.secret_key}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">转动图任务</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_id]" value="{$_live_config.qiniu_live.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][access_key]" value="{$_live_config.qiniu_live.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_key]" value="{$_live_config.qiniu_live.secret_key}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">封面截图</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_id]" value="{$_live_config.qiniu_live.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][access_key]" value="{$_live_config.qiniu_live.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_key]" value="{$_live_config.qiniu_live.secret_key}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">内容审核</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_id]" value="{$_live_config.qiniu_live.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][access_key]" value="{$_live_config.qiniu_live.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_key]" value="{$_live_config.qiniu_live.secret_key}"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">内容分析</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_id]" value="{$_live_config.qiniu_live.secret_id}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][access_key]" value="{$_live_config.qiniu_live.access_key}"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_config[qiniu_live][secret_key]" value="{$_live_config.qiniu_live.secret_key}"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">视频发布免审</td>
                                    <td>
                                        <select class="base_select" name="vod[audit_config][status]" selectedval="{$_info.audit_config.status}">
                                            <option selected value="1">关闭</option>
                                            <option value="2">开启</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">实名认证免审</td>
                                    <td>
                                        <select class="base_select" name="vod[audit_config][verified_status]" selectedval="{$_info.audit_config.verified_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">创作号免审</td>
                                    <td>
                                        <select class="base_select" name="vod[audit_config][creation_status]" selectedval="{$_info.audit_config.creation_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">虚似号免审</td>
                                    <td>
                                        <select class="base_select" name="vod[audit_config][isvirtual_status]" selectedval="{$_info.audit_config.isvirtual_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">短视频广告</td>
                                    <td>
                                        <select class="base_select" name="vod[audit_config][isadvideo_status]" selectedval="{$_info.audit_config.isadvideo_status}">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">信用分免审</td>
                                    <td>
                                        <input class="base_text" name="vod[audit_config][credit_score]" value="{$_info.audit_config.credit_score}"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name" >视频推荐方式</td>
                                    <td>
                                        <select class="base_select show_type" name="vod[audit_config][vido_show]" selectedval="{$_info.audit_config.vido_show}">
                                            <option value="0">默认随机</option>
                                            <option value="1">按曝光值</option>
                                        </select>
                                        <span>按曝光值展现的话 短视频精选推荐值越高 越优先展示</span>
                                    </td>
                                </tr>

                                <tr class="weight" <eq name="_info['audit_config']['vido_show']" value="0">style ="display:none"</eq>>
                                <td class="field_name">推荐权重值</td>
                                <td>
                                    <input class="base_text" name="vod[audit_config][recomment_weight]" value="{$_info.audit_config.recomment_weight}"/>
                                    <span>用于视频推荐展现</span>
                                </td>
                                </tr>

                                <tr class="weight" <eq name="_info['audit_config']['vido_show']" value="0">style ="display:none"</eq>>
                                <td class="field_name">标识权重值</td>
                                <td>
                                    <input class="base_text" name="vod[audit_config][identification_weight]" value="{$_info.audit_config.identification_weight}"/>
                                    <span>无标识的优先于有标识的</span>
                                </td>
                                </tr>

                                <tr class="weight" <eq name="_info['audit_config']['vido_show']" value="0">style ="display:none"</eq>>
                                    <td class="field_name">评论权重值</td>
                                    <td>
                                        <input class="base_text" name="vod[audit_config][comment_weight]" value="{$_info.audit_config.comment_weight}"/>
                                        <span>用于视频推荐展现</span>
                                    </td>
                                </tr>

                                <tr class="weight" <eq name="_info['audit_config']['vido_show']" value="0">style ="display:none"</eq>>
                                    <td class="field_name">点赞权重值</td>
                                    <td>
                                        <input class="base_text" name="vod[audit_config][zan_weight]" value="{$_info.audit_config.zan_weight}"/>
                                        <span>用于视频推荐展现</span>
                                    </td>
                                </tr>

                                <tr class="weight" <eq name="_info['audit_config']['vido_show']" value="0">style ="display:none"</eq>>
                                    <td class="field_name">分享权重值</td>
                                    <td>
                                        <input class="base_text" name="vod[audit_config][share_weight]" value="{$_info.audit_config.share_weight}"/>
                                        <span>用于视频推荐展现</span>
                                    </td>
                                </tr>

                                <tr class="weight" <eq name="_info['audit_config']['vido_show']" value="0">style ="display:none"</eq>>
                                <td class="field_name">初始曝光值</td>
                                <td>
                                    <input class="base_text" name="vod[audit_config][exposure]" value="{$_info.audit_config.exposure}"/>
                                    <span>曝光值会随着审核通过后的  慢慢减少（曝光值会根据你每次后台调整短视频重置）</span>
                                </td>
                                </tr>

                            </table>
                        </div>


                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>

            </form>
        </div>
    </div>
    <script>
        $(".show_type").change(function(){
            var type = $('.show_type option:selected').val();

            if (type == 0) {
                $('.weight').hide();

            }
            if (type == 1) {
                $('.weight').show();
            }
        });
    </script>
</block>