<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <div class="content_toolbar_search">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="type" type="hidden" class="modal_select_value finder" value="类型"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">wap</li>
                            <li class="modal_select_option" value="2">微信</li>
                            <li class="modal_select_option" value="3">APP</li>
                        </ul>
                    </div>

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="状态"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="0">不显示</li>
                            <li class="modal_select_option" value="1">显示</li>
                        </ul>
                    </div>

            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">专题名称</td>
                <td style="width: 20%;">banner图片</td>
                <td style="width: 20%;">介绍</td>
                <td style="width: 10%;">banner图是否显示</td>
                <td style="width: 10%;">状态</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.name}</td>
                        <td>
                            <div class="thumb">
                                <img src="{$vo['img']}"/>
                            </div>
                        </td>
                        <td>{$vo.intro}</td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:special:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.banner_status}" tgradio-name="banner_status" tgradio="{:url('changeBannerStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <div tgradio-not="{:check_auth('taoke:special:update')?'0':'1'}" tgradio-on="1" tgradio-off="0"
                                 tgradio-value="{$vo.status}" tgradio-name="status" tgradio="{:url('changeStatus',array('id'=>$vo['id']))}"></div>
                        </td>
                        <td>
                            <auth rules="taoke:special:update">
                                <a href="{:url('edit',array('id'=>$vo['id']))}?__JUMP__">编辑</a>
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