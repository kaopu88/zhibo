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
                <auth rules="admin:timer:management">
                    <li><a href="{:url('add',['type'=>input('type')])}?__JUMP__"
                           class="base_button base_button_s">新增</a></li>
                </auth>
                <auth rules="admin:timer:management">
                    <li><a href="{:url('del')}" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a></li>
                </auth>
            </ul>
            <div class="content_toolbar_search">
                <div class="base_group">
                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="sort" type="hidden" class="modal_select_value finder"
                               value="{:input('sort')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="asc">时间正序</li>
                            <li class="modal_select_option" value="desc">时间倒序</li>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="KEY(必须一致)"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 sm_width" style="min-width:600px;">
            <thead>
            <tr>
                <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%">KEY</td>
                <td style="width: 10%">定时时间</td>
                <td style="width: 15%">回调地址</td>
                <td style="width: 20%">回调数据</td>
                <td style="width: 10%">循环参数</td>
                <td style="width: 10%">已回调</td>
                <td style="width: 10%">添加时间</td>
                <td style="width: 10%">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr>
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.key}"/></td>
                        <td>{$vo.key}</td>
                        <td>
                            {$vo.trigger_time|date='Y-m-d H:i:s'}<br/>
                            <gt name="vo['diff']" value="0">
                                <span class="fc_green">{$vo.trigger_str}</span>
                                <else/>
                                <span class="fc_red">{$vo.trigger_str}</span>
                            </gt>
                        </td>
                        <td>
                            [{$vo.method}]<br/>
                            {$vo.url}
                        </td>
                        <td>{$vo.data}</td>
                        <td>
                            <switch name="vo['cycle']">
                                <case value="-1">无限循环</case>
                                <case value="0">不循环</case>
                                <default>循环{$vo['cycle']}次</default>
                            </switch>
                            <neq name="vo['cycle']" value="0">
                                <br/>每{$vo.interval}s一次
                            </neq>
                        </td>
                        <td>{$vo.trigger_num}次</td>
                        <td>
                            {$vo.add_time|time_format}
                        </td>
                        <td>
                            <auth rules="admin:timer:management">
                                <a href="{:url('timer/edit',array('key'=>$vo['key']))}?__JUMP__">编辑</a><br/>
                            </auth>
                            <auth rules="admin:timer:management">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('timer/del',array('id'=>$vo['key']))}">删除</a>
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