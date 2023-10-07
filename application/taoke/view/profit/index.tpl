<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <auth rules="taoke:profit:delete">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">

                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索用户昵称"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">用户昵称</td>
                <td style="width: 10%;">时间</td>
                <td style="width: 10%;">预估金额</td>
                <td style="width: 10%;">结算金额</td>
                <td style="width: 5%;">团队收入</td>
                <td style="width: 5%;">平级收入</td>
                <td style="width: 5%;">已返利金额</td>
                <td style="width: 5%;">类型</td>
                <td style="width: 5%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>
                            {$vo.nickname}
                        </td>
                        <td>{$vo.create_time|time_format='','Y-m'}</td>
                        <td>{$vo.predict_money}</td>
                        <td>{$vo.settlement_price}</td>
                        <td>{$vo.team_income}</td>
                        <td>{$vo.level_income}</td>
                        <td>{$vo.rebate_money}</td>
                        <td>
                            <if condition="vo.type == 1">
                                平台总收入
                                <else/>
                                用户
                            </if>
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