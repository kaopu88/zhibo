<extend name="public:base_nav"/>
<block name="js">
    <script>
        var getInfoUrl = '{:url("category/get_info")}',
            addTypeUrl = '{:url("category/add")}',
            editTypeUrl = '{:url("category/edit")}',
            pid = '{$_pid}';
    </script>
    <script src="__JS__/category/index.js?v=__RV__"></script>
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
                <auth rules="admin:category:add">
                    <li><a href="javascript:;" class="base_button base_button_s add_btn">新增</a></li>
                </auth>
                <auth rules="admin:category:delete">
                    <li>
                        <a href="{:url('category/delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                    </li>
                </auth>
                <auth rules="admin:setting:data_update">
                    <li style="line-height: 30px;font-size: 12px;padding-left: 10px;">
                        <a data-to="category:{$data_v}" class="update_data_version" href="javascript:;">{:$data_v=='last'?'已更新至最新版本':'更新至:'.$data_v}</a>
                    </li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">启用</li>
                            <li class="modal_select_option" value="0">禁用</li>
                        </ul>
                    </div>
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
                        <a href="{:url('category/index',array('pid'=>$pa['id']))}">{$pa.name}</a>&nbsp;>&nbsp;
                    </volist>
                </td>
            </tr>
            <tr class="thead_tr">
                <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%">ID</td>
                <td style="width: 17%">名称</td>
                <td style="width: 13%">标识符</td>
                <td style="width: 10%">排序</td>
                <td style="width: 13%">状态</td>
                <td style="width: 10%">子项</td>
                <td style="width: 12%">创建时间</td>
                <td style="width: 10%">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr>
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td><a href="{:url('category/index',array('pid'=>$vo['id']))}">{$vo.name}</a></td>
                        <td>{$vo.mark}</td>
                        <td>{$vo.sort}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:category:update')?'0':'1'}" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}"
                                 tgradio-name="status" tgradio="{:url('category/change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.child_num}</td>
                        <td>{$vo.create_time|time_format='','date'}</td>
                        <td>
                            <auth rules="admin:category:update">
                                <a data-id="{$vo.id}" class="edit_btn" href="javascript:;">编辑</a>
                            </auth>
                            <auth rules="admin:category:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('category/delete',array('id'=>$vo['id']))}">删除</a>
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
    <include file="category/add_pop"/>
</block>