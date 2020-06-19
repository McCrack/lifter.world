<div class="h-bar white-txt" data-translate="textContent">modules</div>
<div class="root">
<?foreach(scandir("modules") as $module) if(file_exists("modules/".$module."/config.init")):

	$module_cng = JSON::load("modules/".$module."/config.init");
	$groups = preg_split("/,\s*/", $module_cng['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	if(($conf['free access']['value']=="YES") || in_array(USER_GROUP, $groups))
		
	if($module_cng['status']['value']=="enabled") if($module_cng['mode']['value']=="box"):?>
	<a href="javascript:openBox(null,'<?=$module?>/box')" data-translate="textContent" <?if($module==SECTION):?>class="selected"<?endif?>><?=$module?></a>
	<?else:?>
	<a href="/<?=$module?>" data-translate="textContent" <?if($module==SECTION):?>class="active-txt"<?endif?>><?=$module?></a>
	<?endif?>
<?endif?>
</div>