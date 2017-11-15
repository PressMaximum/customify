<?php
/**
 * Header search item config
 *
 * @see _beacon_customizer_get_header_config
 * @return array
 */
function _beacon_builder_config_header_search(){
    $section = 'header_search';
    $prefix = 'header_search_';
    $fn = '_beacon_builder_header_search_item';

    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title' => __( 'Search', '_beacon' ),
        ),

        array(
            'name' => 'header_search_placeholder',
            'type' => 'text',
            'section' => $section,
            'selector' => '.header-search-form',
            'render_callback' => $fn,
            'default' => __( 'Search...', '_beacon' ),
            'title' => __( 'Placeholder Text', '_beacon' ),
        ),

        array(
            'name' => 'header_search_btn',
            'type' => 'checkbox',
            'section' => $section,
            'selector' => '.'.$section.'-item',
            'render_callback' => $fn,
            'title' => __( 'Show Submit Button', '_beacon' ),
            'default' => 1,
            'checkbox_label' => __( 'Show Submit Button', '_beacon' ),
        ),

    );
    return $config;
}


function _beacon_builder_header_search_item(){

    $placeholder = _Beacon_Customizer()->get_setting( 'header_search_placeholder' );
    $show_btn = _Beacon_Customizer()->get_setting( 'header_search_btn' );

    ?>
    <form role="search" method="get" class="header-search-form search-form" action="<?php echo home_url( '/' ); ?>">
        <input type="text" name="s" class="s" placeholder="<?php echo esc_attr( $placeholder ); ?>">
        <?php if ( $show_btn ) { ?>
        <input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Search', '_beacon' ); ?>" />
        <?php } ?>
    </form>

    <?php
}


