<?php

namespace Customify\Tests;

use Brain\Monkey\Functions;
use Customify\Tests\UnitTestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Shared base for tests that exercise private helpers on the Customify
 * singleton without booting the whole theme.
 *
 * Strategy:
 *   - Stub the WP functions Customify constructor + helpers might touch.
 *   - require_once the class file.
 *   - Build the instance via ReflectionClass::newInstanceWithoutConstructor()
 *     so we skip init_hooks() and other side-effect-heavy boot code.
 *   - Invoke the target method through ReflectionMethod.
 */
abstract class CustomifyHelpersTestCase extends UnitTestCase
{
    /** @var \Customify */
    protected $customify;

    protected function setUp(): void
    {
        parent::setUp();

        Functions\when('get_template_directory')->justReturn(dirname(__DIR__, 3));
        Functions\when('get_template_directory_uri')->justReturn('http://example.test/wp-content/themes/customify');
        Functions\when('add_query_arg')->alias(static function ($key, $value, $url = '') {
            $sep = strpos((string) $url, '?') === false ? '?' : '&';
            return $url . $sep . urlencode((string) $key) . '=' . urlencode((string) $value);
        });
        Functions\when('admin_url')->alias(static fn ($p = '') => 'http://example.test/wp-admin/' . ltrim((string) $p, '/'));
        Functions\when('wp_get_theme')->justReturn(new class {
            public function get($k) {
                return ['Version'=>'0.4.14','ThemeURI'=>'http://x','Name'=>'Customify','Author'=>'PressMaximum'][$k] ?? '';
            }
        });
        Functions\when('class_exists')->alias(static fn ($c) => \class_exists($c));
        Functions\when('function_exists')->alias(static fn ($f) => \function_exists($f));

        require_once dirname(__DIR__, 2) . '/../inc/class-customify.php';

        // Skip constructor — init_hooks() registers add_action callbacks which
        // we do not need for unit-testing pure helpers.
        $reflection      = new ReflectionClass(\Customify::class);
        $this->customify = $reflection->newInstanceWithoutConstructor();
    }

    protected function invoke(string $method, ...$args)
    {
        $m = new ReflectionMethod(\Customify::class, $method);
        return $m->invoke($this->customify, ...$args);
    }
}
