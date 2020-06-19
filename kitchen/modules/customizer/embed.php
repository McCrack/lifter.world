<!DOCTYPE html>
<html>
	<head>
	<?include_once("components/head.php")?>
	<script>

	</script>
	<style>
		body{
			margin:0;
			background-color:white;
		}
		form{
			padding:20px;
		}
		fieldset{
			border:1px solid #888;
		}
		select,
		input[type='text'],
		input[type='color']{
			height:28px;
			border-width:0;
			vertical-align:middle;
		}
		input[type='color']{
			padding:0;
			width:30px;
		}
		input[type='text']{
			padding:5px;
			margin:4px 2px;
			border-radius:3px;
			box-sizing:border-box;
			box-shadow:inset 0 0 5px 0 rgba(0,0,0, .5);
			background-image:linear-gradient(to top, #FFF, #EEE);
		}
		input[type='range']{
			-webkit-appearance:none;
			margin:0;
			height:2px;
			background-color:#AAA;
		}
		div.select>select{
			font-size:14px;
			text-align:center;
			vertical-align:baseline;
		}
		div.select::before{
			content:attr(title);
		}
		div.select>select>option{
			color:black;
		}
		label{
			color:#AAA;
			display:block;
			cursor:pointer;
			vertical-align:middle;
			text-transform:capitalize;
		}
		label>input[type='radio']+span::before,
		label>input[type='checkbox']+span::before{
			color:#777;
			margin-right:4px;
			font-family:tools;
			display:inline-block;
		}
		label>input[type='radio']+span::before{
			font-size:14px;
			content:"\ea56";
			vertical-align:top;
		}
		label>input[type='radio']:checked+span::before{
			color:#00ADF0;
			content:"\ea54";
		}
		label>input[type='checkbox']+span::before{
			height:22px;
			font-size:20px;
			content:"\e5d0";
			vertical-align:middle;
		}
		label>input[type='checkbox']:checked+span::before{
			color:#00F0AD;
			content:"\e5d1";
		}
		</style>
	</head>
	<body>
		<form autocomplete="off" class="light-txt" action="/actions/customizer/sv_customize_options/<?=ARG_2?>" method="post">
			<?include_once("modules/customizer/form.html")?>
			<script>
			var options = <?=($mySQL->getRow("SELECT customizer FROM gb_pages WHERE PageID = {int} LIMIT 1", ARG_2)['customizer'])?>;

			var form = document.currentScript.parentNode;
			var submit = function(){form.submit()}
			
			for(var key in options){
				if(form[key]){
					switch(form[key].type){
						case "checkbox": form[key].checked = true; break;
						case "radio": if(form[key].value==options[key]) form[key].checked = true; break;
						default: form[key].value=options[key];
					}
				}
			}
			</script>
		</form>
	</body>
</html>