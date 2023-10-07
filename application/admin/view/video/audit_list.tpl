<extend name="public:base_nav"/>
<block name="css">
    <style>
        .film_audit_handler .label_ul {
            display: flex;
            flex-wrap: wrap;
            box-sizing: border-box;
        }

        .film_audit_handler .label_ul * {
            box-sizing: border-box;
        }

        .film_audit_handler .label_ul .label_li {
            flex: 0 1 auto;
            display: block;
            line-height: 25px;
            padding: 2px 5px;
            border: solid 1px #DCDCDC;
            border-radius: 5px;
            margin: 5px;
        }

        .film_audit_handler .label_ul .label_li .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
        }

        .film_audit_handler .label_ul .label_li .icon-remove:hover {
            color: #ed0202;
        }

        .film_audit_handler .label_ul .label_plus {
            border: none;
            font-size: 18px;
            cursor: pointer;
        }

        .film_audit_handler .label_ul .label_plus:hover {
            color: #1D9DFD;
        }

    </style>
</block>

<block name="js">
    <script src="https://webapi.amap.com/maps?v=1.4.8&key=0d29625c9a07fbc35067cc31b0b30489"></script>
    <script src="__VENDOR__/raty/jquery.raty.min.js?v=__RV__"></script>
    <script src="__JS__/video/audit.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20 p-0">
        <include file="components/tab_nav"/>
        <div class="table_slide bg_container">
            <table class="content_list mt_10 audit_list md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 11%;">视频封面</td>
                    <td style="width: 11%;">视频描述</td>
                    <td style="width: 9%;">视频属性</td>
                    <td style="width: 8%;">商品</td>
                    <td style="width: 10%;">发布用户</td>
                    <td style="width: 10%;">标签</td>
                    <td style="width: 10%;">审核状态</td>
                    <td style="width: 10%;">申请时间</td>
                    <td style="width: 10%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <include file="video/video_info"/>
                            </td>
                            <td>
                                <notempty name="vo['city_name']">
                                    <span class="fc_orange">【{$vo.city_name}】</span>
                                </notempty>
                                {$vo.describe|default='未填写'}
                            </td>
                            <td>
                                宽高：{$vo.width}*{$vo.height}<br/>
                                大小：{$vo.file_size_str}<br/>
                                来源：
                                <include file="video/source"/>
                                <br/>
                                版权：
                                <eq name="vo['copy_right']" value="0">
                                    <span class="fc_green">无标识</span>
                                    <else/>
                                    <span class="fc_red">有标识</span>
                                </eq>
                                <br/>
                                人评：{$vo.rating}<br/>
                                总分：{$vo.score}
                            </td>
                            <td>
                                <notempty name="vo['goods']">
                                    <img src="{$vo['goods']['img']}" style="width: 80px;height: 80px"/>
                                    <Br>
                                    {$vo['goods']['short_title']}
                                    <else/>
                                    暂无
                                </notempty>

                            </td>
                            <td>
                                <include file="components/vo_user"/>
                            </td>
                            <td>
                                <if condition="!empty($vo['location_lng']) AND !empty($vo['location_lat'])">
                                    位置：{$vo.location_name|short=20}
                                    <a class="check_location" location-name="发布位置" location-lng="{$vo.location_lng}"
                                    location-lat="{$vo.location_lat}" href="javascript:;"><span
                                            class="icon-location2"></span> </a>
                                </if>
                                <br/>
                                标签：{$vo.tag_names}
                            </td>
                            <td>
                                <include file="components/vo_audit_status"/>
                            </td>
                            <td>
                                申请：{$vo.create_time|time_format}<br/>
                                处理：{$vo.audit_time|time_format='未处理'}
                            </td>
                            <td>
                                <switch name="vo['audit_status']">
                                    <case value="0">
                                    </case>
                                    <case value="1">
                                        <auth rules="admin:film:audit">
                                            <a data-id="id:{$vo.id}" poplink="film_audit_handler"
                                            href="javascript:;" class="repair_font">审核</a>
                                        </auth>
                                        <br/>
                                        <a data-query="id={$vo.id}&type=audit_film" poplink="task_transfer_box"
                                        href="javascript:;" class="optimize_font">转交</a>
                                    </case>
                                    <case value="2">
                                        原因：{$vo.reason}
                                    </case>
                                </switch>
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
</block>

<block name="layer">
    <include file="video/film_audit_handler"/>
    <include file="components/task_transfer_box"/>
</block>