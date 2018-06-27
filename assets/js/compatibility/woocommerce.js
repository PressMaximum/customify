console.log( 'loaded-woocommerce-js' );


/*
function customify_woocommerce(){
    // do somthing here
    console.log( 'loaded-woocommerce-js' );
}
*/
jQuery( document ).ready( function ( $ ) {

    jQuery(document).on( 'selective-refresh-content-rendered', function( e, id ) {
        if ( id === 'Customify_Builder_Item_WC_Cart__render' ) {
            $( document.body ).trigger( 'wc_fragment_refresh' );
        }
    } );


   // $( document.body ).trigger( 'wc_fragments_refreshed' );
    // $( document.body ).trigger( 'wc_fragments_loaded' );

} );
