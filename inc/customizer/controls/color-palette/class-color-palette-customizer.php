<?php
/**
 * Color Palette — WP Customizer integration.
 *
 * Adds a "Color palette" control inside the Customizer's Global Colors
 * section. The control mounts a React app (light DOM) on a host div whose id
 * is reused as the SCSS scope (`#customify-color-palette-root`). Settings
 * write through the Customizer Publish pipeline (`transport: 'postMessage'`),
 * with the preview iframe rebroadcasting the `:root` vars on every change.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Color_Palette_Customizer
{
	const SECTION_ID = 'global_styling';
	const CONTROL_ID = 'customify_color_palette';
	// HOST_ID is also the SCSS scope id — every panel rule in
	// src/backend/customizer/color-palette/customizer.scss is scoped under
	// this selector, so the value must stay in sync between PHP + SCSS.
	const HOST_ID    = 'customify-color-palette-root';
	// Independent script handle — the Customizer bundle is a separate webpack
	// entry from the frontend overlay so the two can evolve independently.
	const HANDLE     = 'customify-color-palette-customizer';

	public static function init()
	{
		// Theme runs its own `customize_register` callback at priority 666 to
		// translate `apply_filters('customify/customizer/config', …)` into
		// sections and controls. We need the global_styling section to exist
		// before our control attaches, so register at a later priority.
		add_action('customize_register', array(__CLASS__, 'register'), 700);
		add_action('customize_controls_enqueue_scripts', array(__CLASS__, 'enqueue_controls'));
		// Preview-iframe live update is bundled into the `customify-customizer`
		// script (see src/backend/customizer/customizer.js + the localize block
		// in inc/customizer/class-customizer.php::preview_js()) — one combined
		// iframe asset instead of two.
	}

	/**
	 * Register the control inside the existing Global Colors section.
	 *
	 * The control binds two `option`-type settings that target the same
	 * wp_options keys used by the standalone overlay's AJAX endpoints. In
	 * Customizer context the panel JS calls `wp.customize(setting).set(value)`
	 * instead of POSTing — Publish then writes through the Customizer save
	 * pipeline, sanitised by the same Ajax helpers so the storage shape stays
	 * identical between the two entry points.
	 */
	public static function register($wp_customize)
	{
		if (! ($wp_customize instanceof WP_Customize_Manager)) {
			return;
		}

		require_once __DIR__ . '/class-color-palette-customizer-control.php';

		// The Global Colors section is registered through the theme's config
		// filter pipeline (inc/customizer/configs/styling.php), which runs on
		// `after_setup_theme`. By the time `customize_register` fires, the
		// section exists. Bail gracefully if it doesn't (e.g. config disabled).
		if (! $wp_customize->get_section(self::SECTION_ID)) {
			return;
		}

		// Active palette id — string keyed slug. Default = first entry in
		// the filtered theme_presets list (`'ashwood'` out of the box) so
		// the Customizer reverts here on "Reset to defaults" / publishes a
		// sensible value on first save.
		$wp_customize->add_setting(Customify_Color_Palette_Ajax::OPTION_ACTIVE, array(
			'type'              => 'option',
			'capability'        => Customify_Color_Palette_Ajax::CAPABILITY,
			'default'           => Customify_Color_Palette_Config::default_active_id(),
			'transport'         => 'postMessage',
			'sanitize_callback' => array('Customify_Color_Palette_Ajax', 'sanitize_active_id'),
		));

		// User palettes — array of `{id, name, colors{base,text,…}}` objects.
		$wp_customize->add_setting(Customify_Color_Palette_Ajax::OPTION_PALETTES, array(
			'type'              => 'option',
			'capability'        => Customify_Color_Palette_Ajax::CAPABILITY,
			'default'           => array(),
			'transport'         => 'postMessage',
			'sanitize_callback' => array('Customify_Color_Palette_Ajax', 'sanitize_palettes'),
		));

		$wp_customize->add_control(new Customify_Color_Palette_Customizer_Control(
			$wp_customize,
			self::CONTROL_ID,
			array(
				'label'          => __('Color palette', 'customify'),
				'section'        => self::SECTION_ID,
				'priority'       => 5, // Top of section, above the individual color rows.
				'settings'       => array(
					'active'   => Customify_Color_Palette_Ajax::OPTION_ACTIVE,
					'palettes' => Customify_Color_Palette_Ajax::OPTION_PALETTES,
				),
				// "Reset section" must not wipe the user's saved custom palettes.
				'reset_exclude'  => array(Customify_Color_Palette_Ajax::OPTION_PALETTES),
			)
		));
	}

	/**
	 * Enqueue the panel bundle on the Customizer controls page (left side).
	 * The bundle is the same one used by the frontend overlay; it mounts on
	 * the host <div> emitted by the control's render_content().
	 */
	public static function enqueue_controls()
	{
		if (! current_user_can(Customify_Color_Palette_Ajax::CAPABILITY)) {
			return;
		}

		$suffix   = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		$base_uri = get_template_directory_uri();
		$base_dir = get_template_directory();
		$css_path = '/build/css/backend/customizer/color-palette' . $suffix . '.css';
		$js_path  = '/build/js/backend/customizer/color-palette' . $suffix . '.js';
		// File mtime as cache buster so any rebuild invalidates the browser
		// cache without a manual theme-version bump.
		$css_ver  = file_exists($base_dir . $css_path) ? filemtime($base_dir . $css_path) : (Customify::$version ?: '0.4.13');
		$js_ver   = file_exists($base_dir . $js_path)  ? filemtime($base_dir . $js_path)  : (Customify::$version ?: '0.4.13');

		// Light-DOM panel — enqueue CSS directly into the Customizer controls
		// page so WP can manage it via wp_register_style/wp_enqueue_style.
		wp_enqueue_style(
			self::HANDLE,
			$base_uri . $css_path,
			array(),
			$css_ver
		);

		wp_enqueue_script(
			self::HANDLE,
			$base_uri . $js_path,
			array('customize-controls'),
			$js_ver,
			true
		);

		wp_localize_script(self::HANDLE, 'CustomifyColorPalette', array(
			'rootId'        => self::HOST_ID,
			// Tells the bundle to use Customizer setting writes (deferred save
			// on Publish) instead of immediate AJAX. Setting IDs match the
			// option keys; the bundle calls `wp.customize(id).set(value)`.
			'context'       => 'customizer',
			'settingIds'    => array(
				'active'   => Customify_Color_Palette_Ajax::OPTION_ACTIVE,
				'palettes' => Customify_Color_Palette_Ajax::OPTION_PALETTES,
			),
			'slots'         => Customify_Color_Palette_Config::slots(),
			'slotDesc'      => Customify_Color_Palette_Config::slot_descriptions(),
			'themePresets'  => Customify_Color_Palette_Config::theme_presets(),
			'settingsRows'  => Customify_Color_Palette_Config::settings_rows(),
			'userPalettes'  => Customify_Color_Palette_Ajax::get_user_palettes(),
			'activeId'      => Customify_Color_Palette_Ajax::get_active_id(),
		));
	}
}
