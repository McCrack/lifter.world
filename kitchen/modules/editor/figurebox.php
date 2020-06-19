<?$h = "n".time()?>
<div id="<?=$h?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	form.figure-box{
		outline:1px solid #CCC;
	}
	form.figure-box>div.box-caption{
		font-size:16px;
	}
	form.figure-box>div.box-body{
		position:relative;
		padding-bottom:56.25%;
		box-sizing:border-box;
	}
	form.figure-box>div.box-body>img,
	form.figure-box>div.box-body>iframe{
		top:0;
		left:0;
		width:100%;
		height:100%;
		position:absolute;
		object-fit:contain;
	}
	form.figure-box>div.box-body>img{
		z-index:2;
		transition:top .5s;
	}
	form.figure-box>div.box-body:hover>img{
		top:-100%;
	}
	form.figure-box>div.box-footer>input{
		padding:6px;
		height:30px;
		border-radius:3px;
		border:1px solid #CCC;
		vertical-align:middle;
		box-sizing:border-box;
		width:calc(50% - 105px);
	}
	</style>
	<form class="box figure-box light-btn-bg" style="width:760px">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption active-bg">&#xe927;<?include_once("components/movebox.php")?></div>
		<div class="h-bar light-btn-bg">
			<div class="toolbar t">
				<label onclick="<?=$h?>.contentWindow.upload()" title="upload" data-translate="title" class="tool">&#xe905;</label>
				<label onclick="<?=$h?>.contentWindow.createFolder()" title="create folder" data-translate="title" class="tool">&#xe2cc;</label>
				<label onclick="<?=$h?>.contentWindow.remove()" title="remove" data-translate="title" class="tool">&#xe94d;</label>
			</div>
		</div>
		<div class="box-body">
			<img src="/images/NIA.jpg" class="body-bg">
			<iframe width="100%" height="100%" frameborder="no"></iframe>
			<script>
				reauth();
				var options=[];
				var <?=$h?> = document.currentScript.previousElementSibling;
				<?=$h?>.standby = (window.localStorage['navigator'] || "undefined").jsonToObj() || {};
				if(<?=$h?>.standby.subdomain) options.push("subdomain="+<?=$h?>.standby.subdomain);
				if(<?=$h?>.standby[<?=$h?>.standby.subdomain]) options.push("path="+<?=$h?>.standby[<?=$h?>.standby.subdomain]);
				<?=$h?>.contentWindow.location.href="/navigator/folder/image/radio?"+options.join("&");
				<?=$h?>.onload=function(){
					<?=$h?>.contentWindow.onchange=function(event){
						if(event.target.name=="files-on-folder"){
							<?=$h?>.previousElementSibling.src=event.target.value;
						}
					}
				}
			</script>
		</div>
		<div class="box-footer" align="justify">
			<input name="caption" placeholder="Caption">
			<input name="link" placeholder="Link">
			<button type="submit" class="light-btn-bg" data-translate="textContent">insert</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
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