<?php
/**
 * Demo palette set — Ashwood demo.
 *
 * Loaded by `Customify_Preview_Colors_Demo_Import::preload('ashwood')` after
 * the demo content import completes. Seeds the panel with 5 alternates and
 * activates the original.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

return array(
	'active'   => 'ashwood-original',
	'palettes' => array(
		array(
			'id'     => 'ashwood-original',
			'name'   => _x('Ashwood', 'palette name', 'customify'),
			'colors' => array(
				'base'      => '#F9F3E4',
				'text'      => '#1A3A28',
				'primary'   => '#B35932',
				'secondary' => '#1C2147',
				'accent'    => '#F5DE9A',
				'surface'   => '#FFFFFF',
			),
		),
		array(
			'id'     => 'ashwood-coastal',
			'name'   => _x('Coastal', 'palette name', 'customify'),
			'colors' => array(
				'base'      => '#F4F8FA',
				'text'      => '#102A43',
				'primary'   => '#3578B8',
				'secondary' => '#0F2B40',
				'accent'    => '#F4D38A',
				'surface'   => '#FFFFFF',
			),
		),
		array(
			'id'     => 'ashwood-moss',
			'name'   => _x('Moss', 'palette name', 'customify'),
			'colors' => array(
				'base'      => '#F4FAF5',
				'text'      => '#0F2616',
				'primary'   => '#2B9348',
				'secondary' => '#2B3D28',
				'accent'    => '#D9F0B5',
				'surface'   => '#FFFFFF',
			),
		),
		array(
			'id'     => 'ashwood-rust',
			'name'   => _x('Rust', 'palette name', 'customify'),
			'colors' => array(
				'base'      => '#FBF4EE',
				'text'      => '#3D1F0A',
				'primary'   => '#C25B2E',
				'secondary' => '#492012',
				'accent'    => '#F0C988',
				'surface'   => '#FFFFFF',
			),
		),
		array(
			'id'     => 'ashwood-dusk',
			'name'   => _x('Dusk', 'palette name', 'customify'),
			'colors' => array(
				'base'      => '#0B0D10',
				'text'      => '#F2F0EB',
				'primary'   => '#FF7A45',
				'secondary' => '#1C1F26',
				'accent'    => '#FFD36A',
				'surface'   => '#1C1F26',
			),
		),
	),
);
