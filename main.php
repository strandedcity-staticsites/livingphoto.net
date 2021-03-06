<?php
# include the function here
include 'function.resize.php';

$height = $_POST["height"];
$width = $_POST["width"];

if (empty($height) || empty($height)) {
	# set default image size if not set by POST data:
    $height = 768;
	$width = 1366;
}

$settings = array('w'=>$width,'h'=>$height,'quality'=>58);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Living Photography | Phil Seaton</title>
<link rel="stylesheet" href="css/redmond/jquery-ui-1.8.18.custom.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="js/jquery.imgpreload.min.js"></script>
<script src="js/jQueryRotateCompressed.2.1.js"></script>
<script src="js/jquery-ui-1.8.18.custom.min.js"></script>

<script type="text/javascript">

<!--BEGIN GOOGLE ANALYTICS CODE-->
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-29165519-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
<!--END GOOGLE ANALYTICS CODE-->

$(document).ready(function(){
	////////////////////////////////////// TEST MODE SKIPS ALL THE LOADING STUFF AND CUTS RIGHT TO THE SITE:
	var testMode = false;
	
	// Prep the height and widths of the fullscreen images:
	var currentConstraint = ''; // store globally the window orientation. When this changes b/c of resize, it will update the associated image styles as needed.
	setupFullScreen();
	
	$(window).resize( function() {
		setupFullScreen();
		recenterFullScreen();
		recenterLetterBoxed('animated');
	});

	function recenterLetterBoxed(method) {
		// centers "letterboxed" images inside their containing DIV
		$('.letterboxed_image').each(function() {
			var offsetH = ( $(this).parent().width() - $(this).width() ) / 2;
			if ( $(this).hasClass('alignLeft') ) {
				offsetH = 0;
			}
			var offsetV = ( $(this).parent().height() - $(this).height() ) / 2;
			$(this).data('offsetH',offsetH);
			$(this).data('offsetV',offsetV);
			if (method == 'animated') { 
				if ( $(this).hasClass('alignRight') ) {
					$(this).stop().animate({right:0,top:offsetV},300);
				} else {
					$(this).stop().animate({left:offsetH,top:offsetV},300);
				}
			}
			else { 
				if ( $(this).hasClass('alignRight') ) {
					$(this).css({right:0,top:offsetV},300); 
				} else {
					$(this).css({left:offsetH,top:offsetV},300); 
				}
			}
			
		});
	}
	
	
	var loadedImageCount = 0;
	var mainscreenLoaded = false;
	
	// we'll use an array to hold the paths of the images to preload:
	var preloadImages = []; // array to hold the images for preload
	preloadImages.push( 'images2012/logo.png' );
	preloadImages.push( 'images2012/main-icons.png' );
	preloadImages.push( $('#firstload').attr('src') );  // preload main BG image
	preloadImages.push( $('#galleries-show img').first().attr('src') ); // preload first gallery image
	preloadImages.push( $('#raves-show img').first().attr('src') ); // preload first ravesshow image

	if (testMode) {
			initMain(); // skips intro
	} else {
		$.imgpreload(preloadImages,
		{
			each: function()
			{	
				var name = $(this).attr('src');
				
				// wait until logo.png loads. Once it does, start the distracting splash animation.
				// For other images that load up, just keep a tally so we can wait until they all load up:
				
					if ( name == 'images2012/logo.png') {
						// start the cute splash animation. It's all programmatic, defined here:
						$("#logoImage").rotate({ angle:14, }); // set image starting angle
						$("#logoImage").delay(1700).queue(function() { // wait, then rotate the image
							$("#logoImage").rotate({ angle:14, animateTo:0, duration: 650 });
						});
						$('#loadingbox').delay(500).fadeIn(300).delay(2600).fadeToggle(300, function() { splashComplete();} );
						$('#inner').delay(700).fadeIn(300).delay(2300).fadeOut(200);
					} else {
						// add 1 to the tally of loaded images:
						loadedImageCount++;
						setProgress(parseInt(100 * loadedImageCount / (preloadImages.length-1) ) );
					}
				
			}, all: function() {;} // do nothing in particular once everything loads. This event will be handled by the progress bar instead.
		});
	}
	function splashComplete() {
		// function runs after cute splash animation. It checks to see if all the preloading is done. If so, it skips directly
		// to the main page loading. Otherwise it initializes, shows, and binds a handler to the progress bar:
		if (loadedImageCount == preloadImages.length-1) {
			// preload complete; skip directly to main screen
			initMain();	
		} 
		else {
			// preload still working; show the progress bar and bind the "showmain" routine to its completion:
			toggleProgress('on');
			$( "#progressbar" ).bind( "progressbarcomplete", function() {
				initMain();
			});
		}
	}
	
	// Initiate progress bar:
	$( "#progressbar" ).progressbar({value: 1});
	
	// toggle progressbar visibility:
	function toggleProgress(setValue) {
		if (setValue == 'on') {
			$('#progressbar').fadeIn(200);
		} else {
			$('#progressbar').fadeOut(200);
		}
	}
	
	// handle the setting of the progress bar to a particular level:
	function setProgress ( percent ) {
		$( "#progressbar" ).progressbar( "option", "value", percent );
	}
	
	// show main screen:
	function initMain() {
		// finish with the progress bar:
		toggleProgress('off');
		$( "#progressbar" ).unbind();
		
		// move the big background image offscreen, show it, then use the recentering method to animate it into fullscreen position
		$('#firstload').css({left:$(window).width(),display:'block'});
		recenterFullScreen("slow");
		
		// animate main navigation menus into place:
		mainMenuInit();
	}
	
	// show details of special when user clicks the big ad:
	$("#showspecialdetails").click( function() {
		$("#special-details").fadeIn(300);
	});
	$("#special-details").click( function() {
		$("#contact").trigger('click');
	});

	
	// Compare screen shape to aspect ratio of fullscreen images (image files will be full HD); apply appropriate styles for positioning:
	function setupFullScreen() {
		var browserAR = $(window).width() / $(window).height();
		var imageAR = 1920/1080;
		if ( (browserAR >= imageAR && currentConstraint == 'height') || (browserAR < imageAR && currentConstraint == 'width') || currentConstraint == '' ) {
			// user has changed window proportions enough to change constraints on the image:
			$('.fullscreen_image').removeClass('fullscreen_widthconstrained').removeClass('fullscreen_heightconstrained').addClass( function() {
				if (browserAR >= imageAR) {
					currentConstraint = 'width';
					return 'fullscreen_widthconstrained';
				} else {
					currentConstraint = 'height';
					return 'fullscreen_heightconstrained';	
				}
			});
		}
	}
	
	function recenterFullScreen(speed) {
		var duration;
		if (speed == "slow") {duration = 1000;}
		else {duration = 300;}
		// Regardless of whether the constraint changes, we have to re-center the images in their boxes as the window is resized:
		if (currentConstraint == 'width') {
			// figure out how many pixels overhang the window, knowing that fullscreen images will be fullHD proportions.
			// these offsets have to be calculated from the known proportion because the auto-sizing doesn't resize fast enough
			// to be read directly
			var offsetH = parseInt( ( $(window).height() - (1080*$(window).width()/1920) ) / 2 );
			$('.fullscreen_image').stop().animate({left:0, top:offsetH},duration);
		} else { // height-constrained
			var offsetW = parseInt( ( $(window).width() - (1920*$(window).height()/1080) ) / 2 );
			$('.fullscreen_image').stop().animate({top:0,left:offsetW},duration);
		}
	}
	
	////////////////////// The following functions deal with the preparation of the main menu items:
	$('.navbar-bar').width( function() {
		// the invisible navbar containers need to be 20px wider than the visible parts because of a strange
		// shape-morphing issue that occurred with the rounded corners when animating the visible part directly.
		var w = $(this).children('.navbar-visible-bar').width()+20;
		return w;
	});
	
	$('.navbar-bar').hover( function() {
			// hover in
			$(this).stop().animate({"left":"0px"}, 150);  // open menu
			
			if ( ! $(this).hasClass('clicked') ) {
				$(this).children('.navbar-visible-bar').addClass('navbar-hover',200); // highlight green
			}
			
			// make sure the menu doesn't hide on the gallery and raves screens:
			$('#navbar-container').data('mouseover',true);
		}, function() {
			// hover out
			if (!$(this).hasClass('paused')){
				animateHome($(this)); // close menu
			} else {$(this).removeClass('paused');}
			if ( ! $(this).hasClass('clicked') ) {
				$(this).children('.navbar-visible-bar').removeClass('navbar-hover',200); // clear green highlight
			}
			// allow menu to hide on the gallery and raves screens:
			$('#navbar-container').data('mouseover',false);

		}
	);
	
	// handle click events for main menu items:
	$('.navbar-bar').click( function() {
		// clear previously clicked items
		$('.navbar-bar').not($(this)).removeClass('clicked').children('.navbar-visible-bar').removeClass('navbar-hover').removeClass('navbar-click',200); 
		$(this).addClass('paused').delay(800).queue( function() {animateHome($(this));} ); // wait a beat, then return to "icon" state (but with BG color indicating state)
		$(this).addClass('clicked'); // prevent hover events from changing things
		$(this).children('.navbar-visible-bar').addClass('navbar-click',200);
		
		/// HIDE ALL CONTENT EXCEPT THE ONE THAT'S BEEN REQUESTED:
		$('.hideable-content').not($('#' + $(this).attr('id') + '_content')).fadeOut(600);
		
		// Hide the gallery navbar if exiting the gallery / raves sections
		if ( $(this).attr('id') != 'galleries_button' && $(this).attr('id') != 'raves_button' ) {
			// organize the main and gallery navbars to show permanently or hide entirely:
			$('#navbar-container').data('persistent',true);
			toggleMainBar( true );
			$('#gallery-nav-container').data('allowed',false);
			$('#gallery-nav-container').animate({bottom:0},'fast');
		}
		
		/// LOAD CONTENT!
		$('#' + $(this).attr('id') + '_content').fadeIn(600);
		
		// stop slideshows (always) -- they'll be restarted in just a moment if necessary:
		stopSS( $('#raves-show') );
		stopSS( $('#galleries-show') );
		
		// Start slideshows if necessary:
		if ( $(this).attr('id') == 'galleries_button' || $(this).attr('id') == 'raves_button' ) {
			
			// Gallery and raves sections share some preparation of the menu items and slide layouts:
			$('#navbar-container').data('persistent',false);
			$('#gallery-nav-container').data('allowed',true);
			recenterLetterBoxed();
			
			// now, start the particular show that's been requested:
			if ($(this).attr('id') == 'raves_button'){
				updateGalleryNavbar ( 'raves-show' );
				startSS($('#raves-show')); 
			} else if ( $(this).attr('id') == 'galleries_button' ) {
				updateGalleryNavbar ( 'galleries-show' )
				startSS($('#galleries-show')); 
			}
		}
	});
	
	// store mouseover data for gallery navbar to prevent it from disappearing during use:
	$('#gallery-nav-container').hover(function() {
		$(this).data('mouseover',true);	
		}, function () {
		$(this).data('mouseover',false);	
	});
	
	// add "click for next" functionality to each slide:
	$('.cycle-slide').click( function() {
		advanceSlide( $(this).parent() , 'next');
	});
	
	// add "move to show menu" functionality to each slide:
	$('.cycle-slide').mousemove(function() {
		toggleMainBar(true);
		if ($('#navbar-container').data('timer')) {clearTimeout($('#navbar-container').data('timer'));}
		var t = setTimeout( function() {
			if ( !$('#navbar-container').data('mouseover') && !$('#gallery-nav-container').data('mouseover') ) { // if the user isn't on the navbar container, hide it after 800ms
				toggleMainBar(false);
			}
		}, 800 );
		$('#navbar-container').data('timer',t);
	});
	
	// add slide-advancement to the arrow buttons in the gallery navbar:
	$('#arrow-left').click(function() {advanceSlide( $('#'+$('#gallery-nav-container').data('currentshow') ) , 'back');});
	$('#arrow-right').click(function() {advanceSlide( $('#'+$('#gallery-nav-container').data('currentshow') ) , 'next');});
	
	// animates menu items back to home position
	function animateHome(jqueryObj) {
		var homeposition = parseInt(jqueryObj.data('left-home-position'));
		jqueryObj.stop().animate({"left":homeposition+"px"}, 300);
	}
	
	// Initialize main menu:
	function mainMenuInit() {
		$('#navbar-container').css({display:'block'});
		$('.navbar-bar-container').delay(400).each( function(index){
			// animate each button into its home position:
			$(this).delay((index+1)*75).animate({left:'0px'},"fast", function() {
				// for the first button, simulate clicking it so that the "home" button activates on initial loading:
				if (index == 0) {
					$('#home_button').delay(1500).trigger('mouseenter').trigger('click').trigger('mouseleave');	
				}
			});
			
			// store "home position" data so that the hover and click commands can work:
			var left = $(this).children('.navbar-bar').position().left;
			$(this).children('.navbar-bar').data('left-home-position',left);
			$(this).children('.navbar-bar').css({left:left});
		});
	};
	
	// function to toggle main navbar presence on/off during gallery shows
	function toggleMainBar( visible ) {
		// if visble = false, this function hides the bar; inverse if true
		if ( !visible  && !$('#navbar-container').data('persistent') ) {
			$('#navbar-container').data('animatingIN',true)
			$('#navbar-container').stop().animate({left:-50},'fast');
			// hide the gallery navbar, too:
			$('#gallery-nav-container').animate({bottom:0},'fast');
			$('#navbar-container').data('animatingIN',false);
		} else if ( !$('#navbar-container').data('animatingIN') ) {
			clearTimeout($('#navbar-container').data('timer'));
			$('#navbar-container').data('animatingIN',true);
			$('#navbar-container').stop().animate({left:18},'fast',function(){$(this).data('animatingIN',false);});
			// show the gallery navbar, too:
			if ( $('#gallery-nav-container').data('allowed') ) {
				$('#gallery-nav-container').animate({bottom:60},'fast');
			}
		} 
	}
	//////////////////////////// END preparation of main menu items
	
	// Activate the datepicker widget for the form input:
	$( "#weddate" ).datepicker();
	
	///////// Prepare slideshows:
	setupFScycler($('#raves-show'),'fade',4500);
	setupFScycler($('#galleries-show'),'fade',3500);
	
	
	
	function setupFScycler(jqueryobj, transition, delay) {
		// transition (either fade or slide) specifies how slides are changed
		// delay is how long they're on display
		jqueryobj.data('running',false);
		jqueryobj.data('transition',transition);
		jqueryobj.data('delay',delay);
		
		// assing ID's to each slide within the show:
		var slideBaseName = jqueryobj.attr('id');
		jqueryobj.children().each( function(index) {
			$(this).attr('id',slideBaseName+'_slide_'+index);
		});
		
		// show first slide by default (the whole show should be hidden until needed):
		jqueryobj.children().first().show();
		jqueryobj.children().first().addClass('currentSlide');
		
		// make all slides except the current one invisible, either by moving offscreen or hiding:
		if ( transition == 'fade' ) {jqueryobj.children().not('.currentSlide').hide();}
		else {jqueryobj.children().not('.currentSlide').css({left:'50%'}).hide();}
	}
	
	// listen for clicks on the pause button:
	$('#pauseButton').click(function() {
		// when the user clicks "play", the show should start immediately rather than waiting for the first delay
		if ( $(this).html() == 'play' ) {$('#arrow-right').trigger('click');}
		
		// handle the clicking of the pause/play button
		togglePlayBack( $( '#' + $('#gallery-nav-container').data('currentshow') ) );
	});
	
	function updateGalleryNavbar ( currentShowID ) {
		// Changes the target of the play/pause/back/forward buttons, and updates the statistics re: which slide is showing:
		$('#gallery-nav-container').data('currentshow',currentShowID);
		
		// update the total slide count for the gallery navbar:
		$('#slide_count_total').html( $('#'+currentShowID ).children().size() );
		
		// update the current slide number for the current show:
		var sequenceNo = $('#'+currentShowID ).children('.currentSlide').first().attr('id');
		var chopOff = ( currentShowID + '_slide_').length; // remove the "prefix" from the slide's ID to get its original sequence number
		$('#slide_count_current').html(parseInt(sequenceNo.substr(chopOff))+1); // add one so as never to say "slide zero"
		
		// Make sure the play/pause button is current:
		if ( $('#'+currentShowID ).data('running') ) {
			$('#pauseButton').html('pause');
		} else {
			$('#pauseButton').html('play');
		}
	}

	function togglePlayBack(jqueryobj) {
		if ( !jqueryobj.data('running') ) {startSS(jqueryobj);}	
		else {stopSS(jqueryobj);}	
	}
	
	function startSS(jqueryobj) {
		
		// it's critical that this be defined before a slideshow is advanced:
		jqueryobj.data('running',true);
		
		var t = setTimeout( function() {advanceSlide(jqueryobj,'next')}, jqueryobj.data('delay') );
		jqueryobj.data('timerID',t);
		
		// hide the main menu:
		setTimeout ( function() {toggleMainBar( false ) } , 1200 ) ; // wait a beat, then hide the menu
		
		// update gallery navbar
		updateGalleryNavbar ( jqueryobj.attr('id') );
	}
	
	function stopSS(jqueryobj) {
		if (jqueryobj.data('running') == true) {
			jqueryobj.data('running',false);
			clearTimeout(jqueryobj.data('timerID'));
		}
		
		// update gallery navbar
		updateGalleryNavbar ( jqueryobj.attr('id') );
	}
	
	function advanceSlide(jqueryobj,direction) {
		var style = jqueryobj.data('transition');
		var delay = jqueryobj.data('delay');
		// jquery obj stores which slideshow should be advancing
		// direction will either be "next" "back"
		// style will either be "fade" or "slide" for the two effects used on this page
		// delay will specify how long the slide will show. If zero, no timer is set
		
		// cancel the timer:
		clearTimeout(jqueryobj.data('timerID'));

		// 1: retrieve the jquery object associated with the current slide:
		var currentSlide = jqueryobj.children('.currentSlide').first();

		// 2: retrieve the jquery object for the slide that should be showed next, depending on "direction" input
		var nextSlide;
		if (direction == undefined || direction == 'next') {
			nextSlide = jqueryobj.children('.currentSlide').first().next();
			if (nextSlide.index() == -1 ) {
				// we're on the last slide; time to wrap around
				//nextSlide = jqueryobj.children().first(); // WORKS, but only if fadeouts are allowed
				jqueryobj.append( jqueryobj.children().first() ); // move the first element into the last position so it appears on top
				nextSlide = jqueryobj.children().last(); // set the next slide
			}
		} else if (direction == 'back') {
			nextSlide = jqueryobj.children('.currentSlide').first().prev();
			if (nextSlide.index() == -1) {
				// we're on the first slide; time to wrap around to the last
				//nextSlide = jqueryobj.children().last();// WORKS, but only if fadeouts are allowed
				jqueryobj.prepend( jqueryobj.children().last() ); // move the first element into the last position so it appears on top
				nextSlide = jqueryobj.children().first(); // set the next slide
			}
		}
		
		// 3: hide all slides that are not the next slide or the current slide
		var hideSlides = jqueryobj.children().not(currentSlide).not(nextSlide);
		if (style == 'fade') {hideSlides.hide();}
		if (style == 'slide') {hideSlides.css({left:'50%'}).hide();}
		
		// 4: if the next slide is above the current slide, fade IN the next slide.
		// if the next slide is below the current slide, fade OUT the current slide.
		//if (nextSlide.index() > currentSlide.index() ) {nextSlide.fadeIn(800);}
		if (style == 'fade') {nextSlide.fadeIn(1000);} else {nextSlide.show();}
		recenterLetterBoxed();
		recenterLetterBoxed('animated'); // the letterbox adjustments only take place if the slide is visible, so it must be applied now

		if (nextSlide.index() > currentSlide.index()) {
			// next slide is ABOVE current slide
			if (style == 'slide') {nextSlide.animate({left:'0%'},800); }// slide next slide in from right
			//else if (style == 'fade') {
			//	nextSlide.fadeIn(1000);
			//}
		} else {
			// next slide is BELOW the current slide
			if (style == 'slide') {
				nextSlide.css({left:'0%'});
				currentSlide.animate({left:'50%'},800); // slide current slide OUT to right (revealing nextslide below)
			} else if (style == 'fade') {
				nextSlide.show();
				currentSlide.fadeOut(1000); // slide current slide OUT to right (revealing nextslide below)
			}
		}
		
		// 5: get the number of the slide we've just animated into place:
		var sequenceNo = nextSlide.attr('id');

		// 6: update the "currentslide" class to reflect the newly advanced slideshow:
		jqueryobj.children('.currentSlide').removeClass('currentSlide');
		nextSlide.addClass('currentSlide');
		
		// 6B: Push google event tracking to mark this photo as viewed:
		_gaq.push(['_trackEvent', 'Photos', 'View', nextSlide.attr('id')]);
		
		// 7: setup timer for next slide:
		if ( jqueryobj.data('running') ) {
			var t = setTimeout( function() {advanceSlide(jqueryobj,'next',style,delay)}, delay );
			jqueryobj.data('timerID',t);
		}
		
		// 8: set the counter to the current slide number, as determined by the ID tag set during setup
		// (the original index values no longer apply, as the list is constantly being shuffled)
		var chopOff = (jqueryobj.attr('id') + '_slide_').length; // remove the "prefix" from the slide's ID to get its original sequence number
		$('#slide_count_current').html(parseInt(sequenceNo.substr(chopOff))+1); // add one so as never to say "slide zero"
	}
	
	///////////////////// Prepare the jquery for the pricing form:
	$('#submit').button();
	
	$('#submit').click(function () {
		//Get the data from all the fields
        var name = $('input[name=name]');
        var email = $('input[name=email]');
        var phone = $('input[name=phone]');
        var weddate = $('input[name=weddate]');
        var comments = $('textarea[name=comments]');
 
        //Simple validation to make sure user entered something
        //If error found, add hightlight class to the text field
        if (name.val()=='') {
            name.addClass('input-reminder');
            return false;
        } else name.removeClass('input-reminder');
         
        if (email.val()=='') {
            email.addClass('input-reminder');
            return false;
        } else email.removeClass('input-reminder');
        
		if (weddate.val()=='') {
            weddate.addClass('input-reminder');
            return false;
        } else weddate.removeClass('input-reminder');
         
        //organize the data properly
        var data = 'name=' + name.val() + '&email=' + email.val() + '&phone=' + phone.val() + '&weddate='
        + weddate.val() + '&comments='  + encodeURIComponent(comments.val());
		
		//start the ajax
        $.ajax({
            //this is the php file that processes the data and send mail
            url: "process.php", 
             
            //GET method is used
            type: "POST",
 
            //pass the data         
            data: data,     
             
            //Do not cache the page
            cache: false,
             
            //success
            success: function (html) {              
                //if process.php returned 1/true (send mail success)
                if (html != 0) { 
					$('#price-info').fadeOut(400, function() {
						// replace column contents with price PDFs:
						$('#price-info').html(html);
					} ).fadeIn(400);            
                //if process.php returned 0/false (send mail failed)
                } else alert('Sorry, we hit a snag. Please email Phil directly: he doesn\'t know about this!');               
            }       
        });
         
        //cancel the submit button default behaviours
        return false;
	})
});


</script>
 
<style>
/* splash animation styles */
#loadingbox {
	display:none;
	position: absolute;
	left: -150px;
	top: -100px;
	width: 300px;
	height: 200px;
	border-radius: 20px;
	background-color:#FFF;
	text-align:center;
}
#progressbar {
	display:none;
	position:absolute;
	width: 300px;
	height: 10px;
	left:-150px;
	top:-5px;
	border-radius:5px;
	z-index:10;
}
#gallery-nav-container {
	/* styling for the gallery status window that pops up during the main slideshow */
	overflow:visible;
	z-index:10;
	position:absolute;
	bottom:0px; /* offsreen to start */
	left:50%;
}
#gallery-navbar {
	/* styling for the gallery status window that pops up during the main slideshow */
	position:absolute;
	top:20px;
	left: -100px;
	width:200px;
	height:40px;
}
#gallery-navbar-visible {
	/* styling for the gallery status window that pops up during the main slideshow */
	border-radius:43px 43px 0px 0px;
	background-color: #00c870;
	width:100%;
	height:100%;
   	cursor:default !important;
   
	text-align:center;
	color:#FFF;
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	font-weight:bold;
	line-height:38px;
}
#arrow-right {
	position:absolute;
	top: 10px;
	right: 30px;
	width: 0;
	height: 0;
	border-top: 10px solid transparent;
	border-bottom: 10px solid transparent;
	border-left: 10px solid white;
	cursor:pointer;
}
#arrow-left {
	position:absolute;
	top: 10px;
	left: 30px;
	width: 0;
	height: 0;
	border-top: 10px solid transparent;
	border-bottom: 10px solid transparent;
	border-right: 10px solid white;
	cursor:pointer;
}
.unselectable {
   -moz-user-select: -moz-none;
   -khtml-user-select: none;
   -webkit-user-select: none;
   user-select: none;
}


.fullscreen_image {
	/* the real attributes of this class come from javascript because it's dependent 
	on the aspect ratio of the image file versus browser window */
	position:absolute;
	top:0;
	left:0;
}
.fullscreen_widthconstrained {
	width:100%;
	height:auto;
}
.fullscreen_heightconstrained {
	height: 100%;
	width:auto;
}
.letterboxed_image {
	max-width:100%;
	max-height:100%;
	position:absolute;
}
.alignRight {
	right:0px;	
}
.alignLeft {
	left:0px;	
}

/* Main menu styles */
#navbar-container {
	display:none;
	position:absolute;
	width:45px;
	height:400px;
	left:18px; /* exact pixel positioning to have a nice shaped button showing*/
	top:50%;
	margin-top:-190px; /* places menu tray just a few pixels high of centered on the left */
	overflow:visible;
	z-index:10; /* appears on top of all other content */
}
.navbar-bar-container {
	position:relative;
	top: 0;
	left: -50px;
	width:50px;
	height:45px;
	margin-bottom:30px;
	overflow:visible;
}
.navbar-bar {
	position:absolute;
	right:0px;
	height: 45px;
}
.navbar-visible-bar {
	float:right;
	margin-right:20px;
	height:45px;
	background-color: #0097C8;
	border-radius: 0px 45px 45px 0px;
	cursor:pointer;
	padding-left:18px; /* matches LEFT value of #navbar-container */
	
	text-align:left;
	color:#FFF;
	font-family:Arial, Helvetica, sans-serif;
	font-size:18px;
	font-weight:bold;
	line-height:42px;
	text-indent:10px;
}
.navbar-hover {
	background-color: #006383;	
}
.navbar-click {
	background-color: #00c870;	
}
.mainMenuIcon {
	position:absolute;
	right:27px;
	top:5px;
	width:35px;
	height:35px;
	overflow:hidden;
	cursor:pointer;
}
.mainMenuIcon img {
	position:absolute;
	left:-15px;	
}

/* Styles for the main window content*/
.hideable-content{
	position:absolute;
	top:0px;
	left:0px;
}
.mainwindow {
	position:absolute;
	top:0px;
	left:30px; /* just to give the sidebar nav menu some extra room */
	width:750px;
	height:575px;

	color: #555;
	font-family:Arial, Helvetica, sans-serif;
	font-size:11px;
	line-height:16px;
	
	display:none; /* all main content starts as hidden; shown on request using main menu */
}
.mainwindowBG {
	width:100%;
	height:100%;
	background-color:#F6F6F6;
	opacity:0.75;
	border-radius:15px;	
}
.mainwindow img {
	position:absolute;
	top:0px;
	left:0px;
	border-radius: inherit;
}
.mainwindow a {
	color: #000;	
	font-weight: bold;
}
input {
	padding: 0px;
	margin: 0px;
}
.input-reminder {
	background-color: #FF6;	
}
textarea {
	width: 280px;
	height: 50px;
	border: 1px solid;
	font-family: Tahoma, sans-serif;
	resize: none;
}
.highlight {
	font-weight:bold;
	color: #333;
}
.ital {
	font-style:italic;	
}
.contentCol {
	position: absolute;
	width: 300px;
	height: 495px;
	overflow: hidden;
	top: 40px;
}
.contentCol img {
	border-radius: 15px;	
}
.leftCol {
	left: 40px;
}
.rightCol {
	left: 410px;
}
.ravequote {
	position:absolute;
	width: 600px;
	padding:10px;
	font-family:Arial, Helvetica, sans-serif;
	font-size:10px;
	font-weight:bold;
	line-height:14px;
	color:#333;
	text-align:left;
	border-radius: 15px;
	cursor:pointer;
	
	/* Fallback for web browsers that doesn't support RGBa */
	background: rgb(255, 255, 255) transparent;
	/* RGBa with 0.6 opacity */
	background: rgba(255, 255, 255, 0.8);
	/* For IE 5.5 - 7*/
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#FFFFFFFF, endColorstr=#FFFFFFFF);
	/* For IE 8*/
	-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#FFFFFFFF, endColorstr=#FFFFFFFF)";
}
.ravequote .high {
	font-size:18px;
	line-height: 22px;	
}
.ravequote h5 {
	font-size:10px;
	line-height:14px;
	font-weight:normal;
}
.cycle-slide {
	/* styles for the slides inside the gallery and raves slideshows */	
	position:absolute;
	top:0px;
	left:0px;
	width:50%;
	height:100%;
	display:none;
	overflow:hidden;
}
.container {
	position: absolute;
	left: 50%;
	top: 50%;	
}
.centeredDiv {
	position:absolute;
	top: -288px;
	left: -375px; /* to center, this should be (navwindow:width + mainwindow:width ) / 2 */
}

/* Make sure the datepicker comes up at the right size -- it's based on the size of its parent element if left alone */
div.ui-datepicker{
 font-size:11px;
}

</style>

</head>

<body style="background-color:#EFEFEF;overflow:hidden;">

<!-- START SPLASH CONTENTS HERE -->
<div id="load-container" style="position:absolute;left:50%;top:50%;">
    <div id="loadingbox">
        <div id="inner" style="display:none;">
            <img src="images2012/logo.png" id="logoImage" style="margin-top:45px;" />
        </div>
    </div>
    
    <div id="progressbar"></div>
</div>
<!-- END SPLASH CONTENTS HERE -->

<img id="firstload" class="fullscreen_image" src="<?=resize('images2012/mainscreenBG.jpg',$settings)?>" style="display:none" />

<!-- START NAVBAR CONTENTS HERE -->
<div id="navbar-container" >
	<div class="navbar-bar-container" ><div class="navbar-bar" id="home_button">
    	<div class="mainMenuIcon"><img src="images2012/main-icons.png" style="top:-20px"/></div>
        <div class="navbar-visible-bar" style="width:120px">
        home
        </div>
    </div></div>
    
	<div class="navbar-bar-container" ><div class="navbar-bar"  id="galleries_button" >
        <div class="mainMenuIcon"><img src="images2012/main-icons.png" style="top:-97px"/></div>
        <div class="navbar-visible-bar"  style="width:150px">
        galleries
        </div>
    </div></div>
    
	<div class="navbar-bar-container" ><div class="navbar-bar" id="pricing_button" >
        <div class="mainMenuIcon"><img src="images2012/main-icons.png" style="top:-175px"/></div>
        <div class="navbar-visible-bar"  style="width:130px">
        pricing
        </div>
    </div></div>
    
	<div class="navbar-bar-container" ><div class="navbar-bar"  id="aboutphil_button" >
        <div class="mainMenuIcon"><img src="images2012/main-icons.png" style="top:-252px"/></div>
        <div class="navbar-visible-bar"  style="width:165px">
        about phil
        </div>
    </div></div>
    
	<div class="navbar-bar-container" ><div class="navbar-bar"  id="raves_button" >
        <div class="mainMenuIcon"><img src="images2012/main-icons.png" style="top:-329px"/></div>
        <div class="navbar-visible-bar"  style="width:130px">
        raves
        </div>
    </div></div>
</div>
<!-- END NAVBAR CONTENTS HERE -->

<!-- SOME OF THE SITE'S CONTENT IS CENTERED -- THAT CONTENT ALL APEARS INSIDE A 'CENTERED' WRAPPER -->
<div class="container">
<div class="centeredDiv">

    <div class="mainwindow hideable-content" id="aboutphil_button_content">
        <div class="mainwindowBG"></div>
        <div class="contentCol leftCol">
        <img src="images2012/about-phil.jpg" width="300" height="495" />
        </div>
        
        <div class="contentCol rightCol">
            I have two priorities: <span class="highlight">get great photos, and keep it fun.</span>
            
            <p>And really, it's only one priority. If you're having fun your photos will look great, and if your photos look great it's because you were having fun. That's why I take a candid approach to wedding photography; for almost the whole day you won't hear much from me. I'm just watching and carefully documenting what I see. If you're having fun, that's what you'll see too. Even portraits can be lively; I put away all the heavy lights and backdrops in an effort to make the experience of pictures more dynamic. Sometimes the people watching the portraits being taken make for great photos, too!
            
            <p>If I can keep it fun and fresh, your photos will be great. Each time I add one new thing to the mix. It ensures that you get the benefits of experience, but without the rust.
            
            <p>I've been taking photos since I was very young, but my interests are diverse! Ask me about architecture, woodworking, web design, italian, and energy. Or <a href="http://www.phil-seaton.com" style="text-decoration:none" target="_blank">take a sneak peak</a>, if you like.
            
            <p>Thanks for your interest in Living Photography! I'm based in San Francisco, but available all over. If you like what you see, give me a shout!
            
            <p class="highlight">+1 (877) 513-6410
            
            <br />phil@livingphoto.net
        </div>
	</div><!-- END aboutphil window -->  

    <div class="mainwindow hideable-content" id="pricing_button_content">
    	<div class="mainwindowBG"></div>
        <div class="contentCol leftCol">
        Living Photography is about capturing 
        moments as they actually occur. The aesthetic is contemporary: 
        sometimes black and white, sometimes color, but always natural. 
        Phil Seaton's ten years working as a wedding photographer have 
        taught him how to find those cherished moments that make your 
        wedding <span class="ital">yours</span>. Even his portraits are intended to have 
        a natural feel: they are photographs of people interacting, 
        not people posing.
        <p><span class="highlight">Complete pricing information is just a click away. </span></p>
        
        <p><span class="highlight">Coverage is
        always time-unlimited. Let me worry about your schedule; you should never have
        to worry about mine.</span></p>
        <p><span class="highlight">Starting at $2599</span></p>
        </div>
        
        <div class="contentCol rightCol" id="price-info">
        <span class="highlight">Phil is no longer accepting new clients. </span><br><br>  
        Check out his latest endeavors at strandedcity.com and phil-seaton.com <br>
         <br>
        <!--<form action="cgi-bin/2008/price_request.pl" method="post" name="price_request" target="submitinfo">
        <br />
        <input name="name" type="text" class="textfields" id="name2">
        <br>
        your name<br>
        <br>
        <input name="phone" type="text" class="textfields" id="phone">
        <br>
        phone number<br>
        <br>
        <input name="email" type="text" class="textfields" id="email" size="35">
        <br>
        email<br>
        <br>
        <input name="weddate" type="text" class="textfields" id="weddate" size="20">
        <br>
        wedding date<br>
         <br>
        <textarea name="comments" type="text" class="textfields" id="comments" size="20"></textarea>
        <br>
        comments<br>
        
        <p><br>
        <input type="submit" id="submit" value="view pricing">
        </form> -->
        </div>
    </div> <!-- end "contact" window-->


    <div class="mainwindow hideable-content" id="home_button_content">
        <div class="contentCol rightCol" style="margin-top:100px;">
            <div style="position:absolute;background-color:#FFF;opacity:0.75;width:300px;height:300px;border-radius:15px;overflow:hidden;"><div class="mainwindowBG"></div></div>
            <div style="cursor:pointer;position:absolute;width:260px;height:260px;margin:20px;text-align:center;" id="showspecialdetails"><br />
            <span style="display:inline;font-size:40px;line-height:46px;font-weight:bold;text-align:center">welcome!<br /> <span style="font-size:14px;line-height:16px;font-weight:normal;text-align:center"><br /><br />please look around and enjoy, but Phil is not currently accepting new clients<br /></span><span style="font-size:14px;line-height:16px;font-weight:normal;text-align:center"><br /><br /><br /><span style="font-size:9px"></span></span></span>
            </div>
        </div>  
    </div> <!-- end "special" window-->

</div></div> <!-- End container and centeredDIV -->

<!-- BELOW THIS POINT ARE THE FULLSCREEN CONTENT PARTS -- RAVE REVIEWS & GALLERIES -->

<div class="hideable-content" id="raves_button_content" style="display:none;width:100%;height:100%;overflow:hidden;">
    <div id="raves-show" class="show" style="position:absolute;width:200%;height:100%;">
    
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/Zessin_376.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="top:80px;left:120px;width:300px;"><span class="high">I. Am. Speechless.</span> <br /><br />You have managed to turn me into a total narcissist by giving us such lovely pictures.<h5>Claire & Eavan | Kingston, RI</h5></div>
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/Days_103.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="bottom:50px;right:50px;width:200px;">Thank you so much for photographing our wedding! We had a perfect day and have so many great pictures to remember it by. It's been hard to choose our favorites... <br /><br /><span class="high">we love them all!</span><h5>Lori & Brian | Baltimore, MD</h5></div>
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/petrillo_015.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="top:50px;right:50px;width:250px">James and I both absolutely LOVE these pictures! Wow, you are such a talented photographer. Your images are really just gorgeous, and captured the events and everyone´s emotions in such a stylish and authentic way. We couldn´t be more pleased with how they turned out.<h5>Adria & James | Seattle, WA</h5></div>
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/Kovach_100.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="top:50px;left:80px;width:200px;"><span class="high">The pictures are amazing!</span> <br /><br />Thank you so much, I love all of them, and again i've gotten so many compliments about them and you.... Again, thank you so much for making such amazing photos for me!<h5>Susan & Chris | Staten Island, NY</h5></div>       
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/Georgacopoulos_022.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="bottom:50px;left:80px;width:300px;"><span class="high">We just returned this week from the honeymoon and we are blown away by the pictures. </span><br /><br />They are absolutely beautiful and we are both so pleased with how they turned out. Thank you!<h5>Monica & Darius | Wheeling, WV</h5></div>       
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/ColeKelly_411.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="bottom:50px;right:50px;width:200px;"><span class="high">Wow is all I can say!</span><br /><br />I wanted to write you a nice handwritten thank you note, but I couldn't wait to tell you how amazing these photographs are.  Doug and I are very impressed by your talent in photography.  I cannot thank you enough for these beautiful photographs, you captured every special moment of the day/night perfectly.<h5>Lindsay & Doug | Nahant, MA</h5></div>       
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/Wagner_080.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="bottom:80px;left:80px;width:300px;"><span class="high">They are AMAZING. I almost cried!</span><h5>Ale & Horacio | Cambridge, MA</h5></div>       
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/Zessin_001.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="bottom:50px;right:50px;width:200px;">Thank you so much- these look amazing! <br /><br /> <span class="high">I have cleared my calendar for the morning so I can click through over and over and over</span><h5>Colleen & Chris | Boston, MA</h5></div>       
        </div>
        
        <div class="cycle-slide">
            <img src="<?=resize('images2012/rave-images/ColeKelly_321.jpg',$settings)?>" style="cursor:pointer;" class="fullscreen_image"/>
            <div class="ravequote" style="bottom:80px;left:80px;width:250px;">The photos look AMAZING!!!! <br /><br /><span class="high">I've looked through them about 5 times since I got your email yesterday morning and can't believe how many there are that I love. </span><h5>Corynne & Paul | Cape Cod, MA</h5></div>    
        </div>
        
        
       
        
    </div>
</div><!-- END RAVES CONTENT! -->

<div class="hideable-content" id="galleries_button_content" style="display:none;width:100%;height:100%;overflow:hidden;">
    <div id="galleries-show" class="show" style="position:absolute;width:200%;height:100%;">
        
<?php
# The following code reads through the directory of available gallery images.
# For each image, it parses certain display data from the filename (see below) and then generates
# the appropriate HTML to display it.

$galleryImageHTML = <<<EOF
<div class="cycle-slide" style="background-color:#{BGCOLOR}">
	<img src="{FILENAME}" style="cursor:pointer;" class="{DISPLAYCLASS} {ALIGNMENT}"/>
</div>
EOF;

$dir = "images2012/gallery-images";
$filearr = scandir($dir);

foreach ($filearr as $filename) {
	if ($filename != '.' && $filename != '..' ) {
		# define variables to store collected info:
		$bgcolor = "333333"; # unless specified, the background color will be a dark grey
		$alignment = ""; # unless specified, alignment will be empty, resulting in a centered image
		$displayclass = "letterboxed_image"; # unless specified fullscreen, images will be letterboxed
		
		# perform filename parsing
		if ( preg_match('/_fullscreen_/i', $filename) != 0 ) {
			$displayclass = "fullscreen_image";
			$alignment = "";
		}
		
		# align left or right if told to. Otherwise alignment will remain empty, and the image will be centered
		if ( preg_match('/_alignleft_/i', $filename) != 0 ) {
			$alignment = 'alignLeft';
		} elseif ( preg_match('/_alignright_/', $filename ) != 0 ) {
			$alignment = 'alignRight';
		}
		
		# Look for a background/canvas color in the filename. If found, set it accordingly.
		preg_match('/_BG([\da-fA-F]+)_/i', $filename, $matches);
		if ( $matches[1] ) {
			$bgcolor = $matches[1];
		}
		
		# start with a blank version of the HTML code:
		$finalCode = $galleryImageHTML;
		
		# replace placeholders in HTML
		$imgsrc = resize('images2012/gallery-images/'.$filename,$settings);
		$finalCode = str_replace('{FILENAME}', $imgsrc, $finalCode );
		$finalCode = str_replace("{BGCOLOR}", $bgcolor, $finalCode );
		$finalCode = str_replace("{ALIGNMENT}", $alignment, $finalCode );
		$finalCode = str_replace("{DISPLAYCLASS}", $displayclass, $finalCode );
		
		# echo contents of HTML string:
		echo $finalCode;
	}
}
?>        
        
    </div>
</div><!-- END GALLERIES CONTENT! -->


<div id="gallery-nav-container">
<div id="gallery-navbar">
<div id="gallery-navbar-visible">
<div id="arrow-right"></div><div id="arrow-left"></div><div id="pauseButton" style="display:inline;margin-right:15px;cursor:pointer;">pause</div><span id="slide_count_current">1</span> of <span id="slide_count_total"></span>
</div>
</div>
</div>


</body>
</html>
