<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taoke:module:add">
                    <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="taoke:module:delete">
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
                        <input name="position_id" type="hidden" class="modal_select_value finder" value="{:input('position_id')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <volist name="position" id="vo">
                                <li class="modal_select_option" value="{$vo.id}">{$vo.name}</li>
                            </volist>
                        </ul>
                    </div>

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索广告名称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">广告ID</td>
                <td style="width: 10%;">标题</td>
                <td style="width: 5%;">打开方式</td>
                <td style="width: 10%;">关联页面</td>
                <td style="width: 10%;">图片</td>
                <td style="width: 10%;">选中后图片</td>
                <td style="width: 15%;">展示平台</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 5%;">添加时间</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.module_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.module_id}"/></td>
                        <td>{$vo.module_id}</td>
                        <td>{$vo.title}</td>
                        <td>
                            <if condition="$vo.open_type eq 1">
                                APP内链
                            </if>
                            <if condition="$vo.open_type eq 2">
                                外部链接
                            </if>
                            <if condition="$vo.open_type eq 0">
                                无
                            </if>
                        </td>
                        <td>
                            <notempty name="vo.page_id">
                                {$vo.page_name}
                            </notempty>
                            <notempty name="vo.open_url">
                                {$vo.open_url}
                            </notempty>
                        </td>
                        <td>
                            <notempty name="vo.image">
                                <div class="thumb">
                                    <a rel="thumb" href="{:img_url($vo['image'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($vo['image'],'200_200','thumb')}"/>
                                    </a>
                                </div>
                            </notempty>
                        </td>
                        <td>
                            <notempty name="vo.selected_image">
                                <div class="thumb">
                                    <img src="{:img_url($vo['selected_image'],'200_200','thumb')}"/>
                                </div>
                            </notempty>
                        </td>
                        <td>{$vo.name}</td>
                        <td>{$vo.sort}</td>
                        <td>{$vo.add_time|time_format='','Y-m-d H:i:s'}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:module:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('changeStatus',array('id'=>$vo['module_id']))}"></div>
                        </td>
                        <td>
                            <auth rules="taoke:module:update">
                                <a href="{:url('edit',array('id'=>$vo['module_id']))}?__JUMP__">编辑</a> |
                            </auth>
                            <auth rules="taoke:module:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['module_id']))}?__JUMP__">删除</a>
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