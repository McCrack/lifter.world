<?php

$time = time();
$log = "<small class='gold-txt'>".USER_NAME." [".date("d M, H:i:s")."]</small>";

switch(ARG_2){
	case "add-comment":
		$log .= "<div class='green-txt'>".file_get_contents('php://input')."</div><hr>";
		$mySQL->inquiry("UPDATE gb_orders SET log=CONCAT({str},log) WHERE OrderID={int} LIMIT 1", $log, ARG_3);

		print showLog();
	break;
	case "change-paid":
		$log .= "<div>".((ARG_4=="yes") ? "<span class='green-txt'>Set</span>" : "<span class='red-txt'>Drop</span>")." a payment marker</div><hr>";
		$mySQL->inquiry("UPDATE gb_orders SET paid={str}, modified={int}, log=CONCAT({str},log) WHERE OrderID={int} LIMIT 1", ARG_4, $time, $log, ARG_3);
		print showLog();
	break;
	case "change-amount":
		$amount = $mySQL->getRow("SELECT amount FROM gb_orders WHERE OrderID={int} LIMIT 1", ARG_3)['amount'];
		$log .= "<div>Change amount: <span class='red-txt'>".$amount."</span> to <span class='active-txt'>".ARG_4."</span></div><hr>";
		
		$cng = new config("../".$config->{"base folder"}."/".$config->{"config file"});
		$price = ($cng->{"base price"} + ($cng->{"price factor"} * 2)) + ((ARG_4 - 1) * $cng->{"base price"}) + ((INT)(ARG_4>1) * $cng->{"price factor"});

		$mySQL->inquiry("UPDATE gb_orders SET amount={str}, price={str}, modified={int}, log=CONCAT({str},log) WHERE OrderID={int} LIMIT 1", ARG_4, $price, $time, $log, ARG_3);
		print showLog();
	break;
	case "change-status":
		$order = $mySQL->getRow("SELECT status,UserID FROM gb_orders WHERE OrderID={int} LIMIT 1", ARG_3);
		$userID = ($order['status']=="new") ? USER_ID : $order['UserID'];
		
		$log .= "<div>Change status: <span class='red-txt'>".$order['status']."</span> to <span class='active-txt'>".ARG_4."</span></div><hr>";
		$mySQL->inquiry("UPDATE gb_orders SET UserID={int}, status={str}, modified={int}, log=CONCAT({str},log) WHERE OrderID={int} LIMIT 1", $userID, ARG_4, $time, $log, ARG_3);

		print showLog();
	break;
	case "change-delivery-data":
		$log .= "<div>Change <span class='active-txt'>delivery</span> options</div><hr>";
		$data = file_get_contents('php://input');
		$mySQL->inquiry("UPDATE gb_orders SET delivery={str}, modified={int}, log=CONCAT({str},log) WHERE OrderID={int} LIMIT 1", $data,$time, $log,ARG_3);

		print showLog();
	break;
	case "create":
		$p = JSON::load('php://input');
		$citizen = $mySQL->getRow("SELECT CommunityID,reputation FROM gb_community WHERE CommunityID={int} LIMIT 1", $p['citizen']);
		
		if(empty($citizen)){
			$citizen = [
				"reputation"=>0,
				"CommunityID"=>$mySQL->inquiry("INSERT INTO gb_community (CitizenID,Name,Phone,Email) VALUES ({int},{str},{str},{str})",
					$mySQL->getRow("SELECT MAX(CitizenID) AS CitizenID FROM gb_community LIMIT 1")['CitizenID']+1,
					$p['name'],
					$p['phone'],
					$p['email']
				)['last_id']
			];
		}else $mySQL->inquiry("UPDATE gb_community SET reputation=reputation+1, Name={str},Email={str} WHERE CommunityID={int} LIMIT 1", $p['name'],$p['email'],$p['citizen']);
		
		$log .= "<div>Created order<br>Buyer reputation: <span class='gold-txt'>".$citizen['reputation']."</span><br>Pice: <span class='green-txt'>".$p['price']." uah</span></div><hr>";
		$OrderID = $mySQL->inquiry("
		INSERT INTO gb_orders (CommunityID,UserID,created,modified,status,price,delivery,log)
		VALUES ({int},{int},{int},{int},'accepted',{str},{str},{str})",
			$citizen['CommunityID'],
			USER_ID,
			$time,
			$time,
			$p['price'],
			JSON::encode($p['delivery']),
			$log
		)['last_id'];
		print $OrderID;
	break;
	default:break;
}


function showLog(){
	return $GLOBALS['mySQL']->getRow("SELECT log FROM gb_orders WHERE OrderID={int} LIMIT 1", ARG_3)['log'];
}
?>