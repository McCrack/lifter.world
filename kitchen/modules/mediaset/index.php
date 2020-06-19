<?php
$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=uploader" defer charset="utf-8"></script>
		<style>
		.root>a.mediaset::before{
			content:"—\e94b";
		}
		main{font-size:0;}
		.grid{
			display:grid;
			grid-gap:10px;
			grid-template-columns:repeat(auto-fill, minmax(280px, min-content));
		}
		main>section>.snippet{
			color:#444;
			font-size:18px;
			text-align:center;
			background-color:white;
		}
		</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden disabled>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="mediaset-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="categories" class="tab body-bg">
						<div class="root light-txt">
							<?$categories = $mySQL->getTree("Name","Category","SELECT SetID,Category,Name FROM gb_media ORDER BY category");
							foreach($categories as $category=>$sets):?>
							<input id="<?=$category?>" type="radio" name="mediaset" value="<?=$category?>" <?if($category==ARG_1):?>checked<?endif?> hidden autocomplete="off">
							<label for="<?=$category?>"><?=$category?></label>
							<div class="root">
								<?foreach($sets as $set):?>
								<a class="mediaset<?if($set['SetID']==ARG_2):?> active-txt<?endif?>" href="/mediaset/<?=$category?>/<?=$set['SetID']?>"><?=$set['Name']?></a>
								<?endforeach?>
							</div>
							<?endforeach?>
							<script>
							(function(root){
								root.onchange=function(event){
									history.pushState({}, event.target.value, "/mediaset/"+event.target.value+"/"+(location.pathname.split(/\//)[3]||""));
								}
							})(document.currentScript.parentNode)
							</script>
						</div>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="categories" class="tool" for="mediaset-tab" data-translate="title">&#xe94b;</label>
					</div>
					<div class="toolbar">
						<label title="navigator" class="tool" data-translate="title" onclick="new Box(null, 'navigator/box')">&#xf07c;</label>
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
				</form>
			</aside>
			<header class="h-bar light-btn-bg">
				<div class="toolbar t">
					<label title="create mediaset" data-translate="title" class="tool" onclick="MEDIASET.createSet()">&#xe89c;</label>
					
					<label title="save" data-translate="title" class="tool" onclick="MEDIASET.save()">&#xf0c7;</label>
					<label title="remove" data-translate="title" class="tool" onclick="MEDIASET.remove()">&#xe94d;</label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main>
				<iframe width="100%" height="100%" frameborder="no"></iframe>
				<script>
				reauth();
				var IMGFRAME = document.currentScript.previousElementSibling;
				var MEDIASET = IMGFRAME.contentWindow;
				<?if(defined("ARG_2")):?>
				MEDIASET.location.href = "/mediaset/set/<?=ARG_1?>/<?=ARG_2?>";
				<?elseif(defined("ARG_1")):?>
				MEDIASET.location.href = "/mediaset/set/<?=ARG_1?>";
				<?else:?>
				MEDIASET.location.href = "/mediaset/set";
				<?endif?>
				IMGFRAME.onload=function(){
					var path = MEDIASET.location.pathname.split(/\//);
					if(path[4]){
						history.pushState({}, "MediaSet", "/mediaset/"+path[3]+"/"+path[4]);
					}else if(path[3]) history.pushState({}, "mediaset", "/mediaset/"+path[3]);
				}
				</script>
			</main>
		</div>
	</body>
</html>