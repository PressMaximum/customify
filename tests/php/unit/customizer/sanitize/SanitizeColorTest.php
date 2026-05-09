<?php

namespace Customify\Tests\Unit\Customizer\Sanitize;

use Brain\Monkey\Functions;
use Customify\Tests\UnitTestCase;

/**
 * Customify_Sanitize_Input::sanitize_color()
 *
 * Static method, not device-aware. Accepts hex (#rgb / #rrggbb) and rgba()
 * strings. Empty / non-string / array inputs sanitize to empty.
 *
 * Defends against arbitrary user input from the color picker control —
 * malformed values must NOT propagate to wp_add_inline_style() output.
 */
final class SanitizeColorTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // sanitize_color() delegates to WordPress's sanitize_hex_color()
        // for hex paths. Mirror its real behavior (returns the hex if valid,
        // otherwise null).
        Functions\when('sanitize_hex_color')->alias(static function ($color) {
            if ('' === (string) $color) {
                return '';
            }
            if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
                return $color;
            }
            return null;
        });

        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';
    }

    /** @dataProvider provideValidHex */
    public function test_valid_hex_passes_through($input): void
    {
        $this->assertSame($input, \Customify_Sanitize_Input::sanitize_color($input));
    }
    public function provideValidHex(): array
    {
        return array(
            'short hex'           => array('#fff'),
            'short hex non-zero'  => array('#a0c'),
            'long hex lowercase'  => array('#235787'),
            'long hex uppercase'  => array('#ABCDEF'),
            'long hex digits'     => array('#000000'),
            'long hex full white' => array('#ffffff'),
        );
    }

    /** @dataProvider provideRgba */
    public function test_rgba_normalizes_whitespace($input, $expected): void
    {
        $this->assertSame($expected, \Customify_Sanitize_Input::sanitize_color($input));
    }
    public function provideRgba(): array
    {
        return array(
            'rgba 0 alpha'      => array('rgba(0,0,0,0)',           'rgba(0,0,0,0)'),
            'rgba half alpha'   => array('rgba(255, 0, 0, 0.5)',    'rgba(255,0,0,0.5)'),
            'rgba full alpha'   => array('rgba(12, 34, 56, 1)',     'rgba(12,34,56,1)'),
            'rgba spaces galore' => array('rgba( 1 , 2 , 3 , 0.7 )','rgba(1,2,3,0.7)'),
        );
    }

    /** @dataProvider provideEmptyOrInvalid */
    public function test_empty_or_non_string_yields_empty($input, $expected): void
    {
        $this->assertSame($expected, \Customify_Sanitize_Input::sanitize_color($input));
    }
    public function provideEmptyOrInvalid(): array
    {
        return array(
            'empty string'  => array('',     ''),
            'null'          => array(null,   ''),
            'array'         => array(array('#fff'), ''),
            'invalid hex'   => array('#xyz',  null),
            'no leading #'  => array('123abc', null),
            'too long hex'  => array('#1234567890', null),
        );
    }

    public function test_javascript_protocol_rejected(): void
    {
        $this->assertNull(\Customify_Sanitize_Input::sanitize_color('javascript:alert(1)'));
    }

    public function test_html_tags_in_color_string_rejected(): void
    {
        $this->assertNull(\Customify_Sanitize_Input::sanitize_color('<script>alert(1)</script>'));
    }
}
