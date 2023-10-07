<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="js">
    <script>
        var selectedListJson = '{:htmlspecialchars_decode($selected_list)}';
    </script>
    <script src="__JS__/music_album/find.js?v=__RV__"></script>
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
                        <auth rules="admin:music_album:add">
                            <a href="{:url('add')}?__JUMP__" class="base_button base_button_s">新增</a>
                        </auth>
                        <auth rules="admin:music_album:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="歌手ID、名称" type="text" name="user_keyword" value="{:input('user_keyword')}"/>
                        <input placeholder="ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 find_list">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 15%;">标题</td>
                    <td style="width: 15%;">歌手</td>
                    <td style="width: 10%;">发布公司</td>
                    <td style="width: 5%;">第三方ID</td>
                    <td style="width: 20%;">描述</td>
                    <td style="width: 10%;">时间</td>
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
                                        <img src="{:img_url($vo['image'],'200_200','avatar')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.title}
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="thumb">
                                    <a href="javascript:;"
                                    class="thumb_img thumb_img_avatar">
                                        <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.singer_name}
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td>
                                {$vo.company}
                            </td>
                            <td>
                                {$vo.channel_album_id}
                            </td>
                            <td>{$vo.desc}</td>
                            <td>
                                创建时间：{$vo.create_time|time_format='无','date'}<br/>
                                发表时间：{$vo.release_time|time_format='无','date'}
                            </td>
                            <td>
                                <input class="find_params" type="hidden" name="id" value="{$vo.id}"/>
                                <input class="find_params" type="hidden" name="title" value="{$vo.title}"/>
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