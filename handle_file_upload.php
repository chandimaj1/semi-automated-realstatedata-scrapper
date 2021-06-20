<?php
$csv_array = Array();
$file = fopen($_FILES['file']['tmp_name'], 'r');
if($file){
    fgetcsv($file, 1000, ",");
    while (($line = fgetcsv($file)) !== FALSE) {
      //$line is an array of the csv elements
      
      array_push($csv_array,$line);
    }
    fclose($file);
}

//$send = json.encode($csv_array);
//echo($send);

$send = json_encode($csv_array);
echo($send);

?>