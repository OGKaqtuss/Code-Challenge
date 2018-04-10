<?php


//Include functions
include "pages/utils.php";

session_start();

//Set max number of movie lists on index page
$maxLists = 20;

/**
 * 
 * If categorys.txt doesn't exist or is empty.
 * Create a new one with all the movie categorys from the api
 * 
 */
function curlCategories(){
    $myfile = fopen("cache/categories.txt", "w");
    $categorys = curl("http://bbmiddleware.wexo.dk/page/main");
    fwrite($myfile, $categorys);
    $categorys = json_decode($categorys, true);
    return $categorys; 
}


//Check if categorys.txt exists
if(!file_exists("cache/categories.txt")) {
    $categorys = curlCategories();
} else {
    //Open file for reading
    $myfile = fopen("cache/categories.txt", "r");
    //Check if file is empty, if it is run the function to make a new one
    if(fgets($myfile) == ""){
        $categorys = curlCategories();
    } else {
        //If the file has contents, get the content and decode it.
        $categorys = json_decode(file_get_contents("cache/categories.txt"), true);
    }
}

fclose($myfile);

?>

<!DOCTYPE HTML>
<html>
    <head>
        <link rel="stylesheet" href="css/main.css" type="text/css" />
        <link rel="stylesheet" href="css/media.css" type="text/css"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MovieShowcase | Home</title>
    </head>
    <body>
        <?php 
            //Implement Header
            include_once "pages/header.html";
        ?>
        <div class="main-banner">
            <div class="main-banner-text">
                <h1>Welcome to Movie<span>Showcase</span></h1>
                <p>Get information on more than 100 movies</p>
            </div>
            <div class="main-banner-img"></div>
        </div>
        <section>
            <?php
                //Counter variable
                $maxCount = 0;
                //Loop through all the movie categorys.
                foreach($categorys as $val){
                    /**
                     * 
                     * This part has alot of movies that the other categorys has aswell
                     * So i excluded it.
                     * 
                     */
                    if($val == "bb-all-pas") continue;

                    //Stop looping through categorys if the $maxLists variable is reached
                    if($maxCount >= $maxLists) break;
                    
                    /**
                     * 
                     * If the session isn't set with the movies of the current category
                     * Set a session with all the movies of the current category to make loading faster
                     * 
                     */
                    if(!isset($_SESSION["$val"])){
                        $response = curl("http://bbmiddleware.wexo.dk/feed/$val/movies");
                        $response = json_decode($response, true);

                        if(!isset($response[0]["title"])) continue;

                        $array = array();

                        //Loop through all the movies returned and get specific information
                        foreach ($response as $value){

                            //Get title of movie
                            $title = $value["title"];

                            //Get thumbnail of movie
                            $image = $value["selectedImages"]["thumbnail"];

                            //Get id of movie used to get all information about a movie
                            $simpleId = $value["simpleId"];

                            //Put the movie in an array with $title, $simpleId and $image
                            $array[$title . "--" . $simpleId] = $image;

                        }
                        //Put all information about the movie in a session of specific category
                        $_SESSION["$val"] = $array;
                    }
                    
                    //Get all the movies in the session
                    $result = $_SESSION["$val"];
                    
                    //Get total count of movies
                    $total = sizeof($result);
                    
                    //Get title of movie category and set first letter uppercase
                    $title = ucfirst($val);

                    /**
                     * 
                     * Echo all the information about the movie out as html to be showed on the page
                     * 
                     */
                    echo "<div class='movie-feed $val'>";
                    echo "<h1 class='category-text'>$title</h1>";
                    echo "<h1 class='movie-count-text'>$total Movies</h1>";
                    echo "<div class='show_all_container'><button onclick='show_all()' category='$val' class='main_btn show_all'>Show all</button></div>";
                    echo "<div class='movie-holder movie-holder-$val'>";

                    //Count number of movies 
                    $movieCount = 0;

                    //Loop through all movies in the session and get the key which the value is set to
                    foreach($result as $key => $value){
                        //If the number is above 17 stop the loop
                        if($movieCount > 17) break;

                        /**
                         * 
                         * Explode current movie from array
                         * $key1 will then be title of the movie
                         * $key2 will be simpleId of the movie
                         * $value will be image link of the movie 
                         * 
                         */
                        $key = explode("--", $key);
                        $key1 = $key[0];
                        $key2 = $key[1];
                        echo "<div class='movie' onclick='movieRedirect(".$key2.")'><img title='$key1' data-src='$value' class='movie-cover lazy'/><p class='movie-title'>".$key1."</p></div>";
                        
                        $movieCount++;
                    }
                    echo "</div>";

                    //Only echo the load button if the amount of movies makes whole 3 rows of 6 movies
                    if($total > 17){
                        echo "<div class='container'><button category='$val' class='main_btn load_more'>Load more</button></div>";
                    }
                    echo "</div>";
                    $maxCount++;
                }
            ?>
        </section>

        <?=include "pages/javascripts.html"?>

    </body>
</html>