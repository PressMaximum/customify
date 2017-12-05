<?php
class Customify_Builder_Item_Languages
{
    public $id = 'languages';

    function item()
    {
        return array(
            'name' => __( 'Languages', 'customify' ),
            'id' => 'languages',
            'col' => 0,
            'width' => '4',
            'section' => 'header_languages' // Customizer section to focus when click settings
        );
    }

    function customize(){
        $section = 'header_languages';
        $prefix = 'header_languages';
        return array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title'          => __( 'Languages Switcher', 'customify' ),
            ),

            array(
                'name' => $prefix.'switcher',
                'type' => 'textarea',
                'section' => $section,
                'theme_supports' => '',
                'title'    => __( 'Languages Switcher', 'customify' ),
            ),
        );
    }

}


Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Languages() );

