<?php
/**
 * Preview Colors — WP Customizer integration.
 *
 * Adds a "Color palette" control inside the Customizer's Global Colors
 * section. The control hosts the same panel UI that the standalone
 * `?preview-colors=1` overlay uses (shadow-DOM mount, same JS bundle, same
 * AJAX endpoints, same option storage). Only the host context differs.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Customizer
{
	const SECTION_ID = 'global_styling';
	const CONTROL_ID = 'customify_color_palette';
	const HOST_ID    = 'customify-preview-colors-root';
	// Independent script handle — the Customizer bundle is a separate webpack
	// entry from the frontend overlay so the two can evolve independently.
	const HANDLE     = 'customify-preview-colors-customizer';

	public static function init()
	{
		// Theme runs its own `customize_register` callback at priority 666 to
		// translate `apply_filters('customify/customizer/config', …)` into
		// sections and controls. We need the global_styling section to exist
		// before our control attaches, so register at a later priority.
		add_action('customize_register', array(__CLASS__, 'register'), 700);
		add_action('customize_controls_enqueue_scripts', array(__CLASS__, 'enqueue_controls'));
		// Preview-iframe bundle: rewrites the :root vars block on every
		// setting change so `transport: 'postMessage'` updates land without
		// reloading the iframe.
		add_action('customize_preview_init', array(__CLASS__, 'enqueue_preview'));
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

		require_once __DIR__ . '/class-preview-colors-customizer-control.php';

		// The Global Colors section is registered through the theme's config
		// filter pipeline (inc/customizer/configs/styling.php), which runs on
		// `after_setup_theme`. By the time `customize_register` fires, the
		// section exists. Bail gracefully if it doesn't (e.g. config disabled).
		if (! $wp_customize->get_section(self::SECTION_ID)) {
			return;
		}

		// Active palette id — string keyed slug.
		$wp_customize->add_setting(Customify_Preview_Colors_Ajax::OPTION_ACTIVE, array(
			'type'              => 'option',
			'capability'        => Customify_Preview_Colors_Ajax::CAPABILITY,
			'default'           => '',
			'transport'         => 'postMessage',
			'sanitize_callback' => array('Customify_Preview_Colors_Ajax', 'sanitize_active_id'),
		));

		// User palettes — array of `{id, name, colors{base,text,…}}` objects.
		$wp_customize->add_setting(Customify_Preview_Colors_Ajax::OPTION_PALETTES, array(
			'type'              => 'option',
			'capability'        => Customify_Preview_Colors_Ajax::CAPABILITY,
			'default'           => array(),
			'transport'         => 'postMessage',
			'sanitize_callback' => array('Customify_Preview_Colors_Ajax', 'sanitize_palettes'),
		));

		$wp_customize->add_control(new Customify_Preview_Colors_Customizer_Control(
			$wp_customize,
			self::CONTROL_ID,
			array(
				'label'    => __('Color palette', 'customify'),
				'section'  => self::SECTION_ID,
				'priority' => 5, // Top of section, above the individual color rows.
				'settings' => array(
					'active'   => Customify_Preview_Colors_Ajax::OPTION_ACTIVE,
					'palettes' => Customify_Preview_Colors_Ajax::OPTION_PALETTES,
				),
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
		if (! current_user_can(Customify_Preview_Colors_Ajax::CAPABILITY)) {
			return;
		}

		$suffix   = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		$base_uri = get_template_directory_uri();
		$base_dir = get_template_directory();
		$css_path = '/build/css/backend/customizer/preview-colors' . $suffix . '.css';
		$js_path  = '/build/js/backend/customizer/preview-colors' . $suffix . '.js';
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

		wp_localize_script(self::HANDLE, 'CustomifyPreviewColors', array(
			'rootId'        => self::HOST_ID,
			// Tells the bundle to use Customizer setting writes (deferred save
			// on Publish) instead of immediate AJAX. Setting IDs match the
			// option keys; the bundle calls `wp.customize(id).set(value)`.
			'context'       => 'customizer',
			'settingIds'    => array(
				'active'   => Customify_Preview_Colors_Ajax::OPTION_ACTIVE,
				'palettes' => Customify_Preview_Colors_Ajax::OPTION_PALETTES,
			),
			'slots'         => Customify_Preview_Colors_Config::slots(),
			'slotDesc'      => Customify_Preview_Colors_Config::slot_descriptions(),
			'themePresets'  => Customify_Preview_Colors_Config::theme_presets(),
			'settingsRows'  => Customify_Preview_Colors_Config::settings_rows(),
			'userPalettes'  => Customify_Preview_Colors_Ajax::get_user_palettes(),
			'activeId'      => Customify_Preview_Colors_Ajax::get_active_id(),
			// Dark-mode fallback blobs (PHASE-7-PLAN §10.6). JS uses these to
			// resolve the same 5-tier chain that PHP runs server-side, so live
			// panel preview matches what the visitor will see post-publish.
			'darkBaselines' => Customify_Preview_Colors_Dark::baselines(),
			'legacyMods'    => self::collect_legacy_mods(),
		));
	}

	/**
	 * Collect saved theme_mod values for the legacy slots used in the dark
	 * resolve chain (Phase 6 compat carries them; this helper is just the
	 * read side). Returns slot-keyed array — only slots with a saved value
	 * are present.
	 */
	public static function collect_legacy_mods()
	{
		$out = array();
		$map = array(
			'text'      => 'global_styling_color_text',
			'primary'   => 'global_styling_color_primary',
			'secondary' => 'global_styling_color_secondary',
		);
		foreach ($map as $slot => $mod_key) {
			$val = get_theme_mod($mod_key);
			if (is_string($val) && preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $val)) {
				$out[$slot] = strtoupper($val);
			}
		}
		return $out;
	}

	/**
	 * Enqueue the preview-iframe bundle. Listens for postMessage updates from
	 * the controls pane and rewrites `<style id="customify-preview-colors-vars">`
	 * inside the iframe — same shape as `output_root_vars()` so the override
	 * layer in style-theme.css picks up changes without an iframe refresh.
	 */
	public static function enqueue_preview()
	{
		$suffix = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		$base   = get_template_directory_uri();
		$ver    = Customify::$version ?: '0.4.13';

		wp_enqueue_script(
			self::HANDLE . '-preview',
			$base . '/build/js/backend/customizer/preview-colors-preview' . $suffix . '.js',
			array('customize-preview'),
			$ver,
			true
		);

		wp_localize_script(self::HANDLE . '-preview', 'CustomifyPreviewColorsPreview', array(
			'styleId'      => Customify_Preview_Colors::HANDLE . '-vars',
			'settingIds'   => array(
				'active'   => Customify_Preview_Colors_Ajax::OPTION_ACTIVE,
				'palettes' => Customify_Preview_Colors_Ajax::OPTION_PALETTES,
			),
			'slots'        => Customify_Preview_Colors_Config::slots(),
			'themePresets' => Customify_Preview_Colors_Config::theme_presets(),
			// Preview iframe needs the same dark resolve fixtures as the
			// controls bundle so live previews match the published render.
			'darkBaselines' => Customify_Preview_Colors_Dark::baselines(),
			'legacyMods'    => self::collect_legacy_mods(),
		));
	}
}
