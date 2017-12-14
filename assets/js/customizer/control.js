(function( $, wpcustomize ) {
    'use strict';

    var $document = $( document );

    var CustomifyMedia =  {
        setAttachment: function( attachment ){
            this.attachment = attachment;
        },
        addParamsURL: function( url, data ) {
            if ( ! $.isEmptyObject(data) )
            {
                url += ( url.indexOf('?') >= 0 ? '&' : '?' ) + $.param(data);
            }
            return url;
        },
        getThumb: function( attachment ){
            var control = this;
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
            $( '.customify-image-preview', this.preview ).addClass( 'customify--has-file' ).html(  '<img src="'+url+'" alt="">' );
            $( '.attachment-url', this.preview ).val( this.toRelativeUrl( url ) );
            $( '.attachment-mime', this.preview ).val( mime );
            $( '.attachment-id', this.preview ).val( id ).trigger( 'change' );
            this.preview.addClass( 'attachment-added' );
            this.showChangeBtn();

        },
        toRelativeUrl: function( url ){
            return url;
            //return url.replace( Customify_Control_Args.home_url, '' );
        },
        showChangeBtn: function(){
            $( '.customify--add', this.preview ).addClass( 'customify--hide' );
            $( '.customify--change', this.preview ).removeClass( 'customify--hide' );
            $( '.customify--remove', this.preview ).removeClass( 'customify--hide' );
        },
        insertVideo: function(attachment ){
            if ( typeof attachment !== "undefined" ) {
                this.attachment = attachment;
            }

            var url = this.getURL();
            var id = this.getID();
            var mime = this.attachment.mime;
            var html = '<video width="100%" height="" controls><source src="'+url+'" type="'+mime+'">Your browser does not support the video tag.</video>';
            $( '.customify-image-preview', this.preview ).addClass( 'customify--has-file' ).html( html );
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

            $( '.customify-image-preview', this.preview ).addClass( 'customify--has-file' ).html( '<a href="'+url+'" class="attachment-file" target="_blank">'+basename+'</a>' );
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
            $( '.customify-image-preview', this.preview ).removeAttr( 'style').html( '' ).removeClass( 'customify--has-file' );
            $( '.attachment-url', this.preview ).val( '' );
            $( '.attachment-mime', this.preview ).val( '' );
            $( '.attachment-id', this.preview ).val( '' ).trigger( 'change' );
            this.preview.removeClass( 'attachment-added' );

            $( '.customify--add', this.preview ).removeClass( 'customify--hide' );
            $( '.customify--change', this.preview ).addClass( 'customify--hide' );
            $( '.customify--remove', this.preview ).addClass( 'customify--hide' );
        }

    };

    CustomifyMedia.controlMediaImage = wp.media({
        title: wp.media.view.l10n.addMedia,
        multiple: false,
        library: {type: 'image' }
    });

    CustomifyMedia.controlMediaImage.on('select', function () {
        var attachment = CustomifyMedia.controlMediaImage.state().get('selection').first().toJSON();
        CustomifyMedia.insertImage( attachment );
    });

    CustomifyMedia.controlMediaVideo = wp.media({
        title: wp.media.view.l10n.addMedia,
        multiple: false,
        library: {type: 'video' }
    });

    CustomifyMedia.controlMediaVideo.on('select', function () {
        var attachment = CustomifyMedia.controlMediaVideo.state().get('selection').first().toJSON();
        CustomifyMedia.insertVideo( attachment );
    });

    CustomifyMedia.controlMediaFile = wp.media({
        title: wp.media.view.l10n.addMedia,
        multiple: false
    });

    CustomifyMedia.controlMediaFile.on('select', function () {
        var attachment = CustomifyMedia.controlMediaFile.state().get('selection').first().toJSON();
        CustomifyMedia.insertFile( attachment );
    });









    wp.customize.controlConstructor.customify = wp.customize.Control.extend({

        // When we're finished loading continue processing
        ready: function() {
            var control = this;
            control.init();
        },
        type: 'customify',
        settingField: null,
        devices:  ['desktop', 'tablet', 'mobile'],
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

            return function (data, id, data_variable_name ) {
                if (_.isUndefined(id)) {
                    id = 'tmpl-customize-control-' + control.type;
                }
                if ( ! _.isUndefined( data_variable_name ) && _.isString( data_variable_name ) ) {
                    options.variable = data_variable_name;
                } else {
                    options.variable = 'data';
                }
                compiled = _.template($('#' + id).html(), null, options);
                return compiled(data);
            };

        }),
        addDeviceSwitchers: function( $el ){
            var control = this;
            if ( _.isUndefined( $el ) ) {
                $el = control.container;
            }
            var clone = $('#customize-footer-actions .devices').clone();
            clone.addClass('customify-devices');
            $('.customify-field-heading', $el ).append(clone).addClass( 'customify-devices-added' );

        },
        init: function() {
            var control = this;
            // The hidden field that keeps the data saved (though we never update it)
            control.settingField = control.container.find( '[data-customize-setting-link]' ).first();

            control.initTabs();
            if ( control.params.device_settings ) {
                control.addDeviceSwitchers();
            }

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
            control.initSlider( control.container );
            control.initCSSRuler();

        },
        addParamsURL: function( url, data ) {
            if ( ! $.isEmptyObject(data) )
            {
                url += ( url.indexOf('?') >= 0 ? '&' : '?' ) + $.param(data);
            }

            return url;
        },
        initMedia: function(){
            var control = this;

            // When add/Change
            control.container.on( 'click',  '.customify--media .customify--add, .customify--media .customify--change, .customify--media .customify-image-preview', function( e ) {
                e.preventDefault();
                var p = $( this ).closest('.customify--media');
                CustomifyMedia.setPreview( p )  ;
                CustomifyMedia.controlMediaImage.open();
            } );

            // When add/Change
            control.container.on( 'click',  '.customify--media .customify--remove', function( e ) {
                e.preventDefault();
                var p = $( this ).closest('.customify--media');
                CustomifyMedia.remove( p );
            } );
        },
        initCSSRuler: function(){
            var control = this;
            control.container.on( 'change', '.customify--label-parent', function(){
                if ( $( this ).attr( 'type' ) == 'radio' ){
                    var name = $( this ).attr( 'name' );
                    $( 'input[name="'+name+'"]', control.container ).parent().removeClass('customify--label-active');
                }
                var checked = $( this ).is( ':checked' );
                if ( checked ) {
                    $( this ).parent().addClass( 'customify--label-active' );
                } else {
                    $( this ).parent().removeClass( 'customify--label-active' );
                }

                control.getValue();
            } );


            control.container.on( 'change keyup', '.customify--css-ruler .customify-input-css', function(){
                var p = $( this ).closest('.customify--css-ruler');
                var link_checked = $( '.customify--css-ruler-link input', p ).is( ':checked' );
                if ( link_checked ) {
                    var v = $( this ).val();
                    $( '.customify-input-css', p ).not( $( this ) ).val(v);
                }

                control.getValue();
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
                case 'empty':
                    var _v =  _.clone( value1 );
                    if ( _.isObject( _v ) || _.isArray( _v ) ) {
                        _.each( _v, function ( v, i ) {
                            if ( _.isEmpty( v ) ) {
                                delete _v[ i ];
                            }
                        } );

                        equal = _.isEmpty( _v ) ? true: false;
                    } else {
                        equal = _.isNull( _v ) || _v == '' ? true : false;
                    }


                    break;
                case 'not_empty':
                    var _v =  _.clone( value1 );
                    if ( _.isObject( _v ) || _.isArray( _v ) ) {
                        _.each( _v, function ( v, i ) {
                            if ( _.isEmpty( v ) ) {
                                delete _v[ i ];
                            }
                        } )
                    }
                    equal = _.isEmpty( _v ) ? false : true;
                    break;
                default:
                    equal = ( value1 == value2 ) ? true : false;

            }
            return equal;
        },
        multiple_compare: function( list, values, decodeValue ){
            if ( _.isUndefined( decodeValue ) ) {
                decodeValue = false;
            }
            var control = this;
            try {
                var test =  list[0];
                var check = true;
                if ( _.isString( test ) ) {
                    check = false;
                    var cond = list[1];
                    var cond_val = list[2];
                    var cond_device = false;
                    if ( ! _.isUndefined( list[3] ) ) { // can be desktop, tablet, mobile
                        cond_device = list[3];
                    }
                    var value;
                    if ( ! _.isUndefined( values[ test ] ) ) {
                        value = values[ test ];
                        if ( cond_device ) {
                            if ( _.isObject( value ) && !_.isUndefined( value[ cond_device ] ) ) {
                                value =  value[ cond_device ];
                            }
                        }
                        try {
                            if ( decodeValue ) {
                                value = control.decodeValue( value )
                            }
                        } catch ( e ) {

                        }

                        check = control.compare( value, cond, cond_val );
                    }

                } else if ( _.isArray( test ) ) {
                    check  = true;
                    _.each( list, function( req ) {
                        var cond_key = req[0];
                        var cond_cond = req[1];
                        var cond_val = req[2];
                        var cond_device = false;
                        if ( ! _.isUndefined( req[3] ) ) { // can be desktop, tablet, mobile
                            cond_device = req[3];
                        }

                        var t_val = values[ cond_key ];

                        if ( _.isUndefined( t_val ) ) {
                            t_val = '';
                        }
                        if ( decodeValue ) {
                            t_val = control.decodeValue( t_val )
                        }

                        if ( cond_device ) {
                            if ( _.isObject( t_val ) && !_.isUndefined( t_val[ cond_device ] ) ) {
                                t_val =  t_val[ cond_device ];
                            }
                        }

                        if ( ! control.compare( t_val, cond_cond, cond_val ) ) {
                            check = false;
                        }
                    } );

                }
            } catch  ( e ) {
                //console.log( 'Trying_test_error', e  );
            }


            return check;
        },
        initConditional: function ( $el, values ){
            var control = this;
            var $fields  = $( '.customify--field', $el );
            $fields.each( function( ) {
                var $field = $(this);
                var check = true;
                var req = $field.attr('data-required') || false;
                if ( !_.isUndefined( req ) && req ) {
                    req = JSON.parse( req );
                    check = control.multiple_compare( req, values );
                    if ( ! check ) {
                        $field.addClass( 'customify--hide' );
                    } else {
                        $field.removeClass( 'customify--hide' );
                    }
                }
            });
        },
        initColor: function( $el ){

            $( '.customify-input-color', $el ).each( function(){
                var colorInput = $( this );
                var df = colorInput.data( 'default' ) || '';
                var current_val = $( '.customify-input', colorInput ).val();
                // data-alpha="true"
                $( '.customify--color-panel', colorInput ).attr( 'data-alpha', 'true' );
                $( '.customify--color-panel', colorInput ).wpColorPicker({
                    defaultColor: df,
                    change: function( event, ui ){
                        var new_color = ui.color.toString();
                        $( '.customify-input', colorInput ).val( new_color );
                        if( ui.color.toString() !== current_val ) {
                            current_val = new_color;
                            $( '.customify-input', colorInput ).trigger('change');
                        }
                    },
                    clear: function( event, ui ){
                        $( '.customify-input', colorInput ).val( '' );
                        $( '.customify-input', colorInput ).trigger('data-change');
                    }

                });
            } );
        },
        initSlider: function( $el ){
            if ( $( '.customify-input-slider', $el ).length > 0 ) {
                $('.customify-input-slider', $el ).each( function(){
                    var slider = $( this );
                    var p = slider.parent();
                    var input = $( '.customify--slider-input', p );
                    var min = slider.data( 'min' ) || 0;
                    var max = slider.data( 'max' ) || 300;
                    if ( !_.isNumber( min ) ) {
                        min = 0;
                    }

                    if ( !_.isNumber( max ) ) {
                        max = 300;
                    }

                    var current_val = input.val();
                    slider.slider({
                        range: "min",
                        value: current_val,
                        step: 1,
                        min: min,
                        max: max,
                        slide: function (event, ui) {
                            input.val( ui.value ).trigger('data-change');
                        }
                    });

                    input.on( 'change', function(){
                        slider.slider( "value", $( this ).val() );
                    } );

                    // Reset
                    var wrapper = slider.closest('.customify-input-slider-wrapper');
                    wrapper.on( 'click', '.reset', function( e ) {
                        e.preventDefault();
                        var d = slider.data('default');
                        if ( ! _.isObject( d ) ) {
                            d = {
                                'unit': 'px',
                                'value': ''
                            }
                        }

                        $( '.customify--slider-input', wrapper ).val( d.value);
                        slider.slider( "option", "value", d.value );
                        $( '.customify--css-unit input.customify-input[value="'+d.unit+'"]', wrapper ).trigger('click');
                        $( '.customify--slider-input', wrapper ).trigger( 'change' );

                    });

                } );
            }
        },
        getFieldValue: function( name, fieldSetting, $field ){
            var control = this;
            var type = undefined ;
            var support_devices = false;
            if ( _.isUndefined( $field ) ) {
                $field = control.container.find( '.customify--settings-fields .customify--field' ).first();
            }

            if ( ! _.isUndefined( fieldSetting ) ) {
                type = fieldSetting.type;
                support_devices = fieldSetting.device_settings;
            }

            if ( _.isUndefined( type ) || ! type ) {
                type = control.params.setting_type;
                support_devices = control.params.device_settings;
            }

            var value = '';
            switch ( type ) {
                case 'media':
                case 'image':
                case 'video':
                case 'attachment':
                case 'audio':
                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var _name = name+'-'+device;
                            value[ device ] = {
                                id:  $( 'input[data-name="'+_name+'"]', $field ).val(),
                                url:  $( 'input[data-name="'+_name+'-url"]', $field ).val(),
                                mime:  $( 'input[data-name="'+_name+'-mime"]', $field ).val()
                            };
                        } );
                    } else {
                        value = {
                            id:  $( 'input[data-name="'+name+'"]', $field ).val(),
                            url:  $( 'input[data-name="'+name+'-url"]', $field ).val(),
                            mime:  $( 'input[data-name="'+name+'-mime"]', $field ).val()
                        };
                    }

                break;
                case 'css_ruler':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var _name = name+'-'+device;
                            value[ device ] = {
                                unit:  $( 'input[data-name="'+_name+'-unit"]:checked', $field ).val(),
                                top:  $( 'input[data-name="'+_name+'-top"]', $field ).val(),
                                right:  $( 'input[data-name="'+_name+'-right"]', $field ).val(),
                                bottom:  $( 'input[data-name="'+_name+'-bottom"]', $field ).val(),
                                left:  $( 'input[data-name="'+_name+'-left"]', $field ).val(),
                                link:  $( 'input[data-name="'+_name+'-link"]', $field ).is(':checked') ? 1 : ''
                            };
                        } );
                    } else {
                        value = {
                            unit:  $( 'input[data-name="'+name+'-unit"]:checked', $field ).val(),
                            top:  $( 'input[data-name="'+name+'-top"]', $field ).val(),
                            right:  $( 'input[data-name="'+name+'-right"]', $field ).val(),
                            bottom:  $( 'input[data-name="'+name+'-bottom"]', $field ).val(),
                            left:  $( 'input[data-name="'+name+'-left"]', $field ).val(),
                            link:  $( 'input[data-name="'+name+'-link"]', $field ).is(':checked') ? 1 : ''
                        };
                    }

                    break;
                case 'font_style':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var _name = name+'-'+device;
                            value[ device ] = {
                                b:  $( 'input[data-name="'+_name+'-b"]', $field ).is(':checked') ? 1 : '',
                                i: $( 'input[data-name="'+_name+'-i"]', $field ).is(':checked') ? 1 : '',
                                u:  $( 'input[data-name="'+_name+'-u"]', $field ).is(':checked') ? 1 : '',
                                s: $( 'input[data-name="'+_name+'-s"]', $field ).is(':checked') ? 1 : '',
                                t: $( 'input[data-name="'+_name+'-t"]', $field ).is(':checked') ? 1 : ''
                            };
                        } );
                    } else {
                        value = {
                            b:  $( 'input[data-name="'+name+'-b"]', $field ).is(':checked') ? 1 : '',
                            i: $( 'input[data-name="'+name+'-i"]', $field ).is(':checked') ? 1 : '',
                            u:  $( 'input[data-name="'+name+'-u"]', $field ).is(':checked') ? 1 : '',
                            s: $( 'input[data-name="'+name+'-s"]', $field ).is(':checked') ? 1 : '',
                            t: $( 'input[data-name="'+name+'-t"]', $field ).is(':checked') ? 1 : ''
                        };
                    }

                    break;
                case 'font':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var _name = name+'-'+device;
                            var subsets = {};
                            $( '.list-subsets[data-name="'+_name+'-subsets"] input', $field ).each ( function(){
                                if ( $(this ).is(':checked') ) {
                                    var _v = $( this ).val();
                                    subsets[ _v ] = _v;
                                }
                            } );
                            value[ device ] = {
                                font:  $( 'select[data-name="'+_name+'-font"]', $field ).val(),
                                type:  $( 'input[data-name="'+_name+'-type"]', $field ).val(),
                                variant:  $( 'select[data-name="'+_name+'-variant"]', $field ).val(), // variant
                                subsets:  subsets
                            };
                        } );
                    } else {
                        var subsets = {};
                        $( '.list-subsets[data-name="'+name+'-subsets"] input', $field ).each ( function(){
                            if ( $(this ).is(':checked') ) {
                                var _v = $( this ).val();
                                subsets[ _v ] = _v;
                            }
                        } );
                        value = {
                            font:  $( 'select[data-name="'+name+'-font"]', $field ).val(),
                            type:  $( 'input[data-name="'+name+'-type"]', $field ).val(),
                            variant:  $( 'select[data-name="'+name+'-variant"]', $field ).val(),
                            subsets:  subsets
                        };
                    }

                    break;
                case 'slider':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var _name = name+'-'+device;
                            value[ device ] = {
                                unit:  $( 'input[data-name="'+_name+'-unit"]:checked', $field ).val(),
                                value:  $( 'input[data-name="'+_name+'-value"]', $field ).val()
                            };
                        } );
                    } else {
                        value = {
                            unit:  $( 'input[data-name="'+name+'-unit"]:checked', $field ).val(),
                            value:  $( 'input[data-name="'+name+'-value"]', $field ).val()
                        };
                    }

                    break;
                case 'icon':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var _name = name+'-'+device;
                            value[ device ] = {
                                type:  $( 'input[data-name="'+_name+'-type"]', $field ).val(),
                                icon:  $( 'input[data-name="'+_name+'"]', $field ).val()
                            };
                        } );
                    } else {
                        value = {
                            type:  $( 'input[data-name="'+name+'-type"]', $field ).val(),
                            icon:  $( 'input[data-name="'+name+'"]', $field ).val()
                        };
                    }
                    break;
                case 'radio':
                case 'text_align':
                case 'text_align_no_justify':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            var input = $( 'input[data-name="'+name+'-'+device+'"]:checked', $field );
                            value[ device ] = input.length ? input.val() : '' ;
                        } );
                    } else {
                        value = $( 'input[data-name="'+name+'"]:checked', $field ).val();
                    }

                    break;
                case 'checkbox':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            value[ device ] = $( 'input[data-name="'+name+'-'+device+'"]', $field ).is(':checked') ? 1 : '' ;
                        } );
                    } else {
                        value = $( 'input[data-name="'+name+'"]', $field ).is(':checked') ? 1 : '' ;
                    }

                    break;
                default:
                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            value[ device ] = $( '[data-name="'+name+'-'+device+'"]', $field ).val();
                        } );
                    } else {
                        value = $( '[data-name="'+name+'"]', $field ).val();
                    }
                    break;
            }

            return value;

        },
        getValue: function( save ){
            var control = this;
            var value = '';
            switch ( control.params.setting_type ) {
                case 'group':
                    value = {};

                    if ( control.params.device_settings ) {
                        _.each( control.devices, function( device ){
                            var $area = $( '.customify-group-device-fields.customify--for-'+device, control.container );
                            value[ device ] = {};
                            var _value = {};
                            _.each( control.params.fields, function( f ){
                                var $_field = $( '.customify--group-field[data-field-name="'+f.name+'"]', $area );
                                _value[ f.name ] = control.getFieldValue( f.name, f, $_field );
                            } );
                            value[ device ] = _value;
                            control.initConditional( $area, _value );

                        } );
                    } else {
                        _.each( control.params.fields, function( f ){
                            var $_field = $( '.customify--group-field[data-field-name="'+f.name+'"]', control.container );
                            value[ f.name ] = control.getFieldValue( f.name, f, $_field );
                        } );

                        control.initConditional( control.container, value );
                    }
                    //console.log( 'GROUP_VALUE' );
                    break;
                case 'repeater':
                    value = [];
                    $( '.customify--repeater-item', control.container ).each( function( index ){
                        var $item = $( this );
                        var _v = {};
                        _.each( control.params.fields, function( f ){
                            var inputField = $( '[data-field-name="'+f.name+'"]', $item );
                            //var $_field = inputField.closest('.customify--field');
                            //var $_field = inputField.closest('.customify--repeater-field');
                            var _fv =  control.getFieldValue( f.name, f,  $item );
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

            if ( _.isUndefined( save ) || save ) {
               //console.log( 'VALUES: ', value );
                control.setting.set( control.encodeValue( value ) );
                $document.trigger( 'customify/customizer/change' );
            } else {

            }

           // console.log( 'All Value: ', wpcustomize.get( ) );
            return value;
        },
        encodeValue: function( value ){
            return encodeURI( JSON.stringify( value ) )
        },
        decodeValue: function( value ){
            return JSON.parse( decodeURI( value ) );
        },
        updateRepeaterLiveTitle: function( value, $item, field ){
            $( '.customify--repeater-live-title', $item ).text( value );
        },
        initGroup: function(){
            var control = this;
            if ( control.params.device_settings ) {
                control.container.find( '.customify--settings-fields' ).addClass( 'customify--multiple-devices' );
                if ( ! _.isObject( control.params.value ) ) {
                    control.params.value = {};
                }

                _.each( control.devices , function( device, device_index ){
                    var $group_device = $( '<div class="customify-group-device-fields customify-field-settings-inner customify--for-'+device+'"></div>' );
                    control.container.find( '.customify--settings-fields' ).append( $group_device );
                    var device_value = {};
                    if ( ! _.isUndefined( control.params.value[ device] ) ) {
                        device_value = control.params.value[ device ];
                    }
                    if ( ! _.isObject( device_value ) ) {
                        device_value = {};
                    }

                    _.each( control.params.fields, function( f, index ){
                        var $fieldArea = $( '<div class="customify--group-field" data-field-name="'+f.name+'"></div>' );
                        $group_device.append( $fieldArea );
                        f.device_settings = false;
                        f.value = device_value[ f.name ];
                        control.addField( f, $fieldArea );
                    } );

                });

            } else {
                _.each( control.params.fields, function( f, index ){
                    var $fieldArea = $( '<div class="customify--group-field" data-field-name="'+f.name+'"></div>' );
                    control.container.find( '.customify--settings-fields' ).append( $fieldArea );
                    f.original_name = f.name;
                    control.addField( f, $fieldArea );

                    if ( ! _.isUndefined( f.device_settings ) && f.device_settings  ) {
                        control.addDeviceSwitchers( $fieldArea );
                    }
                } );
            }

            control.getValue( false );
        },
        addField: function( field, $fieldsArea ){
            var control = this;
            var template = control.getTemplate();
            var template_id =  'tmpl-field-'+control.type+'-'+field.type;
            if (  $( '#'+template_id ).length == 0 ) {
                template_id =  'tmpl-field-'+control.type+'-text';
            }
            if ( field.device_settings ) {

                var fieldItem =  null;
                _.each( control.devices , function( device, index ){

                    var _field = _.clone( field );
                    _field.original_name = field.name;
                    if ( _.isObject( field.value ) ){
                        if ( ! _.isUndefined( field.value[device] ) ) {
                            _field.value = field.value[device];
                        } else {
                            _field.value = '';
                        }
                    } else {
                        _field.value = '';
                        if ( index === 0 ) {
                            _field.value = field.value;
                        }
                    }
                    _field.name =  field.name+'-'+device;

                    var $deviceFields = $( template( _field , template_id, 'field' ) );
                    var deviceFieldItem = $deviceFields.find( '.customify-field-settings-inner' ).first();

                    if ( ! fieldItem ) {
                        $fieldsArea.append( $deviceFields ).addClass( 'customify--multiple-devices' );
                    }

                    deviceFieldItem.addClass( 'customify--for-'+device );
                    deviceFieldItem.attr( 'data-for-device', device );

                    if ( fieldItem ) {
                        deviceFieldItem.insertAfter( fieldItem );
                        fieldItem = deviceFieldItem;
                    }
                    fieldItem = deviceFieldItem;

                }) ;
            } else {
                field.original_name = field.name;
                var $fields = template( field , template_id, 'field' );
                $fieldsArea.html( $fields );
            }
        },
        initField: function( ){
            var control = this;
            //console.log( 'control.params - '+ control.id, control.params );
            var field = {
                type: control.params.setting_type,
                name: control.id,
                value: control.params.value,
                default: control.params.default,
            };

            if ( field.type == 'slider' ) {
                field.min = control.params.min;
                field.max = control.params.max;
            }

            if ( control.params.setting_type == 'select' || control.params.setting_type == 'radio' ) {
                field.choices = control.params.choices;
            }
            if ( control.params.setting_type == 'checkbox' ) {
                field.checkbox_label = control.params.checkbox_label;
            }

            field.device_settings = control.params.device_settings;
            var $fieldsArea = control.container.find('.customify--settings-fields');

            control.addField( field, $fieldsArea );

        },
        initTabs: function(){},
        addRepeaterItem: function( value ){
            if ( ! _.isObject( value ) ) {
                value = {};
            }

            var control = this;
            var template = control.getTemplate();
            var fields = control.params.fields;

            var $itemWrapper = $( template( '' , 'tmpl-customize-control-'+control.type+'-repeater') );
            control.container.find( '.customify--settings-fields' ).append( $itemWrapper );
            _.each( fields, function( f, index ){
                f.value = '';
                if ( ! _.isUndefined( value[ f.name ] ) ) {
                    f.value = value[ f.name ];
                }
                var $fieldArea = $( '<div class="customify--repeater-field"></div>' );
                $( '.customify--repeater-item-inner', $itemWrapper ).append( $fieldArea );
                control.addField( f, $fieldArea );

                if ( ! _.isUndefined( f.device_settings ) && f.device_settings  ) {
                    control.addDeviceSwitchers( $fieldArea );
                }
            } );

            $itemWrapper.find( '.customify--repeater-live-title' ).html( control.params.l10n.untitled );

            control.initColor( $itemWrapper );
            control.initSlider( $itemWrapper );
            $document.trigger('customify/customizer/repeater/add', [ $itemWrapper, control ] );
            return $itemWrapper;
        },
        limitRepeaterItems: function(){
            var control = this;

            var addButton = $( '.customify--repeater-add-new', control.container );
            var c = $( '.customify--settings-fields .customify--repeater-item', control.container ).length;

            if ( control.params.limit > 0 ) {
                if ( c >= control.params.limit ) {
                    addButton.addClass( 'customify--hide' );
                    if ( control.params.limit_msg ) {
                        if ( $( '.customify--limit-item-msg', control.container ).length === 0 ) {
                            $( '<p class="customify--limit-item-msg">'+control.params.limit_msg+'</p>' ).insertBefore( addButton );
                        } else {
                            $( '.customify--limit-item-msg', control.container ).removeClass( 'customify--hide' );
                        }

                    }
                } else {
                    $( '.customify--limit-item-msg', control.container ).addClass( 'customify--hide' );
                    addButton.removeClass( 'customify--hide' );
                }
            }

            if ( c > 0 ) {
                $( '.customify--repeater-reorder', control.container ).removeClass('customify--hide');
            } else {
                $( '.customify--repeater-reorder', control.container ).addClass('customify--hide');
            }

        },
        initRepeater: function(){
            var control = this;
            control.params.limit = parseInt( control.params.limit );
            if ( isNaN( control.params.limit ) ) {
                control.params.limit = 0;
            }

            // Sortable
            control.container.find( '.customify--settings-fields' ).sortable({
                handle: '.customify--repeater-item-heading',
                containment: "parent"
            });

            // Toggle Move
            control.container.on( 'click', '.customify--repeater-reorder', function ( e ) {
                e.preventDefault();
                $( '.customify--repeater-items', control.container ).toggleClass('reorder-active');
                $( '.customify--repeater-add-new', control.container ).toggleClass('disabled');
                if ( $( '.customify--repeater-items', control.container ).hasClass( 'reorder-active' ) ) {
                    $( this ).html( $( this ).data( 'done' ) );
                } else {
                    $( this ).html( $( this ).data( 'text' ) );
                }
            } );

            // Move Up
            control.container.on( 'click', '.customify--repeater-item .customify--up', function( e ){
                e.preventDefault();
                var i = $( this ).closest('.customify--repeater-item');
                var index = i.index();
                if ( index > 0 ) {
                    var up =  i.prev();
                    i.insertBefore( up );
                    control.getValue();
                }
            } );

            control.container.on( 'click', '.customify--repeater-item .customify--down', function( e ){
                e.preventDefault();
                var n = $( '.customify--repeater-items .customify--repeater-item', control.container ).length;
                var i = $( this ).closest('.customify--repeater-item');
                var index = i.index();
                if ( index < n - 1 ) {
                    var down =  i.next();
                    i.insertAfter( down );
                    control.getValue();
                }
            } );



            // Add item when customizer loaded
            if ( _.isArray( control.params.value ) ) {
                _.each(  control.params.value, function( itemValue ){
                    control.addRepeaterItem( itemValue );
                } );
                control.getValue( false );
            }
            control.limitRepeaterItems();

            // Toggle
            control.container.on( 'click', '.customify--repeater-item-toggle, .customify--repeater-item-heading', function(e){
                e.preventDefault();
                var  p = $( this ).closest('.customify--repeater-item');
                p.toggleClass('customify--open');
            } );

            // Remove
            control.container.on( 'click', '.customify--remove', function(e){
                e.preventDefault();
                var  p = $( this ).closest('.customify--repeater-item');
                p.remove();
                $document.trigger('customify/customizer/repeater/remove', [ control ] );
                control.getValue();
                control.limitRepeaterItems();
            } );


            var defaultValue = {};
            _.each( control.params.fields , function( f, k ){
                defaultValue[ f.name ] = null;
                if ( !_.isUndefined( f.default ) ) {
                    defaultValue[ f.name ] = f.default;
                }
            } );

            // Add Item
            control.container.on( 'click', '.customify--repeater-add-new', function(e){
                e.preventDefault();
                if ( ! $( this ).hasClass( 'disabled' ) ) {
                    control.addRepeaterItem( defaultValue );
                    control.getValue();
                    control.limitRepeaterItems();
                }
            } );
        }

    });


    var IconPicker = {
        pickingEl: null,
        render: function(){
            var that = this;
            if ( !_.isUndefined( Customify_Control_Args.icons ) && !_.isEmpty( Customify_Control_Args.icons ) ) {
                _.each( Customify_Control_Args.icons, function( icon_config, font_type ) {
                    $( '#customify--sidebar-icon-type' ).append( ' <option value="'+font_type+'">'+icon_config.name+'</option>' );
                    that.addCSS( icon_config, font_type );
                    that.addIcons( icon_config, font_type );
                } );
            }
        },

        addCSS: function( icon_config, font_type ){
            $( 'head' ).append( "<link rel='stylesheet' id='font-icon-"+font_type+"'  href='"+icon_config.url+"' type='text/css' media='all' />" )
        },

        addIcons: function( icon_config, font_type ){
            var icon_html = '<ul class="customify--list-icons icon-'+font_type+'" data-type="'+font_type+'">';
            _.each( icon_config.icons, function( icon_class, i ){
                var class_name = '';
                if ( icon_config.class_config ) {
                    class_name = icon_config.class_config.replace(/__icon_name__/g, icon_class  );
                } else {
                    class_name = icon_class;
                }

                icon_html += '<li title="'+icon_class+'" data-type="'+font_type+'" data-icon="'+class_name+'"><span class="icon-wrapper"><i class="'+class_name+'"></i></span></li>';

            } );
            icon_html += '</ul>';

            $( '#customify--icon-browser').append( icon_html );
        },
        changeType: function(){
            $document.on( 'change', '#customify--sidebar-icon-type', function(){
                var type = $( this ).val();
                if ( ! type || type == 'all' ) {
                    $( '#customify--icon-browser .customify--list-icons' ).show();
                } else {
                    $( '#customify--icon-browser .customify--list-icons' ).hide();
                    $( '#customify--icon-browser .customify--list-icons.icon-'+type ).show();
                }
            } );
        },
        show: function () {
            var controlWidth = $( '#customize-controls' ).width();
            $( '#customify--sidebar-icons' ).css( 'left', controlWidth ).addClass( 'customify--active' );
        },
        close: function () {
            $( '#customify--sidebar-icons' ).css( 'left', -300 ).removeClass( 'customify--active' );
            $( '.customify--icon-picker' ).removeClass('customify--icon-picking');
            this.pickingEl = null;
        },
        autoClose: function(){
            var that = this;
            $document.on( 'click', function( event ) {
                if ( ! $(event.target).closest('.customify--icon-picker').length ) {
                    if ( ! $(event.target).closest('#customify--sidebar-icons').length ) {
                        that.close();
                    }
                }
            } );

            $( '#customify--sidebar-icons .customize-controls-icon-close' ).on( 'click', function(){
                that.close();
            } );

            $document.on( 'keyup', function( event ) {
                if (  event.keyCode === 27 ) {
                    that.close();
                }
            } );


        },
        picker: function(){
            var that = this;
            $document.on( 'click', '.customify--icon-picker .customify--pick-icon', function( e ) {
                e.preventDefault();
                if (  that.pickingEl ) {
                    that.pickingEl.removeClass('customify--icon-picking');
                }
                that.pickingEl =  $( this ).closest( '.customify--icon-picker' );
                that.pickingEl.addClass( 'customify--picking-icon' );
                that.show();
            } );


            $document.on( 'click', '#customify--icon-browser li', function( e ) {
                e.preventDefault();
                var li = $( this );
                var icon_preview = li.find( 'i' ).clone();
                var icon = li.attr( "data-icon" ) || '';
                var type = li.attr( 'data-type' ) || '';
                $( '.customify--input-icon-type', that.pickingEl ).val( type );
                $( '.customify--input-icon-name', that.pickingEl ).val( icon ).trigger( 'change' );
                $( '.customify--icon-preview-icon', that.pickingEl ).html( icon_preview );

                that.close();
            } );

            // remove
            $document.on( 'click', '.customify--icon-picker .customify--icon-remove', function( e ) {
                e.preventDefault();
                if (  that.pickingEl ) {
                    that.pickingEl.removeClass('customify--icon-picking');
                }
                that.pickingEl =  $( this ).closest( '.customify--icon-picker' );
                that.pickingEl.addClass( 'customify--picking-icon' );

                $( '.customify--input-icon-type', that.pickingEl ).val( '' );
                $( '.customify--input-icon-name', that.pickingEl ).val( '' ).trigger( 'change' );
                $( '.customify--icon-preview-icon', that.pickingEl ).html( '' );

            } );

        },
        init: function(){
            this.render();
            this.changeType();
            this.picker();
            this.autoClose();

            // Search icon
            $document.on( 'keyup', '#customify--icon-search', function( e ) {
                var v = $( this).val();
                v = v.trim();
                if ( v ) {
                    $( "#customify--icon-browser li" ).hide();
                    $( "#customify--icon-browser li[data-icon*='"+v+"']" ).show();
                } else {
                    $( "#customify--icon-browser li" ).show();
                }
            } );
        }
    };

    var FontSelector = {
        fonts: null,
        optionHtml: '',
        get: function(){
            var that = this;
            $.get( Customify_Control_Args.ajax, { action: 'customify/customizer/ajax/fonts'  }, function(res ){
                if ( res.success ) {
                    that.fonts = res.data;
                    that.ready()
                }
            } );
        },
        toSelectOptions: function ( options, v ){
            var html = '';
            if ( _.isUndefined( v ) ) {
                v = '';
            }
            _.each( options, function( value ) {
                var selected = '';
                if ( value === v ) {
                    selected = ' selected="selected" ';
                }
                html += '<option'+selected+' value="'+value+'">'+value+'</option>';
            } );
            return html;
        },
        toCheckboxes: function ( options, v ){
            var html = '';
            if ( ! _.isObject( v ) ) {
                v = {};
            }
            _.each( options, function( value ) {
                var checked = '';
                if ( ! _.isUndefined( v[ value ] ) ) {
                    checked = ' checked="checked" ';
                }
                html += '<p><label><input '+checked+'type="checkbox" value="'+value+'"> '+value+'</label></p>';
            } );
            return html;
        },
        ready: function(){
            var that = this;
            _.each( that.fonts, function( group, type ){
                // theme_default
                that.optionHtml += '<option value="">'+Customify_Control_Args.theme_default+'</option>';
                that.optionHtml += '<optgroup label="'+group.title+'">';
                    _.each( group.fonts, function( font, font_name ) {
                        that.optionHtml += '<option value="'+font_name+'">'+font_name+'</option>';
                    } );
                that.optionHtml += '</optgroup>';
            } );

            $( 'select.customify--font-families' ).html( that.optionHtml );
            $( 'select.customify--font-families' ).each( function(){
                var save_value = $( this ).data( 'value' );
                if ( ! _.isObject( save_value ) ) {
                    save_value = {};
                }
                var p = $( this ).closest('.customify-field-settings-inner');
                if ( save_value.font ) {
                    $( 'option[value="'+save_value.font+'"]', $( this ) ).attr( 'selected', 'selected' );
                }
                that.setUpFont( save_value, p );
            } );

            $document.on( 'change', 'select.customify--font-families', function(){
                var font =  $( this ).val();
                var p = $( this ).closest('.customify-field-settings-inner');
                that.setUpFont( font, p );
            } );
        },

        setUpFont: function( font, p ){
            var that = this;
            var font_settings, variants, subsets, type;

            if ( _.isEmpty( font ) ) {
                type = 'normal';
            }


            if (  _.isString( font ) ) {
                if ( ! _.isUndefined( that.fonts.google.fonts[ font ] ) ) {
                    type = 'google';
                } else {
                    type = 'normal';
                }

                font_settings = that.fonts.google.fonts[ font ];
            } else {
                font_settings = that.fonts.google.fonts[ font.font ];
            }

            if ( ! _.isUndefined( font_settings ) && ! _.isEmpty( font_settings ) ) {
                variants = font_settings.variants;
                subsets = font_settings.subsets;
            }

            if ( _.isObject( font ) && ! _.isUndefined( font.type ) ) {
                type = font.type;
            }


            if ( type == 'normal' ) {
                $( '.customify--font-variants-wrapper', p ).addClass( 'customify--hide').find('select').html('');
                $( '.customify--font-subsets-wrapper', p ).addClass( 'customify--hide').find( '.list-subsets' ).html('');
            } else {
                $( '.customify--font-type', p ).val( type );
                $( '.customify--font-variants-wrapper', p ).removeClass( 'customify--hide');
                $( '.customify--font-subsets-wrapper', p ).removeClass( 'customify--hide');
                $( '.customify--font-variants', p).html( that.toSelectOptions(variants, _.isObject( font ) ? font.variant : '' ) );
                $( '.list-subsets', p).removeClass('customify--hide').html( that.toCheckboxes(subsets, _.isObject( font ) ? font.subsets : '' ) );
            }

        },


        init: function(){
            this.get();
        }

    };


    wpcustomize.bind( 'ready', function( e, b ) {

        $document.on( 'customify/customizer/device/change', function( e, device ) {
            $( '.customify--device-select a' ).removeClass( 'customify--active' );
            if ( device != 'mobile' ) {
                $( '.customify--device-mobile').addClass( 'customify--hide' );
                $( '.customify--device-general' ).removeClass( 'customify--hide' );
                $( '.customify--tab-device-general' ).addClass('customify--active');
            } else {
                $( '.customify--device-general' ).addClass( 'customify--hide' );
                $( '.customify--device-mobile' ).removeClass( 'customify--hide' );
                $( '.customify--tab-device-mobile' ).addClass('customify--active');
            }
        } );

        $document.on( 'click', '.customify--tab-device-mobile', function(e){
            e.preventDefault();
            $document.trigger( 'customify/customizer/device/change',['mobile'] );
        } );

        $document.on( 'click', '.customify--tab-device-general', function(e){
            e.preventDefault();
            $document.trigger( 'customify/customizer/device/change',['general'] );
        } );

        $( '.accordion-section' ).each( function(){
            var s = $( this );
            var t = $( '.customify--device-select', s ).first();
            $( '.customize-section-title', s ).append( t );
        } );

        IconPicker.init();
        if ( $( '.customify--font-families' ).length > 0 ) {
            FontSelector.init();
        }

        // Devices Switcher
        $document.on( 'click', '.customify-devices button', function(e){
            e.preventDefault();
            var device = $( this ).attr( 'data-device' ) || '';
            console.log( 'Device', device );
            $( '#customize-footer-actions .devices button[data-device="'+device+'"]' ).trigger('click');
        } );

        // Devices Switcher
        $document.on( 'change', '.customify--field input:checkbox', function(e){
            if ( $( this ).is(':checked') ) {
                $( this ).parent().addClass('customify--checked');
            } else {
                $( this ).parent().removeClass('customify--checked');
            }
        } );

        // Setup conditional

        var ControlConditional = function( decodeValue ){
            if ( _.isUndefined( decodeValue ) ) {
                decodeValue = false;
            }
            var allValues = wpcustomize.get( );
           // console.log( 'ALL Control Values', allValues );
            _.each( allValues, function( value, id ){
                var control = wpcustomize.control( id );
                if ( ! _.isUndefined( control ) ) {
                    if ( control.params.type == 'customify' ) {
                        if ( ! _.isEmpty( control.params.required ) ) {
                            var check = false;
                            check = control.multiple_compare( control.params.required, allValues, decodeValue );
                            //console.log( 'Check C '+control.id, check );
                            if ( ! check ) {
                                control.container.addClass( 'customify--hide' );
                            } else {
                                control.container.removeClass( 'customify--hide' );
                            }
                        }
                    }
                }

            } );
        };

        ControlConditional( false );
        $document.on( 'customify/customizer/change', function(){
            ControlConditional( true );
        } );


    } );

    $document.ready( function( $ ){

    } );

})( jQuery, wp.customize || null );