<?php


if(defined("ARG_3")){
	define("SETID", ARG_3);
	$mediaset = $mySQL->getRow("SELECT * FROM gb_media WHERE SetID={int} LIMIT 1", SETID);
}elseif(defined("ARG_2")){
	if(is_numeric(ARG_2)){
		define("SETID", ARG_2);
		$mediaset = $mySQL->getRow("SELECT * FROM gb_media WHERE SetID={int} LIMIT 1", SETID);
		define("CATEGORY", $mediaset['Category']);
	}else{
		define("SETID", false);
		define("CATEGORY", ARG_2);
		$category = $mySQL->getGroup("SELECT SetID,language,Name,Mediaset FROM gb_media WHERE Category LIKE {str}", CATEGORY);
	}
}else{
	define("SETID", false);
	define("CATEGORY", false);
	$categories = $mySQL->getGroup("SELECT Category FROM gb_media GROUP BY Category LIMIT 50")['Category'];
}

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<style>
		body{
			overflow:auto;
		}
		main>a.snippet{
			color:#444;
			width:280px;
			min-height:150px;
			vertical-align:top;
			display:inline-block;
			background-color:white;
		}
		main>a.snippet::after{
			padding:5px;
			display:block;
			font-size:16px;
			text-align:center;
			content:attr(title);
		}

		form{
			z-index:2;
			top:0;
			left:0;
			width:100%;
			position:absolute;
			padding-bottom:24%;
		}
		body>form::before{
			top:15px;
			left:15px;
			color:#FFF;
			font-size:16px;
			position:absolute;
			content:attr(title);
			text-transform:capitalize;
			mix-blend-mode:difference;
		}
		body>form>button{
			color:#EEE;
			cursor:pointer;
			border-width:0;
			border-radius:3px;
			position:absolute;
		}
		body>form>button#add-btn{
			top:10px;
			right:10px;
			font-size:12px;
			line-height:16px;
			background-color:#802;
			padding:2px 15px 2px 5px;
		}
		body>form>button#add-btn::before{
			width:22px;
			content:"\e2c7";
			margin-right:5px;
			font:16px/18px tools;
			display:inline-block;
			vertical-align:middle;
		}
		body>form>button#add-btn:hover::before{
			content:"\f07c";
		}
		body>form>button#left-btn,
		body>form>button#right-btn{
			bottom:0;
			font-size:20px;
			padding:6px 12px;
		}
		body>form>button#left-btn{left:10px;}
		body>form>button#right-btn{right:10px;}
		
		main{
			font-size:0;
			overflow:hidden;
			white-space:nowrap;
		}
		main>form{
			width:100%;
			position:relative;
			display:inline-block;
			padding-bottom:56.25%;
		}
		main>form>img,
		main>form>video{
			width:100%;
			height:100%;
			position:absolute;
			object-fit:contain;
			background-color:rgba(0,0,0, .4);
		}
		main>form>fieldset{
			z-index:2;
			left:20px;
			bottom:48px;
			border-width:0;
			text-align:right;
			position:absolute;
		}
		main>form>fieldset>input,
		main>form>fieldset>textarea{
			padding:8px;
			border-radius:3px;
			box-sizing:border-box;
			border:1px solid white;
			background-color:rgba(255,255,255, .8);
			box-shadow:10px 10px 5px -8px rgba(0,0,0, .5);
		}
		main>form>fieldset>input{
			width:100%;
			font:14px calibri,helvetica,arial;
		}
		main>form>fieldset>textarea{
			font-size:12px;
			min-width:300px;
			min-height:90px;
		}
		main>form>fieldset>label{
			color:#444;
			margin:1px;
			cursor:pointer;
			text-align:center;
		}
		main>form>fieldset>label>input+span{
			width:30px;
			height:30px;
			display:inline-block;
			font:24px/30px tools;
			background-color:#DDD;
			border:1px solid white;
		}
		main>form>fieldset>label:hover,
		main>form>fieldset>label>input:checked+span{
			color:#00ADF0;
			background-color:#222;
		}
		section{
			padding:10px;
			display:grid;
			grid-gap:10px;
			grid-template-columns:repeat(auto-fill, minmax(160px, min-content));
		}
		section>.snippet{
			color:#444;
			text-align:center;
			background-color:white;
		}
		section>.snippet>div.header{
			font-size:16px;
			padding:4px 8px;
		}
		</style>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" defer charset="utf-8"></script>
	</head>
	<body>
		<?if(SETID):?>
		<main>
			<?$set = JSON::parse($mediaset['Mediaset']);
			foreach($set as $img):?>
			<form>
				<fieldset>
					<label><input type="radio" hidden <?if($img['position']=="top"):?>checked<?endif?> name="position" value="top"><span>&#xe86b;</span></label>
					<label><input type="radio" hidden <?if($img['position']=="bottom"):?>checked<?endif?> name="position" value="bottom"><span>&#xe3c8;</span></label>
					<label><input type="radio" hidden <?if($img['color']=="light"):?>checked<?endif?> name="color" value="light"><span>&#xf069;</span></label>
					<label><input type="radio" hidden <?if($img['color']=="dark"):?>checked<?endif?> name="color" value="dark"><span>&#xe94e;</span></label>
					<br>
					<textarea placeholder="Description" name="description"><?=$img['description']?></textarea>
					<br>
					<input value="<?=$img['alt']?>" placeholder="Alternative Text" name="alt">
				</fieldset>
				<?if($img['type']=="video"):?>
				<video src="<?=$img['url']?>" controls></video>
				<?else:?>
				<img src="<?=$img['url']?>">
				<?endif?>
			</form>
			<?endforeach?>	
			<script>var SLIDER = document.currentScript.parentNode</script>
		</main>
		<section>
			<?foreach($set as $img):?>
			<div class="snippet">
				<label class="drop-snippet">✕</label>
				<div class="preview">
					<?if($img['type']=="video"):?>
					<video src="<?=$img['url']?>" preload="metadata"></video>
					<?else:?>
					<img src="<?=$img['url']?>">
					<?endif?>
				</div>
				<div class="header" contenteditable="true"><?=$img['key']?></div>
			</div>
			<?endforeach?>
			<script>
			var timeout,
				SETID = <?=SETID?>,
				MEDIASET = document.currentScript.parentNode;
			MEDIASET.refresSlideshow = function(){
				MEDIASET.querySelectorAll(".snippet").forEach(function(img,i){
					img.onmouseover=function(){
						SLIDER.shotSlide(i*SLIDER.offsetWidth);
					}
				});
				MEDIASET.querySelectorAll("label").forEach(function(label,i){
					label.onclick=function(){
						SLIDER.removeChild( SLIDER.querySelectorAll("form")[i] );
						MEDIASET.removeChild( label.parentNode);
						MEDIASET.refresSlideshow();
					}
				});
			}
			MEDIASET.refresSlideshow();
			</script>
		</section>
		<form title="<?=$mediaset['Category']?> / <?=$mediaset['Name']?>">
			<button name="previous" id="left-btn" data-dir="-1" class="dark-btn-bg">❰</button>
			<button name="next" id="right-btn" data-dir="1" class="dark-btn-bg">❱</button>
			<button name="add" id="add-btn">add</button>
			<template id="slide-tpl">
				<fieldset>
					<label><input type="radio" hidden checked name="position" value="top"><span>&#xe86b;</span></label>
					<label><input type="radio" hidden name="position" value="bottom"><span>&#xe3c8;</span></label>
					<label><input type="radio" hidden checked name="color" value="light"><span>&#xf069;</span></label>
					<label><input type="radio" hidden name="color" value="dark"><span>&#xe94e;</span></label>
					<br><textarea placeholder="Description" name="description"></textarea>
					<br><input placeholder="Alternative Text" name="alt">
				</fieldset>
			</template>
			<script>
			(function(form){
				var animate;
				form.onsubmit=function(event){
					event.preventDefault();
				}
				form.next.onclick=
				form.previous.onclick=function(event){
					event.preventDefault();
					let dir = parseInt(event.target.dataset.dir),
					offset = SLIDER.offsetWidth*(dir+(SLIDER.scrollLeft/SLIDER.offsetWidth)>>0);

					if((offset<0) || offset>(SLIDER.scrollWidth-SLIDER.offsetWidth)) return false;
					SLIDER.shotSlide(offset);
				}
				SLIDER.shotSlide = function(offset){
					cancelAnimationFrame(animate);
					animate = requestAnimationFrame(function scrollSlide(){
						if(Math.abs(offset - SLIDER.scrollLeft) > 16){
							SLIDER.scrollLeft += (offset - SLIDER.scrollLeft)/8;
							animate = requestAnimationFrame(scrollSlide);
						}else SLIDER.scrollLeft = offset;
					});
				}
				form.add.onclick=function(event){
					event.preventDefault();
					window.parent.openBox('{}', "mediaset/navigatorbox",function(box){
						box.querySelector(".box-body>iframe").contentWindow.getSelected().forEach(function(img){

							let frm = doc.create("form",{}, form.querySelector("#slide-tpl").cloneNode(true).content);
								switch(img.type){
									case "image":
										var slide = doc.create("img",{src:img.url,alt:""});
										frm.appendChild(slide.cloneNode(true));
									break;
									case "video":
										frm.appendChild( doc.create("video",{src:img.url,controls:"true"}) );
										var slide = doc.create("video",{src:img.url});
									break;
									default:break;
								}
							SLIDER.appendChild(frm);
							let snippet = doc.create("div", {class:"snippet"}, "<label class='drop-snippet'>✕</label>");
							let preview = doc.create("div", {class:"preview"});
								preview.appendChild( slide );
								snippet.appendChild( preview );
								snippet.appendChild( doc.create("div", {class:"header",contenteditable:"true"}, img.url.split('/').pop().split('.').shift()) );
							MEDIASET.appendChild(snippet);

						});
						MEDIASET.refresSlideshow();
						box.drop();
					});
				}
			})(document.currentScript.parentNode)
			</script>
		</form>
		<?elseif(CATEGORY):?>
		<section class="grid">
			<?foreach($category['Name'] as $i=>$set): $itm=JSON::parse($category['Mediaset'][$i]);?>
			<a class="snippet" href="/mediaset/set/<?=ARG_2?>/<?=$category['SetID'][$i]?>">
				<div class="preview">
					<?if($itm[0]['type']=="img"):?>
					<img src="<?=$itm[0]['url']?>" alt="&#xe94a">
					<?elseif($itm[0]['type']=="video"):?>
					<video src="<?=$itm[0]['url']?>"></video>
					<?endif?>
				</div>
				<div class="language logo-bg"><?=$category['language'][$i]?></div>
				<div class="header"><?=$set?></div>
			</a>
			<?endforeach?>
			<script>var SETID = false</script>
		</section>
		<?else:?>
		<section class="grid">
			<?foreach($categories as $category):?>
			<a class="snippet" href="/mediaset/set/<?=$category?>">
				<img src="" alt="&#xe94b">
				<div class="header"><?=$category?></div>
			</a>
			<?endforeach?>
			<script>var SETID = false</script>
		</section>
		<?endif?>
		<script>
		var remove = function(){
			<?if(defined("ARG_3")):?>
			window.parent.confirmBox("Delete mediaset?", function(){
				XHR.push({
					addressee:"/actions/mediaset/rm_mediaset/<?=ARG_3?>",
					onsuccess:function(response){
						if(isNaN(response)){
							alert(response);
						}else location.pathname = location.pathname.split(/\//).slice(0,-1).join("/")
					}
				});
			});
			<?else:?>
			window.parent.alertBox("Mediaset is not selected",["logo-bg","h-bar"]);
			<?endif?>
		}
		var save = function(showAlert){
			<?if(SETID):?>
			let mediaset = [];
			var keys = MEDIASET.querySelectorAll(".snippet>div.header");
			SLIDER.querySelectorAll("form").forEach(function(form,i){
				let obj = form.querySelector("img,video");
				mediaset.push({
					url:obj.src,
					type:obj.nodeName.toLowerCase(),
					alt:form.alt.value.trim().replace(/"/g,"”").replace("/'/g","’"),
					description:form.description.value.trim().replace(/"/g,"”").replace("/'/g","’"),
					color:form.color.value,
					position:form.position.value,
					key:keys[i].textContent.trim().replace(/"/g,"”").replace("/'/g","’"),
				});
			});
			XHR.push({
				addressee:"/actions/mediaset/sv_mediaset/<?=SETID?>",
				body:JSON.encode(mediaset),
				onsuccess:function(response){
					if(isNaN(response)) alert(response);
				}
			});
			<?else:?>
			if(showAlert) window.parent.alertBox("Mediaset is not selected",["active-bg","h-bar"]);
			<?endif?>
		}
		var createSet = function(){
			window.parent.openBox(null,"mediaset/createbox", function(form){
				XHR.push({
					addressee:"/actions/mediaset/ad_mediaset",
					body:JSON.encode({
						Name:form.named.value.trim(),
						Category:form.category.value.trim(),
						language:form.language.value,
						Mediaset:{}
					}),
					onsuccess:function(response){
						if(parseInt(response)){
							location.href="/mediaset/set/"+form.category.value.trim()+"/"+response;
							form.reset();
						}else alert(response);
					}
				});
			})
		}
		</script>
	</body>
</html>