<?php

class Customify_Dashboard {
    static $_instance;
    public $title;
    public $config;
    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();

            self::$_instance->title = __( 'Customify Options', 'customify' );
            add_action( 'admin_menu', array( self::$_instance, 'add_menu' ) );
            add_action( 'admin_enqueue_scripts', array(  self::$_instance, 'scripts' ) );
            add_action( 'customify/dashboard/main', array(  self::$_instance, 'box_links' ), 10 );
            add_action( 'customify/dashboard/main', array(  self::$_instance, 'pro_modules_box' ), 15 );
            add_action( 'customify/dashboard/sidebar', array(  self::$_instance, 'box_plugins' ), 10 );
            add_action( 'customify/dashboard/sidebar', array(  self::$_instance, 'box_community' ), 20 );

        }
        return self::$_instance;
    }

    function add_menu(){
        add_theme_page(
            $this->title,
            $this->title,
            'manage_options',
            'customify',
            array( $this, 'page' )
        );
    }

    function scripts($id)
    {
        if ($id != 'appearance_page_customify') {
            return;
        }
        $suffix = Customify()->get_asset_suffix();
        wp_enqueue_style('customify-admin', get_template_directory_uri() . '/assets/css/admin/dashboard' . $suffix . '.css', false, Customify::$version);
        wp_enqueue_style( 'plugin-install' );
        wp_enqueue_script( 'plugin-install' );
        wp_enqueue_script( 'updates' );
        add_thickbox();
    }

    function setup(){
        $theme = wp_get_theme();
        $this->config = array(
            'name' => $theme->get('Name'),
            'theme_uri' => $theme->get('ThemeURI'),
            'desc' => $theme->get('Description'),
            'author' => $theme->get('Author'),
            'author_uri' => $theme->get('AuthorURI'),
            'version' => $theme->get('Version'),
        );
    }

    function page(){
        $this->setup();
        $this->page_header();
        echo '<div class="wrap">';
        $this->page_inner();
        echo '</div>';
    }

    private function page_header(){
        ?>
        <div class="cd-header">
            <div class="cd-row">
                <div class="cd-header-inner">
                    <a href="https://wpcustomify.com" target="_blank" class="cd-branding">
                        <img src="<?php echo get_template_directory_uri().'/assets/images/admin/customify_logo@2x.png'; ?>" alt="logo">
                    </a>
                    <span class="cd-version"><?php echo esc_html( $this->config['version'] ); ?></span>
                </div>
            </div>
        </div>
        <?php
    }

    function box_links(){
        $url = admin_url( 'customize.php' ); //

        $links = array(
            array(
                'label' => __( 'Logo & Site Identity', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'section' => 'title_tagline' ) ), $url ),
            ),
            array(
                'label' => __( 'Layout Settings', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'section' => 'global_layout_section' ) ), $url ),
            ),
            array(
                'label' => __( 'Header Builder', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'panel' => 'header_settings' ) ), $url ),
            ),
            array(
                'label' => __( 'Footer Builder', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'panel' => 'footer_settings' ) ), $url ),
            ),
            array(
                'label' => __( 'Styling', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'panel' => 'styling_panel' ) ), $url ),
            ),
            array(
                'label' => __( 'Typography', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'panel' => 'typography_panel' ) ), $url ),
            ),
            array(
                'label' => __( 'Sidebar Settings', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'section' => 'sidebar_layout_section' ) ), $url ),
            ),
            array(
                'label' => __( 'Titlebar Settings', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'section' => 'titlebar' ) ), $url ),
            ),

            array(
                'label' => __( 'Blog Posts', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'panel' => 'blog_panel' ) ), $url ),
            ),
            array(
                'label' => __( 'Homepage Settings', 'customify' ),
                'url' => add_query_arg( array( 'autofocus' => array( 'section' => 'static_front_page' ) ), $url ),
            )
        );

        $links = apply_filters( 'customify/dashboard/links', $links );
        ?>
        <div class="cd-box">
            <div class="cd-box-top"><?php _e( 'Links to Customizer Settings', 'customify' ); ?></div>
            <div class="cd-box-content">
                <ul class="cd-list-flex">
                    <?php foreach( $links as $l ) { ?>
                        <li class="">
                            <a class="cd-quick-setting-link" href="<?php echo esc_url( $l['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $l['label'] ); ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php
    }

    function box_community() {
        ?>
        <div class="cd-box">
            <div class="cd-box-top"><?php _e( 'Join the community!', 'customify-pro' ); ?></div>
            <div class="cd-box-content">
                <p><?php _e( 'Join the Facebook group for updates, discussions, chat with other Customify lovers.', 'customify' ) ?></p>
                <a href="https://www.facebook.com/groups/133106770857743"><?php _e( 'Join Our Facebook Group &rarr;	', 'customify' ); ?></a>
            </div>
        </div>
        <?php
    }

    function box_plugins(){
        ?>
        <div class="cd-box">
            <div class="cd-box-top"><?php _e( 'Recommend Customify Site Library', 'customify' ); ?></div>
            <div id="plugin-filter" class="cd-box-content">
                <p><?php _e( '<strong>Customify Site Library</strong> is an add-on for the Customify WordPress Theme which help you browse and import your favorite site with few clicks.', 'customify' ) ?></p>
                <?php

                $plugin_slug = 'customify-sites';
                $plugin_info = array(
                    'name' => 'customify-sites',
                    'active_filename' => 'customify-sites/customify-sites.php'
                );

                $plugin_info = wp_parse_args( $plugin_info, array(
                    'name' => '',
                    'active_filename' => '',
                ) );
                $status = is_dir( WP_PLUGIN_DIR . '/' . $plugin_slug );
                $button_class = 'install-now button'; //
                if ( $plugin_info['active_filename'] ) {
                    $active_file_name = $plugin_info['active_filename'] ;
                } else {
                    $active_file_name = $plugin_slug . '/' . $plugin_slug . '.php';
                }


                $sites_url = add_query_arg(
                    array(
                        'page' => 'customify-sites',
                    ),
                    admin_url( 'themes.php' )
                );

                $view_site_txt = __( 'View Site Library', 'customify' );

                if ( ! is_plugin_active( $active_file_name ) ) {
                    $button_txt = esc_html__( 'Install Now', 'customify' );
                    if ( ! $status ) {
                        $install_url = wp_nonce_url(
                            add_query_arg(
                                array(
                                    'action' => 'install-plugin',
                                    'plugin' => $plugin_slug
                                ),
                                network_admin_url( 'update.php' )
                            ),
                            'install-plugin_'.$plugin_slug
                        );

                    } else {
                        $install_url = add_query_arg(array(
                            'action' => 'activate',
                            'plugin' => rawurlencode( $active_file_name ),
                            'plugin_status' => 'all',
                            'paged' => '1',
                            '_wpnonce' => wp_create_nonce('activate-plugin_' . $active_file_name ),
                        ), network_admin_url('plugins.php'));
                        $button_class = 'activate-now button-primary';
                        $button_txt = esc_html__( 'Active Now', 'customify' );
                    }

                    $detail_link = add_query_arg(
                        array(
                            'tab' => 'plugin-information',
                            'plugin' => $plugin_slug,
                            'TB_iframe' => 'true',
                            'width' => '772',
                            'height' => '349',

                        ),
                        network_admin_url( 'plugin-install.php' )
                    );

                    echo '<div class="rcp">';
                    echo '<p class="action-btn plugin-card-'.esc_attr( $plugin_slug ).'"><a href="'.esc_url( $install_url ).'" data-slug="'.esc_attr( $plugin_slug ).'" class="'.esc_attr( $button_class ).'">'.$button_txt.'</a></p>';
                    echo '<a class="plugin-detail thickbox open-plugin-details-modal" href="'.esc_url( $detail_link ).'">'.esc_html__( 'Details', 'customify' ).'</a>';
                    echo '</div>';
                } else {
                    echo '<div class="rcp">';
                    echo '<p ><a href="'.esc_url( $sites_url ).'" data-slug="'.esc_attr( $plugin_slug ).'" class="view-site-library">'.$view_site_txt.'</a></p>';
                    echo '</div>';
                }

                ?>
                <script type="text/javascript">
                    jQuery( document ).ready( function($){
                        var  sites_url = <?php echo json_encode( $sites_url ); ?>;
                        var  view_sites = <?php echo json_encode( $view_site_txt ); ?>;
                        $( '#plugin-filter' ).on( 'click', '.activate-now', function( e ){
                            e.preventDefault();
                            var button = $( this );
                            var url = button.attr('href');
                            button.addClass( 'button installing updating-message' );
                            $.get( url, function( ){
                                $( '.rcp .plugin-detail' ).hide();
                                button.attr( 'href', sites_url );
                                button.attr( 'class', 'view-site-library' );
                                button.text( view_sites );
                            } );
                        } );
                    } );
                </script>
            </div>
        </div>
        <?php
    }

    function pro_modules_box(){

        $modules = array(
            array(
                'name' => __( 'Header Transparent', 'customify' ),
                'url' => '#',
            ),
            array(
                'name' => __( 'Header Sticky', 'customify' ),
                'url' => '#',
            ),
            array(
                'name' => __( 'Header Footer Builder Items', 'customify' ),
                'url' => '#',
            ),
            array(
                'name' => __( 'Scroll To Top', 'customify' ),
                'url' => '#',
            ),
            array(
                'name' => __( 'Blog Pro', 'customify' ),
                'url' => '#',
            ),
        );

        ?>
        <div class="cd-box">
            <div class="cd-box-top"><?php _e( 'Customify Pro Modules', 'customify' ); ?></div>
            <div class="cd-box-content cd-modules">
                <?php foreach( $modules as $m ) { ?>
                <div class="cd-module-item">
                    <div class="cd-module-info">
                        <div class="cd-module-name"><?php echo esc_html( $m['name'] ); ?></div>
                        <a class="cd-module-doc-link" href="<?php echo esc_url( $m['url'] ); ?>"><?php _e( 'Learn more &rarr;', 'customify' ); ?></a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    private function page_inner(){

        ?>
        <div class="cd-row metabox-holder">
            <hr class="wp-header-end">
            <div class="cd-main">
                <?php do_action( 'customify/dashboard/main', $this ); ?>
            </div>
            <div class="cd-sidebar">
                <?php do_action( 'customify/dashboard/sidebar', $this ); ?>
            </div>
        </div>
    <?php
    }

}

Customify_Dashboard::get_instance();


