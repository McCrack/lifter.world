<?php

switch(COMMAND){
	case "save":
		if(defined("ARG_2")){
			$settings = JSON::load('php://input');
			$saved = JSON::save("../".ARG_1."/modules/".ARG_2."/config.init", array_shift($settings));
		}else $saved = file_put_contents("../".ARG_1."/config.init", $GLOBALS['HTTP_RAW_POST_DATA']);
		print($saved);
	break;
	case "sv_module":
		print file_put_contents("modules/".ARG_1."/config.init", $GLOBALS['HTTP_RAW_POST_DATA']);
	break;
	case "showsubdomain":
		if(defined("ARG_2")):?>
		<tr class="v-bar-bg" height="36"><td class="section" align="center" colspan="5"><?=ARG_2?></td></tr>
		<?foreach(JSON::load("../".ARG_1."/modules/".ARG_2."/config.init") as $key=>$val):?>
		<tr data-type="<?=$val['type']?>">
			<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
			<td align="center" data-translate="textContent" data-key="<?=$key?> <?=(empty($key) ? "contenteditable='true'" : "")?>"><?=$key?></td>
			<td contenteditable="true"><?=$val['value']?></td>
			<td contenteditable="true">
			<?switch($key):
				case "status": print implode(", ", ["enabled","disabled"]); break;
				case "access": eval("show_".$GLOBALS['mySQL']->getRow("SHOW COLUMNS FROM gb_staff LIKE 'Group'")['Type'].";"); break;
				default: print implode(", ", $val['valid']); break;
			endswitch?>
			</td>
			<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
		</tr>
		<?endforeach;

		elseif(defined("ARG_1")):
			$subdomains=$modules=$themes = [];
			foreach(glob("../*", GLOB_ONLYDIR) as $folder) $subdomains[] = basename($folder);
			foreach(glob("../".ARG_1."/modules/*", GLOB_ONLYDIR) as $module) $modules[] = basename($module);
			foreach(glob("../".ARG_1."/themes/*", GLOB_ONLYDIR) as $dir) $themes[] = basename($dir);

			foreach(JSON::load("../".ARG_1."/config.init") as $name=>$section):?>
			<tr class="v-bar-bg" height="36"><td class="section" align="center" colspan="5"><?=$name?></td></tr>
			<?foreach($section as $key=>$val):?>
			<tr data-type="<?=$val['type']?>">
				<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
				<td align="center" data-translate="textContent" data-key="<?=$key?> <?=(empty($key) ? "contenteditable='true'" : "")?>"><?=$key?></td>
				<td contenteditable="true"><?=$val['value']?></td>
				<td contenteditable="true">
				<?switch($key):
					case "subdomain":
					case "mobile subdomain":
					case "desktop subdomain":
					case "base folder": print implode(", ",$subdomains); break;
					case "default module": print implode(", ", $modules); break;
					case "theme":
					case "mobile theme": print implode(", ", $themes); break;
					default: print implode(", ", $val['valid']); break;
				endswitch?>
				</td>
				<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
			</tr>
			<?endforeach; endforeach;
		endif;
	break;
	default:break;
}
?>
<?function show_enum(){ print implode(", ", func_get_args()); }?>