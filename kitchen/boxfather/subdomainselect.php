<style>
	div.alert-box{
	display:flex;
	flex-wrap:wrap;
	align-items:center;
	justify-content:center;
}
div.alert-box>output{
	font-size:22px;
}
div.alert-box>input[name='field']{
	width:100%;
	padding:10px;
	margin:8px 0;
	border-width:0;
	text-align:center;
	box-sizing:border-box;
}
</style>
<form class="box <?=implode(' ', $classes)?>" style="width:360px;">
	<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
	<div class="h-bar">
		<script>
		(function(bar){
			bar.onmousedown=function(event){
				event.preventDefault();
				var form = bar.parentNode,
					y = event.clientY - form.offsetTop,
					x = event.clientX - form.offsetLeft;
				document.onmousemove=function(event){
					let top = event.clientY - y;
					let left = event.clientX - x;
					form.style.top = (top > 0) ? top+"px" : "0";
					form.style.left = (left > 0) ? left+"px" : "0";
				}
				document.onmouseup=function(){document.onmousemove = null;}
			}
		})(document.currentScript.parentNode)
		</script>
	</div>
	<div class="box-body alert-box" align="center">
		<output>Choose Subdomain</output>:
		<div class="select white-bg" style="margin-left:5px;height:28px">
			<select name="subdomain" class="dark-txt" style="font-size:15px;text-align:center">
				<?foreach(glob("../*", GLOB_ONLYDIR) as $path): $subdomain=basename($path)?>
				<option <?if($subdomain==BASE_FOLDER):?>selected<?endif?> value="<?=$subdomain?>"><?=$subdomain?></option>
				<?endforeach?>
			</select>
		</div>
	</div>
	<div class="box-footer" align="right">
		<button class="light-btn-bg" type="submit" data-translate="textContent">apply</button>
	</div>
	<script>
	(function(form){
		form.onreset=function(){form.drop()}
		form.addEventListener("submit",function(){
			COOKIE.set("subdomain", form.subdomain.value, {expires:2592000,paht:"/"});
			location.reload();
		});
	})(document.currentScript.parentNode);
	</script>
</form>