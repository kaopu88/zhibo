<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taokeshop:shop:delete">
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

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索小店标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
         <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">用户</td>
                <td style="width: 10%;">店铺标题</td>
                <td style="width: 5%;">店铺描述</td>
                <td style="width: 5%;">背景图片</td>
                <td style="width: 25%;">其他图片</td>
                <td style="width: 5%;">店铺状态</td>
                <td style="width: 5%;">创建时间</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.nickname}</td>
                        <td>
                            <notempty name="$vo.title">
                                {$vo.title}
                                <else/>
                                暂无
                            </notempty>
                        </td>
                        <td>
                            <notempty name="$vo.desc">
                                {$vo.desc}
                                <else/>
                                暂无
                            </notempty>
                        </td>
                        <td>
                            <div class="thumb">
                                <notempty name="$vo.bg_img">
                                    <a rel="thumb" href="{:img_url($vo['bg_img'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($vo['bg_img'],'200_200','thumb')}"/>
                                    </a>
                                </notempty>
                            </div>
                        </td>
                        <td>
                            <div class="thumb">
                                <notempty name="$vo.images">
                                    <volist name="$vo.images" id="vm" key="k">
                                        <img src="{:img_url($vm,'67_90','thumb')}"/>
                                    </volist>
                                </notempty>
                            </div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('taokeshop:shop:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('changeStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            {$vo.create_time|time_format='','Y-m-d H:i:s'}
                        </td>
                        <td>
                            <!--<auth rules="taokeshop:shop:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a><br/>
                            </auth>-->
                            <auth rules="taokeshop:shop:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['id'], "user_id"=>$vo['user_id']))}?__JUMP__">删除</a>
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