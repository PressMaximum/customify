

(function( $, wpcustomize ) {
    var $document = $( document );

    var CustomizeBuilder = function( $el ,controlId, items ){
        var Builder = {
            controlId: '',
            cols: 12,
            cellHeight: 45,
            items: [],
            rows: {
                top: null,
                main: null,
                bottom: null
            },
            container: null,
            ready: false,
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
                /**
                 * @see https://github.com/gridstack/gridstack.js/tree/develop/doc#addedevent-items
                 *
                 * view-source:http://gridstackjs.com/demo/serialization.html
                 */
                $( '._beacon--cb-items.grid-stack', that.container ).each( function(){
                    var g =  $( this );
                    //var appendTo =  $( '._beacon--cb-items.grid-stack', that.container ).not( g );
                    var id = $( this ).data( 'id' );
                    that.rows[id] = {};
                    g.gridstack({
                        resizable: {
                            autoHide: true, handles: 'e, w'
                        },
                        acceptWidgets: '.grid-stack-item',
                        //acceptWidgets: false,
                        cellHeight: that.cellHeight,
                        height: 3,
                        //draggable: {handle: '.grid-stack-item-content', scroll: false, appendTo: appendTo }
                    });
                    that.rows[id].container = $( this );
                    that.rows[id].gridstack = g.data('gridstack');

                    $( this ).on('change', function( event, items ) {
                       that.save();
                    });

                } );
            },

            findNewPosition: function( new_node ){
                var that = this;
                var $main = $('._beacon--row-main ._beacon--cb-items', that.container );
                if ( $('.grid-stack-item', $main ).length === 0 ) {
                    new_node.x = 0;
                    return new_node;
                }
                // if still have space for this item at the end
                var last = $('.grid-stack-item', $main ).last();
                var node = last.data('_gridstack_node');

                var last_note = {
                    x: node.x,
                    y: node.y,
                    width: node.width,
                    height: node.height
                };
                var width = 1;
                var space;
                if ( last_note.x + last_note.width < that.cols ) {
                    space = that.cols - ( last_note.x + last_note.width );
                    if ( space < new_node.width ) {
                        new_node.width = space;
                    }
                    new_node.x =  last_note.x + last_note.width;
                    return new_node;
                }


                return new_node;
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
                _.each( that.items, function( node ) {
                    var item = that.addItem( node );
                    $( '._beacon-available-items', that.container ).append( item );
                } );
            },

            addExistingRowsItems: function(){
                var that = this;
                var data =  wpcustomize.control( that.controlId ).params.value;
                if ( ! _.isObject( data ) ) {
                    data = {};
                }

                _.each( that.rows, function( row, index ) {
                    var rowData = data[ index ];
                    if (! _.isUndefined( rowData ) && ! _.isEmpty( rowData ) ) {
                        _.each( rowData, function( node ){
                            var $item = $( '.grid-stack-item[data-id="'+node.id+'"]', that.container ).clone();
                            if ( $item.length > 0 ) {
                                that.rows[index].gridstack.addWidget( $item , node.x, node.y, node.width, node.height);
                            }
                        } );
                    }
                });

                that.ready = true;
            },


            addNewWidget: function ( $item ) {
                var node = {
                    x: 0,
                    y: 0,
                    width: $item.data('gs-width') || 1,
                    height: 1
                };
                node = this.findNewPosition( node );
                this.rows.main.gridstack.addWidget( $item , node.x, node.y, node.width, node.height);
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
                $document.on( 'click', '.grid-stack-item ._beacon--cb-item-remove',  function(e){
                    e.preventDefault();
                    var item = $( this ).closest('.grid-stack-item');
                    var layout = item.closest('.grid-stack');
                    var id = layout.data( 'id' );
                    if ( that.rows[id] ) {
                        that.rows[id].gridstack.removeWidget( item );
                    }
                } );
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
                _.each( that.rows, function( row, index ) {
                    var rowData = _.map($(' > .grid-stack-item:visible', row.container ), function (el) {
                        el = $(el);
                        var node = el.data('_gridstack_node');
                        return {
                            x: node.x,
                            y: node.y,
                            width: node.width,
                            height: node.height,
                            id: el.data('id') || ''
                        };
                    } );
                    data[ index ] = rowData;
                });

                wpcustomize.control( that.controlId ).setting.set( that.encodeValue( data ) );
                console.log('Panel Data: ', data );

            },

            init: function( $el, controlId, items ){
                var that = this;
                that.container = $el;
                that.controlId = controlId;
                that.items = items;


                that.drag_drop();
                that.focus();
                that.remove();
                that.addAvailableItems();
                that.addExistingRowsItems();

                $( '._beacon-available-items', that.container ).on( 'click', '.grid-stack-item', function( e ){
                    e.preventDefault();
                    var item = $( this ).clone();
                    that.addNewWidget( item );
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