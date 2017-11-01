<?php

class _Beacon_Customizer_Layout_Builder {
    static $_instance;
    function __construct()
    {
        require_once get_template_directory().'/inc/customizer-layout-builder/config/header-builder.php';

        add_action( 'customize_controls_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
    }

    function scripts(){
        wp_enqueue_script( 'jquery-ui-resizable' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
        wp_enqueue_script( 'gridstack.js', get_template_directory_uri() . '/assets/js/customizer/gridstack.js' );
        wp_enqueue_script( 'gridstack.jQueryUI.js', get_template_directory_uri() . '/assets/js/customizer/gridstack.jQueryUI.js' );

        wp_enqueue_script( 'jquery.gridster.js', get_template_directory_uri() . '/assets/js/customizer/jquery.gridster.js' );


        wp_enqueue_script( '_beacon-layout-builder', get_template_directory_uri() . '/assets/js/customizer/builder.js', array( 'customize-controls', 'jquery-ui-resizable', 'jquery-ui-droppable', 'jquery-ui-draggable', 'gridstack.js', 'gridstack.jQueryUI.js', 'jquery.gridster.js' ), false, true );
        wp_localize_script( '_beacon-layout-builder',  '_Beacon_Layout_Builder',  array(
            'header_items' => $this->get_header_items(),
            'header_devices' => array(
                'desktop' => __( 'Desktop', '_beacon' ),
                'tablet' => __( 'Tablet', '_beacon' ),
                'mobile' => __( 'Mobile', '_beacon' ),
            ),
        ) );
    }

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance ;
    }

    function get_header_items(){
        $items = array(
            array(
                'name' => __( 'Logo', '_beacon' ),
                'id' => 'logo',
                'width' => '3',
                'section' => 'header_logo' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Nav Icon', '_beacon' ),
                'id' => 'nav-icon',
                'width' => '3',
                'section' => 'header_nav_icon' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Primary Menu', '_beacon' ),
                'id' => 'nav-menu',
                'width' => '6',
                'section' => 'header_menu_primary' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Search', '_beacon' ),
                'id' => 'search',
                'col' => 0,
                'width' => '3',
                'section' => 'search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Social Icons', '_beacon' ),
                'id' => 'social-icons',
                'col' => 0,
                'width' => '4',
                'section' => 'search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Button', '_beacon' ),
                'id' => 'button',
                'col' => 0,
                'width' => '4',
                'section' => 'search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'Icon List', '_beacon' ),
                'id' => 'icon-list',
                'col' => 0,
                'width' => '4',
                'section' => 'search' // Customizer section to focus when click settings
            ),

            array(
                'name' => __( 'HTML', '_beacon' ),
                'id' => 'html',
                'col' => 0,
                'width' => '4',
                'section' => 'header_html' // Customizer section to focus when click settings
            ),

        );


        $items = apply_filters( '_beacon/builder/header/items', $items );
        $new_items = array();
        foreach (  $items as $k => $i ) {
            $new_items[ $i['id'] ] = $i;
        }

        return $new_items;
    }

    function template(){
        ?>
        <div class="_beacon--customize-builder">
            <div class="_beacon--cb-inner">
                <div class="_beacon--cb-header">
                    <div class="_beacon--cb-devices-switcher">
                    </div>
                    <div class="_beacon--cb-actions">
                        <a href="#"><?php _e( 'Settings', '_beacon' ); ?></a>
                        <a href="#"><?php _e( 'Templates', '_beacon' ); ?></a>
                        <a href="#"><?php _e( 'Close', '_beacon' ); ?></a>
                    </div>
                </div>
                <div class="_beacon--cb-body"></div>

                <div class="_beacon--cb-footer"></div>

            </div>

        </div>
        <script type="text/html" id="tmpl-_beacon--cb-panel">
            <div class="_beacon--cp-rows">
                <div class="_beacon--row-top _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="top"><ul></ul></div>
                    </div>
                </div>
                <div class="_beacon--row-main _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="main"><ul></ul></div>
                    </div>
                </div>
                <div class="_beacon--row-bottom _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="bottom"><ul></ul></div>
                    </div>
                </div>
            </div>
            <div class="_beacon--cp-sidebar">
                <div class="_beacon--row-bottom _beacon--cb-row">
                    <a class="_beacon--cb-row-settings" href="#">set</a>
                    <div class="_beacon--row-inner">
                        <div class="_beacon--cb-items grid-stack gridster" data-id="sidebar"><ul></ul></div>
                    </div>
                </div>
            <div>
        </script>

        <script type="text/html" id="tmpl-_beacon--cb-item">
            <li class="grid-stack-item"
                 title="{{ data.name }}"
                 data-id="{{ data.id }}"

                 data-sizex="{{ data.width }}" data-sizey="1"
                 data-col="{{ data.x }}" data-row="{{ data.y }}"
            >
                <div class="grid-stack-item-content">
                    <span class="_beacon--cb-item-name">{{ data.name }}</span>
                    <span class="_beacon--cb-item-add _beacon-cb-icon"></span>
                    <span class="_beacon--cb-item-remove _beacon-cb-icon"></span>
                    <span class="_beacon--cb-item-setting _beacon-cb-icon" data-section="{{ data.section }}"></span>
                </div>
            </li>
        </script>
        <?php
    }



}

new _Beacon_Customizer_Layout_Builder();


function _beacon_customize_render_header(){
    ?>
    <div class="header-top">
        <div class="_beacon-container">
            header top
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-top -->

    <div class="header-main">
        <div class="_beacon-container">
            <div class="site-branding">
                <?php
                the_custom_logo();
                if ( is_front_page() && is_home() ) : ?>
                    <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                <?php else : ?>
                    <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                    <?php
                endif;

                $description = get_bloginfo( 'description', 'display' );
                if ( $description || is_customize_preview() ) : ?>
                    <p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
                    <?php
                endif; ?>
            </div><!-- .site-branding -->

            <nav id="site-navigation" class="main-navigation">
                <?php
                //                            wp_nav_menu( array(
                //                                'theme_location' => 'menu-1',
                //                                'menu_id'        => 'primary-menu',
                //                            ) );
                ?>
            </nav><!-- #site-navigation -->
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-main -->

    <div class="header-bottom">
        <div class="_beacon-container">
            header bottom
        </div> <!-- #._beacon-container -->
    </div><!-- #.header-bottom -->
    <pre class="debug"><?php print_r( get_theme_mod( 'header_builder_panel' ) ); ?></pre>
    <?php
}