<?php

class Customify_Dashboard {
    public $title;
    public $config;
    function __construct()
    {
        $this->title = __( 'Customify Options', 'customify' );
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
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
    private function page_inner(){

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
        ?>
        <div class="cd-row metabox-holder">
            <h1 class="cd-hidden-heading"">&nbsp;</h1>
            <div class="cd-main">

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

                <?php do_action( 'customify/dashboard/main', $this ); ?>
            </div>
            <div class="cd-sidebar">

                <div class="cd-box">
                    <div class="cd-box-top">Join the community!</div>
                    <div class="cd-box-content">
                        <p><?php _e( 'Join the Facebook group for updates, discussions, chat with other Customify lovers.', 'customify' ) ?></p>
                        <a href="https://www.facebook.com/groups/133106770857743"><?php _e( 'Join Our Facebook Group »', 'customify' ); ?></a>
                    </div>
                </div>

                <?php do_action( 'customify/dashboard/sidebar', $this ); ?>

            </div>
        </div>
    <?php
    }

}

new Customify_Dashboard();


