<?php
$handle = "b:".time();

$statuses = $mySQL->getGroup("SELECT status FROM gb_task_shunter GROUP BY status")['status'];

?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.clear-box>div.box-body{
		padding:0 30px;
	}
	.clear-box>div.box-body>span{
		font:60px/80px tools;
		letter-spacing:30px
	}
	.clear-box>div.box-body>label{
		display:block;
		font-size:18px;
	}
	.clear-box>div.box-body>label>span::before{
		content:"\ea53";
		font:14px tools;
		margin-right:5px;
	}
	.clear-box>div.box-body>label>input:checked+span::before{
		color:#00ADF0;
		content:"\ea52";
	}
	</style>
	<form class="box clear-box dark-btn-bg" style="width:340px;">
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
		<div class="box-body">
			<span class="right">&#xe9ac;</span>
			<?foreach($statuses as $status):?>
			<label><input type="checkbox" value="<?=$status?>" hidden> <span><?=$status?></span></label>
			<?endforeach?>
			<br clear="left">
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
					addressee:"/shunter/actions/clear",
					body:JSON.encode((function(statuses){
						form.querySelectorAll("input:checked").forEach(function(inp){
							statuses.push(inp.value);
						});
						return statuses;
					})([])),
					onsuccess:function(response){
						if(parseInt(response)) location.reload();
					}
				});
				form.drop();
			});
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>