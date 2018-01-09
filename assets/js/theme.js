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
        $( this ).toggleClass( 'is-active' );
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


    // When Header Panel rendered by customizer
    $document.on( 'header_builder_panel_changed', function(){
        setupMobileHeight();
        insertNavIcon();
        //stickyHeaders.load($(".header--row.is-sticky"));
    } );










} );