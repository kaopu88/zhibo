<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="admin:notice:add">
                    <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__"
                           class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="admin:notice:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="visible" type="hidden" class="modal_select_value finder"
                               value="{:input('visible')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">可见性</li>
                            <li class="modal_select_option" value="0">全部</li>
                            <li class="modal_select_option" value="1">总后台</li>
                            <li class="modal_select_option" value="2">{:config('app.agent_setting.agent_name')}</li>
                        </ul>
                    </div>
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="barrage" type="hidden" class="modal_select_value finder"
                               value="{:input('barrage')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">弹幕</li>
                            <li class="modal_select_option" value="1">开启</li>
                            <li class="modal_select_option" value="0">关闭</li>
                        </ul>
                    </div>
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder"
                               value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">启用</li>
                            <li class="modal_select_option" value="0">禁用</li>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索ID、标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10" style="min-width:900px">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">标题</td>
                <td style="width: 10%;">发布者</td>
                <td style="width: 10%;">可见性</td>
                <td style="width: 10%;">弹幕</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 15%;">创建时间</td>
                <td style="width: 15%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.id}
                        </td>
                        <td>
                            <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">
                                {$vo.title}
                            </a>
                        </td>
                        <td>admin</td>
                        <td>{$vo.visible ? ($vo.visible == 1 ? '总后台' : config('app.agent_setting.agent_name')) : '全部'}</td>
                        <td>{$vo.barrage ? '开启' : '关闭'}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:notice:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            {$vo.create_time|time_format}
                        </td>
                        <td>
                            <auth rules="admin:notice:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑公告</a><br/>
                            </auth>
                            <auth rules="admin:notice:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除公告</a>
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="components/recommend_pop" />
</block>