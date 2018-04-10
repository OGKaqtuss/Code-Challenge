<?php

//Include functions from utils.php
include "pages/utils.php";


//Start session
session_start();

//Is the p parameter set? if so it should be the value of the p parameter else it should be 1
$page = (isset($_GET["p"])) ? secureValue($_GET["p"]) : 1;

//Get the data from the wishlist cookie
$result = (isset($_COOKIE["wishlist"])) ? $_COOKIE["wishlist"] : "";
//Split at / to get an array of all the movies
$result = explode("/", $result);

//Get amount of movies
$total = sizeof($result);
$title = "Wishlist";

//Get max number of pages for pagination when each page has a maximum of 30 movies
$pages = ceil($total / 30);

//If there are only enough movies for 3 pages but the p parameter is 4 set the p parameter to 3
if($page > $pages){
    $page = $pages;
}

?>
<html>
    <head>
        <link rel="stylesheet" href="css/main.css" type="text/css"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/media.css" type="text/css"/>
        <title>MovieShowcase | Wishlist</title>
    </head>
    <body>
        <?php include "pages/header.html"?>
        <section class="movie-feed movie-list-feed wishlist">
            <?php 

                echo "<h1 class='category-text'>$title</h1>";
                echo "<h1 class='movie-count-text'>$total Movies</h1>";
                
            ?>
            <div class="movie-holder">
                <?php
                    
                    $movieCount = 0;
                    //Get number where the loop should break
                    $max = $page * 29;

                    //Get the number which the loop should start outputting movies from
                    $min = $max - 30 + ($page - 1);

                    //Set the max to the desired value as the ealier value was to be used in the $min value and here
                    $max = $max + $page;

                    //Loop through all the movies from this category
                    foreach($result as $value){
                        //Skip movies already output on previus page
                        if($movieCount <= $min) {
                            $movieCount++;
                            continue;
                        }

                        //Be sure it won't try to get data from empty result from the wishlist
                        if(strlen($value) < 1) continue;
                        
                        if($movieCount >= $max) break;

                        //Get data of the movies
                        $data = curl("http://bbmiddleware.wexo.dk/movie/$value");
                        $data = json_decode($data, true);

                        //Get movie title
                        $title = $data[0]["title"];

                        //Get movie thumbnail image
                        $image = $data[0]["selectedImages"]["thumbnail"];

                        //Echo the movie on to the page
                        echo "<div class='movie movie_wishlist' onclick='movieRedirect(".$value.")'><div class='remove_wishlist'><div class='thumb-button'><a mov-id='$value' class='remove_wishlist_button'>Remove</a></div></div><img data-src='$image' class='movie-cover lazy'/><p class='movie-title'>".$title."</p></div>";
                        $movieCount++;
                    }

                ?>
            </div>
            <div class="list-bottom-bar">
                <div class="bottom-bar">
                    <div class="pages">
                        <ul>
                            <?php
                                //Make the pagination bar at the bottom 
                                for($i = 1; $i <= $pages; $i++){
                                    if($i == $page){
                                        echo "<li p-number='$i' class='active pagination'>".$i."</li>";
                                    } else {
                                        echo "<li p-number='$i' class='pagination'>".$i."</li>";
                                    }
                                }  
                                echo "<li p-number='next' class='pagination'>Next</li>";
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <?=include "pages/javascripts.html"?>
    </body>
</html>