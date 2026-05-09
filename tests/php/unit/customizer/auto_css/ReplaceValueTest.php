<?php

namespace Customify\Tests\Unit\Customizer\AutoCss;

use Brain\Monkey\Functions;
use Customify\Tests\UnitTestCase;
use ReflectionMethod;

/**
 * Customify_Customizer_Auto_CSS::replace_value() — token substitution helper.
 *
 * Two tokens supported:
 *   {{value}}          — full value (incl. unit)
 *   {{value_no_unit}}  — numeric-only, used by composite formats
 *
 * This is the foundation of every css_format render path. A regression here
 * silently breaks every settings-driven CSS rule across the theme.
 */
final class ReplaceValueTest extends UnitTestCase
{
    /** @var ReflectionMethod */
    private $replace;

    protected function setUp(): void
    {
        parent::setUp();

        // Auto-CSS file references Customify() / sanitize_hex_color at top
        // level via static helpers; stub to avoid bootstrapping the whole theme.
        Functions\when('sanitize_hex_color')->returnArg(1);
        Functions\when('get_template_directory')->justReturn(__DIR__);

        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-auto-css.php';

        $this->replace = new ReflectionMethod(
            \Customify_Customizer_Auto_CSS::class,
            'replace_value'
        );
    }

    private function call($value, $format, $value_no_unit = ''): string
    {
        $instance = new \Customify_Customizer_Auto_CSS();
        return $this->replace->invoke($instance, $value, $format, $value_no_unit);
    }

    public function test_value_token_substituted(): void
    {
        $this->assertSame(
            'color: #ff0000;',
            $this->call('#ff0000', 'color: {{value}};')
        );
    }

    public function test_value_no_unit_token_substituted(): void
    {
        $this->assertSame(
            'opacity: 0.5;',
            $this->call('50%', 'opacity: {{value_no_unit}};', '0.5')
        );
    }

    public function test_both_tokens_in_same_format(): void
    {
        $out = $this->call(
            '20px',
            'width: {{value}}; flex-grow: {{value_no_unit}};',
            '20'
        );
        $this->assertSame('width: 20px; flex-grow: 20;', $out);
    }

    public function test_format_without_token_returned_unchanged(): void
    {
        $this->assertSame(
            'display: block;',
            $this->call('whatever', 'display: block;')
        );
    }

    public function test_falsy_value_substitutes_empty_string(): void
    {
        // Note: when value is null/0/'', the helper coerces to '' so the
        // resulting CSS is "color: ;" — still emitted so the caller can
        // decide whether to keep it. This is intentional per the
        // implementation and test guards against accidental "color: 0;".
        $this->assertSame('color: ;', $this->call('', 'color: {{value}};'));
        $this->assertSame('color: ;', $this->call(null, 'color: {{value}};'));
    }

    public function test_null_format_returns_empty_string(): void
    {
        $this->assertSame('', $this->call('#fff', null));
    }

    public function test_token_repeats_all_replaced(): void
    {
        $this->assertSame(
            'a:1; b:1; c:1;',
            $this->call('1', 'a:{{value}}; b:{{value}}; c:{{value}};')
        );
    }
}
