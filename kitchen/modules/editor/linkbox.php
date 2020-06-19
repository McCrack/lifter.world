<?$handle = "l:".time()?>
<div id="<?=$handle?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.link-box>div.box-caption{
		font:16px/36px iconset;
	}
	.link-box fieldset{
		border-width:0;
	}
	.link-box input[name='url']{
		width:100%;
		height:30px;
		padding:8px;
		border:1px solid #BBB;
		box-sizing:border-box;
		box-shadow:inset 0 0 8px -4px rgba(0,0,0, .5);
		background-image:linear-gradient(to top, #FFF, #F0F0F0);
	}
	</style>
	<form class="box link-box light-btn-bg" style="width:360px">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption white-bg">&#xe9cb;<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg" data-translate="textContent">create link</div>
		<div class="box-body">
			<br>
			<fieldset><legend>HREF:</legend>
				<input name="url" placeholder="URL" type="url" required>
			</fieldset>
			<fieldset>
				<label class="right"><input type="checkbox" name="nofolow" checked> No Folow</label>
				<small>TARGET:</small> <div class="select">
					<select name="target">
						<option value="_blank">blank</option>
						<option value="_self">self</option>
						<option value="_parent">parent</option>
						<option value="_top">top</option>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="box-footer" align="right">
			<button type="submit" class="light-btn-bg">Ok</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onsubmit=function(){

			}
			form.onreset=function(){form.drop()}
		})(document.currentScript.parentNode);
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