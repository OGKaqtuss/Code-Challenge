<?php 

//Include function from the utils.php
include "pages/utils.php";

//If the movID parameter isn't set return to the index page
if(!isset($_GET["movID"])){
    exit(header("Location: index"));
}

//Get secureValue of movID parameter value
$movieID = secureValue($_GET["movID"]);

//Get all movies from wishlist to determine if (this) movie is in it
$wishlist = (isset($_COOKIE["wishlist"])) ? $_COOKIE["wishlist"] : "";

//Check if the movie simpleId is in the list
if(strpos($wishlist, $movieID) !== false) {
    $wishlistText = "Remove from wishlist"; 
    $wishlistAttr = "remove";
}
else {
    $wishlistText = "Add to wishlist";
    $wishlistAttr = "add"; 
} 

//Get all information about the movie
$movie = curl("http://bbmiddleware.wexo.dk/movie/$movieID");
$movie = json_decode($movie, true);

//Get title of movie
$title = $movie[0]["title"];

//Get description of the movie and make it ready for html
$desc = "<p>".$movie[0]["description"]."</p>";

//Get the thumbnail of the movie
$thumbnail = $movie[0]["wexoAdditional"]["selectedImages"]["thumbnail"];

//Get the cover of the movie to be set as background
$backCover = $movie[0]["wexoAdditional"]["selectedImages"]["background"];

//Get how long the movie is
$runtime = $movie[0]['plprogram$runtime'];

//Get credits to check for directors and actors
$credits = $movie[0]['plprogram$credits'];

//Get tags to get genres
$tags = $movie[0]['plprogram$tags'];

//Make the runtime from seconds to Hours and Minutes
$runtime = $runtime / 60;
$runtimeMin = $runtime % 60;
$runtime = $runtime - $runtimeMin;
$runtime = $runtime / 60;

//Check its more than 2 hours, if so it should be "timer" instead "time"
if($runtime < 2){
    $runtime = $runtime . " time";
} else {
    $runtime = $runtime . " timer";
}

//Check if the movie is exactly a specific amount of hours so it wont write out "0 minutter"
if($runtimeMin != 0){ 
    $runtime = $runtime . " og " . $runtimeMin . " minutter";
} else {
    $runtime = $runtime;
}

//Check if the movie is less than 1 hour, if so only write out minutes
if($runtime < 1){
    $runtime = $runtimeMin . " minutter";
}

$trailer = "This video has no trailer";

//Check if the movie has a trailer and afterwards set the $traielr value to the link of the trailer
if(isset($movie[0]['tdc$youtubeTrailer'])) $trailer = $movie[0]['tdc$youtubeTrailer'];

//Get year the movie was produced in
$year = $movie[0]['plprogram$year'];

//Check if the movie has seoText, if not only write out the description
if(isset($movie[0]['tdc$seoText'])) $about = $movie[0]['tdc$seoText'];
else $about = $desc;

//Check if the movie has seoText, but the seoText doesn't include a description
if(substr($about, 0, 4) == "<h2>") {
    $desc .= "\r\n" . $about;
    $about = $desc;
}


$actors = array();
$directors = array();

$genres = "";

//Get all the actors and directors, then put them in arrays.
foreach($credits as $value){
    if($value['plprogram$creditType'] == "actor"){
        array_push($actors, $value['plprogram$personName']);
    } else if ($value['plprogram$creditType'] == "director"){
        array_push($directors, $value['plprogram$personName']);
    }
}

//Get the genres of the movie
foreach($tags as $value){
    if($value['plprogram$scheme'] == "genre"){
        $genres .= $value['plprogram$title']. " / ";
    } 
}

//Trim the last / from the genres so it won't be "Action / Comedy / "
$genres = rtrim($genres, "/ ");
?>

<!doctype html>
<html>
    <head>
        <title><?=$title?></title>
        <link rel="stylesheet" href="css/main.css" type="text/css"/>
        <link rel="stylesheet" href="css/media.css" type="text/css"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <?php include_once "pages/header.html" ?>
        <div class="movie-banner">
            <div data-src="<?=$backCover?>" class="movie-cover-big"></div>
        </div>
        <div class="movie-contentcontainer">
            <div class="movie-details-container">
                <div class="movie-thumbnail-container">
                    <img class="movie-thumbnail" src="<?=$thumbnail?>" />
                </div>
                <div class="thumbnail-details">
                    <div class="thumbnail-inner">
                        <h1><?=$title?></h1>
                        <div class="movie-trailer-box">
                            <iframe class="trailer_frame" src="https://www.youtube.com/embed/<?=$trailer?>?rel=0&showinfo=0&html5=1"></iframe>
                        </div>
                        <div class="trailer-bottom-bar">
                            <ul class="trailer-bottom-list">
                                <li list-action="<?=$wishlistAttr?>" movie="<?=$movieID?>" class="add_wishlist"><a><?=$wishlistText?></a></li>
                            </ul>
                        </div>
                    </div>
                
                </div>
            </div>
            <div class="movie-extra-details">   
                <div class="movie-left-info">
                    <div class="movie-facts">
                        <h2><?=$genres?></h2>
                        <h2><?=$runtime?></h2>
                        <h2><?=$year?></h2>
                    </div>
                    <div class="movie-actors-box">
                        <div class="actors-box-title">
                            <h1>Crew</h1>
                        </div>
                        <div class="movie-actors-inner">
                            <div class="actor-box">
                                <ul>
                                    <?php 
                                        foreach($directors as $val){
                                            echo "<li class='actor'><h5>$val</h5><h6 class='actor-facts'>Director</h6></li>";
                                        }
                                        foreach($actors as $val){
                                            echo "<li class='actor'><h5>$val</h5><h6 class='actor-facts'>Actor</h6></li>";
                                        }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="movie-right-info">
                    <div class="movie-resume">
                        <h2>Resume</h2>
                        <?="$about"?>  
                    </div>
                </div>
            </div>
        </div>

        <?=include "pages/javascripts.html"?>
    </body>
</html>