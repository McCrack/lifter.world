<?$handle = "u:".time();?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.log-box>div.box-body{
		padding:10px;
		height:220px;
		overflow:auto;
		font-size:12px;
		line-height:20px;
		white-space:nowrap;
	}
	#progress{
		box-sizing:border-box;
		width:calc(100% - 64px);
	}
	</style>
	<form class="box log-box white-bg" style="width:480px">
		<div class="box-caption black-bg">&#xe905;</div>
		<div class="h-bar light-btn-bg"><progress id="progress" value="0"></progress></div>
		<div class="box-body">
			
		</div>
		<script>
		(function(form){
			location.hash = "<?=$handle?>";
			form.onreset = function(){ form.drop() }
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>