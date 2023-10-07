<extend name="public:base_nav"/>
<block name="js">
    <script>
        var getInfoUrl = '{:url("film_tags/get_info")}',
            addTypeUrl = '{:url("film_tags/add")}',
            editTypeUrl = '{:url("film_tags/edit")}',
            pid = '{$_pid}';
    </script>
    <script src="__JS__/film_tags/index.js?v=__RV__"></script>
</block>
<block name="css"></block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:film_tags:add">
                    <li><a href="javascript:;" class="base_button base_button_s add_btn">新增</a></li>
                </auth>
                <auth rules="admin:film_tags:delete">
                    <li>
                        <a href="{:url('film_tags/delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                    </li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、名称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 sm_width">
            <thead>
            <tr>
                <td colspan="9">
                    <volist name="_path" id="pa">
                        <a href="{:url('film_tags/index',array('pid'=>$pa['id']))}">{$pa.name}</a>&nbsp;>&nbsp;
                    </volist>
                </td>
            </tr>
            <tr class="thead_tr">
                <td style="width: 10%"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%">ID</td>
                <td style="width: 15%">名称</td>
                <td style="width: 25%">描述</td>
                <td style="width: 10%">排序</td>
                <td style="width: 10%">子项</td>
                <td style="width: 10%">创建时间</td>
                <td style="width: 10%">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr>
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td><a href="{:url('film_tags/index',array('pid'=>$vo['id']))}">{$vo.name}</a></td>
                        <td>{$vo.descr}</td>
                        <td>{$vo.sort}</td>
                        <td>{$vo.child_num}</td>
                        <td>{$vo.create_time|time_format='','date'}</td>
                        <td>
                            <auth rules="admin:film_tags:update">
                                <a data-id="{$vo.id}" class="edit_btn" href="javascript:;">编辑</a>
                            </auth>
                            <auth rules="admin:film_tags:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('film_tags/delete',array('id'=>$vo['id']))}">删除</a>
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
        </div>
        <div class="pageshow mt_10">
            {:htmlspecialchars_decode($_page)}
        </div>

    </div>
    <script>
        new FinderController('.finder', '');
    </script>
</block>


<block name="layer">
    <include file="film_tags/add_pop"/>
</block>