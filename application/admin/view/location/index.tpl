<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'level',
                    title: '区域级别',
                    opts: [
                        {name: '国家', value: '0'},
                        {name: '省', value: '1'},
                        {name: '市', value: '2'},
                        {name: '区县', value: '3'},
                        {name: '街道', value: '4'}
                    ]
                },
                {
                    name: 'province',
                    title: '所在省份',
                    data: {country: 0},
                    auto_sub: false,
                    get: '{:url("common/get_area")}'
                },
                {
                    name: 'city',
                    parent: 'province',
                    title: '所在城市',
                    get: '{:url("common/get_area")}'
                },
                {
                    name: 'district',
                    parent: 'city',
                    title: '所在区县',
                    get: '{:url("common/get_area")}'
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
                        <input placeholder="地图标识点、位置名称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="level" value="{:input('level')}"/>
            <input type="hidden" name="province" value="{:input('province')}"/>
            <input type="hidden" name="city" value="{:input('city')}"/>
            <input type="hidden" name="district" value="{:input('district')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">地图标识点</td>
                <td style="width: 15%;">位置信息</td>
                <td style="width: 10%;">区域级别</td>
                <td style="width: 10%;">标签</td>
                <td style="width: 8%;">经纬度</td>
                <td style="width: 8%;">地区</td>
                <td style="width: 8%;">图片</td>
                <td style="width: 8%;">发布数量</td>
                <td style="width: 8%;">收藏数量</td>
                <td style="width: 10%;">时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>{$vo.poi_id}</td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   target="_blank"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['cover'],'200_200','cover')}"/>
                                </a>
                                <p class="thumb_info">
                                    {$vo.name}
                                </p>
                            </div>
                        </td>
                        <td>{$vo.level_txt}</td>
                        <td>{$vo.tags}</td>
                        <td>
                            经度：{$vo.lat}<br>
                            纬度：{$vo.lng}
                        </td>
                        <td>
                            <notempty name="vo.province_name">{$vo.province_name}<br></notempty>
                            <notempty name="vo.city_name">{$vo.city_name}<br></notempty>
                            <notempty name="vo.district_name">{$vo.district_name}<br></notempty>
                            <notempty name="vo.street_address">{$vo.street_address}</notempty>
                        </td>
                        <td><a href="{:url('cover',['id'=>$vo.id])}">{$vo.cover_num}</a></td>
                        <td>{$vo.publish_num}</td>
                        <td>{$vo.collect_num}</td>
                        <td>
                            {$vo.create_time|time_format='无'}
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