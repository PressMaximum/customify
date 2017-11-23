jQuery( document ).ready( function( $ ){
    var $document = $( document );
    $( 'body' ).prepend(  $( '#mobile-header-panel' ) );

    var insertNavIcon = function(){
        $( '.menu-item-has-children', $( '#mobile-header-panel .nav-menu-mobile' ) ).each( function(){
            var $el = $( this );
            $( '<span class="nav-t-icon"></span>' ).insertBefore( $( '.sub-menu', $el ) );
        } );

    };

    var setupMobileSidebarHeight = function(){
        var h = $( window ).height();
        $( '#mobile-header-panel' ).height( h );
    };
    setupMobileSidebarHeight();
    insertNavIcon();
    $( window ).resize( function(){
        setupMobileSidebarHeight();
    } );

    $document.on( 'header_builder_panel_changed', function(){
        setupMobileSidebarHeight();
        insertNavIcon();
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



} );