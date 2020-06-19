<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Goolybeep</title>
		<link rel="stylesheet" media="all" type="text/css" href="/themes/index.css">
		<link rel="stylesheet" media="all" type="text/css" href="/themes/<?=$config->themes?>/index.css">
		<script src="/js/md5.js"></script>
		<script src="/js/gbAPI.js"></script>
		<style>
		#login{
			z-index:2;
			position:relative;
		}
		#cover{
			height:100%;
			background-size:cover;
			background-image:url(/themes/default/images/cover.jpg);
		}
		#cover>div{
			text-align:center;
			font:bold 32vw/90vh impact,calibri;

			width:100%;
			height:100%;
			background-size:cover;
			background-image:url(/themes/default/images/cover.jpg);

			background-clip:text;
  			-webkit-background-clip:text;
  			color:transparent;
		}
		</style>
	</head>
	<body>
		<form id="login">
			<input name="login" placeholder="Login" required autofocus>
			<input name="passwd"  placeholder="●●●●●" type="password" required>
			<button type="submit" class="active-bg">Войти в систему</button>
			<script>
			(function (form){
				form.onsubmit=function(event){
					event.preventDefault();
					var passwd = md5(form.passwd.value);
					session.open();
					session.setItem("login", form.login.value);
					session.setItem("passwd", passwd);
					COOKIE.set("finger", md5(form.login.value+passwd + COOKIE.get("key")), {"path":"/"});
				
					location.reload();
				}
			})(document.currentScript.parentNode);
			</script>
		</form>
		<div id="cover">
			<div>
			LIFTER
			<script>
			(function(cover){
				var X = Y = 0;
				cover.onmouseover=function(event){
					X = event.clientX;
					Y = event.clientY;
				}
				cover.onmousemove=function(event){
					cover.style.backgroundPosition = (event.clientX - X)*0.1+"px "+(event.clientY - Y)*0.1+"px";
				}
			})(document.currentScript.parentNode)
			</script>
			</div>
		</div>
	</body>
</html>