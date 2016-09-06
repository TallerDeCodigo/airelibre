(function($){

	function docReady(){

	"use strict";

	$(function(){


		console.log('hello from functions.js');

			$(window).load(function(){
				$(function() {

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
						$(".submenu").show();
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

		var newHash = '';

		$(document).on('click', '.inlink', function(e) {

		 	e.preventDefault();
		 	var newHash = $(this).attr('href');
		 	console.log(newHash);
		 	$('#content').empty();
		 	$('#content').addClass('contenido_hash');
 	 	    $('#content').load(newHash+' #content', function() {
 				var $grid = $('.grid').isotope();
 				$grid.isotope('layout');
 			});

		 	var myNewState = {
		     data: {
		 	        a: 1,
		 	        b: 2
		 	    },
		 	    title: '',
		 	    url: newHash
		 	};
		 	history.pushState(myNewState.data, myNewState.title, myNewState.url);
		 	window.onpopstate = function(event){
		 	    console.log(myNewState.url); // previous 
		 	    console.log(window.location.href); // actual
		 	    var newHash = window.location.href;
		 	    $('#content').empty();
		 	    $('#content').load(newHash+' #content', function() {
					var $grid = $('.grid').isotope();
					$grid.isotope('layout');
				});
		 	}

		});

		/* SUBMENU SIEMPRE */

		$(".openmenu").click(function(){
            $(".openmenu").toggleClass('open');
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

        $(".autorin").click(function(){
		    $(".dropdown").removeClass('active');
		    $(".openmenu").removeClass('open');
		    if ($('.mob-menu').css('display') == 'none') {
		    	$(".submenu").hide();
		    } else {
		    	$(".mob-menu").animate({opacity:"0"}, 300);
		    	setTimeout(function() {
		    		$(".mob-menu").toggle();
		    		$("input[name=search2]").css("width", '0%');
		    		$(".submenu").hide();
		    	}, 310);
		    }
        });

		$(".dropdown").click(function(){
			$(".submenu").toggle();
			$(".dropdown").toggleClass('active');
		});

		$(document).on('click',function(e){
		   if ( $(e.target).closest('.dropdown').length === 0 && $(e.target).closest('.submenu').length === 0 && $(e.target).closest('.separator').length === 0 ) {
		      	$(".submenu").hide();
		      	$(".dropdown").removeClass('active');
		   }
		});


		/* DON'T CHANGE THE PLAY ICON */


		// $(window).unload(function(){
		//   localStorage.setItem('alibre_playing','0');
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

	//console.log(random_list);

	//$('.pause').hide();


	var audioElement = document.createElement('audio');
        audioElement.setAttribute('src', 'http://airelibre.devtdc.online/wp-content/uploads/radio/'+random_list+'.mp3');
        console.log(audioElement);
    var generalTimer = null;
    var plPointer = 0;

    var whichsounds = 'rd';
    var started = 0;
    var eltimer0 = new Date();
    var eltimer = new Date();

        //audioElement.setAttribute('autoplay', 'autoplay');
        //audioElement.load()

        $.get();

        // audioElement.addEventListener("load", function() {
        //     audioElement.play();
        // }, true);
       
        function setGeneralTimer(myTimer){

  			var timerArray = myTimer.split(":");
  			var minutes = (timerArray[0]*60)*1000;
  			var seconds = timerArray[1]*1000;
  			generalTimer = minutes+seconds;
  			plPointer++;
  			if (plPointer>1) {
  				console.log('Rola '+(plPointer-1));
  			}
        }

        function mySetTimeout(){
      		
      		setTimeout( function(){
      			var context = radio_pl.meta[plPointer-1];
	      		$('.showname').empty().text(context.title);
	        	$('.album').attr('src', context.cover);
	        	$('.breadcrumbs').empty().text(context.artist);
	        	var myTimer = radio_pl.meta[plPointer].start;
	        	setGeneralTimer(myTimer);
	        	mySetTimeout();
          	}, generalTimer);
        }

        $('.controller_radio').on('click', function(){

        	if ($(this).hasClass('play')) {

	          	$(this).addClass('pause');
	          	$(this).removeClass('play');
	          	$(this).attr('src', 'http://airelibre.devtdc.online/wp-content/themes/airelibre/images/pause.svg');
          		if (whichsounds=="rd" && started==0) {
          			eltimer0 = new Date();
          			eltimer0 = eltimer0.getTime();
          			console.log('Empieza');
          			mySetTimeout();
          			setGeneralTimer(radio_pl.meta[0].start);
          			started=1;
          		} else if (whichsounds=="rd" && started==1){
					eltimer = new Date();
          			eltimer = eltimer.getTime();
          			eltimer = eltimer - eltimer0;
          			eltimer = Math.round(eltimer/1000);
          			console.log('Reanuda a los: '+eltimer+'s');
          			audioElement.currentTime = eltimer;
          		}
        		audioElement.play();

	        } else {

	          	audioElement.pause();
	            $(this).addClass('play');
	          	$(this).removeClass('pause');
	          	$(this).attr('src', 'http://airelibre.devtdc.online/wp-content/themes/airelibre/images/play.svg');

	        }

        });

        $(document).on('click', '.play_podcast', function() {

        	var new_audio = $(this).data('audio');
        	var portada = $(this).data('portada');
        	var titulo = $(this).data('titulo');
        	var programa = $(this).data('programa');
        	
        	audioElement.setAttribute('src', new_audio);
        	audioElement.play();

        	$('.podc-ch').addClass('circle').addClass('blue');
        	$('.live-ch').removeClass('circle').removeClass('roja');
        	whichsounds = "pc";
        	
        	$('.controller_radio').removeClass('play');
        	$('.controller_radio').removeClass('pause');
        	$('.controller_radio').addClass('pause');
        	$('.controller_radio').attr('src', 'http://airelibre.devtdc.online/wp-content/themes/airelibre/images/pause.svg');

        	$('.showname').empty().text(titulo);
        	$('.breadcrumbs').empty().text(programa);
        	$('.album').attr('src', portada);
        	console.log(portada);

        });

        /* BACK TO RADIO */

        $(".live-ch").click(function(){
        	if (whichsounds=="pc") {
        		$('.podc-ch').removeClass('circle').removeClass('blue');
        		$('.live-ch').addClass('circle').addClass('roja');
        		whichsounds = 'rd';
        		audioElement.pause();
        		audioElement.setAttribute('src', 'http://airelibre.devtdc.online/wp-content/uploads/radio/'+random_list+'.mp3');
        		$('.controller_radio').addClass('play');
	          	$('.controller_radio').removeClass('pause');
	          	$('.controller_radio').attr('src', 'http://airelibre.devtdc.online/wp-content/themes/airelibre/images/play.svg');
	          	
    		  	if (started==0) {
		  			$('.showname').empty().text(radio_pl.meta[0].title);
		  		  	$('.album').attr('src', radio_pl.meta[0].cover);
		  		  	$('.breadcrumbs').empty().text(radio_pl.meta[0].artist);
		  		} else {
		  		  	var rola = plPointer-1;
	    		  	console.log('Rola '+rola);
	  		  		$('.showname').empty().text(radio_pl.meta[rola-1].title);
	  		  	  	$('.album').attr('src', radio_pl.meta[rola-1].cover);
	  		  	  	$('.breadcrumbs').empty().text(radio_pl.meta[rola-1].artist);
		  		}
    			
        	}
        });

        audioElement.addEventListener("ended", function(){
		     audioElement.currentTime = 0;
		     console.log("ended");
		});


     /// PODCASTS PLAYLIST ///////

	    $(document).on('click', '.pl-item', function() {

	    		$('.pl-item').removeClass('selected');
	        	$(this).addClass('selected');

        });

	}

	docReady();
	
})(jQuery);