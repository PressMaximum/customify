/**
 * Typography control runtime.
 *
 * Extracted from control.js (was lines ~2790-3240). Lives in its own
 * file so the giant control.js stays browsable, but compiles back into
 * the same webpack bundle via the import in control.js — no PHP
 * enqueue change, no behavioural diff.
 *
 * Factory pattern: closure deps that originated inside the outer IIFE
 * in control.js (`$`, `$document`, `wpcustomize`, `customifyField`) are
 * passed in explicitly instead of being read from window globals. This
 * preserves the original scoping; `customifyField` in particular is a
 * private IIFE variable, not a true global at module-load time.
 *
 * Other refs (`_`, `Customify_Control_Args`, `_wpCustomizeSettings`)
 * are genuine browser globals and read directly.
 */
import { attachPopoverChrome } from "./popover-chrome";

export function setupTypographyControl(deps) {
	var $ = deps.$;
	var $document = deps.$document;
	var wpcustomize = deps.wpcustomize;
	var customifyField = deps.customifyField;

	// ── Trigger value preview ──────────────────────────────────────────
	// The control row renders a select-like trigger (see
	// class-control-typography.php) whose spans preview the saved value:
	// font family on the left, "16px / 700"-style meta on the right.
	// Reads the SAME hidden input the runtime already round-trips —
	// chrome only, no new value plumbing.

	// Resolve a slider sub-value ({value, unit}, or a device map keyed
	// {desktop, tablet, mobile}) to a display string. Previews the
	// desktop value.
	function sliderDisplayValue(v) {
		if (!_.isObject(v)) {
			return "";
		}
		if (_.isObject(v.desktop)) {
			v = v.desktop;
		}
		if (_.isUndefined(v.value) || v.value === null || v.value === "") {
			return "";
		}
		var unit = v.unit || "px";
		// '-' is the unitless sentinel (line-height multiplier).
		if (unit === "-") {
			unit = "";
		}
		return v.value + unit;
	}

	function weightDisplayValue(w) {
		if (!w || w === "default") {
			return "";
		}
		if (w === "regular" || w === "normal") {
			return "400";
		}
		return String(w);
	}

	// Resolve a `display_defaults` entry (string, or a per-device map for
	// device-scoped sub-fields) to the desktop display string.
	function displayDefaultValue(v) {
		if (_.isObject(v)) {
			return v.desktop || "";
		}
		return v || "";
	}

	function controlParams($control) {
		var id = ($control.attr("id") || "").replace(
			/^customize-control-/,
			""
		);
		if (!id) {
			return null;
		}
		var c = wpcustomize.control(id);
		return c ? c.params : null;
	}

	function renderTypoTrigger($control) {
		var $trigger = $(".customify-typo-trigger", $control);
		if (!$trigger.length) {
			return;
		}
		var value = {};
		try {
			value = JSON.parse(
				$(".customify-typography-input", $control).val() || ""
			);
		} catch (e) { }
		if (!_.isObject(value)) {
			value = {};
		}

		// Unset sub-values fall back to display-only metadata: the
		// field's `display_defaults` (the literal CSS fallbacks from
		// _base.scss, declared in configs/typography.php) and, for a
		// gated-off font picker, the "Inherit" label. Purely visual —
		// nothing here is written back.
		var params = controlParams($control) || {};
		var gates = _.isObject(params.fields) ? params.fields : {};
		var dd = _.isObject(params.display_defaults)
			? params.display_defaults
			: {};

		var family;
		if (value.font && _.isString(value.font)) {
			family = value.font;
		} else if (gates.font === false) {
			family = Customify_Control_Args.inherit || "Inherit";
		} else {
			family = Customify_Control_Args.default_label || "Default";
		}

		var meta = [];
		var size =
			sliderDisplayValue(value.font_size) ||
			displayDefaultValue(dd.font_size);
		if (size) {
			meta.push(size);
		}
		// Weight slot only when the field actually offers a weight
		// control — fields that gate it off (h1–h6) would otherwise show
		// a meaningless "/ Inherit" tail.
		if (gates.font_weight !== false) {
			var weight =
				weightDisplayValue(value.font_weight) ||
				displayDefaultValue(dd.font_weight);
			if (weight) {
				meta.push(weight);
			}
		}

		$(".customify-trigger--family", $trigger).text(family);
		$(".customify-trigger--meta", $trigger).text(meta.join(" / "));
	}

	var FontSelector = {
		fonts: null,
		optionHtml: "",
		$el: null,
		values: {},
		config: {}, // Config to disable fields
		container: null,
		fields: {},
		load: function (callback) {
			var that = this;
			$.get(
				Customify_Control_Args.ajax,
				{
					action: "customify/customizer/ajax/fonts",
					wp_customize: "on",
					_nonce: _wpCustomizeSettings.nonce.preview,
					customize_theme: _wpCustomizeSettings.theme.stylesheet
				},
				function (res) {
					if (res.success) {
						that.fonts = res.data;
					}
					if (typeof callback === "function") {
						callback();
					}
				}
			);
		},
		toSelectOptions: function (options, v, type) {
			var html = "";
			if (_.isUndefined(v)) {
				v = "";
			}

			// Google, Library and Theme fonts all ship explicit variant
			// lists (Library + Theme variants are derived from
			// font-face children in to_google_variant()). System fonts
			// fall through to the generic 100..900 weight ladder below.
			if (type === "google" || type === "library" || type === "theme") {
				// Track rendered weight tokens so we can backfill
				// 400 (regular) and 700 (bold) when the font ships
				// with a minimal variant list (e.g. a Library font
				// declaring only `regular`). Browsers synthesise the
				// missing weights via faux-bold, so exposing the
				// option is more useful than locking the user out.
				var rendered = {};
				_.each(options, function (value) {
					var selected = "";
					if (value === v) {
						selected = ' selected="selected" ';
					}
					rendered[String(value).toLowerCase()] = true;
					html +=
						"<option" +
						selected +
						' value="' +
						value +
						'">' +
						value +
						"</option>";
				});

				var hasRegular =
					rendered["400"] ||
					rendered["regular"] ||
					rendered["normal"] ||
					rendered["default"];
				if (!hasRegular) {
					html +=
						"<option" +
						(v === "400" ? ' selected="selected" ' : "") +
						' value="400">400</option>';
				}
				var hasBold = rendered["700"] || rendered["bold"];
				if (!hasBold) {
					html +=
						"<option" +
						(v === "700" ? ' selected="selected" ' : "") +
						' value="700">700</option>';
				}
			} else {
				_.each(Customify_Control_Args.list_font_weight, function (
					value,
					key
				) {
					var selected = "";
					if (value === v) {
						selected = ' selected="selected" ';
					}
					html +=
						"<option" +
						selected +
						' value="' +
						key +
						'">' +
						value +
						"</option>";
				});

				var value, selected, i;

				for (i = 1; i <= 9; i++) {
					value = i * 100;
					selected = "";
					if (value === v) {
						selected = ' selected="selected" ';
					}
					html +=
						"<option" +
						selected +
						' value="' +
						value +
						'">' +
						value +
						"</option>";
				}
			}

			return html;
		},
		toCheckboxes: function (options, v) {
			var html = '<div class="list-subsets">';
			if (!_.isObject(v)) {
				v = {};
			}
			_.each(options, function (value) {
				var checked = "";
				if (!_.isUndefined(v[value])) {
					checked = ' checked="checked" ';
				}
				html +=
					"<p><label><input " +
					checked +
					'type="checkbox" class="customify-typo-input change-by-js" data-name="languages" name="_n-' +
					new Date().getTime() +
					'" value="' +
					value +
					'"> ' +
					value +
					"</label></p>";
			});
			html += "</div>";
			return html;
		},
		ready: function () {
			var that = this;
			customifyField.devices = _.clone(customifyField.allDevices);
			if (!_.isObject(that.values)) {
				that.values = {};
			}

			that.fields = {};

			//Customify_Control_Args.typo_fields
			if (!_.isEmpty(that.config)) {
				_.each(Customify_Control_Args.typo_fields, function (_f, _key) {
					var show = true;
					if (!_.isUndefined(that.config[_f.name])) {
						if (that.config[_f.name] === false) {
							show = false;
						}
					}

					if (show) {
						that.fields[_f.name] = _f;
					}
				});
			} else {
				that.fields = Customify_Control_Args.typo_fields;
			}

			// `languages` visibility is driven by the font picker's
			// change event (setUpFont() toggles it based on font type).
			// If the field config hides the font picker, that event
			// never fires and `languages` would render visible by
			// default — drop it here so the modal stays consistent.
			if (_.isUndefined(that.fields.font)) {
				delete that.fields.languages;
			}

			// Stamp per-control display defaults as input placeholders
			// (display-only — the inputs stay empty, so nothing is saved
			// until the user actually picks a value). Clone each field
			// config first: typo_fields is ONE list shared by every
			// typography control; mutating it would leak placeholders
			// across controls.
			var stamped = _.isArray(that.fields) ? [] : {};
			_.each(that.fields, function (_f, _key) {
				var f = _.clone(_f);
				if (
					_.isObject(that.displayDefaults) &&
					!_.isUndefined(that.displayDefaults[f.name])
				) {
					f.placeholder = that.displayDefaults[f.name];
				}
				stamped[_key] = f;
			});
			that.fields = stamped;

			$(".customify-modal-settings--fields", that.container).append(
				'<input type="hidden" class="customify--font-type">'
			);
			customifyField.addFields(
				that.fields,
				that.values,
				$(".customify-modal-settings--fields", that.container),
				function () {
					that.get();
				}
			);

			$("input, select, textarea", $(".customify-modal-settings--fields"))
				.removeClass("customify-input")
				.addClass("customify-typo-input change-by-js");
			that.optionHtml +=
				'<option value="">' +
				Customify_Control_Args.theme_default +
				"</option>";
			_.each(that.fonts, function (group, type) {
				that.optionHtml += '<optgroup label="' + group.title + '">';
				_.each(group.fonts, function (font, font_name) {
					// Three shapes accepted:
					//   - string          → use as the visible label
					//   - object with .label + ._disabled → placeholder
					//     row (eg. "No fonts activated…" in the WP Font
					//     Library group when nothing is uploaded yet)
					//   - any other object → use font_name as label
					var label, disabled;
					if (_.isString(font)) {
						label = font;
						disabled = false;
					} else if (_.isObject(font) && font.label) {
						label = font.label;
						disabled = !!font._disabled;
					} else {
						label = font_name;
						disabled = false;
					}
					that.optionHtml +=
						'<option value="' +
						font_name +
						'"' +
						(disabled ? " disabled" : "") +
						">" +
						label +
						"</option>";
				});
				that.optionHtml += "</optgroup>";
			});

			$('.customify-typo-input[data-name="font"]', that.container).html(
				that.optionHtml
			);

			if (
				!_.isUndefined(that.values["font"]) &&
				_.isString(that.values["font"])
			) {
				$(
					'.customify-typo-input[data-name="font"] option[value="' +
					that.values["font"] +
					'"]',
					that.container
				).attr("selected", "selected");
			}

			that.container.on(
				"change init-change",
				'.customify-typo-input[data-name="font"]',
				function () {
					var font = $(this).val();
					that.setUpFont(font);
				}
			);

			$(
				'.customify-typo-input[data-name="font"]',
				that.container
			).trigger("init-change");

			var $fontPicker = $(
				'.customify-typo-input[data-name="font"]',
				that.container
			);
			$fontPicker.select2({
				// Tag the inner dropdown so the width/skin rule in
				// customizer.scss can target this picker's popup.
				dropdownCssClass: "customify-font-dropdown",
				// Attach the popup inside the font row's own settings box
				// instead of <body>. Select2's coordinate math is ignored
				// entirely: _control.scss pins the popup wrapper with
				// `top:100% / right:0 !important` relative to this box, so
				// it always hangs right under the trigger and moves with
				// the popover (the old body-attached popup kept stale
				// document coordinates and drifted away from the control).
				dropdownParent: $fontPicker.closest(
					".customify-field-settings-inner"
				)
			});

			// Bind events on inputs
			that.container.on(
				"change data-change",
				"input, select",
				function () {
					that.get();
				}
			);

			// Bind event on container
			that.container.on(
				"container-data-change",
				function () {
					that.get();
				}
			);


		},

		setUpFont: function (font) {
			var that = this;
			var font_settings, variants, subsets, type;

			if (_.isEmpty(font)) {
				type = "normal";
			}

			if (!_.isObject(that.fonts) || _.isEmpty(that.fonts)) {
				that.fonts = {
					theme:   { fonts: {} },
					library: { fonts: {} },
					normal:  { fonts: {} },
					google:  { fonts: {} }
				};
			}

			// Resolve which group the chosen font lives in. Priority
			// matches server-side ajax_fonts(): theme > library > google
			// > normal. Fall through to "normal" so system fonts don't
			// accidentally inherit other groups' variant metadata.
			var fontKey = _.isString(font) ? font : (font && font.font);
			if (fontKey) {
				if (that.fonts.theme && !_.isUndefined(that.fonts.theme.fonts[fontKey])) {
					type = "theme";
					font_settings = that.fonts.theme.fonts[fontKey];
				} else if (that.fonts.library && !_.isUndefined(that.fonts.library.fonts[fontKey])) {
					type = "library";
					font_settings = that.fonts.library.fonts[fontKey];
				} else if (that.fonts.google && !_.isUndefined(that.fonts.google.fonts[fontKey])) {
					type = "google";
					font_settings = that.fonts.google.fonts[fontKey];
				} else if (that.fonts.normal && !_.isUndefined(that.fonts.normal.fonts[fontKey])) {
					type = "normal";
					font_settings = that.fonts.normal.fonts[fontKey];
				}
			} else {
				font_settings = {};
			}

			if (!_.isUndefined(font_settings) && !_.isEmpty(font_settings)) {
				variants = font_settings.variants;
				subsets = font_settings.subsets;
			}

			$(
				'.customify-typo-input[data-name="font_weight"]',
				that.container
			).html(
				that.toSelectOptions(
					variants,
					_.isObject(that.values) ? that.values.font_weight : "",
					type
				)
			);
			$(".customify--font-type", that.container).val(type);

			if (type !== "google") {
				$(
					'.customify--group-field[data-field-name="languages"]',
					that.container
				)
					.addClass("customify--hide")
					.find(".customify-field-settings-inner")
					.html("");
			} else {
				$(
					'.customify--group-field[data-field-name="languages"]',
					that.container
				).removeClass("customify--hide");
				$(
					'.customify--group-field[data-field-name="languages"]',
					that.container
				)
					.removeClass("customify--hide")
					.find(".customify-field-settings-inner")
					.html(
						that.toCheckboxes(
							subsets,
							_.isObject(that.values) ? that.values.languages : ""
						)
					);
			}
		},

		open: function () {
			//this.$el = $el;
			var that = this;
			var $el = that.$el;

			var status = $el.attr("data-opening") || false;
			if (status !== "opening") {
				that.values = $(".customify-typography-input", that.$el).val();
				that.values = JSON.parse(that.values);
				$el.addClass("customify-modal--inside");
				if (!$(".customify-modal-settings", $el).length) {
					var $wrap = $($("#tmpl-customify-modal-settings").html());
					that.container = $wrap;
					this.$el.append($wrap);
					that.ready();
				} else {
					that.container = $(".customify-modal-settings", $el);
				}
				that.openPopover();
				// Re-fetch the font catalogue every time the modal
				// opens so variant lists stay in sync with the
				// wp-admin Font Library — users can add/remove
				// font-face files in another tab while the
				// customizer is open, and the old behaviour cached
				// `that.fonts` once at doc-ready forever. When the
				// fetch returns, re-trigger init-change on the
				// font picker so the font_weight dropdown rebuilds
				// from the fresh variants for the active font.
				that.load(function () {
					$(
						'.customify-typo-input[data-name="font"]',
						that.container
					).trigger("init-change");
					// The variant rebuild can change the popover height —
					// re-measure so a flipped (above-trigger) popover
					// stays anchored to the trigger.
					if ("opening" === that.$el.attr("data-opening")) {
						that.positionPopover();
					}
				});
			} else {
				that.closePopover();
			}
		},

		reset: function () {
			//this.$el = $el;
			var that = this;
			var $el = that.$el;

			// The reset action is only reachable while the popover is
			// open (the button is hidden otherwise) — rebuilding removes
			// the panel, so re-open it afterwards for continuity.
			var wasOpen = "opening" === $el.attr("data-opening");

			$(".customify-modal-settings", $el).remove();
			that.values =
				$(".customify-typography-input", that.$el).attr(
					"data-default"
				) || "{}";
			try {
				that.values = JSON.parse(that.values);
			} catch (e) { }

			$el.addClass("customify-modal--inside");
			if (!$(".customify-modal-settings", $el).length) {
				var $wrap = $($("#tmpl-customify-modal-settings").html());
				that.container = $wrap;
				this.$el.append($wrap);
				that.ready();
			} else {
				that.container = $(".customify-modal-settings", $el);
			}
			that.get();
			if (wasOpen) {
				that.openPopover();
			}
		},

		get: function () {
			var data = {};
			var that = this;
			_.each(this.fields, function (f) {
				if (f.name === "languages") {
					f.type = "checkboxes";
				}
				data[f.name] = customifyField.getValue(
					f,
					$(
						'.customify--group-field[data-field-name="' +
						f.name +
						'"]',
						that.container
					)
				);
			});

			data.variant = {};
			if (data.font) {
				// Variant list follows the source the font came from.
				// Priority matches setUpFont: theme > library > google.
				var src = null;
				if (that.fonts.theme && that.fonts.theme.fonts && that.fonts.theme.fonts[data.font]) {
					src = that.fonts.theme.fonts[data.font];
				} else if (that.fonts.library && that.fonts.library.fonts && that.fonts.library.fonts[data.font]) {
					src = that.fonts.library.fonts[data.font];
				} else if (that.fonts.google && that.fonts.google.fonts && that.fonts.google.fonts[data.font]) {
					src = that.fonts.google.fonts[data.font];
				}
				if (src && src.variants) {
					data.variant = src.variants;
				}
			}

			data.font_type = $(".customify--font-type", that.container).val();
			$(".customify-typography-input", this.$el)
				.val(JSON.stringify(data))
				.trigger("change");
			return data;
		},

		init: function () {
			this.load();
		}
	};

	// Popover lifecycle (open/close/position/dismiss) comes from the
	// shared chrome — one popover at a time across typography AND styling
	// controls. Attached to the base object so the per-control clones
	// made in intTypos() inherit the methods.
	attachPopoverChrome(FontSelector, {
		$: $,
		anchor: function (that) {
			return $(".customify-typo-trigger", that.$el);
		}
	});

	var intTypoControls = {};
	var intTypos = function () {
		$document.on(
			"click",
			".customize-control-customify-typography .action--edit, .customize-control-customify-typography .action--reset",
			function (e) {
				e.preventDefault();
				var controlID = $(this).attr("data-control") || "";
				if (_.isUndefined(intTypoControls[controlID])) {
					var c = wpcustomize.control(controlID);
					if (controlID && !_.isUndefined(c)) {
						var m = _.clone(FontSelector);
						m.config = c.params.fields;
						m.displayDefaults = c.params.display_defaults || {};
						m.$el = $(this)
							.closest(".customize-control-customify-typography")
							.eq(0);
						intTypoControls[controlID] = m;
					}
				}

				if (!_.isUndefined(intTypoControls[controlID])) {
					if ($(this).hasClass("action--reset")) {
						intTypoControls[controlID].reset();
					} else {
						intTypoControls[controlID].open();
					}
				}
			}
		);

		// Trigger value preview: every value write round-trips through
		// the hidden input with a change/data-change event (user edits,
		// reset, live updates) — re-render the trigger summary there.
		$document.on(
			"change data-change",
			".customify-typography-input",
			function () {
				renderTypoTrigger(
					$(this).closest(".customize-control-customify-typography")
				);
			}
		);

		// Re-render after a programmatic control repaint (external
		// setting write → refreshFromSetting in control.js).
		$document.on(
			"customify/control/refreshed",
			".customize-control-customify-typography",
			function () {
				renderTypoTrigger($(this));
			}
		);

		// First paint. All customify controls are batch-initialized at
		// document.ready in control.js BEFORE intTypos() runs, so every
		// typography control's hidden input + trigger exist by now.
		$(".customize-control-customify-typography").each(function () {
			renderTypoTrigger($(this));
		});
	};

	return { FontSelector: FontSelector, intTypos: intTypos };
}
