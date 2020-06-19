<?php
function patterns_tree($root="patterns/css", $level=0){
	foreach(glob($root."/*", GLOB_ONLYDIR) as $i=>$dir):?>
		<input id="l-<?=($level.'-'.$i)?>" type="radio" name="l-<?=$level?>" value="<?=$dir?>" hidden>
		<label for="l-<?=($level.'-'.$i)?>" data-path="<?=$dir?>"><?=basename($dir)?></label>
		<div class="root"><?=patterns_tree($dir, $level+1)?></div>
	<?endforeach;
	global $iconset;
	foreach(array_filter(glob($root."/*"), "is_file") as $file):
		$info = pathinfo($file);
		$type = explode("/",mime_content_type($file));
		$symbol = $iconset['application'];		
		if(isset($iconset[$info['extension']])){
			$symbol = $iconset[$info['extension']];
		}elseif(isset($iconset[$type[1]])){
			$symbol = $iconset[$type[1]];
		}elseif(isset($iconset[$type[0]])) $symbol = $iconset[$type[0]]?>
		<a class="file" data-path="<?=$root?>" data-type="<?=(($type[1]=="zip") ? $type[1] : $type[0])?>" data-name="<?=$info['basename']?>"><?=$symbol?></a>
	<?endforeach;
}
?>
<div class="box-caption active-bg">CSS<?include_once("components/movebox.php")?></div>
<div class="h-bar active-bg">
	<input name="path" value="patterns/css" required>
	<input name="pname" placeholder="pattern name" data-translate="placeholder" pattern="[a-zA-Z0-9_-]+" required>
	<div class="toolbar">
		<button class="tool transparent-bg white-txt" name="create-folder" title="create folder" data-translate="title">&#xe2cc;</button>
		<button class="tool transparent-bg white-txt" name="remove" title="remove" data-translate="title">&#xe94d;</button>
	</div>
</div>
<div class="box-body">
	<aside class="black-bg">
		<div class="root"><?=patterns_tree()?></div>
	</aside>
	<script>
	(function(aside){
		aside.onchange=function(event){
			form = aside.parent(2);
			form.path.value = event.target.value;
			form.pname.value = "";
		}
	})(document.currentScript.previousElementSibling)
	</script>
	<main></main>
</div>
<div class="box-footer" align="right">
	<button name="apply" class="light-btn-bg" data-translate="textContent">apply</button>
	<button name="save" type="submit" class="light-btn-bg" data-translate="textContent">save</button>
	<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
</div>
<script>
(function(form){
	form.onsubmit=
	form.onreset=function(){this.drop()}

	form.save.onclick=function(event){
		event.preventDefault();
		XHR.push({
			addressee:"/patterns/actions/save-pattern/"+form.pname.value.trim()+"?path="+form.path.value,
			body:ace.edit(form.querySelector("main")).getValue(),
			onsuccess:function(response){
				form.querySelector("aside").innerHTML = response;
				form.refreshTree();
			}
		});
	}
	form.remove.onclick=function(event){
		event.preventDefault();
		if(form.pname.value.trim()){
			var parameter = form.pname.value.trim();
			var alrt = "remove pattern";
		}else{
			var parameter = "folder";
			var alrt = "Delete directory with files in it?";
		}
		confirmBox(alrt, function(confirmForm){
			XHR.push({
				addressee:"/patterns/actions/remove/"+parameter+"?path="+form.path.value,
				onsuccess:function(response){
					form.querySelector("aside").innerHTML = response;
					form.refreshTree();
					confirmForm.drop();
				}
			});
		},["white-bg"]);
	}
	form['create-folder'].onclick=function(event){
		event.preventDefault();
		promptBox("new folder name", function(promptForm){
			XHR.push({
				addressee:"/patterns/actions/create-folder/css",
				body:(form.path.value+"/"+promptForm.field.value.trim().toLowerCase()),
				onsuccess:function(response){
					form.querySelector("aside").innerHTML = response;
					form.refreshTree();
				}
			});
		},["dark-btn-bg"]);
	}
	form.refreshTree=function(){
		form.querySelectorAll("aside a.file").forEach(function(itm){
			itm.onclick=function(){
				XHR.push({
					addressee:"/patterns/actions/get-pattern?path="+itm.dataset.path+"/"+itm.dataset.name, 
					onsuccess:function(response){
						form.path.value = itm.dataset.path;
						form.pname.value = itm.dataset.name.split(/\./)[0];
						ace.edit(form.querySelector("main")).session.setValue(response);
					}
				});
			}
		});
	}
	form.refreshTree();
})(document.currentScript.parentNode)
</script>