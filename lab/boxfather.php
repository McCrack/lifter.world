<?php

require_once "core/index.php";

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);
define("BOX", array_shift($params));
foreach($params as $i=>$itm) define("ARG_".($i+1), $itm);

$h = "b".time();
$classes = JSON::load('php://input')?>
<?if(ARG_1=="modal"):?>
<div id="<?=$h?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?include_once("boxfather/".BOX.".php")?>
	<script>
	(function(form){
		location.hash = "<?=$h?>";
		translate.fragment(form);
		form.onreset = function(){ form.drop() }
		form.addEventListener("submit",function(){
			form.drop();
		});
		form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
	})(document.currentScript.previousElementSibling);
	</script>
</div>
<?else:?>
<div id="<?=$h?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?include_once("boxfather/".BOX.".php")?>
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
<?endif?>