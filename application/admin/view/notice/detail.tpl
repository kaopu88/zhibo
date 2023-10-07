<block name="css">
<!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</block>

<block name="js">

</block>

<block name="body">
<div style="width: 900px;margin: 0 auto;">
	<div class="page-header">
	  <h1>{$_info['title']} <small>{$_info['create_time']|time_format}</small><a href="{:url('edit',['id'=>$_info['id']])}?__JUMP__"></a></h1>
	</div>
	<div class="media">
	  <div class="media-left">
	  </div>
	  <div class="media-body">
	    {:htmlspecialchars_decode($_info['content']);}
	  </div>
	</div>
</div>
</block>

<block name="layer">
</block>