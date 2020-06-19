<?$handle = "b:".time()?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.prompt-box>div.box-caption{
		font-size:18px;
		line-height:28px;
	}
	.prompt-box>div.box-body>input,
	.prompt-box>div.box-body>div.select{
		height:30px;
		vertical-align:middle;
		box-shadow:inset 0 0 6px 0 rgba(0,0,0, .5);
		background-image:linear-gradient(to top, #FFF, #EEE);
	}
	.prompt-box>div.box-body>div.select>select{
		max-width:120px;
	}
	.prompt-box>div.box-body>input{
		padding:8px;
		border-width:0;
		text-align:center;
		box-sizing:border-box;
		width:calc(100% - 150px);
	}
	</style>
	<form class="box prompt-box dark-btn-bg" style="width:360px;">
		<div class="box-caption light-btn-bg">Create Schema</div>
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
		<div class="box-body" align="center">
			<br><br>
			<div class="select">
				<select name="subdomain">
					<?foreach(glob("../*", GLOB_ONLYDIR) as $dir): $subdomain=basename($dir)?>
					<option <?if($subdomain==BASE_FOLDER):?>selected<?endif?> value="<?=$subdomain?>"><?=$subdomain?></option>
					<?endforeach?>
				</select>
			</div>
			<input name="schema" placeholder="Schema Name" required autocomplete="off">
		</div>
		<div class="box-footer" align="right">
			<button class="light-btn-bg" type="submit">Ok</button>
			<button class="dark-btn-bg" type="reset" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			location.hash = "<?=$handle?>";
			translate.fragment(form);
			form.onreset = function(){ form.drop() }
			form.addEventListener("submit",function(){
				form.drop();
			});
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>