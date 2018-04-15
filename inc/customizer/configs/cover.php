<?php

class Customify_Header_Cover
{
    public $name = null;
    public $description = null;
    static $is_transparent = null;
    static $_instance = null;
    static $_settings = null;

    function __construct()
    {
        add_filter('customify/customizer/config', array( $this , 'config') );
        if (!is_admin()) {
            add_action('customify/site-start', array($this, 'render'), 35);
            add_action('customify_is_post_title_display', array($this, 'display_page_title'), 35);
            add_action('customify/titlebar/is-showing', array($this, 'showing_titlebar'), 35);
            add_action('customify/breadcrumb/config/positions', array($this, 'breadcrumb_config_positions'), 35);

        }
        self::$_instance = $this;
    }


    static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function breadcrumb_config_positions($positions = array())
    {
        $positions['inside_titlebar'] = __('Display inside header cover/titlebar', 'customify-pro');
        return $positions;
    }

    function showing_titlebar($return)
    {
        $cover_settings = $this->get_settings();
        if ($cover_settings['hide']) {
            return $return;
        } else {
            $return = false;
        }
        return $return;
    }

    function config($configs)
    {

        $section = 'page_header';
        $render_cb_el = array($this, 'render');
        $selector = '#page-cover';
        $config = array(

            // Global layout section.
            array(
                'name'  => $section,
                'type'  => 'section',
                'panel' => 'layout_panel',
                'title' => __('Page Header', 'customify-pro'),
            ),

            array(
                'name'            => "{$section}_hide",
                'type'            => 'checkbox',
                'section'         => $section,
                'default'         => 1,
                'checkbox_label'  => __('Hide Page Header', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name'     => "{$section}_styling_h",
                'type'     => 'heading',
                'section'  => $section,
                'required' => array("{$section}_hide", '!=', '1'),
                'title'    => __('Styling Settings', 'customify-pro')
            ),

            array(
                'name'       => $section . '_bg',
                'type'       => 'modal',
                'section'    => $section,
                'title'      => __('Background', 'customify-pro'),
                'selector'   => $selector,
                'required'   => array("{$section}_hide", '!=', '1'),
                'css_format' => 'styling', // styling
                'fields'     => array(
                    'tabs'          => array(
                        'normal' => '_'
                    ),
                    'normal_fields' => array(
                        array(
                            'name'       => 'bg_image',
                            'type'       => 'image',
                            'label'      => __('Background Image', 'customify-pro'),
                            'selector'   => "$selector",
                            'css_format' => 'background-image: url("{{value}}");'
                        ),
                        array(
                            'name'       => 'bg_cover',
                            'type'       => 'select',
                            'choices'    => array(
                                ''        => __('Default', 'customify-pro'),
                                'auto'    => __('Auto', 'customify-pro'),
                                'cover'   => __('Cover', 'customify-pro'),
                                'contain' => __('Contain', 'customify-pro'),
                            ),
                            'required'   => array('bg_image', 'not_empty', ''),
                            'label'      => __('Size', 'customify-pro'),
                            'class'      => 'field-half-left',
                            'selector'   => "$selector",
                            'css_format' => '-webkit-background-size: {{value}}; -moz-background-size: {{value}}; -o-background-size: {{value}}; background-size: {{value}};'
                        ),
                        array(
                            'name'       => 'bg_position',
                            'type'       => 'select',
                            'label'      => __('Position', 'customify-pro'),
                            'required'   => array('bg_image', 'not_empty', ''),
                            'class'      => 'field-half-right',
                            'choices'    => array(
                                ''              => __('Default', 'customify-pro'),
                                'center'        => __('Center', 'customify-pro'),
                                'top left'      => __('Top Left', 'customify-pro'),
                                'top right'     => __('Top Right', 'customify-pro'),
                                'top center'    => __('Top Center', 'customify-pro'),
                                'bottom left'   => __('Bottom Left', 'customify-pro'),
                                'bottom center' => __('Bottom Center', 'customify-pro'),
                                'bottom right'  => __('Bottom Right', 'customify-pro'),
                            ),
                            'selector'   => "$selector",
                            'css_format' => 'background-position: {{value}};'
                        ),
                        array(
                            'name'       => 'bg_repeat',
                            'type'       => 'select',
                            'label'      => __('Repeat', 'customify-pro'),
                            'class'      => 'field-half-left',
                            'required'   => array(
                                array('bg_image', 'not_empty', ''),
                            ),
                            'choices'    => array(
                                'repeat'    => __('Default', 'customify-pro'),
                                'no-repeat' => __('No repeat', 'customify-pro'),
                                'repeat-x'  => __('Repeat horizontal', 'customify-pro'),
                                'repeat-y'  => __('Repeat vertical', 'customify-pro'),
                            ),
                            'selector'   => "$selector",
                            'css_format' => 'background-repeat: {{value}};'
                        ),

                        array(
                            'name'       => 'bg_attachment',
                            'type'       => 'select',
                            'label'      => __('Attachment', 'customify-pro'),
                            'class'      => 'field-half-right',
                            'required'   => array(
                                array('bg_image', 'not_empty', '')
                            ),
                            'choices'    => array(
                                ''       => __('Default', 'customify-pro'),
                                'scroll' => __('Scroll', 'customify-pro'),
                                'fixed'  => __('Fixed', 'customify-pro')
                            ),
                            'selector'   => "$selector",
                            'css_format' => 'background-attachment: {{value}};'
                        ),

                        array(
                            'name'            => "overlay",
                            'type'            => 'color',
                            'section'         => $section,
                            'class'           => 'customify--clear',
                            'device_settings' => false,
                            'selector'        => "$selector:before",
                            'label'           => __('Cover Overlay', 'customify-pro'),
                            'css_format'      => 'background-color: {{value}};',
                        ),

                    ),
                    'hover_fields'  => false
                )
            ),

            array(
                'name'       => $section . '_title_styling',
                'type'       => 'styling',
                'section'    => $section,
                'required'   => array("{$section}_hide", '!=', '1'),
                'title'      => __('Title Styling', 'customify-pro'),
                'selector'   => array(
                    'normal'            => "{$selector} .header-cover-title",
                    'normal_link_color' => "{$selector} a",
                    'hover_link_color'  => "{$selector} a:hover",
                ),
                'css_format' => 'styling', // styling
                'fields'     => array(
                    'normal_fields' => array(
                        'link_color' => false, // disable for special field.
                        'bg_image'   => false,
                        'bg_cover'   => false,
                        'bg_repeat'  => false,
                        'box_shadow' => false,
                    ),
                    'hover_fields'  => false
                )
            ),

            array(
                'name'       => $section . '_tagline_styling',
                'type'       => 'styling',
                'section'    => $section,
                'required'   => array("{$section}_hide", '!=', '1'),
                'title'      => __('Tagline Styling', 'customify-pro'),
                'selector'   => array(
                    'normal'            => "{$selector} .header-cover-title",
                    'normal_link_color' => "{$selector} a",
                    'hover_link_color'  => "{$selector} a:hover",
                ),
                'css_format' => 'styling', // styling
                'fields'     => array(
                    'normal_fields' => array(
                        'link_color' => false, // disable for special field.
                        'bg_image'   => false,
                        'bg_cover'   => false,
                        'bg_repeat'  => false,
                        'box_shadow' => false,
                    ),
                    'hover_fields'  => false
                )
            ),

            array(
                'name'            => "{$section}_title_typo",
                'type'            => 'typography',
                'css_format'      => 'typography',
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'selector'        => "{$selector} .header-cover-title",
                'render_callback' => $render_cb_el,
                'title'           => __('Title Typography', 'customify-pro')
            ),

            array(
                'name'            => "{$section}_tagline_typo",
                'type'            => 'typography',
                'css_format'      => 'typography',
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'selector'        => "{$selector} .header-cover-tagline",
                'render_callback' => $render_cb_el,
                'title'           => __('Tagline Typography', 'customify-pro')
            ),

            array(
                'name'            => "{$section}_height",
                'type'            => 'slider',
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'device_settings' => true,
                'render_callback' => $render_cb_el,
                'title'           => __('Page Header Height', 'customify-pro'),
                'selector'        => "{$selector} .page-cover-inner",
                'css_format'      => 'min-height: {{value}};',
                'default'         => array(
                    'desktop' => 350,
                    'tablet'  => 350,
                    'mobile'  => 350,
                ),
            ),

            array(
                'name'            => "{$section}_padding_top",
                'type'            => 'slider',
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'device_settings' => true,
                'render_callback' => $render_cb_el,
                'title'           => __('Margin Top', 'customify-pro'),
                'selector'        => "{$selector}",
                'css_format'      => 'padding-top: {{value}};',
            ),

            array(
                'name'            => "{$section}_align",
                'type'            => 'text_align_no_justify',
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'device_settings' => true,
                'selector'        => "$selector",
                'css_format'      => 'text-align: {{value}};',
                'title'           => __('Text Align', 'customify-pro'),
            ),

            array(
                'name'     => "{$section}_texts_h",
                'type'     => 'heading',
                'section'  => $section,
                'required' => array("{$section}_hide", '!=', '1'),
                'title'    => __('Texts', 'customify-pro')
            ),

            array(
                'name'            => "{$section}_title",
                'type'            => 'text',
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
                'title'           => __('Page Header Title', 'customify-pro')
            ),

            array(
                'name'            => "{$section}_tagline",
                'type'            => 'textarea',
                'section'         => $section,
                'selector'        => $selector,
                'required'        => array("{$section}_hide", '!=', '1'),
                'render_callback' => $render_cb_el,
                'title'           => __('Page Header Tagline', 'customify-pro')
            ),

            array(
                'name'     => "{$section}_display_h",
                'type'     => 'heading',
                'section'  => $section,
                'required' => array("{$section}_hide", '!=', '1'),
                'title'    => __('Display Settings', 'customify-pro')
            ),

            array(
                'name'            => "{$section}_display_cat",
                'type'            => 'checkbox',
                'section'         => $section,
                'default'         => 1,
                'required'        => array("{$section}_hide", '!=', '1'),
                'checkbox_label'  => __('Display on categories', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name'            => "{$section}_display_search",
                'type'            => 'checkbox',
                'section'         => $section,
                'default'         => 1,
                'required'        => array("{$section}_hide", '!=', '1'),
                'checkbox_label'  => __('Display on search', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name'            => "{$section}_display_archive",
                'type'            => 'checkbox',
                'default'         => 0,
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'checkbox_label'  => __('Display on archive', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),

            array(
                'name'            => "{$section}_display_page",
                'type'            => 'checkbox',
                'default'         => 1,
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'checkbox_label'  => __('Display on single page', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name'            => "{$section}_display_post",
                'type'            => 'checkbox',
                'default'         => 1,
                'section'         => $section,
                'required'        => array("{$section}_hide", '!=', '1'),
                'checkbox_label'  => __('Display on single post', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),
            array(
                'name'            => "{$section}_display_post_blog",
                'type'            => 'checkbox',
                'default'         => 1,
                'section'         => $section,
                'required'        => array(
                    array("{$section}_hide", '!=', '1'),
                    array("{$section}_display_post", '!=', '1')
                ),
                'checkbox_label'  => __('Display blog header page on single post instead', 'customify-pro'),
                'selector'        => $selector,
                'render_callback' => $render_cb_el,
            ),
        );

        if (Customify()->is_woocommerce_active()) {
            $config[] = array(
                'name'           => "{$section}_display_shop",
                'type'           => 'checkbox',
                'default'        => 1,
                'section'        => $section,
                'checkbox_label' => __('Display on shop and product page', 'customify'),
            );
        }

        return array_merge($configs, $config);
    }

    function page_header($args)
    {
        if ( $args['hide'] ) {
            return $args;
        }
        $has_type = false;

        // Check post type if have page header settings
        if (is_singular()) {
            foreach ( Customify_MetaBox::get_instance()->get_support_post_types() as $type) {
                if (is_singular($type)) {
                    $has_type = true;
                }
            }
        }
        $has_settings = false;
        // Check if current page is using post/page
        if (Customify()->is_using_post()) {
            $has_settings = true;
        }

        // Check post type if have page header settings
        $id = false;
        $title = false;
        $tagline = false;
        $post_thumbnail_id = false;
        if (is_singular()) {
            if (!$has_type) {
                return $args;
            }
        }

        $id = Customify()->get_current_post_id();
        if (Customify()->is_woocommerce_active()) {
            if (is_shop() || is_product() || is_product_taxonomy()) {
                $id = wc_get_page_id('shop');
                $has_settings = true;
            }
        }

        if ($has_settings) {

            if (is_page($id)) {
                if (!Customify()->get_setting('page_header_display_page')) {
                    $args['hide'] = 1;
                }
            }
            $is_blog_page = false;
            // Check if not show on single post
            if (is_singular('post')) {
                if (!Customify()->get_setting('page_header_display_post')) {
                    // Check if not use blog page instead.
                    $args['hide'] = 1;
                    if (!Customify()->get_setting('page_header_display_post_blog')) {
                        $args['hide'] = 1;
                    } else {
                        $id = get_option('page_for_posts');
                        $args['hide'] = false;
                        $is_blog_page = true;
                        $args['show_title'] = true;
                    }
                }
            }

            if ($id) {
                $args['shortcode'] = trim(get_post_meta($id, '_customify_page_header_shortcode', true));
            }

            if (!$is_blog_page) {
                if (get_post_meta($id, '_customify_disable_page_title', true)) {
                    $args['title_tag'] = 'h1';
                }
            }

            $page_header_display = get_post_meta($id, '_customify_page_header_display', true);
            if ($page_header_display == 'hide') {
                $args['hide'] = 1;
            } elseif ($page_header_display == 'show') {
                $args['hide'] = false;
            }

            $title = get_post_meta($id, '_customify_page_header_title', true);
            if (!$title) {
                $title = get_the_title($id);
            }

            $args['title'] = $title;
            $args['tagline'] = trim(get_post_meta($id, '_customify_page_header_tagline', true));
            if (!$args['tagline']) {
                $args['tagline'] = get_the_excerpt($id);
            }

            $post_thumbnail_id = get_post_thumbnail_id($id);

            $media = get_post_meta($id, '_customify_page_header_image', true);
            if (!empty($media)) {
                $image = Customify()->get_media($media);
                if ($image) {
                    $args['image'] = $image;
                    $post_thumbnail_id = false;
                }
            }
        }

        // Check if is cate page
        if (is_category()) {
            if (Customify()->get_setting('page_header_display_cat')) {
                $has_settings = true;
                $args['hide'] = '';
                $args['title'] = get_the_archive_title();
                $args['tagline'] = get_the_archive_description();
            } else {
                $args['hide'] = 1;
            }
        } elseif (is_search()) {
            if (Customify()->get_setting('page_header_display_search')) {
                $has_settings = true;
                $args['hide'] = '';
                $args['title'] = sprintf( // WPCS: XSS ok.
                /* translators: 1: Search query name */
                    __('Search Results for: %s', 'customify-pro'),
                    '<span>' . get_search_query() . '</span>'
                );
                $args['tagline'] = '';
            } else {
                $args['hide'] = 1;
            }
        } elseif (is_archive()) {
            if (Customify()->get_setting('page_header_display_archive')) {
                $has_settings = true;
                $args['hide'] = '';
                $args['title'] = get_the_archive_title();
                $args['tagline'] = get_the_archive_description();
            } else {
                $args['hide'] = 1;
            }
        }


        if (Customify()->is_woocommerce_active()) {
            if (is_product_taxonomy()) {
                if (Customify()->get_setting('page_header_display_shop')) {
                    $has_settings = true;
                    $args['hide'] = '';
                    $args['title'] = single_term_title('', false);
                    $args['tagline'] = get_the_archive_description();
                } else {
                    $args['hide'] = 1;
                }
            }
        }

        if (!$has_settings) {
            return $args;
        }

        if ($post_thumbnail_id) {
            $_i = Customify()->get_media($post_thumbnail_id);
            if ($_i) {
                $args['image'] = $_i;
            }
        }

        return $args;
    }

    function get_settings()
    {
        if (self::$_settings === null) {
            $args = array(
                'hide'       => Customify()->get_setting('page_header_hide'),
                'title'      => Customify()->get_setting('page_header_title'),
                'tagline'    => Customify()->get_setting('page_header_tagline'),
                'image'      => '',
                'title_tag'  => 'h1',
                'show_title' => false, // force show post title
                'shortcode'  => false, // force show post title
            );
            self::$_settings = apply_filters('customify-pro/page-cover/args', $this->page_header($args));
        }
        return self::$_settings;
    }

    function display_page_title($show)
    {
        $settings = $this->get_settings();
        if (!$settings['hide']) {
            $show = false;
        }
        if ($settings['show_title']) {
            $show = true;
        }
        return $show;
    }

    function render()
    {
        $args = $this->get_settings();
        extract($args, EXTR_SKIP);

        if ($args['hide']) {
            return '';
        }

        if ($shortcode) {
            echo do_shortcode(wp_kses_post($shortcode));
            return false;
        }

        $style = '';
        if ($image) {
            $style = ' style="background-image: url(\'' . esc_url($image) . '\')" ';
        }

        if (!$title_tag) {
            $title_tag = 'h2';
        }

        ?>
        <div id="page-cover" class="page-cover"<?php echo $style; ?>>
            <div class="page-cover-inner customify-container">
                <?php
                if ($title) {
                    echo '<' . $title_tag . ' class="page-cover-title">' . wp_kses_post($title) . '</' . $title_tag . '>';
                }
                if ($tagline) {
                    echo '<div class="page-cover-tagline-wrapper"><div class="page-cover-tagline">' . apply_filters('customify_the_content', wp_kses_post($tagline)) . '</div></div>';
                }
                do_action('customify/page-cover/content');
                ?>
            </div>
        </div>
        <?php
    }

}

Customify_Header_Cover::get_instance();

