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
                <auth rules="admin:packages:add">
                    <li><a href="{:url('add')}?__JUMP__"
                           class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="admin:packages:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a></li>
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
                        <input name="channel" type="hidden" class="modal_select_value finder" value="{:input('channel')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部渠道</li>
                            <volist name=":enum_array('packages_channel')" id="channel">
                                <li class="modal_select_option" value="{$channel.value}">{$channel.name}</li>
                            </volist>
                        </ul>
                    </div>
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="os" type="hidden" class="modal_select_value finder" value="{:input('os')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部平台</li>
                            <volist name=":enum_array('packages_os')" id="os">
                                <li class="modal_select_option" value="{$os.value}">{$os.name}</li>
                            </volist>
                        </ul>
                    </div>
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="update_type" type="hidden" class="modal_select_value finder" value="{:input('update_type')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部类型</li>
                            <volist name=":enum_array('packages_update_types')" id="update_type">
                                <li class="modal_select_option" value="{$update_type.value}">{$update_type.name}</li>
                            </volist>
                        </ul>
                    </div>
                    <input name="code" class="base_text base_text_s finder" style="width:180px;" value="{:input('code')}" placeholder="内部版本号"/>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索ID、包名"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 12%;">安装包名</td>
                <td style="width: 8%;">渠道</td>
                <td style="width: 6%;">版本号</td>
                <td style="width: 8%;">运行平台</td>
                <td style="width: 6%;">下载量</td>
                <td style="width: 6%">文件大小</td>
                <td style="width: 6%;">更新类型</td>
                <td style="width: 9%;">最低版本</td>
                <td style="width: 9%;">状态</td>
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
                            {$vo.name}<br/>
                            <notempty name="vo['url']">
                                <a title="点击跳转到第三方地址" target="_blank" href="{$vo.url}">第三方地址</a>
                            </notempty>
                            <notempty name="vo['file_path']">
                                <a title="点击下载安装包" class="fc_green" target="_blank" href="{$vo.file_path}">安装包</a>
                            </notempty>
                        </td>
                        <td>{$vo.channel|enum_name='packages_channel'}</td>
                        <td>
                            <b>[ {$vo.code}]</b><br/>
                            {$vo.version}
                        </td>
                        <td>{$vo.os|enum_name='packages_os'}</td>
                        <td>{$vo.download_num}</td>
                        <td>{$vo.filesize_str}</td>
                        <td>{$vo.update_type|enum_name='packages_update_types'}</td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:packages:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.min_version}" tgradio-name="min_version"
                                 tgradio-on-name="启用" tgradio-off-name="普通"
                                 tgradio="{:url('change_min_version',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:packages:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.create_time|time_format}</td>
                        <td>
                            <auth rules="admin:packages:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a><br/>
                            </auth>
                            <auth rules="admin:packages:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id']))}">删除</a>
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