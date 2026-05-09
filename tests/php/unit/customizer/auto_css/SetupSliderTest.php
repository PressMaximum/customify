<?php

namespace Customify\Tests\Unit\Customizer\AutoCss;

use Brain\Monkey\Functions;
use Customify\Tests\UnitTestCase;

/**
 * Customify_Customizer_Auto_CSS::setup_slider()
 *
 * Combines value + unit + format → CSS fragment.
 * Returns false (not '') when value is empty so the caller can skip emit.
 */
final class SetupSliderTest extends UnitTestCase
{
    /** @var \Customify_Customizer_Auto_CSS */
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('sanitize_hex_color')->returnArg(1);
        Functions\when('get_template_directory')->justReturn(__DIR__);

        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-auto-css.php';

        $this->renderer = new \Customify_Customizer_Auto_CSS();
    }

    public function test_typical_value_with_explicit_unit(): void
    {
        $out = $this->renderer->setup_slider(
            array('unit' => 'px', 'value' => 20),
            'gap: {{value}};'
        );
        $this->assertSame('gap: 20px;', $out);
    }

    public function test_unit_defaults_to_px_when_empty(): void
    {
        $out = $this->renderer->setup_slider(
            array('value' => 15),
            'width: {{value}};'
        );
        $this->assertSame('width: 15px;', $out);
    }

    public function test_em_unit_round_trips(): void
    {
        $out = $this->renderer->setup_slider(
            array('unit' => 'em', 'value' => 1.5),
            'line-height: {{value}};'
        );
        $this->assertSame('line-height: 1.5em;', $out);
    }

    public function test_value_no_unit_token_uses_raw_value(): void
    {
        $out = $this->renderer->setup_slider(
            array('unit' => 'px', 'value' => 42),
            'flex: {{value_no_unit}};'
        );
        $this->assertSame('flex: 42;', $out);
    }

    public function test_zero_value_renders(): void
    {
        // 0 is a legitimate slider value (e.g. zero margin); must NOT short-circuit.
        $out = $this->renderer->setup_slider(
            array('unit' => 'px', 'value' => 0),
            'margin: {{value}};'
        );
        $this->assertSame('margin: 0px;', $out);
    }

    public function test_empty_value_returns_false(): void
    {
        $this->assertFalse($this->renderer->setup_slider(
            array('unit' => 'px', 'value' => ''),
            'gap: {{value}};'
        ));
        $this->assertFalse($this->renderer->setup_slider(
            array('unit' => 'px', 'value' => null),
            'gap: {{value}};'
        ));
    }

    public function test_no_format_returns_false(): void
    {
        $this->assertFalse($this->renderer->setup_slider(
            array('unit' => 'px', 'value' => 10),
            null
        ));
    }
}
