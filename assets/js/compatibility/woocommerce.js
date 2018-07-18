
jQuery( document ).ready( function ( $ ) {

    if ( $.blockUI ) {
        $.blockUI.defaults.overlayCSS.backgroundColor = '#FFF';
        $.blockUI.defaults.overlayCSS.opacity = 0.7;
    }

    jQuery(document).on( 'selective-refresh-content-rendered', function( e, id ) {
        if ( id === 'Customify_Builder_Item_WC_Cart__render' || id === 'customify_customize_render_header' ) {
            $( document.body ).trigger( 'wc_fragment_refresh' );
        }
    } );

    $( document.body ).on( 'added_to_cart', function( event, fragments, cart_hash ) {
        $( '.item--wc_cart' ).addClass( 'cart-active' );
    });

    $( document.body ).on( 'wc_cart_button_updated', function ( e, button ) {
        var p = button.parent();
        if( ! button.hasClass( 'single_add_to_cart_button' ) ) {
            $( '.added_to_cart', p ).addClass( 'button' );
        }

        var pos = $( '.add_to_cart_button', p ).data('icon-pos') || 'before';
        var icon = $( '.add_to_cart_button', p ).data('cart-icon') || '';
        var text = '';
        var icon_code = '';
        if ( icon ) {
            icon_code = '<i class="'+icon+'"></i>';
        }
        if ( pos === 'after' ) {
            if ( icon_code ) {
                text = wc_add_to_cart_params.i18n_view_cart+' '+icon_code;
            } else {
                text = wc_add_to_cart_params.i18n_view_cart;
            }

        } else {
            if ( icon_code ) {
                text = icon_code+' '+wc_add_to_cart_params.i18n_view_cart;
            } else {
                text = wc_add_to_cart_params.i18n_view_cart;
            }
        }

        $( '.added_to_cart.wc-forward', p ).html( text );
    } );
    
    
    $( document.body ).on( 'hover', '.item--wc_cart', function(){
        $( this ).removeClass( 'cart-active' );
    } );

    // Switch View mod
    $( document.body ).on( 'click', '.wc-view-switcher .wc-view-mod', function( e ){
        e.preventDefault();
        var mod = $( this ).data('mod') || 'grid';
        $( '.wc-view-switcher .wc-view-mod' ).removeClass( 'active' );
        $( this ).addClass( 'active' );
        $( '.woocommerce-listing, .products' ).removeClass( 'wc-grid-view wc-list-view' );
        $( '.woocommerce-listing, .products' ).addClass( 'wc-'+mod+'-view'  );
    } );


} );
