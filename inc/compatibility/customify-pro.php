<?php
/**
 * Customify Pro compatibility — disable Pro modules that the theme implements
 * natively, and surface the override to the React dashboard so the user sees
 * "Owned by theme" instead of a toggle that silently does nothing.
 *
 * Why a read-time filter (not just the dashboard injection):
 *   Customify_Pro::load_modules() runs at after_setup_theme:30 and
 *   instantiates every enabled module. If the user previously turned the Pro
 *   module on (option = 1) and the theme later started shipping the feature,
 *   we MUST keep the option masked at read so Pro doesn't double-instantiate
 *   the conflicting module on the frontend.
 *
 * Filterable via `customify/compat/pro_modules_owned_by_theme` so a child
 * theme or future Pro module can opt in/out per class name.
 */

/**
 * Map of Pro module class names → reason the theme has disabled them.
 * Reason is shown in the dashboard tooltip when canToggle is false.
 *
 * Currently empty — Header Transparent used to be forced off here, but
 * that broke the toggle when the Pro plugin was active and gave the user
 * no way to opt into Pro's richer version of the feature. The theme's
 * native Customify_Header_Transparent now defers at runtime when the Pro
 * module is enabled (see inc/customizer/configs/header/transparent.php).
 *
 * Kept as a filterable shim so future Pro/theme overlap can re-use it
 * without reintroducing the read-time option mask.
 *
 * @return array<string,string>
 */
function customify_pro_modules_owned_by_theme() {
	return apply_filters( 'customify/compat/pro_modules_owned_by_theme', array() );
}
