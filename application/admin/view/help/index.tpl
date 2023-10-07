<extend name="public:base_nav2"/>
<block name="css">
    <link rel="stylesheet" type="text/css" href="__ADMIN__/css/help/index.css?v=__RV__"/>
</block>

<block name="js">
	<script>
		
	</script>
</block>

<block name="body">
    <div class="help_body">
        <div class="hero-unit search-box">
            <form method="get" action="{:url('search')}" class="search" role="search">
                <div style="margin:0;padding:0;display:inline">
                </div>
                <input id="query" name="keyword" placeholder="搜索你的问题或关键词..." type="search">
                <input type="submit" value="搜索">
            </form>
        </div>
        <notempty name="_list">
            <volist name="_list" id="vo">
                <div class="page" style="padding-bottom: 50px;">
                    <h1 class="home-title">{$vo.name}</h1>

                    <table class="table-row2-clo3 table-question">
                        <tbody>
                        <?php $length=3;$ceil=ceil($vo['child_num']/$length);
                        for ($ii = 0; $ii < $ceil; $ii++) { ?>
                        <tr>
                            <?php $start=$ii*3;$child_list=array_slice($vo['child_list'],$start,$length);?>
                            <notempty name="child_list">
                            <volist name="child_list" id="xo">
                            <?php $help_list=$xo['help_list'];?>
                            <td>
                                <h3>{$xo.name}
                                <auth rules="admin:help:add">    
                                <a href="{:url('add',['pcat_id'=>$vo['id'],'cat_id'=>$xo['id']])}?__JUMP__"> <span class="icon-plus help-add"></span></a>
                                </auth>
                                </h3>
                                <ul class="question-list">
                                    <notempty name="help_list">
                                    <volist name="help_list" id="mo">	
                                    <li><a class="question-item" href="{:url('detail',['id'=>$mo['id']])}" target="_blank">· {$mo.title}</a><!-- <span class="help-add">{$mo['create_time']|time_format}</span> --></li>
                                    </volist>
                                    </notempty>
                                    <if condition="count($help_list) egt 8">
                                        <li><a style="font-size: 12px;line-height: 30px;display: block;background-color: #F5F5F5;text-align: center;" href="{:url('more',['pcat_id'=>$vo['id'],'cat_id'=>$xo['id']])}">查看更多</a></li>
                                    </if>
                                </ul>
                            </td>
                            </volist>
                            </notempty>
                        </tr>
                        <?php }?>
                        </tbody>
                    </table>

                </div>
            </volist>
        </notempty>
    </div>
</block>

<block name="layer">
</block>