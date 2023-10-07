<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <li><a href="#" class="base_button base_button_s" onclick="syncAds()">一键同步</a></li>
                <li><a href="#" class="base_button base_button_s" onclick="delOuttime()">一键清除过期计划</a></li>
                <auth rules="taoke:duomai:add">
                    <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="taoke:duomai:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
        </div>

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

                <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索广告id或名称"/>
                <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
            </div>
        </div>
        <div class="table_slide">

        <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 4%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 4%;">广告id</td>
                <td style="width: 8%;">广告名称</td>
                <td style="width: 10%;">logo</td>
                <td style="width: 5%;">佣率</td>
                <td style="width: 10%;">广告url</td>
                <td style="width: 10%;">过期时间</td>
                <td style="width: 25%;">描述</td>
                <td style="width: 6%;">状态</td>
                <td style="width: 6%;">置顶</td>
                <td style="width: 5%;">结算周期</td>
                <td style="width: 7%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.ads_id}</td>
                        <td>{$vo.ads_name}</td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['site_logo'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['site_logo'],'200_200','thumb')}"/>
                                </a>
                            </div>
                        </td>
                        <td>{$vo.ads_commission}</td>
                        <td>{$vo.site_url}</td>
                        <td>{$vo.ads_endtime|time_format='','Y-m-d H:i:s'}</td>
                        <td>{$vo.site_description}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:duomai:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('changeStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:duomai:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.is_top}" tgradio-name="is_top" tgradio="{:url('setTop',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>{$vo.charge_period}</td>
                        <td>
                            <auth rules="taoke:duomai:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a> |
                            </auth>
                            <auth rules="taoke:duomai:delete">
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

        function syncAds(){
            var loading = layer.load(0, {
                shade: false,
                time: 2*1000
            });
            $.ajax({
                url: "{:url('syncExpended')}",
                dataType: "json",
                type: "POST",
                data: {},
                success:function(data){
                    if(data.status == 0){
                        layer.msg("同步完成", {
                            icon: 1,
                            time: 2000
                        }, function(){
                            window.location.reload();
                        });
                    }else{
                        layer.msg(data.message);
                    }
                    layer.close(loading);
                }
            });
        }

        function delOuttime(){
            $.ajax({
                url: "{:url('delExpired')}",
                dataType: "json",
                type: "POST",
                data: {},
                success:function(data){
                    if(data.status == 0){
                        layer.msg(data.message, {
                            icon: 1,
                            time: 2000
                        }, function(){
                            window.location.reload();
                        });
                    }else{
                        layer.msg(data.message);
                    }
                }
            });
        }
    </script>

</block>

<block name="layer">
    <include file="components/recommend_pop" />
</block>