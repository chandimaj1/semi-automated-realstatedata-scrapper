<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

$url = $_GET['link'];
$domain = $_GET['domain'];
$confidence = $_GET['confidence'];

$url = "http://publicrecords.netronline.com/state/FL/county/clay";
$domain = 'beenverified.com';
$confidence = 100;

include('simple_html_dom.php');
require_once('search_texts.php');
if($conf_proxies){
    require_once('proxies.php');
}

//Extract function to extract from dom
function extract_text($domResult,$extract_find,$i){
    $x = $domResult -> find($extract_find,$i);
    $x = $x -> plaintext;
    return ($x);
}

function extract_zipcode($address) {
    $zipcode = preg_match("/\b[A-Z]{2}\s+\d{5}(-\d{4})?\b/", $address, $matches);
    return $matches[0];
}

 
// Clean passed string for search friendliness
function clean($string) {
    $string = str_replace(' ', '+', $string); // Replaces all spaces with hyphens.
 
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
 }
 $search = clean($search);

   
    $curl = curl_init('http://127.0.0.1:9999');
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt_array($curl,array(
        CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
        CURLOPT_ENCODING=>'gzip, deflate',
        CURLOPT_HTTPHEADER=>array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Cookie',      
        ),
    ));

    $result = curl_exec($curl);
    curl_close($curl);

    $domResult = new simple_html_dom();
    $domResult->load($result);

    

if ($domain=="redfin.com"){
    $mailing_address = extract_text($domResult,'#overview-scroll span[data-rf-test-id=abp-streetLine]',0);
    $mailing_city = extract_text($domResult,'#overview-scroll span[data-rf-test-id=abp-cityStateZip] .locality',0);
    $mailing_state = extract_text($domResult,'#overview-scroll span[data-rf-test-id=abp-cityStateZip] .region',0);
    $mailing_zip = extract_text($domResult,'#overview-scroll span[data-rf-test-id=abp-cityStateZip] .postal-code',0);
    $year_built = extract_text($domResult,'#overview-scroll span[data-rf-test-id=abp-yearBuilt] .value',0);
    $estimated_value = extract_text($domResult,'#overview-scroll div[data-rf-test-id=avm-price] .statsValue',0);
    $last_sale_price = extract_text($domResult,'#overview-scroll div[data-rf-test-id=abp-price] .statsValue',0);
    $bedrooms = extract_text($domResult,'#overview-scroll div[data-rf-test-id=abp-beds] .statsValue',0);
    $bathrooms = extract_text($domResult,'#overview-scroll div[data-rf-test-id=abp-baths] .statsValue',0);
    $state = extract_text($domResult,'span[data-rf-test-id=abp-status] .value text',0);
    $year_built = extract_text($domResult,'span[data-rf-test-id=abp-yearBuilt] .value',0);


}else if($domain=="movoto.com"){
    $mailing_address = extract_text($domResult,'#dppHeader .dpp-header-title .title',0);
    $estimated_value = extract_text($domResult,'#dppHeader .dpp-price span[data-priceupdate]',0);
    $property_use = extract_text($domResult,'div[section=PublicRecord] ul li a[data-ga-name=BasicInfoPropertyType]',0);
    
    $estimated_value = extract_text($domResult,'#lnHomeValueEst',0);
    $property_use = extract_text($domResult,'div[section=PublicRecord] ul li a[data-ga-name=BasicInfoPropertyType]',0);

    

}else if($domain=="realtor.com"){
    $mailing_address = extract_text($domResult,'span[itemprop=streetAddress]',0);
    $estimated_value = extract_text($domResult,'span[itemprop=addressLocality]',0);
    $mailing_state = extract_text($domResult,'span[itemprop=addressRegion]',0);
    $mailing_zip = extract_text($domResult,'span[itemprop=postalCode]',0);
    $bedrooms = extract_text($domResult,"li[data-label=property-meta-beds] span.data-value",0);
    $bathrooms = extract_text($domResult,"li[data-label=property-meta-bath] span.data-value",0);
    $last_sale_price = extract_text($domResult,"#ldp-pricewrap span[itemprop=price]",0);
    $property_use = extract_text($domResult,"#key-fact-carousel li[data-label=property-type] .key-fact-data",0);
    $year_built = extract_text($domResult,'#key-fact-carousel li[data-label=property-year] .key-fact-data',0);

    $last_sale_date = extract_text($domResult,'#ldp-history-price .table tr td',0);
    $last_sale_price = extract_text($domResult,'#ldp-history-price .table tr td',2);
    $state = extract_text($domResult,'#ldp-history-price .table tr td',1);
    
}else if($domain=="trulia.com"){
    $mailing_address = extract_text($domResult,'div[data-testid=home-details-summary-container] span[data-testid=home-details-summary-headline]',0);
    $mailing_state = extract_text($domResult,'div[data-testid=home-details-summary-container] span[data-testid=home-details-summary-city-state]',0);
    $bedrooms = extract_text($domResult,"div[data-testid=home-details-summary-container] ul[data-testid=facts-list] li[data-testid=bed]",0);
    $bathrooms=extract_text($domResult,"div[data-testid=home-details-summary-container] ul[data-testid=facts-list] li[data-testid=bath]",0);
    $last_sale_date= extract_text($domResult,"div[data-testid=price-history-container] td",0);
    $last_sale_price = extract_text($domResult,"h3[data-testid=home-details-price-detail]",0);
    
    $state = extract_text($domResult,"span[data-testid=hero-image-property-tag-0] span",0);



}else if($domain=="estately.com"){
    $mailing_address = extract_text($domResult,'.listing-address h1',0);
    $mailing_state = extract_text($domResult,'.listing-address p',0);
    $bedrooms = extract_text($domResult,'ul.listing-details li strong',0);
    $bathrooms = extract_text($domResult,'ul.listing-details li strong',1);
    $year_built = extract_text($domResult,'ul.listing-details li strong',4);
    $last_sale_date = extract_text($domResult,'.price-info-module .panel-heading p',0);
    $last_sale_price = extract_text($domResult,'.price-info-module .panel-heading div',0);
    $state = extract_text($domResult,'.price-info-module .panel-heading p .text-red',0);

}else if($domain=="zillow.com"){
    $bedrooms = extract_text($domResult,"h3.ds-bed-bath-living-area-container .ds-bed-bath-living-area span",0);
    $bathrooms = extract_text($domResult,"h3.ds-bed-bath-living-area-container .ds-bed-bath-living-area span",2);
    $mailing_address = extract_text($domResult,'.ds-price-change-address-row .ds-address-container span',0);
    $mailing_state = extract_text($domResult,'.ds-price-change-address-row .ds-address-container span',1);
    $state = extract_text($domResult,'.ds-home-details-chip .ds-status-details text',0);
    $last_sale_price = extract_text($domResult,'.ds-home-details-chip .ds-status-details span',1);
    $estimated_value = extract_text($domResult,'#home-details-home-values .zestimate-value',0);

}else if($domain=="beenverified.com"){
    $mailing_address = extract_text($domResult,".add-street .loc-a",0);
    $mailing_city = extract_text($domResult,".add-street .loc-b",0);
    $mailing_zip = extract_zipcode($mailing_city);
    
    
}else{
    $domain = 'no_domain';
}

    //Parsing property details scroll data
    $grab_next = false;

    //check test containing values in a string
function strposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(stripos($haystack, $query, $offset) !== false) return true; // stop on first true result
    }
    return false;
}
    

 switch ($domain){   

    case 'redfin.com':{
        //Legal Desc, Property use
        foreach ($domResult -> find('#property-details-scroll .amenities-container .entryItemContent text') as $x){
            $x = $x->plaintext;
            //Executed 2nd based on grab_next
            if($grab_next=='legal_description'){$legal_description = $x; $grab_next = false; continue;}
            else if($grab_next == "property_use"){ $property_use = $x; $grab_next = false; continue;}
            //Executed 1st and set grab_next
            if (strpos($x, 'Legal Description:') !== false) {$grab_next = "legal_description";}
            else if(strpos($x, 'State Use Description') !== false){$grab_next = "property_use";}
        }


        foreach ($domResult -> find('#house-info .keyDetail') as $k=>$x){
                $y = $x->find('span.header',0)->plaintext;
                $z = $x->find("span.content",0)->plaintext;
                // Executed first
                if (strpos($y, 'Redfin Estimate') !== false) {$estimated_value = $z; continue;}
        }

        $estimated_mortgage_balance = $domResult->find('input[aria-label=Outstanding Mortgage]',0)->value;
    }
    break;

    case 'movoto.com':{

        // City & State
        $x = $domResult -> find('#dppHeader .dpp-header-title a.text-gray',0);
        $x = $x->plaintext;
        $arr = explode(",",$x,2);
        $mailing_city = $arr[0];
        $mailing_state = $arr[1];
        //Zip
        $x = $domResult -> find('#dppHeader .dpp-header-title a.text-gray',1);
        $mailing_zip= $x->plaintext;

        //Beds, Baths, Last Sale Price, Last Sale Date, Year Built
        foreach ($domResult -> find('.dpp-column[section=PublicRecord] ul li text') as $x){
            $x = $x->plaintext;

            // Executed 2nd checking grab_next value
            if($grab_next=="bathrooms"){$bathrooms = $x; $grab_next = false; continue;}
            else if($grab_next == "bedrooms"){ $bedrooms = $x; $grab_next = false; continue;}
            else if($grab_next == "last_sale_price"){ $last_sale_price = $x; $grab_next = false; continue;}
            else if($grab_next == "year_built"){ $year_built = $x; $grab_next = false; continue;}
            else if($grab_next == "last_sale_date"){ $last_sale_date = $x; $grab_next = false; continue;}

            // Executed first
            if (strpos($x, 'Baths') !== false) {$grab_next = "bathrooms";}
            else if(strpos($x, 'Beds') !== false){$grab_next = "bedrooms";}
            else if(strpos($x, 'Last Sale Price') !== false){$grab_next = "last_sale_price";}
            else if(strpos($x, 'Year Built') !== false){$grab_next = "year_built";}
            else if(strpos($x, 'Last Sale Date') !== false){$grab_next = "last_sale_date";}
        }


        foreach ($domResult -> find('div[section=BaseInfo] ul li') as $k=>$x){
            $y = $x->find('span',0)->plaintext;
            $z = $x->find("span",1)->plaintext;

            //echo($y.":".$z);
            // Executed first
            if (strpos($y, 'Property Type') !== false) {$property_use = $z; continue;}
            else if (strpos($y, 'Year Built') !== false) {$year_built = $z; continue;}
            else if (strpos($y, 'Mortgage Payment') !== false) {$mortgage_lender = $x->find('a',1)->plaintext; continue;}
        }

        foreach ($domResult -> find('div#featureInfo .dpp-feature .dpp-feature-item .col-xs-12') as $k=>$x){
            $y = $x->find('span',0)->plaintext;
            $z = $x->find("span",1)->plaintext;

            //echo($y.":".$z."<br>");
            // Executed first
            if (strpos($y, 'Status') !== false) {$state = $z; continue;}
            else if (strpos($y, 'Bathrooms') !== false) {$bathrooms = $z; continue;}
            else if (strpos($y, 'Bedroom Desc') !== false) {$bedrooms = $z; continue;}
        }

        
        
    }
    break;


    case 'realtor.com':{
        
    }
    break;

    case 'trulia.com':{
        $x = $domResult->find('span[data-testid=home-details-summary-city-state]',0)->plaintext;
        $mailing_zip = extract_zipcode($x);
        
    }
    break;

   case 'estately.com':{

        //Beds, Baths, Last Sale Price, Last Sale Date, Year Built
        foreach ($domResult -> find('.home-attribute-group .home-attributes-list') as $x){

            foreach ($x -> find('dt') as $k=>$y){
                $y = $y->plaintext;
                // Executed first
                if (strpos($y, 'Latitude') !== false) {$latitude = $x->find('dd',$k)->plaintext;continue;}
                else if (strpos($y, 'Longitude') !== false) {$longitude = $x->find('dd',$k)->plaintext;continue;}

            }

            
        }

        
    }
    break; 

    case 'zillow.com':{
        //Beds, Baths, Last Sale Price, Last Sale Date, Year Built
        foreach ($domResult -> find('ul.ds-home-fact-list li.ds-home-fact-list-item') as $x){

            foreach ($x -> find('span.ds-home-fact-label') as $k=>$y){
                $y = $y->plaintext;
                // Executed first
                if (strpos($y, 'Type') !== false) {$property_use = $x->find('.ds-home-fact-value',$k)->plaintext;continue;}
                else if (strpos($y, 'Year built') !== false) {$year_built = $x->find('.ds-home-fact-value',$k)->plaintext;continue;}

            }

            
        }
        
    }
    break; 


    case 'beenverified.com':{
        //Beds, Baths, Last Sale Price, Last Sale Date, Year Built
        foreach ($domResult -> find('.result.list-box ul.add-full li') as $x){
            $x = $x->plaintext;
            $x = explode(":",$y);
            $y = $x[0];
            $z = $x[1];
            
            //echo($y.":".$z."<br>");
            if (strpos($y, 'Status') !== false) {$state = $z; continue;}
            
        }
        
    }
    break; 
/*
    default:{
        foreach ($domResult -> find('body text') as $x){
            $x = $x->plaintext;
 

            // Executed 2nd checking grab_next value
            
            if($grab_next=="owner_fname"){$owner_fname = $x; $grab_next = false; continue;}
            else if($grab_next == "owner_lname"){ $owner_lname = $x; $grab_next = false; continue;}
            else if($grab_next == "mailing_address"){ $mailing_address = $x; $grab_next = false; continue;}
            else if($grab_next == "mailing_city"){ $mailing_city = $x; $grab_next = false; continue;}
            else if($grab_next == "mailing_state"){ $mailing_state = $x; $grab_next = false; continue;}
            else if($grab_next == "mailing_zip"){ $mailing_zip = $x; $grab_next = false; continue;}
            else if($grab_next == "bedrooms"){ $bedrooms = $x; $grab_next = false; continue;}
            else if($grab_next == "bathrooms"){ $bathrooms = $x; $grab_next = false; continue;}
            else if($grab_next == "owner_type"){ $owner_type = $x; $grab_next = false; continue;}
            else if($grab_next == "property_use"){ $property_use = $x; $grab_next = false; continue;}
            else if($grab_next == "year_built"){ $year_built = $x; $grab_next = false; continue;}
            else if($grab_next == "estimated_value"){ $estimated_value = $x; $grab_next = false; continue;}
            else if($grab_next == "last_sale_date"){ $last_sale_date = $x; $grab_next = false; continue;}
            else if($grab_next == "last_sale_price"){ $last_sale_price = $x; $grab_next = false; continue;}
            else if($grab_next == "legal_description"){ $legal_description = $x; $grab_next = false; continue;}
            else if($grab_next == "mortgage_date"){ $mortgage_date = $x; $grab_next = false; continue;}
            else if($grab_next == "mortgage_lender"){ $mortgage_lender = $x; $grab_next = false; continue;}
            else if($grab_next == "mortgage_amount"){ $mortgage_amount = $x; $grab_next = false; continue;}
            else if($grab_next == "estimated_mortgage_balance"){ $estimated_mortgage_balance = $x; $grab_next = false; continue;}
            else if($grab_next == "estimated_equity_usd"){ $estimated_equity_usd = $x; $grab_next = false; continue;}
            else if($grab_next == "estimated_equity_percentage"){ $estimated_equity_percentage = $x; $grab_next = false; continue;}
            else if($grab_next == "latitude"){ $latitude = $x; $grab_next = false; continue;}
            else if($grab_next == "longitude"){ $longitude = $x; $grab_next = false; continue;}

            // Executed first
            // Add search texts from search_texts.php
            if (strposa($x, $owner_fname_arr)) {$grab_next = "owner_fname";}
            else if(strposa($x, $owner_lname_arr)){$grab_next = "owner_lname";}
            else if(strposa($x, $mailing_address_arr) !== false){$grab_next = "mailing_address";}
            else if(strposa($x, $mailing_city_arr) !== false){$grab_next = "mailing_city";}
            else if(strposa($x, $mailing_state_arr) !== false){$grab_next = "mailing_state";}
            else if(strposa($x, $mailing_zip_arr) !== false){$grab_next = "mailing_zip";}
            else if(strposa($x, $bedrooms_arr) !== false){$grab_next = "bedrooms";}
            else if(strposa($x, $bathrooms_arr) !== false){$grab_next = "bathrooms";}
            else if(strposa($x, $owner_type_arr) !== false){$grab_next = "owner_type";}
            else if(strposa($x, $property_use_arr) !== false){$grab_next = "property_use";}
            else if(strposa($x, $year_built_arr) !== false){$grab_next = "year_built";}
            else if(strposa($x, $estimated_value_arr) !== false){$grab_next = "estimated_value";}
            else if(strposa($x, $last_sale_date_arr) !== false){$grab_next = "last_sale_date";}
            else if(strposa($x, $last_sale_price_arr) !== false){$grab_next = "last_sale_price";}
            else if(strposa($x, $legal_description_arr) !== false){$grab_next = "legal_description";}
            else if(strposa($x, $mortgage_date_arr) !== false){$grab_next = "mortgage_date";}
            else if(strposa($x, $mortgage_lender_arr) !== false){$grab_next = "mortgage_lender";}
            else if(strposa($x, $mortgage_amount_arr) !== false){$grab_next = "mortgage_amount";}
            else if(strposa($x, $estimated_mortgage_balance_arr) !== false){$grab_next = "estimated_mortgage_balance";}
            else if(strposa($x, $estimated_equity_usd_arr) !== false){$grab_next = "estimated_equity_usd";}
            else if(strposa($x, $estimated_equity_percentage_arr) !== false){$grab_next = "estimated_equity_percentage";}
            else if(strposa($x, $latitude_arr) !== false){$grab_next = "latitude";}
            else if(strposa($x, $longitude_arr) !== false){$grab_next = "longitude";}
            
        }
        break;
        
    }
    */
    
    
}

    $send = array(
        'state'=>"$state",
        'owner_fname'=>"$owner_fname",
        'owner_lname'=>"$owner_lname",
        'mailing_address'=>"$mailing_address",
        'mailing_city'=>"$mailing_city",
        'mailing_state'=>"$mailing_state",
        'mailing_zip'=>"$mailing_zip",
        'bedrooms'=>"$bedrooms",
        'bathrooms'=>"$bathrooms",
        'owner_type'=>"$owner_type", 
        'property_use'=>"$property_use",
        'year_built'=>"$year_built",
        'estimated_value'=>"$estimated_value",
        'last_sale_date'=>"$last_sale_date",
        'last_sale_price'=>"$last_sale_price",
        'legal_description'=>"$legal_description",
        'mortgage_date'=>"$mortgage_date",
        'mortgage_lender'=>"$mortgage_lender",
        'mortgage_amount'=>"$mortgage_amount",
        'estimated_mortgage_balance'=>"$estimated_mortgage_balance",
        'estimated_equity_usd'=>"$estimated_equity_usd",
        'estimated_equity_percentage'=>"$estimated_equity_percentage",
        'latitude'=>"$latitude",
        'longitude'=>"$longitude"
    );
/*
    foreach ($send as $key=>$val){
        echo($key.'='.$val.'<br>');
    }
*/

   

   $send = json_encode($send);
   
   echo($send);
   //echo($domain);
  echo($domResult);
   
   

   
?>