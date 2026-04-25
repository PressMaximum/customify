<?php
/**
 * Preview Colors — demo import preload.
 *
 * When a Customify demo is imported, seed the panel with the demo's curated
 * palette set + active palette. Reads palette data from
 * `inc/preview-colors/demo-palettes/<demo-slug>.json` and writes through the
 * same wp_options the panel + Customizer control share. Importing twice
 * (e.g. switching demos) overwrites both options cleanly.
 *
 * Activation hooks:
 *   - `customify/demo_imported`  (Customify's own demo importer)
 *   - `pt-ocdi/after_import`     (One Click Demo Import compat)
 *
 * Pass the demo slug as the action argument; if the matching JSON file does
 * not exist this method is a no-op (fail-quiet).
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Demo_Import
{
	const PALETTES_DIR = 'demo-palettes';

	public static function init()
	{
		add_action('customify/demo_imported', array(__CLASS__, 'preload'));
		add_action('pt-ocdi/after_import',    array(__CLASS__, 'preload_from_ocdi'));
	}

	/**
	 * One Click Demo Import passes a $selected_import array, not a slug. Pluck
	 * a usable slug out of it and dispatch through the standard preload path.
	 */
	public static function preload_from_ocdi($selected_import)
	{
		$slug = '';
		if (is_array($selected_import) && isset($selected_import['import_file_name'])) {
			$slug = sanitize_title((string) $selected_import['import_file_name']);
		} elseif (is_string($selected_import)) {
			$slug = sanitize_title($selected_import);
		}
		if ('' !== $slug) {
			self::preload($slug);
		}
	}

	/**
	 * Read demo-palettes/<slug>.json and seed both panel options.
	 *
	 * Expected file shape:
	 *   {
	 *     "active": "ashwood-original",
	 *     "palettes": [
	 *       {"id":"…","name":"…","colors":{"base":"#…", … six slots …}},
	 *       …
	 *     ]
	 *   }
	 *
	 * Anything that fails sanitize is dropped; an empty result aborts the
	 * preload so we never overwrite a working palette set with garbage.
	 *
	 * @param string $demo_slug
	 * @return void
	 */
	public static function preload($demo_slug)
	{
		$slug = sanitize_title((string) $demo_slug);
		if ('' === $slug) {
			return;
		}

		$file = __DIR__ . '/' . self::PALETTES_DIR . '/' . $slug . '.json';
		if (! is_readable($file)) {
			return;
		}

		$raw = file_get_contents($file);
		if (false === $raw) {
			return;
		}

		$data = json_decode($raw, true);
		if (! is_array($data) || empty($data['palettes']) || ! is_array($data['palettes'])) {
			return;
		}

		$clean = Customify_Preview_Colors_Ajax::sanitize_palettes($data['palettes']);
		if (empty($clean)) {
			return;
		}

		update_option(Customify_Preview_Colors_Ajax::OPTION_PALETTES, $clean, false);

		$active = isset($data['active']) ? Customify_Preview_Colors_Ajax::sanitize_active_id($data['active']) : '';
		if ('' === $active) {
			$active = isset($clean[0]['id']) ? $clean[0]['id'] : '';
		}
		// Make sure the active id actually points at a palette we just saved.
		$ids = array_map(function ($p) { return $p['id']; }, $clean);
		if (! in_array($active, $ids, true)) {
			$active = $clean[0]['id'];
		}
		update_option(Customify_Preview_Colors_Ajax::OPTION_ACTIVE, $active, false);
	}
}
