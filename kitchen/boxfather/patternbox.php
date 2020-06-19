<?php
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
?>
<style>
.pattern-box>div.box-body{
	display:grid;
	padding:0 1px;
	min-height:400px;
}
.pattern-box>div.box-body>aside{
	overflow:auto;
	padding:0 10px;
}
@media (max-width:460px){
	.pattern-box>div.box-body{grid-template-columns:180px auto;}
}
@media (min-width:461px){
	.pattern-box{
		width:820px;
	}
	.pattern-box>div.box-body{grid-template-columns:240px auto;}
}
.pattern-box>div.box-caption{
	font-size:16px;
}
.pattern-box>.h-bar>input[name='path'],
.pattern-box>.h-bar>input[name='pname']{
	border-width:0;
	height:24px;
	padding:0 10px;
	vertical-align:middle;
	box-sizing:border-box;
}
.pattern-box>.h-bar>input[name='path']{
	width:280px;
	max-width:32%;
	margin-left:-12px;
}
.pattern-box>.h-bar>input[name='pname']{
	width:140px;
}
</style>
<form class="box pattern-box white-bg">
	<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
	
	<?include_once("boxfather/patterns/".ARG_1.".php")?>
</form>