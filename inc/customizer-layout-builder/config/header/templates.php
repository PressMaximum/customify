<?php
function _beacon_builder_config_header_templates(){
    $section = 'header_templates';
    $prefix = 'header_templates_';


    $theme_name = wp_get_theme()->get('Name');
    $option_name = $theme_name.'_saved_templates';

    $saved_templates = get_option( $option_name );
    if ( ! is_array( $saved_templates ) ) {
        $saved_templates = array();
    }

    $html = '';
    $html .= '<span class="customize-control-title">'.__( 'Saved Template', '_beacon' ).'</span>';
    $html .= '<ul class="list-saved-templates">';
    if ( count( $saved_templates ) > 0 ) {
        foreach ( $saved_templates as $key => $tpl ) {
            $tpl = wp_parse_args( $tpl, array(
                'name' => '',
                'data' => '',
            ) );
            if ( ! $tpl['name'] ) {
                $name =  __( 'Untitled', '_beacon' );
            } else {
                $name = $tpl['name'] ;
            }
            $html .= '<li class="saved_template" data-id="'.esc_attr( $key ).'" data-data="'.esc_attr( json_encode( $tpl['data'] ) ).'">'.esc_html( $name ).'</li>';
        }
    } else {
        $html .= '<li class="no_template">'.__( 'No saved templates.', '_beacon' ).'</li>';
    }
    $html .= '</ul>';
    $html .= '</div>';


    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Templates', '_beacon' ),
        ),

        array(
            'name' => $prefix.'save',
            'type' => 'custom_html',
            'section' => $section,
            'theme_supports' => '',
            'title'       => __( 'Save Template', '_beacon' ),
            'description' => '<div class="save-template-form"><input type="text" data-builder-id="header" class="template-input-name"><button class="save-builder-template" type="button">'.esc_html__( 'Save', '_beacon' ).'</button></div>'.$html,
        ),
    );
}