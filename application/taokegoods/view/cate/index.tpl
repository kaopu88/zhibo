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
                <auth rules="taokegoods:cate:add">
                    <li><a href="{:url('add',['pcat_id'=>input('pcat_id'),'cat_id'=>input('cat_id')])}?__JUMP__" class="base_button base_button_s">新增</a></li>
                </auth>
                <!--<auth rules="taokegoods:cate:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>-->
            </ul>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">id</td>
                <td style="width: 5%;">img</td>
                <td style="width: 5%;">分类名</td>
                <td style="width: 5%;">描述</td>
                <td style="width: 5%;">排序</td>
                <td style="width: 5%;">状态</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.cate_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.cate_id}"/></td>
                        <td>{$vo.cate_id}</td>
                        <td>
                            <div class="thumb">
                                <a rel="thumb" href="{:img_url($vo['img'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                    <img src="{:img_url($vo['img'],'200_200','thumb')}"/>
                                </a>
                            </div>
                        </td>
                        <td>{$vo.name}</td>
                        <td>{$vo.desc}</td>
                        <td>{$vo.sort}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taokegoods:cate:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('change_status',array('id'=>$vo['cate_id']))}"></div>
                        </td>
                        <td>
                            <auth rules="taokegoods:cate:update">
                                <a href="{:url('edit',array('id'=>$vo['cate_id']))}?__JUMP__">编辑</a>
                            </auth>
                            <!--<auth rules="taokegoods:cate:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('del',array('id'=>$vo['cate_id']))}?__JUMP__">删除</a>
                            </auth>-->
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