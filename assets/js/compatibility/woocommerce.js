
jQuery( document ).ready( function ( $ ) {

    jQuery(document).on( 'selective-refresh-content-rendered', function( e, id ) {
        if ( id === 'Customify_Builder_Item_WC_Cart__render' || id === 'customify_customize_render_header' ) {
            $( document.body ).trigger( 'wc_fragment_refresh' );
        }
    } );

    $( document.body ).on( 'added_to_cart', function( event, fragments, cart_hash ) {
        $( '.item--wc_cart' ).addClass( 'cart-active' );
    });
    $( document.body ).on( 'hover', '.item--wc_cart', function(){
        $( this ).removeClass( 'cart-active' );
    } );
    
} );
