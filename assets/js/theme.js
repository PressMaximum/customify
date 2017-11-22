jQuery( document ).ready( function( $ ){
    var $document = $( document );
    $( 'body' ).prepend(  $( '#mobile-header-panel' ) );
    var setupMobileSidebarHeight = function(){
        var h = $( window ).height();
        $( '#mobile-header-panel' ).height( h );
    };
    setupMobileSidebarHeight();
    $( window ).resize( function(){
        setupMobileSidebarHeight();
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

} );