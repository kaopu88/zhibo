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
                    name: 'type',
                    title: '性质',
                    opts: [
                        {name: '小礼物', value: '1'},
                        {name: '大礼物', value: '0'}
                    ]
                },
                {
                    name: 'cid',
                    title: '类型',
                    opts: [
                        {name: '直播间礼物', value: '0'},
                        {name: '视频礼物', value: '1'},
                        {name: '道具礼物', value: '10'}
                    ]
                }
            ]
        };
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
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
                    <div style="float: left">
                        <auth rules="admin:gift:add">
                            <a class="base_button base_button_s" href="{:url('gift/add')}?__JUMP__">新增礼物</a>
                        </auth>
                        <auth rules="admin:gift:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{:input('status')}"/>
            <input type="hidden" name="type" value="{:input('type')}"/>
            <input type="hidden" name="cid" value="{:input('cid')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">类型</td>
                <td style="width: 10%;">标题</td>
                <td style="width: 10%;">礼物信息</td>
                <td style="width: 5%;">启用状态</td>
                <td style="width: 5%;">是否守护礼物</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 10%;">参数</td>
                <td style="width: 5%;">礼物特权</td>
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
                        <td>{$vo.cid_str}</td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['picture_url'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['picture_url'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.name}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            单价：{$vo.price}<br/>
                            折扣：{$vo.discount_str}<br/>
                            等值{:config('app.product_info.bean_name')}：{$vo.conv_millet}<br/>
                            总销量：{$vo.sales}<br/>
                            性质：{$vo.type_str}<br/>
                            点击提示信息：{$vo.tips}
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('admin:gift:update')?'0':'1'}"
                                 tgradio-value="{$vo.status}" tgradio-name="status"
                                 tgradio="{:url('change_status',array('id'=>$vo['id']))}"></div>
                        </td>

                        <td>
                            <div tgradio-not="{:check_auth('admin:gift:update')?'0':'1'}" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="{$vo.isguard}"
                                 tgradio-name="isguard" tgradio-on-name="是" tgradio-off-name="否"
                                 tgradio="{:url('change_guard',['id'=>$vo['id']])}"></div>
                        </td>
                        <td>{$vo.sort}</td>
                        <td>{$vo.show_params}</td>
                        <td>{$vo.privileges_str}</td>
                        <td>{$vo.create_time|time_format='无','date'}</td>
                        <td>
                            <auth rules="admin:gift:update">
                                <a data-id="gift_id:{$vo.id}" poplink="badge_box" href="javascript:;">
                                    礼物角标
                                </a><br/>
                            </auth>
                            <auth rules="admin:gift:update">
                                <a href="{:url('edit',['id'=>$vo.id])}?__JUMP__">编辑礼物</a><br/>
                            </auth>
                            <auth rules="admin:gift:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('delete',array('id'=>$vo['id']))}?__JUMP__">删除礼物</a>
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
</block>

<block name="layer">
    <include file="gift/update_badge"/>
</block>
