(function($){

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

	$('.player').transe({
	    0: {
	        top: '90px'
	    },
	    40: {
	        top: '45px'
	    }
	});

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

	/// RADIO ////////////////////

	$('.pause').hide();

	var audioElement = document.createElement('audio');
        audioElement.setAttribute('src', 'http://airelibre.devtdc.online/wp-content/uploads/radio/1.mp3');
        //audioElement.setAttribute('autoplay', 'autoplay');
        //audioElement.load()

        $.get();

        // audioElement.addEventListener("load", function() {
        //     audioElement.play();
        // }, true);

        $('.play').on('click', function() {
            audioElement.play();
            $(this).hide();
            $('.pause').show();
        
        });

        $('.pause').on('click', function() {
            audioElement.pause();
            $(this).hide();
            $('.play').show();
        });

        $('.play_podcast').on('click', function(){

        	var new_audio = $(this).data('audio');

        	audioElement.setAttribute('src', new_audio);
        	audioElement.play();

        });


     /// PODCASTS PLAYLIST ///////

    $('.pl-item').on('click', function(){

    		$('.pl-item').removeClass('selected');
        	$(this).addClass('selected');

        });


})(jQuery);

// jQuery(document).ready(function($) {
//     var $mainContent = $("#container"),
//         siteUrl = "http://" + top.location.host.toString(),
//         url = ''; 

//     $(document).delegate("a[href^='"+siteUrl+"']:not([href*=/wp-admin/]):not([href*=/wp-login.php]):not([href$=/feed/])", "click", function() {
//         location.hash = this.pathname;
//         return false;
//     }); 

//     $("#searchform").submit(function(e) {
//         location.hash = '?s=' + $("#s").val();
//         e.preventDefault();
//     }); 

//     $(window).bind('hashchange', function(){
//         url = window.location.hash.substring(1); 

//         if (!url) {
//             return;
//         } 

//         url = url + " #content"; 

//         $mainContent.animate({opacity: "0.1"}).html('&lt;p&gt;Please wait...&lt;/&gt;').load(url, function() {
//             $mainContent.animate({opacity: "1"});
//         });
//     });

//     $(window).trigger('hashchange');
// });
