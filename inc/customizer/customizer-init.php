<?php


require_once get_template_directory() . '/inc/customizer/class-customify-wp-customize-panel.php';
require_once get_template_directory() . '/inc/customizer/class-customify-wp-customize-section.php';
require_once get_template_directory() . '/inc/customizer/class-customizer-fonts.php';
require_once get_template_directory() . '/inc/customizer/class-customizer-sanitize.php';
require_once get_template_directory() . '/inc/customizer/class-customizer-auto-css.php';
require_once get_template_directory() . '/inc/customizer/class-customizer.php';


function customify_sanitize_css_code($val)
{
    return wp_kses_post($val);
}

if (!function_exists('Customify_Customizer')) {
    function Customify_Customizer()
    {
        return Customify_Customizer::get_instance();
    }
}
Customify_Customizer();


/**
 * Reset Customize section
 */
function customify__reset_customize_section()
{
    if (!current_user_can('customize')) {
        wp_send_json_error();
    }

    $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : array();

    foreach ($settings as $k) {
        $k = sanitize_text_field($k);
        remove_theme_mod($k);
    }

    wp_send_json_success();
}

add_action('wp_ajax_customify__reset_section', 'customify__reset_customize_section');