<?php

//Include functions from the utils.php
include_once "pages/utils.php";

//Don't report errors
error_reporting(0);


//Start session
session_start();

//Check if update parameter is set
$update = (isset($_GET["update"])) ? true : false;

//If the category parameter isn't set, exit
if(!isset($_GET["category"])) exit("No category found!");

//Make the category a secureValue and lowercase
$category = strtolower(secureValue($_GET["category"]));

//check if the session of the specified category is set
if(isset($_SESSION["$category"])){
    //If it is, set $result as the value
    $result = $_SESSION["$category"];

    //Check if it was requested by the javascript.
    //If so, return the session json encoded
    if($update == false)
        return;
    exit(json_encode($_SESSION["$category"], true));
}

//Get all the movies from the specified category
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://bbmiddleware.wexo.dk/feed/$category/movies",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));

//Data recieved from the curl
$response = curl_exec($curl);

//Json decode curl response
$response = json_decode($response, true);

$err = curl_error($curl);

curl_close($curl);

$array = array();

$checkCount = 0;

//Loop through all the movies returned and get specific information
foreach ($response as $value){

    //Get title of the movie
    $title = $value["title"];

    //Get thumbnail of the movie
    $image = $value["selectedImages"]["thumbnail"];

    //Get id of the movie
    $simpleId = $value["simpleId"];

    //Push to the $array with a key ($title--$simpleId) and a value($image)
    $array[$title . "--" . $simpleId] = $image;

    $checkCount++;

}

$_SESSION["$category"] = $array;

if ($err) {
  $result = "cURL Error #:" . $err;
} else {
  $result = $array;
  if($update == true) exit(json_encode($array, true));
}



?>