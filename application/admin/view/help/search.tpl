<extend name="public:base_nav2"/>
<block name="css">
<!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="__ADMIN__/css/help/index.css?v=__RV__"/>
</block>

<block name="js">

</block>

<block name="body">
	<div class="hero-unit search-box">
        <form method="get" action="" class="search" role="search">
            <div style="margin:0;padding:0;display:inline">
            </div>
            <input id="query" name="keyword" placeholder="搜索你的问题或关键词..." type="search" value="{$keyword}">
            <input type="submit" value="搜索" style="padding: 14px 52px;">
        </form>
    </div>
	<div style="padding: 25px 25%;">
	<ul class="list-group">
	  <notempty name="_list">
	  <volist name="_list" id="vo">
	  <li class="list-group-item list-group-item-success"><a class="question-item" href="{:url('detail',['id'=>$vo['id']])}" target="_blank">· 【{$vo.pcat_name}-{$vo.cat_name}】{$vo.title}</a><!-- <span style="float: right;margin-right: 10px;">{$vo['create_time']|time_format}</span> --></li>
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