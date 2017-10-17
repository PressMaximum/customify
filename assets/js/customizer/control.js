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

            control.container.on( 'change keyup data-change', 'input:not(.change-by-js), select:not(.change-by-js), textarea:not(.change-by-js)', function(){
                control.getValue();
            } );

            control.initMedia();
            control.initColor( control.container );
            control.initCSSRuler();

        },
        addParamsURL: function( url, data )
        {
            if ( ! $.isEmptyObject(data) )
            {
                url += ( url.indexOf('?') >= 0 ? '&' : '?' ) + $.param(data);
            }

            return url;
        },

        initMedia: function(){
            var control = this;
            control.controlMedia =  {
                setAttachment: function( attachment ){
                    this.attachment = attachment;
                },
                getThumb: function( attachment ){
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }
                    var t = new Date().getTime();
                    if ( typeof this.attachment.sizes !== "undefined" ) {
                        if ( typeof this.attachment.sizes.medium !== "undefined" ) {
                            return control.addParamsURL( this.attachment.sizes.medium.url, { t : t } );
                        }
                    }
                    return control.addParamsURL( this.attachment.url, { t : t } );
                },
                getURL: function( attachment ) {
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }
                    var t = new Date().getTime();
                    return control.addParamsURL( this.attachment.url, { t : t } );
                },
                getID: function( attachment ){
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }
                    return this.attachment.id;
                },
                getInputID: function( attachment ){
                    $( '.attachment-id', this.preview ).val( );
                },
                setPreview: function( $el ){
                    this.preview = $el;
                },
                insertImage: function( attachment ){
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }

                    var url = this.getThumb();
                    var id = this.getID();
                    var mime = this.attachment.mime;
                    $( '._beacon-image-preview', this.preview ).html(  '<img src="'+url+'" alt="">' );
                    $( '.attachment-url', this.preview ).val( this.toRelativeUrl( url ) );
                    $( '.attachment-mime', this.preview ).val( mime );
                    $( '.attachment-id', this.preview ).val( id ).trigger( 'change' );
                    this.preview.addClass( 'attachment-added' );
                    this.showChangeBtn();

                },
                toRelativeUrl: function( url ){
                    return url.replace( _Beacon_Control_Args.home_url, '' );
                },
                showChangeBtn: function(){
                    $( '._beacon--add', this.preview ).addClass( '_beacon--hide' );
                    $( '._beacon--change', this.preview ).removeClass( '_beacon--hide' );
                },
                insertVideo: function(attachment ){
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }

                    var url = this.getURL();
                    var id = this.getID();
                    var mime = this.attachment.mime;
                    var html = '<video width="100%" height="" controls><source src="'+url+'" type="'+mime+'">Your browser does not support the video tag.</video>';
                    $( '._beacon-image-preview', this.preview ).html( html );
                    $( '.attachment-url', this.preview ).val( this.toRelativeUrl( url ) );
                    $( '.attachment-mime', this.preview ).val( mime );
                    $( '.attachment-id', this.preview ).val( id ).trigger( 'change' );
                    this.preview.addClass( 'attachment-added' );
                    this.showChangeBtn();
                },
                insertFile: function( attachment ){
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }
                    var url = attachment.url;
                    var mime = this.attachment.mime;
                    var basename = url.replace(/^.*[\\\/]/, '');

                    $( '._beacon-image-preview', this.preview ).html( '<a href="'+url+'" class="attachment-file" target="_blank">'+basename+'</a>' );
                    $( '.attachment-url', this.preview ).val( this.toRelativeUrl( url ) );
                    $( '.attachment-mime', this.preview ).val( mime );
                    $( '.attachment-id', this.preview ).val( this.getID() ).trigger( 'change' );
                    this.preview.addClass( 'attachment-added' );
                    this.showChangeBtn();
                },
                remove: function( $el ){
                    if ( typeof $el !== "undefined" ) {
                        this.preview = $el;
                    }
                    $( '._beacon-image-preview', this.preview ).removeAttr( 'style').html( '' );
                    $( '.attachment-url', this.preview ).val( '' );
                    $( '.attachment-mime', this.preview ).val( '' );
                    $( '.attachment-id', this.preview ).val( '' ).trigger( 'change' );
                    this.preview.removeClass( 'attachment-added' );

                    $( '._beacon--add', this.preview ).removeClass( '_beacon--hide' );
                    $( '._beacon--change', this.preview ).addClass( '_beacon--hide' );
                }

            };

            control.controlMediaImage = wp.media({
                title: wp.media.view.l10n.addMedia,
                multiple: false,
                library: {type: 'image' }
            });

            control.controlMediaImage.on('select', function () {
                var attachment = control.controlMediaImage.state().get('selection').first().toJSON();
                control.controlMedia.insertImage( attachment );
            });

            control.controlMediaVideo = wp.media({
                title: wp.media.view.l10n.addMedia,
                multiple: false,
                library: {type: 'video' }
            });

            control.controlMediaVideo.on('select', function () {
                var attachment = control.controlMediaVideo.state().get('selection').first().toJSON();
                control.controlMedia.insertVideo( attachment );
            });

            control.controlMediaFile = wp.media({
                title: wp.media.view.l10n.addMedia,
                multiple: false
            });

            control.controlMediaFile.on('select', function () {
                var attachment = control.controlMediaFile.state().get('selection').first().toJSON();
                control.controlMedia.insertFile( attachment );
            });


            // When add/Change
            control.container.on( 'click',  '._beacon--media ._beacon--add, ._beacon--media ._beacon--change, ._beacon--media ._beacon-image-preview', function( e ) {
                e.preventDefault();
                var p = $( this ).closest('._beacon--media');
                control.controlMedia.setPreview( p )  ;
                control.controlMediaImage.open();
            } );

            // When add/Change
            control.container.on( 'click',  '._beacon--media ._beacon--remove', function( e ) {
                e.preventDefault();
                var p = $( this ).closest('._beacon--media');
                control.controlMedia.remove( p );
            } );
        },
        compare: function( value1, cond, value2 ){
            var equal = false;
            switch ( cond ) {
                case '===':
                    equal = ( value1 === value2 ) ? true : false;
                    break;
                case '>':
                    equal = ( value1 > value2 ) ? true : false;
                    break;
                case '<':
                    equal = ( value1 < value2 ) ? true : false;
                    break;
                case '!=':
                    equal = ( value1 != value2 ) ? true : false;
                    break;
                case 'not_empty':
                    equal = _.isEmpty( value2 );
                    break;
                default:
                    equal = ( value1 == value2 ) ? true : false;

            }
            return equal;
        },
        multiple_compare: function( list, values ){
            var control = this;
            try {
                var test =  list[0];
                var check = true;
                if ( _.isString( test ) ) {
                    check = false;
                    var cond = list[1];
                    var cond_val = list[2];
                    if ( ! _.isUndefined( values[ test ] ) ) {
                        check = control.compare( values[ test ], cond, cond_val );
                    }
                } else if ( _.isArray( test ) ) {
                    check  = true;
                    _.each( list, function( req ) {
                        var cond_key = req[0];
                        var cond_cond = req[1];
                        var cond_val = req[2];
                        var t_val = values[ cond_key ];
                        if ( _.isUndefined( t_val ) ) {
                            t_val = '';
                        }
                        if ( ! control.compare( t_val, cond_cond, cond_val ) ) {
                            check = false;
                        }
                    } );
                }
            } catch  ( e ) {

            }


            return check;
        },

        initCSSRuler: function(){
            var control = this;
            control.container.on( 'change', '._beacon--label-parent', function(){
                if ( $( this ).attr( 'type' ) == 'radio' ){
                    var name = $( this ).attr( 'name' );
                    $( 'input[name="'+name+'"]', control.container ).parent().removeClass('_beacon--label-active');
                }
                var checked = $( this ).is( ':checked' );
                if ( checked ) {
                    $( this ).parent().addClass( '_beacon--label-active' );
                } else {
                    $( this ).parent().removeClass( '_beacon--label-active' );
                }

                control.getValue();
            } );


            control.container.on( 'change keyup', '._beacon--css-ruler ._beacon-input-css', function(){
                var p = $( this ).closest('._beacon--css-ruler');
                var link_checked = $( '._beacon--css-ruler-link input', p ).is( ':checked' );
                if ( link_checked ) {
                    var v = $( this ).val();
                    $( '._beacon-input-css', p ).not( $( this ) ).val(v);
                }

                control.getValue();
            } );

        },

        initConditional: function ( $el, values ){
            var control = this;
            var $fields  = $( '._beacon--field', $el );
            $fields.each( function( ) {
                var $field = $(this);
                var check = true;
                var req = $field.attr('data-required') || false;
                if ( !_.isUndefined( req ) && req ) {
                    req = JSON.parse( req );
                    check = control.multiple_compare( req, values );
                    if ( ! check ) {
                        $field.addClass( '_beacon--hide' );
                    } else {
                        $field.removeClass( '_beacon--hide' );
                    }
                }
            });
        },
        initColor: function( $el ){
            $( '._beacon-input-color', $el ).each( function(){
                var colorInput = $( this );
                $( '._beacon--color-panel', colorInput ).wpColorPicker({
                    change: function( event, ui ){
                        $( '._beacon-input', colorInput ).val( ui.color.toString() ).trigger('change');
                    }
                });
            } );

        },
        getFieldValue: function( name, type, $field ){
            var control = this;
            if ( _.isUndefined( $field ) ) {
                $field = control.container.find( '._beacon--settings-fields ._beacon--field' ).first();
            }

            if ( _.isUndefined( type ) ) {
                type = control.params.setting_type;
            }

            var value = '';
            switch ( type ) {
                case 'media':
                case 'image':
                case 'video':
                case 'attachment':
                    value = {
                        id:  $( 'input[data-name="'+name+'"]', $field ).val(),
                        url:  $( 'input[data-name="'+name+'-url"]', $field ).val(),
                        mime:  $( 'input[data-name="'+name+'-mime"]', $field ).val()
                    };
                break;
                case 'css_ruler':
                    value = {
                        unit:  $( 'input[data-name="'+name+'-unit"]:checked', $field ).val(),
                        top:  $( 'input[data-name="'+name+'-top"]', $field ).val(),
                        right:  $( 'input[data-name="'+name+'-right"]', $field ).val(),
                        bottom:  $( 'input[data-name="'+name+'-bottom"]', $field ).val(),
                        left:  $( 'input[data-name="'+name+'-left"]', $field ).val(),
                        link:  $( 'input[data-name="'+name+'-link"]', $field ).is(':checked') ? 1 : ''
                    };
                    break;
                case 'radio':
                    value = $( 'input[data-name="'+name+'"]:checked', $field ).val();
                    break;
                case 'checkbox':
                    value = $( 'input[data-name="'+name+'"]', $field ).is(':checked') ? 1 : '' ;
                    break;
                default:
                        value = $( '[data-name="'+name+'"]', $field ).val();
                    break;
            }

            return value;

        },
        updateRepeaterLiveTitle: function( value, $item, field ){
            console.log( 'Live Title:', value );
            $( '._beacon--repeater-live-title', $item ).text( value );
        },
        getValue: function( save ){
            var control = this;
            var value = '';
            switch ( control.params.setting_type ) {
                case 'group':
                    value = {};
                    _.each( control.params.fields, function( f ){
                        var $_field = $( '[data-field-name="'+f.name+'"]', control.container ).closest('._beacon--field');
                        value[ f.name ] = control.getFieldValue( f.name, f.type, $_field );
                    } );
                    //console.log( 'GROUP_VALUE' );
                    control.initConditional( control.container, value );
                    break;
                case 'repeater':
                    value = [];
                    $( '._beacon--repeater-item', control.container ).each( function( index ){
                        var $item = $( this );
                        var _v = {};
                        _.each( control.params.fields, function( f ){
                            var inputField = $( '[data-field-name="'+f.name+'"]', $item );
                            var $_field = inputField.closest('._beacon--field');
                            var _fv =  control.getFieldValue( f.name, f.type, $_field );
                            _v[ f.name ] = _fv;

                            // Update Live title
                            if ( control.params.live_title_field == f.name ) {
                                if ( inputField.prop("tagName") == 'select' ) {
                                    _fv = $( 'option[value="'+_fv+'"]' ).first().text();
                                }
                                if ( _.isUndefined( _fv ) || _fv == '' ){
                                    _fv = control.params.l10n.untitled;
                                }
                                control.updateRepeaterLiveTitle( _fv, $item, f );
                            }

                        } );

                        control.initConditional( $item, _v );

                        value[index] = _v;

                    } );
                    break;
                default:
                    value = this.getFieldValue( control.id );
                    break;
            }

            console.log( 'getValue:', value );
            if ( _.isUndefined( save ) || save ) {
                control.setting.set( encodeURI( JSON.stringify( value ) ) );
            }
            return value;
        },
        initGroup: function(){
            var control = this;
            var template = control.getTemplate();
            var $fields = template( control.params.fields , 'tmpl-customize-control-'+control.type+'-fields');
            control.container.find( '._beacon--settings-fields' ).html( $fields );
            control.getValue( false );
        },
        initField: function( ){
            var control = this;
            var template = control.getTemplate();
            var field = {
                type: control.params.setting_type,
                name: control.id,
                value: control.params.value
            };

            if ( control.params.setting_type == 'select' || control.params.setting_type == 'radio' ) {
                field.choices = control.params.choices;
            }
            if ( control.params.setting_type == 'checkbox' ) {
                field.label = control.params.checkbox_label;
            }

            var $fields = template( [ field ] , 'tmpl-customize-control-'+control.type+'-fields');
            control.container.find( '._beacon--settings-fields' ).html( $fields );
        },
        initTabs: function(){
        },
        addRepeaterItem: function( value ){
            if ( ! _.isObject( value ) ) {
                value = {};
            }

            var control = this;
            var template = control.getTemplate();

            var fields = control.params.fields;
            _.each( fields, function( f, index ){
                fields[index].value = '';
                if ( ! _.isUndefined( value[ f.name ] ) ) {
                    fields[index].value = value[ f.name ];
                }
            } );

            var htmlSettings = template( fields , 'tmpl-customize-control-'+control.type+'-fields');
            var $itemWrapper = $( template( htmlSettings , 'tmpl-customize-control-'+control.type+'-repeater') );
            control.container.find( '._beacon--settings-fields' ).append( $itemWrapper );
            $itemWrapper.find( '._beacon--repeater-live-title' ).html( control.params.l10n.untitled );
            control.initColor( $itemWrapper );
            $document.trigger('_beacon/customizer/repeater/add', [ $itemWrapper, control ] );
            return $itemWrapper;
        },
        initRepeater: function(){
            var control = this;
            // Sortable
            control.container.find( '._beacon--settings-fields' ).sortable({
                handle: '._beacon--repeater-item-heading',
                containment: "parent"
            });

            // Add item when customizer loaded
            if ( _.isArray( control.params.value ) ) {
                console.log( 'control.params.value', control.params.value );
                _.each(  control.params.value, function( itemValue ){
                    control.addRepeaterItem( itemValue );
                } );
                control.getValue( false );
            }

            // Toggle
            control.container.on( 'click', '._beacon--repeater-item-toggle', function(e){
                e.preventDefault();
                var  p = $( this ).closest('._beacon--repeater-item');
                p.toggleClass('_beacon--open');
            } );

            // Remove
            control.container.on( 'click', '._beacon--remove', function(e){
                e.preventDefault();
                var  p = $( this ).closest('._beacon--repeater-item');
                p.remove();
                $document.trigger('_beacon/customizer/repeater/remove', [ control ] );
                control.getValue();
            } );

            // Add Item
            control.container.on( 'click', '._beacon--repeater-add-new', function(e){
                e.preventDefault();
                control.addRepeaterItem();
                control.getValue();
            } );
        }

    });


    wpcustomize.bind( 'ready', function( e, b ) {

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

        $document.on( 'click', '._beacon--tab-device-mobile', function(e){
            e.preventDefault();
            $document.trigger( '_beacon/customizer/device/change',['mobile'] );
        } );

        $document.on( 'click', '._beacon--tab-device-general', function(e){
            e.preventDefault();
            $document.trigger( '_beacon/customizer/device/change',['general'] );
        } );

        $( '.accordion-section' ).each( function(){
            var s = $( this );
            var t = $( '._beacon--device-select', s ).first();
            $( '.customize-section-title', s ).append( t );
        } );



    } );

    $document.ready( function( $ ){

    } );

})( jQuery, wp.customize || null );