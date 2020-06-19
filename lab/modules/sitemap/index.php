<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];

if(defined("ARG_2")){
	$material = $mySQL->getRow("
	SELECT * FROM gb_sitemap
	CROSS JOIN gb_pages USING(PageID)
	CROSS JOIN gb_static USING(PageID)
	WHERE 
		name LIKE {str} AND
		language LIKE {str}
	LIMIT 1", ARG_2, ARG_1);
	define("MATERIAL_ID", $material['PageID']);
	define("MATERIAL_NAME", $material['name']);
}else{
	define("MATERIAL_ID", 0);
	define("MATERIAL_NAME", "root");
}
$cng = new config("../".BASE_FOLDER."/".$config->{"config file"});

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/sitemap/index.css">
		<script src="/modules/sitemap/tpl/sitemap.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=sitemap" defer charset="utf-8"></script>
		<script src="/js/ace/src-min/ace.js" type="text/javascript" charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden onchange="STANDBY.screenmode=this.checked">
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label for="rightbar-shower"></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="sitemap-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="sitemap" class="tab body-bg light-txt">
						<div class="h-bar white-txt">
							<span data-translate="textContent">sitemap</span>
							<div class="select right">
								<select class="active-txt">
									<?php 
									$language = defined("ARG_1") ? ARG_1 : $cng->language;
									foreach($cng->languageset as $lang):?>
										<option <?if($lang === $language):?>selected<?endif?> value="<?=$lang?>"><?=$lang?></option>
									<?endforeach?>
									<script>
									(function(select){
										select.onchange=function(){
											LANGUAGE = select.value;
											reloadTree(select.value);
										}
									})(document.currentScript.parentNode);
									</script>
								</select>
							</div>
						</div>
						<a class="root-itm light-txt" href="/sitemap/<?=$language?>">Root</a>
						<?function staticTree(&$items, $offset="root"){
							if(is_array($items[$offset])):?>
							<div class="root">
								<?foreach($items[$offset] as $key=>$val):?>
								<a data-id="<?=$val['PageID']?>" data-parent="<?=$val['parent']?>" href="/sitemap/<?=($val['language'].'/'.$val['name'])?>" class="<?if($val['PageID']==MATERIAL_ID):?>active-txt<?elseif($val['published']==='Published'):?>published-txt<?endif?>"><?=(empty($val['header'])?$val['name']:$val['header'])?> <label>❬</label></a>
								<?staticTree($items, $key);
								endforeach?>
							</div>
							<?endif;
						}
						$tree = $mySQL->getTree("name", "parent", "SELECT * FROM gb_sitemap WHERE language LIKE '".$language."' ORDER BY SortID ASC");
						staticTree($tree)?>
						<script>
						(function(map){
							map.onscroll=function(){STANDBY.mapScrollTop = map.scrollTop;}
							var items = map.querySelectorAll("a>label");
							items.forEach(function(point,i){
								point.onclick=function(event){
									event.preventDefault();
									var item = point.parentNode,
										previous = item.previousElementSibling;
									if(previous){
										var root = item.nextElementSibling;
										var itr = previous.classList.contains("root") ? 2 : 1;
										item.swap(false, itr);
										if(root && root.classList.contains("root")) root.swap(false, itr);
										XHR.push({
											addressee:"/sitemap/actions/assort",
											body:JSON.encode((function(lst){
												item.parentNode.querySelectorAll("a[data-parent='"+item.dataset.parent+"']").forEach(function(itm){
													lst.push(itm.dataset.id);
												});
												return lst;
											})([]))
										});
									}
								}
							});
						})(document.currentScript.parentNode)
						</script>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="sitemap" class="tool" for="sitemap-tab" data-translate="title">&#xe902;</label>
					</div>
					<div class="toolbar">
						<label title="navigator" class="tool" data-translate="title" onclick="new Box(null, 'navigator/box')">&#xf07c;</label>
						<label title="mediaset" class="tool" data-translate="title" onclick="new Box(null, 'mediaset/box')">&#xe94b;</label>
					</div>
					<div class="toolbar">
						<label title="keywords" class="tool" data-translate="title" onclick="new Box(null, 'keywords/box')">&#xe9d3;</label>
						<?if($access['settings']):?>
						<label title="settings" class="tool" data-translate="title" onclick="new Box(null, 'settings/box')">&#xf013;</label>
						<?endif?>
						<?if($access['staff']):?>
						<label title="staff" class="tool" data-translate="title" onclick="new Box(null, 'staff/box')">&#xe972;</label>
						<?endif?>
					</div>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							if(event.target.id!="left-default") STANDBY.leftbar = event.target.id;
						}});
    					if(STANDBY.leftbar) bar[STANDBY.leftbar].checked = true;
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar t light-txt">
				<?if(MATERIAL_ID):?><a class="tool" title="Back" href="/sitemap/<?=ARG_1?>">❬</a><?endif?>
				<div class="toolbar t">
					<label title="create page" data-translate="title" class="tool" onclick="new Box('<?=MATERIAL_NAME?>','sitemap/addpagebox/'+LANGUAGE+'/sitemap')">&#xe89c;</label>
					<?if(defined("ARG_2")):?>
					<button title="save" form="metadata" data-translate="title" class="tool transparent-bg light-txt" type="submit">&#xf0c7;</button>
					<button title="remove" form="metadata" data-translate="title" class="tool transparent-bg light-txt" type="reset">&#xe94d;</button>
					<?endif?>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<?if(defined("ARG_2")):?>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<?endif?>
			</header>
			<main class="light-txt">
				<?if(defined("ARG_2")):?>
				<iframe src="/editor/embed" width="100%" height="100%" frameborder="no"></iframe>
				<script>var EDITOR = document.currentScript.previousElementSibling.contentWindow;</script>
				<?else:?>
				<div id="tile">
				<?$roots = [];
					foreach($tree['root'] as $key=>$itm): $roots[]=$key?>
					<a class="snippet" href="/sitemap/<?=($itm['language'].'/'.$itm['name'])?>">
						<div class="preview"><img src="<?=$itm['preview']?>"></div>
						<div class="header"><?=(empty($itm['header'])?$itm['name']:$itm['header'])?></div>
						<div class="options">
							<span><?=$itm['language']?></span>
							<span <?if($itm['published']=="Published"):?>class="green-txt"<?endif?>"><?=$itm['published']?></span>
						</div>
					</a>
					<?endforeach?>
				
					<?foreach($roots as $key) foreach($tree[$key] as $itm):?>
					<a class="snippet" href="/sitemap/<?=($itm['language'].'/'.$itm['name'])?>">
						<div class="preview"><img src="<?=$itm['preview']?>"></div>
						<div class="header"><?=(empty($itm['header'])?$itm['name']:$itm['header'])?></div>
						<div class="options">
							<span><?=$itm['language']?></span>
							<span <?if($itm['published']=="Published"):?>class="green-txt"<?endif?>"><?=$itm['published']?></span>
						</div>
					</a>
					<?endforeach?>
				</div>
				<?endif?>
			</main>
			<?if(defined("ARG_2")):?>
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="metadata" class="tab body-bg light-txt" autocomplete="off">
						<div class="h-bar light-btn-bg">
							<div class="toolbar right">
								<label class="right"><small>Published</small> <input name="published" <?if($material['published']=="Published"):?>checked<?endif?> type="checkbox"></label>
							</div>
							<small>ID: <output name="PageID"><?=MATERIAL_ID?></output></small>
						</div>
						<div class="grid">
							<!-- URL Name -->					
							<fieldset><legend data-translate="textContent">to url</legend>
								<input name="toURL" value="<?=MATERIAL_NAME?>" placeholder="..." required>
							</fieldset>
							<fieldset><legend class="active-txt" data-translate="textContent">to menu</legend>
								<input name="header" value="<?=$material['header']?>" placeholder="...">
							</fieldset>
							<fieldset><legend data-translate="textContent">type</legend>
								<div class="select">
									<select name="entity">
									<?foreach(["category", "material"] as $entity):?>
										<option <?if($entity == $material['type']):?>selected<?endif?> value="<?=$entity?>"><?=$entity?></option>
									<?endforeach?>
									</select>
								</div>
							</fieldset>
							<fieldset><legend data-translate="textContent">category</legend>
								<input name="parent" value="<?=$material['parent']?>" size="15">
							</fieldset>
							<!-- Template -->
							<fieldset><legend class="active-txt" data-translate="textContent">template</legend>
								<div class="select">
									<select name="template"> 
										<?foreach(glob("../".BASE_FOLDER."/themes/".$cng->theme."/includes/*.html") as $file):$file = pathinfo($file)['filename']?>
										<option <?if($file==$material['template']):?>selected<?endif?> value="<?=$file?>"'><?=$file?></option>
										<?endforeach?>
									</select>
								</div>
							</fieldset>
							<!-- Module -->
							<fieldset><legend data-translate="textContent">module</legend>
								<div class="select">
									<select name="module">
										<?foreach(glob("../".BASE_FOLDER."/modules/*", GLOB_ONLYDIR) as $dir): $dir = basename($dir)?>
										<option <?if($dir==$material['module']):?>selected<?endif?> value="<?=$dir?>"><?=$dir?></option>
										<?endforeach?>
									</select>
								</div>
							</fieldset>
						</div>
						<div id="cover">
							<iframe frameborder="no"></iframe>
							<script>
							(function(script){
								var frame =  script.previousElementSibling;
								var	navigator = frame.contentWindow, options = [];
								navigator.standby = (window.localStorage['navigator'] || "undefined").jsonToObj() || {};

								if(navigator.standby.subdomain) options.push("subdomain="+navigator.standby.subdomain);
								if(navigator.standby[navigator.standby.subdomain]) options.push("path="+navigator.standby[navigator.standby.subdomain]);

								window.addEventListener("load",function(){
									reauth();
									navigator.location.href="/navigator/folder/image/radio?"+options.join("&");
									frame.onload=function(){
										navigator.onchange=function(event){
											if(event.target.name=="files-on-folder"){
												script.nextElementSibling.src=event.target.value;
											}
										}
									}
								});
							})(document.currentScript)
							</script>
							<img src="<?=$material['preview']?>">
						</div>
						<div class="h-bar active-bg"><small data-translate="textContent">Options</small>
							<div class="toolbar right">
								<label title="Pattern" class="tool" onclick="showPattern(optionsToJSON(doc.querySelector('#options')), jsontooptions);">⌘</label>
							</div>
							<script>
							function jsontooptions(json){
								var sobj = JSON.parse(json),
									tbody = doc.querySelector("#options>tbody");
									tbody.innerHTML = "";
								for(var key in sobj){
									var row = doc.create("tr");
									row.appendChild(doc.create("th",{title:"Add Row",class:"tool",onclick:"addRow(this)"},"+"));
									
									row.appendChild(doc.create("td",{contenteditable:"true"},key));
									row.appendChild(doc.create("td",{contenteditable:"true"},sobj[key]));
									
									row.appendChild(doc.create("th",{title:"Delete Row",class:"tool",onclick:"deleteRow(this)"},"✕"));
									tbody.appendChild(row);
								}
							}
							function optionsToJSON(owner){
								var key, options={};
								owner.querySelectorAll("tbody>tr>td").forEach(function(cell,i){
									if(i%2) options[key] = cell.textContent.trim();
									else key = cell.textContent.trim();
								});
								return JSON.encode(options);
							}
							</script>
						</div>
						<table id="options" rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
							<colgroup><col width="36"><col><col><col width="36"></colgroup>
							<tbody class="dark-txt">
							<?
							$optionset = JSON::parse($material['optionset']);
							if(!empty($optionset)):foreach($optionset as $key=>$val):?>
								<tr>
									<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
									<td contenteditable="true"><?=$key?></td>
									<td contenteditable="true"><?=$val?></td>
									<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
								</tr>
							<?endforeach?>
							<?else:?>
								<tr>
									<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
									<td contenteditable="true"></td>
									<td contenteditable="true"></td>
									<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
								</tr>
							<?endif?>
							</tbody>
						</table>
						<br>
						<fieldset><legend>Title:</legend>
							<input name="pagetitle" value="<?=$material['title']?>" placeholder="...">
						</fieldset>
						<br>
						<fieldset><legend>Context:</legend>
							<input name="context" value="<?=$material['context']?>" placeholder="...">
						</fieldset>
						<br>
						<fieldset><legend>Description:</legend>
							<textarea name="description" placeholder="..."><?=$material['description']?></textarea>
						</fieldset>
						<script>
						(function(form){
							form.onsubmit=function(event){
								event.preventDefault();
								var box = new Box(true, "boxfather/savelogbox/modal");
								box.onopen = function(){
									var mediasetform = document.querySelector("#mediaset");
										mediasetform.querySelector("iframe").contentWindow.save();
									XHR.push({
										addressee:"/sitemap/actions/save-metadata",
										body:JSON.encode({
											id:form.PageID.value,
											parent:form.parent.value.trim().translite(),
											name:form.toURL.value.trim().translite(),
											header:form.header.value.trim() || "",
											title:form.pagetitle.value.trim() || "",
											context:form.context.value.trim() || "",
											type:form.entity.value,
											module:form.module.value,
											template:form.template.value,
											preview:form.querySelector("#cover>img").src,
											description:form.description.value.trim() || "",
											published:form.published.checked ? "Published" : "Not published",
											SetID:mediasetform.setid.value,
											options:(function(properties,key){
												form.querySelectorAll("table#options>tbody>tr>td").forEach(function(cell,i){
													if(i%2) properties[key] = cell.textContent.trim();
													else key = cell.textContent.trim();
												});
												return properties;
											})({})
										}),
										onsuccess:function(response){
											var answer = JSON.parse(response);
											for(var key in answer){
												box.body.appendChild(doc.create("div", {}, "<tt><b>"+key+"</b>: "+answer[key]+"</tt>"));
											}
											box.align();
											XHR.push({
												"Content-Type":"text/html",
												"addressee":"/sitemap/actions/save-content/<?=MATERIAL_ID?>",
												"body":EDITOR.getContent(),
												"onsuccess":function(response){
													if(parseInt(response)){
														box.body.appendChild(doc.create("h3", {}, "Content - <span class='green-txt'>Saved</span>"));
													}else box.body.appendChild(doc.create("h3", {}, "Content - <span class='red-txt'>Failed save or not changes</span>"));
													box.align();
												}
											});
										}
									});
								}
							}
							form.onreset=function(event){
								event.preventDefault();
								confirmBox("remove material", function(){
									XHR.push({
										addressee:"/sitemap/actions/remove-page/<?=MATERIAL_ID?>", 
										onsuccess:function(response){
											setTimeout(function(){
												isNaN(response) ? alertBox(response) : (location.pathname = "sitemap"); 
											}, 300);
										}
									});
								});		
							}
						})(document.currentScript.parentNode);
						</script>
					</form>
					
					<input id="mediaset-tab" name="tabs" type="radio" form="rightbar-tabs" hidden>
					<form id="mediaset" class="tab">
						<div class="h-bar dark-btn-bg">
							<span class="tool">&#xe94b;</span> Mediaset
							<div class="toolbar r right">
								<label for="reset" title="reset" class="tool" data-translate="title">&#xf021;</label>
							</div>
						</div>
						<input id="reset" type="reset" hidden>
						<input name="setid" value="<?=$material['SetID']?>" type="hidden">
						<iframe width="100%" height="800px" frameborder="no"></iframe>
						<script>
						(function(form){
							var frame = form.querySelector("iframe");
							var imgset = frame.contentWindow;	
							window.addEventListener("load",function(){
								frame.onload=function(){ form.setid.value = imgset.SETID || "NULL" }
								
								reauth();
								imgset.location.href = (<?if(empty($material['SetID'])):?>false<?else:?>true<?endif?>) 
									? "/mediaset/set/<?=$material['SetID']?>"
									: "/mediaset/set";
							});
							form.onreset=function(event){
								event.preventDefault();
								imgset.location.href = "/mediaset/set";
							}
						})(document.currentScript.parentNode);
						</script>
					</form>

					<input id="codefullscreen" name="codefullscreen" type="checkbox" hidden autocomplete="off" form="rightbar-tabs">
					<input id="code-editor-tab" name="tabs" type="radio" form="rightbar-tabs" hidden>
					<div id="code" class="tab">
						<div class="h-bar dark-btn-bg">
							<span class="tool">&#xeae4;</span> HTML
							<div class="toolbar r right">
								<label for="codefullscreen" title="screen mode" data-translate="title" class="screenmode-btn"></label>
							</div>
						</div>
						<xmp><?=gzdecode($material['content'])?></xmp>
						<script>
						var	CODE = ace.edit(document.currentScript.previousElementSibling);
							CODE.setTheme("ace/theme/twilight");
							CODE.getSession().setMode("ace/mode/html");
							CODE.setShowInvisibles(false);
							CODE.setShowPrintMargin(false);
							CODE.resize();
							CODE.session.on('change', function(event){
								if(CODE.curOp && CODE.curOp.command.name){
									html_change = true;
									setTimeout(function(){
										if(html_change) EDITOR.setContent(CODE.session.getValue());
										html_change = false;
									},1000);
								}
							});
						EDITOR.onload = function(){
							EDITOR.CODE = CODE;
							EDITOR.setContent( CODE.session.getValue() );
							EDITOR.save = function(){
								XHR.push({
									"Content-Type":"text/html",
									"addressee":"/sitemap/actions/save-content/<?=MATERIAL_ID?>",
									"body":EDITOR.getContent()
								});
							}
						}
						window.addEventListener("keydown",function(event){
							if((event.ctrlKey || event.metaKey) && event.keyCode==83){
								event.preventDefault();
								EDITOR.save();
							}
						});
						</script>
					</div>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Metadata" class="tool" for="right-default" data-translate="title">&#xe871;</label>
					<div class="toolbar">
						<label title="microdata" class="tool" data-translate="title" onclick="new Box('<?=MATERIAL_ID?>','sitemap/microdatabox')">&#xe8ab;</label>
						<label title="customizer" class="tool" data-translate="title" onclick="new Box(null,'customizer/box/<?=MATERIAL_ID?>')">&#xe993;</label>
					</div>
					<div class="toolbar">
						<label title="mediaset" class="tool" for="mediaset-tab" data-translate="title">&#xe94b;</label>
						<label title="code editor" class="tool" for="code-editor-tab" data-translate="title">&#xeae4;</label>
					</div>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							STANDBY.rightbar = event.target.id;
						}});
						if(STANDBY.rightbar) bar[STANDBY.rightbar].checked = true;
						bar.codefullscreen.onchange=function(){
							CODE.resize();
						}
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
			<?endif?>
		</div>
		<script>
		<?if(defined("ARG_2")):?>
		(function(body){
			body.querySelector("#screenmode").checked = (STANDBY.screenmode=="true");
		})(document.currentScript.parentNode);
		<?endif?>
		var LANGUAGE = "<?=$language?>";
		function reloadTree(lang){
			var path = location.pathname.split(/\//);
			path[2] = lang;
			XHR.push({
				addressee:"/sitemap/actions/reload-tree/"+lang+"/sitemap",
				onsuccess:function(response){
					doc.querySelector("#sitemap>.root").outerHTML = response;
					window.history.pushState("", "tree", path.join("/"));
				}
			});
		}
		</script>
	</body>
</html>