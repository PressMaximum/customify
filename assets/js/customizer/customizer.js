/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $, api ) {

	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title, .site-description' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title, .site-description' ).css( {
					'clip': 'auto',
					'position': 'relative'
				} );
				$( '.site-title a, .site-description' ).css( {
					'color': to
				} );
			}
		} );
	} );

    var $document = $( document );

    api.bind( 'preview-ready', function() {
        var defaultTarget = window.parent === window ? null : window.parent;
        $document.on( 'click', '#masthead .customize-partial-edit-shortcut-header_panel', function( e ){
            e.preventDefault();
            defaultTarget.wp.customize.panel( 'header_settings' ).focus();
        } );


        // for custom when click on preview
        $document.on( 'click', '.builder-item-focus', function( e ){
            e.preventDefault();
            var section_id =  $( this ).attr( 'data-section' ) || '';
            if( section_id ) {
                if ( defaultTarget.wp.customize.section( section_id ) ) {
                    defaultTarget.wp.customize.section( section_id ).focus();
                }

            }
        } );


        /*
        $( window ).resize( function(){
            var css_code = $( '#customify-style-inline-css' ).html();
            // Fix Chrome Lost CSS When resize ??
            $( '#customify-style-inline-css' ).html( css_code );
        });
        */

        // Get all values
       // console.log( 'ALL Control Values', api.get( ) );

        // Get a control
       // console.log( 'Test Get control',  defaultTarget.wp.customize.control( 'repeater' ) );
        //console.log( 'Customify_Preview_Config_Fields', Customify_Preview_Config_Fields );

        /*
        $.each( Customify_Preview_Config_Fields, function ( index, field ) {
            if ( index.indexOf( 'setting|' ) > -1 ) {
                //console.log( field );
                if ( ! _.isUndefined( field.selector ) && ! _.isUndefined( field.css_format )  && field.selector ) {
                    console.log( 'Selector' , field.selector  );
                    $document.on( 'click', field.selector, function(){
                        //console.log( 'field.selector-Click', field.selector );
                       defaultTarget.wp.customize.control( field.name ).focus();
                    } );
                }
            }
        } );
        */



        /*
        wp.customize.selectiveRefresh.bind( 'sidebar-updated', function( sidebarPartial ) {
            var widgetArea;

            console.log( 'sidebar-updated', sidebarPartial );

        } );
        wp.customize.selectiveRefresh.bind( 'render-partials-response', function( sidebarPartial ) {
            var widgetArea;
            console.log( 'sidebarPartial', sidebarPartial );
        } );
        */


        wp.customize.selectiveRefresh.bind( 'partial-content-rendered', function( settings ) {
            //var widgetArea;
            if( settings.partial.id == 'header_builder_panel' ) {
                $('body > .mobile-header-panel' ).remove();
                $( 'body' ).prepend(  $( '#mobile-header-panel' ) );

            }

            var header = $( '#masthead' );
            if ( $( '.search-form--mobile', header ).length ) {
                $( '.mobile-search-form-sidebar' ).remove();
                var search_form = $( '.search-form--mobile' ).eq(0);
                search_form.addClass('mobile-search-form-sidebar')
                    .removeClass( 'hide-on-mobile hide-on-tablet' );
                $( 'body' ).prepend( search_form );
            }

            $document.trigger( 'header_builder_panel_changed',[ settings.partial.id ] );

            //console.log( 'partial-content-rendered', sidebarPartial );
        } );



    } );


} )( jQuery, wp.customize );
