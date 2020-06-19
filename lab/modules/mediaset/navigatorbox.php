<?$h = "n".time()?>
<div id="<?=$h?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	form.navigator-box{
		outline:1px solid #CCC;
	}
	form.navigator-box>div.box-body{
		width:80vw;
		height:80vh;
		resize:both;
		font-size:0;
	}
	div.box-footer>#select-all-btn{
		padding:0;
		width:24px;
		height:24px;
		font:24px tools;
	}
	</style>
	<form class="box navigator-box white-bg">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption active-bg">&#xf07c;<?include_once("components/movebox.php")?></div>
		<div class="h-bar light-btn-bg">
			<div class="toolbar t">
				<label onclick="<?=$h?>.upload()" title="upload" data-translate="title" class="tool">&#xe905;</label>
				<label onclick="<?=$h?>.createFolder()" title="create folder" data-translate="title" class="tool">&#xe2cc;</label>
				<label onclick="<?=$h?>.remove()" title="remove" data-translate="title" class="tool">&#xe94d;</label>
			</div>
		</div>
		<div class="box-body">
			<iframe id="navigator" width="100%" height="100%" frameborder="no"></iframe>
			<script>
				reauth();
				var options=[];
				var <?=$h?> = document.currentScript.previousElementSibling.contentWindow;
				<?=$h?>.standby = (window.localStorage['navigator'] || "undefined").jsonToObj() || {};
				if(<?=$h?>.standby.subdomain) options.push("subdomain="+<?=$h?>.standby.subdomain);
				if(<?=$h?>.standby[<?=$h?>.standby.subdomain]) options.push("path="+<?=$h?>.standby[<?=$h?>.standby.subdomain]);
				<?=$h?>.location.href="/navigator/folder/image/checkbox?"+options.join("&");
			</script>
		</div>
		<div class="box-footer light-btn-bg" align="right">
			<button id="select-all-btn" title="select all" data-translate="title" name="selectall" class="left transparent-bg dark-txt">&#xe948;</button>

			<button type="submit" class="light-btn-bg" data-translate="textContent">add</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
			form.selectall.onclick=function(event){
				event.preventDefault();
				<?=$h?>.selectAll();
			}
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