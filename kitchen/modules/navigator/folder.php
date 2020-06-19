<?php
function navigator($map, $root="", $l){
	$path = "../".$_GET['subdomain']."/data/".$root;
	$entry = array_shift($map);
	foreach(glob($path."/*", GLOB_ONLYDIR) as $i=>$dir): $dir = basename($dir)?>
	<input id="l-<?=("$l-$i")?>" name="l-<?=$l?>" <?if($dir==$entry):?>checked<?endif?> data-subdomain="<?=$_GET['subdomain']?>" value="<?=($root."/".$dir)?>" type="radio" hidden>
	<label for="l-<?=("$l-$i")?>"><?=$dir?></label>
	<div class="root"><?if($dir==$entry) navigator($map, $root."/".$dir, "$l-$i")?></div>
	<?endforeach;
}

$match = defined("ARG_2") ? "/^".ARG_2."$/" : $match = "/.*/";
$multiselect = defined("ARG_3") ? ARG_3 : "checkbox";

$map = preg_split("/\//", $_GET['path'], -1, PREG_SPLIT_NO_EMPTY);

$entry = array_shift($map);

$hash = time();

$iconset = [
	"application"=>"&#xeae3;",
	"text"=>"&#xe926;",
	"mp3"=>"&#xe928;",
	"mp4"=>"&#xe929;", 
	"mov"=>"&#xe92a;",
	"video"=>"&#xe92a;",
	"zip"=>"&#xe92b;",
	"html"=>"&#xeae4;", 
	"css"=>"&#xeae6;",
	"jpg"=>"&#xe927;",
	"jpeg"=>"&#xe927;",
	"png"=>"&#xe927;",
	"image"=>"&#xe927;"
];
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<style>
		@media (max-width:640px){
			body>main{
				min-height:100vh;
				display:grid;
				grid-template-rows:36px auto;
			}
			body>main>aside{
				display:none;
				right:0;
				top:36px;
				position:fixed;
				max-width:100%;
				min-width:280px;
				height:calc(100vh - 36px);
			}
			#showfoldertree:checked+main>aside{
				display:block;
			}
		}
		@media (min-width:641px){
			body>main{
				height:100vh;
				display:grid;
				grid-template-rows:36px auto;
				grid-template-columns:auto 280px;
			}
			body>main>aside{
				grid-area:1/2/3/3;
			}
			#treeshower{
				display:none;
			}
		}
		/*****************************/
		main>header>form{
			font-size:0;
			padding:4px 0;
			margin-left:-5px;
			vertical-align:top;
			width:calc(100% - 32px);
		}
		main>header>form>input{
			height:28px;
			color:#777;
			padding:0 8px;
			font-size:15px;
			border-width:0;
			box-sizing:border-box;
			background-color:#FCFCFC;
		}
		main>header>form>input[name='subdomain']{
			width:120px;
			max-width:20%;
			text-align:right;
			box-shadow:inset 0 1px 6px -4px black;
		}
		main>header>form>input[name='entry']{
			width:60px;
			max-width:10%;
			text-align:center;
			color:rgba(255,255,255,0.8);
			background-color:transparent;
		}
		main>header>form>input[name='path']{
			width:calc(100% - 290px);
			min-width:30%;
			box-shadow:inset 0 0 6px -4px black;
		}
		main>header>form>div.select{
			height:28px;
			margin-left:3px;
			vertical-align:top;
			background-color:#FCFCFC;
			box-shadow:inset 0 1px 6px -4px black;
		}
		/*****************************/
		main>aside{
			padding:5px;
			overflow:auto;
			background-color:#162228;
		}
		/*****************************/
		#folder{
			padding:10px;
			overflow:auto;
			display:grid;
			grid-gap:10px;
			grid-template-rows:repeat(auto-fill, minmax(100px, min-content));
			grid-template-columns:repeat(auto-fill, minmax(100px, min-content));
		}
		#folder>label>div{
			padding:8px;
			font-size:14px;
			text-align:center;
			border:1px solid transparent;
			background-color:white;
		}
		#folder>label:hover>div,
		#folder>label>input:checked+div{
			border-color:#70EDF0;
			background-color:#E0F0FF;
		}
		#folder>label>div>img{
			width:100%;
			height:64px;
			display:block;
			position:relative;
			object-fit:contain;
			background-color:inherit;
		}
		#folder>label>div>img::before{
			color:#EA4;
			content:attr(alt);
			font:48px/64px tools;
			
			top:0;
  			left:0;
  			width:100%;
			height:100%;
			display:block;
  			position:absolute;
  			background-color:inherit;
		}
		#folder>label.folder>div>img::before{
			color:#40BDE0;
			font-size:68px;
		}
		</style>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=uploader" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="showfoldertree" type="checkbox" name="showtree" form="tree" autocomplete="on" hidden>
		<main>
			<header class="h-bar active-bg">
				<form class="toolbar" autocomplete="off">
					<input name="subdomain" value="<?=$_GET['subdomain']?>" placeholder="subdomain">
					<input name="entry" value="/data/" readonly>
					<input name="path" value="<?=$_GET['path']?>" placeholder="...">
					<div class="select">
						<select name="filter">
							<?foreach([".*"=>"All Files","image"=>"Only Images","video"=>"Only Videos"] as $mask=>$key):?>
							<option <?if($mask==ARG_2):?>selected<?endif?> value="<?=$mask?>"><?=$key?></option>
							<?endforeach?>
						</select>
					</div>
					<script>
					(function(form){
						STANDBY.subdomain = "<?=$_GET['subdomain']?>";
						STANDBY[STANDBY.subdomain] = "<?=$_GET['path']?>";
						form.onsubmit=function(event){event.preventDefault()}
						form.onchange=function(event){
							if(event.target.name=="filter"){
								var path = location.pathname.split(/\//);
								path[3] = event.target.value;
								location.pathname = path.join("/");
							}else location.search = "subdomain="+form.subdomain.value.trim()+"&path="+form.path.value.trim()
						}
					})(document.currentScript.parentNode)
					</script>
				</form>
				<div class="toolbar r right">
					<label id="treeshower" title="folders tree" data-translate="textContent" class="tool" for="showfoldertree">&#xe902;</label>
				</div>
			</header>
			<form id="folder" class="white-bg" autocomplete="off">
				<?if(!empty($_GET['path'])):?>
				<label class="folder backfolder" data-path="<?=implode('/', array_slice(explode("/",$_GET['path']),0, -1) )?>">
					<div class="backfolder"><img alt="&#xe2c8;">● ●</div>
				</label>
				<?endif?>
				<?foreach( glob("../".$_GET['subdomain']."/data".$_GET['path']."/*", GLOB_ONLYDIR) as $dir): $dir=basename($dir)?>
				
				<label data-path="<?=($_GET['path'].'/'.$dir)?>" class="folder">
					<!--<input type="<?=$multiselect?>" name="folders-on-folder" hidden>-->
					<div><img alt="&#xe2c7;"><?=$dir?></div>
				</label>
				
				<?endforeach?>
				<?foreach(array_filter(glob("../".$_GET['subdomain']."/data/".$_GET['path']."/*"), 'is_file') as $file):
				$path = pathinfo($file);
				$type = explode("/",mime_content_type($file));
				
				if(preg_match($match, $type[0])):
				if(isset($iconset[$path['extension']])){
					$symbol = $iconset[$path['extension']];
				}elseif(isset($iconset[$type[1]])){
					$symbol = $iconset[$type[1]];
				}elseif(isset($iconset[$type[0]])){
					$symbol = $iconset[$type[0]];
				}else $symbol = $iconset['application']?>
				<label data-path="<?=($_GET['path'].'/'.$path['basename'])?>" class="file" data-type="<?=$type[1]?>">
					<input type="<?=$multiselect?>" name="files-on-folder" data-type="<?=$type[0]?>" value="//<?=($_GET['subdomain'].".".HOST."/data".$_GET['path']."/".$path['basename'])?>" hidden>
					<div>
						<img src="//<?=($_GET['subdomain'].".".HOST."/data".$_GET['path']."/".$path['basename']."#".$hash)?>" alt="<?=$symbol?>">
						<?=$path['basename']?>
					</div>
				</label>
				<?endif; endforeach?>
				<template id="context">
					<div class="context-item" data-action="copy" data-translate="textContent">copy</div>
					<div class="context-item" data-action="cut" data-translate="textContent">cut</div>
					<div class="context-item" data-action="rename" data-translate="textContent">rename</div>
					<hr size="1" color="#CCC">
					<div class="context-item" data-icon="&#xe94d;" data-action="remove" data-translate="textContent">delete</div>
				</template>
				<template id="root-context">
					<div class="context-item" data-icon="&#xe905;" onclick="upload()" data-translate="textContent">upload</div>
					<div class="context-item" data-icon="&#xe2cc;" onclick="createFolder()" data-translate="textContent">create folder</div>
					<div class="context-item" data-icon="&#xe948;" onclick="selectAll()" data-translate="textContent">select all</div>
				</template>
				<template id="paste-context">
					<hr size="1" color="#CCC">
					<div class="context-item" data-icon="&#xe925;" onclick="paste()" data-translate="textContent">paste</div>
				</template>
				<template id="unzip-context">
					<div class="context-item" data-icon="&#xe92b;" data-action="unzip" data-translate="textContent">unzip</div>
					<hr size="1" color="#CCC">
				</template>
				<template id="zip-context">
					<div class="context-item" data-icon="&#xe92b;" data-action="zip" data-translate="textContent">create archive</div>
					<hr size="1" color="#CCC">
				</template>
				<script>
				var folder = document.currentScript.parentNode;
					
				
				folder.querySelectorAll("label").forEach(function(node){
					node.ondblclick=function(){
						if(node.classList.contains("folder")){
							location.search = "subdomain=<?=$_GET['subdomain']?>&path="+node.dataset.path;
						}else if(node.className=="file"){
							location.href = "/actions/download?path=../<?=$_GET['subdomain']?>/data"+node.dataset.path;
						}
					}
					node.oncontextmenu = function(event){
						let menu = ContextMenu.create(event, node, context);
						translate.fragment(menu);
					}
				});
				
				var context = function(obj){
					var menu = document.createDocumentFragment();
					if(obj.classList.contains("backfolder")){
						
					}else if(obj.classList.contains("folder")){
						menu.appendChild(folder.querySelector("#zip-context").cloneNode(true).content);
					}else if(obj.classList.contains("file")){
						if(obj.dataset.type=="zip"){
							menu.appendChild(folder.querySelector("#unzip-context").cloneNode(true).content);
						}
					}
					menu.appendChild(folder.querySelector("#context").cloneNode(true).content);


					menu.querySelectorAll(".context-item").forEach(function(itm){
						itm.onclick = function(){
							window[itm.dataset.action](obj)
						}
					});
					return menu;
				}
				var copy = function(obj){
					let lst = [];
					getSelected().forEach(function(itm){lst.push(itm.path)});
					STANDBY.movelist =
					STANDBY.copylist = [];
					if(lst.length){
						STANDBY.copylist = lst;
					}else STANDBY.copylist = ["../<?=$_GET['subdomain']?>/data"+obj.dataset.path];
				}
				var cut = function(obj){
					let lst = [];
					getSelected().forEach(function(itm){lst.push(itm.path)});
					STANDBY.movelist =
					STANDBY.copylist = [];
					if(lst.length){
						STANDBY.movelist = lst;
					}else STANDBY.movelist = ["../<?=$_GET['subdomain']?>/data"+obj.dataset.path];
				}
				var rename = function(obj){
					let path = obj.dataset.path.split(/\//);
					let old = path.pop();
					let box = promptBox("", function(form){
						let newname = form.field.value.trim().translite();
						path.push(newname);
						let newpath = path.join("/");
						XHR.push({
							addressee:"/actions/navigator/fs_rename",
							body:JSON.stringify({
								old:"../<?=$_GET['subdomain']?>/data"+obj.dataset.path,
								new:"../<?=$_GET['subdomain']?>/data"+newpath
							}),
							onsuccess:function(response){ location.reload() }					
						});
					}).onopen = function(form){
						form.field.value = old;
						form.alert.value = translate['rename'] || "Rename";
						form.field.focus();
					}
				}
				var zip = function(obj){
					XHR.push({
						addressee:"/actions/navigator/tozip",
						body:"../<?=$_GET['subdomain']?>/data"+obj.dataset.path,
						onsuccess:function(response){ location.reload() }
					});
				}
				var unzip = function(obj){
					XHR.push({
						addressee:"/actions/navigator/unzip",
						body:"../<?=$_GET['subdomain']?>/data"+obj.dataset.path,
						onsuccess:function(response){ location.reload() }
					});
				}
				folder.oncontextmenu = function(event){
					if(!folder.compareDocumentPosition(event.target)){
						ContextMenu.create(event, folder, function(){
							STANDBY.copylist = STANDBY.copylist || [];
							STANDBY.movelist = STANDBY.movelist || [];
							var menu = folder.querySelector("#root-context").cloneNode(true).content;
							if(STANDBY.copylist.length || STANDBY.movelist.length){
								menu.appendChild(folder.querySelector("#paste-context").cloneNode(true).content);
							}
							translate.fragment(menu);
							return menu;
						});
					}
				}
				var paste = function(){
					if(STANDBY.movelist.length){
						var addressee = "/actions/navigator/move?path=../<?=$_GET['subdomain']?>/data<?=$_GET['path']?>";
						var body = JSON.encode(STANDBY.movelist);
					}else if(STANDBY.copylist.length){
						var addressee = "/actions/navigator/copy?path=../<?=$_GET['subdomain']?>/data<?=$_GET['path']?>";
						var body = JSON.encode(STANDBY.copylist);
					}
					XHR.push({
						addressee:addressee,
						body:body,
						onsuccess:function(response){
							STANDBY.movelist = 
							STANDBY.copylist = [];
							location.reload();
						}
					});
				}

				var getSelected = function(){
					var answer = [];
					folder.querySelectorAll("input:checked").forEach(function(inp){
						answer.push({
							type:inp.dataset.type,
							path:"../<?=$_GET['subdomain']?>/data"+inp.parentNode.dataset.path,
							url:inp.value
						});
					});
					return answer;
				}
				var getSelectedURLs = function(){
					var answer = [];
					folder.querySelectorAll("input[name='files-on-folder']:checked+div>img").forEach(function(itm){
						answer.push(itm.src.split(/#/).shift());
					});
					return answer;
				}
				var selectAll = function(){
					folder.querySelectorAll("input[type='checkbox']").forEach(function(inp){
						inp.checked = !inp.checked;
					});
				}
				var upload = function(){
					var inp = doc.create("input", {type:"file",name:"files[]",accept:"*.*",multiple:"multiple"});
					inp.onchange = function(){
						XHR.uploader(inp.files, "/actions/navigator/upload?path=../<?=$_GET['subdomain']?>/data<?=$_GET['path']?>", function(response){
							location.reload();
						});
					}
					inp.click();
				}
				var createFolder = function(){
					window.parent.promptBox("new folder name",function(form){
						XHR.push({
							addressee:"/actions/navigator/mkpath",
							body:"../<?=("{$_GET['subdomain']}/data{$_GET['path']}")?>/"+form.field.value.trim().translite(),
							onsuccess:function(response){
								form.drop();
								location.reload();
							}
						});
					},["light-btn-bg"]);
				}
				var remove = function(obj){
					let lst = getSelected();
					if(lst.length){
						window.parent.confirmBox("delete selected",function(form){
							XHR.push({
								addressee:"/actions/navigator/rmpath",
								body:JSON.encode(lst),
								onsuccess:function(response){
									form.drop();
									location.reload()
								}
							});
						},["logo-bg"]);
					}else if(obj){
						XHR.push({
							addressee:"/actions/navigator/rmpath",
							body:JSON.encode([{
								path:"../<?=$_GET['subdomain']?>/data"+obj.dataset.path
							}]),
							onsuccess:function(response){location.reload()}
						});
					}
				}
				var prowler = function(){
					new Box("../<?=$_GET['subdomain']?>/data<?=$_GET['path']?>", "navigator/prowlerbox");
				}
				var createMediaset = function(){
					var box = new Box(null,"mediaset/createbox",function(form){
						var mediaset = [];
						getSelectedURLs().forEach(function(itm){
							mediaset.push({
								key:itm.split('/').pop().split('.').shift(),
								url:itm
							});
						});
						XHR.push({
							addressee:"/actions/mediaset/ad_mediaset",
							body:JSON.encode({
								Name:form.named.value.trim(),
								Category:form.category.value.trim(),
								Mediaset:mediaset
							}),
							onsuccess:function(response){
								box.drop();
								if(isNaN(response)){
									console.log(response);
								}else new Box(null,"mediaset/box/"+form.category.value.trim()+"/"+response);
							}
						});
					});
				}
				</script>
			</form>	
			<aside>
				<form id="tree" class="root light-txt" autocomplete="off">
					<?foreach($config->domains as $subdomain=>$value):?>
					<input id="<?=$subdomain?>" <?if($subdomain==$_GET['subdomain']):?>checked<?endif?> value="<?=$subdomain?>" name="subdomain" type="radio" hidden>
					<label for="<?=$subdomain?>"><?=$subdomain?></label>
					<div class="root">
					<?foreach(glob("../".$subdomain."/data/*", GLOB_ONLYDIR) as $j=>$dir): $dir=basename($dir)?>
						<input id="l-<?=($i."-".$j)?>" value="<?=$dir?>" data-subdomain="<?=$subdomain?>" <?if($dir==$entry):?>checked<?endif?> name="l-<?=$i?>" type="radio" hidden>
						<label for="l-<?=($i."-".$j)?>"><?=$dir?></label>
						<?if($dir==$entry):?>
						<div class="root"><?navigator( $map, $entry, $i."-".$j)?></div>
						<?else:?><div class="root empty"></div><?endif?>
					<?endforeach?>
					</div>
					<?endforeach?>
					<script>
					(function(form){
						form.onchange=function(event){
							var inp = event.target;
							if(inp.name=="subdomain"){
								location.search = "subdomain="+inp.value;
							}else location.search = "subdomain="+inp.dataset.subdomain+"&path=/"+inp.value;
						}
						form.showtree.onchange=function(event){
							STANDBY.showtree = form.showtree.checked;
						}
						form.showtree.checked = (STANDBY.showtree=="true");
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
		</main>
	</body>
</html>