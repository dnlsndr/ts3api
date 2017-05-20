<?php


class Ts3{
	function runData($command){
		$command = str_replace(' ','%20',$command);
	 //Get contents from the api, this will be a variable in future
		$json = file_get_contents("http://127.0.0.1:8080/ts3bot/api/$command");
		$obj = json_decode($json, true);
		$result = $obj['data'];
		if(substr_count($result, 'Welcome to the TeamSpeak 3 ServerQuery interface,') > 0){
			$json = file_get_contents("http://127.0.0.1:8080/ts3bot/api/$command");
			$obj = json_decode($json, true);
			$result = $obj['data'];
		} 
	 
	 //pack all restults in to a array and return them
	 $final = $this->arrayify($result);
		if (array_key_exists("error",$final)){
			return false;
		} else {
			return $final;
		}
	}
	
	function arrayify($string){
	 //explode the string into subsections divided by a pipe
	 if(substr_count($string, '|') > 0){
		 $channel = explode('|', $string);
		 foreach ($channel as $channelKey=>$channelVal){

			//explode the string into substrings divided by a space
			 $spaced = explode(' ', $channelVal);
			 foreach ($spaced as $spacedKey=>$spacedVal){

				//explode those substrings into key => Value at the first occurence of a equal sign (=)
				 $detailed = preg_split("~=~",$spacedVal, 2);
				 $detailed[1] = $this->escapeHumanFriendly($detailed[1]);
				 $finalReturn[$channelKey][$detailed[0]] = $detailed[1];	
			 }
		 }
		 return $finalReturn;
	 } else {
		//do this whn there are no pipe symbold for dividing available
		 $spaced = explode(' ', $string);
			 foreach ($spaced as $spacedVal){
				 $detailed = preg_split("~=~",$spacedVal, 2);
				 if(isset($detailed[1])){
					 $valtemp = $detailed[1];
				 } else {
					 $valtemp = "";
				 }
				 $valtemp = $this->escapeHumanFriendly($valtemp);
				 $finalReturn[$detailed[0]] = $valtemp;	
			 }
		 return $finalReturn;
	 }	
	}
	
 
 //make all passed strings more human readable
	function escapeHumanFriendly($string){
		$string = str_replace('\s',' ',$string);
		$string = str_replace('\p','|',$string);
		return $string;
	}
 
 //make strings more browser conform
	function escapeBrowserFriendly($string){
		$string = str_replace(' ','%5Cs',$string);
		
		return $string;
	}
 
 //make passed string suirtable for passing into api url
	function escapeUrlFriendly($string){
		$string = str_replace(' ','%20',$string);
		return $string;
	}
 
 //list all client with corresponding information
	function clientList(){
		$clients = $this->runData('clientlist');
		$result = array();
		foreach($clients as $client){
			if(!isset($client['clid'])){} else {
			if($client['client_type'] == 0){
			$req = "clientinfo clid=".$client['clid'];
			$data = $this->runData($req);
			array_push($result, $data);
			}
			}
		}
		return $result;
	}
 	// make passed client id channel admin of channel id
	function changeChannelGroup($uniqueid, $cgid, $cid = ""){
		$uniqueid = rawurlencode($uniqueid);
		$u_id = $_SESSION['u_id'];
		$dbid = $this->runData('clientgetdbidfromuid cluid='.$uniqueid)['cldbid'];
		$query = "SELECT * FROM channel_index WHERE u_id = '$u_id' OR owner_ts_id = '$uniqueid'";
		$result = mysqlConnect($query, 'interface');
		$result = mysqli_fetch_array($result);
		if(empty($cid)){
			$cid = $result['channel_id'];
		}
		$this->runData("setclientchannelgroup cgid=$cgid cid=$cid cldbid=$dbid");
					echo "Channelrechte geändert";
	}

 	//send message to everyone
	function globalMessage($message){
		$msg = $message;
		$clients = $this->runData('clientlist');
		$message = "%5Cn".preg_replace("/[\n\r]/","%5Cn", 	$message);
		foreach($clients as $client){
			$clid = $client['clid'];
			$this->runData("sendtextmessage targetmode=1 target=$clid msg=$message");	
		}
		echo "Nachricht an alle gesendet: $msg";
	}
 	//set new password for certain channel id
	function setNewPw($cid, $passphrase){
		$this->runData("channeledit cid=$cid channel_password=$passphrase channel_flag_password=1");	
	}

 	
	
}


?>
