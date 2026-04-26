<?php
/**
 * Phase 7 dark mode fallback fixtures.
 *
 * Read by both PHP (`Customify_Preview_Colors_Dark::baselines()`) and JS
 * (shipped via wp_localize_script as `CustomifyPreviewColors.darkBaselines`).
 * `scss` mirrors `_skins.scss` greyscale literals so unconfigured sites
 * match today's header/footer dark sections; `hex` is the last-resort hex
 * (Midnight preset values).
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

return array(
	'scss' => array(
		'base'      => '#1A1A1A',
		'text'      => '#FCFCFC',
		'primary'   => '#FCFCFC',
		'secondary' => null,
		'accent'    => null,
		'surface'   => '#1F1F1F',
	),
	'hex' => array(
		'base'      => '#0B0D10',
		'text'      => '#F2F0EB',
		'primary'   => '#FF7A45',
		'secondary' => '#FFFFFF',
		'accent'    => '#FFD36A',
		'surface'   => '#1C1F26',
	),
);
