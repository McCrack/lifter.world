<?php

$log = USER_NAME." [".date("d M, H:i:s")."]";

switch(ARG_2){
	case "prowler":
		$url = file_get_contents('php://input');
		$data = [
			"og:title"=>"n/a",
			"og:image"=>""
		];
		$html = file_get_contents($url);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$meta = $dom->getElementsByTagName("meta");
		foreach($meta as $tag){
			$property = $tag->getAttribute("property");
			if($property=="og:title" || $property=="og:image"){
				$data[$property]  = $tag->getAttribute("content");
			}
		}
		if(defined("ARG_3")) $mySQL->inquiry("UPDATE gb_task_shunter SET header={str}, image={str} WHERE TaskID={int} LIMIT 1", $data['og:title'],$data['og:image'],ARG_3);

		print JSON::encode($data);
	break;
	case "create":
		$p = JSON::load('php://input');

		$p['task'] = urldecode($p['task']);
		$p['header'] = urldecode($p['header']);

		$log .= " -> Creared;";

		$TaskID = $mySQL->inquiry("INSERT INTO gb_task_timing SET CommunityID=".$p['performer'].",log={str},created={int}", $log,time())['last_id'];

		if($TaskID) $mySQL->inquiry("INSERT INTO gb_task_shunter SET {prp}", $mySQL->parse("{set}",[
			"TaskID"=>$TaskID,
			"rank"=>$p['rank'],
			"type"=>$p['type'],
			"link"=>$p['link'],
			"image"=>$p['image'],
			"header"=>$p['header'],
			"task"=>$p['task'],
			"optionset"=>JSON::encode($p['optionset'])
		]));
		print $TaskID;
	break;
	case "change":
		$p = JSON::load('php://input');
		$field = key($p);
		$value = base64_decode($p[$field]);
		print $mySQL->inquiry("UPDATE gb_task_shunter SET {set} WHERE TaskID={int} LIMIT 1", [$field=>$value],ARG_3)['affected_rows'];

		$log .= " -> Change ".$field.";\n";
		$mySQL->inquiry("UPDATE gb_task_timing SET log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log, ARG_3);
	break;
	case "status":
		$p = JSON::load('php://input');
		$changed =  $mySQL->inquiry("UPDATE gb_task_shunter SET status={str} WHERE TaskID={int} LIMIT 1", $p['status'],ARG_3)['affected_rows'];
		
		$log .= " -> Change Status to ".$p['status'].";\n";

		if($changed>0){
			if($p['status']=="new"){
				print $mySQL->inquiry("UPDATE gb_task_timing SET towork=0,towaste=0, log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log,ARG_3);
			}elseif($p['status']=="in work"){
				print $mySQL->inquiry("UPDATE gb_task_timing SET towork={int}, CommunityID={int}, log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", time(),$p['performer'],$log,ARG_3);
			}else print $mySQL->inquiry("UPDATE gb_task_timing SET towaste={int}, CommunityID={int}, log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", time(),$p['performer'],$log,ARG_3);
		}

		foreach($p['list'] as $i=>$task){
			$mySQL->inquiry("UPDATE gb_task_shunter SET SortID={int} WHERE TaskID={int} LIMIT 1", $i,$task);
		}
	break;
	case "change-status":
		$p = JSON::load('php://input');
		
		$log .= " -> Change Status to ".$p['status'].";\n";

		if($p['status']=="in work"){
			$mySQL->inquiry("UPDATE gb_task_timing SET towork={int},CommunityID={int},log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", time(),$p['performer'],$log,ARG_3);
		}elseif(($p['status']=="done") || ($p['status']=="waste")){
			$mySQL->inquiry("UPDATE gb_task_timing SET towaste={int},CommunityID={int},log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", time(),$p['performer'],$log,ARG_3);
		}else $mySQL->inquiry("UPDATE gb_task_timing SET towork=0,towaste=0,CommunityID={int},log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $p['performer'],$log,ARG_3);
		
		print $mySQL->inquiry("UPDATE gb_task_shunter SET status={str} WHERE TaskID={int} LIMIT 1", $p['status'],ARG_3)['affected_rows'];
	break;
	case "change-type":
		$p = JSON::load('php://input');
		print $mySQL->inquiry("UPDATE gb_task_shunter SET type={str} WHERE TaskID={int} LIMIT 1", $p['type'],ARG_3)['affected_rows'];

		$log .= " -> Change Type to ".$p['type'].";\n";
		$mySQL->inquiry("UPDATE gb_task_timing SET log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log, ARG_3);
	break;
	case "change-performer":
		$p = JSON::load('php://input');

		$log .= " -> Change Performer to ".ARG_4.";\n";
		print $mySQL->inquiry("UPDATE gb_task_timing SET CommunityID=".ARG_4.",towork=0,towaste=0,log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log,ARG_3)['affected_rows'];
		$mySQL->inquiry("UPDATE gb_task_shunter SET SortID=0,status='new' WHERE TaskID={int} LIMIT 1", ARG_3);
	break;
	case "back-tostream":
		
		$log .= " -> Back to Stream;\n";

		$mySQL->inquiry("UPDATE gb_task_shunter SET status='new' WHERE TaskID={int} LIMIT 1", ARG_3);
		print $mySQL->inquiry("UPDATE gb_task_timing SET CommunityID=NULL,towork=0,towaste=0,log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log,ARG_3)['affected_rows'];
	break;
	case "change-optionset":
		print $mySQL->inquiry("UPDATE gb_task_shunter SET optionset={str} WHERE TaskID={int} LIMIT 1", file_get_contents('php://input'),ARG_3)['affected_rows'];

		$log .= " -> Change Optionset;\n";
		$mySQL->inquiry("UPDATE gb_task_timing SET log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log, ARG_3);
	break;
	case "clear":
		$p = JSON::load('php://input');
		print $mySQL->inquiry("UPDATE gb_task_shunter SET status='deleted' WHERE status IN ({arr})", $p)['affected_rows'];
	break;
	case "remove";
		print $mySQL->inquiry("UPDATE gb_task_shunter SET status='deleted' WHERE TaskID = {int}", ARG_3)['affected_rows'];

		$log .= " -> Remove Card;\n";
		$mySQL->inquiry("UPDATE gb_task_timing SET log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log, ARG_3);

		/*
		$towaste = $mySQL->getRow("SELECT towaste FROM gb_task_timing WHERE TaskID = {int} LIMIT 1", ARG_3)['towaste'];
		if($towaste>0){
			print $mySQL->inquiry("DELETE FROM gb_task_shunter WHERE TaskID = {int} LIMIT 1", ARG_3)['affected_rows'];
		}else print $mySQL->inquiry("DELETE FROM gb_task_timing WHERE TaskID = {int} LIMIT 1", ARG_3)['affected_rows'];
		*/
	break;
	case "user-settings":
		$mySQL->settings['shunter'] = JSON::load('php://input');
		$mySQL->inquiry("UPDATE gb_staff SET settings={str} WHERE UserID={int} LIMIT 1", JSON::encode($mySQL->settings), USER_ID);
	break;
	case "questionnaire":
		print file_put_contents("patterns/json/questionnaire.json", file_get_contents('php://input'));
	break;
	case "save-all":
		$p = JSON::load('php://input');

		print $mySQL->inquiry("UPDATE gb_task_shunter SET {set} WHERE TaskID={int} LIMIT 1", [
			"rank"=>$p['rank'],
			"status"=>$p['status'],
			"type"=>$p['type'],
			"image"=>$p['image'],
			"link"=>$p['link'],
			"header"=>base64_decode($p['header']),
			"task"=>base64_decode($p['task']),
			"optionset"=>JSON::encode($p['properties'])
		],ARG_3)['affected_rows'];

		$log .= " -> Save All;\n";
		$mySQL->inquiry("UPDATE gb_task_timing SET log=CONCAT(log,{str}) WHERE TaskID={int} LIMIT 1", $log, ARG_3);
	break;
	default:break;
}
?>