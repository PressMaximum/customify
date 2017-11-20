<?php
/**
 * Header search item config
 *
 * @see customify_customizer_get_header_config
 * @return array
 */
function customify_builder_config_header_search(){
    $section = 'header_search';
    $prefix = 'header_search_';
    $fn = 'customify_builder_header_search_item';

    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title' => __( 'Search', 'customify' ),
        ),

        array(
            'name' => 'header_search_placeholder',
            'type' => 'text',
            'section' => $section,
            'selector' => '.header-search-form',
            'render_callback' => $fn,
            'default' => __( 'Search...', 'customify' ),
            'title' => __( 'Placeholder Text', 'customify' ),
        ),

        array(
            'name' => 'header_search_btn',
            'type' => 'checkbox',
            'section' => $section,
            'selector' => '.'.$section.'-item',
            'render_callback' => $fn,
            'title' => __( 'Show Submit Button', 'customify' ),
            'default' => 1,
            'checkbox_label' => __( 'Show Submit Button', 'customify' ),
        ),

    );
    return $config;
}


function customify_builder_header_search_item(){

    $placeholder = Customify_Customizer()->get_setting( 'header_search_placeholder' );
    $show_btn = Customify_Customizer()->get_setting( 'header_search_btn' );

    ?>
    <form role="search" method="get" class="header-search-form search-form" action="<?php echo home_url( '/' ); ?>">
        <input type="text" name="s" class="s" placeholder="<?php echo esc_attr( $placeholder ); ?>">
        <?php if ( $show_btn ) { ?>
        <input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Search', 'search submit', 'customify' ); ?>" />
        <?php } ?>
    </form>

    <?php
}


