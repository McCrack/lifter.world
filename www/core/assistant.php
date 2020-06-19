<?php

class Assistant{
	private $config, $wordlist;
	public function __construct(&$p=[]){
		$this->config = &$GLOBALS['config'];
		$this->wordlist = new Wordlist([], $config->subdomain);
		if(isset($p['action'])){
			$this->{$p['action']}($p);
		}
	}
	public function __call($name, $arguments){
		return false;
	}
	public function callme(&$p){
		$message = "Name: ".$p['name']."\n";
		$message .= "Phone: ".$p['message'];
		$sent = $this->EmailNotification($this->config->{"admin email"}, $this->wordlist->{"order call"}." ".$_SERVER['HTTP_HOST'], $message);
		print $sent ? $this->wordlist->{"request was sent"} : $this->wordlist->{"failed to send request"};
	}
	public function feedback(&$p){
		$message = "Name: ".$p['name']."\n";
		$message .= "Email: ".$p['email']."\n";
		$message .= "Message: \n".$p['message'];
		$sent = $this->EmailNotification($p['email'], $this->wordlist->{"message from site"}." ".$_SERVER['HTTP_HOST'], $message);
		print $sent ? "message was sent" : "failed to send message";
	}
	public function TelegramNotification($message){
		$token = $this->config->{"telegram token"};
		$BotID = $this->config->{"telegram bot id"};
		
		if(empty($token) || empty($BotID)) return false;

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL				=> "https://api.telegram.org/".$token."/sendMessage?disable_web_page_preview=true&chat_id=".$BotID,
			CURLOPT_POST			=> TRUE,
			CURLOPT_RETURNTRANSFER	=> TRUE,
			CURLOPT_FOLLOWLOCATION	=> FALSE,
			CURLOPT_HEADER			=> FALSE,
			CURLOPT_TIMEOUT			=> 10,
			CURLOPT_HTTPHEADER		=> array("Accept-Language: ru,uk,en-us"),
			CURLOPT_POSTFIELDS		=> $message,
		));
		curl_exec($ch);
		curl_close($ch);
	}
	public function EmailNotification($sender, $theme, $message){
		$sent = $this->SendByEmail($message, $theme, "plain", $this->config->{"admin email"}, $sender);
		return $sent;
	}
	public function SendByEmail($message, $theme, $mode="plain", $recipient, $sender){
		$headers="MIME-Version: 1.0\r\n";
		$headers.="Content-type: text/".$mode."; charset=utf8\r\n";
		$headers.="From: ".$sender."\r\n";
		if($mode==="plain"){
			$message=wordwrap($message, 70);
		}
		$sent = mail($recipient, "=?utf-8?B?".base64_encode($theme)."?=", $message, $headers);
		return $sent;
	}
}
	
?>