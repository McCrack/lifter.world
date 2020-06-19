<?php

require_once("core/db.php");

if((INT)ARG_3){
	$mySQL->inquiry("UPDATE gb_pages SET rating=rating+({int}) WHERE PageID={int} LIMIT 1", (ARG_2 - ARG_3),ARG_1);
}else $mySQL->inquiry("UPDATE gb_pages SET votes=votes+1, rating=rating+{int} WHERE PageID={int} LIMIT 1", ARG_2,ARG_1);

?>