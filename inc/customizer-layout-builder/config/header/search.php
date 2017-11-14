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
    $render_fn = '_beacon_builder_header_search_item';

    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Search', '_beacon' ),
        ),

        array(
            'name' => 'header_search',
            'type' => 'text',
            'section' => 'header_search',
            'selector' => '.'.$section.'-item',
            'render_callback' => $render_fn,
            'title'          => __( 'Search', '_beacon' ),
        ),
    );
    return $config;
}


function _beacon_builder_header_search_item(){
    $section = 'header_search';
    $prefix = 'header_search_';
    ?>
    <div class="header_search-item">
        <input type="text" placeholder="Search">
    </div>

    <?php
}


