<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */


defined('ABSPATH') || exit;

if (!wc_coupons_enabled()) { // @codingStandardsIgnoreLine.
	return;
}

?>
<div class="woocommerce-form-coupon-toggle">
	<?php wc_print_notice(apply_filters('woocommerce_checkout_coupon_message', __('Have a coupon?', 'customify') . ' <a href="#" class="showcoupon">' . __('Click here to enter your code', 'customify') . '</a>'), 'notice'); ?>
</div>

<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">

	<p><?php esc_html_e('If you have a coupon code, please apply it below.', 'customify'); ?></p>

	<div class="input-group-text-button">
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Coupon code', 'customify'); ?>" id="coupon_code" value="" />
		<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'customify'); ?>"><?php esc_html_e('Apply coupon', 'customify'); ?></button>
	</div>

</form>
