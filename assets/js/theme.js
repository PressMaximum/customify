jQuery( document ).ready( function( $ ){
    var $document = $( document );
    var menu_sidebar_state = 'closed';

    if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
        $( '#header-menu-sidebar' ).insertAfter( "#masthead" );
    } else {
        $( 'body' ).prepend(  $( '#header-menu-sidebar' ) );
    }

    if ( $( '.search-form--mobile' ).length ) {
        var search_form = $( '.search-form--mobile' ).eq(0);
        search_form.addClass('mobile-search-form-sidebar menu-sidebar-panel')
            .removeClass( 'hide-on-mobile hide-on-tablet' );
        $( 'body' ).prepend( search_form );
    }

    var insertNavIcon = function(){
        $( '.menu-item-has-children', $( '#header-menu-sidebar .nav-menu-mobile' ) ).each( function(){
            var $el = $( this );
            $( '<span class="nav-toggle-icon"><i class="fa fa-angle-down"></i></span>' ).insertBefore( $( ' > .sub-menu', $el ) );
        } );
    };

    var insertMenuOverlayClass = function() {
        if ( $( 'body' ).hasClass( 'menu_sidebar_slide_overlay' ) ) {
            $('.nav-menu-mobile').addClass('nav-menu-overlay');
        } else {
            $('.nav-menu-mobile').removeClass('nav-menu-overlay');
        }
    };

    var setupMobileHeight = function( $el ){
        var h = $( window ).height();
        if ( typeof ( $el ) ===  "undefined" ) {
            $el = $( '#header-menu-sidebar' );
        }
        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
           // $el.height( 0 );
        } else {
            $el.height( h );
        }

        var t = .2;
        var index = 0;
        $( '.item--inner', $el ).each( function(){
            index ++ ;
            $( this ).css( { 'transition-delay' : ( index * t ) + 's' } );
        } );

    };
    setupMobileHeight();
    insertNavIcon();
    insertMenuOverlayClass();
    $( window ).resize( function(){
        setupMobileHeight();
    } );

    var open_menu_sidebar = function( toggle ){
        $( 'body' ).removeClass( 'hiding-header-menu-sidebar' );
        if ( typeof toggle === "undefined" ) {
            toggle = true;
        }

        if( ! toggle ) {
            $( 'body' ).addClass( 'is-menu-sidebar' );
        } else {
            $( 'body' ).toggleClass( 'is-menu-sidebar' );
        }

        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
            if ( toggle ) {
                $( '.menu-mobile-toggle, .menu-mobile-toggle .hamburger' ).toggleClass( 'is-active' );
            } else {
                $( '.menu-mobile-toggle, .menu-mobile-toggle .hamburger' ).addClass( 'is-active' );
            }

            if ( $( 'body' ).hasClass( 'is-menu-sidebar' ) ) {
                var h = $( '#header-menu-sidebar-inner' ).outerHeight();
                $('#header-menu-sidebar').animate({
                    height: h
                }, 300, function () {
                    // Animation complete.
                    $('#header-menu-sidebar').height( 'auto' );
                    //$( '#site-content' ).hide();
                });
            } else {
                if( toggle ) {
                    close_menu_sidebar();
                }
            }
        }
    };

    // close icon
    var close_menu_sidebar = function(){
        $( 'body' ).addClass( 'hiding-header-menu-sidebar' );
        $( 'body' ).removeClass( 'is-menu-sidebar' );
        $('.menu-mobile-toggle, .menu-mobile-toggle .hamburger').removeClass( 'is-active' );

        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) )
        {
            $( 'body' ).removeClass( 'hiding-header-menu-sidebar' );
            var h = $( '#header-menu-sidebar #header-menu-sidebar-inner' ).outerHeight();
            //$( '#header-menu-sidebar' ).css( 'height', 0 );
            //$( '#site-content' ).show();
            $( '#header-menu-sidebar' ).slideUp(300, function(){
                $( '#header-menu-sidebar' ).css( { height: 0, display: 'block' } );
            });

        } else {
            //$( '#site-content' ).show();
            setTimeout( function () {
                $( 'body' ).removeClass( 'hiding-header-menu-sidebar' );
            }, 1000 );
        }
    };

    $document.on( 'click',  '.menu-mobile-toggle', function( e ){
        e.preventDefault();
        open_menu_sidebar();
    } );

    $document.on( 'customize_section_opened', function( e, id ){
        //$( '#site-content' ).show();
        if( id === 'header_sidebar' ) {
            open_menu_sidebar( false );
        }
    } );

    // When click close button
    $document.on( 'click',  '#header-menu-sidebar .close-panel, .close-sidebar-panel', function( e ){
        e.preventDefault();
        close_menu_sidebar();
    } );

    $document.on( 'click', function(event) {
        if ( $( 'body' ).hasClass( 'is-menu-sidebar' ) ) {
            //console.log(  'has_open_panel' );
            var $sidebar = $("#header-menu-sidebar");
            var $button = $( '.menu-mobile-toggle' );
            if (
                $sidebar.has(event.target).length == 0 //checks if descendants of $box was clicked
                &&
                !$sidebar.is(event.target) //checks if the $box itself was clicked

                &&  $button.has(event.target).length == 0
                && !$button.is(event.target)
            ) {
                close_menu_sidebar();
            } else {
                //$log.text("you clicked inside the box");
            }
        }
    });

    $document.on( 'keyup', function( e ) {
        if ( e.keyCode === 27 ) {
            close_menu_sidebar();
        }
    } );

    // Toggle sub menu
    $document.on( 'click',  'li .nav-toggle-icon', function( e ){
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
            $('body').addClass( 'is-mobile-form-panel' );
        } else {
            $('body').removeClass( 'is-mobile-form-panel' );
        }
    } );

    // When Header Panel rendered by customizer
    $document.on( 'header_builder_panel_changed', function(){
        setupMobileHeight();
        insertNavIcon();
        insertMenuOverlayClass();
        //stickyHeaders.load($(".header--row.is-sticky"));
    } );

} );