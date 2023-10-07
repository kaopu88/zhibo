<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'category_id',
                    title: '类别',
                    get: '{:url("common/get_music_category")}'
                },
                {
                    name: 'languages',
                    title: '语种',
                    opts: [
                        {name: '华语', value: '华语'},
                        {name: '欧美', value: '欧美'},
                        {name: '日语', value: '日语'},
                        {name: '韩语', value: '韩语'},
                        {name: '粤语', value: '粤语'},
                        {name: '其它', value: '其它'}
                    ]
                },
                {
                    name: 'channel',
                    title: '来源',
                    opts: [
                        {name: '百度', value: 'baidu'},
                        {name: '小米', value: 'xiaomi'},
                        {name: '本地', value: 'local'}
                    ]
                },
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '上架', value: '1'},
                        {name: '下架', value: '0'}
                    ]
                }
            ]
        };
    </script>
    <script src="__JS__/music/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left">
                        <auth rules="admin:music:add">
                            <a href="{:url('add')}?__JUMP__" class="base_button base_button_s">新增</a>
                        </auth>
                        <auth rules="admin:music:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="作者ID、昵称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="ID、歌曲标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="languages" value="{$get.languages}"/>
            <input type="hidden" name="channel" value="{$get.channel}"/>
            <input type="hidden" name="status" value="{$get.status}"/>
            <input type="hidden" name="category_id" value="{$get.category_id}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 14%;">标题</td>
                <td style="width: 14%;">作者</td>
                <td style="width: 6%;">分类</td>
                <td style="width: 5%;">来源</td>
                <td style="width: 11%;">专辑内容</td>
                <td style="width: 10%;">歌曲信息</td>
                <td style="width: 8%;">其它信息</td>
                <td style="width: 6%;">状态</td>
                <td style="width: 10%;">时间</td>
                <td style="width: 6%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <div class="thumb">
                                <a href="{$vo.link}"
                                   target="_blank"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['image'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="{$vo.link}" target="_blank">
                                        <eq name="vo['is_original']" value="1">
                                            <span class="fc_red">[原创]</span><br/>
                                        </eq>
                                        {$vo.title}<br/>
                                        <a href="{$vo.lrc_link}">歌词链接</a>
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <include file="recharge_app/user_info"/>
                        </td>
                        <td>{$vo.cat_name}</td>
                        <td>
                            <switch name="vo['channel']">
                                <case value="baidu">
                                    百度
                                </case>
                                <case value="xiaomi">
                                    小米
                                </case>
                                <case value="local">
                                    本地
                                </case>
                            </switch>
                        </td>
                        <td>
                            专辑：<a href="javascript:;">{$vo.album}</a><br/>
                            歌手：<a href="javascript:;">{$vo.singer}</a><br/>
                            语种：<a href="javascript:;">{$vo.languages}</a><br/>
                            风格：<a href="javascript:;">{$vo.style_title}</a><br/>
                            所属公司：<a href="javascript:;">{$vo.company}</a>
                        </td>
                        <td>
                            时长：<a href="javascript:;">{$vo.duration}</a><br/>
                            大小：<a href="javascript:;">{$vo.size}</a><br/>
                            码率：<a href="javascript:;">{$vo.all_bitrate}</a><br/>
                            当前码率：<a href="javascript:;">{$vo.bitrate}</a><br/>
                            格式：<a href="javascript:;">{$vo.ext}</a><br/>
                            标签：<a href="javascript:;">{$vo.tag}</a>
                        </td>
                        <td>
                            使用量：<a href="javascript:;">{$vo.use_num}</a><br/>
                            收藏量：<a href="javascript:;">{$vo.collect_num}</a><br/>
                            歌词举报次数：<a href="javascript:;">{$vo.lrc_report}</a><br/>
                            相关描述：<a href="javascript:;">{$vo.desc}</a>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:music:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-on-name="上架" tgradio-off-name="下架"
                                 tgradio-name="status"
                                 tgradio="{:url('music/change_status',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>
                            发表时间：{$vo.release_time|time_format='无','date'}<br/>
                            创作时间：{$vo.create_time|time_format='无','date'}
                        </td>
                        <td>
                            <auth rules="admin:music:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>
                            </auth>
                            <auth rules="admin:music:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('delete',array('id'=>$vo['id']))}?__JUMP__">删除</a>
                            </auth>
                        </td>
                    </tr>
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
            </tbody>
        </table>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

</block>