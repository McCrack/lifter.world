<?$handle = "p:".time()?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.prowler-box>div.h-bar input{
		height:28px;
		padding:0 12px;
		border-width:0;
		vertical-align:middle;
		box-sizing:border-box;
		background-color:#FCFCFC;
		box-shadow:inset 0 1px 6px -2px black;
	}
	.prowler-box>div.h-bar input[name='url']{
		margin-left:-12px;
		min-width:100px;
		width:calc(100% - 340px);
	}
	.prowler-box>div.h-bar div.select{
		height:28px;
	}
	.prowler-box>div.box-body>iframe{
		max-height:80vh;
	}
	.prowler-box>div.box-footer>#select-all-btn{
		padding:0;
		width:24px;
		height:24px;
		font:24px tools;
	}
	</style>
	<form class="box prowler-box white-bg" style="min-width:840px">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">
			🌎
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
		<div class="h-bar dark-btn-bg">
			<input type="url" name="url" placeholder="URL">
			<div class="toolbar">
				<span data-translate="textContent">filter</span>:
				<div class="select light-btn-bg">
					<select name="filterkey">
						<option value="naturalWidth">Width larger than</option>
						<option value="naturalHeight">Height larger than</option>
					</select>
				</div>
				<input name="filter" value="200px" placeholder="px" size="5">
			</div>
		</div>
		<div class="box-body">
			<iframe width="100%" frameborder="no"></iframe>
		</div>
		<div class="box-footer light-btn-bg" align="right">
			<button id="select-all-btn" title="select all" data-translate="title" name="selectall" class="left transparent-bg dark-txt">&#xe948;</button>
			
			<button name="upload" class="light-btn-bg" type="submit" data-translate="textContent" disabled>upload</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			location.hash = "<?=$handle?>";
			form.filter.oninput=
			form.filterkey.onchange=function(){
				form.querySelector(".box-body>iframe").contentWindow.setFilter(form.filterkey.value, parseInt(form.filter.value));
			}
			form.url.onkeydown=function(event){
				if(event.keyCode==13){event.preventDefault();}
			}
			form.url.oninput=function(){
				var frame = form.querySelector(".box-body>iframe");
				frame.src="/navigator/prowler?path="+form.url.value.trim()
				frame.height = "800px";
				form.align();
				frame.onload=function(){
					frame.contentWindow.setFilter(form.filterkey.value, parseInt(form.filter.value));
					form.upload.disabled = false;
				}
			}
			form.selectall.onclick=function(event){
				event.preventDefault();
				form.querySelector(".box-body>iframe").contentWindow.selectAll();
			}
			form.onsubmit=function(){
				var lst = form.querySelector(".box-body>iframe").contentWindow.getSelected();
				XHR.push({
					addressee:"/actions/navigator/import?path=<?=$HTTP_RAW_POST_DATA?>",
					body:JSON.encode(lst),
					onsuccess:function(){
						location.reload();
					}
				});
				form.drop();
			}
			form.onreset = function(){ form.drop() }
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>