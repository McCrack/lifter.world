<!DOCTYPE html>
<html>
<head>
	<?include_once("components/head.php")?>
	<style>
		body{
			overflow:auto;
			padding:15px;
			background:white;
			display:grid;
			grid-gap:6px;
			grid-template-columns:repeat(auto-fill, minmax(200px, min-content));
		}
		label>div{
			padding:8px;
			font-size:14px;
			text-align:center;
			word-wrap:break-word;
			border:1px solid transparent;
		}
		label:hover>div,
		label>input:checked+div{
			border-color:#70EDF0;
			background-color:#E0F0FF;
		}
		label>div>img{
			width:100%;
			max-height:120px;
			object-fit:contain;
		}
		label>div>img::before{
			color:#EA4;
			width:100%;
			height:100%;
			display:block;
			content:attr(alt);
			font:48px/64px tools;
		}
		label>div>div[contenteditable]{
			padding:5px;
		}
		label>div>div[contenteditable]:focus{
			background-color:white;
		}
	</style>
	<script>
	var selectAll = function(){
		document.querySelectorAll("input").forEach(function(inp){
			inp.checked = !inp.checked;
		});
	}
	var setFilter = function(rule, val){
		document.querySelectorAll("input").forEach(function(inp){
			let img = inp.nextElementSibling.getElementsByTagName("IMG")[0];
			if(img[rule] < val){
				inp.checked = false;
				inp.disabled = true;
				inp.parentNode.style.display = "none";
			}else{
				inp.disabled = false;
				inp.parentNode.removeAttribute("style");
			}
		});
	}
	getSelected = function(){
		var lst = [];
		document.querySelectorAll("input:checked+div").forEach(function(div){
			 lst.push({
			 	url:div.dataset.path,
			 	filename:div.textContent.trim().translite()
			 });
		});
		return lst;
	}
	</script>
</head>
<body>
	<?php
	$html = file_get_contents($_GET['path']);
	$dom = new DOMDocument();
	$dom->loadHTML($html);
	$images=$dom->getElementsByTagName("img");
	$page_url=parse_url($_GET['path']);
	
	for($i=0; $i<$images->length; $i++):
		$img_url = parse_url($images->item($i)->getAttribute("src"));
		if(empty($img_url['scheme'])){
			$img_url['scheme']=$page_url['scheme'];
			$img_url['host']=$page_url['host'];
		}
		$path=$img_url['scheme']."://".$img_url['host']."".$img_url['path']?>
		<label>
			<input type="checkbox" hidden>
			<div data-path="<?=$path?>">
				<img src="<?=$path?>" alt="&#xe927;">
				<div contenteditable="true"><?=basename($img_url['path'])?></div>
			</div>
		</label>
	<?endfor?>
</body>
</html>