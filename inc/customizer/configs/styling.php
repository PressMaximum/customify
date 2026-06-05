<?php
/**
 * Customizer config: Styling panel (retired).
 *
 * The Styling panel and every site-wide color field that used to live under it
 * were consolidated into the new top-level "Colors" section
 * (inc/customizer/configs/colors.php). Nothing registers under `styling_panel`
 * anymore, so the empty panel registration has been removed — an empty panel is
 * dead registration (WordPress hides panels with no sections) and it needlessly
 * reserved a slot in the "General Options" panel group.
 *
 * The `customify_customizer_styling_config()` callback is kept as a no-op: it is
 * a public `customify/customizer/config` filter callback (AGENTS.md §4.2 — never
 * delete public functions), so external code that references it keeps working.
 * No theme_mod keys are touched; saved values on existing sites are unaffected.
 *
 * Colors is anchored into the General Options group via get_panel_groups() in
 * inc/customizer/class-customizer.php — not via a raw priority here.
 *
 * @package Customify
 */

if ( ! function_exists( 'customify_customizer_styling_config' ) ) {
	function customify_customizer_styling_config( $configs ) {
		// Styling panel retired — the Colors section now owns these settings.
		// See inc/customizer/configs/colors.php.
		return $configs;
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_styling_config' );
