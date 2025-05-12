jQuery(document).ready(function($) {

    "use strict";

    /**
     * Color Blog Preloader
     */
    if($('#preloader-background').length > 0) {
        setTimeout(function(){$('#preloader-background').hide();}, 600);
    }

    var grid = document.querySelector(
            '.color-blog-content-masonry'
        ),
        masonry;

    if (
        grid &&
        typeof Masonry !== undefined &&
        typeof imagesLoaded !== undefined
    ) {
        imagesLoaded( grid, function( instance ) {
            masonry = new Masonry( grid, {
                itemSelector: '.hentry'
            } );
        } );
    }


    /**
     * Header Search script
     */
    $('.mt-menu-search .mt-search-icon').click(function() {
        $('.mt-menu-search .mt-form-wrap').toggleClass('search-activate');
    });

    $('.mt-menu-search .mt-form-close').click(function() {
        $('.mt-menu-search .mt-form-wrap').removeClass('search-activate');
    });


    /**
     * Settings about WOW animation
     */
    var wowOption = color_blogObject.wow_effect;
    if( wowOption === 'on' ) {
        new WOW().init();
    }

    /**
     * Settings about sticky menu
     */
    var stickyOption = color_blogObject.menu_sticky;
    if( stickyOption === 'on' ) {
        var windowWidth = $( window ).width();
        if( windowWidth < 500 ) {
            var wpAdminBar = 0;
        } else {
            var wpAdminBar = $('#wpadminbar');
        }
        if ( wpAdminBar.length ) {
            $(".mt-social-menu-wrapper").sticky({topSpacing:wpAdminBar.height()});
        } else {
            $(".mt-social-menu-wrapper").sticky({topSpacing:0});
        }
    }
    
    /**
     * Scroll To Top
     */
    $(window).scroll(function() {
        if ($(this).scrollTop() > 1000) {
            $('#mt-scrollup').fadeIn('slow');
        } else {
            $('#mt-scrollup').fadeOut('slow');
        }
    });
    $('#mt-scrollup').click(function() {
        $("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
    });
    
    /**
     * Slider scripts
     */
    $('.front-slider').lightSlider({
        pager: false,
        auto: false,
        loop: true,
        item: 1,
        controls: true,
        slideMargin:0,
        rtl:true,
        nextHtml: '<span class="icon-prev"><i class="fa fa-angle-left"></i></span>',
        prevHtml: '<span class="icon-next"><i class="fa fa-angle-right"></i></span>',

        onSliderLoad: function() {
            $('.front-slider').removeClass('cS-hidden');
        }
        
    });

    /**
     * Slider scripts
     */
    $('.mt-gallery-slider').lightSlider({
        pager: false,
        auto: false,
        loop: true,
        item: 1,
        controls: true,
    });

    /**
     * Responsive
     */
    $('.menu-toggle').click(function(event) {
        $('#site-navigation').slideToggle('slow');
    });

    /**
     * responsive sub menu toggle
     */
    $('#site-navigation .menu-item-has-children').append('<span class="sub-toggle"> <i class="fa fa-angle-right"></i> </span>');
    $('#site-navigation .page_item_has_children').append('<span class="sub-toggle"> <i class="fa fa-angle-right"></i> </span>');
    

    $('#site-navigation .sub-toggle').click(function() {
        $(this).parent('.menu-item-has-children').children('ul.sub-menu').first().slideToggle('1000');
        jQuery(this).parent('.page_item_has_children').children('ul.children').first().slideToggle('1000');
        $(this).children('.fa-angle-right').first().toggleClass('fa-angle-down');
    });

    /**
     * Slider Section dynamic height script
     */
    $(window).on('load', function() {
        if ($(window).width() > 839) {
            $(".front-slider-wrapper").each(function() {
                var imageHeight = $(this).height();
                $(this).find(".slider-post-wrap").css('height', imageHeight);
                $(this).find(".front-slider ").css('height', imageHeight);
            });
        }
    });


});