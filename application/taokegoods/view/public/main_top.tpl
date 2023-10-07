
    <div class="main_nav">
        <div class="flex_between">
            <div class="flex">
                <div class="main_top_logo">
                    <a href="{:url('index/index')}">
                        <span>{:config('site.company_full_name')}</span>
                    </a>
                </div>
                <div class="icon_more"></div>
                <div class="menu_icon" title="主菜单"></div>
            </div>
            <ul class="tool_list">
                <li><a title="网站首页" target="_self" href="{:url('/admin/index/index')}"><span class="icon-home"></span></a>
                </li>
                <li>
                    <a poplink="work_box" title="任务设置" href="javascript:;">
                        <span class="icon-light-bulb"></span>&nbsp;<span style="font-size: 12px;"
                                                                        class="badge_work_num">0</span>
                    </a>
                </li>
                <li><a title="退出" ajax-confirm="是否确认退出？" confirm ajax="get" href="{:url('/admin/account/logout')}"><span
                                class="icon-exit"></span></a></li>
                <li>
                    <!--  <a title="消息" href="javascript:;">
                            <span class="icon-envelope"></span>
                            <span class="message_badge">0</span>
                        </a>-->
                    <ul class="message_list">
                        <li><a href="javascript:;"><span class="icon-remove"></span>清空消息</a></li>
                        <li><a href="javascript:;"><span class="icon-plus"></span>查看更多</a></li>
                    </ul>
                </li>

                <li class="avatar_link">
                    <a title="{$admin|user_name}" href="javascript:;">
                        <div class="main_top_avatar"><img
                                    src="{:img_url($admin['avatar'],'200_200','admin_avatar')}"/></div>
                    </a>
                    <ul class="avatar_list">
                        <li><a href="{:url('/admin/personal/base_info')}">{$admin|user_name}</a></li>
                        <li><a href="{:url('/admin/personal/change_pwd')}">修改密码</a></li>
                        <li><a poplink="work_box" href="javascript:;">任务设置</a></li>
                        <li><a ajax-confirm ajax="get" href="{:url('/admin/account/logout')}">退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="main_top_content">
        <div class="main_top_progress"></div>
        <div class="main_top_bar">
            <ul class="nav_list">
                <volist name="admin_tree[0]['children']" id="menu1">
                    <li class="pitch"><a class="{$menu1.current}" target="{$menu1.target}"
                                         href="{$menu1.url ? $menu1.menu_url : $menu1["children"][0]["children"][0]["menu_url"]}">
                            {$menu1.name}<span unread-types="{$menu1.badge}" class="badge_unread">0</span>
                        </a></li>
                </volist>
            </ul>
        </div>
    </div>
