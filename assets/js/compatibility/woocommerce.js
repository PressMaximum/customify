
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


    $( document.body ).on( 'click', '.wc-view-switcher .wc-view-mod', function( e ){
        e.preventDefault();
        var mod = $( this ).data('mod') || 'grid';
        $( '.wc-view-switcher .wc-view-mod' ).removeClass( 'active' );
        $( this ).addClass( 'active' );
        $( '.woocommerce-listing' ).removeClass( 'wc-grid-view wc-list-view' );
        $( '.woocommerce-listing' ).addClass( 'wc-'+mod+'-view'  );
    } );


    // Switch View mod

} );
