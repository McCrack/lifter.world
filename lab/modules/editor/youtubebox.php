<?$handle = "l:".time()?>
<div id="<?=$handle?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.youtube-box>div.box-caption{
		font:16px/36px iconset;
	}
	.youtube-box>div.box-body{
		font-size:0;
	}
	.youtube-box input[name='link']{
		width:99%;
		height:30px;
		padding:8px;
		border:1px solid #BBB;
		box-sizing:border-box;
		box-shadow:inset 0 0 8px -4px rgba(0,0,0, .5);
		background-image:linear-gradient(to top, #FFF, #F0F0F0);
	}
	</style>
	<form class="box youtube-box light-btn-bg" style="width:680px">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">▶<?include_once("components/movebox.php")?></div>
		<div class="h-bar dark-btn-bg" data-translate="textContent">YouTube</div>
		<div class="box-body" align="center">
			<input name="link" placeholder="URL:" pattern=".*">
			<div align="center">
				<iframe src="https://www.youtube.com/embed" frameborder="0" allowfullscreen width="99%" height="300px"></iframe>
			</div>
		</div>
		<div class="box-footer dark-btn-bg" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent">insert</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.addEventListener("onsubmit",function(event){
				event.preventDefault();
			});
			form.onreset=function(){form.drop()}
			form.link.oninput=function(){
				var url = form.link.value;
				var link = doc.create("a", {"href":url});
				var mode = link.pathname.split("/").pop();
				if(mode=="watch"){
					var items = link.search.split(/\?|&/);
					for(var i=0;  i<items.length; i++){
						items[i]=items[i].split(/=/);
						if(items[i][0]==="v"){
							url = "https://www.youtube.com/embed/"+items[i][1];
							break;
						}
					}
				}else url = "https://www.youtube.com/embed/"+mode;
				form.link.value = 
				form.querySelector("iframe").src = url;
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