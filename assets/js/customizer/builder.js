

(function( $, wpcustomize ) {
    var $document = $( document );

    var CustomizeBuilder = function( $el ,controlId, items ){
        var Builder = {
            controlId: '',
            cols: 12,
            cellHeight: 45,
            items: [],
            container: null,
            ready: false,
            devices: {'desktop': 'Desktop/Tablet', 'mobile': 'Mobile' },
            activePanel: 'desktop',
            panels: {},
            activeRow: 'main',
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

            drag_drop: function(){
                var that = this;
                var handleTarget;
                $( '._beacon--cb-items', that.container ).each( function(){
                    var container = $(this);
                    var numberOfCol = $( '._beacon--p-cell', container ).length;
                    var sibTotalWidth;
                    $( '._beacon--p-cell', container ).not(':eq('+( numberOfCol -1 )+')').resizable({
                   // $( '._beacon--p-cell', container ).resizable({
                        handles: 'e',
                        start: function(event, ui){

                        },
                        stop: function(event, ui){
                            var cellPercentWidth=100 * ui.originalElement.outerWidth()/ container.innerWidth();
                            ui.element.attr( 'data-width', cellPercentWidth );
                            ui.originalElement.css('width', cellPercentWidth + '%');
                        },
                        resize: function(event, ui){

                        }

                    });
                } );




                $( '._beacon--device-panel', that.container ).each( function(){
                    var panel = $( this );
                    var device = panel.data( 'device' );
                    var sortable_ids= [];
                    $( '._beacon--p-items', panel ).each( function( index ){
                        var id = '_sid_'+device+index;
                        $( this ).attr( 'id', id );
                        sortable_ids[ index ] = '#'+id;
                    });

                    $( '._beacon--p-items', panel ).each( function( index ){
                       // var id = '_sid_'+device+index;
                       // $( this ).attr( 'id', id );
                        $( this ).sortable({
                            connectWith: sortable_ids.join(','),
                            revert: false,
                            zIndex: 999,
                            helper: "clone",
                            appendTo: panel,
                            tolerance: 'pointer',
                            start: function( event, ui ){
                                $( 'body' ).addClass( 'builder-item-moving' );
                            },
                            stop: function(  event, ui ){
                                if ( ui.helper ) {
                                    ui.helper.remove();
                                }
                                $( 'body' ).removeClass( 'builder-item-moving' );
                            },
                            receive: function( event, ui ) {
                                console.log( 'item', ui.item );
                                ui.item.removeAttr( 'style' );
                                if ( ui.sender && ! ui.sender.hasClass( '_beacon--p-items' ) ) {
                                    ui.item.hide();
                                }

                                /*
                                if ( ui.item.hasClass( 'ui-draggable' ) ) {
                                    ui.item.draggable( "destroy" );
                                    ui.item.hide();
                                }
                                */

                            }
                        });
                    } );



                    $( "._beacon-available-items .grid-stack-item", panel ).draggable({
                        connectToSortable: sortable_ids.join(','),
                        helper: "clone",
                        revert: "invalid",
                        appendTo: panel,
                        zIndex: 999,
                        start: function( event, ui ){
                            $( 'body' ).addClass( 'builder-item-moving' );
                        },
                        stop: function(  event, ui ){
                            $( 'body' ).removeClass( 'builder-item-moving' );
                        }
                    });




                } );










            },

            findNewPosition: function( new_node ){

            },

            addPanel: function( device ){
                var that = this;
                var template = that.getTemplate();
                var template_id =  'tmpl-_beacon--cb-panel';
                if (  $( '#'+template_id ).length == 0 ) {
                    return ;
                }
                var html = template( {}, template_id );
                return '<div class="_beacon--device-panel _beacon-vertical-panel _beacon--panel-'+device+'" data-device="'+device+'">'+html+'</div>';
            },



            addDevicePanels: function(){
                var that = this;
                _.each( that.devices, function( device_name, device ) {
                    var panelHTML = that.addPanel( device );
                    $( '._beacon--cb-devices-switcher', that.container ).append( '<a href="#" class="switch-to-'+device+'" data-device="'+device+'">'+device_name+'</a>' );
                    $( '._beacon--cb-body', that.container ).append( panelHTML );
                } );

            },

            addItem: function( node ){
                var that = this;
                var template = that.getTemplate();
                var template_id =  'tmpl-_beacon--cb-item';
                if (  $( '#'+template_id ).length == 0 ) {
                  return ;
                }
                var html = template( node, template_id );
                return $( html );
            },

            addAvailableItems: function(){
                var that = this;

                // <div class="_beacon-available-items"></div>
                _.each( that.devices, function(device_name, device ){
                    var $itemWrapper = $( '<div class="_beacon-available-items" data-device="'+device+'"></div>' );
                    $( '._beacon--panel-'+device, that.container ).append( $itemWrapper );
                    _.each( that.items, function( node ) {
                        var item = that.addItem( node );
                        $itemWrapper.append( item );
                    } );
                } );

            },

            switchToDevice: function( device ){
                var that = this;
                $( '._beacon--cb-devices-switcher a', that.container).removeClass('_beacon--tab-active');
                $( '._beacon--cb-devices-switcher .switch-to-'+device, that.container ).addClass( '_beacon--tab-active' );
                $( '._beacon--device-panel', that.container  ).addClass( '_beacon--panel-hide' );
                $( '._beacon--device-panel._beacon--panel-'+device, that.container  ).removeClass( '_beacon--panel-hide' );
                that.activePanel = device;
            },

            addExistingRowsItems: function(){
                var that = this;
                var data =  wpcustomize.control( that.controlId ).params.value;

                that.ready = true;
            },


            addNewWidget: function ( $item ) {


            },
            focus: function(){
                $document.on( 'click', '._beacon--cb-item-setting', function( e ) {
                    e.preventDefault();
                    var section = $( this ).data( 'section' ) || '';
                    var control = $( this ).data( 'control' ) || '';
                    var did = false;
                    if ( control ) {
                        if ( ! _.isUndefined(  wpcustomize.control( control ) ) ) {
                            wpcustomize.control( control ).focus();
                            did = true;
                        }
                    }
                    if ( ! did ) {
                        if ( section && ! _.isUndefined(  wpcustomize.section( section ) ) ) {
                            wpcustomize.section( section ).focus();
                            did = true;
                        }
                    }

                } );

            },
            /**
             * @see https://github.com/gridstack/gridstack.js/tree/develop/doc#removewidgetel-detachnode
             */
            remove: function(){
                var that = this;

            },

            encodeValue: function( value ){
                return encodeURI( JSON.stringify( value ) )
            },
            decodeValue: function( value ){
                return JSON.parse( decodeURI( value ) );
            },

            save: function(){
                var that = this;
                if ( ! that.ready  ) {
                    return ;
                }

                var data = {};


                wpcustomize.control( that.controlId ).setting.set( that.encodeValue( data ) );
                console.log('Panel Data: ', data );

            },

            init: function( $el, controlId, items ){
                var that = this;
                that.container = $el;
                that.controlId = controlId;
                that.items = items;

                that.addDevicePanels();
                that.switchToDevice( that.activePanel );
                that.addAvailableItems();
                that.switchToDevice( that.activePanel );
                that.drag_drop();
                that.focus();
                that.remove();
                that.addExistingRowsItems();

                $( '._beacon-available-items', that.container ).on( 'click', '.grid-stack-item', function( e ){
                    e.preventDefault();
                    var item = $( this );
                    that.addNewWidget( item );
                } );

                // Switch panel
                that.container.on( 'click', '._beacon--cb-devices-switcher a', function(e){
                    e.preventDefault();
                    var device = $( this ).data('device');
                    that.switchToDevice( device );
                } );


            }
        };

        Builder.init( $el, controlId, items );
        return Builder;
    };


    wpcustomize.bind( 'ready', function( e, b ) {
        var Header = new CustomizeBuilder(
            $( '._beacon--customize-builder' ),
            'header_builder_panel',
            _Beacon_Layout_Builder.header_items
        );
    });


    // When data change
    /*
    wpcustomize.bind( 'change', function( e, b ) {
       console.log( 'Change' );
    });
    */


})( jQuery, wp.customize || null );