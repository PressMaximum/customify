<?php

namespace Customify\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;

/**
 * Base class for fast PHP unit tests.
 *
 * Boots Brain Monkey so theme code can call WordPress functions without
 * the full WP test suite. Each test gets a fresh Monkey context so stubs
 * don't leak between tests.
 *
 * Subclasses should:
 *   - Override `setUp()` and call `parent::setUp()` first.
 *   - Use `Functions\stubs()` / `Functions\when()` / `Functions\expect()` to
 *     mock WP API calls the code under test makes.
 *   - Override `tearDown()` and call `parent::tearDown()` to release Monkey.
 */
abstract class UnitTestCase extends TestCase
{
    use AssertStringContains;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        $this->stubCommonWordPressFunctions();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Default stubs for WordPress functions every test would otherwise have
     * to mock. Stubs are no-op pass-throughs that return their input or a
     * sane default — matches typical WP behaviour for sanitization helpers.
     */
    private function stubCommonWordPressFunctions(): void
    {
        Functions\when('__')->returnArg(1);
        Functions\when('_e')->echoArg(1);
        Functions\when('esc_html__')->returnArg(1);
        Functions\when('esc_html_e')->echoArg(1);
        Functions\when('esc_attr__')->returnArg(1);
        Functions\when('esc_attr_e')->echoArg(1);

        Functions\when('wp_unslash')->returnArg(1);
        Functions\when('sanitize_text_field')->alias(static fn ($v) => is_string($v) ? trim(strip_tags($v)) : '');
        Functions\when('sanitize_email')->alias(static function ($v) {
            $v = is_string($v) ? trim($v) : '';
            return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
        });
        Functions\when('absint')->alias(static fn ($v) => abs((int) $v));
        Functions\when('wp_strip_all_tags')->alias(static fn ($v) => trim(strip_tags((string) $v)));
        Functions\when('wp_kses_post')->returnArg(1);
        Functions\when('esc_url')->returnArg(1);
        Functions\when('esc_url_raw')->returnArg(1);
        Functions\when('esc_html')->returnArg(1);
        Functions\when('esc_attr')->returnArg(1);

        Functions\when('rest_sanitize_boolean')->alias(static function ($v) {
            if (is_bool($v)) {
                return $v;
            }
            if (is_int($v)) {
                return (bool) $v;
            }
            $s = strtolower((string) $v);
            return in_array($s, ['1', 'true', 'on', 'yes'], true);
        });

        Functions\when('apply_filters')->alias(static fn ($tag, $value) => $value);
        Functions\when('do_action')->alias(static fn () => null);
        Functions\when('add_action')->alias(static fn () => true);
        Functions\when('add_filter')->alias(static fn () => true);

        Functions\when('wp_parse_args')->alias(static function ($args, $defaults = []) {
            return array_merge((array) $defaults, (array) $args);
        });
    }
}
