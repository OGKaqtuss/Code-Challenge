//require("jquery");

$(function(){
    $(".lazy").Lazy();
});

$(".load_more").on("click", function(){

    //Get the movie category to load more items from
    var category = $(this).attr("category");

    //Get how many items have already been loaded
    var items = $(".movie-holder-"+category).children().length;

    //Get value to start getting movies from in the php file
    var start = parseInt(items)+1;

    //Get end value so it wont load anymore movies past this point
    var end = parseInt(items)+6;

    //Jquery ajax request to request the movies from the curlMovies.php file
    $.ajax({
      url: "curlMovies?update=true&category="+category,
      success: function(data){
        try {
            //Try to parse the data from json
            data = $.parseJSON(data);

            //Count value to make sure duplicate items are not loaded
            var counter = 0;

            //Loop through all the data returned
            for(var i in data){

                //Skip already loaded movies
                if(counter < items){
                    counter++;
                    continue;
                }

                //Get the simpleId value of the movie aswell as the title
                var key = i.split("--");

                //Break the looop when specific number of movies have been loaded
                if(counter >= end)
                    break;
                
                //Create html syntaxed message
                var htmlSyntax = "<div class='movie' onclick='movieRedirect("+key[1]+")'><img src='"+data[i]+"' class='movie-cover'><p class='movie-title'>"+key[0]+"</p></div>"; 
                
                //Append the html to a movie-holder class with the category assigned
                $(".movie-holder-"+category).append(htmlSyntax);

                counter++;
            } 
        } catch(err){
            console.log(err);
        }
      }
    })
})

//Listen for a click on the .show_all class element
$(".show_all").on("click", function(){
    //Get value of the category attribute from the clicked element
    var id = $(this).attr("category");

    //Redirect the page to movielist.php with a category and page number
    window.location.href="./movielist?category="+id+"&p=1";
})

//Listen for a click on the .add_wishlist class element
$(".add_wishlist").on("click", function(){
    //Get value of movie attribute from clicked element
    var movie = $(this).attr("movie");

    //Get action value of current element
    var currentAction = $(this).attr("list-action");

    //Get the cookie with name wishlist
    var wishlist = getCookie("wishlist");
    
    var textBool = false;
    var cookie = "";
    var _self = this;

    //Check if wishlist cookie contains anything
    if(wishlist != ""){
        //Split the wishlist at every / to get all movies saved
        var movies = wishlist.split("/");
        //Loop through all the movies returned after the split to make sure the movie isn't already in the wishlist
        for(var i = 0; i < movies.length; i++){
            //If it is in the list, make the text already added and return
            if(movies[i] == movie){
                movies = removeA(movies, movie);
                $(this).children(0).text("Now removed");
                textBool = true;
            }
        }
        if(textBool == false){
            //If it doesn't already exist in the wishlist add every movie from the wishlist to cookie variable and the new movie
            cookie += movie + "/";
        }
        //Loop through all the remaining movies in the list
        for(var i = 0; i < movies.length; i++){
            //If its the last movie in the list don't add a / at the end
            if(i == movies.length - 1)
                cookie += movies[i];
            else
                //Add all the movies to the cookie again without the newly removed one
                cookie += movies[i] + "/";
        }
    } else {
        //If the wishlist is empty just add the movie.
        cookie = movie;
    }

    //Set a timeout to make the text change back to its original
    setTimeout(function(){
        var displayText = "";
        if(currentAction == "add") displayText = "Remove from wishlist";
        else displayText = "Add to wishlist";
        $(_self).children(0).text(displayText);
    }, 1000)

    if(textBool == false){
        //If the movie wasn't already in the wishlist display text "Now added"
        $(this).children(0).text("Now added");
    }
    //Refresh the wishlist cookie with the new movie
    setCookie("wishlist", cookie, 360);

    //Update the list-action attribute
    if(currentAction == "remove") 
        $(this).attr("list-action", "add");
    else 
        $(this).attr("list-action", "remove");

})

//Listen for a click on the .pagination class element
$(".pagination").on("click", function(){
    //Get p-number attribute of the clicked element
    var page = $(this).attr("p-number");

    var pnow = 0;

    //Get the category parameter in the URL
    var category = getUrlParameter("category");

    //If the p parameters value is nothing or it is undefined we now that the current page is 1
    if(getUrlParameter("p") == "" || getUrlParameter("p") == undefined) pnow = 1;
    //Else the current page is the p parameter from the URL
    else pnow = getUrlParameter("p");

    //If the user clicks the next button but there are no more movies to be displayed return
    if($(".movie").length < 30 && page == "next") return;

    //If the next button is clicked add 1 to the value of the currentPage
    if(page == "next"){
        page = parseInt(pnow) + 1;
    }

    /**
     * Check if category parameter is undefined, if so we know that our user is on the wishlist.php page
     * Else if the category parameter is defined redirect to the new page number with the same category
     */
    if(category != undefined) window.location.href = "./movielist?category="+category+"&p="+page;
    else window.location.href = "./wishlist?p="+page;
})

//Listen for a click on the .menu-icon class element
$(".menu-icon").on("click", function(){
    //Toggle class on the clicked element to make animations run
    $(this).toggleClass("change");
    $(".right-navigation").toggleClass("open");
})

//Defined variable to be used later
var disableLink = false;

//Listen for a hover event on the .remove_wishlist class element
$(".remove_wishlist").hover(function(){
    /**
     * If the .remove_wishlist element is being hovered we need to disable the link that will redirect us -
     * to the page with all the information about a movie
     */
     disableLink = true;
    //Set the brightness of the movie thumbnail to 50%
    $(this).next().css("filter",  "brightness(50%)");
}, function(){
    //When we are no longer hovering, enable the link again and remove the brightness(50%)
    $(this).next().css("filter",  "");
    disableLink = false;
})

//Listen for a click on the .remove_wishlist_button class element
$(".remove_wishlist_button").on("click", function(e){
    //Get the mov-id attribute of the clicked element
    var movID = $(this).attr("mov-id");

    //Remove the movie without a refresh from the page
    $(this).parents(".movie").remove();

    //Get the wishlist cookie
    var movList = getCookie("wishlist");

    //Split the cookie at / to get all the movies in the list
    var movList = movList.split("/");

    //Remove the movie with the specific movID from the array of movies
    var movList = removeA(movList, movID);

    var cookie = "";

    //Loop through all the remaining movies in the list
    for(var i = 0; i < movList.length; i++){
        //If its the last movie in the list don't add a / at the end
        if(i == movList.length - 1)
            cookie += movList[i];
        else
            //Add all the movies to the cookie again without the newly removed one
            cookie += movList[i] + "/";
    }

    $(".movie-count-text").text(movList.length + " Movies");

    //Refresh the wishlist cookie with a new set of movies
    setCookie("wishlist", cookie, 360);
})

//Listen for a hover event on the .movie class elements child: .movie_wishlist
$(".movie.movie_wishlist").hover(function(){
    //Make the "Remove" button visible
    $(this).children(0).css("display", "block");
}, function(){
    //Make the "Remove" button hidden
    $(this).children(0).css("display", "");
})

/**
 * 
 * @param {*} id Id of the movie that the site should be redirected to
 */
function movieRedirect(id){
    //Check if the link should work
    if(disableLink == false)
        window.location.href = "./movie?movID="+id;
}


//Find all the div elements with the attribute [data-src] on the page
//Function for lazy load of parallax backgrounds
[].forEach.call(document.querySelectorAll('div[data-src]'), function(div) {
    //Make the background-image of the found div to the ealier data-src attribute value
    $(div).css('background-image', "url("+$(div).attr("data-src")+")");
    //If the page has loaded fully run this function
    this.onload = function() {
        //Remove the data-src attribute from the div element because the page has now been loaded
        $(div).removeAttr('data-src');
    };
});

/**
 *   SET A COOKIE
 *
 *   @param {string} name Name of cookie
 *   @param {any} val Value the cookie should hold on to
 *   @param {int} days Number of days the cookie should last
 */

function setCookie(name, val, days){
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + days);
    document.cookie = name + "=" + escape(val) + "; expires="+exdate.toUTCString() + "; path=/";
}

/** 
 *   getCookie(name) GET A VALUE OF SAT COOKIE
 *
 *   @param {string} name Name of cookie you wan't to get the value of
 */

function getCookie(name){
    if(document.cookie.length >0){
        var cookie_start = document.cookie.indexOf(name + "=");
        if(cookie_start != -1){
            cookie_start = cookie_start + name.length+1;
            var cookie_end = document.cookie.indexOf(";", cookie_start);
            if(cookie_end == -1){
                cookie_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(cookie_start,cookie_end));
        }
    }
    return "";
}

/**
 * 
 *   getUrlParameter(sParam) GET VALUE OF A PARAMETER IN THE URL
 * 
 *   @param {string} sParam Name of the parameter whichs value you want returned
 * 
 */

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

/**
 * 
 *   removeA(arr) REMOVE SPECIFIC VALUE FROM AN ARRAY
 * 
 *   @param {arr} arr Array that you want a value removed from
 *   @param {*} infinite Put arguments for each value you want removed
 * 
 */
function removeA(arr) {
    var what, a = arguments, L = a.length, ax;
    while (L > 1 && arr.length) {
        what = a[--L];
        while ((ax= arr.indexOf(what)) !== -1) {
            arr.splice(ax, 1);
        }
    }
    return arr;
}
