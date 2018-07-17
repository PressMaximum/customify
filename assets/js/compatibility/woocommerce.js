
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

    //------------------------------------------------------------------------------------------

    // Quick view
    // Close quick view
    $( document.body ).on( 'click', '.customify-wc-modal-close, .customify-wc-modal-overlay', function( e  ) {
        e.preventDefault();
        $( this ).closest( '.customify-wc-modal' ).removeClass('show' ).addClass( 'hide' );
    } );

    $( window ).on('keyup', function(e) {
        if( e.which=== 27 ){ // esc button
            $( '.customify-wc-modal' ).removeClass('show' ).addClass( 'hide' );
        }
    });

    // Open quick view
    $( document.body ).on( 'click', '.customify-wc-quick-view', function( e ){
        e.preventDefault();
        var id = $( this ).attr(  'data-id') || '';

        if ( id  ) {

            if ( $( '#customify-wc-modal-product-'+id ).length ) {
                 $( '#customify-wc-modal-product-'+id ).removeClass( 'hide' ).addClass( 'show' );
                 setTimeout( function(){
                     $( window ).resize();
                 }, 400 );
            } else {
                $.ajax({
                    url: woocommerce_params.ajax_url,
                    type:'get',
                    data: {
                        action: 'customify/wc/quick-view',
                        product_id: id,
                    },
                    success: function( res ){
                        wc_single_product_params = res.params;
                        var content = $( '<div id="customify-wc-modal-product-'+id+'" class="customify-wc-modal hide">' +
                            '<div class="customify-wc-modal-overlay"></div>' +
                            '<div class="customify-wc-modal-cont  woocommerce woocommerce-page single single-product">' +
                            '<div class="customify-wc-modal-inner"><a href="#" class="customify-wc-modal-close">x</a> ' +
                            '<div class="customify-container"><div class="customify-grid">'+res.content+'</div>' +
                            '</div>' +
                            '</div>' +
                            '</div></div>' );
                        $( 'body' ).append( content );

                        /*
                        * Initialize all galleries on page.
                        */
                        $( '.woocommerce-product-gallery' ).each( function() {
                            $( this ).wc_product_gallery();
                        } );

                        if ( res.type === 'variable' ) {
                            wc_add_to_cart_variation_params = res.variation_params;
                            if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
                                $( '.variations_form', content ).each( function() {
                                    $( this ).wc_variation_form();
                                });
                            }
                        }

                        content.removeClass( 'hide' ).addClass( 'show' );

                    }
                });
            }
        }

    } );

    $.fn._add_cart_serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };


    // Quick view add to cart
    $( document.body ).on( 'click', '.customify-wc-modal .single_add_to_cart_button', function( e ) {
        e.preventDefault();

        var $thisbutton = $( this );

        if ( $thisbutton.hasClass( 'disabled' ) || $thisbutton.is( ':disabled' ) ) {
            return;
        }

        var form = $thisbutton.closest('form');
        var data = form._add_cart_serializeObject();

        $thisbutton.removeClass( 'added' );
        $thisbutton.addClass( 'loading' );

        if ( typeof data.product_id === "undefined" && typeof data['add-to-cart'] !== "undefined" ){
            data.product_id = data['add-to-cart'];
        } else if ( typeof data['add-to-cart'] === "undefined" ) {
            data.product_id = $thisbutton.val();
        }

        // Trigger event.
        $( document.body ).trigger( 'adding_to_cart', [ $thisbutton, data ] );

        // Ajax action.
        $.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ), data, function( response ) {
            console.log( response );
            if ( ! response ) {
                return;
            }

            if ( response.error && response.product_url ) {
                window.location = response.product_url;
                return;
            }

            // Redirect to cart option
            if ( wc_add_to_cart_params.cart_redirect_after_add === 'yes' ) {
                window.location = wc_add_to_cart_params.cart_url;
                return;
            }

            // Trigger event so themes can refresh other areas.
            $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $thisbutton ] );
        });

    } );

    //---------------------------------------------------------------------

} );
