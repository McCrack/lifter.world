<?$h = "i".time();?>
<div id="<?=$h?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	form.mediaset-box>div.box-caption{
		font-size:16px;
	}
	form.mediaset-box>div.box-body{
		width:80vw;
		resize:horizontal;
	}
	form.mediaset-box>div.box-body>iframe{
		max-height:80vh;
	}
	</style>
	<form class="box mediaset-box white-bg">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption active-bg">&#xe94b;<?include_once("components/movebox.php")?></div>
		<div class="h-bar dark-btn-bg">
			<div class="toolbar">
				<label title="create mediaset" data-translate="title" class="tool" onclick="<?=$h?>.createSet()">&#xe89c;</label>
				
				<label title="save" data-translate="title" class="tool" onclick="<?=$h?>.save()">&#xf0c7;</label>
				<label title="remove" data-translate="title" class="tool" onclick="<?=$h?>.remove()">&#xe94d;</label>
				
			</div>
		</div>
		<div class="box-body">
			<iframe width="100%" height="800px" frameborder="no"></iframe>
			<script>
			reauth();
			var <?=$h?> = document.currentScript.previousElementSibling.contentWindow;

			<?if(defined("ARG_3")):?>
			<?=$h?>.location.href = "/mediaset/set/<?=ARG_2?>/<?=ARG_3?>";
			<?elseif(defined("ARG_2")):?>
			<?=$h?>.location.href = "/mediaset/set/<?=ARG_2?>";
			<?else:?>
			<?=$h?>.location.href = "/mediaset/set";
			<?endif?>
			</script>
		</div>
		<div class="box-footer light-btn-bg" align="right">
			<button disabled class="light-btn-bg" type="submit" data-translate="textContent">save</button>
			<button class="dark-btn-bg" type="reset" data-translate="textContent">close</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){form.drop();}
			form.onsubmit=function(){
				<?=$h?>.save();
			}
		})(document.currentScript.parentNode);
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