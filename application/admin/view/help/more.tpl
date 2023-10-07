<extend name="public:base_nav2"/>
<block name="css">
<!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="__ADMIN__/css/help/index.css?v=__RV__"/>
</block>

<block name="js">

</block>

<block name="body">
	<ol class="breadcrumb">
		<li><a href="{:url('help/index')}">文档首页</a></li>
	  <li><a href="#">{$pcat_name['name']}</a></li>
	  <li><a href="#">{$cat_name['name']}</a></li>
	</ol>
	<div style="padding: 0 25%;">
	<ul class="list-group">
	  <notempty name="_list">
	  <volist name="_list" id="vo">
	  <li class="list-group-item list-group-item-success"><a class="question-item" href="{:url('detail',['id'=>$vo['id']])}" target="_blank">· {$vo.title}</a><!-- <span style="float: right;margin-right: 10px;">{$vo['create_time']|time_format}</span> --></li>
	  </volist>
	  <else/>
        <div class="content_empty">
            <div class="content_empty_icon"></div>
            <p class="content_empty_text">暂未查询到相关数据</p>
        </div>
	  </notempty>
	</ul>
	<div class="pageshow mt_10" style="float: right;">{:htmlspecialchars_decode($_page);}</div>
	</div>
</block>

<block name="layer">
</block>