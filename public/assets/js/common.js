
// Document Ready
$(function(){
    $( document ).ajaxStart(function() {
        $('#ajaxspinner').removeClass('hidden');
    });
    /* hide spinner */
    $( document ).ajaxStop(function() {
        $('#ajaxspinner').addClass('hidden');
    });

    if ($(".dropdown").length > 0) {
        $(".brick-bloc ").on("dragstart", function() {
            return false;
        });
    }

    // main menu > dropdown menu
    if ($(".dropdown").length > 0) {
        $(".dropdown").hover(
            function() {
                $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true, true).slideDown("400");
                $(this).toggleClass('open');
            },
            function() {
                $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true, true).slideUp("400");
                $(this).toggleClass('open');
            }
        );
    }

    // banner slider in home page
    if ($(".slider-banner").length > 0) {
        var banner = $('.banner-home .owl-carousel');
        banner.owlCarousel({
            loop: true,
            margin: 0,
            nav: true,
            navText: ["<i class='iconucm-arrowLeft'></i>", "<i class='iconucm-arrowRight'></i>"],
            dot: false,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            items: 1,
        })
        $('.play').on('click', function () {
            banner.trigger('play.owl.autoplay', [1000]);
        })
        $('.stop').on('click', function () {
            banner.trigger('stop.owl.autoplay');
        })
    }

    // gallery image slider multiple column with swiper
    // var swiper = new Swiper(".swiperGallery", {
    //     slidesPerView: 3,
    //     slidesPerColumn: 2,
    //     slidesPerColumnFill: 'row',
    //     spaceBetween: 30,
    //     pagination: {
    //       el: ".swiper-pagination",
    //       clickable: true,
    //     },
    // });

    // scrolldown home banner
    if ($(".btn-slideDown").length > 0) {
        $(".btn-slideDown").on("click", function () {
            $(window).scrollTop($('#mainBloc').offset().top);

            // $(document).animate({
            //    scrollTop: $("#mainBloc").offset().top
            // }, 500);
        });
    }
    if ($("select.custom").length > 0) {
        $("select.custom").each(function () {
            var sb = new SelectBox({
                selectbox: $(this),
                height: 150,
                ///width: 200
            });
        });
    }

    if ($(".grid").length > 0) {
        /* $('.brick-listing').packery({
            // options
            itemSelector: '.grid-item',
            gutter: 30,
            //percentPosition: true,
        }); */
        /* $('.brick-listing').isotope({
            //options
            itemSelector: '.grid-item',
            gutter: 30,
            //stamp elements
            stamp: '.stamp',
        }); */
    }

    // Swipe content in "formation > ecnomie et gestion"
    var $tab_bar = document.querySelector(".mdl-tabs__tab-bar");

    // Our events are going to be in the tab bar
    var mc = new Hammer($tab_bar);

    // Our specific pan event
    mc.add(new Hammer.Pan({
        direction: Hammer.DIRECTION_HORIZONTAL
    }));

    // Listen to events
    mc.on("panleft panright", function(e) {
        if (e.type === "panleft") $tab_bar.scrollLeft += 10;
        else $tab_bar.scrollLeft -= 10;
    });

    
});

