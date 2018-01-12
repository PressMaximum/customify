jQuery( document ).ready( function( $ ){
    var $document = $( document );
    var menu_sidebar_state = 'closed';

    if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
        $( '#mobile-header-panel' ).insertAfter( "#masthead" );
    } else {
        $( 'body' ).prepend(  $( '#mobile-header-panel' ) );
    }

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
    $( window ).resize( function(){
        setupMobileHeight();
    } );

    var open_menu_sidebar = function( toggle ){
        $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
        if ( typeof toggle === "undefined" ) {
            toggle = true;
        }

        if( ! toggle ) {
            $( 'body' ).addClass( 'display-mobile-header-panel' );
        } else {
            $( 'body' ).toggleClass( 'display-mobile-header-panel' );
        }

        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
            if ( toggle ) {
                $( '.nav-mobile-toggle, .nav-mobile-toggle .hamburger' ).toggleClass( 'is-active' );
            } else {
                $( '.nav-mobile-toggle, .nav-mobile-toggle .hamburger' ).addClass( 'is-active' );
            }

            if ( $( 'body' ).hasClass( 'display-mobile-header-panel' ) ) {
                var h = $( '#mobile-header-panel-inner' ).outerHeight();
                $('#mobile-header-panel').animate({
                    height: h
                }, 300, function () {
                    // Animation complete.
                    $('#mobile-header-panel').height( 'auto' );
                    $( '#site-content' ).hide();
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
        $( 'body' ).addClass( 'hiding-mobile-header-panel' );
        $( 'body' ).removeClass( 'display-mobile-header-panel' );
        $('.nav-mobile-toggle, .nav-mobile-toggle .hamburger').removeClass( 'is-active' );

        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) )
        {
            $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
            var h = $( '#mobile-header-panel #mobile-header-panel-inner' ).outerHeight();
            //$( '#mobile-header-panel' ).css( 'height', 0 );
            $( '#site-content' ).show();
            $( '#mobile-header-panel' ).slideUp(300, function(){
                $( '#mobile-header-panel' ).css( { height: 0, display: 'block' } );
            });

        } else {
            $( '#site-content' ).show();
            setTimeout( function () {
                $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
            }, 1000 );
        }
    };

    $document.on( 'click',  '.nav-mobile-toggle', function( e ){
        e.preventDefault();
        open_menu_sidebar();
    } );

    $document.on( 'customize_section_opened', function( e, id ){
        $( '#site-content' ).show();
        if( id === 'header_sidebar' ) {
            open_menu_sidebar( false );
        }
    } );

    // When click close button
    $document.on( 'click',  '#mobile-header-panel .close-panel, .close-sidebar-panel', function( e ){
        e.preventDefault();
        close_menu_sidebar();
    } );

    $document.on( 'click', function(event) {
        if ( $( 'body' ).hasClass( 'display-mobile-header-panel' ) ) {
            //console.log(  'has_open_panel' );
            var $sidebar = $("#mobile-header-panel");
            var $button = $( '.nav-mobile-toggle' );
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


    // When Header Panel rendered by customizer
    $document.on( 'header_builder_panel_changed', function(){
        setupMobileHeight();
        insertNavIcon();
        //stickyHeaders.load($(".header--row.is-sticky"));
    } );



} );