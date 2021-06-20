<?php

$urlx = 'https://www.sslproxies.org/';

// get 10 search results and populate in to table

    $curl = curl_init('http://127.0.0.1:9999');
    curl_setopt($curl, CURLOPT_URL, "$urlx");
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    //echo $result;

    $domResult = new simple_html_dom();
    $domResult->load($result);


    $ip = false;
    $grab_next = false;
    $arr_proxies = array();

    foreach ($domResult -> find('#proxylisttable tr td text') as $x){

        $x = $x->plaintext;

        $isip = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $x);
        
        if($grabnext == false && $isip>0){
            $ip = $x;
            $grab_next = true;
            continue;

        }else if($grab_next == true){
            $ip = $ip.":".$x; 
            array_push($arr_proxies,$ip);
            $grab_next = false;
            continue;
        }
        
    }

    function get_random_proxy(){
        global $arr_proxies;
        $ix = mt_rand(0, count($arr_proxies) - 1);
        $random_proxy =$arr_proxies[$ix];
        return $random_proxy;
    }
    

?>