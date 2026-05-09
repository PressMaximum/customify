<?php

namespace Customify\Tests\Unit\Customizer\Sanitize;

use Customify\Tests\UnitTestCase;
use ReflectionMethod;

/**
 * Customify_Sanitize_Input::sanitize_css_ruler() (private — tested via reflection).
 *
 * 4-side ruler value shape: { unit, top, right, bottom, left, link }.
 * Used by margin/padding fields. Each side is independently text-sanitized;
 * `link` is coerced to 1 or null (truthy/falsy).
 */
final class SanitizeCssRulerTest extends UnitTestCase
{
    /** @var ReflectionMethod */
    private $sanitize;

    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';

        $this->sanitize = new ReflectionMethod(\Customify_Sanitize_Input::class, 'sanitize_css_ruler');
        // setAccessible no-op on PHP 8.1+ (deprecated on 8.5); private methods
        // are reflection-accessible by default since 8.1.
    }

    private function call(array $value): array
    {
        return $this->sanitize->invoke(new \Customify_Sanitize_Input(), $value);
    }

    public function test_full_value_round_trips(): void
    {
        $in = array(
            'unit'   => 'px',
            'top'    => '10',
            'right'  => '20',
            'bottom' => '15',
            'left'   => '5',
            'link'   => 1,
        );
        $this->assertSame($in, $this->call($in));
    }

    public function test_missing_sides_default_to_null(): void
    {
        $out = $this->call(array('unit' => 'em', 'top' => '1'));
        $this->assertSame('em', $out['unit']);
        $this->assertSame('1', $out['top']);
        // sanitize_text_field on null returns empty string
        $this->assertSame('', $out['right']);
        $this->assertSame('', $out['bottom']);
        $this->assertSame('', $out['left']);
    }

    public function test_link_truthy_becomes_one(): void
    {
        $out = $this->call(array('link' => true));
        $this->assertSame(1, $out['link']);

        $out = $this->call(array('link' => '1'));
        $this->assertSame(1, $out['link']);

        $out = $this->call(array('link' => 'yes'));
        $this->assertSame(1, $out['link']);
    }

    public function test_link_falsy_becomes_null(): void
    {
        $out = $this->call(array('link' => 0));
        $this->assertNull($out['link']);

        $out = $this->call(array('link' => false));
        $this->assertNull($out['link']);

        $out = $this->call(array('link' => ''));
        $this->assertNull($out['link']);
    }

    public function test_html_in_side_value_stripped(): void
    {
        $out = $this->call(array('top' => '<script>10</script>', 'unit' => 'px'));
        $this->assertSame('10', $out['top']);
    }

    public function test_all_known_keys_present_even_when_unset(): void
    {
        $out = $this->call(array());
        $expected_keys = array('unit', 'top', 'right', 'bottom', 'left', 'link');
        sort($expected_keys);
        $actual_keys = array_keys($out);
        sort($actual_keys);
        $this->assertSame($expected_keys, $actual_keys);
    }
}
