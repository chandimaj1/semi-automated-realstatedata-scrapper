<?php 
//require_once ("head.php");
  
$steam_api_key = STEAMKEY; 

function encrypt_string($steam_id){

global  $steam_api_key;
    // Create unique browser id 
    $browser_id = uniqid('sfp',true);
    $expiry = time()+Cookie_Duration;
	setcookie("sfp_browser", $browser_id, $expiry,'/');
    // Check if steam key exists
    $servers = DB::query("SELECT steam_id FROM " . MYSQL_PREFIX_PORTAL . "browser_sessions WHERE (steam_api_key='$steam_api_key' AND steam_id='$steam_id') ORDER BY id DESC LIMIT 1");

echo("database si:".$servers[0]["steam_id"]."<br>");
echo("passed si:".$steam_id."<br>");
    //--Yes--Update browser id
 

	if($servers[0]["steam_id"]==$steam_id){
		DB::query("UPDATE ". MYSQL_PREFIX_PORTAL . "browser_sessions
				        SET browser_id='$browser_id', expiry=$expiry
                        WHERE (steam_api_key='$steam_api_key' AND steam_id='$steam_id') LIMIT 1");

    //--No--Create new browser id and register steam_id
	}else{
        DB::insert(MYSQL_PREFIX_PORTAL . "browser_sessions", array(
                    "steam_api_key"     => $steam_api_key,
                    "browser_id" => $browser_id,
                    "steam_id"  =>  strip_tags($steam_id),
                    "expiry" => $expiry
                ));
        
    }
	
     	return ($browser_id);
}
  
function decrypt_string(){
    global  $steam_api_key;
    /*
	if (!isset($_COOKIE["sfp_browser_admin"])){
    $browser_id = $_COOKIE["sfp_browser"];
    $browser_id = strip_tags($browser_id);
	} elseif (isset($_COOKIE["sfp_browser_admin"])) {		
    $browser_id = $_COOKIE["sfp_browser_admin"];
    $browser_id = strip_tags($browser_id);
    }
    */

    $browser_id = $_COOKIE["sfp_browser"];
    $browser_id = strip_tags($browser_id);

    $servers = DB::query("SELECT steam_id,expiry FROM " . MYSQL_PREFIX_PORTAL . "browser_sessions WHERE (steam_api_key='$steam_api_key' AND browser_id='$browser_id') ORDER BY id DESC LIMIT 1");

	if(isset($servers[0]["steam_id"])){
        $browser_id = uniqid('sfp',true);
        $expiry=$servers[0]["expiry"];
        $expiry = (int)$expiry;
        setcookie("sfp_browser", $browser_id,$expiry,'/');
        //setcookie("sfp_browser_admin", $browser_id,$expiry,'/admin/');
        
        $steam_id = $servers[0]["steam_id"];
        /*
		DB::update(MYSQL_PREFIX_PORTAL . 'browser_sessions', array(
			'browser_id' => $browser_id,
		), "steam_api_key=%s", $steam_api_key, "steam_id=%s", $steam_id, );
        */
        DB::query("UPDATE ". MYSQL_PREFIX_PORTAL . "browser_sessions
				        SET browser_id='$browser_id' 
                        WHERE (steam_api_key='$steam_api_key' AND steam_id='$steam_id') LIMIT 1");
                        
	}else {
        $steam_id=null;
		unset($_SESSION["steamid"]);
		unset($_COOKIE["sfp_browser"]);
		setcookie("sfp_browser",null,time()-60,'/');
		//unset($_COOKIE["sfp_browser_admin"]);
		//setcookie("sfp_browser_admin",null,time()-60,'/admin');
	}
    return ($steam_id);
}
//$x = decrypt_string();
//var_dump($x);
?> 