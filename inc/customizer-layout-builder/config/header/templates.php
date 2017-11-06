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

    $saved_templates = array_reverse( $saved_templates );

    $n = count( $saved_templates );

    $html = '';
    $html .= '<span class="customize-control-title">'.__( 'Saved Template', '_beacon' ).'</span>';
    $html .= '<ul class="list-saved-templates '.( $n > 0 ? 'has-templates' : 'no-templates' ).'">';
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
            $html .= '<li class="saved_template" data-control-id="'.esc_attr( $prefix.'save' ).'" data-id="'.esc_attr( $key ).'" data-data="'.esc_attr( json_encode( $tpl['data'] ) ).'">'.esc_html( $name ).' <a href="#" class="load-tpl">'.__( 'Load', '_beacon' ).'</a><a href="#" class="remove-tpl">'.__( 'Remove', '_beacon' ).'</a></li>';
        }
    }

    $html .= '<li class="no_template">'.__( 'No saved templates.', '_beacon' ).'</li>';

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
            'description' => '<div class="save-template-form"><input type="text" data-builder-id="header" data-control-id="'.esc_attr( $prefix.'save' ).'" class="template-input-name change-by-js"><button class="save-builder-template" type="button">'.esc_html__( 'Save', '_beacon' ).'</button></div>'.$html,
        ),
    );
}