
jQuery( document ).ready( function( $ ){
    $( '._beacon--cb-items' ).sortable();
    $( "._beacon--cb-item" ).resizable({
        handles: "n, e, s, w",
        containment: "parent"
    });





} );