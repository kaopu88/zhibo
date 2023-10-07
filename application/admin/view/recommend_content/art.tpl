<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'rec_id',
                    title: '推荐位',
                    get: '{:url("recommend_content/get_rec",array('type'=>'art'))}'
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
                    <div style="float: left">
                        <a href="{:url('del_recommend')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">取消推荐</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="搜索ID、标题" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="rec_id" value="{$get.rec_id}" />
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 md_width">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">推荐位</td>
                <td style="width: 20%;">标题</td>
                <td style="width: 15%;">所属类目</td>
                <td style="width: 15%;">发布者</td>
                <td style="width: 10%;">排序</td>
                <td style="width: 20%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.id}
                            <notempty name="vo['mark']">
                                <br/>{$vo.mark}
                            </notempty>
                        </td>
                        <td>{$vo.rs_name}</td>
                        <td>
                            <notempty name="vo['title']">
                            <div class="thumb">
                                <a href="#" class="thumb_img">
                                    <img src="{:img_url($vo['image'],'200_200','thumb')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="#">
                                        {$vo.title}
                                    </a>
                                </p>
                            </div>
                            <else/><span class="fc_red">此文章已删除</span>
                            </notempty>
                        </td>
                        <td>
                            <span>{$vo.pcat_info.name}</span><br/>
                            <span>{$vo.cat_info.name}</span>
                        </td>
                        <td>admin</td>
                        <td>{$vo.sort}</td>
                        <td>
                            <a data-query="id={$vo.id}&sort={$vo.sort}" poplink="sort_handler"
                            href="javascript:;">修改排序</a><br/>
                            <a class="fc_red" ajax-confirm ajax="get" href="{:url('del_recommend',array('id'=>$vo['id']))}?__JUMP__">取消推荐</a>
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
        $(function () {
            new SearchList('.filter_box',myConfig);
        });
    </script>

</block>

<block name="layer">
    <include file="recommend_content/sort_handler"/>
    <include file="components/recommend_pop" />
</block>