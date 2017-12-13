

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

                $( '.customify--device-panel', that.container ).each( function(){
                    var panel = $( this );
                    var device = panel.data( 'device' );
                    var sortable_ids= [];
                    that.panels[ device ] = {};
                    $( '.customify--cb-items', panel ).each( function( index ){
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

                                //console.log( 'DROP Over',  ui.offset );

                            },
                            drop: function( event, ui ) {
                                var $wrapper = $( this );
                                console.log( 'drop stop', $wrapper );
                                console.log( 'drop pos', ui.position );
                                //that.grid( $wrapper, ui, event );
                                that.gridster( $wrapper, ui, event );
                                //that.updateGridFlag( $wrapper );
                                that.save();
                            }
                        } );

                    } );

                    var sidebar = $( '#_sid_mobile-sidebar', panel );
                    var sidebar_id = sidebar.attr( 'id' ) || false;

                    $( '.customify-available-items .grid-stack-item', panel ).draggable({
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
                                $( this ).find( '.grid-stack-item' ).removeAttr('style').attr( 'data-gs-width', 1 );
                                that.save();
                            }
                        });

                        that.panels[ device ][ 'sidebar' ] = sidebar;
                    }


                    $( '.customify-available-items .grid-stack-item', panel ).resizable({
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

            updateItemsPositions: function( flag ){
                var maxCol = this.cols;
                for( var i = 0; i <= maxCol; i++ ) {
                    if( typeof  flag[i] === 'object' || typeof flag[i] === 'function'  ) {
                        flag[i].attr( 'data-gs-x', i );
                    }
                }
            },

            gridster: function( $wrapper, ui ){
                var flag = [], backupFlag = [], that = this;
                var maxCol = this.cols;

                var addItemToFlag = function( node ){
                    var x = node.x, w = node.w;
                    var el = node.el;

                    for ( var i = x; i < x+w ; i++ ) {
                        if( i === x ) {
                            flag[ i ] = el; // mean start item item
                        } else {
                            flag[ i ] = 1;
                        }
                    }
                };

                var removeNode = function( node ){
                    var x = node.x, w = node.w;
                    var el = node.el;
                    for ( var i = x; i < x+w ; i++ ) {
                        flag[ i ] = 0;
                    }
                };

                var  getEmptySlots = function ( ) {
                    var emptySlots = 0;
                    for( var i = 0; i< maxCol; i++ ) {
                        if ( flag[ i ] === 0 ) {
                            emptySlots ++;
                        }
                    }

                    return emptySlots;
                };

                var getRightEmptySlotFromX = function (x, stopWhenNotEmpty){
                    var emptySlots = 0;
                    for( var i = x; i < maxCol; i++ ) {
                        if ( flag[ i ] === 0 ) {
                            emptySlots ++;
                        } else {
                            if ( stopWhenNotEmpty ) {
                                return emptySlots;
                            }
                        }
                    }
                    return emptySlots;
                };

                var getLeftEmptySlotFromX = function (x, stopWhenNotEmpty ){
                    var emptySlots = 0;
                    if ( typeof stopWhenNotEmpty === "undefined" ) {
                        stopWhenNotEmpty = false;
                    }
                    for( var i = x; i >= 0; i-- ) {
                        if ( flag[ i ] === 0 ) {
                            emptySlots ++;
                        } else {
                            if ( stopWhenNotEmpty ) {
                                return emptySlots;
                            }
                        }
                    }
                    return emptySlots;
                };

                var isEmptyX = function ( x ){
                    if ( flag[ x ] === 0 ) {
                        return true;
                    }
                    return false;
                };

                var checkEnoughSpaceFromX = function (x, w){
                    var check = true;
                    var i = x;
                    var j;
                    while ( i < x + w && check ) {
                        if ( flag[ i ] !== 0 ) {
                            return false;
                        }
                        i++;
                    }
                    return check;
                };

                var getPrevBlock = function( x ){
                    if ( x < 0 ) {
                        return {
                            x: -1,
                            w: 1
                        }
                    }

                    var i, _x = -1, _xw, found;

                    if ( flag[x] <= 1  ) {
                        i= x;
                        found = false;
                        while ( i >= 0 && ! found ) {
                            if ( flag[i] !== 1 && flag[i] !== 0 ) {
                                _x = i;
                                found = true;
                            }
                            i--;
                        }
                    } else {
                        _x = x;
                    }
                    // tìm kiếm độ rộng của chuỗi này
                    i = _x + 1;
                    _xw = _x; // chiều rộng nhỏ nhất là môt

                    while( flag[ i ] === 1 ) {
                        _xw ++ ;
                        i++;
                    }
                    return {
                        x: _x,
                        w: ( _xw + 1 ) - _x
                    }
                };

                var getNextBlock = function ( x ){
                    var i, _x = -1, _xw, found;

                    if ( flag[x] < maxCol  ) {
                        i = x;
                        found = false;
                        while ( i < maxCol && ! found ) {
                            if ( flag[i] !== 1 && flag[i] !== 0 ) {
                                _x = i;
                                found = true;
                            }
                            i++;
                        }
                    } else {
                        _x = x;
                    }
                    // tìm kiếm độ rộng của chuỗi này
                    i = _x + 1;
                    _xw = _x; // chiều rộng nhỏ nhất là môt

                    while( flag[ i ] === 1 ) {
                        _xw ++ ;
                        i++;
                    }
                    return {
                        x: _x,
                        w: ( _xw + 1 ) - _x
                    }
                };

                var moveAllItemsFromXToLeft = function( x, number ){
                    var backupFlag = flag.slice();
                    var maxNumber = getLeftEmptySlotFromX( x );

                    if ( maxNumber === 0 ) {
                        return number;
                    }
                    var prev=  getPrevBlock( x );
                    var newX = prev.x >= 0 ? prev.x + prev.w - 1 : x;
                    var nMove = number;
                    if ( number > maxNumber ) {
                        nMove = maxNumber;
                    } else {
                        nMove = number;
                    }

                    // Tim vi tri x trống về bên trái
                    var xE = 0, c = 0, i = newX;
                    while ( c <= nMove && i >= 0 ) {
                        if ( flag[i] === 0 ) {
                            c++;
                            xE = i;
                        }
                        i--;
                    }

                    // vị trí cần di chuyển tới là x và loại bỏ mọi khoảng trống trừ x đến xE
                    var flagNoEmpty = [], j = 0;
                    for ( i =  xE; i <= newX; i++ ) {
                        flag[i] =0;
                        if ( backupFlag[ i ] !== 0 ) {
                            flagNoEmpty[j] = backupFlag[ i ];
                            j++;
                        }
                    }

                    j = 0;
                    for ( i = xE; i<= newX; i++ ){
                        if ( typeof flagNoEmpty[ j ] !== "undefined" ) {
                            flag[ i ] = flagNoEmpty[ j ];
                        } else {
                            flag[ i ] = 0;
                        }
                        j++;
                    }

                    var left = number - nMove;
                    return left;

                };

                var moveAllItemsFromXToRight = function ( x, number ){
                    var backupFlag = flag.slice();
                    var maxNumber = getRightEmptySlotFromX( x );
                    if ( maxNumber === 0 ) {
                        return number;
                    }

                    var prev = getPrevBlock( x );
                    var newX = prev.x >= 0 ? prev.x : x;
                    var nMove = number;
                    if ( number <= maxNumber ) {
                        nMove = number;
                    } else {
                        nMove = maxNumber;
                    }

                    // Tim vi tri x trống về bên trái
                    var xE = x, c = 0, i = newX;
                    while ( c < nMove && i < maxCol ) {
                        if ( flag[i] === 0 ) {
                            c++;
                            xE = i;
                        }
                        i++;
                    }

                    // vị trí cần di chuyển tới là x và loại bỏ mọi khoảng trống trừ x đến xE
                    var flagNoEmpty = [], j = 0;

                    for ( i = newX ; i <= xE; i++ ) {
                        flag[i] =0;
                        if ( backupFlag[ i ] !== 0 ) {
                            flagNoEmpty[j] = backupFlag[ i ];
                            j++;
                        }
                    }

                    j = flagNoEmpty.length - 1;
                    for ( i = xE; i >= newX; i-- ){
                        if ( typeof flagNoEmpty[ j ] !== "undefined" ) {
                            flag[ i ] = flagNoEmpty[ j ];
                        } else {
                            flag[ i ] = 0;
                        }
                        j--;
                    }

                    var left = number - nMove ;
                    return left;

                };

                var updateItemsPositions = function(){
                    that.updateItemsPositions( flag );
                };



                // Chèn vào trong danh sách giới hạn với 1 phẩn tử và có độ vị trí tại X và độ dài là w
                var insertToFlag = function( node, swap ){
                    var x = node.x, w = node.w;
                    var emptySlots = getEmptySlots( );
                    // không còn bất kỳ chỗ trống nào có thể thêm dc
                    console.log( 'emptySlots', emptySlots );
                    if( emptySlots <= 0 ) {
                        return false;
                    }

                    if (checkEnoughSpaceFromX(x, w)) {
                        console.log( { x: x, w: w } );
                        addItemToFlag(node);
                        node.el.attr( 'data-gs-x', x );
                        node.el.attr( 'data-gs-width', w );
                        return true;
                    }


                    var remain = 0;
                    if ( _.isUndefined( swap ) ) {
                        swap = false;
                    }
                    if ( swap ) {
                        remain = moveAllItemsFromXToRight (x, w );
                        if (remain > 0) {
                            remain = moveAllItemsFromXToLeft(x, remain);
                        }
                    } else {
                        remain = moveAllItemsFromXToLeft (x, w );
                        if (remain > 0) {
                            remain = moveAllItemsFromXToRight(x, remain);
                        }
                    }

                    updateItemsPositions();
                    console.log( 'After moved', flag );

                    var newX = x;
                    var i;
                    var found = false;
                    var le = 0 ;
                    var re = 0;

                    while( w >= 1 ) {
                        // Nếu số chỗ trống lớn hơn hoặc  = chiều rộng của item
                        if ( emptySlots >= w ) {
                            // Nếu tại vị trí hiện tại mà đủ chỗ trống
                            if (checkEnoughSpaceFromX(x, w)) {
                                console.log( { x: x, w: w } );
                                addItemToFlag(node);
                                node.el.attr( 'data-gs-x', x );
                                node.el.attr( 'data-gs-width', w );
                                return true;
                            }

                            found = false;
                            le = getLeftEmptySlotFromX(x, true);
                            // re = getRightEmptySlotFromX(x, true);
                            // Nếu trỗ trông bên trái nhiều hơn bên phải
                            newX = x - le;
                            // tìm kiếm từ vị trí trống từ new sang bên phải xem có chỗ nào chèn dc ko ?
                            console.log( 'newX', newX );
                            i = newX;
                            while (i < maxCol && !found) {
                                if ( checkEnoughSpaceFromX(i, w) ) {
                                    console.log( 'Insert in While', { x: i, w: w } );
                                    addItemToFlag( {el: node.el, x: i, w: w});
                                    node.el.attr( 'data-gs-x', i );
                                    node.el.attr( 'data-gs-width', w );
                                    found = true;
                                    return true;
                                }
                                i++;
                            }
                        }
                        w --;
                    }


                    // Chèn vào bất kỳ đâu đủ chỗ
                    w = node.w;
                    found = false;
                    while( w >= 1 ) {
                        i = 0;
                        while (i < maxCol && !found) {
                            if ( checkEnoughSpaceFromX(i, w) ) {
                                console.log( 'Insert in While 2', { x: i, w: w } );
                                addItemToFlag( {el: node.el, x: i, w: w});
                                node.el.attr( 'data-gs-x', i );
                                node.el.attr( 'data-gs-width', w );
                                found = true;
                                return true;
                            }
                            i++;
                        }
                        w --;
                    }


                    console.log( 'Insert END While', { x: i, w: w } );

                    return false;
                };

                /**
                 * Dổi chỗ 2 item trong 1 hàng
                 * @param x Vị trị bắt đầu của item dc thay đổi
                 * @param newX Vị trí của item chuyển đến
                 */
                var swap = function( node, newX ){
                    var x = node.x;
                    var w = node.w;

                    removeNode( node );

                    console.log( 'Swap newX', newX );
                    console.log( 'Swap FLAG', flag );

                    if ( checkEnoughSpaceFromX( newX , w ) ) {
                        addItemToFlag( { el: node.el, x: newX, w: w } );
                        return true;
                    }
                    var block2 = getPrevBlock( newX );
                    insertToFlag( { el: node.el, x: newX, w: node.w }, true );
                };

                //-----------------------------------------------------------------------------------------------------------------------------
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

                x = Math.round( left/ colWidth );
                if ( x < 0 ) {
                    x = 0;
                }
                var w = that.getW( ui.draggable );
                var in_this_row;

                if ( ! ui.draggable.parent().is( $wrapper ) ) {
                    in_this_row = false;
                    console.log( 'Not in this row' );
                } else {
                    in_this_row = true;
                    console.log( 'Item in this row' );
                }

                flag = that.getFlag( $wrapper );
                console.log( 'flag', flag );
                backupFlag = flag.slice();

                var node = {
                    el: ui.draggable,
                    x: x,
                    w: w
                };

                if ( node.x <= 0 ) {
                    node.x = 0;
                }

                var did = false;
                if ( in_this_row ) {
                    node.x = parseInt( ui.draggable.attr( 'data-gs-x' ) || 0 );
                    node.w = parseInt( ui.draggable.attr( 'data-gs-width' ) || 1 );
                    console.log( 'swap node', node );
                    swap( node, x );
                    did = true;
                } else {
                    did = insertToFlag( node );
                    console.log( 'Insert node' );
                }

                console.log( 'Drop on X: ' + x + ', width: '+ w );
                console.log( 'Drop Flag: ', flag );

                if ( ! did ) {
                    ui.draggable.removeAttr('style');
                    console.log( 'Can not insert' );
                    flag = backupFlag; // rollback;
                } else {
                    // Add drop item from somewhere to current row
                    ui.draggable.removeClass( 'item-from-list' );

                    $wrapper.append(ui.draggable);
                    ui.draggable.removeAttr( 'style' );
                    console.log( 'DID Flag: ', flag );
                    //ui.draggable.attr( 'data-gs-x', x );
                    //ui.draggable.attr( 'data-gs-y', y );
                    that.draggingItem = null;
                }

                updateItemsPositions();
                that.updateAllGrids();

                //-----------------------------------------------------------------------------------------------------------------------------



            },

            updateAllGrids: function(){
                var that = this;
                _.each( that.panels[ that.activePanel ], function( row, row_id ) {
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

                //console.log( 'ui.size', ui.size );
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
               // console.log( 'resize itemInfo', itemInfo );
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

            getFlag: function( $row ){
                var that = this;
                var flag = $row.data( 'gridRowFlag' ) || [];
                var i;
                if ( _.isEmpty( flag ) ) {
                    for ( i =0; i< that.cols; i++ ) {
                        flag[ i ] = 0;
                    }
                    $row.data( 'gridRowFlag', flag );
                }
                return flag;
            },

            updateGridFlag: function( $row ){
                var that = this;
                var rowFlag = [];
                var i;
                for ( i = 0; i < that.cols; i++ ) {
                    rowFlag[ i ] = 0;
                }
                var items;
                items =  $( '.grid-stack-item', $row );
                items.each( function( index ){
                    $( this ).removeAttr( 'style' );
                    var x = that.getX( $( this ) );
                    var w = that.getW( $( this ) );

                    for ( i = x; i < x + w; i ++  ) {
                        if ( i === x ) {
                            rowFlag[ i ] = $( this );
                        } else {
                            rowFlag[ i ] = 1;
                        }
                    }

                } );
                $row.data( 'gridRowFlag', rowFlag );
                that.updateItemsPositions( rowFlag );
                that.sortGrid( $row );
                console.log( 'Update rowFlag: '+ $row.attr( 'data-id' ), rowFlag );
                return rowFlag;
            },

            addNewWidget: function ( $item, row ) {

                var that = this;
                var panel = that.container.find('.customify--device-panel.customify--panel-'+that.activePanel );
                var el = row;
                if ( ! _.isObject( el ) ) {
                    el =  panel.find( '.customify--cb-items' ).first();
                }

                var elItem = $item;
                elItem.draggable({
                    revert: "invalid",
                    appendTo: panel,
                    scroll: false,
                    zIndex: 99999,
                    handle: '.grid-stack-item-content',
                    start: function( event, ui ){
                        $( 'body' ).addClass( 'builder-item-moving' );
                        //var w = that.getW( ui.helper );
                       // var x = that.getX( ui.helper );
                        ui.helper.parent().css( 'z-index', 500 );
                    },
                    stop: function(  event, ui ){
                        $( 'body' ).removeClass( 'builder-item-moving' );
                        ui.helper.parent().css( 'z-index', 'auto' );
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
                var template_id =  'tmpl-customify--cb-panel';
                if (  $( '#'+template_id ).length == 0 ) {
                    return ;
                }
                if ( ! _.isObject( options.rows ) ) {
                    options.rows = {};
                }
                var html = template( {
                        device: device,
                        id: options.id,
                        rows: options.rows
                    }, template_id );
                return '<div class="customify--device-panel customify-vertical-panel customify--panel-'+device+'" data-device="'+device+'">'+html+'</div>';
            },

            addDevicePanels: function(){
                var that = this;
                _.each( that.devices, function( device_name, device ) {
                    var panelHTML = that.addPanel( device );
                    $( '.customify--cb-devices-switcher', that.container ).append( '<a href="#" class="switch-to-'+device+'" data-device="'+device+'">'+device_name+'</a>' );
                    $( '.customify--cb-body', that.container ).append( panelHTML );
                } );

            },

            addItem: function( node ){
                var that = this;
                var template = that.getTemplate();
                var template_id =  'tmpl-customify--cb-item';
                if (  $( '#'+template_id ).length == 0 ) {
                    return ;
                }
                var html = template( node, template_id );
                return $( html );
            },

            addAvailableItems: function(){
                var that = this;

                _.each( that.devices, function(device_name, device ){
                    var $itemWrapper = $( '<div class="customify-available-items" data-device="'+device+'"></div>' );
                    $( '.customify--panel-'+device, that.container ).append( $itemWrapper );
                    _.each( that.items, function( node ) {
                        var _d = true;
                        if ( ! _.isUndefined( node.devices ) && ! _.isEmpty( node.devices ) ) {
                            if ( _.isString( node.devices ) ) {
                                if ( node.devices != device ) {
                                    _d = false;
                                }
                            } else {
                                var _has_d = false;
                                _.each( node.devices, function( _v ){
                                    if ( device == _v ){
                                        _has_d = true;
                                    }} );
                                if ( ! _has_d ) {
                                    _d = false;
                                }
                            }
                        }

                        if ( _d ) {
                            var item = that.addItem( node );
                            $itemWrapper.append( item );
                        }

                    } );
                } );

            },

            switchToDevice: function( device, toggle_button ){
                var that = this;
                var numberDevices = _.size( that.devices );
                if( numberDevices > 1 ) {
                    $('.customify--cb-devices-switcher a', that.container).removeClass('customify--tab-active');
                    $('.customify--cb-devices-switcher .switch-to-' + device, that.container).addClass('customify--tab-active');
                    $('.customify--device-panel', that.container).addClass('customify--panel-hide');
                    $('.customify--device-panel.customify--panel-' + device, that.container).removeClass('customify--panel-hide');
                    that.activePanel = device;
                } else {
                    $('.customify--cb-devices-switcher a', that.container).addClass('customify--tab-active');
                }

                if ( _.isUndefined( toggle_button ) || toggle_button ) {
                    if ( device == 'desktop' ) {
                        $( '#customize-footer-actions .preview-desktop' ).trigger('click');
                    } else {
                        $( '#customize-footer-actions .preview-mobile' ).trigger('click');
                    }
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
                                var item = $('.customify-available-items[data-device="' + device + '"] .grid-stack-item[data-id="' + node.id + '"]').first();
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
                this.container.on( 'click', '.customify--cb-item-setting', function( e ) {
                    e.preventDefault();
                    var section = $( this ).data( 'section' ) || '';
                    console.log( 'Clicked section' , section );
                    var control = $( this ).attr( 'data-control' ) || '';
                    var did = false;
                    console.log( 'control', control );
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
                this.container.on( 'click', '.customify--cb-row-settings', function( e ){
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
                $document.on( 'click', '.customify--device-panel .customify--cb-item-remove', function ( e ) {
                    e.preventDefault();

                    var item = $( this ).closest('.grid-stack-item');
                    var panel = item.closest( '.customify--device-panel' );
                    item.attr( 'data-gs-width', 1 );
                    item.attr( 'data-gs-x', 0 );
                    item.removeAttr( 'style' );
                    $( '.customify-available-items', panel ).append( item );
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
                var that = this;
                this.container.removeClass('customify--builder--hide').addClass( 'customify--builder-show' );
                setTimeout( function(){
                    var h = that.container.height();
                    $( '#customize-preview' ).addClass( 'cb--preview-panel-show' ).css( 'bottom', h-1 );
                }, 100 );
            },
            hidePanel: function(){
                this.container.removeClass( 'customify--builder-show' );
                $( '#customize-preview' ).removeClass( 'cb--preview-panel-show' ).removeAttr('style');
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

                that.container.on( 'click', '.customify--panel-close', function(e){
                    e.preventDefault();
                    that.container.toggleClass( 'customify--builder--hide' );
                    if( that.container.hasClass('customify--builder--hide') ) {
                        $( '#customize-preview' ).removeClass( 'cb--preview-panel-show' );
                    } else {
                        $( '#customize-preview' ).addClass( 'cb--preview-panel-show' );
                    }
                } );

            },

            panelLayoutCSS: function(){
                //wpcustomize.state( 'paneVisible' ).get()
                var sidebarWidth = $( '#customize-controls' ).width();
                if ( ! wpcustomize.state( 'paneVisible' ).get() ) {
                    sidebarWidth = 0;
                }
                this.container.find( '.customify--cb-inner' ).css( {'margin-left': sidebarWidth } );
            },

            init: function( controlId, items, devices ){
                var that = this;


                var template = that.getTemplate();
                var template_id =  'tmpl-customify--builder-panel';
                var html = template( options , template_id );
                that.container = $( html );
                $( 'body .wp-full-overlay' ).append( that.container );
                that.controlId = controlId;
                that.items = items;
                that.devices = devices;

                if ( options.section ) {
                    wpcustomize.section( options.section ).container.addClass( 'customify--hide' );
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
                        that.switchToDevice( 'desktop', false );
                    } else {
                        that.switchToDevice( 'mobile', false );
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
                that.container.on( 'click', '.customify--cb-devices-switcher a', function(e){
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
        _.each( Customify_Layout_Builder.builders, function( opts, id ){
            new CustomizeBuilder( opts );
        } );

        wpcustomize.bind( 'pane-contents-reflowed', function(){
            setTimeout( function(){
                if ( $( '#sub-accordion-panel-widgets .no-widget-areas-rendered-notice .footer_moved_widgets_text' ).length ) {

                } else {
                    $( '#sub-accordion-panel-widgets .no-widget-areas-rendered-notice' ).append('<p class="footer_moved_widgets_text">'+Customify_Layout_Builder.footer_moved_widgets_text+'</p>');
                }

            }, 1000 );
        } );



        wpcustomize.bind( '_section_focus', function( e, b ) {
            console.log( '_section_focus', b );
        });

        // When focus section
        wpcustomize.state( 'expandedSection' ).bind( function( section ) {
            $( '.customify--device-panel .grid-stack-item' ).removeClass( 'item-active' );
            $( '.customify--cb-row' ).removeClass('row-active');
            if ( section ) {
                $( '.customify--cb-row[data-id="'+section.id+'"]' ).addClass('row-active');
                $( '.customify--device-panel .grid-stack-item.for-s-'+section.id ).addClass( 'item-active' );
            }
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
        if ( ! id ) {
            id = $( this ).attr( 'href' ) || '';
            id = id.replace('#','');
        }

        if ( id ) {
            if ( wpcustomize.section( id ) ) {
                wpcustomize.section( id ).focus();
            }
        }
    } );

    $document.on( 'click', '.focus-control', function( e ) {
        e.preventDefault();
        var id = $( this ).attr( 'data-id' ) || '';
        if ( ! id ) {
            id = $( this ).attr( 'href' ) || '';
            id = id.replace('#','');
        }
        if ( id ) {
            if ( wpcustomize.control( id ) ) {
                wpcustomize.control( id ).focus();
            }
        }
    } );

    $document.on( 'click', '.focus-panel', function( e ) {
        e.preventDefault();
        var id = $( this ).attr( 'data-id' ) || '';
        if ( ! id ) {
            id = $( this ).attr( 'href' ) || '';
            id = id.replace('#','');
        }
        if ( id ) {
            if ( wpcustomize.panel( id ) ) {
                wpcustomize.panel( id ).focus();
            }
        }
    } );

    // Save Template
    $document.on( 'click', '.save-template-form .save-builder-template', function( e ) {
        e.preventDefault();
        var form = $(this).closest('.customize-control');
        var input = $( '.template-input-name', form );
        var template_name =  input.val();
        if ( template_name && template_name !== '' ) {
            // Need Improve
            $.post(ajaxurl, {
                action: 'customify_builder_save_template',
                name: input.val(),
                id: input.attr('data-builder-id') || '',
                control: input.attr('data-control-id') || '',
                preview_data: wpcustomize.get()
            }, function (res) {

                if ( res.success  ) {
                    input.val('');
                    form.find('.list-saved-templates').prepend(res.data.li);
                    form.find('.list-saved-templates').addClass('has-templates');
                }

                /**
                 * @see app/public/wp-admin/js/customize-controls.js L1452
                 *  loadThemePreview
                 */

            });
        }
    });


    $document.on( 'click', '.list-saved-templates .saved_template .remove-tpl', function( e ) {
        e.preventDefault();
        var item = $( this ).parent();
        var form = $(this).closest('.customize-control');
        var input = $( '.template-input-name', form );
        var key = item.data( 'id' ) || '';
        $.post(ajaxurl, {
            action: 'customify_builder_save_template',
            id: input.attr('data-builder-id') || '',
            remove: key
        }, function (res) {
            item.remove();
            if ( form.find('.list-saved-templates li.saved_template').length <= 0 ) {
                form.find('.list-saved-templates').removeClass( 'has-templates' );
            }

        });

    });

    var encodeValue = function( value ){
        return encodeURI( JSON.stringify( value ) )
    };


    // Load templates
    $document.on( 'click', '.list-saved-templates .saved_template .load-tpl', function( e ) {
        e.preventDefault();
        var item = $( this ).parent();
        var deferred = $.Deferred();
        var urlParser;
        urlParser = document.createElement( 'a' );
        urlParser.href = location.href;

        var control_id = item.data('control-id') || '';

        urlParser.search = $.param( _.extend(
            wpcustomize.utils.parseQueryString( urlParser.search.substr( 1 ) ),
            {
                changeset_uuid: wpcustomize.settings.changeset.uuid,
                autofocus: {
                    control: control_id
                }
            }
        ) );

        var data = item.data( 'data' ) || {};
        if ( !_.isObject( data ) ) {
            data = {};
        }

        _.each( data, function( value, key ){
            if ( wpcustomize.control( key ) ) {
                wpcustomize.control( key ).setting.set( encodeValue( value ) );
            }
        } );

        var overlay = $( '.wp-full-overlay' );
        overlay.addClass( 'customize-loading' );

        var onceProcessingComplete = function(){

            var request;
            if ( wpcustomize.state( 'processing' ).get() > 0 ) {
                return;
            }

            wpcustomize.state( 'processing' ).unbind( onceProcessingComplete );

            request = wpcustomize.requestChangesetUpdate();
            request.done( function() {
                $( window ).off( 'beforeunload.customize-confirm' );
                top.location.href = urlParser.href;
                deferred.resolve();
            } );
            request.fail( function() {
                overlay.removeClass( 'customize-loading' );
                deferred.reject();
            } );

            return deferred.promise();
        };


        if ( 0 === wpcustomize.state( 'processing' ).get() ) {
            onceProcessingComplete();
        } else {
            wpcustomize.state( 'processing' ).bind( onceProcessingComplete );
        }

    });

    $document.on( 'mouseover', '.customify--cb-row .grid-stack-item', function( e ) {
        var item = $( this );
        var nameW = $( '.customify--cb-item-name',item ).innerWidth();
        var itemW = $( '.grid-stack-item-content', item ).innerWidth();
        if ( nameW > itemW - 50 ) {
            item.addClass('show-tooltip');
        }
    });

    $document.on( 'mouseleave', '.customify--cb-row .grid-stack-item', function( e ) {
        $( this ).removeClass('show-tooltip');
    });




})( jQuery, wp.customize || null );