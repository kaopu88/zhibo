<?php /*a:2:{s:64:"/www/wwwroot/zhibb/application/admin/view/bean_log/data_list.tpl";i:1592356646;s:68:"/www/wwwroot/zhibb/application/admin/view/recharge_app/user_info.tpl";i:1624446656;}*/ ?>
<div class="table_slide">
<table class="content_list mt_10">
    <thead>
    <tr>
        <td style="width: 5%;">ID</td>
        <td style="width: 15%;">用户</td>
        <td style="width: 10%;">流水单号</td>
        <td style="width: 15%;">交易内容</td>
        <td style="width: 10%;"><?php echo APP_BEAN_NAME; ?>总额</td>
        <td style="width: 10%;">冻结<?php echo APP_BEAN_NAME; ?></td>
        <td style="width: 10%;">剩余<?php echo APP_BEAN_NAME; ?></td>
        <td style="width: 10%;">客户端内容</td>
        <td style="width: 15%;">创建时间</td>
    </tr>
    </thead>
    <tbody>
    <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <tr data-id="<?php echo htmlentities($vo['id']); ?>">
                <td><?php echo htmlentities($vo['id']); ?></td>
                <td><div class="thumb">
    <a href="<?php echo url('user/detail',['user_id'=>$vo['user']['user_id']]); ?>"
       class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['user']['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['user']['level_name']); ?>" src="<?php echo htmlentities($vo['user']['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('user/detail',['user_id'=>$vo['user']['user_id']]); ?>">
            <?php if($vo['user']['isvirtual'] == '1'): ?>
                <span class="fc_red">[虚拟号]</span><br/>
            <?php endif; ?>
            <?php echo htmlentities(user_name($vo['user'])); ?><br/>
            <?php echo htmlentities((str_hide($vo['user']['phone'],3,4) ?: '未绑定')); ?>
        </a>
    </p>
</div></td>
                <td>
                    <?php echo htmlentities($vo['log_no']); ?>
                </td>
                <td>
                    交易类型：<?php echo htmlentities(enum_name($vo['trade_type'],'trade_types')); ?><br/>
                    交易单号：<?php echo htmlentities($vo['trade_no']); ?><br/>
                    数额：<span class="<?php echo $vo['type']=='inc' ? 'fc_green'  :  'fc_red'; ?>"><?php echo $vo['type']=='inc' ? '+'  :  '-'; ?><?php echo htmlentities($vo['total']); ?></span>
                </td>
                <td>
                    交易前：<?php echo htmlentities($vo['last_total_bean']); ?><br/>
                    交易后：<?php echo htmlentities($vo['total_bean']); ?>
                </td>
                <td>
                    交易前：<?php echo htmlentities($vo['last_fre_bean']); ?><br/>
                    交易后：<?php echo htmlentities($vo['fre_bean']); ?>
                </td>
                <td>
                    交易前：<?php echo htmlentities($vo['last_bean']); ?><br/>
                    交易后：<?php echo htmlentities($vo['bean']); ?>
                </td>
                <td>
                    客户端IP：<?php echo htmlentities($vo['client_ip']); ?><br/>
                    客户端版本：<?php echo htmlentities($vo['app_v']); ?>
                </td>
                <td>
                    <?php echo htmlentities(time_format($vo['create_time'],'Y-m-d H:i')); ?>
                </td>
            </tr>
        <?php endforeach; endif; else: echo "" ;endif; else: ?>
        <tr>
            <td>
                <div class="content_empty">
                    <div class="content_empty_icon"></div>
                    <p class="content_empty_text">暂未查询到相关数据</p>
                </div>
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<div class="pageshow async_container_pages mt_10"><?php echo htmlspecialchars_decode($_page);; ?></div>