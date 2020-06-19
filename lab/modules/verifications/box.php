<?php
	
	$rows = $mySQL->get("SELECT * FROM gb_labels ORDER BY LabelID DESC LIMIT 30");
	$handle = "s:".time();

	$LabelID = file_get_contents('php://input');
?>
<div id="<?=$handle?>" class="mount" style="width:720px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.verifications-box>div.box-caption{
		font-size:18px;
		line-height:30px;
		letter-spacing:1px;
	}
	.verifications-box>div.h-bar>label>a{
		color:inherit;
	}
	</style>
	<form class="box verifications-box white-bg" autocomplete="off">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">Spyware Search<?include_once("components/movebox.php")?></div>
		<div class="h-bar t dark-btn-bg">
			<label class="tool" title="Create Hash Map"><a href="/actions/verifications/build_map">&#xeae3;</a></label>
			<label class="tool" title="Match Hashes"><input name="matches" type="checkbox" hidden>&#xe925;</label>
		</div>
		<div class="box-body">
			
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}

			form.matches.onchange=function(){
				var inp = doc.create("input", {type:"file",name:"files[]",accept:"*.json"});
				inp.onchange = function(){
					var file = inp.files[0];
					var reader = new FileReader();
  					reader.onload = function(e){
    					var content = e.target.result;
    					XHR.push({
    						addressee:"/actions/verifications/match_hashes",
    						body:content,
    						onsuccess:function(response){
    							form.querySelector(".box-body").innerHTML = response;
    							form.align();
    						}
    					});
  					};
  					reader.readAsText(file);
				}
				inp.click();
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