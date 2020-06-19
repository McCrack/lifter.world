<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<?include_once("components/head.php")?>
		<style>
			@font-face {
  				font-family:'iconset';
  				src: url(/modules/editor/iconset.ttf) format('truetype');
			}
			html,body{
				margin:0px;
				height:100%;
				overflow:hidden;
				background-color:#AAA;
			}
			#content{
				margin:0 auto;
				max-width:780px;
				height:calc(100% - 40px);
				padding:10px 5vh 40px 5vh;
				
				overflow:auto;
				font:18px/1.6 calibri;
				box-sizing:border-box;
				background-color:white;
				box-shadow:1px 0px 4px -2px rgba(0,0,0, .5)
			}
			#content:focus{ outline:none; }
			#content img{
				max-width:100%;
			}
			#content>p,
			#content>h1,
			#content>h2,
			#content>h3,
			#content>h4,
			#content>ul,
			#content>ol,
			#content>div,
			#content>figure,
			#content>blockquote{
				margin:4px 0px;
				min-height:20px;
				line-height:1.4;
				outline:1px dotted #CCC;
				box-shadow:0px 10px 4px -10px rgba(0,0,0,0.4);
			}
			#content>p,
			#content>h1,
			#content>h2,
			#content>h3,
			#content>h4,
			#content>ul,
			#content>ol{
				padding:16px 5%;
			}
			h1,h2{
				line-height:1;
			}
			h1{
				font-size:36px;
			}
			h2{
				font-size:22px;
			}
			#content>blockquote{
				font-style:italic;
				padding:5vh 3vw 5vh 10vw;
				background:url(/images/quotes.jpg) 10px 10px no-repeat;
			}
			#content>figure{
				font-size:0;
				margin:5px -2vw;
			}
			#content>figure>img,
			#content>figure>video{
				width:100%;
			}
			#content>figure>figcaption{
				font-size:15px;
				min-height:24px;
				padding:4px 14px;
			}
			#content>figure.video,
			#content>figure.youtube{
				position:relative;
				padding-bottom:56.25%;
			}
			#content>figure.video>video,
			#content>figure.video>iframe,
			#content>figure.youtube>iframe{
				top:0;
				left:0;
				height:100%;
				position:absolute;
			}
			#content a{
				color:#0090E0;
			}
			header{
				position:sticky;
				box-shadow:0 6px 3px -4px rgba(0,0,0, .4);
			}
			.toolbar{
				min-width:36px;
			}
			.toolset{
				top:0;
				width:36px;
				height:34px;
				overflow:hidden;
				position:absolute;
			}
			.toolset:hover{
				height:auto;
				overflow:visible;
			}
			label.tool{
				color:#567;
				width:36px;
				margin:1px 0;
				border-radius:3px;
				font:16px/32px iconset;
				background-color:#EEE;
				background:linear-gradient(to top, #DDD, #FFF);
			}
			label.tool:hover{
				color:#FFF;
				background:#E55;
			}
			label.tool::after{
				color:white;
				pointer-events:none;
				background-color:#00ADF0;
			}
			.toolset:hover>.tool{
				box-shadow:5px 5px 3px -4px rgba(0,0,0, .5);
			}
			#toolbar input{
				margin:2px;
				width:64px;
				height:30px;
				padding:5px;
				border-radius:3px;
				text-align:center;
				box-sizing:border-box;
				border:1px solid #BBB;
				vertical-align:middle;
				background-image:linear-gradient(to top, #FFF, #EEE);
				box-shadow:inset 0 0 5px -2px rgba(0,0,0, .5);
			}
			.right{ float:right; }
			/* VIDEO *****************************/

			.video{
				position:relative;
				padding-bottom:56.25%;
			}
			.video>video,
			.video>iframe{
				top:0;
				left:0;
				width:100%;
				height:100%;
				position:absolute;
			}
		</style>
		<script defer src="/js/gbAPI.js"></script>
		<script defer src="/modules/editor/editor.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d=editor" defer charset="utf-8"></script>
	</head>
	<body id="editor">
		<header class="h-bar light-btn-bg">
			<div class="toolbar r right">
				<label data-translate="title" title="element properties" class="tool right" onclick="doc.properties()">&#xe992;</label>
			</div>
			<div class="toolbar l">
				<label data-translate="title" title="spell check" class="tool" onclick="doc.spellCheck()">&#xea12;</label>
			</div>
			<div class="toolbar l">
				<div class="toolset">
					<label data-translate="title" title="create link" class="tool" data-tag="A" onmousedown="doc.createlink();">&#xe9cb;</label>
					<label data-translate="title" title="bold" class="tool" data-tag="B" onmousedown="doc.insertTag('bold', 'B')">&#xea62;</label>
					<label data-translate="title" title="italic" class="tool" data-tag="I" onmousedown="doc.insertTag('italic','I')">&#xea64;</label>
					<label data-translate="title" title="underline" class="tool" data-tag="U" onmousedown="doc.insertTag('underline','U')">&#xea63;</label>
					<label data-translate="title" title="strike" class="tool" data-tag="S" onmousedown="doc.insertTag('strikeThrough','S')">&#xea65;</label>
					<label data-translate="title" title="insert free tag" class="tool" data-tag="OTHER" onmousedown="doc.freeTag(this)">&#xea80;</label>
				</div>
			</div>
			<div class="toolbar r">
				<div class="toolset">
					<label data-translate="title" title="paragraph" class="tool" data-tag="P" onmousedown="doc.formatblock('p')">&#xea73;</label>
					<label data-translate="title" title="header level 1" class="tool" onmousedown="doc.formatblock('h1')">H1</label>
					<label data-translate="title" title="header level 2" class="tool" onmousedown="doc.formatblock('h2')">H2</label>
					<label data-translate="title" title="header level 3" class="tool" onmousedown="doc.formatblock('h3')">H3</label>
					<label data-translate="title" title="header level 4" class="tool" onmousedown="doc.formatblock('h4')">H4</label>
					<label data-translate="title" title="quote" class="tool" data-tag="blockquote" onmousedown="doc.formatblock('blockquote')">&#xe977;</label>
				</div>
			</div>
			<div class="toolbar r">
				<div class="toolset">
					<label data-translate="title" title="insert figure" class="tool" onmousedown="doc.figureBox()">&#xe927;</label>
					<label data-translate="title" title="insert image" class="tool" onmousedown="doc.imgBox()">&#xe90d;</label>
					<label data-translate="title" title="insert youtube" class="tool" onmousedown="doc.youtubeBox()">▶</label>
					<label data-translate="title" title="insert video" class="tool" onmousedown="doc.videoBox()"></label>
					<label data-translate="title" title="insert pattern" class="tool" onmousedown="doc.patternBox()"></label>
				</div>
			</div>
			<div class="toolbar l">
				<div class="toolset">					
					<label data-translate="title" title="bulleted list" class="tool" data-tag="UL" onmousedown="doc.list('insertUnorderedList')">&#xe9bb;</label>
					<label data-translate="title" title="numbered list" class="tool" data-tag="OL" onmousedown="doc.list('insertOrderedList')">&#xe9b9;</label>
				</div>
			</div>
			<div class="toolbar t">
				<label data-translate="title" title="drop tag" class="tool" onmousedown="doc.drop()">&#xe9ac;</label>
			</div>
			<div class="toolbar t">
				<div class="toolset">
					<label data-translate="title" title="align left" class="tool" onmousedown="doc.setProperty('align','left')">&#xea77;</label>
					<label data-translate="title" title="align center" class="tool" onmousedown="doc.setProperty('align','center')">&#xea78;</label>
					<label data-translate="title" title="align justify" class="tool" onmousedown="doc.setProperty('align','justify')">&#xea7a;</label>
					<label data-translate="title" title="align right" class="tool" onmousedown="doc.setProperty('align', 'right')">&#xea79;</label>
				</div>
			</div>
			<form id="toolbar" class="toolbar">
				<input name="fsize" oninput="doc.setFontSize(this.value)" placeholder="px" size="3" list="font-sizes">
				<datalist id="font-sizes">
					<option>12px</option>
					<option>14px</option>
					<option>16px</option>
					<option>18px</option>
					<option>22px</option>
					<option>24px</option>
					<option>28px</option>
					<option>32px</option>
					<option>36px</option>
					<option>48px</option>
					<option>52px</option>
					<option>60px</option>
				</datalist>
			</form>
		</header>
		<article id="content" contenteditable="true" onblur="syncContent()"></article>
		<script>
			var CODE;
			var EDITOR = document.currentScript.previousElementSibling;
			var getContent = function(){
				return EDITOR.innerHTML;
			}
			var setContent = function(value){
				EDITOR.innerHTML = value;
			}
			var syncContent = function(){
				CODE.session.setValue( EDITOR.innerHTML );
			}
		</script>
	</body>
</html>