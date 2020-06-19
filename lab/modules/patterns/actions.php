<?php
switch(ARG_2){
	case "get-pattern":
		$data = file_get_contents($_GET['path']);
		print($data);
	break;
	case "save-pattern":
		$data = file_get_contents('php://input');
		$map = explode("/", $_GET['path']);
		file_put_contents($_GET['path']."/".ARG_3.".".$map[1], $data)?>

		<div class="root"><?=patterns_tree("patterns/".$map[1], array_slice($map, 2))?></div>
	<?break;
	case "create-folder":
		$path = file_get_contents('php://input');
		mkpath($path)?>
		<div class="root"><?=patterns_tree("patterns/".ARG_3, array_slice(explode("/",$path), 2))?></div>
	<?break;
	case "remove":
		$map = explode("/", $_GET['path']);
		if(ARG_3==="folder"){
			 deletedir($_GET['path']);
		}elseif(file_exists($_GET['path']."/".ARG_3.".".$map[1])){
			unlink($_GET['path']."/".ARG_3.".".$map[1]);
		}?>
		<div class="root"><?=patterns_tree("patterns/".$map[1], array_slice($map, 2, -1))?></div>
	<?break;
	default:break;
}

/*******************************************************************/

function patterns_tree($root, $map, $level=0){
	foreach(glob($root."/*", GLOB_ONLYDIR) as $i=>$dir):
		$folder = basename($dir);
		if($folder==$map[0]) $entry = array_shift($map)?>
		<input id="l-<?=($level.'-'.$i)?>" type="radio" name="l-<?=$level?>" value="<?=$dir?>" <?if($folder==$entry):?>checked<?endif?> hidden>
		<label for="l-<?=($level.'-'.$i)?>" data-path="<?=$dir?>"><?=$folder?></label>
		<div class="root"><?=patterns_tree($dir, $map, $level+1)?></div>
	<?endforeach;
	$iconset = [
		"application"=>"&#xeae3;",
		"text"=>"&#xe926",
		"zip"=>"&#xe92b",
		"html"=>"&#xeae4", 
		"css"=>"&#xeae6",
		"php"=>"&#xf069",
		"json"=>"&#xe8ab",
		"init"=>"&#xe995",
		"js"=>"&#xf013"
	];
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