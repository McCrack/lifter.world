<?php
	$key = file_get_contents('php://input');
	$handle = "b:".time();
?>
<div id="<?=$handle?>" class="mount" style="width:680px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.wordlist-box>div.box-body{
		display:grid;
	}
	.wordlist-box>div.box-body>aside{
		overflow:auto;
		max-height:350px;
	}
	@media (max-width:460px){
		.wordlist-box>div.box-body{grid-template-columns:180px auto;}
	}
	@media (min-width:461px){
		.wordlist-box>div.box-body{grid-template-columns:200px auto;}
	}
	.h-bar>input[name='key']{
		color:white;
		cursor:default;
		border-width:0;
		font-size:16px;
		width:calc(100% - 96px);
		background-color:transparent;
	}
	.wordlist-box>div.box-footer>button{
		width:96px;
		padding:5px;
		border-width:0;
		cursor:pointer;
	}
	.wordlist-box>div.box-footer>button.light-btn-bg:hover{
		color:white;
		background:#009DE0;
	}
	.wordlist-box>div.box-footer>button.dark-btn-bg:hover{
		background:#924;
	}
	.wordlist-box>div.box-footer>button:disabled{
		pointer-events:none;
		background:none;
		background-color:#BBB;
	}
	</style>
	<script src="/modules/wordlist/tpl/wordlist.js"></script>
	<form class="box wordlist-box white-bg">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xe431;<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg">
			<input name="key" value="<?=$key?>" readonly>
			<input type="hidden" name="path">
		</div>
		<div class="box-body">
			<aside class="body-bg light-txt">
				<div class="h-bar dark-txt" data-translate="textContent">wordlist</div>
				<div class="root">
				<?foreach(scandir("../") as $subdomain) if($subdomain!="." && $subdomain!=".."):?>
					<a><?=$subdomain?></a>
					<?if(is_dir("../".$subdomain."/localization")):?>
					<div class='root'>
						<?foreach(scandir("../".$subdomain."/localization") as $file) if(is_file("../".$subdomain."/localization/".$file)):?>
						<a data-path="../<?=($subdomain."/localization/".$file)?>" data-key="<?=$key?>"><?=explode(".",$file)[0]?></a>
						<?endif?>
					</div>
					<?endif;
				endif?>
				</div>
				<script>
				(function(aside){
					var form = aside.ancestor("form");
					aside.querySelectorAll("a[data-key]").forEach(function(itm){
						itm.onclick=function(){
							form.path.value = itm.dataset.path;
							XHR.push({
								addressee:"/actions/wordlist/sh_key_wordlist",
								body:JSON.encode({
									key:itm.dataset.key,
									path:itm.dataset.path
								}),
								onsuccess:function(response){
									aside.parentNode.querySelector("table>tbody").innerHTML = response;
									form.save.disabled = false;
								}
							});
						}
					})
				})(document.currentScript.parentNode)
				</script>
			</aside>
			<table width="100%" rules="cols" cellpadding="4" cellspacing="0" bordercolor="#999">
				<thead><tr class="h-bar-bg" height="36px"><th width="50" data-translate="textContent">language</th><th><?=$key?></th></tr></thead>
				<tbody></tbody>
			</table>
		</div>
		<div class="box-footer" align="right">
			<button name="save" type="submit" class="light-btn-bg" data-translate="textContent" disabled>save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){form.drop();}
			form.onsubmit=function(){
				var params = {path:form.path.value, wordlist:{}};
				if(params.path){
					var key = "<?=$key?>",
						langs = form.querySelectorAll("table>tbody>tr>th");
						values = form.querySelectorAll("table>tbody>tr>td");
					XHR.push({
						addressee:"/actions/wordlist/sv_key_wordlist",
						body:(function(){
							values.forEach(function(cell,i){
								var lang = langs[i].textContent;
								params.wordlist[lang] = {};
								params.wordlist[lang][key] = cell.textContent.trim();
							});
							return JSON.encode(params);
						})(),
						onsuccess:function(response){form.drop();}
					});
				}
			}
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