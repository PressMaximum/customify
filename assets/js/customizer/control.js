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
            return this.addParamsURL( this.attachment.url, { t : t } );
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

            var url = this.getURL();
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

    var customify_controls_list = {};
    //---------------------------------------------------------------------------

    var customifyField = {
        devices: ['desktop', 'tablet', 'mobile'],
        allDevices: ['desktop', 'tablet', 'mobile'],
        type: 'customify',
        getTemplate: _.memoize(function () {
            var field = this;
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

            return function (data, id, data_variable_name) {
                if (_.isUndefined(id)) {
                    id = 'tmpl-customize-control-' + field.type;
                }
                if (!_.isUndefined(data_variable_name) && _.isString(data_variable_name)) {
                    options.variable = data_variable_name;
                } else {
                    options.variable = 'data';
                }
                compiled = _.template($('#' + id).html(), null, options);
                return compiled(data);
            };
        }),

        getFieldValue: function (name, fieldSetting, $field ) {
            var control = this;
            var type = undefined;
            var support_devices = false;

            if (!_.isUndefined(fieldSetting)) {
                type = fieldSetting.type;
                support_devices = fieldSetting.device_settings;
            }

            var value = '';
            switch (type) {
                case 'media':
                case 'image':
                case 'video':
                case 'attachment':
                case 'audio':
                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var _name = name + '-' + device;
                            value[device] = {
                                id: $('input[data-name="' + _name + '"]', $field).val(),
                                url: $('input[data-name="' + _name + '-url"]', $field).val(),
                                mime: $('input[data-name="' + _name + '-mime"]', $field).val()
                            };
                        });
                    } else {
                        value = {
                            id: $('input[data-name="' + name + '"]', $field).val(),
                            url: $('input[data-name="' + name + '-url"]', $field).val(),
                            mime: $('input[data-name="' + name + '-mime"]', $field).val()
                        };
                    }

                    break;
                case 'css_ruler':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var _name = name + '-' + device;
                            value[device] = {
                                unit: $('input[data-name="' + _name + '-unit"]:checked', $field).val(),
                                top: $('input[data-name="' + _name + '-top"]', $field).val(),
                                right: $('input[data-name="' + _name + '-right"]', $field).val(),
                                bottom: $('input[data-name="' + _name + '-bottom"]', $field).val(),
                                left: $('input[data-name="' + _name + '-left"]', $field).val(),
                                link: $('input[data-name="' + _name + '-link"]', $field).is(':checked') ? 1 : ''
                            };
                        });
                    } else {
                        value = {
                            unit: $('input[data-name="' + name + '-unit"]:checked', $field).val(),
                            top: $('input[data-name="' + name + '-top"]', $field).val(),
                            right: $('input[data-name="' + name + '-right"]', $field).val(),
                            bottom: $('input[data-name="' + name + '-bottom"]', $field).val(),
                            left: $('input[data-name="' + name + '-left"]', $field).val(),
                            link: $('input[data-name="' + name + '-link"]', $field).is(':checked') ? 1 : ''
                        };
                    }

                    break;
                case 'font_style':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var _name = name + '-' + device;
                            value[device] = {
                                b: $('input[data-name="' + _name + '-b"]', $field).is(':checked') ? 1 : '',
                                i: $('input[data-name="' + _name + '-i"]', $field).is(':checked') ? 1 : '',
                                u: $('input[data-name="' + _name + '-u"]', $field).is(':checked') ? 1 : '',
                                s: $('input[data-name="' + _name + '-s"]', $field).is(':checked') ? 1 : '',
                                t: $('input[data-name="' + _name + '-t"]', $field).is(':checked') ? 1 : ''
                            };
                        });
                    } else {
                        value = {
                            b: $('input[data-name="' + name + '-b"]', $field).is(':checked') ? 1 : '',
                            i: $('input[data-name="' + name + '-i"]', $field).is(':checked') ? 1 : '',
                            u: $('input[data-name="' + name + '-u"]', $field).is(':checked') ? 1 : '',
                            s: $('input[data-name="' + name + '-s"]', $field).is(':checked') ? 1 : '',
                            t: $('input[data-name="' + name + '-t"]', $field).is(':checked') ? 1 : ''
                        };
                    }

                    break;
                case 'font':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var _name = name + '-' + device;
                            var subsets = {};
                            $('.list-subsets[data-name="' + _name + '-subsets"] input', $field).each(function () {
                                if ($(this).is(':checked')) {
                                    var _v = $(this).val();
                                    subsets[_v] = _v;
                                }
                            });
                            value[device] = {
                                font: $('select[data-name="' + _name + '-font"]', $field).val(),
                                type: $('input[data-name="' + _name + '-type"]', $field).val(),
                                variant: $('select[data-name="' + _name + '-variant"]', $field).val(), // variant
                                subsets: subsets
                            };
                        });
                    } else {
                        var subsets = {};
                        $('.list-subsets[data-name="' + name + '-subsets"] input', $field).each(function () {
                            if ($(this).is(':checked')) {
                                var _v = $(this).val();
                                subsets[_v] = _v;
                            }
                        });
                        value = {
                            font: $('select[data-name="' + name + '-font"]', $field).val(),
                            type: $('input[data-name="' + name + '-type"]', $field).val(),
                            variant: $('select[data-name="' + name + '-variant"]', $field).val(),
                            subsets: subsets
                        };
                    }

                    break;
                case 'slider':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var _name = name + '-' + device;
                            value[device] = {
                                unit: $('input[data-name="' + _name + '-unit"]:checked', $field).val(),
                                value: $('input[data-name="' + _name + '-value"]', $field).val()
                            };
                        });
                    } else {
                        value = {
                            unit: $('input[data-name="' + name + '-unit"]:checked', $field).val(),
                            value: $('input[data-name="' + name + '-value"]', $field).val()
                        };
                    }

                    break;
                case 'icon':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var _name = name + '-' + device;
                            value[device] = {
                                type: $('input[data-name="' + _name + '-type"]', $field).val(),
                                icon: $('input[data-name="' + _name + '"]', $field).val()
                            };
                        });
                    } else {
                        value = {
                            type: $('input[data-name="' + name + '-type"]', $field).val(),
                            icon: $('input[data-name="' + name + '"]', $field).val()
                        };
                    }
                    break;
                case 'radio':
                case 'text_align':
                case 'text_align_no_justify':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            var input = $('input[data-name="' + name + '-' + device + '"]:checked', $field);
                            value[device] = input.length ? input.val() : '';
                        });
                    } else {
                        value = $('input[data-name="' + name + '"]:checked', $field).val();
                    }

                    break;
                case 'checkbox':

                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            value[device] = $('input[data-name="' + name + '-' + device + '"]', $field).is(':checked') ? 1 : '';
                        });
                    } else {
                        value = $('input[data-name="' + name + '"]', $field).is(':checked') ? 1 : '';
                    }

                    break;

                case 'checkboxes':
                    value = {};
                    if (support_devices ) {
                        _.each(control.allDevices, function (device) {
                            value[device] = {};
                            $('input[data-name="' + name + '-' + device + '"]', $field).each( function(){
                                var v = $( this ).val();
                                if ( $( this ).is(':checked') ) {
                                    value[ v ] = v;
                                }
                            } );

                        });
                    } else {
                        $('input[data-name="' + name + '"]', $field ).each( function(){
                            var v = $( this ).val();
                            if ( $( this ).is(':checked') ) {
                                value[ v ] = v;
                            }
                        } );
                    }

                    break;
                case 'typography':
                case 'modal':
                case 'styling':
                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            value[device] = $('[data-name="' + name + '-' + device + '"]', $field).val();
                        });
                    } else {
                        value = $('[data-name="' + name + '"]', $field).val();
                    }

                    try {
                        value = JSON.parse( value );
                    } catch  (e) {

                    }
                    break;
                default:
                    if (support_devices) {
                        value = {};
                        _.each(control.allDevices, function (device) {
                            value[device] = $('[data-name="' + name + '-' + device + '"]', $field).val();
                        });
                    } else {
                        value = $('[data-name="' + name + '"]', $field).val();
                    }
                    break;
            }

            return value;

        },
        getValue: function ( field, container ) {
            var control = this;
            var value = '';

            switch ( field.type ) {
                case 'group':
                    value = {};

                    if ( field.device_settings ) {
                        _.each(control.allDevices, function (device) {
                            var $area = $('.customify-group-device-fields.customify--for-' + device, container);
                            value[device] = {};
                            var _value = {};
                            _.each( field.fields, function (f) {
                                var $_field = $('.customify--group-field[data-field-name="' + f.name + '"]', $area);
                                _value[f.name] = control.getFieldValue(f.name, f, $_field);
                            });
                            value[device] = _value;
                            control.initConditional($area, _value);

                        });
                    } else {
                        _.each( field.fields, function (f) {
                            var $_field = $('.customify--group-field[data-field-name="' + f.name + '"]', container );
                            value[f.name] = control.getFieldValue(f.name, f, $_field);
                        });
                        control.initConditional( container, value);
                    }

                    break;
                case 'repeater':
                    value = [];
                    $('.customify--repeater-item', container).each(function (index) {
                        var $item = $(this);
                        var _v = {};
                        _.each( field.fields, function (f) {
                            var inputField = $('[data-field-name="' + f.name + '"]', $item);
                            //var $_field = inputField.closest('.customify--field');
                            //var $_field = inputField.closest('.customify--repeater-field');
                            var _fv = control.getFieldValue(f.name, f, $item);
                            _v[f.name] = _fv;
                            // Update Live title
                            if ( field.live_title_field == f.name) {
                                if (inputField.prop("tagName") == 'select') {
                                    _fv = $('option[value="' + _fv + '"]').first().text();
                                }
                                if (_.isUndefined(_fv) || _fv == '') {
                                    _fv = 'Untitled';
                                }
                                control.updateRepeaterLiveTitle(_fv, $item, f);
                            }
                        });

                        control.initConditional($item, _v);

                        value[index] = _v;
                        value[index]['_visibility'] = 'visible';

                        if ($('input.r-visible-input', $item).length) {
                            if (!$('input.r-visible-input', $item).is(':checked')) {
                                value[index]['_visibility'] = 'hidden';
                            }
                        }

                    });
                    break;
                default:
                    value = this.getFieldValue( field.name, field, container );
                    break;
            }

            return value;
        },
        encodeValue: function (value) {
            return encodeURI(JSON.stringify(value))
        },
        decodeValue: function (value) {
            return JSON.parse(decodeURI(value));
        },
        updateRepeaterLiveTitle: function (value, $item, field) {
            $('.customify--repeater-live-title', $item).text(value);
        },
        compare: function (value1, cond, value2) {
            var equal = false;
            switch (cond) {
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
                    var _v = _.clone(value1);
                    if (_.isObject(_v) || _.isArray(_v)) {
                        _.each(_v, function (v, i) {
                            if (_.isEmpty(v)) {
                                delete _v[i];
                            }
                        });

                        equal = _.isEmpty(_v) ? true : false;
                    } else {
                        equal = _.isNull(_v) || _v == '' ? true : false;
                    }
                    break;
                case 'not_empty':
                    var _v = _.clone(value1);
                    if (_.isObject(_v) || _.isArray(_v)) {
                        _.each(_v, function (v, i) {
                            if (_.isEmpty(v)) {
                                delete _v[i];
                            }
                        })
                    }
                    equal = _.isEmpty(_v) ? false : true;
                    break;
                default:
                    if (_.isArray(value2)) {
                        if (!_.isEmpty(value2) && !_.isEmpty(value1)) {
                            equal = _.contains(value2, value1);
                        } else {
                            equal = false;
                        }
                    } else {
                        equal = ( value1 == value2 ) ? true : false;
                    }
            }

            return equal;
        },
        multiple_compare: function (list, values, decodeValue) {
            if (_.isUndefined(decodeValue)) {
                decodeValue = false;
            }
            var control = this;
            var check = false;
            try {
                var test = list[0];

                if (_.isString(test)) {
                    check = false;
                    var cond = list[1];
                    var cond_val = list[2];
                    var cond_device = false;
                    if (!_.isUndefined(list[3])) { // can be desktop, tablet, mobile
                        cond_device = list[3];
                    }
                    var value;
                    if (!_.isUndefined(values[test])) {
                        value = values[test];
                        if (cond_device) {
                            if (_.isObject(value) && !_.isUndefined(value[cond_device])) {
                                value = value[cond_device];
                            }
                        }
                        try {
                            if (decodeValue) {
                                value = control.decodeValue(value)
                            }
                        } catch (e) {

                        }

                        check = control.compare(value, cond, cond_val);
                    }

                } else if (_.isArray(test)) {
                    check = true;
                    //console.log( '___', list );
                    _.each(list, function (req) {

                        var cond_key = req[0];
                        var cond_cond = req[1];
                        var cond_val = req[2];
                        var cond_device = false;
                        if (!_.isUndefined(req[3])) { // can be desktop, tablet, mobile
                            cond_device = req[3];
                        }
                        var t_val = values[cond_key];
                        if (_.isUndefined(t_val)) {
                            t_val = '';
                        }
                        // console.log( '___reql', req );
                        if (decodeValue && _.isString(t_val)) {
                            try {
                                t_val = control.decodeValue(t_val)
                            } catch (e) {

                            }
                        }

                        //console.log( '___t_val', t_val );
                        if (cond_device) {
                            if (_.isObject(t_val) && !_.isUndefined(t_val[cond_device])) {
                                t_val = t_val[cond_device];
                            }
                        }

                        if (!control.compare(t_val, cond_cond, cond_val)) {
                            check = false;
                        }
                    });

                }
            } catch (e) {
                //console.log( 'Trying_test_error', e  );
            }

            return check;
        },
        initConditional: function ($el, values) {
            var control = this;
            var $fields = $('.customify--field', $el);
            $fields.each(function () {
                var $field = $(this);
                var check = true;
                var req = $field.attr('data-required') || false;
                if (!_.isUndefined(req) && req) {
                    req = JSON.parse(req);
                    check = control.multiple_compare(req, values);
                    if (!check) {
                        $field.addClass('customify--hide');
                    } else {
                        $field.removeClass('customify--hide');
                    }
                }
            });
        },

        addDeviceSwitchers: function ($el) {
            var field = this;
            if (_.isUndefined($el)) {
                $el = field.container;
            }
            var clone = $('#customize-footer-actions .devices').clone();
            clone.addClass('customify-devices');
            $('button', clone).each(function () {
                var d = $(this).attr('data-device');
                if (_.indexOf(field.devices, d) < 0) {
                    $(this).remove();
                }
            });
            $('.customify-field-heading', $el).append(clone).addClass('customify-devices-added');
        },

        add: function (field, $fieldsArea, cb ) {
            var control = this;
            var template = control.getTemplate();
            var template_id = 'tmpl-field-' + control.type + '-' + field.type;
            if ($('#' + template_id).length == 0) {
                template_id = 'tmpl-field-' + control.type + '-text';
            }
            if (field.device_settings) {
                var fieldItem = null;
                _.each(control.devices, function (device, index) {
                    var _field = _.clone(field);
                    _field.original_name = field.name;
                    if (_.isObject(field.value)) {
                        if (!_.isUndefined(field.value[device])) {
                            _field.value = field.value[device];
                        } else {
                            _field.value = '';
                        }
                    } else {
                        _field.value = '';
                        if (index === 0) {
                            _field.value = field.value;
                        }
                    }
                    _field.name = field.name + '-' + device;
                    _field._current_device = device;

                    var $deviceFields = $(template(_field, template_id, 'field'));
                    var deviceFieldItem = $deviceFields.find('.customify-field-settings-inner').first();

                    if (!fieldItem) {
                        $fieldsArea.append($deviceFields).addClass('customify--multiple-devices');
                    }

                    deviceFieldItem.addClass('customify--for-' + device);
                    deviceFieldItem.attr('data-for-device', device);

                    if (fieldItem) {
                        deviceFieldItem.insertAfter(fieldItem);
                        fieldItem = deviceFieldItem;
                    }
                    fieldItem = deviceFieldItem;

                });
            } else {
                field.original_name = field.name;
                var $fields = template(field, template_id, 'field');
                $fieldsArea.html($fields);
            }

            if (field.css_format && _.isString(field.css_format)) {
                if (field.css_format.indexOf('value_no_unit') > 0) {
                    $('.customify--css-unit .customify--label-active', $fieldsArea).hide();
                }
            }

            // Add unility
            switch ( field.type ) {
                case  'color':
                    control.initColor( $fieldsArea );
                    break;
                case 'image':
                case 'video':
                case 'audio':
                case 'attchment':
                case 'file':
                    control.initMedia( $fieldsArea );
                    break;
                case 'slider':
                    control.initSlider( $fieldsArea );
                    break;
                case 'css_ruler':
                    control.initCSSRuler( $fieldsArea, cb );
                    break;
            }
            if ( field.type !== 'hidden') {
                if ( !_.isUndefined( field.device_settings ) && field.device_settings ) {
                    control.addDeviceSwitchers( $fieldsArea );
                }
            }

        },

        addFields: function( fields, values, $fieldsArea, cb ){
            var control = this;
            if ( ! _.isObject( values ) ) {
                values = {};
            }
            _.each(fields, function (f, index) {
                var $fieldArea = $('<div class="customify--group-field" data-field-name="' + f.name + '"></div>');
                $fieldsArea.append( $fieldArea );
                f.original_name = f.name;
                if ( !_.isUndefined( values[ f.name ] ) ) {
                    f.value = values[ f.name ];
                }
                control.add( f, $fieldArea, cb );
            });
        },

        initSlider: function ($el) {
            if ($('.customify-input-slider', $el).length > 0) {
                $('.customify-input-slider', $el).each(function () {
                    var slider = $(this);
                    var p = slider.parent();
                    var input = $('.customify--slider-input', p);
                    var min = slider.data('min') || 0;
                    var max = slider.data('max') || 300;
                    var step = slider.data('step') || 1;
                    if (!_.isNumber(min)) {
                        min = 0;
                    }

                    if (!_.isNumber(max)) {
                        max = 300;
                    }

                    if (!_.isNumber(step)) {
                        step = 1;
                    }

                    var current_val = input.val();
                    slider.slider({
                        range: "min",
                        value: current_val,
                        step: step,
                        min: min,
                        max: max,
                        slide: function (event, ui) {
                            input.val(ui.value).trigger('data-change');
                        }
                    });

                    input.on('change', function () {
                        slider.slider("value", $(this).val());
                    });

                    // Reset
                    var wrapper = slider.closest('.customify-input-slider-wrapper');
                    wrapper.on('click', '.reset', function (e) {
                        e.preventDefault();
                        var d = slider.data('default');
                        if (!_.isObject(d)) {
                            d = {
                                'unit': 'px',
                                'value': ''
                            }
                        }

                        $('.customify--slider-input', wrapper).val(d.value);
                        slider.slider("option", "value", d.value);
                        $('.customify--css-unit input.customify-input[value="' + d.unit + '"]', wrapper).trigger('click');
                        $('.customify--slider-input', wrapper).trigger('change');

                    });

                });
            }
        },

        initMedia: function ( $el ) {

            // When add/Change
            $el.on('click', '.customify--media .customify--add, .customify--media .customify--change, .customify--media .customify-image-preview', function (e) {
                e.preventDefault();
                var p = $(this).closest('.customify--media');
                CustomifyMedia.setPreview(p);
                CustomifyMedia.controlMediaImage.open();
            });

            // When add/Change
            $el.on('click', '.customify--media .customify--remove', function (e) {
                e.preventDefault();
                var p = $(this).closest('.customify--media');
                CustomifyMedia.remove(p);
            });
        },

        initCSSRuler: function ( $el, change_cb ) {
            // When toggle value change
            $el.on('change', '.customify--label-parent', function () {
                if ($(this).attr('type') == 'radio') {
                    var name = $(this).attr('name');
                    $('input[name="' + name + '"]', $el ).parent().removeClass('customify--label-active');
                }
                var checked = $(this).is(':checked');
                if (checked) {
                    $(this).parent().addClass('customify--label-active');
                } else {
                    $(this).parent().removeClass('customify--label-active');
                }
                if( _.isFunction( change_cb ) ) {
                    change_cb();
                }
            });


            $el.on('change keyup', '.customify--css-ruler .customify-input-css', function () {
                var p = $(this).closest('.customify--css-ruler');
                var link_checked = $('.customify--css-ruler-link input', p).is(':checked');
                if (link_checked) {
                    var v = $(this).val();
                    $('.customify-input-css', p).not($(this)).each(function () {
                        if (!$(this).is(':disabled')) {
                            $(this).val(v);
                        }
                    });
                }
                if( _.isFunction( change_cb ) ) {
                    change_cb();
                }
            });

        },

        initColor: function ($el) {

            $('.customify-input-color', $el).each(function () {
                var colorInput = $(this);
                var df = colorInput.data('default') || '';
                var current_val = $('.customify-input--color', colorInput).val();
                // data-alpha="true"
                $('.customify--color-panel', colorInput).attr('data-alpha', 'true');
                $('.customify--color-panel', colorInput).wpColorPicker({
                    defaultColor: df,
                    change: function (event, ui) {
                        var new_color = ui.color.toString();
                        $('.customify-input--color', colorInput).val(new_color);
                        if (ui.color.toString() !== current_val) {
                            current_val = new_color;
                            $('.customify-input--color', colorInput).trigger('change');
                        }
                    },
                    clear: function (event, ui) {
                        $('.customify-input--color', colorInput).val('');
                        $('.customify-input--color', colorInput).trigger('data-change');
                    }

                });
            });
        },

    };

    //-------------------------------------------------------------------------

    var customify_controlConstructor = {
        devices: ['desktop', 'tablet', 'mobile'],
        // When we're finished loading continue processing
        type: 'customify',
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

            return function (data, id, data_variable_name) {
                if (_.isUndefined(id)) {
                    id = 'tmpl-customize-control-' + control.type;
                }
                if (!_.isUndefined(data_variable_name) && _.isString(data_variable_name)) {
                    options.variable = data_variable_name;
                } else {
                    options.variable = 'data';
                }
                compiled = _.template($('#' + id).html(), null, options);
                return compiled(data);
            };

        }),
        addDeviceSwitchers: customifyField.addDeviceSwitchers,
        init: function () {

            var control = this;

            if (_.isArray( control.params.devices) && !_.isEmpty(control.params.devices)) {
                control.devices = control.params.devices;
            }

            // The hidden field that keeps the data saved (though we never update it)
            control.settingField = control.container.find('[data-customize-setting-link]').first();

            switch (control.params.setting_type) {
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

            control.container.on('change keyup data-change', 'input:not(.change-by-js), select:not(.change-by-js), textarea:not(.change-by-js)', function () {
                control.getValue();
            });

        },
        addParamsURL: function (url, data) {
            if (!$.isEmptyObject(data)) {
                url += ( url.indexOf('?') >= 0 ? '&' : '?' ) + $.param(data);
            }
            return url;
        },

        compare: customifyField.compare,
        multiple_compare: customifyField.multiple_compare,
        initConditional: customifyField.initConditional,

        getValue: function (save) {
            var control = this;
            var value = '';
            var field = {
                type: control.params.setting_type,
                name: control.id,
                value: control.params.value,
                default: control.params.default,
                devices: control.params.devices,
            };

            if (field.type === 'slider') {
                field.min = control.params.min;
                field.max = control.params.max;
                field.step = control.params.step;
            }

            if (field.type === 'css_ruler') {
                field.fields_disabled = control.params.fields_disabled;
            }

            if (field.type === 'group' || field.type === 'repeater' ) {
                field.fields = control.params.fields;
            }

            if (control.params.setting_type === 'select' || control.params.setting_type === 'radio') {
                field.choices = control.params.choices;
            }
            if (control.params.setting_type === 'checkbox') {
                field.checkbox_label = control.params.checkbox_label;
            }

            field.device_settings = control.params.device_settings;

            value = customifyField.getValue( field, $( '.customify--settings-fields', control.container ) );

            if (_.isUndefined(save) || save) {
                //console.log( 'VALUES: ', value );
                control.setting.set(control.encodeValue(value));
                $document.trigger('customify/customizer/change');
            } else {

            }

            console.log( 'All Value: ', value );
            return value;
        },
        encodeValue: function (value) {
            return encodeURI(JSON.stringify(value))
        },
        decodeValue: function (value) {
            return JSON.parse(decodeURI(value));
        },
        updateRepeaterLiveTitle: function (value, $item, field) {
            $('.customify--repeater-live-title', $item).text(value);
        },
        initGroup: function () {
            var control = this;
            if (control.params.device_settings) {
                control.container.find('.customify--settings-fields').addClass('customify--multiple-devices');
                if (!_.isObject(control.params.value)) {
                    control.params.value = {};
                }

                _.each(control.devices, function (device, device_index) {
                    var $group_device = $('<div class="customify-group-device-fields customify-field-settings-inner customify--for-' + device + '"></div>');
                    control.container.find('.customify--settings-fields').append($group_device);
                    var device_value = {};
                    if (!_.isUndefined(control.params.value[device])) {
                        device_value = control.params.value[device];
                    }
                    if (!_.isObject(device_value)) {
                        device_value = {};
                    }

                    customifyField.addFields( control.params.fields,  device_value, $group_device, function(){
                        control.getValue();
                    } );

                });

            } else {

                customifyField.addFields( control.params.fields,  control.params.value,  control.container.find('.customify--settings-fields'), function(){
                    control.getValue();
                } );
            }

            control.getValue(false);
        },
        addField: function( field, $fieldsArea, cb ){
            customifyField.devices = _.clone( this.devices );
            customifyField.add( field, $fieldsArea, cb );
        },
        initField: function () {
            var control = this;
            var field = {
                type: control.params.setting_type,
                name: control.id,
                value: control.params.value,
                default: control.params.default,
                devices: control.params.devices,
            };

            if (field.type == 'slider') {
                field.min = control.params.min;
                field.max = control.params.max;
                field.step = control.params.step;
            }

            if (field.type == 'css_ruler') {
                field.fields_disabled = control.params.fields_disabled;
            }

            if (control.params.setting_type == 'select' || control.params.setting_type == 'radio') {
                field.choices = control.params.choices;
            }
            if (control.params.setting_type == 'checkbox') {
                field.checkbox_label = control.params.checkbox_label;
            }

            field.device_settings = control.params.device_settings;
            var $fieldsArea = control.container.find('.customify--settings-fields');

            control.addField(field, $fieldsArea , function(){
                control.getValue();
            });
            if ( field.type !== 'hidden') {
                if ( !_.isUndefined( field.device_settings ) && field.device_settings ) {
                    control.addDeviceSwitchers( control.container );
                }
            }

        },
        addRepeaterItem: function (value) {
            if (!_.isObject(value)) {
                value = {};
            }

            var control = this;
            var template = control.getTemplate();
            var fields = control.params.fields;
            var addable = true;
            var title_only = control.params.title_only;
            if (control.params.addable === false) {
                addable = false;
            }

            var $itemWrapper = $(template(control.params, 'tmpl-customize-control-' + control.type + '-repeater'));
            control.container.find('.customify--settings-fields').append($itemWrapper);
            _.each(fields, function (f, index) {
                f.value = '';
                f.addable = addable;
                if (!_.isUndefined(value[f.name])) {
                    f.value = value[f.name];
                }
                var $fieldArea;

                $fieldArea = $('<div class="customify--repeater-field"></div>');
                $('.customify--repeater-item-inner', $itemWrapper).append($fieldArea);
                control.addField(f, $fieldArea,function(){
                    control.getValue();
                });
            });

            if (!_.isUndefined(value._visibility) && value._visibility === 'hidden') {
                $itemWrapper.addClass('item---visible-hidden');
                $itemWrapper.find('input.r-visible-input').removeAttr('checked');
            } else {
                $itemWrapper.find('input.r-visible-input').attr('checked', 'checked');
            }

            $itemWrapper.find('.customify--repeater-live-title').html(control.params.l10n.untitled);
            if (title_only) {
                $('.customify--repeater-item-settings, .customify--repeater-item-toggle', $itemWrapper).hide();
            } else {

            }


            $document.trigger('customify/customizer/repeater/add', [$itemWrapper, control]);
            return $itemWrapper;
        },
        limitRepeaterItems: function () {
            var control = this;

            var addButton = $('.customify--repeater-add-new', control.container);
            var c = $('.customify--settings-fields .customify--repeater-item', control.container).length;

            if (control.params.limit > 0) {
                if (c >= control.params.limit) {
                    addButton.addClass('customify--hide');
                    if (control.params.limit_msg) {
                        if ($('.customify--limit-item-msg', control.container).length === 0) {
                            $('<p class="customify--limit-item-msg">' + control.params.limit_msg + '</p>').insertBefore(addButton);
                        } else {
                            $('.customify--limit-item-msg', control.container).removeClass('customify--hide');
                        }
                    }
                } else {
                    $('.customify--limit-item-msg', control.container).addClass('customify--hide');
                    addButton.removeClass('customify--hide');
                }
            }

            if (c > 0) {
                $('.customify--repeater-reorder', control.container).removeClass('customify--hide');
            } else {
                $('.customify--repeater-reorder', control.container).addClass('customify--hide');
            }

        },
        initRepeater: function () {
            var control = this;
            control.params.limit = parseInt(control.params.limit);
            if (isNaN(control.params.limit)) {
                control.params.limit = 0;
            }

            // Sortable
            control.container.find('.customify--settings-fields').sortable({
                handle: '.customify--repeater-item-heading',
                containment: "parent",
                update: function (event, ui) {
                    control.getValue();
                }
            });

            // Toggle Move
            control.container.on('click', '.customify--repeater-reorder', function (e) {
                e.preventDefault();
                $('.customify--repeater-items', control.container).toggleClass('reorder-active');
                $('.customify--repeater-add-new', control.container).toggleClass('disabled');
                if ($('.customify--repeater-items', control.container).hasClass('reorder-active')) {
                    $(this).html($(this).data('done'));
                } else {
                    $(this).html($(this).data('text'));
                }
            });

            // Move Up
            control.container.on('click', '.customify--repeater-item .customify--up', function (e) {
                e.preventDefault();
                var i = $(this).closest('.customify--repeater-item');
                var index = i.index();
                if (index > 0) {
                    var up = i.prev();
                    i.insertBefore(up);
                    control.getValue();
                }
            });

            // Move Down
            control.container.on('click', '.customify--repeater-item .customify--down', function (e) {
                e.preventDefault();
                var n = $('.customify--repeater-items .customify--repeater-item', control.container).length;
                var i = $(this).closest('.customify--repeater-item');
                var index = i.index();
                if (index < n - 1) {
                    var down = i.next();
                    i.insertAfter(down);
                    control.getValue();
                }
            });


            // Add item when customizer loaded
            if (_.isArray(control.params.value)) {
                _.each(control.params.value, function (itemValue) {
                    control.addRepeaterItem(itemValue);
                });
                control.getValue(false);
            }
            control.limitRepeaterItems();

            // Toggle visibility
            control.container.on('change', '.customify--repeater-item .r-visible-input', function (e) {
                e.preventDefault();
                var p = $(this).closest('.customify--repeater-item');
                if ($(this).is(':checked')) {
                    p.removeClass('item---visible-hidden');
                } else {
                    p.addClass('item---visible-hidden');
                }
            });

            // Toggle
            if (!control.params.title_only) {
                control.container.on('click', '.customify--repeater-item-toggle, .customify--repeater-live-title', function (e) {
                    e.preventDefault();
                    var p = $(this).closest('.customify--repeater-item');
                    p.toggleClass('customify--open');
                });
            }

            // Remove
            control.container.on('click', '.customify--remove', function (e) {
                e.preventDefault();
                var p = $(this).closest('.customify--repeater-item');
                p.remove();
                $document.trigger('customify/customizer/repeater/remove', [control]);
                control.getValue();
                control.limitRepeaterItems();
            });


            var defaultValue = {};
            _.each(control.params.fields, function (f, k) {
                defaultValue[f.name] = null;
                if (!_.isUndefined(f.default)) {
                    defaultValue[f.name] = f.default;
                }
            });

            // Add Item
            control.container.on('click', '.customify--repeater-add-new', function (e) {
                e.preventDefault();
                if (!$(this).hasClass('disabled')) {
                    control.addRepeaterItem(defaultValue);
                    control.getValue();
                    control.limitRepeaterItems();
                }
            });
        }

    };

    var customify_control = function( control ){
        control = _.extend( control, customify_controlConstructor );
        control.init();
    };
    //---------------------------------------------------------------------------

    wp.customize.controlConstructor.customify = wp.customize.Control.extend({
        ready: function() {
            customify_controls_list[ this.id] = this;
        }
    });

    var IconPicker = {
        pickingEl: null,
        listIcons: null,
        render: function( list_icons ){
            var that = this;
            if ( !_.isUndefined( list_icons ) && !_.isEmpty( list_icons ) ) {
                _.each( list_icons, function( icon_config, font_type ) {
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

            var open = function () {
                if (  that.pickingEl ) {
                    that.pickingEl.removeClass('customify--icon-picking');
                }
                that.pickingEl =  $( this ).closest( '.customify--icon-picker' );
                that.pickingEl.addClass( 'customify--picking-icon' );
                that.show();
            };

            $document.on( 'click', '.customify--icon-picker .customify--pick-icon', function( e ) {
                e.preventDefault();
                if ( _.isNull( that.listIcons ) ) {
                    that.ajaxLoad( function(){
                        open();
                    } );
                } else {
                    open();
                }
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

        ajaxLoad: function( cb ){
            var that = this;
            $.get( Customify_Control_Args.ajax, { action: 'customify/customizer/ajax/get_icons' }, function(res ){
                if ( res.success ) {
                    that.listIcons = res.data;
                    that.render( res.data );
                    that.changeType();
                    that.autoClose();
                    if ( _.isFunction( cb ) ) {
                        cb();
                    }
                }
            } );
        },
        init: function(){
            var that = this;

            that.picker();

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
        $el: null,
        values: {},
        container: null,
        fields: Customify_Control_Args.typo_fields,
        load: function(){
            var that = this;
            $.get( Customify_Control_Args.ajax, { action: 'customify/customizer/ajax/fonts'  }, function(res ){
                if ( res.success ) {
                    that.fonts = res.data;
                    //that.ready()
                }
            } );
        },
        toSelectOptions: function ( options, v, type ){
            var html = '';
            if ( _.isUndefined( v ) ) {
                v = '';
            }

            if ( type === 'google' ) {

                _.each(options, function (value) {
                    var selected = '';
                    if (value === v) {
                        selected = ' selected="selected" ';
                    }
                    html += '<option' + selected + ' value="' + value + '">' + value + '</option>';
                });
            } else {

                _.each( Customify_Control_Args.list_font_weight, function( value, key ){
                    var selected = '' ;
                    if ( value === v ) {
                        selected = ' selected="selected" ';
                    }
                    html += '<option' + selected + ' value="' + key + '">' + value + '</option>';
                } );

                var value, selected, i;

                for( i = 1; i <= 9;  i++ ) {
                    value = i*100;
                    selected = '';
                    if ( value === v ) {
                        selected = ' selected="selected" ';
                    }
                    html += '<option' + selected + ' value="' + value + '">' + value + '</option>';
                }
            }

            return html;
        },
        toCheckboxes: function ( options, v ){
            var html = '<div class="list-subsets">';
            if ( ! _.isObject( v ) ) {
                v = {};
            }
            _.each( options, function( value ) {
                var checked = '';
                if ( ! _.isUndefined( v[ value ] ) ) {
                    checked = ' checked="checked" ';
                }
                html += '<p><label><input '+checked+'type="checkbox" class="customify-typo-input change-by-js" data-name="languages" name="_n-'+( new Date().getTime() )+'" value="'+value+'"> '+value+'</label></p>';
            } );
            html += '</div>';
            return html;
        },
        ready: function(){
            var that = this;
            customifyField.devices = _.clone( customifyField.allDevices );

            $( '.customify-modal-settings--fields', that.container ).append( '<input type="hidden" class="customify--font-type">' );

            customifyField.addFields( FontSelector.fields, that.values, $( '.customify-modal-settings--fields', that.container ), function () {
                that.get();
            } );

            $( 'input, select, textarea',  $( '.customify-modal-settings--fields' ) ).removeClass('customify-input').addClass( 'customify-typo-input change-by-js' );

            _.each( that.fonts, function( group, type ){
                // theme_default
                that.optionHtml += '<option value="">'+Customify_Control_Args.theme_default+'</option>';
                that.optionHtml += '<optgroup label="'+group.title+'">';
                    _.each( group.fonts, function( font, font_name ) {
                        that.optionHtml += '<option value="'+font_name+'">'+font_name+'</option>';
                    } );
                that.optionHtml += '</optgroup>';
            } );

            $('.customify-typo-input[data-name="font"]', that.container ).html( that.optionHtml );

            if ( ! _.isUndefined( that.values['font'] ) && _.isString( that.values['font'] ) ) {
                $('.customify-typo-input[data-name="font"] option[value="'+that.values['font']+'"]', that.container ).attr( 'selected', 'selected' );
            }

            that.container.on( 'change init-change', '.customify-typo-input[data-name="font"]', function(){
                var font =  $( this ).val();
                that.setUpFont( font );
            } );

            $('.customify-typo-input[data-name="font"]', that.container ).trigger('init-change');

            that.container.on( 'change data-change', 'input, select', function(){
                that.get();
            } ) ;

            $(document).mouseup(function(e) {
                var actions = $(".customify-actions .action--edit", that.$el );
                if (
                    !that.container.is(e.target) && that.container.has(e.target).length === 0
                    &&
                    !actions.is(e.target) && actions.has(e.target).length === 0
                ) {
                    that.container.hide();
                    that.$el.removeClass( 'modal--opening' );
                    that.$el.attr( 'data-opening', '' );
                }
            });

        },

        setUpFont: function( font ){
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

            $( '.customify-typo-input[data-name="font_weight"]', that.container ).html( that.toSelectOptions( variants, _.isObject( that.values ) ? that.values.font_weight: '', type ) );
            $( '.customify--font-type', that.container ).val( type );

            if ( type == 'normal' ) {
                $( '.customify--group-field[data-field-name="languages"]', that.container ).addClass( 'customify--hide').find( '.customify-field-settings-inner' ).html('');
            } else {
                $( '.customify--group-field[data-field-name="languages"]', that.container ).removeClass( 'customify--hide');
                $( '.customify--group-field[data-field-name="languages"]', that.container ).removeClass('customify--hide').find( '.customify-field-settings-inner' ).html( that.toCheckboxes( subsets, _.isObject( that.values ) ? that.values.languages : '' ) );
            }

        },

        open: function( $el ){
            this.$el = $el;
            var that = this;
            var status = $el.attr( 'data-opening' ) || false;
            if ( status !== 'opening' ) {
                $el.attr( 'data-opening', 'opening' );
                that.values = $('.customify-typography-input', that.$el).val();
                that.values = JSON.parse(that.values);
                $el.addClass('customify-modal--inside');
                if (!$('.customify-modal-settings', $el).length) {
                    var $wrap = $($('#tmpl-customify-modal-settings').html());
                    that.container = $wrap;
                    this.$el.append($wrap);
                    that.ready();
                } else {
                    that.container = $('.customify-modal-settings', $el);
                }
                this.container.show();
                this.$el.addClass('modal--opening');
            } else {
                $el.attr( 'data-opening', '' );
                $('.customify-modal-settings', $el).hide();
                $el.removeClass('modal--opening');
            }
        },

        reset: function( $el ){
            this.$el = $el;
            var that = this;

            $el.attr( 'data-opening', '' );
            $('.customify-modal-settings', $el).remove();
            $el.removeClass('modal--opening');
            that.values = $('.customify-typography-input', that.$el).attr('data-default') || '[]';
            try {
                that.values = JSON.parse(that.values);
            } catch (e) {
            }

            $el.addClass('customify-modal--inside');
            if (!$('.customify-modal-settings', $el).length) {
                var $wrap = $($('#tmpl-customify-modal-settings').html());
                that.container = $wrap;
                this.$el.append($wrap);
                that.ready();
            } else {
                that.container = $('.customify-modal-settings', $el );
            }
            that.get();
        },

        get: function(){
            var data = {};
            var that = this;
            _.each( this.fields, function( f ) {
                if ( f.name === 'languages' ) {
                    f.type = 'checkboxes';
                }
                data[ f.name ] = customifyField.getValue( f, $( '.customify--group-field[data-field-name="'+f.name+'"]', that.container ) );
            });

            data.font_type = $( '.customify--font-type', that.container ).val();
            $( '.customify-typography-input', this.$el ).val( JSON.stringify( data ) ).trigger( 'change' );
            return data;
        },

        init: function(){
            this.load();
            var that = this;
            $document.on( 'click', '.customize-control-customify-typography .action--edit', function(){
                that.open( $( this ).closest('.customize-control-customify-typography').eq( 0 ) );
            } );
        }

    };
    //----------------------------------------------



    //---------------------------------------------------------------------------
    var customifyStyling  = {
        tabs: {
            normal: 'Normal',
            hover: 'Hover'
        },
        fields:  {},
        normal_fields: {},
        hover_fields: {},
        controlID: '',
        setupFields: function( fields, list ){
            var newfs;
            var i;
            var newList = [];
            if ( fields === -1  ) {
                newList = list;
            } else if ( fields === false ){
                newList = null;
            } else {
                if ( _.isObject( fields ) ) {
                    newfs = {};
                    i = 0;
                    _.each( list, function( f ){
                        if ( _.isUndefined( fields[ f.name ] ) || fields[ f.name ] ) {
                            newfs[ i ] = f;
                            i++;
                        }

                    } );

                    newList = newfs;
                }
            }
            return newList;
        },
        setupConfig: function( tabs, normal_fields, hover_fields ){
            var that = this;
            that.tabs = {};
            that.normal_fields = {};
            that.hover_fields = {};

            that.tabs = _.clone( Customify_Control_Args.styling_config.tabs );
            if ( tabs === false ) {
                that.tabs['hover'] = false;
            } else if ( _.isObject( tabs ) ) {
                that.tabs = tabs;
            }

            that.normal_fields = that.setupFields( normal_fields, Customify_Control_Args.styling_config.normal_fields );
            that.hover_fields = that.setupFields( hover_fields, Customify_Control_Args.styling_config.hover_fields );

        },
        init: function(){
            var that = this;
            $document.on( 'click', '.customize-control-customify-styling .action--edit', function(){
                that.controlID = $( this ).attr( 'data-control' ) || '';
                var c = wpcustomize.control( that.controlID );
                var tabs = null, normal_fields = -1, hover_fields = -1;
                if (  that.controlID && ! _.isUndefined( c ) ) {
                    if ( !_.isUndefined( c.params.fields ) && _.isObject( c.params.fields ) ) {

                        if ( ! _.isUndefined( c.params.fields.tabs  ) ) {
                            tabs = c.params.fields.tabs;
                        }

                        if ( ! _.isUndefined( c.params.fields.normal_fields  ) ) {
                            normal_fields = c.params.fields.normal_fields;
                        }

                        if ( ! _.isUndefined( c.params.fields.hover_fields ) ) {
                            hover_fields = c.params.fields.hover_fields;
                        }

                    }
                }

                that.setupConfig( tabs, normal_fields, hover_fields );

                that.open( $( this ).closest('.customize-control-customify-styling').eq( 0 ) );
            } );




        },
        addFields: function(){
            var that = this;
            if ( ! _.isObject( that.values ) ) {
                that.values = {};
            }
            that.values = _.defaults( that.values, {
                hover: {},
                normal: {}
            } );
            var fieldsArea = $( '.customify-modal-settings--fields', that.container );

            var tabsHTML = $( '<div class="modal--tabs"></div>' );
            var c = 0;
            _.each( that.tabs, function( label, key ){
                if ( label && ! _.isEmpty( that[ key+'_fields' ] ) ) {
                    c ++ ;
                    tabsHTML.append( '<div><span data-tab="'+key+'" class="modal--tab modal-tab--'+key+'">'+label+'</span></div>' );
                }
            } );

            fieldsArea.append( tabsHTML );
            if ( c <= 1 ) {
                tabsHTML.addClass('customify--hide');
            }

            customifyField.devices = Customify_Control_Args.devices;

            _.each(that.tabs, function( label, key ){
                if ( _.isObject( that[ key +'_fields' ] ) && !_.isEmpty( key +'_fields' ) ) {

                    var content = $('<div class="modal-tab-content modal-tab--' + key + '"></div>');
                    fieldsArea.append(content);
                    customifyField.addFields( that[ key +'_fields' ], that.values[key], content, function () {
                        that.get();
                    });
                    customifyField.initConditional(content, that.values[key]);

                }
            });


            $( 'input, select, textarea', that.container ).removeClass('customify-input').addClass( 'customify-modal-input change-by-js' );

            fieldsArea.on( 'change data-change', 'input, select, textarea', function(){
                that.get();
            } ) ;

            that.container.on( 'click', '.modal--tab', function(){
                var id = $( this ).attr( 'data-tab' ) || '';
                $( '.modal--tabs .modal--tab', that.container ).removeClass( 'tab--active' );
                $( this ).addClass( 'tab--active' );
                $( '.modal-tab-content', that.container ).removeClass('tab--active');
                $( '.modal-tab-content.modal-tab--'+id, that.container ).addClass('tab--active');
            } ) ;
            $( '.modal--tabs .modal--tab', that.container ).eq(0).trigger('click');


            $(document).mouseup(function(e) {
                var actions = $(".customify-actions .action--edit", that.$el );
                if (
                    !that.container.is(e.target) && that.container.has(e.target).length === 0
                    &&
                    !actions.is(e.target) && actions.has(e.target).length === 0
                ) {
                    that.container.hide();
                    that.$el.removeClass( 'modal--opening' );
                    that.$el.attr( 'data-opening', '' );
                }
            });

        },

        get: function(){
            var data = {};
            var that = this;
            _.each( that.tabs, function( label, key ){
                var subdata = {};
                var content = $( '.modal-tab-content.modal-tab--'+key, that.container );
                if ( _.isObject( that[key+'_fields'] ) )
                {
                    _.each( that[key+'_fields'], function (f) {
                        subdata[f.name] = customifyField.getValue(f, $('.customify--group-field[data-field-name="' + f.name + '"]', content));
                    });
                }
                data[ key ] = subdata;
                customifyField.initConditional( content, subdata );
            } );

            $( '.customify-hidden-modal-input', this.$el ).val( JSON.stringify( data ) ) .trigger( 'change' );
            console.log( 'Styling_data', data );

            return data;
        },

        open: function( $el ){
            this.$el = $el;
            var that = this;
            var status = $el.attr( 'data-opening' ) || false;
            if ( status !== 'opening' ) {
                $el.attr( 'data-opening', 'opening' );

                that.values = $('.customify-modal-input', that.$el).val();
                try {
                    that.values = JSON.parse(that.values);
                } catch ( e ){

                }

                $el.addClass('customify-modal--inside');

                if (!$('.customify-modal-settings', $el).length) {
                    var $wrap = $($('#tmpl-customify-modal-settings').html());
                    that.container = $wrap;
                    this.$el.append($wrap);
                    that.addFields();
                } else {
                    that.container = $('.customify-modal-settings', $el);
                }

                this.container.show();
                this.$el.addClass('modal--opening');
            } else {
                $el.attr( 'data-opening', '' );
                $('.customify-modal-settings', $el).hide();
                $el.removeClass('modal--opening');
            }
        },
    };

    //---------------------------------------------------------------------------



    wpcustomize.bind( 'ready', function( e, b ) {

        $document.on('customify/customizer/device/change', function (e, device) {
            $('.customify--device-select a').removeClass('customify--active');
            if (device != 'mobile') {
                $('.customify--device-mobile').addClass('customify--hide');
                $('.customify--device-general').removeClass('customify--hide');
                $('.customify--tab-device-general').addClass('customify--active');
            } else {
                $('.customify--device-general').addClass('customify--hide');
                $('.customify--device-mobile').removeClass('customify--hide');
                $('.customify--tab-device-mobile').addClass('customify--active');
            }
        });

        $document.on('click', '.customify--tab-device-mobile', function (e) {
            e.preventDefault();
            $document.trigger('customify/customizer/device/change', ['mobile']);
        });

        $document.on('click', '.customify--tab-device-general', function (e) {
            e.preventDefault();
            $document.trigger('customify/customizer/device/change', ['general']);
        });

        $('.accordion-section').each(function () {
            var s = $(this);
            var t = $('.customify--device-select', s).first();
            $('.customize-section-title', s).append(t);
        });


        // Devices Switcher
        $document.on('click', '.customify-devices button', function (e) {
            e.preventDefault();
            var device = $(this).attr('data-device') || '';
            //console.log('Device', device);
            $('#customize-footer-actions .devices button[data-device="' + device + '"]').trigger('click');
        });

        // Devices Switcher
        $document.on('change', '.customify--field input:checkbox', function (e) {
            if ($(this).is(':checked')) {
                $(this).parent().addClass('customify--checked');
            } else {
                $(this).parent().removeClass('customify--checked');
            }
        });

        // Setup conditional
        var ControlConditional = function (decodeValue) {
            if (_.isUndefined(decodeValue)) {
                decodeValue = false;
            }
            var allValues = wpcustomize.get();
            // console.log( 'ALL Control Values', allValues );
            _.each(allValues, function (value, id) {
                var control = wpcustomize.control(id);
                if (!_.isUndefined(control)) {
                    if (control.params.type == 'customify') {
                        if (!_.isEmpty(control.params.required)) {
                            var check = false;
                            check = control.multiple_compare(control.params.required, allValues, decodeValue);
                            if (!check) {
                                control.container.addClass('customify--hide');
                            } else {
                                control.container.removeClass('customify--hide');
                            }
                        }
                    }
                }

            });
        };

        $document.ready( function() {
            _.each( customify_controls_list, function( c, k){
                new customify_control( c );
            } );

            ControlConditional(false);
            $document.on('customify/customizer/change', function () {
                ControlConditional(true);
            });

            IconPicker.init();
            FontSelector.init();
            customifyStyling.init();
        });





        $document.on( 'click', '.customize-control-customify-typography .action--reset', function(){
            FontSelector.reset( $( this ).closest('.customize-control-customify-typography').eq( 0 ) );
        } );

        // Add reset button to sections
        wpcustomize.section.each( function ( section ) {
            if ( section.params.type == 'section' ) {
                section.container.find( '.customize-section-description-container .customize-section-title' ).append( '<button data-section="'+section.id+'" type="button" title="'+Customify_Control_Args.reset+'" class="customize--reset-section" aria-expanded="false"><span class="screen-reader-text">'+Customify_Control_Args.reset+'</span></button>' );
            }
        } );
        
        $document.on( 'click', '.customize--reset-section', function( e ){
            e.preventDefault();
            if ( $( this ).hasClass( 'loading' ) ) {
                return ;
            }

            if ( ! confirm( Customify_Control_Args.confirm_reset ) ) {
                return ;
            }

            $( this ).addClass( 'loading' );
            var section = $( this ).attr( 'data-section' ) || '';
            var urlParser = _.clone( window.location );
            urlParser.search = $.param( _.extend(
                wpcustomize.utils.parseQueryString( urlParser.search.substr( 1 ) ),
                {
                    autofocus: {
                        section: section
                    }
                }
            ) );

            if ( section ) {
                var setting_keys = [];
                var controls = wp.customize.section( section ).controls();
                _.each( controls, function(c , index ){
                    wpcustomize( c.id ).set( '' );
                    setting_keys[ index ] = c.id;
                } );

               $.post( ajaxurl, {
                   action: 'customify__reset_section',
                   section: section,
                   settings: setting_keys
               }, function(){
                    $( window ).off( 'beforeunload.customize-confirm' );
                    top.location.href = urlParser.origin+urlParser.pathname+'?'+urlParser.search;
               } );

            }


        } );

       // console.log( 'wpcustomize.setting', wpcustomize.settings( 'custom_logo' ) );

       // wp.customize('customify__css').set('dsadas');

    }); // end customize ready

})( jQuery, wp.customize || null );