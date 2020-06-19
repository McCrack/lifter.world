<?$handle = "p:".time()?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	form.create-box>div.box-caption{
		font-size:16px;
	}
	form.create-box>div.box-body{
		display:flex;
		align-items:center;
		justify-content:space-around;
	}
	form.create-box>div.box-body>fieldset{
		border-width:0;
	}
	form.create-box>div.box-body>fieldset input,
	form.create-box>div.box-body>fieldset>.select{
		padding:8px;
		box-sizing:border-box;
		border:1px solid #AAA;
		background:linear-gradient(to top, #FFF, #EEE);
		box-shadow:inset 0 1px 6px -4px black;
	}
	</style>
	<form class="box create-box light-btn-bg" style="min-width:360px" autocomplete="off">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">
			&#xe94b;
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
		<div class="h-bar logo-bg"><small data-translate="textContent">create mediaset</small></div>
		<div class="box-body">
			<fieldset><legend data-translate="textContent">named</legend>
				<input name="named" placeholder=".." required>
			</fieldset>
			<fieldset><legend data-translate="textContent">category</legend>
				<div class="input-with-select">
					<input name="category" list="categorylist" placeholder=".." required>
					<datalist id="categorylist" onmousedown="this.previousElementSibling.value=event.target.textContent">
						<?$categories = $mySQL->getGroup("SELECT Category FROM gb_media GROUP BY Category LIMIT 10")['Category'];
						foreach($categories as $category):?>
						<option value="<?=$category?>"><?=$category?></option>
						<?endforeach?>
					</datalist>
				</div>
			</fieldset>
			<fieldset><legend data-translate="textContent">language</legend>
				<div class="select">
					<select class="black-txt" name="language" required>
						<?foreach($cng->languageset as $lang):?>
						<option <?if(in_array($lang, $alts)):?>disabled<?endif?> value="<?=$lang?>"><?=$lang?></option>
						<?endforeach?>
					</select>
				</div>
			</fieldset>
		</div>
		<div class="box-footer" align="right">
			<button name="create" type="submit" class="light-btn-bg" data-translate="textContent" disabled>create</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			location.hash = "<?=$handle?>";
			form.oninput=function(){
				form.create.disabled = false;
				form.oninput = null;
			}
			form.onreset = function(){ form.drop() }
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>