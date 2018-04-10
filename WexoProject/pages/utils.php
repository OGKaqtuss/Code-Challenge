<?php

//Return a value that has been secured of sql injection
function secureValue($value){
    $value = trim($value);
    $value = strip_tags($value);
    $value = htmlspecialchars($value);
    return $value;
}


//Return data recieved from curl of an URL
function curl($url) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}


?>