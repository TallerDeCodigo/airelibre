(function($){

	function docReady(){

	"use strict";

	$(function(){


		console.log('hello from functions.js');

			$(window).load(function(){
				$(function() {

					$(".dropdown").click(function(){
						$(".submenu").toggle();
						$(".dropdown").toggleClass('active');
					});

					$("#nav-icon3").click(function(){
			            $("#nav-icon3").toggleClass('open');
			            if ($('.mob-menu').css('display') == 'none') {
			            	$(".mob-menu").toggle();
			            	$(".mob-menu").animate({opacity:"1"}, 300);
			            } else {
			            	$(".mob-menu").animate({opacity:"0"}, 300);
			            	setTimeout(function() {
			            		$(".mob-menu").toggle();
			            		$("input[name=search2]").css("width", '0%');
			            	}, 310);
			            }
			        });

			        $("#search1").click(function(){
			            $("input[name=search1]").show();
			            $("input[name=search1]").focus();
			        });

			        $("#search2").click(function(){
			            $("input[name=search2]").animate({width:'91.5%'}, 300);
			            $("input[name=search2]").focus();
			        });

			        $(".separator").click(function(){
			        	$(".submenu").css("opacity", '1');
			        	$(".submenu").css("width", '0%');
						$(".submenu").toggle();
						$(".submenu").animate({width:'100%'}, 400);
					});

					$(".arrow-sub img").click(function(){
						$(".submenu").animate({opacity:'0'}, 400);
						setTimeout(function() {
							$(".submenu").toggle();
						}, 410);
					});

				});

			});

			$(window).on("load resize",function(){
				var ancho = document.documentElement.clientWidth;
				if (ancho>999) {
					$(".player").addClass("transe1");
					$(".submenu").addClass("transe2");
				}
				    
			});

		/*** NAVEGACIÓN */

		// var newHash = '';

		// $('.inlink').on('click', function(e){
		// 	e.preventDefault();
		// 	var newHash = $(this).attr('href');
		// 	console.log(newHash);
		// 	$('#content').empty();
		// 	$('#content').load(newHash+ '#content');

		// 	var myNewState = {
		//     data: {
		// 	        a: 1,
		// 	        b: 2
		// 	    },
		// 	    title: '',
		// 	    url: newHash
		// 	};
		// 	history.pushState(myNewState.data, myNewState.title, myNewState.url);
		// 	window.onpopstate = function(event){
		// 	    console.log(myNewState.url); // will be our state data, so myNewState.data
		// 	}

		// });


		/**
		 * Validación de emails
		 */
		window.validateEmail = function (email) {
			var regExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return regExp.test(email);
		};



		/**
		 * Regresa todos los valores de un formulario como un associative array 
		 */
		window.getFormData = function (selector) {
			var result = [],
				data   = $(selector).serializeArray();

			$.map(data, function (attr) {
				result[attr.name] = attr.value;
			});
			return result;
		}


	});



	var $grid = $('.grid').isotope({
	  itemSelector: '.grid-item',
	  percentPosition: true,
	  masonry: {
	    columnWidth: '.grid-sizer'
	  }
	});

	$grid.imagesLoaded().progress( function() {
	  $grid.isotope('layout');
	});

	$('.nav-item').on( 'click', function() {
	  var filterValue = $(this).attr('data-filter');
	  $grid.isotope({ filter: filterValue });
	});



	$('.desktop').transe({
	    0: {
	        height: '90px'
	    },
	    40: {
	        height: '45px'
	    }
	});

	$('.desktop .wrapper').transe({
	    0: {
	        height: '90px'
	    },
	    40: {
	        height: '45px'
	    }
	});

	$('.social .nav-item').transe({
	    0: {
	        opacity: '1'
	    },
	    30: {
	        opacity: '0'
	    }
	});

	$('.menu').transe({
	    0: {
	        height: '90px'
	    },
	    40: {
	        height: '45px'
	    }
	});

	$('.social').transe({
	    30: {
	        top: '0px'
	    },
	    40: {
	        top: '-45px'
	    }
	});

	$('.botones').transe({
	    0: {
	        top: '0px'
	    },
	    40: {
	        top: '-45px'
	    }
	});

	var width = $(window).width();
	if(width > 999){

		$('.player').transe({
		    0: {
		        top: '90px'
		    },
		    40: {
		        top: '45px'
		    }
		});
	}

	$('.submenu').transe({
	    0: {
	        top: '90px'
	    },
	    40: {
	        top: '45px'
	    }
	});

	$('.logogif').transe({
	    0: {
	        width: '96%',
	        opacity: '1'
	    },
	    40: {
	        width: '44%',
	        opacity: '0'
	    }
	});

	$('.logosvg').transe({
	    40: {
	        opacity: '0'
	    },
	    60: {
	        opacity: '1'
	    }
	});

	/// FITVIDS ///////

	$(".wrapper-666").fitVids();

	/// RADIO ////////////////////

	//$('.pause').hide();

	var audioElement = document.createElement('audio');
        audioElement.setAttribute('src', 'http://airelibre.devtdc.online/wp-content/uploads/radio/25.mp3');
        //audioElement.setAttribute('autoplay', 'autoplay');
        //audioElement.load()

        $.get();

        // audioElement.addEventListener("load", function() {
        //     audioElement.play();
        // }, true);

        $('.controller_radio').on('click', function(){

        	if($(this).hasClass('play')){
        		audioElement.play();
	          	$(this).addClass('pause');
	          	$(this).removeClass('play');
	          	$(this).attr('src', 'http://airelibre.devtdc.online/wp-content/themes/airelibre/images/pause.svg');
	          } else {
	          	 audioElement.pause();
	            $(this).addClass('play');
	          	$(this).removeClass('pause');
	          	$(this).attr('src', 'http://airelibre.devtdc.online/wp-content/themes/airelibre/images/play.svg');
	          }

        });


        $('.play_podcast').on('click', function(){

        	var new_audio = $(this).data('audio');

        	audioElement.setAttribute('src', new_audio);
        	audioElement.play();
        	$('.controller_radio').removeClass('play');
        	$('.controller_radio').removeClass('pause');
        	$('.controller_radio').addClass('pause');

        });

        audioElement.addEventListener("ended", function(){
		     audioElement.currentTime = 0;
		     console.log("ended");
		});


     /// PODCASTS PLAYLIST ///////

	    $('.pl-item').on('click', function(){

	    		$('.pl-item').removeClass('selected');
	        	$(this).addClass('selected');

        });

	}

	docReady();
	
})(jQuery);

