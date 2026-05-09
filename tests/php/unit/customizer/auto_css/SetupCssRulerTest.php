<?php

namespace Customify\Tests\Unit\Customizer\AutoCss;

use Brain\Monkey\Functions;
use Customify\Tests\UnitTestCase;

/**
 * Customify_Customizer_Auto_CSS::setup_css_ruler()
 *
 * 4-side ruler (margin/padding/border) → joined CSS lines.
 * Each side has its own format string in the field's css_format array.
 * Sides with null/empty value are skipped.
 */
final class SetupCssRulerTest extends UnitTestCase
{
    /** @var \Customify_Customizer_Auto_CSS */
    private $renderer;

    /** @var array<string,string> */
    private $padding_format = array(
        'top'    => 'padding-top: {{value}};',
        'right'  => 'padding-right: {{value}};',
        'bottom' => 'padding-bottom: {{value}};',
        'left'   => 'padding-left: {{value}};',
    );

    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('sanitize_hex_color')->returnArg(1);
        Functions\when('get_template_directory')->justReturn(__DIR__);

        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-auto-css.php';

        $this->renderer = new \Customify_Customizer_Auto_CSS();
    }

    public function test_all_four_sides_present(): void
    {
        $out = $this->renderer->setup_css_ruler(
            array('unit' => 'px', 'top' => 10, 'right' => 20, 'bottom' => 15, 'left' => 5),
            $this->padding_format
        );
        $this->assertStringContainsString('padding-top: 10px;', $out);
        $this->assertStringContainsString('padding-right: 20px;', $out);
        $this->assertStringContainsString('padding-bottom: 15px;', $out);
        $this->assertStringContainsString('padding-left: 5px;', $out);
    }

    public function test_missing_side_omitted(): void
    {
        // Only top set; other sides should NOT emit a line.
        $out = $this->renderer->setup_css_ruler(
            array('unit' => 'px', 'top' => 10),
            $this->padding_format
        );
        $this->assertStringContainsString('padding-top: 10px;', $out);
        $this->assertStringNotContainsString('padding-right', $out);
        $this->assertStringNotContainsString('padding-bottom', $out);
        $this->assertStringNotContainsString('padding-left', $out);
    }

    public function test_zero_is_a_real_value_and_emits(): void
    {
        // padding: 0 is meaningful; ruler must not skip it.
        $out = $this->renderer->setup_css_ruler(
            array('unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0),
            $this->padding_format
        );
        $this->assertStringContainsString('padding-top: 0px;', $out);
        $this->assertStringContainsString('padding-right: 0px;', $out);
        $this->assertStringContainsString('padding-bottom: 0px;', $out);
        $this->assertStringContainsString('padding-left: 0px;', $out);
    }

    public function test_unit_defaults_to_px(): void
    {
        $out = $this->renderer->setup_css_ruler(
            array('top' => 10),
            $this->padding_format
        );
        $this->assertStringContainsString('padding-top: 10px;', $out);
    }

    public function test_em_unit_applied_to_each_side(): void
    {
        $out = $this->renderer->setup_css_ruler(
            array('unit' => 'em', 'top' => 1, 'right' => 2),
            $this->padding_format
        );
        $this->assertStringContainsString('padding-top: 1em;', $out);
        $this->assertStringContainsString('padding-right: 2em;', $out);
    }

    public function test_partial_format_only_emits_specified_sides(): void
    {
        // If css_format only declares top + bottom (e.g. only vertical
        // padding), no horizontal lines should be emitted regardless of
        // value's left/right.
        $partial = array(
            'top'    => 'padding-top: {{value}};',
            'bottom' => 'padding-bottom: {{value}};',
        );
        $out = $this->renderer->setup_css_ruler(
            array('unit' => 'px', 'top' => 10, 'right' => 20, 'bottom' => 15, 'left' => 5),
            $partial
        );
        $this->assertStringContainsString('padding-top: 10px;', $out);
        $this->assertStringContainsString('padding-bottom: 15px;', $out);
        $this->assertStringNotContainsString('padding-right', $out);
        $this->assertStringNotContainsString('padding-left', $out);
    }

    public function test_empty_value_returns_empty_string(): void
    {
        $out = $this->renderer->setup_css_ruler(array(), $this->padding_format);
        $this->assertSame('', $out);
    }
}
