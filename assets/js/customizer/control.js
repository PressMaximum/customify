(function( $, wpcustomize ) {
    'use strict';

    var $document = $( document );

    wp.customize.controlConstructor._beacon = wp.customize.Control.extend({

        // When we're finished loading continue processing
        ready: function() {
            var control = this;
            control.init();
        },
        type: '_beacon',
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
            clone.addClass('_beacon-devices');
            $('._beacon-field-heading', $el ).append(clone).addClass( '_beacon-devices-added' );

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
                    $( '._beacon-image-preview', this.preview ).addClass( '_beacon--has-file' ).html(  '<img src="'+url+'" alt="">' );
                    $( '.attachment-url', this.preview ).val( this.toRelativeUrl( url ) );
                    $( '.attachment-mime', this.preview ).val( mime );
                    $( '.attachment-id', this.preview ).val( id ).trigger( 'change' );
                    this.preview.addClass( 'attachment-added' );
                    this.showChangeBtn();

                },
                toRelativeUrl: function( url ){
                    return url;
                    //return url.replace( _Beacon_Control_Args.home_url, '' );
                },
                showChangeBtn: function(){
                    $( '._beacon--add', this.preview ).addClass( '_beacon--hide' );
                    $( '._beacon--change', this.preview ).removeClass( '_beacon--hide' );
                    $( '._beacon--remove', this.preview ).removeClass( '_beacon--hide' );
                },
                insertVideo: function(attachment ){
                    if ( typeof attachment !== "undefined" ) {
                        this.attachment = attachment;
                    }

                    var url = this.getURL();
                    var id = this.getID();
                    var mime = this.attachment.mime;
                    var html = '<video width="100%" height="" controls><source src="'+url+'" type="'+mime+'">Your browser does not support the video tag.</video>';
                    $( '._beacon-image-preview', this.preview ).addClass( '_beacon--has-file' ).html( html );
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

                    $( '._beacon-image-preview', this.preview ).addClass( '_beacon--has-file' ).html( '<a href="'+url+'" class="attachment-file" target="_blank">'+basename+'</a>' );
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
                    $( '._beacon-image-preview', this.preview ).removeAttr( 'style').html( '' ).removeClass( '_beacon--has-file' );
                    $( '.attachment-url', this.preview ).val( '' );
                    $( '.attachment-mime', this.preview ).val( '' );
                    $( '.attachment-id', this.preview ).val( '' ).trigger( 'change' );
                    this.preview.removeClass( 'attachment-added' );

                    $( '._beacon--add', this.preview ).removeClass( '_beacon--hide' );
                    $( '._beacon--change', this.preview ).addClass( '_beacon--hide' );
                    $( '._beacon--remove', this.preview ).addClass( '_beacon--hide' );
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
                        if ( decodeValue ) {
                            value = control.decodeValue( value )
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
                console.log( 'Trying_test_error', e  );
            }


            return check;
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
                var df = colorInput.data( 'default' ) || '';
                var current_val = $( '._beacon-input', colorInput ).val();
                $( '._beacon--color-panel', colorInput ).wpColorPicker({
                    defaultColor: df,
                    change: function( event, ui ){
                        var new_color = ui.color.toString();
                        $( '._beacon-input', colorInput ).val( new_color );
                        if( ui.color.toString() !== current_val ) {
                            current_val = new_color;
                            $( '._beacon-input', colorInput ).trigger('change');
                        }

                    }
                });
            } );
        },
        initSlider: function( $el ){
            if ( $( '._beacon-input-slider', $el ).length > 0 ) {
                $('._beacon-input-slider', $el ).each( function(){
                    var slider = $( this );
                    var p = slider.parent();
                    var input = $( '._beacon--slider-input', p );
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

                } );
            }
        },
        getFieldValue: function( name, fieldSetting, $field ){
            var control = this;
            var type = undefined ;
            var support_devices = false;
            if ( _.isUndefined( $field ) ) {
                $field = control.container.find( '._beacon--settings-fields ._beacon--field' ).first();
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
                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            value[ device ] = $( 'input[data-name="'+name+'-'+device+'"]', $field ).is(':checked') ? 1 : '' ;
                        } );
                    } else {
                        value = $( 'input[data-name="'+name+'"]:checked', $field ).val();
                    }

                    break;
                case 'checkbox':

                    if ( support_devices ) {
                        value = {};
                        _.each( control.devices, function( device ){
                            value[ device ] =  value = $( 'input[data-name="'+name+'-'+device+'"]', $field ).is(':checked') ? 1 : '' ;
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
                            var $area = $( '._beacon-group-device-fields._beacon--for-'+device, control.container );
                            value[ device ] = {};
                            var _value = {};
                            _.each( control.params.fields, function( f ){
                                var $_field = $( '._beacon--group-field[data-field-name="'+f.name+'"]', $area );
                                _value[ f.name ] = control.getFieldValue( f.name, f, $_field );
                            } );
                            value[ device ] = _value;
                            control.initConditional( $area, _value );

                        } );
                    } else {
                        _.each( control.params.fields, function( f ){
                            var $_field = $( '._beacon--group-field[data-field-name="'+f.name+'"]', control.container );
                            value[ f.name ] = control.getFieldValue( f.name, f, $_field );
                        } );

                        control.initConditional( control.container, value );
                    }
                    //console.log( 'GROUP_VALUE' );
                    break;
                case 'repeater':
                    value = [];
                    $( '._beacon--repeater-item', control.container ).each( function( index ){
                        var $item = $( this );
                        var _v = {};
                        _.each( control.params.fields, function( f ){
                            var inputField = $( '[data-field-name="'+f.name+'"]', $item );
                            var $_field = inputField.closest('._beacon--field');
                            var _fv =  control.getFieldValue( f.name, f,  $_field );
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
                console.log( 'VALUES: ', value );
                control.setting.set( control.encodeValue( value ) );
                $document.trigger( '_beacon/customizer/change' );
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
            $( '._beacon--repeater-live-title', $item ).text( value );
        },
        initGroup: function(){
            var control = this;
            if ( control.params.device_settings ) {
                control.container.find( '._beacon--settings-fields' ).addClass( '_beacon--multiple-devices' );
                if ( ! _.isObject( control.params.value ) ) {
                    control.params.value = {};
                }

                _.each( control.devices , function( device, device_index ){
                    var $group_device = $( '<div class="_beacon-group-device-fields _beacon-field-settings-inner _beacon--for-'+device+'"></div>' );
                    control.container.find( '._beacon--settings-fields' ).append( $group_device );
                    var device_value = {};
                    if ( ! _.isUndefined( control.params.value[ device] ) ) {
                        device_value = control.params.value[ device ];
                    }
                    if ( ! _.isObject( device_value ) ) {
                        device_value = {};
                    }

                    _.each( control.params.fields, function( f, index ){
                        var $fieldArea = $( '<div class="_beacon--group-field" data-field-name="'+f.name+'"></div>' );
                        $group_device.append( $fieldArea );
                        f.device_settings = false;
                        f.value = device_value[ f.name ];
                        control.addField( f, $fieldArea );
                    } );

                });

            } else {
                _.each( control.params.fields, function( f, index ){
                    var $fieldArea = $( '<div class="_beacon--group-field" data-field-name="'+f.name+'"></div>' );
                    control.container.find( '._beacon--settings-fields' ).append( $fieldArea );
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
                    var deviceFieldItem = $deviceFields.find( '._beacon-field-settings-inner' ).first();

                    if ( ! fieldItem ) {
                        $fieldsArea.append( $deviceFields ).addClass( '_beacon--multiple-devices' );
                    }

                    deviceFieldItem.addClass( '_beacon--for-'+device );
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
                default: control.params.default
            };
            if ( field.type == 'slider' ) {
                field.min = control.params.min;
                field.max = control.params.max;
            }

            if ( control.params.setting_type == 'select' || control.params.setting_type == 'radio' ) {
                field.choices = control.params.choices;
            }
            if ( control.params.setting_type == 'checkbox' ) {
                field.label = control.params.checkbox_label;
            }

            field.device_settings = control.params.device_settings;
            var $fieldsArea = control.container.find('._beacon--settings-fields');

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
            control.container.find( '._beacon--settings-fields' ).append( $itemWrapper );
            _.each( fields, function( f, index ){
                f.value = '';
                if ( ! _.isUndefined( value[ f.name ] ) ) {
                    f.value = value[ f.name ];
                }
                var $fieldArea = $( '<div class="_beacon--repeater-field"></div>' );
                $( '._beacon--repeater-item-inner', $itemWrapper ).append( $fieldArea );
                control.addField( f, $fieldArea );

                if ( ! _.isUndefined( f.device_settings ) && f.device_settings  ) {
                    control.addDeviceSwitchers( $fieldArea );
                }
            } );

            $itemWrapper.find( '._beacon--repeater-live-title' ).html( control.params.l10n.untitled );

            control.initColor( $itemWrapper );
            control.initSlider( $itemWrapper );
            $document.trigger('_beacon/customizer/repeater/add', [ $itemWrapper, control ] );
            return $itemWrapper;
        },
        limitRepeaterItems: function(){
            var control = this;
            if ( control.params.limit > 0 ) {
                var addButton = $( '._beacon--repeater-add-new', control.container );
                var c = $( '._beacon--settings-fields ._beacon--repeater-item', control.container ).length;
                if ( c >= control.params.limit ) {
                    addButton.addClass( '_beacon--hide' );
                    if ( control.params.limit_msg ) {
                        if ( $( '._beacon--limit-item-msg', control.container ).length === 0 ) {
                            $( '<p class="_beacon--limit-item-msg">'+control.params.limit_msg+'</p>' ).insertBefore( addButton );
                        } else {
                            $( '._beacon--limit-item-msg', control.container ).removeClass( '_beacon--hide' );
                        }

                    }
                } else {
                    $( '._beacon--limit-item-msg', control.container ).addClass( '_beacon--hide' );
                    addButton.removeClass( '_beacon--hide' );
                }
            }
        },
        initRepeater: function(){
            var control = this;
            control.params.limit = parseInt( control.params.limit );
            if ( isNaN( control.params.limit ) ) {
                control.params.limit = 0;
            }

            // Sortable
            control.container.find( '._beacon--settings-fields' ).sortable({
                handle: '._beacon--repeater-item-heading',
                containment: "parent"
            });

            // Toggle Move
            control.container.on( 'click', '._beacon--repeater-reorder', function ( e ) {
                e.preventDefault();
                $( '._beacon--repeater-items', control.container ).toggleClass('reorder-active');
                $( '._beacon--repeater-add-new', control.container ).toggleClass('disabled');
                if ( $( '._beacon--repeater-items', control.container ).hasClass( 'reorder-active' ) ) {
                    $( this ).html( $( this ).data( 'done' ) );
                } else {
                    $( this ).html( $( this ).data( 'text' ) );
                }
            } );

            // Move Up
            control.container.on( 'click', '._beacon--repeater-item ._beacon--up', function( e ){
                e.preventDefault();
                var i = $( this ).closest('._beacon--repeater-item');
                var index = i.index();
                if ( index > 0 ) {
                    var up =  i.prev();
                    i.insertBefore( up );
                    control.getValue();
                }
            } );

            control.container.on( 'click', '._beacon--repeater-item ._beacon--down', function( e ){
                e.preventDefault();
                var n = $( '._beacon--repeater-items ._beacon--repeater-item', control.container ).length;
                var i = $( this ).closest('._beacon--repeater-item');
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
                control.limitRepeaterItems();
            } );

            // Add Item
            control.container.on( 'click', '._beacon--repeater-add-new', function(e){
                e.preventDefault();
                if ( ! $( this ).hasClass( 'disabled' ) ) {
                    control.addRepeaterItem();
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
            if ( !_.isUndefined( _Beacon_Control_Args.icons ) && !_.isEmpty( _Beacon_Control_Args.icons ) ) {
                _.each( _Beacon_Control_Args.icons, function( icon_config, font_type ) {
                    $( '#_beacon--sidebar-icon-type' ).append( ' <option value="'+font_type+'">'+icon_config.name+'</option>' );
                    that.addCSS( icon_config, font_type );
                    that.addIcons( icon_config, font_type );
                } );
            }
        },

        addCSS: function( icon_config, font_type ){
            $( 'head' ).append( "<link rel='stylesheet' id='font-icon-"+font_type+"'  href='"+icon_config.url+"' type='text/css' media='all' />" )
        },

        addIcons: function( icon_config, font_type ){
            var icon_html = '<ul class="_beacon--list-icons icon-'+font_type+'" data-type="'+font_type+'">';
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

            $( '#_beacon--icon-browser').append( icon_html );
        },
        changeType: function(){
            $document.on( 'change', '#_beacon--sidebar-icon-type', function(){
                var type = $( this ).val();
                if ( ! type || type == 'all' ) {
                    $( '#_beacon--icon-browser ._beacon--list-icons' ).show();
                } else {
                    $( '#_beacon--icon-browser ._beacon--list-icons' ).hide();
                    $( '#_beacon--icon-browser ._beacon--list-icons.icon-'+type ).show();
                }
            } );
        },
        show: function () {
            var controlWidth = $( '#customize-controls' ).width();
            $( '#_beacon--sidebar-icons' ).css( 'left', controlWidth ).addClass( '_beacon--active' );
        },
        close: function () {
            $( '#_beacon--sidebar-icons' ).css( 'left', -300 ).removeClass( '_beacon--active' );
            $( '._beacon--icon-picker' ).removeClass('_beacon--icon-picking');
            this.pickingEl = null;
        },
        autoClose: function(){
            var that = this;
            $document.on( 'click', function( event ) {
                if ( ! $(event.target).closest('._beacon--icon-picker').length ) {
                    if ( ! $(event.target).closest('#_beacon--sidebar-icons').length ) {
                        // customize-controls-close
                        that.close();
                    }
                }
            } );

            $( '#_beacon--sidebar-icons .customize-controls-close' ).on( 'click', function(){
                that.close();
            } );
        },
        picker: function(){
            var that = this;
            $document.on( 'click', '._beacon--icon-picker ._beacon--pick-icon', function( e ) {
                e.preventDefault();
                if (  that.pickingEl ) {
                    that.pickingEl.removeClass('_beacon--icon-picking');
                }
                that.pickingEl =  $( this ).closest( '._beacon--icon-picker' );
                that.pickingEl.addClass( '_beacon--picking-icon' );
                that.show();
            } );


            $document.on( 'click', '#_beacon--icon-browser li', function( e ) {
                e.preventDefault();
                var li = $( this );
                var icon_preview = li.find( 'i' ).clone();
                var icon = li.attr( "data-icon" ) || '';
                var type = li.attr( 'data-type' ) || '';
                $( '._beacon--input-icon-type', that.pickingEl ).val( type );
                $( '._beacon--input-icon-name', that.pickingEl ).val( icon ).trigger( 'change' );
                $( '._beacon--icon-preview-icon', that.pickingEl ).html( icon_preview );

                that.close();
            } );

            // remove
            $document.on( 'click', '._beacon--icon-picker ._beacon--icon-remove', function( e ) {
                e.preventDefault();
                if (  that.pickingEl ) {
                    that.pickingEl.removeClass('_beacon--icon-picking');
                }
                that.pickingEl =  $( this ).closest( '._beacon--icon-picker' );
                that.pickingEl.addClass( '_beacon--picking-icon' );

                $( '._beacon--input-icon-type', that.pickingEl ).val( '' );
                $( '._beacon--input-icon-name', that.pickingEl ).val( '' ).trigger( 'change' );
                $( '._beacon--icon-preview-icon', that.pickingEl ).html( '' );

            } );

        },
        init: function(){
            this.render();
            this.changeType();
            this.picker();
            this.autoClose();

            // Search icon
            $document.on( 'keyup', '#_beacon--icon-search', function( e ) {
                var v = $( this).val();
                v = v.trim();
                if ( v ) {
                    $( "#_beacon--icon-browser li" ).hide();
                    $( "#_beacon--icon-browser li[data-icon*='"+v+"']" ).show();
                } else {
                    $( "#_beacon--icon-browser li" ).show();
                }
            } );
        }
    };

    var FontSelector = {
        fonts: null,
        optionHtml: '',
        get: function(){
            var that = this;
            $.get( _Beacon_Control_Args.ajax, { action: '_beacon/customizer/ajax/fonts'  }, function(res ){
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
                that.optionHtml += '<option value="">'+_Beacon_Control_Args.theme_default+'</option>';
                that.optionHtml += '<optgroup label="'+group.title+'">';
                    _.each( group.fonts, function( font, font_name ) {
                        that.optionHtml += '<option value="'+font_name+'">'+font_name+'</option>';
                    } );
                that.optionHtml += '</optgroup>';
            } );

            $( 'select._beacon--font-families' ).html( that.optionHtml );
            $( 'select._beacon--font-families' ).each( function(){
                var save_value = $( this ).data( 'value' );
                if ( ! _.isObject( save_value ) ) {
                    save_value = {};
                }
                var p = $( this ).closest('._beacon-field-settings-inner');
                if ( save_value.font ) {
                    $( 'option[value="'+save_value.font+'"]', $( this ) ).attr( 'selected', 'selected' );
                }
                that.setUpFont( save_value, p );
            } );

            $document.on( 'change', 'select._beacon--font-families', function(){
                var font =  $( this ).val();
                var p = $( this ).closest('._beacon-field-settings-inner');
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
                $( '._beacon--font-variants-wrapper', p ).addClass( '_beacon--hide').find('select').html('');
                $( '._beacon--font-subsets-wrapper', p ).addClass( '_beacon--hide').find( '.list-subsets' ).html('');
            } else {
                $( '._beacon--font-type', p ).val( type );
                $( '._beacon--font-variants-wrapper', p ).removeClass( '_beacon--hide');
                $( '._beacon--font-subsets-wrapper', p ).removeClass( '_beacon--hide');
                $( '._beacon--font-variants', p).html( that.toSelectOptions(variants, _.isObject( font ) ? font.variant : '' ) );
                $( '.list-subsets', p).removeClass('_beacon--hide').html( that.toCheckboxes(subsets, _.isObject( font ) ? font.subsets : '' ) );
            }

        },


        init: function(){
            this.get();
        }

    };


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

        IconPicker.init();
        if ( $( '._beacon--font-families' ).length > 0 ) {
            FontSelector.init();
        }

        // Devices Switcher
        $document.on( 'click', '._beacon-devices button', function(e){
            e.preventDefault();
            var device = $( this ).attr( 'data-device' ) || '';
            console.log( 'Device', device );
            $( '#customize-footer-actions .devices button[data-device="'+device+'"]' ).trigger('click');
        } );

        // Devices Switcher
        $document.on( 'change', '._beacon--field input:checkbox', function(e){
            if ( $( this ).is(':checked') ) {
                $( this ).parent().addClass('_beacon--checked');
            } else {
                $( this ).parent().removeClass('_beacon--checked');
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
                    if ( control.params.type == '_beacon' ) {
                        if ( ! _.isEmpty( control.params.required ) ) {
                            var check = false;
                            check = control.multiple_compare( control.params.required, allValues, decodeValue );
                            //console.log( 'Check C '+control.id, check );
                            if ( ! check ) {
                                control.container.addClass( '_beacon--hide' );
                            } else {
                                control.container.removeClass( '_beacon--hide' );
                            }
                        }
                    }
                }

            } );
        };

        ControlConditional( false );
        $document.on( '_beacon/customizer/change', function(){
            ControlConditional( true );
        } );


    } );

    $document.ready( function( $ ){

    } );

})( jQuery, wp.customize || null );