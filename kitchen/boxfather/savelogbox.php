<style>
.log-box>div.box-body{
	padding:10px;
	min-height:220px;
	font-size:12px;
	line-height:20px;
	white-space:nowrap;
}
.box-caption{
	font-size:16px;
	line-height:36px;
}
.log-box>div.box-body>input{
	width:100%;
	padding:8px;
	margin:5px 0;
	cursor:pointer;
	box-sizing:border-box;
	border:1px solid #BBB;
	border-width:1px 0;
}
.log-box>div.box-body>input:focus{
	background-color:#B2D7FF;
}
</style>
<form class="box log-box white-bg">
	<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
	<div class="box-caption black-bg">&#xe926;</div>
	<div class="h-bar light-btn-bg"></div>
	<div class="box-body">
			
	</div>
	<script>
	(function(form){
		form.onreset=function(){form.drop()}
	})(document.currentScript.parentNode)
	</script>
</form>