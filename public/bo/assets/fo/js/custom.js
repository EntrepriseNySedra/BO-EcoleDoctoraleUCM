$(document).ready(function() {	
	$( window ).scroll( function () {			
			if ( $( this ).scrollTop() > 500 ) {
				$( '.top').addClass('scroll-in');
			} else {
				$( '.top' ).removeClass('scroll-in');
			}
		});
	// MENU MOBILE //
	$(".wrapMenuMobile").click(function() {
		$(this).toggleClass('active');
		$(".menuMobile").toggleClass('active');
		$(".menu ul").slideToggle();
		$(".sub").css('display','none');
		$(".close-menu").css('display','block');
		$(".menu li i").removeClass('active');
	});	
	$(".close-menu").click(function(e) {
		e.preventDefault();
		$(".menuMobile").toggleClass('active');
		$(".menu ul").slideToggle();
		$(".sub").css('display','none');
		$(".close-menu").slideToggle();
		$(".menu li i").removeClass('active');
	});
	$(".menu li i").click(function() {
		$(this).toggleClass('active');
		$(".sub").slideToggle();
	});	
	
	// SCROLL Menu //
	jQuery(document).on('click', '.menu li a', function(event){
        var targetSection = jQuery(this).attr('href');
        var scrTo = jQuery(targetSection).offset().top;
        jQuery('html, body').animate({
            scrollTop: scrTo
        }, 1000)
		
		$(".menuMobile").toggleClass('active');
		$(".menu ul").slideToggle();
		$(".sub").css('display','none');
		$(".close-menu").slideToggle();
		$(".menu li i").removeClass('active');
        event.preventDefault();
		
    });
	
	
	// SLICK //
	$('.list-temoignage').slick({
		slidesToShow: 2,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 5000,
		pauseOnHover: false,
		draggable: false,
		fade: false,
		dots:false,
		arrows: true,
		adaptiveHeight: false,
		responsive: [
			{
				breakpoint:500,
				settings: {
					slidesToShow: 1,
					fade: false,
					draggable: true,
					arrows: true,
				}
			}
		]
  	});
	
	var form = $("#form_step");
	form.validate({
		errorPlacement: function errorPlacement(error, element) { element.before(error); },		
	});
	form.children("div").steps({
		headerTag: "h3",
		bodyTag: "section",
		transitionEffect: "slideLeft",
		labels: {
			next: "Suivant",
			previous: "Precedent",
			finish: "soumettre votre demande",
		},
		onStepChanged: function (event, currentIndex, newIndex)
		{
            $('.wizard > .actions').css('margin-top', '-78px');

			console.log(currentIndex, newIndex);
			if(currentIndex == 0){
				$('.titre-step').show();
				$('.show-step').hide();
				$('.map-responsive').show();
			}
			if(currentIndex == 1){
				$('.titre-step').hide();
				$('.show-step').hide();
				$('.show-step2').show();
			}
			if(currentIndex == 2){
				$('.show-step').hide();
				$('.show-step2').hide();
				$('.show-step3').show();

                var $valEstime = parseFloat($('#fi-max').html()),
                    $montant = parseFloat($('#montant').val());
                var $creditDispo = $valEstime - $montant;
                $('#credit-dispo').html(numberFormat($creditDispo, 0, '', ' ') + ' €');
			}
			if(currentIndex == 3){
				$('.wizard > .content').addClass('step-4');
				$('.titre-step-4').show();
				$('.show-step').hide();
				$('.show-step2').hide();
				$('.show-step3').hide();
				$('.show-step4').show();

				$('.wizard > .actions').css('margin-top', '0');
			}		
		},
		onStepChanging: function (event, currentIndex, newIndex)
		{
			form.validate().settings.ignore = ":disabled,:hidden";
			return form.valid();
		},
		onFinishing: function (event, currentIndex)
		{
			form.validate().settings.ignore = ":disabled";
			return form.valid();
		},
		onInit: function (event, currentIndex) { 
		},
		onFinished: function (event, currentIndex)
		{
		    if ($('#statut').val() != 'Propriétaire') {
		        alert('Simulation possible uniquement en tant que proprietaire');
            } else {
		        $("form#form_step").submit();
            }
		}                                                                        
	});
	var formInvest = $("#form_step_investisseur");
	formInvest.validate({
		errorPlacement: function errorPlacement(error, element) { element.before(error); },		
	});
	formInvest.children("div").steps({
		headerTag: "h3",
		bodyTag: "section",
		transitionEffect: "slideLeft",
		labels: {
			next: "Suivant",
			previous: "Precedent",
			finish: "SOUMETTRE",
		},
		onStepChanged: function (event, currentIndex, newIndex)
		{
            $('.wizard > .actions').css('margin-top', '-78px');
            
			console.log(currentIndex, newIndex);
			if(currentIndex == 0){
				$('.titre-step').show();
				$('.show-step').hide();
				$('.show-step1').show();
			}
			if(currentIndex == 1){
				$('.titre-step').hide();
				$('.show-step').hide();
				$('.show-step2').show();
				$('.wizard > .content').addClass('step-2');
			}
			if(currentIndex == 2){
				$('.show-step').hide();
				$('.show-step2').hide();
				$('.show-step3').show();
				$('.wizard > .content').removeClass('step-2');
				$('.wizard > .content').addClass('step-3');
			}
			if(currentIndex == 3){
				$('.show-step').hide();
				$('.show-step2').hide();
				$('.show-step3').hide();
				$('.show-step4').show();
				$('.wizard > .content').removeClass('step-3');
				$('.wizard > .content').addClass('step-4');

                $('.wizard > .actions').css('margin-top', '0');

			}		
		},
		onStepChanging: function (event, currentIndex, newIndex)
		{
			formInvest.validate().settings.ignore = ":disabled,:hidden";
			return formInvest.valid();
		},
		onFinishing: function (event, currentIndex)
		{
			formInvest.validate().settings.ignore = ":disabled";
			return formInvest.valid();
		},
		onInit: function (event, currentIndex) { 
		},
		onFinished: function (event, currentIndex)
		{
            $("form#form_step_investisseur").submit();
		}
	});
	
	$('.selectBox').SumoSelect();
	

	$(".icon-infos").click(function() {
		$(this).toggleClass('active');
		$(this).parent(".item-cm, .cnt-champ, .p-invest").find(".cnt-infos").slideToggle();
	});
	
	/*$("#slider_finance").ionRangeSlider({
		min: 30000,
		max: 120000,
		from: 90000,
		step: 10000,
		postfix: ' €'
	});*/
	
	$( "#date" ).datepicker();


});