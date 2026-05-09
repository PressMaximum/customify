<?php

namespace Customify\Tests\Unit;

/**
 * Customify::parse_pro_admin_notices()
 *
 * Pro modules' after_save() echoes <div class="notice notice-{type}">…</div>
 * markup (legacy admin form pattern). The new dashboard captures that buffer
 * and converts it to JSON-friendly { type, message } notices for the React
 * snackbar. Critical to keep regex tight and tolerant of WP markup variants.
 */
final class ParseProAdminNoticesTest extends \Customify\Tests\CustomifyHelpersTestCase
{
    private function parse(string $html): array
    {
        return $this->invoke('parse_pro_admin_notices', $html);
    }

    public function test_empty_string_returns_empty_array(): void
    {
        $this->assertSame(array(), $this->parse(''));
        $this->assertSame(array(), $this->parse('   '));
    }

    public function test_no_notice_div_returns_empty_array(): void
    {
        $this->assertSame(array(), $this->parse('<p>Hello</p>'));
    }

    public function test_single_error_notice_extracted(): void
    {
        $html = '<div class="notice notice-error is-dismissible"><p>Could not load font file.</p></div>';
        $out  = $this->parse($html);
        $this->assertCount(1, $out);
        $this->assertSame('error', $out[0]['type']);
        $this->assertSame('Could not load font file.', $out[0]['message']);
    }

    public function test_success_notice_type_recognized(): void
    {
        $html = '<div class="updated notice notice-success"><p>Saved.</p></div>';
        $out  = $this->parse($html);
        $this->assertSame('success', $out[0]['type']);
    }

    public function test_warning_and_info_types(): void
    {
        $html = '<div class="notice notice-warning"><p>Heads up</p></div>'
              . '<div class="notice notice-info"><p>FYI</p></div>';
        $out = $this->parse($html);
        $this->assertCount(2, $out);
        $this->assertSame('warning', $out[0]['type']);
        $this->assertSame('info', $out[1]['type']);
    }

    public function test_unknown_type_defaults_to_info(): void
    {
        $html = '<div class="notice notice-purple"><p>Custom level</p></div>';
        $out  = $this->parse($html);
        $this->assertSame('info', $out[0]['type']);
    }

    public function test_html_tags_stripped_from_message(): void
    {
        $html = '<div class="notice notice-error"><p>Could not load <strong>font</strong> file.</p></div>';
        $out  = $this->parse($html);
        $this->assertSame('Could not load font file.', $out[0]['message']);
    }

    public function test_blank_message_skipped(): void
    {
        $html = '<div class="notice notice-error"><p></p></div>';
        $this->assertSame(array(), $this->parse($html));
    }

    public function test_multiple_notices_in_order(): void
    {
        $html = '<div class="notice notice-success"><p>One</p></div>'
              . '<div class="notice notice-error"><p>Two</p></div>'
              . '<div class="notice notice-info"><p>Three</p></div>';
        $out = $this->parse($html);
        $this->assertCount(3, $out);
        $this->assertSame('One', $out[0]['message']);
        $this->assertSame('Two', $out[1]['message']);
        $this->assertSame('Three', $out[2]['message']);
    }
}
