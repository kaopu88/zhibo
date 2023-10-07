<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'space_id',
                    title: '广告位',
                    get: '{:url("ad_space/get_spaces")}'
                },
                {
                    name: 'os',
                    title: '投放平台',
                    opts: JSON.parse('{:json_encode(enum_array("ad_os"))}')
                }
            ]
        };
    </script>
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
                    <auth rules="admin:ad_content:add">
                        <a href="{:url('admin/ad_content/add',['space_id'=>$_info['space_id']])}?__JUMP__" class="base_button base_button_s">新增</a>
                    </auth>
                    <auth rules="admin:ad_content:add">
                        <a href="{:url('admin/ad_content/del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                    </auth>
                    <div class="filter_search">
                        <input placeholder="ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="space_id" value="{:input('space_id')}"/>
            <input type="hidden" name="os" value="{:input('os')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">广告位</td>
                <td style="width: 15%;">标题</td>
                <td style="width: 10%;">投放平台</td>
                <td style="width: 10%;">投放时间</td>
                <td style="width: 10%;">浏览权限</td>
                <td style="width: 10%;">效果</td>
                <td style="width: 8%;">状态</td>
                <td style="width: 8%;">创建时间</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.id}</td>
                        <td>
                            <a href="{:url('admin/ad_space/edit',['id'=>$vo['space_info']['id']])}?__JUMP__">{$vo.space_info.name}</a>
                        </td>
                        <td>
                            <div class="thumb">
                                <a title="{$vo.title}" rel="common"
                                   href="{:img_url($vo['image']['common'],'','thumb2')}" class="thumb_img fancybox">
                                    <img src="{:img_url($vo['image']['common'],'200_200','thumb2')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">
                                        {$vo.title}
                                        <notempty name="vo['url']">
                                            <a title="{$vo['url']}" target="_blank" href="{$vo['url']}"><span class="icon-link"></span></a>
                                        </notempty>
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <empty name="vo['os']">
                                <span>全部</span>
                                <else/>
                                <volist name="vo['os']" id="os_item">
                                    <span>{:enum_attr('ad_os',$os_item,'name')}</span>
                                </volist>
                            </empty>
                            <br/>
                            {$vo.code_min}-{$vo.code_max}
                        </td>
                        <td>
                            <switch name="vo['display_status']">
                                <case value="0">
                                    <span class="fc_gray">未开始</span>
                                </case>
                                <case value="1">
                                    <span class="fc_green">显示中</span>
                                </case>
                                <case value="2">
                                    <span class="fc_red">已过期</span>
                                </case>
                            </switch>
                            <br/>
                            {$vo.start_time|time_format}<br/>
                            {$vo.end_time|time_format}
                        </td>
                        <td>
                            <empty name="vo['purview']">
                                <span>全部可见</span>
                                <else/>
                                <volist name="vo['purview']" id="purview_item">
                                    <span>{:enum_attr('ad_purviews',$purview_item,'name')}</span>
                                </volist>
                            </empty>
                        </td>
                        <td>
                            排序值:{$vo.sort}<br/>
                            浏览量:{$vo.pv}<br/>
                            点击量:{$vo.click}
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:ad_content:update')?'0':'1'}" tgradio-on="1" tgradio-off="0" tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('ad_content/change_status',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.create_time|time_format='','date'}</td>
                        <td>
                            <auth rules="admin:ad_content:update">
                                <a href="{:url('ad_content/edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a><br/>
                            </auth>
                            <auth rules="admin:ad_content:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('ad_content/del',array('id'=>$vo['id']))}?__JUMP__">删除</a>
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
        $('.filter_box').searchList(myConfig);
    </script>

</block>