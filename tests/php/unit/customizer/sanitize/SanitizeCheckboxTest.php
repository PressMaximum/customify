<?php

namespace Customify\Tests\Unit\Customizer\Sanitize;

use Customify\Tests\UnitTestCase;
use ReflectionMethod;

/**
 * Customify_Sanitize_Input::sanitize_checkbox()
 *
 * Coerces to int 1 (when input is exact `1`, `'1'` or `'on'`) or 0 otherwise.
 * Important: uses loose `==` so '1', 1, true all pass; everything else is 0.
 */
final class SanitizeCheckboxTest extends UnitTestCase
{
    /** @var ReflectionMethod */
    private $sanitize;

    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 5) . '/inc/customizer/class-customizer-sanitize.php';
        $this->sanitize = new ReflectionMethod(\Customify_Sanitize_Input::class, 'sanitize_checkbox');
        // setAccessible no-op on PHP 8.1+; deprecated on 8.5.
    }

    private function call($value): int
    {
        return $this->sanitize->invoke(new \Customify_Sanitize_Input(), $value);
    }

    /** @dataProvider provideTruthy */
    public function test_truthy_becomes_one($input): void
    {
        $this->assertSame(1, $this->call($input));
    }
    public function provideTruthy(): array
    {
        return array(
            array(1),
            array('1'),
            array('on'),
            array(true),
        );
    }

    /** @dataProvider provideFalsy */
    public function test_falsy_becomes_zero($input): void
    {
        $this->assertSame(0, $this->call($input));
    }
    public function provideFalsy(): array
    {
        return array(
            array(0),
            array('0'),
            array(''),
            array(null),
            array(false),
            array('off'),
            array('false'),
            array('no'),
            array('arbitrary'),
            array(array()),
        );
    }
}
