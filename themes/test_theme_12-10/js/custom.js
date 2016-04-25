/* 	JavaScript file for Listen4Insight
	By Matt DePero
*/


/* Magic vertical height fix */

$(document).ready(function(){

    $('.podcast_list').each(function(){ 

        var highestBox = 0;
        $('.top-half-of-episode', this).each(function(){

            if($(this).height() > highestBox) 
               highestBox = $(this).height(); 
        });  

        $('.top-half-of-episode',this).height(highestBox);

	});

    if(window.location.href.indexOf("#pk_campaign") != -1){

        setTimeout(
          function() 
          {
                document.location.hash = "";
                console.log("masked campaign url");
          }, 2000);

        }


});





// Doesn't work......

// $( window ).resize(function(){

//     $('.top-half-of-episode',this).css('height', null);

//     $('.podcast_list').each(function(){  

//         var highestBox = 0;
//         $('.top-half-of-episode', this).each(function(){

//             if($(this).height() > highestBox) 
//                highestBox = $(this).height(); 
//         });  

//         $('.top-half-of-episode',this).height(highestBox);

// 	});
// });