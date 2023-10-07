<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taoke:collect:delete">
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

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索商品标题"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">商品ID</td>
                <td style="width: 10%;">用户昵称</td>
                <td style="width: 10%;">标题</td>
                <td style="width: 5%;">图片</td>
                <td style="width: 5%;">店铺类型</td>
                <td style="width: 5%;">价格</td>
                <td style="width: 5%;">销量</td>
                <td style="width: 10%;">收藏时间</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.goods_id}</td>
                        <td>{$vo.nickname}</td>
                        <td>{$vo.title}</td>
                        <td>
                            <div class="thumb">
                                <img src="{:img_url($vo['img'],'200_200','thumb')}"/>
                            </div>
                        </td>
                        <td>
                            <switch name="vo['shop_type']">
                                <case value="B">
                                    天猫
                                </case>
                                <case value="C">
                                    淘宝
                                </case>
                                <case value="P">
                                    拼多多
                                </case>
                                <case value="J">
                                    京东
                                </case>
                            </switch>
                        </td>
                        <td>{$vo.price}</td>
                        <td>{$vo.volume}</td>
                        <td>
                            {$vo.add_time|time_format='','Y-m-d H:i:s'}
                        </td>
                        <td>
                            <auth rules="taoke:collect:delete">
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