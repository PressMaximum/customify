<?php
/**
 * Dump the full Customizer config registry to a fixture file.
 *
 * Run via wp-cli inside the local WP install:
 *
 *   wp eval-file wp-content/themes/customify/tools/dump-customizer-configs.php
 *
 * Output:
 *   tests/php/fixtures/all-configs.php  — array of every field row that
 *   `customify/customizer/config` emits, ready to be `require`d in
 *   schema/sanitizer/auto-css unit tests without booting WordPress.
 *
 * Re-run this whenever Customizer config files change. Tests that depend
 * on shape (Layer A schema tests) will compare against this snapshot, so
 * a stale fixture will surface as a CI failure.
 */

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run via: wp eval-file " . __FILE__ . "\n");
    exit(1);
}

if (! class_exists('Customify_Customizer')) {
    fwrite(STDERR, "Customify_Customizer not loaded — is the Customify theme active?\n");
    exit(1);
}

global $wp_customize;
if (! $wp_customize instanceof WP_Customize_Manager) {
    require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
    $wp_customize = new WP_Customize_Manager();
    do_action('customize_register', $wp_customize);
}

$configs = Customify_Customizer::get_config($wp_customize);

if (! is_array($configs) || empty($configs)) {
    fwrite(STDERR, "No configs returned. Did add_action('customize_register', …) run?\n");
    exit(1);
}

$fixture_path = dirname(__DIR__) . '/tests/php/fixtures/all-configs.php';

$header = "<?php\n"
    . "/**\n"
    . " * Auto-generated Customizer config snapshot. DO NOT edit by hand.\n"
    . " * Regenerate via: wp eval-file tools/dump-customizer-configs.php\n"
    . " *\n"
    . " * Generated: " . gmdate('c') . "\n"
    . " * Field count: " . count($configs) . "\n"
    . " */\n\n"
    . "return ";

// Strip closures that some configs include — they don't survive var_export
// and we don't need them for shape/type validation anyway.
$strippable_keys = array(
    'sanitize_callback',
    'sanitize_js_callback',
    'active_callback',
    'js_vars',
);
$cleaned = array_map(static function ($field) use ($strippable_keys) {
    if (! is_array($field)) {
        return $field;
    }
    foreach ($strippable_keys as $key) {
        if (isset($field[$key]) && ($field[$key] instanceof Closure || is_callable($field[$key]))) {
            // Keep a marker so tests can assert "callback is set" without
            // needing to evaluate it.
            $field[$key] = '__callable_' . (is_array($field[$key]) ? implode('::', $field[$key]) : 'closure') . '__';
        }
    }
    return $field;
}, $configs);

$body = var_export($cleaned, true);

file_put_contents($fixture_path, $header . $body . ";\n");

fwrite(STDOUT, "Wrote " . count($configs) . " fields → " . $fixture_path . "\n");
