<?php

namespace Customify\Tests\Unit;

use Customify\Tests\UnitTestCase;

/**
 * Sanity check that PHPUnit + Brain Monkey wiring is alive. If this fails,
 * fix bootstrap before chasing real test failures.
 */
final class SmokeTest extends UnitTestCase
{
    public function test_phpunit_and_brain_monkey_are_loaded(): void
    {
        $this->assertTrue(class_exists(\PHPUnit\Framework\TestCase::class));
        $this->assertTrue(function_exists('Brain\\Monkey\\setUp'));
        $this->assertTrue(function_exists('Brain\\Monkey\\Functions\\when'));
    }

    public function test_default_wp_function_stubs_work(): void
    {
        $this->assertSame('hello', __('hello', 'customify'));
        $this->assertSame('clean', sanitize_text_field('  <b>clean</b>  '));
        $this->assertTrue(rest_sanitize_boolean('on'));
        $this->assertFalse(rest_sanitize_boolean(''));
    }
}
