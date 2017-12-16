jQuery( document ).ready( function( $ ){
    var $document = $( document );
    $( 'body' ).prepend(  $( '#mobile-header-panel' ) );

    if ( $( '.search-form--mobile' ).length ) {
        var search_form = $( '.search-form--mobile' ).eq(0);
        search_form.addClass('mobile-search-form-sidebar mobile-sidebar-panel')
            .removeClass( 'hide-on-mobile hide-on-tablet' );
        $( 'body' ).prepend( search_form );
    }


    var insertNavIcon = function(){
        $( '.menu-item-has-children', $( '#mobile-header-panel .nav-menu-mobile' ) ).each( function(){
            var $el = $( this );
            $( '<span class="nav-t-icon"></span>' ).insertBefore( $( '.sub-menu', $el ) );
        } );

    };

    var setupMobileHeight = function( $el ){
        var h = $( window ).height();
        if ( typeof ( $el ) ===  "undefined" ) {
            $el = $( '#mobile-header-panel' );
        }
        $el.height( h );
    };
    setupMobileHeight();
    insertNavIcon();
    $( window ).resize( function(){
        setupMobileHeight();
    } );

    $document.on( 'click',  '.nav-mobile-toggle', function( e ){
        e.preventDefault();
        $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
        $( 'body' ).toggleClass( 'display-mobile-header-panel' );
    } );

    $document.on( 'click',  '#mobile-header-panel .close-panel', function( e ){
        e.preventDefault();
        $( 'body' ).addClass( 'hiding-mobile-header-panel' );
        $( 'body' ).removeClass( 'display-mobile-header-panel' );
        setTimeout( function () {
            $( 'body' ).removeClass( 'hiding-mobile-header-panel' );
        }, 1000 );
    } );

    // Toggle sub menu
    $document.on( 'click',  'li .nav-t-icon', function( e ){
        e.preventDefault();
        $( this ).parent().toggleClass('open-sub');
    } );

    // Toggle Header Search
    $document.on( 'click',  '.builder-item--search .search-toggle,  .mobile-search-form-sidebar .close', function( e ){
        e.preventDefault();
        var form = $('.mobile-search-form-sidebar');
        setupMobileHeight( form );
        form.toggleClass('builder-item--search-show');
        if ( form.hasClass( 'builder-item--search-show' ) ) {
            $('body').addClass( 'display-mobile-form-panel' );
        } else {
            $('body').removeClass( 'display-mobile-form-panel' );
        }
    } );


    var stickyHeaders = (function() {

        var that = this;
        that.$el = null;
        that.$sticky = null;

        var lastScrollTop = 0;


        var $window = $(window),
            $stickies;

        var getTop = function( onlyAdminBar ){
            var top = 0;
            if ( $( '#wpadminbar' ).length ) {
                if ( $( '#wpadminbar' ).css('position') == 'fixed' ) {
                    top = $( '#wpadminbar' ).height();
                }
            }
            if ( typeof onlyAdminBar === "undefined" || onlyAdminBar ) {
                top += that.$sticky.height();
            }

            return top;
        };

        var _whenScrolling = function() {
            var scrollTop = $window.scrollTop();
            var top;
            var direction = '';
            if (scrollTop > lastScrollTop){
                // downscroll code
                direction = 'down';
            } else {
                // upscroll code
                direction = 'up';
            }
            lastScrollTop = scrollTop;

            if ( direction === 'down' ) {
                top = getTop( true );
                $('>.header--row.is-sticky', that.$el).each(function () {
                    var row = $(this);
                    var row_id = row.attr('id');
                    var ot = row.offset().top;

                    if (scrollTop >= ot - top ) {
                        row.wrap('<div id="' + row_id + '--backup" data-row-id="'+row_id+'" class="row--backup"></div>');
                        $('#' + row_id + '--backup ').height(row.height());
                        that.$sticky.append(row);
                    }
                });
            } else {
                top = getTop( false );
                var adminBar = getTop( false );
                $( ' >.row--backup', that.$el ).each( function () {
                    var row_backup = $(this);
                    var row_id = row_backup.attr('data-row-id');
                    var row = $( '#'+row_id, that.$el );
                    //var ot = row_backup.offset().top;
                    var ot = row.offset().top;

                    if ( scrollTop + adminBar <= top || scrollTop === ot) {
                        //console.log( ot );
                        row_backup.replaceWith( $( '#'+row_id, that.$el ) );
                    }

                } );
            }


        };

        var load = function( $el ){
            that.$el = $el;

            var id = that.$el.attr( 'id' ) || '';
            if ( ! id ) {
                id = 'id-sticky--'+( new Date().getTime() );
            } else {
                id += '--sticky';
            }

            that.$el.height( that.$el.height() );
            that.$sticky = $( "<div id='"+id+"' class='wrapper-sticky'>" );
            that.$el.prepend( that.$sticky );
            that.$sticky.css( 'top', getTop() );



            $window.off("scroll.stickies").on("scroll.stickies", function() {
                _whenScrolling();
            });

        };

        return {
            load: load
        };

    })();

    stickyHeaders.load($("#masthead"));


    // When Header Panel rendered by customizer
    $document.on( 'header_builder_panel_changed', function(){
        setupMobileHeight();
        insertNavIcon();
        //stickyHeaders.load($(".header--row.is-sticky"));
    } );










} );