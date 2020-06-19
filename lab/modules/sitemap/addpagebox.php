<?
$cng = new config("../".BASE_FOLDER."/config.init");
define("PAGE_PARENT", file_get_contents('php://input'));
$handle = "b:".time()
?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.addpage-box>div.box-caption{
		font-size:16px;
	}
	.addpage-box>div.box-body{
		padding:10px;
	}
	.addpage-box>div.box-body fieldset{
		font-size:14px;
		height:50px;
		padding:0;
		border-width:0;
	}
	.addpage-box>div.box-body fieldset:nth-child(1){
		
	}
	.addpage-box>div.box-body>fieldset>input{
		width:100%;
	}
	.addpage-box>div.box-body>fieldset>input,
	.addpage-box>div.box-body>fieldset>div.select{
		padding:6px;
		height:30px;
		border:1px solid #CCC;
		box-sizing:border-box;
	}
	</style>
	<form class="box addpage-box white-bg" style="width:380px">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">
			📄
			<script>
			(function(bar){
				bar.onmousedown=function(event){
					event.preventDefault();
					var mount = bar.parentNode,
						y = event.clientY - mount.offsetTop,
						x = event.clientX - mount.offsetLeft;
					document.onmousemove=function(event){
						let top = event.clientY - y;
						let left = event.clientX - x;
						mount.style.top = (top > 0) ? top+"px" : "0";
						mount.style.left = (left > 0) ? left+"px" : "0";
					}
					document.onmouseup=function(){document.onmousemove = null;}
				}
			})(document.currentScript.parentNode)
			</script>
		</div>
		<div class="h-bar active-bg" data-translate="textContent">create page</div>
		<div class="box-body">
			<fieldset class="right"><legend data-translate="textContent">language</legend>
				<div class="select">
					<select name="language">
					<?foreach($cng->languageset as $lang):?>
						<option <?if($lang == ARG_2):?>selected<?endif?> value="<?=$lang?>"><?=$lang?></option>
					<?endforeach?>
					</select>
				</div>
			</fieldset>
			<fieldset class="right"><legend data-translate="textContent">type</legend>
				<div class="select">
					<select name="entity">
						<option value="category">Category</option>
						<option value="material">Material</option>
					</select>
				</div>
			</fieldset>
			<fieldset><legend data-translate="textContent">category</legend>
				<div class="select">
					<select name="parent">
						<?if(PAGE_PARENT!="root"):?>
							<option value="root">root</option>
						<?endif?>
						<option value="<?=PAGE_PARENT?>" selected><?=PAGE_PARENT?></option>
						<?$categories=$mySQL->getGroup("SELECT name FROM gb_sitemap CROSS JOIN gb_pages USING(PageID) WHERE type LIKE 'category' AND language LIKE {str}", ARG_2)['name'];
						foreach($categories as $category) if($category!=PAGE_PARENT):?>
						<option value="<?=$category?>"><?=$category?></option>	
						<?else: continue; endif?>
					</select>
				</div>
			</fieldset>
			<br>
			<fieldset><legend data-translate="textContent">page name</legend>
				<input name="named" required placeholder="...">
			</fieldset>
		</div>
		<div class="box-footer" align="right">
			<button type="submit" name="create" class="light-btn-bg" data-translate="textContent" disabled>create</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){ form.drop(); }
			form.onsubmit=function(event){
				event.preventDefault();
				let named = form.named.value.trim().toLowerCase().translite("-", true);
				XHR.push({
					addressee:"/sitemap/actions/add-page",
					body:JSON.encode({
						parent:form.parent.value,
						name:named,
						header:form.named.value.trim(),
						type:form.entity.value,
						language:form.language.value
					}),
					onsuccess:function(response){
						setTimeout(function(){
							if(isNaN(response)){
								alertBox(response)
							}else location.pathname = "<?=ARG_3?>/"+form.language.value+"/"+named;
						}, 100);
						form.drop();
					}
				});
			}
			form.oninput=function(){
				form.create.disabled = false;
			}
			location.hash = "<?=$handle?>";
			translate.fragment(form);
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>