<?php

//Include functions from utils.php
include "pages/utils.php";

//Start session
session_start();

//If the category parameter isn't set, return to the index page
if(!isset($_GET["category"])){
    exit(header("Location: index"));
}

//Get secureValue of category parameter value and in lowercase
$category = strtolower(secureValue($_GET["category"]));

//Is the p parameter set? if so it should be the value of the p parameter else it should be 1
$page = (isset($_GET["p"])) ? secureValue($_GET["p"]) : 1;

//If session of specified category isn't set, curl a new list of movies from the category else get it from session
if(!isset($_SESSION["$category"])){
    include "curlMovies?category=$category";
} 
else {
    $result = $_SESSION["$category"];
}

//Get amount of movies
$total = sizeof($result);

//Set the title to the specified category with first character as uppercase
$title = ucfirst($category);

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
        <title>MovieShowcase | <?=ucfirst($category)?></title>
    </head>
    <body>
        <?php include "pages/header.html"?>
        <section class="movie-feed movie-list-feed <?=$category?>">
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
                    foreach($result as $key => $value){
                        //Skip movies already output on previus page
                        if($movieCount <= $min) {
                            $movieCount++;
                            continue;
                        }
                        if($movieCount >= $max) break;

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
                        echo "<div class='movie' onclick='movieRedirect(".$key2.")'><img data-src='$value' class='movie-cover lazy'/><p class='movie-title'>".$key1."</p></div>";
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