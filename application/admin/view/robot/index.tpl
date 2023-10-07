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
                        <auth rules="admin:robot:add">
                            <a onclick="redirect_to('{:url('add')}?__JUMP__')" class="base_button base_button_s">新增</a>
                            <input class="base_text" name="robot_num" value="1" style="width: 60px;"/>个机器人
                        </auth>
                        <auth rules="admin:robot:delete">
                            <a href="{:url('delete')}" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">删除</a>
                        </auth>
                    </div>
                    <div class="filter_search">
                        <input placeholder="ID、名称" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">用户信息</td>
                <td style="width: 10%;">性别</td>
                <td style="width: 15%;">生日</td>
                <td style="width: 15%;">地区</td>
                <td style="width: 15%;">创建时间</td>
                <td style="width: 15%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.user_id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.user_id}"/></td>
                        <td>{$vo.user_id}</td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['avatar'],'200_200','avatar')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        用户名：{$vo.username}<br/>
                                        昵称：{$vo.nickname}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            <switch name="vo['gender']">
                                <case value="0">
                                    保密
                                </case>
                                <case value="1">
                                    男
                                </case>
                                <case value="2">
                                    女
                                </case>
                            </switch>
                        </td>
                        <td>
                            {$vo.birthday|time_format='无','date'}
                        </td>
                        <td>
                            {$vo.province_name}<br>
                            {$vo.city_name}<br>
                            {$vo.district_name}
                        </td>
                        <td>
                            {$vo.create_time|time_format='无','date'}
                        </td>
                        <td>
                            <auth rules="admin:robot:update">
                                <a href="{:url('edit',array('user_id'=>$vo['user_id']))}?__JUMP__">编辑</a>
                            </auth>
                            <auth rules="admin:robot:delete">
                                <a class="fc_red" ajax-confirm ajax="get" href="{:url('delete',array('id'=>$vo['user_id']))}">删除</a>
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
        $(function () {
            new SearchList('.filter_box',myConfig);
        });
        function redirect_to(url)
        {
            var robot_num = $('input[name="robot_num"]').val();
            location.href = changeURLArg(url,'robot_num',robot_num);
        }
        /*
        * url 目标url
        * arg 需要替换的参数名称
        * arg_val 替换后的参数的值
        * return url 参数替换后的url
        */
        function changeURLArg(url,arg,arg_val){
            var pattern=arg+'=([^&]*)';
            var replaceText=arg+'='+arg_val;
            if(url.match(pattern)){
                var tmp='/('+ arg+'=)([^&]*)/gi';
                tmp=url.replace(eval(tmp),replaceText);
                return tmp;
            }else{
                if(url.match('[\?]')){
                    return url+'&'+replaceText;
                }else{
                    return url+'?'+replaceText;
                }
            }
            return url+'\n'+arg+'\n'+arg_val;
        }
    </script>

</block>