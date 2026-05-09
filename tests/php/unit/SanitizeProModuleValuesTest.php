<?php

namespace Customify\Tests\Unit;

/**
 * Customify::sanitize_pro_module_values()
 *
 * Sanitizes the modal payload before passing to Pro's save(). Each field
 * type has its own coercion path; unknown types fall back to text.
 *
 * Defends the AJAX boundary against:
 *   - extra/spurious keys not in the schema
 *   - type-mismatched payloads (e.g. string for checkbox)
 *   - HTML/script in text fields
 *   - select values not in the allowed list
 */
final class SanitizeProModuleValuesTest extends \Customify\Tests\CustomifyHelpersTestCase
{
    private function sanitize(array $fields, array $values): array
    {
        return $this->invoke('sanitize_pro_module_values', $fields, $values);
    }

    public function test_text_field_strips_html_tags(): void
    {
        // sanitize_text_field uses strip_tags semantics: HTML tags removed,
        // tag content preserved (so <script>x</script>abc → "xabc"). The
        // important guarantee is that NO TAGS survive into the saved value.
        $out = $this->sanitize(
            array(array('name' => 'kit_id', 'type' => 'text')),
            array('kit_id' => '<script>x</script>abc')
        );
        $this->assertStringNotContainsString('<', $out['kit_id']);
        $this->assertStringNotContainsString('>', $out['kit_id']);
    }

    public function test_unknown_field_dropped(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'kit_id', 'type' => 'text')),
            array('kit_id' => 'a', 'evil' => 'b')
        );
        $this->assertArrayNotHasKey('evil', $out);
    }

    public function test_select_value_outside_choices_falls_back_to_first(): void
    {
        $out = $this->sanitize(
            array(array(
                'name' => 'load_type',
                'type' => 'select',
                'options' => array('link' => 'Link', 'import' => 'Import'),
            )),
            array('load_type' => 'attacker')
        );
        $this->assertSame('link', $out['load_type']);
    }

    public function test_select_value_in_choices_passes(): void
    {
        $out = $this->sanitize(
            array(array(
                'name' => 'load_type',
                'type' => 'select',
                'options' => array('link' => 'Link', 'import' => 'Import'),
            )),
            array('load_type' => 'import')
        );
        $this->assertSame('import', $out['load_type']);
    }

    public function test_html_field_skipped_from_output(): void
    {
        // 'html' type is display-only; it must NOT round-trip into the saved values.
        $out = $this->sanitize(
            array(
                array('name' => 'fonts', 'type' => 'html', 'content' => '<ul>...</ul>'),
                array('name' => 'kit_id', 'type' => 'text'),
            ),
            array('fonts' => 'attacker injects', 'kit_id' => 'abc')
        );
        $this->assertArrayNotHasKey('fonts', $out);
        $this->assertSame('abc', $out['kit_id']);
    }

    public function test_checkbox_truthy_normalizes_to_one(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'on', 'type' => 'checkbox')),
            array('on' => 'true')
        );
        $this->assertSame(1, $out['on']);
    }

    public function test_checkbox_falsy_normalizes_to_zero(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'on', 'type' => 'checkbox')),
            array('on' => '')
        );
        $this->assertSame(0, $out['on']);
    }

    public function test_number_clamped_to_min_max(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'count', 'type' => 'number', 'min' => 0, 'max' => 10)),
            array('count' => '999')
        );
        $this->assertSame(10.0, $out['count']);

        $out = $this->sanitize(
            array(array('name' => 'count', 'type' => 'number', 'min' => 0, 'max' => 10)),
            array('count' => '-5')
        );
        $this->assertSame(0.0, $out['count']);
    }

    public function test_color_valid_hex_passes(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'c', 'type' => 'color')),
            array('c' => '#ff00aa')
        );
        $this->assertSame('#ff00aa', $out['c']);
    }

    public function test_color_invalid_dropped(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'c', 'type' => 'color')),
            array('c' => 'javascript:alert(1)')
        );
        $this->assertArrayNotHasKey('c', $out);
    }

    public function test_email_invalid_yields_empty(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'e', 'type' => 'email')),
            array('e' => 'not-an-email')
        );
        // Real WP sanitize_email returns '' on invalid input.
        $this->assertSame('', $out['e']);
    }

    public function test_email_valid_passes_through(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'e', 'type' => 'email')),
            array('e' => 'admin@example.com')
        );
        $this->assertSame('admin@example.com', $out['e']);
    }

    public function test_radio_group_value_outside_options_falls_back(): void
    {
        $out = $this->sanitize(
            array(array(
                'name'    => 'align',
                'type'    => 'radio_group',
                'options' => array('left' => 'Left', 'right' => 'Right'),
            )),
            array('align' => 'attacker')
        );
        $this->assertSame('left', $out['align']);
    }

    public function test_textarea_kses_post_keeps_safe_html(): void
    {
        $out = $this->sanitize(
            array(array('name' => 'note', 'type' => 'textarea')),
            array('note' => '<p>Hello <strong>world</strong></p>')
        );
        $this->assertStringContainsString('Hello', $out['note']);
        $this->assertStringContainsString('<strong>', $out['note']);
    }
}
