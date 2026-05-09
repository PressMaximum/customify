<?php

namespace Customify\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * Customify::guard_pro_module_dependencies()
 *
 * Defensive shim: when WooCommerce is not loaded but a WC-dependent Pro
 * module is enabled in the customify_modules option, force-disable it
 * BEFORE Customify_Pro's load() instantiates the class (would otherwise
 * fatal in wc_get_page_id()).
 *
 * Also surfaces an admin notice via the customify_pro_modules_disabled_for_wc
 * transient.
 */
final class WCDependencyGuardTest extends \Customify\Tests\CustomifyHelpersTestCase
{
    /** @var array<string,mixed> */
    private $options = array();

    /** @var array<string,mixed> */
    private $transients = array();

    protected function setUp(): void
    {
        parent::setUp();

        $this->options    = array();
        $this->transients = array();

        Functions\when('get_option')->alias(fn ($k, $d = false) => $this->options[$k] ?? $d);
        Functions\when('update_option')->alias(function ($k, $v) {
            $this->options[$k] = $v;
            return true;
        });
        Functions\when('set_transient')->alias(function ($k, $v, $ttl = 0) {
            $this->transients[$k] = $v;
            return true;
        });
        Functions\when('get_transient')->alias(fn ($k) => $this->transients[$k] ?? false);
        Functions\when('delete_transient')->alias(function ($k) {
            unset($this->transients[$k]);
            return true;
        });

        if (! defined('HOUR_IN_SECONDS')) {
            define('HOUR_IN_SECONDS', 3600);
        }
    }

    public function test_no_op_when_pro_inactive(): void
    {
        // No Customify_Pro() function available — guard exits silently.
        $this->options['customify_modules'] = array('Customify_Pro_Module_WC_Off_Canvas_Filter' => 1);
        $this->customify->guard_pro_module_dependencies();
        $this->assertSame(1, $this->options['customify_modules']['Customify_Pro_Module_WC_Off_Canvas_Filter']);
    }

    public function test_no_op_when_woocommerce_active(): void
    {
        $this->declareCustomifyProStub();
        Functions\when('class_exists')->alias(fn ($c) => $c === 'WooCommerce' ? true : \class_exists($c));

        $this->options['customify_modules'] = array('Customify_Pro_Module_WC_Off_Canvas_Filter' => 1);
        $this->customify->guard_pro_module_dependencies();

        $this->assertSame(1, $this->options['customify_modules']['Customify_Pro_Module_WC_Off_Canvas_Filter']);
        $this->assertArrayNotHasKey('customify_pro_modules_disabled_for_wc', $this->transients);
    }

    public function test_disables_wc_modules_when_woocommerce_missing(): void
    {
        $this->declareCustomifyProStub();
        // WooCommerce class is NOT defined → is_woocommerce_active() returns false
        Functions\when('class_exists')->alias(fn ($c) => $c === 'WooCommerce' ? false : \class_exists($c));

        $this->options['customify_modules'] = array(
            'Customify_Pro_Module_WC_Off_Canvas_Filter' => 1,
            'Customify_Pro_Module_WC_Quick_View'        => 1,
            'Customify_Pro_Module_Header_Sticky'        => 1, // not WC, must stay
        );

        $this->customify->guard_pro_module_dependencies();

        $this->assertSame(0, $this->options['customify_modules']['Customify_Pro_Module_WC_Off_Canvas_Filter']);
        $this->assertSame(0, $this->options['customify_modules']['Customify_Pro_Module_WC_Quick_View']);
        $this->assertSame(1, $this->options['customify_modules']['Customify_Pro_Module_Header_Sticky']);

        $this->assertArrayHasKey('customify_pro_modules_disabled_for_wc', $this->transients);
        $this->assertContains('Customify_Pro_Module_WC_Off_Canvas_Filter', $this->transients['customify_pro_modules_disabled_for_wc']);
    }

    public function test_no_transient_set_when_no_wc_modules_enabled(): void
    {
        $this->declareCustomifyProStub();
        Functions\when('class_exists')->alias(fn ($c) => $c === 'WooCommerce' ? false : \class_exists($c));

        $this->options['customify_modules'] = array('Customify_Pro_Module_Header_Sticky' => 1);
        $this->customify->guard_pro_module_dependencies();

        $this->assertArrayNotHasKey('customify_pro_modules_disabled_for_wc', $this->transients);
    }

    public function test_filter_can_extend_wc_module_list(): void
    {
        Functions\when('apply_filters')->alias(static function ($tag, $value) {
            if ($tag === 'customify/dashboard/wc_pro_modules') {
                return array_merge((array) $value, array('My_Custom_WC_Module'));
            }
            return $value;
        });

        $modules = $this->customify->get_woocommerce_pro_modules();
        $this->assertContains('My_Custom_WC_Module', $modules);
        $this->assertContains('Customify_Pro_Module_WC_Off_Canvas_Filter', $modules);
    }

    private function declareCustomifyProStub(): void
    {
        if (! function_exists('Customify_Pro')) {
            // Define in global scope; later tests reuse this stub.
            eval('function Customify_Pro() { return new \stdClass(); }');
        }
    }
}
