<?php
	
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	
	define("FH", 800);
	define("FW", 1400);
	define("HSPACE", 50);
	define("VSPACE", 50);
	
	if(defined("ARG_1")){
		$from = explode("-", ARG_1);
		$from = mktime(0,0,0, (INT)$from[1], (INT)$from[2], (INT)$from[0]);
	}else $from = mktime(0,0,0)-1209600;
	
	if(defined("ARG_2")){
		$to = explode("-", ARG_2);
		$to = mktime(0,0,0, (INT)$to[1], (INT)$to[2], (INT)$to[0]);
	}else $to = mktime(0,0,0);
	
	define("PERIOD", floor(($to - $from) / 86400) * 86400);
	$mySQL->real_query("SELECT day,views,reviews FROM `gb_user-analytics` WHERE day BETWEEN ".$from." AND ".$to);
	$list=[];
	if($result=$mySQL->store_result()){
		while($row = $result->fetch_assoc()){
		    $list[$row['day']] = ["views"=>$row['views'], "views"=>$row['views'], "reviews"=>$row['reviews']];
			$unique += $row['views'];
			$total += $row['reviews'];
		}
		$result->free();
	}
	$mySQL->real_query("SELECT day,views FROM `gb_user-analytics` WHERE day BETWEEN ".($from - PERIOD)." AND ".($to - PERIOD));
	if($result=$mySQL->store_result()){
		while($row = $result->fetch_assoc()){
		    $list[$row['day'] + PERIOD]['last'] = $row['views'];
		}
		$result->free();
	}
	
	$date = new DateTime(date("Y-m-d", $from));
	for($i=$from; $i<=$to; $i+=86400){
		$timestamp = $date->format("U");
		$list['days'][] = $date->format("d F, Y");
		$list['views'][] = (INT)$list[$timestamp]['views'];
		$list['reviews'][] = (INT)$list[$timestamp]['reviews'];
		$list['last'][] = (INT)$list[$timestamp]['last'];
		unset($list[$timestamp]);
		$date->add(new DateInterval('P1D'));
	}

	$uniquemax = max($list['views']);
	$min = min([min($list['views']), min($list['last'])]);
	$max = max([max($list['reviews']), max($list['last'])]);
	
	
	$xf = floor(FW / (PERIOD / 86400));
	$yf = round(FH / ($max - $min), 2);
	
	
	for($i=count($list['views']); $i--;){
		$x = $i * $xf + HSPACE;

		$view = (FH + VSPACE - (($list['views'][$i] - $min) * $yf));
		$review = (FH + VSPACE - (($list['reviews'][$i] - $min) * $yf));
		$last = (FH + VSPACE - (($list['last'][$i] - $min) * $yf));
	
		$points .= "<g data-day='".$list['days'][$i]."'>";
		$points .= "<line x1='".$x."' x2='".$x."' y1='".VSPACE."' y2='".(FH+VSPACE)."' class='subaxis'/>";
		$points .= "<circle cx='".$x."' cy='".$last."' data-title='Last period' data-value='".$list['last'][$i]."' r='4' stroke='#BAC' stroke-width='0' fill='#BAC'/>";
		$points .= "<circle cx='".$x."' cy='".$review."' data-title='Total views' data-value='".$list['reviews'][$i]."' r='5' stroke='#2DB' stroke-width='0' fill='#2DB'/>";
		$points .= "<circle cx='".$x."' cy='".$view."' data-title='Unique views' data-value='".$list['views'][$i]."' r='6' stroke='#F68' stroke-width='0' fill='#F68'/>";
		$points .= "</g>";
		
		$list['views'][$i] = $x.",".$view;
		$list['reviews'][$i] = $x.",".$review;
		$list['last'][$i] = $x.",".$last;
	}
	
	$paddingLeft = HSPACE.",".(FH+VSPACE);
	$paddingRight = (FW+HSPACE).",".(FH+VSPACE);
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/analytics/tpl/analytics.css">
		<script src="/modules/analytics/tpl/analytics.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=analytics" defer charset="utf-8"></script>
		<style>
		#pages{overflow:auto;}
		#pages>table{
			width:100%;
			min-width:800px;
		}
		#infobar{
			z-index:6;
			padding:10px;
			font-size:13px;
			position:fixed;
			box-shadow:8px 8px 4px -5px rgba(0,0,0,0.4);
		}
		#infobar::before{
			display:block;
			font-size:16px;
			content:attr(title);
		}
		#infobar>div{text-align:right;}
		#infobar>div::before{
			float:left;
			content:attr(title);	
		}
		#total{
			padding:20px;
			display:flex;
			justify-content:space-around;
		}
		#wrapper>main th>a{color:#268}
		.pagination>a{
			color:inherit;
			margin:0px 5px;
			font-size:18px;
		}
		#wrapper>main th>a:hover,
		.pagination>a:hover,
		.pagination>a.selected{color:#00ADF0}
		</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden disabled>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden disabled="">
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
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
					<script>document.currentScript.parentNode.onsubmit=function(event){ event.preventDefault(); }</script>
				</form>
			</aside>
			<header class="h-bar body-bg light-txt">
				<form class="toolbar t">
					<span class="tool">&#xe900;</span>
					<input required name="from" type="date" value="<?=date("Y-m-d", $from)?>" size="9">
					<input required name="to" type="date" value="<?=date("Y-m-d", $to)?>" size="9"> 
					<script>
					(function(form){
						form.onchange=function(event){
							location.pathname = "/analytics/"+form.from.value+"/"+form.to.value;
						}
					})(document.currentScript.parentNode);
					</script>
				</form>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main class="white-bg">
				<br>
				<svg width="100%" viewBox="0 0 <?=((FW+HSPACE*2)." ".(FH+VSPACE*2))?>" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<style>
						line.axis{
							stroke:#000;
							stroke-width:0.6;
							stroke-dasharray:1,2;
						}
						g:hover>line.subaxis{
							stroke:#555;
						 	stroke-width:2;
						 	stroke-dasharray:2,4;
						}
						g:hover>circle{
							stroke-width:15;
						}
					</style>
					<polygon points="<?=($paddingLeft." ".implode(" ", $list['reviews'])." ".$paddingRight)?>" stroke-width="0" fill="#3EC"/>
					<polygon points="<?=($paddingLeft." ".implode(" ", $list['views'])." ".$paddingRight)?>" stroke-width="0" stroke="#F06" fill="#FAA"/>
					<polygon points="<?=($paddingLeft." ".implode(" ", $list['last'])." ".$paddingRight)?>" stroke-width="0" fill="#536" fill-opacity="0.2"/>
				
					<?php
					print($points);
	
					for($i=VSPACE; $i<=(FH+VSPACE); $i+=50):?>
					<line x1="<?=HSPACE?>" x2="<?=(FW+HSPACE)?>" y1="<?=$i?>" y2="<?=$i?>" class="axis"/>
					<?endfor?>
					<?for($i=HSPACE; $i<=(FW+HSPACE); $i+=50):?>
					<line x1="<?=$i?>" x2="<?=$i?>" y1="<?=VSPACE?>" y2="<?=(FH+VSPACE)?>" class="axis"/>
					<?endfor?>
					<circle cx="60" cy="10" r="10" fill="#3EC"/>
					<text x="80" y="15" fill="grey" data-translate="textContent">total views</text>
					<circle cx="230" cy="10" r="10" fill="#F06"/>
					<text x="250" y="15" fill="grey" data-translate="textContent">unique views</text>
					<circle cx="440" cy="10" r="10" fill="#BAC"/>
					<text x="460" y="15" fill="grey" data-translate="textContent">unique of last</text>
					<script>
					(function(svg){
						svg.querySelectorAll("g").forEach(function(g){
							g.onmouseover = function(evt){
								var infobar = doc.create("div",{
									id:"infobar",
									class:"light-btn-bg",
									title:g.getAttribute("data-day"),
									style:"top:"+evt.clientY+"px;left:"+(evt.clientX+2)+"px"
								});
								g.querySelectorAll("circle").forEach(function(point){
									infobar.appendChild( doc.create("div",{title:point.getAttribute("data-title")+": "},point.getAttribute("data-value")) );
								});
								document.body.appendChild(infobar);
								if((infobar.offsetTop + infobar.offsetHeight) > screen.height){
									infobar.style.top = (evt.clientY - infobar.offsetHeight)+"px";
								}
								if((infobar.offsetLeft + infobar.offsetWidth) > screen.width){
									infobar.style.left = (evt.clientX - infobar.offsetWidth)+"px";
								}
								g.onmouseout = function(){ document.body.removeChild(infobar); }
							}
						});
					})(document.currentScript.parentNode)
					</script>
				</svg>
				<div id="total">
					<span><label data-translate="textContent">max unique</label>: <big><?=($uniquemax)?></big></span>
					<span><label data-translate="textContent">total unique</label>: <big><?=($unique)?></big></span>
					<span><label data-translate="textContent">total views</label>: <big><?=($total)?></big></span>
				</div>
			<?php
			/* Pages view ******************************************************/
	
			$limit = 30;
			$page = defined("ARG_3") ? ARG_3 : 1;
	
			$list = $mySQL->get("SELECT SQL_CALC_FOUND_ROWS * FROM gb_pages LEFT JOIN gb_sitemap USING(PageID) CROSS JOIN gb_static USING(PageID) ORDER BY language,PageID DESC LIMIT {int}, {int}", ($page-1)*$limit, $limit);
			$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];
			?>
				<div id="pages">
					<table cellspacing="0" cellpadding="10" rules="cols" bordercolor="#EEE">
						<thead>
							<tr>
								<th>Language / name</th>
								<th>header</th>
								<th>subheader</th>
								<th>context</th>
								<th>description</th>
								<th data-translate="textContent">views</th>
								<th data-translate="textContent">average</th>
							</tr>
						</thead>
						<tbody>
							<?foreach($list as $row):
							$time = ($row['time'] / $row['views'])>>0?>
							<tr data-id="<?=$row['PageID']?>">
								<th><a target="_blank" href="<?=(BASE_DOMAIN."/".$row['language']."/".$row['name'])?>"><?=($row['language']."/".$row['name'])?></a></th>
								<td contenteditable="true" onfocus="showEditorBox(event.target, <?=$row['PageID']?>)" class="header"><?=$row['header']?></td>
								<td contenteditable="true" class="subheader"><?=$row['subheader']?></td>
								<td contenteditable="true" class="context"><?=$row['context']?></td>
								<td contenteditable="true" oclass="description"><?=$row['description']?></td>
								<th><?=$row['views']?></th>
								<th><?=sprintf("%02d:%02d", ($time / 60)>>0, ($time % 60))?> <progress value="<?=$time?>" max="600" style="width:120px"></progress></th>
							</tr>
							<?endforeach?>
							<script>
							(function(tbody){
								tbody.querySelectorAll("td").forEach(function(cell){
									cell.onfocus=function(){
										var id = cell.parentNode.dataset.id;
										if(isNaN(["context","header","subheader","description"].inArray(cell.className))) return false
										var box = new Box("", "analytics/editorbox/"+cell.className, function(){
											XHR.push({
												addressee:"/sitemap/actions/save-"+cell.className+"/"+id,
												body:box.window.field.value || " ",
												onsuccess:function(response){
													if(parseInt(response)){
														cell.textContent = box.window.field.value;
														box.drop();
													}
												}
											});
										});
										box.onopen = function(){
											box.window.field.value = cell.textContent;
											box.window.field.focus();
										}
									}
								});
							})(document.currentScript.parentNode)
							</script>
						</tbody>
						<tfoot>
							<tr class="dark-btn-bg" align="right">
								<td colspan="7" class="pagination">
								<?php
								$total=ceil($count/$limit);
								$path="analytics/".date("Y-m-d",$from)."/".date("Y-m-d",$to);
								if($total>1):
									if($page>4):
										$j=$page-2?>
										<a href="/<?=$path?>/1">1</a> ... 
									<?else: $j=1; endif;
									for(; $j<$page; $j++):?><a href="/<?=($path."/".$j)?>"><?=$j?></a><?endfor?>
									<a class="selected"><?=$j?></a>
									<?if($j<$total):?>
										<a href="/<?=($path."/".(++$j))?>"><?=$j?></a>
										<?if(($total-$j)>1):?>
						 					... <a href="/<?=($path."/".$total)?>"><?=$total?></a>
										<?elseif($j<$total):?>
											<a href="/<?=($path."/".$total)?>"><?=$total?></a>
										<?endif;
									endif;
								endif?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</main>
		</div>
	</body>
</html>