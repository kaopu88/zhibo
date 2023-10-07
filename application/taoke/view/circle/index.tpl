<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taoke:circle:add">
                    <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="taoke:circle:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
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

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="cid" type="hidden" class="modal_select_value finder" value="{:input('cid')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <volist name="cate_list" id="vo">
                                <li class="modal_select_option" value="{$vo.id}">{$vo.name}</li>
                            </volist>
                        </ul>
                    </div>

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>

        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 5%;">id</td>
                    <td style="width: 10%;">标题</td>
                    <td style="width: 5%;">所属分类</td>
                    <td style="width: 10%;">文案</td>
                    <td style="width: 10%;">评论</td>
                    <td style="width: 5%;">排序</td>
                    <td style="width: 5%;">状态</td>
                    <td style="width: 5%;">添加时间</td>
                    <td style="width: 5%;">操作</td>
                </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>{$vo.title}</td>
                        <td>
                            <notempty name="vo.cid">
                                {$data[$vo['cid']]}
                                <else/>
                                无
                            </notempty>
                        </td>
                        <td>{$vo.showwriting|raw|htmlspecialchars_decode}</td>
                        <td>
                            <notempty name="vo.comment">
                                {$vo.comment|raw|htmlspecialchars_decode}
                                <else/>
                                无
                            </notempty>
                        </td>
                        <td>{$vo.sort}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:circle:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('changeStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.add_time|time_format='','Y-m-d H:i:s'}</td>
                        <td>
                            <auth rules="taoke:circle:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a> |
                            </auth>
                            <auth rules="taoke:circle:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
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