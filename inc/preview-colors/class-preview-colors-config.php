<?php
/**
 * Preview Colors — config (slots, descriptions, theme presets, settings rows).
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Config
{
	const SLOTS = array('base', 'text', 'primary', 'secondary', 'accent', 'surface');

	public static function slot_descriptions()
	{
		return apply_filters('customify_preview_colors/slot_descriptions', array(
			'base'      => '<strong>Page background.</strong> Default canvas for body, neutral sections, form wrappers.',
			'text'      => '<strong>Ink foreground.</strong> Body text, headings (H1-H6), labels, nav links at rest.',
			'primary'   => '<strong>Brand action.</strong> CTA buttons, links, focus rings, active tabs, accent words.',
			'surface'   => '<strong>Elevated container.</strong> Card backgrounds, modals, dropdowns, tooltips.',
			'secondary' => '<strong>Dark sectional band.</strong> Callouts, footer, dark header, secondary buttons.',
			'accent'    => '<strong>Decorative pop.</strong> Badges, sticky notes, stat numbers, hover glow, sale tags.',
		));
	}

	public static function theme_presets()
	{
		return apply_filters('customify_preview_colors/theme_presets', array(
			array(
				'id'     => 'ashwood',
				'name'   => 'Ashwood',
				'colors' => array(
					'base' => '#F9F3E4', 'text' => '#1A3A28', 'primary' => '#B35932',
					'secondary' => '#1C2147', 'accent' => '#F5DE9A', 'surface' => '#FFFFFF',
				),
			),
			array(
				'id'     => 'midnight',
				'name'   => 'Midnight',
				'colors' => array(
					'base' => '#0B0D10', 'text' => '#F2F0EB', 'primary' => '#FF7A45',
					'secondary' => '#FFFFFF', 'accent' => '#FFD36A', 'surface' => '#1C1F26',
				),
			),
			array(
				'id'     => 'ocean',
				'name'   => 'Ocean',
				'colors' => array(
					'base' => '#F5F6F4', 'text' => '#0F1C33', 'primary' => '#0055FF',
					'secondary' => '#001D4A', 'accent' => '#B8E6FF', 'surface' => '#FFFFFF',
				),
			),
			array(
				'id'     => 'moss',
				'name'   => 'Moss',
				'colors' => array(
					'base' => '#F4FAF5', 'text' => '#0F2616', 'primary' => '#2B9348',
					'secondary' => '#2B3D28', 'accent' => '#D9F0B5', 'surface' => '#FFFFFF',
				),
			),
		));
	}

	public static function settings_rows()
	{
		return apply_filters('customify_preview_colors/settings_rows', array(
			array('label' => 'Text & ink',      'sublabel' => 'text',      'slots' => array('text')),
			array('label' => 'Primary action',  'sublabel' => 'primary',   'slots' => array('primary')),
			array('label' => 'Page background', 'sublabel' => 'base',      'slots' => array('base')),
			array('label' => 'Card surface',    'sublabel' => 'surface',   'slots' => array('surface')),
			array('label' => 'Dark section',    'sublabel' => 'secondary', 'slots' => array('secondary')),
			array('label' => 'Highlight / pop', 'sublabel' => 'accent',    'slots' => array('accent')),
		));
	}
}
