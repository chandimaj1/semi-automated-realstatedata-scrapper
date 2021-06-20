<?php

$search = $_POST['search'];

include('simple_html_dom.php');
require_once('known_domains_list.php');

if($conf_proxy){
    require_once('proxies.php');
}


// Clean passed string for search friendliness
function clean($string) {
    $string = str_replace(' ', '+', $string); // Replaces all spaces with hyphens.
 
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
 }
 $search = clean($search);


 //Iterate each search for 10 results with a random time gap to circumvent Google search block
 $i=0;
 for ($i; $i < 10; $i=$i+10) {
    populate_results($i);
    
    $time = rand(1,5);
    sleep($time); // this should halt for random seconds 1 - 5 for every loop
}

// Get domain name from url
function getDomain($url){
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';
    if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
        return $regs['domain'];
    }
    return FALSE;
}

//Get weight of the domain if exist
function getweight($domain) {
    global $domains;
    $j = count($domains);
  
    for ($i=0; $i<$j; $i++){
        if ($domains[$i][0] == $domain) {
            return $domains[$i][1];
        }
    }
    return 0;
    
    
 }

// get 10 search results and populate in to table
function populate_results($i){
    global $search;
    $x = $i+10;
    //echo("<h4>showing results for $search of $i and $x </h4>");
    $s = '';
    if ($i<10){
        $s = '';
    }else{
        $s = "&start=".$i;
    }
    $q = $search.$s;

    
    $curl = curl_init('http://127.0.0.1:9999');
    curl_setopt($curl, CURLOPT_URL, "https://www.google.com/search?q=$q");
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    if($conf_proxy){ // If proxy enabled
        $proxy = get_random_proxy();
        $proxy = explode(':', $proxy);
        curl_setopt($curl, CURLOPT_PROXY, $proxy[0]);
        curl_setopt($curl, CURLOPT_PROXYPORT, $proxy[1]);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    //echo $result;

    $domResult = new simple_html_dom();
    $domResult->load($result);

    // Format data for each result line and create data row
    foreach($domResult->find('a[href^=/url?]') as $link){
        $title = trim($link->plaintext);
        $arr = explode("|", str_replace(array("-"),"|",$title),2);
        $title = $arr[0];
        $link  = trim($link->href);

        if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
            $link = $matches[1];
        } else if (!preg_match('/^https?/', $link)) { // skip if it is not a valid link
            //continue;    
        }

        $domain = getDomain($link);

        echo '<tr><td id="t_title">' . $title . '</td>';
        echo '<td id="t_link">' . $link . '</td><td id="t_domain">'.$domain.'</td><td id="t_weight">'.getweight($domain).'</td></tr>';
    }
}



?>