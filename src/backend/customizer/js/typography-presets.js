/**
 * Typography presets runtime — the font-pair quick picks at the top of
 * the Typography section (class-control-typography-presets.php).
 *
 * The control stores nothing: clicking a card patches ONLY the family
 * bits (font / font_type / variant) of the Body + Heading typography
 * settings, going through the bound controls' own encode/decode so the
 * storage shape is byte-identical to a manual font pick. The external
 * setting.set() drives refreshFromSetting() in control.js, which
 * repaints the typography control DOM and its trigger preview — no
 * extra plumbing here.
 *
 * Factory pattern mirrors typography-control.js: closure deps from the
 * control.js IIFE are passed in explicitly; `_` is a genuine global.
 */
export function setupTypographyPresets(deps) {
	var $ = deps.$;
	var $document = deps.$document;
	var wpcustomize = deps.wpcustomize;

	var BODY_SETTING = "global_typography_base_p";
	var HEADING_SETTING = "global_typography_base_heading";

	function decoded(id) {
		var c = wpcustomize.control(id);
		if (!c) {
			return {};
		}
		var raw = c.setting.get();
		var v;
		// Values written by controls are encodeURI(JSON) strings, but
		// values straight from storage (saved by PHP / older versions)
		// arrive as plain objects — decodeValue throws on those. Fall
		// back to the raw value itself, exactly like
		// refreshFromSetting() does; returning {} here would make the
		// merge in patchFamily() silently DROP every other sub-value.
		try {
			v = c.decodeValue(raw);
		} catch (e) {
			v = raw;
		}
		return _.isObject(v) ? _.clone(v) : {};
	}

	// Merge ONLY the given keys into the setting — sizes, weights and
	// everything else the user already tuned stay untouched.
	function patchFamily(id, patch) {
		var c = wpcustomize.control(id);
		if (!c) {
			return;
		}
		c.setting.set(c.encodeValue(_.extend({}, decoded(id), patch)));
	}

	function applyPreset(preset) {
		patchFamily(HEADING_SETTING, {
			font: preset.heading.family,
			font_type: "google",
			variant: preset.heading.variants
		});
		patchFamily(BODY_SETTING, {
			font: preset.body.family,
			font_type: "google",
			variant: preset.body.variants
		});
	}

	// "Remove preset": clear the family bits back to Default on both
	// settings; everything else stays.
	function resetPresets() {
		patchFamily(HEADING_SETTING, {
			font: "",
			font_type: "",
			variant: ""
		});
		patchFamily(BODY_SETTING, {
			font: "",
			font_type: "",
			variant: ""
		});
	}

	function presetsFor($li) {
		var id = ($li.attr("id") || "").replace(/^customize-control-/, "");
		var c = id ? wpcustomize.control(id) : null;
		return c && _.isObject(c.params.fields) ? c.params.fields : [];
	}

	// Active card = both settings currently hold that pair's families.
	// Derived live from the settings, so manual picks elsewhere keep the
	// grid honest.
	function paint() {
		var bodyFont = decoded(BODY_SETTING).font;
		var headingFont = decoded(HEADING_SETTING).font;
		$(".customize-control-customify-typography_presets").each(function () {
			var $li = $(this);
			var presets = presetsFor($li);
			$(".customify-typo-preset", $li).each(function () {
				var p = presets[parseInt($(this).attr("data-index"), 10)];
				$(this).toggleClass(
					"is-active",
					!!(
						p &&
						p.heading.family === headingFont &&
						p.body.family === bodyFont
					)
				);
			});
		});
	}

	var init = function () {
		$document.on("click", ".customify-typo-preset", function (e) {
			e.preventDefault();
			var $li = $(this)
				.closest(".customize-control-customify-typography_presets")
				.eq(0);
			var p = presetsFor($li)[
				parseInt($(this).attr("data-index"), 10)
			];
			if (p) {
				applyPreset(p);
			}
		});

		$document.on("click", ".customify-presets-reset", function (e) {
			e.preventDefault();
			resetPresets();
		});

		_.each([BODY_SETTING, HEADING_SETTING], function (sid) {
			wpcustomize(sid, function (setting) {
				setting.bind(paint);
			});
		});
		paint();
	};

	return { init: init };
}
