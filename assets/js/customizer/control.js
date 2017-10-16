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

            control.container.on( 'change keyup data-change', 'input, select, textarea', function(){
                control.getValue();
            } );

            control.initMedia();

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
            control.container.on( 'click',  '._beacon--field-image ._beacon--add, ._beacon--field-image ._beacon--change', function( e ) {
                e.preventDefault();
                var p = $( this ).closest('._beacon--field-image');
                control.controlMedia.setPreview( p )  ;
                control.controlMediaImage.open();
            } );

            // When add/Change
            control.container.on( 'click',  '._beacon--field-image ._beacon--remove', function( e ) {
                e.preventDefault();
                var p = $( this ).closest('._beacon--field-image');
                control.controlMedia.remove( p );
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
                case 'color':
                    //
                    break;
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
                case 'radio':
                    value = $( 'input[data-name="'+name+'"]:checked', $field ).val();
                    break;
                default:
                        value = $( '[data-name="'+name+'"]', $field ).val();
                    break;
            }

            return value;

        },
        updateRepeaterLiveTitle: function( value, $item, field ){
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
                                }

                                if ( _.isUndefined( _fv ) || _fv == '' ){
                                    _fv = control.params.l10n.untitled;
                                }
                                control.updateRepeaterLiveTitle( _fv, $item, f );

                            } );
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
                fields[index].value = '';
                if ( ! _.isUndefined( value[ f.name ] ) ) {
                    fields[index].value = value[ f.name ];
                }
            } );


            var htmlSettings = template( fields , 'tmpl-customize-control-'+control.type+'-fields');
            var $itemWrapper = $( template( htmlSettings , 'tmpl-customize-control-'+control.type+'-repeater') );
            control.container.find( '._beacon--settings-fields' ).append( $itemWrapper );
            $itemWrapper.find( '._beacon--repeater-live-title' ).html( liveTitleValue );

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
                console.log( 'control.params.value', control.params.value  );
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
            } );

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