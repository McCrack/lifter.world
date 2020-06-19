<?$h = "n".time()?>
<div id="<?=$h?>" class="mount" style="width:540px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.customizer-box>div.box-body{
		font-size:0;
		max-height:80vh;
		min-height:200px;
	}
	.customizer-box>div.box-body>iframe{
	
	}
	</style>
	<form class="box customizer-box body-bg">
		<button type="reset" class="close-btn light-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption active-bg">&#xe993;<?include_once("components/movebox.php")?></div>
		<div class="h-bar light-txt" data-translate="textContent">customizer</div>
		<div class="box-body">
			<input type="hidden" name="selected">
			<iframe src="" width="100%" frameborder="no"></iframe>
			<script>
			reauth();
			var embed = document.currentScript.previousElementSibling;
				embed.contentWindow.location.href="/customizer/embed/<?=ARG_2?>";
				embed.onload=function(){
					embed.height = embed.contentWindow.document.body.scrollHeight
				}
			</script>
		</div>
		<div class="box-footer" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent">save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
			form.addEventListener("submit",function(){
				embed.contentWindow.submit();
				setTimeout(form.drop, 100);
			});
		})(document.currentScript.parentNode)
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$h?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>