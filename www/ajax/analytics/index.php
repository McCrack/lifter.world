<?php

require_once("core/db.php");

define("DAY", mktime(0,0,0));

$p = JSON::load('php://input');
	
$page = $mySQL->getRow("SELECT time FROM gb_pages WHERE PageID = {int} LIMIT 1", $p['PageID']);

$time = (($p['output'] - $p['input']) / 1000)>>0;
if($time>600) $time = 600;
	
$mySQL->inquiry("UPDATE gb_pages SET  views = views+1, time = time+{int} WHERE PageID = {int} LIMIT 1", $time,$p['PageID']);

if(ARG_1){
	$mySQL->inquiry("UPDATE `gb_user-analytics` SET reviews = reviews+1 WHERE day={int} LIMIT 1", DAY);
}else $mySQL->inquiry("INSERT INTO `gb_user-analytics` SET day={int} ON DUPLICATE KEY UPDATE views = views+1, reviews = reviews+1", DAY);

$mySQL->close();

?>