<?php

namespace Customify\Tests\Schema;

use Customify\Tests\UnitTestCase;

/**
 * Validate the shape of every Customizer field the theme registers.
 *
 * Runs against the fixture dumped by tools/dump-customizer-configs.php.
 * Falls back to the sample fixture when the real one isn't generated yet
 * — tests still validate the assertion logic, just over fewer rows.
 *
 * Adding/changing Customizer config files? Re-run:
 *
 *   wp eval-file wp-content/themes/customify/tools/dump-customizer-configs.php
 *
 * Then commit the regenerated tests/php/fixtures/all-configs.php.
 */
final class CustomizerSchemaTest extends UnitTestCase
{
    /** @var array<int, array<string,mixed>> */
    private static $configs;

    /** @var bool */
    private static $is_full_fixture;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $real   = dirname(__DIR__) . '/fixtures/all-configs.php';
        $sample = dirname(__DIR__) . '/fixtures/all-configs.sample.php';
        if (is_file($real)) {
            self::$configs         = require $real;
            self::$is_full_fixture = true;
        } else {
            self::$configs         = require $sample;
            self::$is_full_fixture = false;
        }
    }

    /**
     * Field types the schema knows about. Synced manually with what
     * `Customify_Sanitize_Input::sanitize()` and Customify_Customizer_Auto_CSS
     * understand. Reviewers should add to this list when introducing a new
     * type AND make sure the sanitizer + renderer handle it.
     */
    private const KNOWN_TYPES = array(
        // Containers
        'panel', 'section', 'heading',
        // Display-only
        'hidden', 'custom_html', 'js_raw',
        // Inputs
        'text', 'textarea', 'number', 'select', 'radio_group',
        'checkbox', 'image_select', 'text_align', 'text_align_no_justify',
        'color', 'image', 'icon', 'media',
        // Composite
        'slider', 'css_ruler', 'typography', 'styling', 'shadow',
        'modal', 'repeater', 'group', 'row_layout',
    );

    public function test_fixture_loads_with_at_least_one_field(): void
    {
        $this->assertNotEmpty(self::$configs, 'Fixture is empty');
        if (! self::$is_full_fixture) {
            $this->addWarning('Running against sample fixture. Regenerate via wp eval-file tools/dump-customizer-configs.php for full coverage.');
        }
    }

    public function test_every_field_has_name_and_type(): void
    {
        foreach (self::$configs as $i => $field) {
            $this->assertIsArray($field, "Row #$i is not an array");
            $this->assertArrayHasKey('name', $field, "Row #$i missing 'name'");
            $this->assertArrayHasKey('type', $field, "Row #$i missing 'type'");
            $this->assertNotEmpty($field['name'], "Row #$i has empty 'name'");
            $this->assertNotEmpty($field['type'], "Row #$i has empty 'type'");
        }
    }

    public function test_no_duplicate_field_names_among_inputs(): void
    {
        // Containers (panel/section) share names with their child fields by
        // design (e.g. a panel "header" + a setting "header_layout"), so we
        // only enforce uniqueness on input rows.
        $input_types = array_diff(self::KNOWN_TYPES, array('panel', 'section', 'heading'));

        $names = array();
        foreach (self::$configs as $field) {
            if (! in_array($field['type'] ?? '', $input_types, true)) {
                continue;
            }
            $name = $field['name'];
            $this->assertArrayNotHasKey(
                $name,
                $names,
                "Duplicate field name '$name' (type {$field['type']})"
            );
            $names[$name] = true;
        }
    }

    public function test_every_field_type_is_known(): void
    {
        foreach (self::$configs as $field) {
            $type = $field['type'] ?? 'MISSING';
            $this->assertContains(
                $type,
                self::KNOWN_TYPES,
                "Unknown field type '$type' on field '{$field['name']}'. Add it to KNOWN_TYPES + sanitizer + auto-css."
            );
        }
    }

    public function test_select_radio_image_select_have_choices_or_options(): void
    {
        $option_required = array('select', 'radio_group', 'image_select', 'text_align', 'text_align_no_justify');
        foreach (self::$configs as $field) {
            $type = $field['type'] ?? '';
            if (! in_array($type, $option_required, true)) {
                continue;
            }
            $has_choices = (! empty($field['choices']) && is_array($field['choices']))
                || (! empty($field['options']) && is_array($field['options']));
            $this->assertTrue(
                $has_choices,
                "Field '{$field['name']}' (type $type) needs 'choices' or 'options'"
            );
        }
    }

    public function test_required_dependencies_reference_existing_fields(): void
    {
        $names = array();
        foreach (self::$configs as $field) {
            if (! empty($field['name'])) {
                $names[$field['name']] = true;
            }
        }

        foreach (self::$configs as $field) {
            if (empty($field['required'])) {
                continue;
            }
            $deps = $this->normalize_required($field['required']);
            foreach ($deps as $dep) {
                $this->assertArrayHasKey(
                    $dep[0],
                    $names,
                    "Field '{$field['name']}' requires non-existent field '{$dep[0]}'"
                );
            }
        }
    }

    public function test_css_format_implies_selector_and_value_token(): void
    {
        foreach (self::$configs as $field) {
            if (empty($field['css_format'])) {
                continue;
            }
            $this->assertNotEmpty(
                $field['selector'] ?? null,
                "Field '{$field['name']}' has css_format but no selector"
            );

            $format = $field['css_format'];
            // String format must contain the {{value}} placeholder, otherwise
            // the renderer has nothing to substitute. Composite formats
            // (typography, css_ruler) use an array of per-side formats —
            // walk all leaves and assert each contains {{value}}.
            $this->walk_format_leaves($format, function ($leaf) use ($field) {
                if (! is_string($leaf) || $leaf === '') {
                    return;
                }
                $this->assertStringContainsString(
                    '{{value}}',
                    $leaf,
                    "Field '{$field['name']}' css_format leaf is missing the {{value}} token"
                );
            });
        }
    }

    public function test_input_fields_have_a_section_or_belong_to_a_panel_via_modal(): void
    {
        // Every input must be assigned to a section so WP can render it.
        // Container types (panel/section/heading) are exempt.
        $container_types = array('panel', 'section', 'heading');
        foreach (self::$configs as $field) {
            $type = $field['type'] ?? '';
            if (in_array($type, $container_types, true)) {
                continue;
            }
            $section = $field['section'] ?? null;
            $this->assertNotEmpty(
                $section,
                "Field '{$field['name']}' has no 'section' assigned"
            );
        }
    }

    /* ----------------------------------------------------------------- */

    private function normalize_required($required): array
    {
        // Two valid shapes:
        //   ['field', '=', 'value']             -> single dep
        //   [['field', '=', 'value'], [...] ]   -> multiple deps
        if (! is_array($required) || empty($required)) {
            return array();
        }
        $first = $required[0] ?? null;
        if (is_array($first)) {
            return $required;
        }
        return array($required);
    }

    private function walk_format_leaves($format, callable $visit): void
    {
        if (is_string($format)) {
            $visit($format);
            return;
        }
        if (is_array($format)) {
            foreach ($format as $leaf) {
                $this->walk_format_leaves($leaf, $visit);
            }
        }
    }
}
