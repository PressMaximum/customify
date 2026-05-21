<?php
/**
 * Customify Pro compatibility.
 *
 * Theme + Pro plugin are separate codebases. Pro modules that the theme
 * also ships natively are coordinated by `class_exists()` guards on the
 * theme side — the theme's port bails out when Pro is active so the Pro
 * module owns the feature (and its dashboard toggle works normally).
 *
 * Currently handled in this way:
 *   - Header Transparent — theme port is gated in
 *     `inc/customizer/configs/header/transparent.php` against
 *     `Customify_Pro_Module_Header_Transparent`.
 *
 * Add new compat gates here only when the same feature is implemented on
 * both sides and a runtime collision needs preventing. Filters that
 * silently force Pro options to a fixed value should be avoided because
 * they break the user's ability to toggle the module from the Pro
 * dashboard.
 */
