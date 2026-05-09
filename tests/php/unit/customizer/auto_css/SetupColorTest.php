<?php

namespace Customify\Tests\Unit\Customizer\AutoCss;

use Brain\Monkey\Functions;
use Customify\Tests\UnitTestCase;

/**
 * Customify_Customizer_Auto_CSS::setup_color() and setup_checkbox().
 *
 * Color delegates to Customify_Sanitize_Input::sanitize_color before
 * substitution. Checkbox emits the format string literally only when
 * value is truthy (no token substitution).
 */
final class SetupColorTest extends UnitTestCase
{
    /** @var \Customify_Customizer_Auto_CSS */
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('sanitize_hex_color')->alias(static function ($v) {
            if ('' === (string) $v) {
                return '';
            }
            return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $v) ? $v : null;
        });
        Functions\when('get_template_directory')->justReturn(__DIR__);

        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-auto-css.php';
        $this->renderer = new \Customify_Customizer_Auto_CSS();
    }

    public function test_valid_hex_color_emits_substituted_format(): void
    {
        $this->assertSame(
            'color: #ff00aa;',
            $this->renderer->setup_color('#ff00aa', 'color: {{value}};')
        );
    }

    public function test_rgba_emits_normalized_form(): void
    {
        $this->assertSame(
            'background: rgba(0,0,0,0.5);',
            $this->renderer->setup_color('rgba( 0, 0, 0, 0.5 )', 'background: {{value}};')
        );
    }

    public function test_invalid_color_returns_false(): void
    {
        $this->assertFalse(
            $this->renderer->setup_color('notacolor', 'color: {{value}};')
        );
    }

    public function test_empty_value_returns_false(): void
    {
        $this->assertFalse(
            $this->renderer->setup_color('', 'color: {{value}};')
        );
    }

    public function test_no_format_returns_false(): void
    {
        $this->assertFalse(
            $this->renderer->setup_color('#fff', null)
        );
    }

    /* ------------- setup_checkbox ----------------------------------- */

    public function test_checkbox_truthy_emits_format_verbatim(): void
    {
        $this->assertSame(
            'display: none;',
            $this->renderer->setup_checkbox(1, 'display: none;')
        );
    }

    public function test_checkbox_falsy_returns_false(): void
    {
        $this->assertFalse(
            $this->renderer->setup_checkbox(0, 'display: none;')
        );
        $this->assertFalse(
            $this->renderer->setup_checkbox('', 'display: none;')
        );
    }

    /* ------------- setup_text_align --------------------------------- */

    public function test_text_align_passes_through_value(): void
    {
        $this->assertSame(
            'text-align: center;;',
            $this->renderer->setup_text_align('center', 'text-align: {{value}};')
        );
    }

    public function test_text_align_strips_html(): void
    {
        $this->assertSame(
            'text-align: left;;',
            $this->renderer->setup_text_align('<script>left</script>', 'text-align: {{value}};')
        );
    }
}
