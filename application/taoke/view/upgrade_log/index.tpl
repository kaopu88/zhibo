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
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="status" type="hidden" class="modal_select_value finder" value="{:input('status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="1">已审核</li>
                            <li class="modal_select_option" value="0">未审核</li>
                        </ul>
                    </div>

                    <!--<input name="keyword" class="base_text base_text_s finder" style="width:180px;" value="{:input('keyword')}" placeholder="搜索等级名称"/>-->
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">等级名称</td>
                <td style="width: 5%;">级别</td>
                <td style="width: 5%;">用户</td>
                <td style="width: 25%;">升级条件</td>
                <td style="width: 5%;">审核状态</td>
                <td style="width: 10%;">添加时间</td>
                <td style="width: 10%;">更新时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.level_name}</td>
                        <td>{$vo.level}</td>
                        <td>{$vo.username}</td>
                        <td>
                            未处理
                            <!--<volist name="$vo.upgrade_condition" id="po" key="key">
                            第{$key}级{$po}%
                            </volist>-->
                        </td>
                        <td>{$vo.status == 1 ? '通过' : '待审核'} </td>
                        <td>
                            {$vo.add_time|time_format='','Y-m-d H:i:s'}
                        </td>
                        <td>
                            {$vo.update_time|time_format='','Y-m-d H:i:s'}
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="level/upgrade_pop"/>
    <include file="components/recommend_pop" />
</block>