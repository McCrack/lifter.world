<?php
$handle = "b:".time();

$PageID = file_get_contents('php://input');

$set = $mySQL->getRow("SELECT microdata FROM gb_static WHERE PageID = {int} LIMIT 1", $PageID)['microdata'];
$set = explode(",", $set);

?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.microdata-box>div.box-caption{
		font-size:22px;
		line-height:30px;
	}
	.microdata-box>div.box-body{
		padding:20px;
	}
	.microdata-box>div.box-body>fieldset{
		border-width:0;
		border-top:1px solid #555;

		column-count:2;
		-moz-column-count:2;
		-webkit-column-count:2;
	}
	.microdata-box>div.box-body>fieldset>label{
		color:#AAA;
		display:block;
		cursor:pointer;
		text-transform:capitalize;
	}
	.microdata-box>div.box-body>fieldset>label>span::before{
		color:#777;
		content:"\e5d0";
		font:18px tools;
		margin-right:4px;
		vertical-align:top;
		display:inline-block;
	}
	.microdata-box>div.box-body>fieldset>label>input:checked+span{
		color:white;
	}
	.microdata-box>div.box-body>fieldset>label>input:checked+span::before{
		color:#00ADF0;
		content:"\e5d1";
	}
	</style>
	<form class="box microdata-box dark-btn-bg" style="width:360px;">
		<button type="reset" class="close-btn light-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption light-btn-bg">&#xe8ab;</div>
		<div class="h-bar active-bg">
			Schemes
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
		<div class="box-body">
		<?foreach(scandir("..") as $subdomain) if(is_dir( "../".$subdomain."/schemes" )):?>
			<fieldset <?if($subdomain!=BASE_FOLDER):?>disabled class="dark-txt"<?endif?>><legend><?=$subdomain?></legend>
				<?foreach( array_filter(glob("../".$subdomain."/schemes/*.json"), "is_file") as $path): $file=pathinfo($path)['filename']?>
				<label><input type="checkbox" value="<?=$file?>" <?if(in_array($file, $set)):?>checked<?endif?> hidden><span><?=$file?></span></label>
				<?endforeach?>
			</fieldset>
		<?endif?>
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
				XHR.push({
					"Content-Type":"text/plain",
					"addressee":"/sitemap/actions/schemes/<?=$PageID?>",
					"body":(function(schemes){
						form.querySelectorAll("input:checked").forEach(function(inp){
							schemes.push(inp.value);
						});
						return schemes.join(",");
					})([]),
					"onsuccess":function(response){form.drop()}
				});
			});
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>