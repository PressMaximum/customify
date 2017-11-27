jQuery( document ).ready( function( $ ){
    var $document = $( document );
    $( 'body' ).prepend(  $( '#mobile-header-panel' ) );

    if ( $( '.search-form--mobile' ).length ) {
        var search_form = $( '.search-form--mobile' ).eq(0);
        search_form.addClass('mobile-search-form-sidebar mobile-sidebar-panel')
            .removeClass( 'hide-on-mobile hide-on-tablet' );
        $( 'body' ).prepend( search_form );
    }


    var insertNavIcon = function(){
        $( '.menu-item-has-children', $( '#mobile-header-panel .nav-menu-mobile' ) ).each( function(){
            var $el = $( this );
            $( '<span class="nav-t-icon"></span>' ).insertBefore( $( '.sub-menu', $el ) );
        } );

    };

    var setupMobileHeight = function( $el ){
        var h = $( window ).height();
        if ( typeof ( $el ) ===  "undefined" ) {
            $el = $( '#mobile-header-panel' );
        }
        $el.height( h );
    };
    setupMobileHeight();
    insertNavIcon();
    $( window ).resize( function(){
        setupMobileHeight();
    } );

    $document.on( 'click',  '.nav-mobile-toggle', function( e ){
        e.preventDefault();
        $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
        $( 'body' ).toggleClass( 'display-mobile-header-panel' );
    } );

    $document.on( 'click',  '#mobile-header-panel .close-panel', function( e ){
        e.preventDefault();
        $( 'body' ).addClass( 'hiding-mobile-header-panel' );
        $( 'body' ).removeClass( 'display-mobile-header-panel' );
        setTimeout( function () {
            $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
        }, 1000 );
    } );

    // Toggle sub menu
    $document.on( 'click',  'li .nav-t-icon', function( e ){
        e.preventDefault();
        $( this ).parent().toggleClass('open-sub');
    } );

    // Toggle Header Search
    $document.on( 'click',  '.builder-item--search .search-toggle,  .mobile-search-form-sidebar .close', function( e ){
        e.preventDefault();
        var form = $('.mobile-search-form-sidebar');
        setupMobileHeight( form );
        form.toggleClass('builder-item--search-show');
        if ( form.hasClass( 'builder-item--search-show' ) ) {
            $('body').addClass( 'display-mobile-form-panel' );
        } else {
            $('body').removeClass( 'display-mobile-form-panel' );
        }
    } );


    var stickyHeaders = (function() {

        var $window = $(window),
            $stickies;

        var lastScrollTop = 0;

        var setData = function( stickies, addWrap  ){
            if ( typeof addWrap === "undefined" ) {
                addWrap = true;
            }
            $stickies = stickies.each(function() {
                var $thisSticky = $(this);
                var p = $thisSticky.parent();
                if ( ! p.hasClass('followWrap')) {
                    if ( addWrap ) {
                        $thisSticky.wrap('<div class="followWrap" />');
                    }
                }

                $thisSticky.parent().removeAttr('style');

                $thisSticky
                    .data('originalPosition', $thisSticky.offset().top )
                    .data('originalHeight', $thisSticky.height() )
                    .parent()
                    .height($thisSticky.height());
            });
        };

        var load = function(stickies) {

            if (typeof stickies === "object" && stickies instanceof jQuery && stickies.length > 0) {

                setData( stickies );

                $window.off("scroll.stickies").on("scroll.stickies", function() {
                    _whenScrolling();
                });

                $window.resize( function(){
                    setData( stickies, false );
                    stickies.each( function(){
                        $( this ).removeClass("fixed").removeAttr("style");
                    } );
                    _whenScrolling();
                } );

                $document.on( 'header_builder_panel_changed', function(){
                    $( '.followWrap' ).removeAttr('style');
                        setTimeout( function(){
                        $( '.followWrap' ).removeAttr('style');
                        setData( stickies, false );
                        _whenScrolling();
                    }, 500 );
                } );

            }
        };

        var _whenScrolling = function() {

            $stickies.each(function(i) {

                var $thisSticky = $(this),
                    $stickyPosition = $thisSticky.data('originalPosition');
                var h = $thisSticky.data('originalHeight');

                if ($stickyPosition + h <= $window.scrollTop()) {

                    var $nextSticky = $stickies.eq(i + 1),
                        $nextStickyPosition = $nextSticky.data('originalPosition') - $thisSticky.data('originalHeight');

                    $thisSticky.addClass("fixed");

                    if ($nextSticky.length > 0 && $thisSticky.offset().top >= $nextStickyPosition) {

                        $thisSticky.addClass("absolute").css("top", $nextStickyPosition);
                    }

                } else {

                    var $prevSticky = $stickies.eq(i - 1);

                    $thisSticky.removeClass("fixed");

                    if ($prevSticky.length > 0 && $window.scrollTop() <= $thisSticky.data('originalPosition') - $thisSticky.data('originalHeight')) {

                        $prevSticky.removeClass("absolute").removeAttr("style");
                    }
                }
            });
        };



        return {
            load: load
        };
    })();

    stickyHeaders.load($(".header--row.is-sticky"));


    // When Header Panel rendered by customizer
    $document.on( 'header_builder_panel_changed', function(){
        setupMobileHeight();
        insertNavIcon();
        stickyHeaders.load($(".header--row.is-sticky"));
    } );










} );