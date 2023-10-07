<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '状态',
                    opts: [
                        {name: '待审核', value: '0'},
                        {name: '已通过', value: '1'},
                        {name: '未通过', value: '2'}
                    ]
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
                    <div class="filter_search">
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="{$get.status}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 15%;">用户信息</td>
                    <!--                <td style="width: 15%;">基本信息</td>-->
                    <td style="width: 15%;">证件信息</td>
                    <td style="width: 10%;">身份证正面</td>
                    <td style="width: 10%;">身份证反面</td>
                    <td style="width: 10%;">手持身份证</td>
                    <td style="width: 10%;">来源</td>
                    <td style="width: 10%;">处理描述</td>
                    <td style="width: 20%;">审核状态</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <include file="recharge_app/user_info"/>
                            </td>
                            <!--                <td>
                            生日：<a href="javascript:;">{$vo.birth}</a><br/>
                            性别：<a href="javascript:;">{$vo.sex_str}</a><br/>
                            行政编码：<a href="javascript:;">{$vo.addr_code}</a><br/>
                            地址：<a href="javascript:;">{$vo.province}{$vo.city}{$vo.area}{$vo.addr}</a>
                        </td>-->
                            <td>
                                真实姓名：<a href="javascript:;">{$vo.name}</a><br/>
                                证件类型：<a href="javascript:;">{$vo.idcard_type_str}</a><br/>
                                证件号：<a href="javascript:;">{$vo.card_num}</a>
                            </td>
                            <td>
                                <div class="thumb">
                                    <a rel="thumb" href="{:img_url($vo['front_idcard'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($vo['front_idcard'],'200_200','thumb')}"/>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="thumb">
                                    <a rel="thumb" href="{:img_url($vo['back_idcard'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($vo['back_idcard'],'200_200','thumb')}"/>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="thumb">
                                    <a rel="thumb" href="{:img_url($vo['hand_idcard'],'','thumb')}" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                        <img src="{:img_url($vo['hand_idcard'],'200_200','thumb')}"/>
                                    </a>
                                </div>
                            </td>

                            <td>
                                <switch name="vo['is_anchor']">
                                    <case value="0">
                                        会员中心实名
                                    </case>
                                    <case value="1">
                                        主播申请实名
                                    </case>
                                </switch>
                            </td>

                            <td>
                                <switch name="vo['status']">
                                    <case value="0">
                                        待审核
                                    </case>
                                    <case value="1"><a href="javascript:;" class="fc_green">已通过</a>
                                    </case>
                                    <case value="2">
                                        <a href="javascript:;" class="fc_red">未通过</a>
                                    </case>
                                </switch>
                                <br/><notempty name="vo['audit_admin']">
                                    <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                    <else/>
                                    <if condition="$vo.status == '1'">
                                        {$vo.handle_desc}
                                        <else/>
                                        未分配
                                    </if>
                                </notempty>
                            </td>
                            <td>
                                申请：{$vo.create_time|time_format}<br/>
                                处理：<if condition="$vo.handle_time != '0'">{$vo.handle_time|time_format='未处理'}<else/>未处理</if>
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