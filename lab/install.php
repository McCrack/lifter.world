<?php

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);

define("ROOT", empty($params[0]) ? "step-1" : $params[0]);

?>
<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Goolybeep</title>
		<style>
		.left{
			float:left;
		}
		.right{
			float:right;
		}
		.active-txt{
			color:#00ADF0;
		}
		.white-txt{
			color:white;
		}
		body{
			margin:0;
			background-color:#123;
		}
		form{
			padding:4vw;
			margin:0 auto;
			max-width:800px;
			background-color:#FAFAFA;
		}
		h1{
			margin-left:4vw;
		}
		h2,p{
			margin:0;
		}
		fieldset{
			max-width:50%;
			border-width:0;
			box-sizing:border-box;
		}
		select,
		input[type='text']{
			height:30px;
			margin:4px;
			padding:6px;
			min-width:100%;
			box-sizing:border-box;
			border:1px solid #AAA;
			box-shadow:inset 0 0 5px -2px rgba(0,0,0, .4);
			background-image:linear-gradient(to top, #FFF, #EEE);
		}
		button{
			color:white;
			height:30px;
			min-width:80px;
			border-width:0;
			font-size:14px;
			background-color:#00ADF0; 
		}
		hr{
			margin:0;
			height:1px;
			border-width:0;
			background-color:#AAA;
		}
		</style>
	</head>
	<body>
		<?if(ROOT=="step-1"):

		//@mkdir("core");
		//copy("zip://core.zip#kitchen/core/index.php", "core/index.php");
		//copy("zip://core.zip#kitchen/core/db.php", "core/db.php");

		?>
		<h1 class="white-txt">STEP 1</h1>
		<hr>
		<form action="/install/step-2" method="post"> 
			<h2>[<span class="active-txt">Kitchen configure</span>]</h2>
			<fieldset class="left"><legend>Base Subdomain:</legend>
				<select name="base-folder">
				<?foreach(glob("../*", GLOB_ONLYDIR) as $path): $dir=basename($path)?>
					<option value="<?=$dir?>"><?=$dir?></option>
				<?endforeach?>
				</select>
			</fieldset>
			<fieldset class="right"><legend>Language:</legend>
				<select name="kitchen-language">
					<option value="en">ENG</option>
					<option value="uk">UKR</option>
					<option value="ru">RUS</option>
				</select>
			</fieldset>
			<fieldset><legend>Config File:</legend>
				<input name="config-file" type="text" placeholder="..." value="config.init" required>
			</fieldset>

			<h2>[<span class="active-txt">Site configuration</span>]</h2>

			<fieldset class="left">
				DB HOST
				<input name="host" type="text" placeholder="..." required>
				DB NAME
				<input name="db_name" type="text" placeholder="..." required>
				DB USER:
				<input name="db_user" type="text" placeholder="..." required>
				DB PASSWORD:
				<input name="db_password" type="text" placeholder="..." required>
			</fieldset>
			<fieldset>
				SITE NAME
				<input name="site-name" type="text" placeholder="..." required>
				HOME PAGE
				<input name="home-page" value="home" type="text" placeholder="..." required>
				LANGUAGE
				<input name="language" type="text" placeholder="..." required>
				LANGUAGE SET
				<input name="languages" value="ru, uk" type="text" placeholder="..." required>
			</fieldset>
			<p align="right">
				<button type="submit">next ❭</button>
			</p>
			<script>
			(function (form){
				
			})(document.currentScript.parentNode);
			</script>
		</form>
		<?elseif(ROOT=="step-2"):
		
		$host = explode(".", $_SERVER['HTTP_HOST']);
		define("SUBDOMAIN", reset($host));
		define("HOST", implode(".", array_slice($host, 1)));
		if(isset($_SERVER['HTTPS'])){
			if(("on" == strtolower($_SERVER['HTTPS'])) || ("1" == $_SERVER['HTTPS'])){
				define("PROTOCOL", "https");
			}else define("PROTOCOL", "http");
		}elseif(isset($_SERVER['SERVER_PORT']) && ("443" == $_SERVER['SERVER_PORT'])){
			define("PROTOCOL", "https");
		}else define("PROTOCOL", "http");


		include_once("core/index.php");

		$kitchen = [
			"general"=>[
				"base folder"=>[
					"type"=>"enum",
					"value"=>$_POST['base-folder'],
					"valid"=>[]
				],
				"config file"=>[
					"type"=>"string",
					"value"=>$_POST['config-file'],
					"valid"=>[]
				],
				"language"=>[
					"type"=>"enum",
					"value"=>$_POST['kitchen-language'],
					"valid"=>["en","uk","ru"]
				],
				"default module"=>[
					"type"=>"string",
					"value"=>"sitemap",
					"valid"=>[]
				],
				"themes"=>[
					"type"=>"enum",
					"value"=>"default",
					"valid"=>["default"]
				]
			],
			"domains"=>[]
		];

		foreach(glob("../*", GLOB_ONLYDIR) as $path){
			$dir=basename($path);
			
			$kitchen['general']['base folder']['valid'][] = $dir;
			$kitchen['domains'][$dir] = [
				"type"=>"string",
				"value"=>(PROTOCOL."://".$dir.".".HOST),
				"valid"=>[]
			];
		}

		define("DB_HOST", $_POST['host']);
		define("DB_NAME", $_POST['db_name']);
		define("DB_USER", $_POST['db_user']);
		define("DB_PASS", $_POST['db_password']);

		$site = [
			"general"=>[
				"site name"=>[
					"type"=>"string",
					"value"=>$_POST['site-name'],
					"valid"=>[]
				],
				"home page"=>[
					"type"=>"string",
					"value"=>$_POST['home-page'],
					"valid"=>[]
				],
				"email"=>[
					"type"=>"string",
					"value"=>"",
					"valid"=>[]
				],
				"language"=>[
					"type"=>"enum",
					"value"=>$_POST['language'],
					"valid"=>[]
				],
				"themes"=>[
					"type"=>"enum",
					"value"=>"",
					"valid"=>[]
				],
				"themes"=>[
					"type"=>"enum",
					"value"=>"",
					"valid"=>[]
				]

			],
			"localization"=>[
				"ru"=>[
					"type"=>"enum",
					"value"=>"RU",
					"valid"=>["RU"]
				],
				"uk"=>[
					"type"=>"enum",
					"value"=>"UA",
					"valid"=>["UA"]
				],
				"en"=>[
					"type"=>"enum",
					"value"=>"US",
					"valid"=>["GB","US"]
				]
			],
			"DataBase"=>[
				"db host"=>[
					"type"=>"string",
					"value"=>$_POST['host'],
					"valid"=>[]
				],
				"db name"=>[
					"type"=>"string",
					"value"=>$_POST['db_name'],
					"valid"=>[]
				],
				"db user"=>[
					"type"=>"string",
					"value"=>$_POST['db_user'],
					"valid"=>[]
				],
				"db password"=>[
					"type"=>"string",
					"value"=>$_POST['db_password'],
					"valid"=>[]
				]
			]
		];
		foreach( explode(",", $_POST['languages']) as $lang){
			$site['general']['language']['valid'][] = trim($lang);
		}

		//JSON::save("conf.init", $kitchen);
		//JSON::save("../".$_POST['base-folder']."/".$_POST['config-file'], $site);

		/*
		@mkdir("ajax");
		@mkdir("components");
		@mkdir("data");
		@mkdir("images");
		@mkdir("js");
		@mkdir("localization");
		@mkdir("modules");
		@mkdir("patterns");
		@mkdir("themes");
		*/

		$zip = new ZipArchive;
		if($zip->open("goolybeep.zip")){
			$zip->extractTo("../".$_POST['base-folder'], "www");
			$zip->extractTo("../".SUBDOMAIN, "kitchen");
			$zip->close();
		}

		//include_once("core/db.php");

		?>
		<h1 class="white-txt">STEP 2</h1>
		<hr>
		<form action="/install/step-3" method="post"> 
		<?foreach(glob("zip://core.zip") as $path):?>
		<div><?=$path?></div>
		<?endforeach?>
		</form>
		<?endif?>
	</body>
</html>