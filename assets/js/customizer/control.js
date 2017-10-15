(function( $, wpcustomize ) {
    'use strict';

    var $document = $( document );

    wp.customize.controlConstructor._beacon = wp.customize.Control.extend({

        // When we're finished loading continue processing
        ready: function() {

            var control = this;
            console.log( control );
            control.init();
        },

        type: '_beacon',
        settingField: null,

        getTemplate: _.memoize(function () {
            var control = this;
            var compiled,
                /*
                 * Underscore's default ERB-style templates are incompatible with PHP
                 * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
                 *
                 * @see trac ticket #22344.
                 */
                options = {
                    evaluate: /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape: /\{\{([^\}]+?)\}\}(?!\})/g,
                    variable: 'data'
                };

            return function (data, id) {
                if (_.isUndefined(id)) {
                    id = 'tmpl-customize-control-' + control.type;
                }
                compiled = _.template($('#' + id).html(), null, options);
                return compiled(data);
            };

        }),
        init: function() {
            var control = this;
            // The hidden field that keeps the data saved (though we never update it)
            control.settingField = control.container.find( '[data-customize-setting-link]' ).first();

            control.initTabs();

            switch ( control.params.setting_type ) {
                case 'group':
                    control.initGroup();
                    break;

                case 'repeater':
                    control.initRepeater();
                    break;
                default:
                    control.initField();
                    break;
            }

        },

        initGroup: function(){
            var control = this;
            var template = control.getTemplate();
            var $fields = template( control.params.fields , 'tmpl-customize-control-'+control.type+'-fields');
            control.container.find( '._beacon--settings-fields' ).html( $fields );
        },

        initField: function( ){
            var control = this;
            var template = control.getTemplate();
            var fields =  [{
                type: control.params.setting_type,
                name: control.id,
                value: control.params.value
            }];
            var $fields = template( fields , 'tmpl-customize-control-'+control.type+'-fields');
            control.container.find( '._beacon--settings-fields' ).html( $fields );
        },

        initTabs: function(){

            var control = this;
            if ( control.container.hasClass( '_beacon--device-mobile' ) ) {
                control.container.addClass( '_beacon--hide' );
            }

            $( '._beacon--tab-device-mobile', control.container ).on( 'click', function(e){
                e.preventDefault();
                $document.trigger( '_beacon/customizer/device/change',['mobile'] );
            } );

            $( '._beacon--tab-device-general', control.container ).on( 'click', function(e){
                e.preventDefault();
                $document.trigger( '_beacon/customizer/device/change',['general'] );
            } );

        },

        addRepeaterItem: function( value ){
            if ( ! _.isObject( value ) ) {
                value = {};
            }

            var control = this;
            var template = control.getTemplate();

            var fields = control.params.fields;
            var liveTitleValue = '';
            _.each( fields, function( f, index ){
                fields.value = '';
                if ( ! _.isUndefined( value[ f.name ] ) ) {
                    fields.value = value[ f.name ];
                }
                if ( f.name == control.params.live_title_field ) {
                    liveTitleValue = f.value;
                }
            } );

            if ( ! liveTitleValue || liveTitleValue == '' ) {
                liveTitleValue = control.params.l10n.untitled;
            }

            var htmlSettings = template( fields , 'tmpl-customize-control-'+control.type+'-fields');
            var $itemWrapper = $( template( htmlSettings , 'tmpl-customize-control-'+control.type+'-repeater') );
            control.container.find( '._beacon--settings-fields' ).append( $itemWrapper );
            $itemWrapper.find( '._beacon--repeater-live-title' ).html( liveTitleValue );


            $document.trigger('_beacon/customizer/repeater/add', [ $itemWrapper, control ] );
            return $itemWrapper;
        },

        initRepeater: function(){
            var control = this;

            control.container.find( '._beacon--settings-fields' ).sortable({
                handle: '._beacon--repeater-item-heading',
                containment: "parent"
            });

            control.container.on( 'click', '._beacon--repeater-item-toggle', function(e){
                e.preventDefault();
                var  p = $( this ).closest('._beacon--repeater-item');
                p.toggleClass('_beacon--open');
            } );

            control.addRepeaterItem();
            control.addRepeaterItem();
            control.container.on( 'click', '._beacon--repeater-add-new', function(e){
                e.preventDefault();
                control.addRepeaterItem();
            } );
        }


    });


    $document.ready( function( $ ){

        $document.on( '_beacon/customizer/device/change', function( e, device ) {
            $( '._beacon--device-select a' ).removeClass( '_beacon--active' );
            if ( device != 'mobile' ) {
                $( '._beacon--device-mobile').addClass( '_beacon--hide' );
                $( '._beacon--device-general' ).removeClass( '_beacon--hide' );
                $( '._beacon--tab-device-general' ).addClass('_beacon--active');
            } else {
                $( '._beacon--device-general' ).addClass( '_beacon--hide' );
                $( '._beacon--device-mobile' ).removeClass( '_beacon--hide' );
                $( '._beacon--tab-device-mobile' ).addClass('_beacon--active');
            }
        } );

    } );

})( jQuery, wp.customize || null );