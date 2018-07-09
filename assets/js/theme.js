function customify_is_mobile() {
    if( navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)
    ){
        return true;
    }
    else {
        return false;
    }
}


jQuery( document ).ready( function( $ ){
    var $document = $( document );
    var menu_sidebar_state = 'closed';

    if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
       // $( '#header-menu-sidebar' ).insertAfter( "#masthead" );
    } else {
        $( 'body' ).prepend(  $( '#header-menu-sidebar' ) );
    }

    if ( $( '.search-form--mobile' ).length ) {
        var search_form = $( '.search-form--mobile' ).eq(0);
        search_form.addClass('mobile-search-form-sidebar menu-sidebar-panel')
            .removeClass( 'hide-on-mobile hide-on-tablet' );
        $( 'body' ).prepend( search_form );
    }

    var is_previewing = $( 'body' ).hasClass( 'customize-previewing' );

    var insertNavIcon = function(){
        $( '.menu-item-has-children', $( '#header-menu-sidebar .nav-menu-mobile' ) ).each( function(){
            var $el = $( this );
            if ( ! $el.hasClass( 'toggle--added' ) ) {
                $el.addClass( 'toggle--added' );
                var first_a = $(' > a', $el);
                var d = first_a.clone();
                if (is_previewing) {
                   // first_a.attr('href', 'javascript:;');
                }
                first_a.append('<span class="nav-toggle-icon"><i class="nav-icon-angle"></i></span>');
                $(' > .sub-menu', $el).prepend(d);
                $(' > .sub-menu, .sub-lv-0', $el).slideUp(0);
                d.wrap('<li class="menu-item li-duplicator"></li>');
            }
        } );

    };

    // Toggle sub menu
    $document.on( 'click',  '.nav-menu-mobile .menu-item-has-children > a > .nav-toggle-icon', function( e ){
        e.preventDefault();
        var li = $( this ).closest('li');
        li.toggleClass('open-sub');
        $( ' > ul.sub-menu, .sub-lv-0', li ).slideToggle( 500 );
    } );

    var insertMenuOverlayClass = function() {
        if ( $( 'body' ).hasClass( 'menu_sidebar_slide_overlay' ) ) {
            $('.nav-menu-mobile').addClass('nav-menu-overlay');
        } else {
            $('.nav-menu-mobile').removeClass('nav-menu-overlay');
        }
    };

    var setupMobileHeight = function( $el ){
        var h = $( window ).height();
        if ( typeof ( $el ) ===  "undefined" ) {
            $el = $( '#header-menu-sidebar' );
        }

        /*
        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
           // $el.height( 0 );
        } else {
            $el.height( h );
        }
        */

        var t = .2;
        var index = 0;
        $( '.item--inner', $el ).each( function(){
            index ++ ;
            $( this ).css( { 'transition-delay' : ( index * t ) + 's' } );
        } );

    };
    setupMobileHeight();
    insertNavIcon();
    insertMenuOverlayClass();
    $( window ).resize( function(){
        setupMobileHeight();
    } );

    var open_menu_sidebar = function( toggle ){
        $( 'body' ).removeClass( 'hiding-header-menu-sidebar' );
        if ( typeof toggle === "undefined" ) {
            toggle = true;
        }

        if( ! toggle ) {
            $( 'body' ).addClass( 'is-menu-sidebar' );
        } else {
            $( 'body' ).toggleClass( 'is-menu-sidebar' );
        }

        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) ) {
            if ( toggle ) {
                $( '.menu-mobile-toggle, .menu-mobile-toggle .hamburger' ).toggleClass( 'is-active' );
            } else {
                $( '.menu-mobile-toggle, .menu-mobile-toggle .hamburger' ).addClass( 'is-active' );
            }

            if ( $( 'body' ).hasClass( 'is-menu-sidebar' ) ) {
                var h = $( '#header-menu-sidebar-inner' ).outerHeight();
                $('#header-menu-sidebar').animate({
                    height: h
                }, 300, function () {
                    // Animation complete.
                    $('#header-menu-sidebar').height( 'auto' );
                    //$( '#site-content' ).hide();
                });
            } else {
                if( toggle ) {
                    close_menu_sidebar();
                }
            }
        }
    };

    // close icon
    var close_menu_sidebar = function(){
        $( 'body' ).addClass( 'hiding-header-menu-sidebar' );
        $( 'body' ).removeClass( 'is-menu-sidebar' );
        $('.menu-mobile-toggle, .menu-mobile-toggle .hamburger').removeClass( 'is-active' );

        if ( $( 'body' ).hasClass( 'menu_sidebar_dropdown' ) )
        {
            $( 'body' ).removeClass( 'hiding-header-menu-sidebar' );
            var h = $( '#header-menu-sidebar #header-menu-sidebar-inner' ).outerHeight();
            //$( '#header-menu-sidebar' ).css( 'height', 0 );
            //$( '#site-content' ).show();
            $( '#header-menu-sidebar' ).slideUp(300, function(){
                $( '#header-menu-sidebar' ).css( { height: 0, display: 'block' } );
            });

        } else {
            setTimeout( function () {
                $( 'body' ).removeClass( 'hiding-header-menu-sidebar' );
            }, 1000 );
        }
    };

    $document.on( 'click',  '.menu-mobile-toggle', function( e ){
        e.preventDefault();
        open_menu_sidebar();
    } );

    $document.on( 'customize_section_opened', function( e, id ){
        if( id === 'header_sidebar' ) {
            open_menu_sidebar( false );
        }
    } );

    // When click close button
    $document.on( 'click',  '#header-menu-sidebar .close-panel, .close-sidebar-panel', function( e ){
        e.preventDefault();
        close_menu_sidebar();
    } );

    $document.on( 'click', function(event) {
        if ( $( 'body' ).hasClass( 'is-menu-sidebar' ) ) {
            //console.log(  'has_open_panel' );
            var $sidebar = $("#header-menu-sidebar");
            var $button = $( '.menu-mobile-toggle' );
            if (
                $sidebar.has(event.target).length == 0 //checks if descendants of $box was clicked
                &&
                !$sidebar.is(event.target) //checks if the $box itself was clicked

                &&  $button.has(event.target).length == 0
                && !$button.is(event.target)
            ) {
                close_menu_sidebar();
            } else {
                //$log.text("you clicked inside the box");
            }
        }
    });

    $document.on( 'keyup', function( e ) {
        if ( e.keyCode === 27 ) {
            close_menu_sidebar();
        }
    } );

    // When Header Panel rendered by customizer
    $document.on( 'header_builder_panel_changed', function(){
        setupMobileHeight();
        insertNavIcon();
        insertMenuOverlayClass();
        //stickyHeaders.load($(".header--row.is-sticky"));
    } );


    // Search Icon
   function search_form(){
       $( '.header-search_icon-item' ).each( function( e ){
           var container = $( this );
           container.on( 'click', '.search-icon', function( e ){
               e.preventDefault();
               container.toggleClass( 'active');
               if (  ! container.hasClass( 'active' ) ) {
                   $( '.search-field', container ).blur();
               } else {
                   $( '.search-field', container ).focus();
               }
           } );

           $document.on( 'click', function(e)
           {
               // if the target of the click isn't the container nor a descendant of the container
               if (!container.is(e.target) && container.has(e.target).length === 0)
               {
                   container.removeClass('active');
               }
           });

       } );

       /*
        if ( ! customify_is_mobile() ) {
            $('.header-search_icon-item .search-icon').blur( function (e) {
                var w = $(this).parent();
                w.removeClass('active');
            });

            $('.header-search_icon-item .search-icon, .header-search_icon-item .search-field, .header-search_icon-item .search-submit').focus(function (e) {
                var w = $(this).closest('.header-search_icon-item');
                //var $e = $( e.target );
                w.addClass('active');
                if (!$('.search-field', w).is(e.target)) {
                    $('.search-field', w).focus();
                }
            });
        }
        */

   }


    function search_box_auto_align(){
        var w = $( window ).width();
        $( '.header-search_icon-item' ).each( function(){
            var p = $( this );
            var button = p.find( '.search-icon' );
            p.removeClass( 'search-right search-left' );
            var button_offset = button.offset();
            if ( button_offset.left > w/2 ) {
                p.removeClass( 'search-right' );
                p.addClass( 'search-left' );
            } else {
                p.removeClass( 'search-left' );
                p.addClass( 'search-right' );
            }

        } );

    }

    search_form();
    search_box_auto_align();
    var tf;
    $( window ).resize( function(){
        $( '.header-search_icon-item' ).removeClass( 'active' );
        if ( tf ) {
            clearTimeout( tf );
        }
        tf = setTimeout( function(){
            search_box_auto_align();
        }, 100 );

    } );

    $document.on( 'selective-refresh-content-rendered', function( e, id ){
        if ( 'Customify_Builder_Item_Search_Icon__render' === id || id === 'customify_customize_render_header' ) {
            search_form();
            search_box_auto_align();
        }
    } );

    $("#page").fitVids();

    // Responsive table
    $( '.entry-content table' ).wrap('<div class="table-wrapper"/>');

} );



/**
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */
( function() {
    var container, button, menu, links, i, len;

    container = document.getElementById( 'site-navigation-main-desktop' );
    if ( ! container ) {
        return;
    }

    menu = container.getElementsByTagName( 'ul' )[0];
    // Hide menu toggle button if menu is empty and return early.
    if ( 'undefined' === typeof menu ) {
        return;
    }

    menu.setAttribute( 'aria-expanded', 'false' );
    if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
        menu.className += ' nav-menu';
    }

    // Get all the link elements within the menu.
    links    = menu.getElementsByTagName( 'a' );

    // Each time a menu link is focused or blurred, toggle focus.
    for ( i = 0, len = links.length; i < len; i++ ) {
        links[i].addEventListener( 'focus', toggleFocus, true );
        links[i].addEventListener( 'blur', toggleFocus, true );
    }

    /**
     * Sets or removes .focus class on an element.
     */
    function toggleFocus() {
        var self = this;

        // Move up through the ancestors of the current link until we hit .nav-menu.
        while ( -1 === self.className.indexOf( 'nav-menu' ) ) {

            // On li elements toggle the class .focus.
            if ( 'li' === self.tagName.toLowerCase() ) {
                if ( -1 !== self.className.indexOf( 'focus' ) ) {
                    self.className = self.className.replace( ' focus', '' );
                } else {
                    self.className += ' focus';
                }
            }

            self = self.parentElement;
        }
    }

    /**
     * Toggles `focus` class to allow submenu access on tablets.
     */
    ( function( container ) {
        var touchStartFn, i,
            parentLink = container.querySelectorAll( '.menu-item-has-children > a, .page_item_has_children > a' );

        if ( 'ontouchstart' in window ) {
            touchStartFn = function( e ) {
                var menuItem = this.parentNode, i;

                if ( ! menuItem.classList.contains( 'focus' ) ) {
                    e.preventDefault();
                    for ( i = 0; i < menuItem.parentNode.children.length; ++i ) {
                        if ( menuItem === menuItem.parentNode.children[i] ) {
                            continue;
                        }
                        menuItem.parentNode.children[i].classList.remove( 'focus' );
                    }
                    menuItem.classList.add( 'focus' );
                } else {
                    menuItem.classList.remove( 'focus' );
                }
            };

            for ( i = 0; i < parentLink.length; ++i ) {
                parentLink[i].addEventListener( 'touchstart', touchStartFn, false );
            }
        }
    }( container ) );

} )();


/**
 *
 * Helps with accessibility for keyboard only users.
 *
 * Learn more: https://git.io/vWdr2
 */
( function() {
    var isIe = /(trident|msie)/i.test( navigator.userAgent );

    if ( isIe && document.getElementById && window.addEventListener ) {
        window.addEventListener( 'hashchange', function() {
            var id = location.hash.substring( 1 ),
                element;

            if ( ! ( /^[A-z0-9_-]+$/.test( id ) ) ) {
                return;
            }

            element = document.getElementById( id );

            if ( element ) {
                if ( ! ( /^(?:a|select|input|button|textarea)$/i.test( element.tagName ) ) ) {
                    element.tabIndex = -1;
                }

                element.focus();
            }
        }, false );
    }
} )();
