<?php
switch(COMMAND){
	case "build_map":
		$tree = [];
		BuildMap("..", $tree);

		$tree = JSON::encode($tree);

		header('Pragma: public');
		header("Content-Length: ".mb_strlen($tree)); 
		header("Content-Type: application/json");
		header("Content-Disposition: attachment; filename=".$_SERVER['HTTP_HOST']."-".date("d_M_Y_H_i").".json");
		header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');

		print($tree);
	break;
	case "match_hashes":
	$tree = JSON::load('php://input')?>
	<table width="100%" cellpadding="5" cellspacing="0">
		<thead>
			<tr class="active-bg">
				<th>Path</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?traversal("..", $tree)?>
		</tbody>
	</table>
	<?break;
	default:break;
}

/*~~~~~~~~~~~~~~~*/

function BuildMap($root="..", &$tree=[]){
	foreach (glob($root."/*", GLOB_ONLYDIR) as $dir) BuildMap($dir, $tree[basename($root)]);
	$folder = basename($root);
	foreach( array_filter(glob($root."/*.{php,html,htm}", GLOB_BRACE), 'is_file') as $path){
		$tree[$folder][basename($path)] = crc32(file_get_contents($path));
	}
}

/*~~~~~~~~~~~~~~~*/

function traversal($root="..", &$tree=[]){
	foreach (glob($root."/*", GLOB_ONLYDIR) as $dir){
		traversal($dir, $tree[basename($root)]);
	}
	$folder = basename($root);
	foreach( array_filter(glob($root."/*.{php,html,htm}", GLOB_BRACE), 'is_file') as $path):
		$hash = crc32(file_get_contents($path));
		$file = basename($path);
		?>
		<tr>
			<td><?=$path?></td>
			<?if(isset($tree[$folder][$file])):?>
				<?if($hash!=$tree[$folder][$file]):?>
				<th class="red-txt">CHANGED</th>
				<?else:?>
				<td class="light-txt" align="center">Not Changed</td>
				<?endif?>
			<?else:?>
			<th align="center" class="active-txt">NEW FILE</th>
			<?endif?>
		</tr>
	<?endforeach;
}

?>