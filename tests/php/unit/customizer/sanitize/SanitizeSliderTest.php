<?php

namespace Customify\Tests\Unit\Customizer\Sanitize;

use Customify\Tests\UnitTestCase;
use ReflectionMethod;

/**
 * Customify_Sanitize_Input::sanitize_slider() (private — tested via reflection).
 *
 * Slider value shape (per device): { unit: 'px', value: '20' }.
 * The full-form `sanitize()` wraps this in device keys (desktop/tablet/mobile)
 * but the per-device sanitizer accepts a single { unit, value } row.
 */
final class SanitizeSliderTest extends UnitTestCase
{
    /** @var ReflectionMethod */
    private $sanitize;

    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';

        $this->sanitize = new ReflectionMethod(\Customify_Sanitize_Input::class, 'sanitize_slider');
        // setAccessible(true) was needed pre-PHP 8.1; on 8.1+ private members
        // are reflection-accessible by default and the call emits a deprecation.
    }

    private function call(array $value): array
    {
        $instance = new \Customify_Sanitize_Input();
        return $this->sanitize->invoke($instance, $value);
    }

    public function test_round_trip_typical_value(): void
    {
        $out = $this->call(array('unit' => 'px', 'value' => '20'));
        $this->assertSame(array('unit' => 'px', 'value' => '20'), $out);
    }

    public function test_partial_value_filled_from_defaults(): void
    {
        $out = $this->call(array('value' => '15'));
        $this->assertSame('px', $out['unit'], 'unit defaults to px');
        $this->assertSame('15', $out['value']);
    }

    public function test_empty_value_yields_default_shape(): void
    {
        $out = $this->call(array());
        $this->assertSame(array('unit' => 'px', 'value' => ''), $out);
    }

    /** @dataProvider provideUnits */
    public function test_unit_string_passes_through_sanitization($input, $expected): void
    {
        $out = $this->call(array('unit' => $input, 'value' => '10'));
        $this->assertSame($expected, $out['unit']);
    }
    public function provideUnits(): array
    {
        return array(
            'px'    => array('px', 'px'),
            'em'    => array('em', 'em'),
            'rem'   => array('rem', 'rem'),
            'vw'    => array('vw', 'vw'),
            '%'     => array('%', '%'),
            // sanitize_text_field strips tags and trims whitespace
            'with tag' => array('<b>px</b>', 'px'),
            'with whitespace' => array('  em  ', 'em'),
        );
    }

    public function test_extra_keys_dropped(): void
    {
        $out = $this->call(array(
            'unit'        => 'px',
            'value'       => '10',
            '__proto__'   => array('polluted' => true),
            'evil_script' => '<script>alert(1)</script>',
        ));
        $this->assertSame(array('unit', 'value'), array_keys($out));
    }

    public function test_value_with_html_stripped(): void
    {
        $out = $this->call(array('unit' => 'px', 'value' => '<script>20</script>'));
        $this->assertSame('20', $out['value']);
    }
}
