<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="js">
    <script>
        var selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/music_singer/find.js?v=__RV__"></script>
    <script>
        var myConfig = {
            list: [
                {
                    name: 'gender',
                    title: '性别',
                    opts: [
                        {name: '男', value: '1'},
                        {name: '女', value: '0'}
                    ]
                },
                {
                    name: 'classify',
                    title: '分类',
                    opts: [
                        {name: '男', value: '1'},
                        {name: '女', value: '2'},
                        {name: '组合', value: '0'}
                    ]
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
                        {name: '东南亚', value: '东南亚'},
                        {name: '其它', value: '其它'}
                    ]
                },
            ]
        };
    </script>
    <script src="__JS__/music/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left">
                        <auth rules="admin:music_singer:add">
                            <a href="{:url('add')}?__JUMP__" class="base_button base_button_s">新增</a>
                        </auth>
                        <auth rules="admin:music_singer:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <ul class="filter_complex" style="margin-left: 10px;float: left">
                        <li>排序：</li>
                        <li sort-name="complex" sort-by="">
                            <span>综合</span>
                        </li>
                        <li sort-name="songs_total" sort-by="desc">
                            <span>歌曲数</span>
                            <em></em>
                        </li>
                        <li sort-name="mv_total" sort-by="desc">
                            <span>mv数</span>
                            <em></em>
                        </li>
                        <li sort-name="albums_total" sort-by="desc">
                            <span>专辑数</span>
                            <em></em>
                        </li>
                        <li sort-name="time" sort-by="desc">
                            <span>发布时间</span>
                            <em></em>
                        </li>
                    </ul>
                    <div class="filter_search">
                        <input placeholder="ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="gender" value="{$get.gender}"/>
            <input type="hidden" name="classify" value="{$get.classify}"/>
            <input type="hidden" name="languages" value="{$get.languages}"/>
            <input type="hidden" name="sort" value="{$get.sort}"/>
            <input type="hidden" name="sort_by" value="{$get.sort_by}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 find_list">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 15%;">标题</td>
                    <td style="width: 5%;">性别</td>
                    <td style="width: 10%;">分类</td>
                    <td style="width: 10%;">出生日期</td>
                    <td style="width: 5%;">地区</td>
                    <td style="width: 5%;">语种</td>
                    <td style="width: 15%;">其他信息</td>
                    <td style="width: 10%;">添加时间</td>
                    <td style="width: 15%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.id}" class="find_list_li">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <div class="thumb">
                                    <a href="javascript:;"
                                    class="thumb_img thumb_img_avatar">
                                        <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.name}
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <switch name="vo['gender']">
                                    <case value="1">
                                        男
                                    </case>
                                    <case value="2">
                                        女
                                    </case>
                                </switch>
                            </td>
                            <td>
                                <switch name="vo['classify']">
                                    <case value="0">
                                        组合
                                    </case>
                                    <case value="1">
                                        男歌手
                                    </case>
                                    <case value="2">
                                        女歌手
                                    </case>
                                </switch>
                            </td>
                            <td>{$vo.birth}</td>
                            <td>{$vo.country}</td>
                            <td>{$vo.languages}</td>
                            <td>
                                第三方id：<a href="javascript:;">{$vo.channel_singer_id}</a><br/>
                                歌手简介：<a href="javascript:;">{$vo.intro}</a><br/>
                                歌曲数量：<a href="javascript:;">{$vo.songs_total}</a><br/>
                                mv数量：<a href="javascript:;">{$vo.mv_total}</a><br/>
                                专辑数量：<a href="javascript:;">{$vo.albums_total}</a>
                            </td>
                            <td>
                                {$vo.create_time|time_format='无','date'}
                            </td>
                            <td>
                                <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                                <input class="find_params" type="hidden" name="name" value="{$vo.name}"/>
                                <a data-id="{$vo.id}" class="select_btn" href="javascript:;">选择</a>
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

</block>