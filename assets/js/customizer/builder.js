

(function( $, wpcustomize ) {
    var $document = $( document );

    var CustomizeBuilder = function( options ){

        var Builder = {
            controlId: '',
            cols: 12,
            cellHeight: 45,
            items: [],
            container: null,
            ready: false,
            devices: {'desktop': 'Desktop', 'mobile': 'Mobile/Tablet' },
            activePanel: 'desktop',
            panels: {},
            activeRow: 'main',
            draggingItem: null,
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

                $( '._beacon--device-panel', that.container ).each( function(){
                    var panel = $( this );
                    var device = panel.data( 'device' );
                    var sortable_ids= [];
                    that.panels[ device ] = {};
                    $( '._beacon--cb-items', panel ).each( function( index ){
                        var data_name = $( this ).attr( 'data-id' ) || '';
                        var id;
                        if ( ! data_name ) {
                            id = '_sid_'+device+index;
                        } else {
                            id = '_sid_'+device+'-'+data_name;
                        }
                        $( this ).attr( 'id', id );
                        sortable_ids[ index ] = '#'+id;
                    });
                    $( '.grid-stack', panel ).each( function(){
                        var _id = $( this ).attr( 'data-id' ) || '';
                        that.panels[ device ][ _id ] = $( this );
                        $( this ).droppable( {
                            out: function ( event, ui ) {

                            },
                            over: function( event, ui ) {
                                var $wrapper = $( this );
                                /**
                                 * @see http://api.jqueryui.com/droppable/#event-over
                                 */
                                //var $wrapper = $( this );

                                console.log( 'DROP Over',  ui.offset );

                            },
                            drop: function( event, ui ) {
                                var $wrapper = $( this );
                                console.log( 'drop stop', $wrapper );
                                console.log( 'drop pos', ui.position );
                                that.grid( $wrapper, ui, event );
                                that.sortGrid( $wrapper );
                                that.updateGridFlag( $wrapper );
                                that.save();
                            }
                        } );

                    } );

                    var sidebar = $( '#_sid_mobile-sidebar', panel );
                    var sidebar_id = sidebar.attr( 'id' ) || false;

                    $( '._beacon-available-items .grid-stack-item', panel ).draggable({
                        revert: 'invalid',
                        connectToSortable: ( sidebar_id ) ? '#'+sidebar_id : false
                    });

                    if ( sidebar.length > 0 ) {
                        sidebar.sortable({
                            revert: true,
                            change: function( event, ui ) {
                                that.save();
                            },
                            receive: function( event, ui ) {
                                $( this ).find( '.grid-stack-item' ).removeAttr('style');
                            }
                        });

                        that.panels[ device ][ 'sidebar' ] = sidebar;
                    }


                    $( '._beacon-available-items .grid-stack-item', panel ).resizable({
                        handles: 'w, e',
                        stop: function( event, ui ){
                            that.setGridWidth( ui.element.parent(), ui );
                            that.save();
                        }
                    });


                } );
            },

            sortGrid: function( $wrapper ){
                $(".grid-stack-item", $wrapper ).each( function( ){
                    var el = $( this );
                    var x = el.attr( 'data-gs-x' ) || 0;
                    x = parseInt( x );
                    var next = el.next();
                    if ( next.length > 0 ) {
                        var nx = next.attr( 'data-gs-x' ) || 0;
                        nx = parseInt( nx );
                        if ( x > nx ) {
                            el.insertAfter( next );
                        }
                    }
                } );

            },

            getX: function( $item ){
                var x = $item.attr( 'data-gs-x' ) || 0;
                return parseInt( x );
            },

            getW: function( $item ){
                var w = $item.attr( 'data-gs-width' ) || 1;
                return parseInt( w );
            },

            countEmptySlots: function( $wrapper ){
                var flag = this.getFlag( $wrapper );
                var empty_slots = 0;
                var i;
                for( i = 0; i < this.cols; i ++ ) {
                    if( flag[ i ] === 0 ){
                        empty_slots ++ ;
                    }
                }
                console.log( 'empty_slots', empty_slots );
                return empty_slots;
            },

            findElFromX: function( x, flag, $wrapper ){
                var that = this;
                var item =  false;
                if ( flag[ x ] > 1 ) {
                    item = $( '.grid-stack-item[data-gs-x="'+x+'"]', $wrapper );
                    return item;
                }
                var i = x;
                while ( i >= 0 && ! item && flag[ i ] > 0 ) {
                    if ( flag[ i ] > 1 ) {
                        item = $( '.grid-stack-item[data-gs-x="'+i+'"]', $wrapper );
                    }
                    i--;
                }

                return item;
            },

            findNextElFromX: function( x, flag, $wrapper ){
                var that = this;
                var next =  false;
                var i = x;
                while ( ! next && i < that.cols ) {
                    if ( flag[ i ] > 1 ) {
                        next = $( '.grid-stack-item[data-gs-x="'+i+'"]', $wrapper );
                    }
                    i++;
                }

                return next;
            },

            findPrevElFromX: function( x, flag, $wrapper ){
                var prev = false;
                var i = x;
                while ( ! prev && i >= 0 ) {
                    if ( flag[ i ] > 1 ) {
                        prev = $( '.grid-stack-item[data-gs-x="'+i+'"]', $wrapper );
                    }
                    i--;
                }

                return prev;
            },

            gridGetItemInfo: function( $item, flag, $wrapper ) {
                var that = this;
                var x = that.getX( $item );
                var w = that.getW( $item );
                var slot_before = 0;
                var slot_after = 0;
                var i;

                var prev =  that.findPrevElFromX( x - 1, flag, $wrapper );
                var next =  that.findNextElFromX( x + 1, flag, $wrapper );
                if ( prev && prev.length ) {
                    var px = that.getX( prev );
                    var pw = that.getW( prev );
                    for ( i = px + pw; i < x; i++ ) {
                        if ( flag[ i ] === 0 ) {
                            slot_before ++;
                        }

                    }
                } else {
                    for ( i = x; i >= 0; i-- ) {
                        if ( flag[ i ] === 0 ) {
                            slot_before ++;
                        }
                    }
                }

                if (  next && next.length ) {
                    var nx = that.getX( next );
                    for ( i = x + w; i < nx; i++ ) {
                        if ( flag[ i ] === 0 ) {
                            slot_after ++;
                        }
                    }
                } else {
                    for ( i = x + w; i < that.cols; i++ ) {
                        if ( flag[ i ] === 0 ) {
                            slot_after ++;
                        }
                    }
                }


                return {
                    flag: flag,
                    x: x,
                    w: w,
                    item: $item,
                    before: slot_before,
                    after: slot_after,
                    next: next,
                    prev: prev,
                    id: $item.attr( 'data-id' ) || '',
                    wrapper: $wrapper
                }
            },

            grid: function( $wrapper, ui ){
                var that = this;

                var wOffset =  $wrapper.offset();

                that.draggingItem = ui.draggable;

                var width  = $wrapper.width();
                var itemWidth = ui.draggable.width();
                var colWidth = width/that.cols;
                var x = 0;
                var y = 1;
                var left = 0;

                var iOffset = ui.offset;

                // Vị trí con trỏ chuột cách mép trái của wapper
                left = iOffset.left - wOffset.left;
                //console.log( 'Drop Left', left );

                x = Math.round( left/ colWidth );
                if ( x < 0 ) {
                    x = 0;
                }
                var w = that.getW( ui.draggable );
                var slots_empty = that.countEmptySlots( $wrapper );
                var hasSpace = slots_empty >= w ? true : false;

                var in_this_row;

                if ( ui.draggable.hasClass( 'item-from-list' ) ) {
                    if ( slots_empty > 0 && ! hasSpace ) {
                        w = 1;
                        ui.draggable.attr( 'data-gs-width', w );
                        hasSpace = true;
                    }
                }


                // Not enough space to drop
                //Revert
                if ( ! ui.draggable.parent().is( $wrapper ) ) {
                    in_this_row = false;
                    console.log( 'Not in this row' );
                    if ( ! hasSpace ) {
                        ui.draggable.removeAttr('style');
                        console.log('Not enough space', w);
                        return;
                    }
                } else {
                    in_this_row = true;
                    console.log( 'Item in this row' );
                }

                var flag = that.getFlag( $wrapper );
                console.log( 'Drop on X: ' + x + ', width: '+ w );
                console.log( 'Drop Flag: ', flag );

                // try to drop on this pos
                var i;
                var check = true;
                for( i = x; i < x + w; i++ ) {
                    if ( flag[ i ] > 0 ) {
                        check = false;
                    }
                }

                //
                if ( ! check ) {
                    console.log( '_____' );

                    $( '.grid-stack-item', $wrapper ).each( function(){
                        $( this ).attr( 'data-revert', $( this ).attr( 'data-gs-x' ) );
                    } );



                    var moveLeft = function( itemInfo, slots ) {

                        if ( slots <= 0 ) {
                            return ;
                        }

                        var steps = itemInfo.before;
                        if ( slots <= itemInfo.before ) {
                            steps = slots;
                        }

                        var newX = itemInfo.x - steps;

                        // remove index
                        for ( i = itemInfo.x; i < itemInfo.x + itemInfo.w; i ++  ) {
                            flag[ i ] = 0;
                        }

                        //move to new index
                        for ( i = newX; i < newX + itemInfo.w; i ++  ) {
                            if ( i === newX ) {
                                flag[ i ] = 2;
                            } else {
                                flag[ i ] = 1;
                            }
                        }

                        console.log( 'moveLeft Flag', flag );

                        itemInfo.item.attr( 'data-gs-x', newX );
                        var slot_left = slots - itemInfo.before;
                        if ( slot_left <= 0 ) {
                            return 0;
                        }

                        if ( itemInfo.prev && itemInfo.prev.length ) {
                            slot_left = moveLeft( that.gridGetItemInfo( itemInfo.prev, itemInfo.flag, itemInfo.wrapper ), slot_left );
                        }

                        return slot_left;

                    };

                    var moveRight = function( itemInfo, slots ) {
                        if ( slots <= 0 ) {
                            return ;
                        }

                        var steps = itemInfo.after;
                        if ( slots <= itemInfo.after ) {
                            steps = slots;
                        }

                        var newX = itemInfo.x + steps;
                        var i;

                        // remove index
                        for ( i = itemInfo.x; i < itemInfo.x + itemInfo.w; i ++  ) {
                            flag[ i ] = 0;
                        }

                        //move to new index
                        for ( i = newX; i < newX + itemInfo.w; i ++  ) {
                            if ( i === newX ) {
                                flag[ i ] = 2;
                            } else {
                                flag[ i ] = 1;
                            }
                        }

                        console.log( 'moveRight Flag New '+newX, flag );

                        itemInfo.item.attr( 'data-gs-x', newX );
                        var slot_left = slots - itemInfo.after;
                        if ( slot_left <= 0 ) {
                            return 0;
                        }


                        if ( itemInfo.next && itemInfo.next.length ) {
                            slot_left = moveRight( that.gridGetItemInfo( itemInfo.next, itemInfo.flag, itemInfo.wrapper  ), slot_left );
                        }

                        return slot_left;

                    };

                    // move other items to have spaces
                    var next, prev;

                    if ( x === 0 ) {
                        next = $( '.grid-stack-item', $wrapper ).first();
                        prev = false;
                    } else {
                        var item = that.findElFromX( x, flag, $wrapper );
                        if ( ! item || item.length <= 0 ) {
                            next = that.findNextElFromX( x, flag, $wrapper );
                            prev = item;
                        } else {
                            next = that.findNextElFromX( x, flag, $wrapper );
                            prev = that.findPrevElFromX( x, flag, $wrapper );
                        }
                    }

                    console.log('Next item', next );
                    var move_slots = w;
                    if ( next.length ) {
                        var nextInfo = that.gridGetItemInfo( next, flag, $wrapper );
                        move_slots = moveRight( nextInfo, move_slots );
                        console.log('Next Item info', nextInfo );
                    }
                    console.log('prev item', prev );
                    if ( prev.length ) {
                        var prevInfo = that.gridGetItemInfo( prev, flag, $wrapper );
                        move_slots = moveLeft( prevInfo, move_slots );
                        console.log('Prev Item info', prevInfo );
                    }

                } // end check


                console.log( 'NEW FLAG', flag );
                var getNewPosX = function( x, w ){
                    var i = x ;
                    if ( flag[ i + w ] > 0 ) {
                        console.log( 'getNewPosX down' );
                        while ( flag[ i + w ] > 0 && i >= 0 ) {
                            i--;
                        }
                        i = i + 1;
                    } else if ( flag[ i ] > 0 ) {
                        console.log( 'getNewPosX up' );
                        while ( flag[ i ] > 0 && i < that.cols ) {
                            i ++ ;
                        }
                        //i = i - 1;
                    }

                    console.log( 'New get post', i );
                    return i;
                };

                x = getNewPosX( x, w );

                var check_revert = false;
                for ( i = x; i< x + w; i++ ) {
                    if ( flag[i] > 0 ) {
                        check_revert = true;
                    }
                }

                if ( x + w > that.cols ) {
                    check_revert = true;
                }

                if ( check_revert ) {
                    // revert
                    console.log( 'revert', x + '-'+w );
                    ui.draggable.removeAttr( 'style' );
                    $( '.grid-stack-item', $wrapper ).each( function(){
                        var id = $( this ).attr( 'data-id' );
                        var rx = $( this ).attr( 'data-revert' ) || '';
                        if (  ! _.isEmpty( rx ) ) {
                            $( this ).attr( 'data-gs-x', rx );
                        }
                        $( this ).attr( 'data-revert', '' );
                    } );

                    that.draggingItem = null;

                    _.each( that.panels[ that.activePanel ], function( row, row_id ) {
                        that.updateGridFlag( row );
                    });
                    return ;
                }

                console.log( 'No revert',  x + '-'+w  );

                // Add drop item from somewhere to current row
                ui.draggable.removeClass( 'item-from-list' );
                $wrapper.append(ui.draggable);

                ui.draggable.removeAttr( 'style' );
                ui.draggable.attr( 'data-gs-x', x );
                ui.draggable.attr( 'data-gs-y', y );

                that.draggingItem = null;

                that.updateAllGrids( );
            },

            updateAllGrids: function(){
                var that = this;
                _.each(  that.panels[ that.activePanel ], function( row, row_id ) {
                    that.updateGridFlag( row );
                });
            },

            setGridWidth: function( $wrapper, ui ){
                var that = this;
                var $item = ui.element;
                var next  = $item.next();
                var prev  = $item.prev();

                var width  = $wrapper.width();
                var itemWidth = ui.size.width;
                var colWidth = width/that.cols;

                console.log( 'ui.size', ui.size );

                var isShiftLeft = ui.originalPosition.left > ui.position.left;
                var isShiftRight = ui.originalPosition.left < ui.position.left;

                var ow =  ui.originalElement.attr( 'data-gs-width' ) || 1;
                var ox =  ui.originalElement.attr( 'data-gs-x' ) || 0;
                ow = parseInt( ow );
                ox = parseInt( ox );

                console.log( 'ow', ow );
                console.log( 'ox', ox );

                var addW;
                var empty_slots;
                var preX;
                var preW;
                var newX;
                var newW;
                var flag = that.getFlag( $wrapper );
                var itemInfo = that.gridGetItemInfo( ui.originalElement, flag, $wrapper );
                console.log( 'resize itemInfo', itemInfo );
                var diffLeft, diffRight;

                if ( isShiftLeft ) {
                    diffLeft = ui.originalPosition.left - ui.position.left;
                    addW =  Math.ceil( diffLeft / colWidth );

                    if ( addW > itemInfo.before ) {
                        addW = itemInfo.before;
                    }
                    newX = ox - addW;
                    newW = ow + addW;

                    $item.attr('data-gs-x', newX ).removeAttr('style');
                    $item.attr('data-gs-width', newW ).removeAttr('style');

                    that.updateGridFlag( $wrapper );

                    return ;

                } else if( isShiftRight ) {
                    diffRight = ui.position.left - ui.originalPosition.left ;
                    addW =  Math.ceil( diffRight / colWidth );

                    newW = ow - addW;
                    if (  newW <= 0 ) {
                        newW = 1;
                        addW = 0;
                    }

                    newX = ox + addW;
                    $item.attr('data-gs-x', newX ).removeAttr('style');
                    $item.attr('data-gs-width', newW ).removeAttr('style');

                    that.updateGridFlag( $wrapper );

                    return ;
                }

                var w ;
                var x = itemInfo.x;
                if ( itemWidth <  ui.originalSize.width ) {
                    w = Math.floor( itemWidth/ colWidth  );
                } else {
                    w = Math.ceil( itemWidth/ colWidth );
                    if ( itemInfo.x + w > itemInfo.x + itemInfo.w + itemInfo.after ) {
                        w = itemInfo.w + itemInfo.after;
                    }
                }


                if ( w <= 0 ) {
                    w = 1;
                }

                $item.attr('data-gs-width', w ).removeAttr('style');
                that.updateGridFlag( $wrapper );

            },

            removeFlag: function( $row, x, w ){
                var  flag = this.getFlag( $row );
                var i;
                for ( i = x; i < x + w; i ++  ) {
                    flag[ i ] = 0;
                }
                //console.log( 'removeFlag: '+$row.attr( 'data-id' ), flag );
                $row.data( 'gridflag', flag );
                return flag;
            },

            getFlag: function( $row ){
                var that = this;
                var flag = $row.data( 'gridflag' ) || {};
                var i;
                if ( _.isEmpty( flag ) ) {
                    for ( i =0; i< that.cols; i++ ) {
                        flag[ i ] = 0;
                    }
                    $row.data( 'gridflag', flag );
                }
                return flag;
            },

            updateGridFlag: function( $row ){
                var that = this;

                var flag = {};
                var i;
                for ( i = 0; i < that.cols; i++ ) {
                    flag[ i ] = 0;
                }
                var items;

                items =  $( '.grid-stack-item', $row );
                items.each( function( index ){
                    var x = that.getX( $( this ) );
                    var w = that.getW( $( this ) );

                    for ( i = x; i < x + w; i ++  ) {
                        if ( i === x ) {
                            flag[ i ] = 2;
                        } else {
                            flag[ i ] = 1;
                        }
                    }

                } );

                $row.data( 'gridflag', flag );
                if ( $row.attr( 'data-id' ) == 'main' ) {
                    console.log( 'Update Flag: '+$row.attr( 'data-id' ), flag );
                }

                return flag;
            },

            addNewWidget: function ( $item, row ) {

                var that = this;
                var panel = that.container.find('._beacon--device-panel._beacon--panel-'+that.activePanel );
                var el = row;
                if ( ! _.isObject( el ) ) {
                    el =  panel.find( '._beacon--cb-items' ).first();
                }

                var elItem = $item;
                elItem.draggable({
                    revert: "invalid",
                    appendTo: panel,
                    scroll: false,
                    zIndex: 999,
                    handle: '.grid-stack-item-content',
                    start: function( event, ui ){
                        $( 'body' ).addClass( 'builder-item-moving' );
                        var w = that.getW( ui.helper );
                        var x = that.getX( ui.helper );
                        that.removeFlag( ui.helper.parent(), x, w );
                    },
                    stop: function(  event, ui ){
                        $( 'body' ).removeClass( 'builder-item-moving' );
                        that.save();
                    },
                    drag: function( event, ui ){

                    }
                }).resizable({
                    handles: 'w, e',
                    stop: function( event, ui ){
                        that.setGridWidth( ui.element.parent(), ui );
                        that.save();
                    }
                });

                el.append( elItem );
                that.updateGridFlag( el );

            },

            addPanel: function( device ){
                var that = this;
                var template = that.getTemplate();
                var template_id =  'tmpl-_beacon--cb-panel';
                if (  $( '#'+template_id ).length == 0 ) {
                    return ;
                }
                var html = template( { device: device, id: options.id }, template_id );
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

                if ( device == 'desktop' ) {
                    $( '#customize-footer-actions .preview-desktop' ).trigger('click');
                } else {
                    $( '#customize-footer-actions .preview-mobile' ).trigger('click');
                }

            },

            addExistingRowsItems: function(){
                var that  = this;

                var data = wpcustomize.control( that.controlId ).params.value;
                if ( ! _.isObject( data ) ) {
                    data = {};
                }
                _.each( that.panels, function( $rows,  device ) {
                    var device_data = {};
                    if ( _.isObject( data[ device ] ) ) {
                        device_data = data[ device ];
                    }
                    _.each( device_data, function( items, row_id ) {
                        if( ! _.isUndefined( items ) ) {
                            _.each( items, function (node, index) {
                                var item = $('._beacon-available-items[data-device="' + device + '"] .grid-stack-item[data-id="' + node.id + '"]').first();
                                item.attr('data-gs-width', node.width);
                                item.attr('data-gs-x', node.x);
                                item.removeClass( 'item-from-list' );
                                that.addNewWidget( item,  $rows[ row_id  ] );
                            });
                        }
                    });
                });

                that.ready = true;
            },

            focus: function(){
                this.container.on( 'click', '._beacon--cb-item-setting', function( e ) {
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

                // Focus rows
                this.container.on( 'click', '._beacon--cb-row-settings', function( e ){
                    e.preventDefault();
                    var id = $( this ).attr( 'data-id' ) || '';

                    var section = options.id + '_'+id;

                    if ( ! _.isUndefined(  wpcustomize.section( section ) ) ) {
                        wpcustomize.section( section ).focus();
                    }

                } );

            },
            /**
             * @see https://github.com/gridstack/gridstack.js/tree/develop/doc#removewidgetel-detachnode
             */
            remove: function(){
                var that = this;
                $document.on( 'click', '._beacon--device-panel ._beacon--cb-item-remove', function ( e ) {
                    e.preventDefault();

                    var item = $( this ).closest('.grid-stack-item');
                    var panel = item.closest( '._beacon--device-panel' );
                    item.attr( 'data-gs-width', 1 );
                    item.attr( 'data-gs-x', 0 );
                    item.removeAttr( 'style' );
                    $( '._beacon-available-items', panel ).append( item );
                    that.updateAllGrids();
                    that.save();
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
                _.each( that.panels, function( $rows,  device ) {
                    data[device] = {};
                    _.each( $rows, function( row, row_id ) {
                        var rowData = _.map( $( ' > .grid-stack-item', row ), function (el) {
                            el = $(el);
                            return {
                                x: that.getX( el ),
                                y: 1,
                                width: that.getW( el ),
                                height: 1,
                                id: el.data('id') || ''
                            };

                        });
                        data[device][row_id] = rowData;
                    });
                });

                wpcustomize.control( that.controlId ).setting.set( that.encodeValue( data ) );
                console.log('Panel Data: ', data );

            },

            showPanel: function(){
                //wpcustomize.state( 'expandedPanel' ).bind( function( paneVisible ) {
                    //console.log( 'expandedPanel state', paneVisible );
               // });
                //this.container.show();
                this.container.addClass( '_beacon--builder-hide' );
            },
            hidePanel: function(){
                //wpcustomize.state( 'expandedPanel' ).bind( function( paneVisible ) {
                //console.log( 'expandedPanel state', paneVisible );
                // });
                //this.container.hide();
                this.container.removeClass( '_beacon--builder-hide' );
            },

            togglePanel: function(){
                var that = this;
                wpcustomize.state( 'expandedPanel' ).bind( function( paneVisible ) {
                    if ( wpcustomize.panel( options.panel ).expanded() ) {
                        that.showPanel();
                    } else {
                        that.hidePanel();
                    }
                });
            },

            panelLayoutCSS: function(){
                //wpcustomize.state( 'paneVisible' ).get()
                var sidebarWidth = $( '#customize-controls' ).width();
                if ( ! wpcustomize.state( 'paneVisible' ).get() ) {
                    sidebarWidth = 0;
                }
                this.container.find( '._beacon--cb-inner' ).css( {'margin-left': sidebarWidth } );
            },

            init: function( controlId, items, devices ){
                var that = this;


                var template = that.getTemplate();
                var template_id =  'tmpl-_beacon--builder-panel';
                var html = template( { id: options.id }, template_id );
                that.container = $( html );
                $( 'body .wp-full-overlay' ).append( that.container );
                that.controlId = controlId;
                that.items = items;
                that.devices = devices;

                console.log( wpcustomize.control( that.controlId ).container );
                if ( options.section ) {
                    wpcustomize.section( options.section ).container.addClass( '_beacon--hide' );
                }


                that.addDevicePanels();
                that.switchToDevice( that.activePanel );
                that.addAvailableItems();
                that.switchToDevice( that.activePanel );
                that.drag_drop();
                that.focus();
                that.remove();
                that.addExistingRowsItems();


                if ( wpcustomize.panel( options.panel ).expanded() ) {
                    that.showPanel();
                } else {
                    that.hidePanel();
                }

                wpcustomize.previewedDevice.bind( function( newDevice ) {
                    if ( newDevice === 'desktop' ) {
                        that.switchToDevice( 'desktop' );
                    } else {
                        that.switchToDevice( 'mobile' );
                    }
                });

                that.togglePanel();
                if ( wpcustomize.state( 'paneVisible' ).get() ) {
                    that.panelLayoutCSS();
                }
                wpcustomize.state( 'paneVisible' ).bind( function(){
                    that.panelLayoutCSS();
                } );


                $( window ).resize( _.throttle( function(){
                    that.panelLayoutCSS();
                }, 100 )  );

                // Switch panel
                that.container.on( 'click', '._beacon--cb-devices-switcher a', function(e){
                    e.preventDefault();
                    var device = $( this ).data('device');
                    that.switchToDevice( device );
                } );

            }
        };

        Builder.init( options.control_id, options.items, options.devices );
        return Builder;
    };


    /*
    // Extend panel
    var _panelFocus = wpcustomize.Panel.prototype.focus;
    var _panelAttachEvents = wpcustomize.Panel.prototype.attachEvents;
    wp.customize.Panel = wp.customize.Panel.extend({
        attachEvents: function () {
            var panel = this;
            _panelAttachEvents.call( this );
            panel.expanded.bind( function( expanded ) {
                console.log( '_panelAttachEvents expand - ' +panel.id, expanded );
            });
        },

        focus: function ( params ) {
            var panel = this;
            _panelFocus.call( this );
            wpcustomize.bind( '__panelFocus_focus', [ panel ] );
            console.log( '_panelFocus Focusted', panel.id );
        }

    });



    // Extend Section
    var _sectionFocus = wpcustomize.Section.prototype.focus;
    var _sectionAttachEvents = wpcustomize.Section.prototype.attachEvents;
    wp.customize.Section = wp.customize.Section.extend({
        attachEvents: function () {
            var meta, content, section = this;
            _sectionAttachEvents.call( this );
            section.expanded.bind( function( expanded ) {
                console.log( 'expand section', expanded );
            });
        },

        focus: function ( params ) {
            var section = this;
            _sectionFocus.call( this );
            wpcustomize.bind( '_section_focus', [ section ] );
            console.log( 'section focus', section );
        }
    });
    */



    wpcustomize.bind( 'ready', function( e, b ) {

        var Header = new CustomizeBuilder( _Beacon_Layout_Builder.header );


        wpcustomize.bind( '_section_focus', function( e, b ) {
            console.log( '_section_focus', b );
        });

       //Event when panel toggle
        /**
         * See /wp-admin/js/customize-controls.js L4690
         */
        /*
        wpcustomize.state( 'paneVisible' ).bind( function( paneVisible ) {
            console.log( 'paneVisible state', paneVisible );
        });

        wpcustomize.state( 'expandedPanel' ).bind( function( paneVisible ) {
            console.log( 'expandedPanel state', paneVisible );
            console.log( 'expandedPanel state Builder', wpcustomize.state( 'expandedPanel' ).get() );
        });
        */

    });


    // Focus
    $document.on( 'click', '.focus-section', function( e ) {
        e.preventDefault();
        var id = $( this ).attr( 'data-id' ) || '';
        if ( id ) {
            if ( wpcustomize.section( id ) ) {
                wpcustomize.section( id ).focus();
            }
        }
    } );

    $document.on( 'click', '.focus-control', function( e ) {
        e.preventDefault();
        var id = $( this ).attr( 'data-id' ) || '';
        if ( id ) {
            if ( wpcustomize.control( id ) ) {
                wpcustomize.control( id ).focus();
            }
        }
    } );

    $document.on( 'click', '.focus-panel', function( e ) {
        e.preventDefault();
        var id = $( this ).attr( 'data-id' ) || '';
        if ( id ) {
            if ( wpcustomize.panel( id ) ) {
                wpcustomize.panel( id ).focus();
            }
        }
    } );

    // Save Template
    $document.on( 'click', '.save-template-form .save-builder-template', function( e ) {
        e.preventDefault();
        var form = $(this).closest('.save-template-form');
        var input = $( '.template-input-name', form );
        var template_name =  input.val();
        if ( template_name && template_name !== '' ) {
            // Need Improve
            $.post(ajaxurl, {
                action: '_beacon_builder_save_template',
                name: input.val(),
                id: input.attr('data-builder-id') || '',
                preview_data: wpcustomize.get()
            }, function (res) {
                input.val('');

                /**
                 * @see app/public/wp-admin/js/customize-controls.js L1452
                 *  loadThemePreview
                 */


            });
        }


    });


    // Load templates
    $document.on( 'click', '.list-saved-templates .saved_template', function( e ) {
        e.preventDefault();
        var deferred = $.Deferred();
        var urlParser;
        urlParser = document.createElement( 'a' );
        urlParser.href = location.href;
        urlParser.search = $.param( _.extend(
            wpcustomize.utils.parseQueryString( urlParser.search.substr( 1 ) ),
            {
                changeset_uuid: wpcustomize.settings.changeset.uuid
            }
        ) );

        console.log( ' urlParser.href',  urlParser.href );

        $( window ).off( 'beforeunload.customize-confirm' );
        top.location.href = urlParser.href;
        window.location = urlParser.href;
        deferred.resolve();
        return deferred.promise();

    });




})( jQuery, wp.customize || null );