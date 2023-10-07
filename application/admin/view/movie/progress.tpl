<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name} 【{$movie.title}】</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:movie_progress:add">
                    <li><a href="{:url('add_progress',['mid'=>$movie['id']])}?__JUMP__"
                           class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="admin:movie_progress:delete">
                    <li><a href="{:url('del_progress')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 10%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 10%;">缩略图</td>
                <td style="width: 30%;">标题</td>
                <td style="width: 20%;">摘要</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 10%;">操作</td>
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
                                <img src="{:img_url($vo['image'],'','thumb')}"/>
                            </div>
                        </td>
                        <td>{$vo.title}</td>
                        <td>{$vo.summary}</td>
                        <td>
                            {$vo.release_time|time_format}<br/>
                            {$vo.create_time|time_format}</td>
                        <td>
                            <auth rules="admin:movie_progress:update">
                                <a href="{:url('edit_progress',array('id'=>$vo['id'],'mid'=>$vo['mid']))}?__JUMP__">编辑</a>
                            </auth>
                            <auth rules="admin:movie_progress:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del_progress',array('id'=>$vo['id']))}">删除</a>
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
    <script>
        new FinderController('.finder', '');
    </script>

</block>