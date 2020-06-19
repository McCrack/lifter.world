<?$handle = "b:".time();?>
<div id="<?=$handle?>" class="mount" style="width:500px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.editorbox>div>textarea{
		color:#555;
		width:100%;
		padding:10px;
		font-size:16px;
		border-width:0;
		min-height:140px;
		box-sizing:border-box;
		resize:vertical;
	}
	.editorbox>div.box-caption{
		font-size:24px;
		line-height:30px;
	}
	</style>
	<form class="box editorbox light-btn-bg">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">📝<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg" data-translate="textContent"><?=ARG_2?></div>
		<div class="box-body">
			<textarea name="field" placeholder="..."></textarea>
		</div>
		<div class="box-footer" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent" name="save" disabled>save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){form.drop()}
			form.oninput=function(){
				form.oninput = null;
				form.save.disabled = false;
			}
		})(document.currentScript.parentNode)
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>