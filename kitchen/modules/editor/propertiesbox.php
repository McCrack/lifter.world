<?php
switch(ARG_2){
	case "table":	$list=["align","background","bgcolor","border","cellpadding","cellspacing","cols","frame","height","rules","summary","width"]; break;
	case "td":
	case "th":		$list=["abbr","align","valign","colspan","rowspan","width","height","nowrap","headers","axis","background","bgcolor","bordercolor","char","charoff","scope"]; break;
	case "thead":
	case "tbody":
	case "tfoot":
	case "tr":		$list=["align","char","charoff","bgcolor","valign"]; break;
	case "a":		$list=["accesskey","coords","download","href","hreflang","name","rel","rev","shape","tabindex","target","title","type"]; break;
	case "ol":		$list=["type","reversed","start"]; break;
	case "ul":		$list=["type"]; break;
	case "li":		$list=["type","value"]; break;
	case "div":
	case "p":
	case "h1":
	case "h2":
	case "h3":
	case "h4":		$list=["align"]; break;
	case "canvas":	$list=["height","width"]; break;
	case "caption": $list=["align","valign"]; break;
	case "q":		$list=["cite"]; break;
	case "img":		$list=["align","alt","border","height","hspace","ismap","longdesc","lowsrc","src","vspace","width","usemap"]; break;
	case "audio":	$list=["autoplay","controls","loop","preload","src"]; break;
	case "video":	$list=["autoplay","controls","height","loop","poster","preload","src","width"]; break;
	case "form":	$list=["accept-charset","action","autocomplete","enctype","method","name","novalidate","target"]; break;
	case "select":	$list=["accesskey","autofocus","disabled","form","multiple","name","required","size","tabindex"]; break;
	case "textarea":$list=["accesskey","autofocus","cols","disabled","form","maxlength","name","placeholder","readonly","required","rows","tabindex","wrap"]; break;
	case "input":	$list=["accept","accesskey","align","alt","autocomplete","autofocus","border","checked","disabled","form","formaction","formenctype","formmethod","formnovalidate","formtarget","list","max","maxlength","min","multiple","name","pattern","placeholder","readonly","required","size","src","step","tabindex","type","value"]; break;
	case "button":	$list=["accesskey","autofocus","disabled","form","formaction","formenctype","formmethod","formnovalidate","formtarget","name","type","value"]; break;
	case "hr":		$list=["align","color","noshade","size","width"]; break;
	case "iframe":	$list=["align","allowtransparency","frameborder","height","hspace","marginheight","marginwidth","name","sandbox","scrolling","seamless","src","srcdoc","vspace","width"]; break;
	case "object":	$list=["align","archive","classid","code","codebase","codetype","data","height","hspace","tabindex","type","vspace","width"]; break;
	default:break;
}
$handle = "b".time()?>
<div id="<?=$handle?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.properties-box>div.box-caption{
		font-size:16px;
		line-height:36px;
	}
	</style>
	<form class="box properties-box light-btn-bg" style="width:540px">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption white-bg">&#xe992;<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg" data-translate="textContent">properties</div>
		<div class="box-body">
			<table width="100%" cellpadding="4" cellspacing="0" rules="cols" bordercolor="white" style="border:1px solid #999">
				<thead>
					<tr class="dark-btn-bg" align="center">
						<td width="200px" data-translate="textContent">attribute</td>
						<td data-translate="textContent">value</td>
					</tr>
				</thead>
				<tbody>
					<tr><td align="center">id</td><td contenteditable="true"></td></tr>
					<tr><td align="center">class</td><td contenteditable="true"></td></tr>
					<tr><td align="center">style</td><td contenteditable="true"></td></tr>
					<tr><td align="center">contenteditable</td><td contenteditable="true"></td></tr>
					<?foreach($list as $key):?><tr><td align="center"><?=$key?></td><td contenteditable="true"></td></tr><?endforeach?>
				</tbody>
			</table>
		</div>
		<div class="box-footer" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent">apply</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){ form.drop() }
		})(document.currentScript.parentNode);
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>