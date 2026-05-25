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
export function setupTypographyControl(deps) {
	var $ = deps.$;
	var $document = deps.$document;
	var wpcustomize = deps.wpcustomize;
	var customifyField = deps.customifyField;

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
				_.each(options, function (value) {
					var selected = "";
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
				});
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
				// Tag the inner dropdown so the open-event hook below
				// can locate this specific picker's popup (body has
				// other Select2 instances; we mustn't widen them too).
				dropdownCssClass: "customify-font-dropdown"
			});
			// Width + right-alignment of the font picker popup:
			//   - inner .select2-dropdown.customify-font-dropdown → 240px
			//     via CSS rule in customizer.scss
			//   - outer .select2-container--open → width + left set in
			//     JS below so the popup's right edge meets the
			//     trigger's right edge (Select2 defaults to
			//     left-alignment which would spill the wider popup
			//     past the panel's right edge).
			//
			// Why requestAnimationFrame and not sync, and not
			// setTimeout(0):
			//
			//  - Select2 registers TWO handlers on its internal 'open'
			//    event: (a) AttachBody's `_showDropdown` which
			//    appends the popup to <body> and positions it, and
			//    (b) `_tieEventsToOriginalSelect` which fires the
			//    public `select2:open` on the original <select>. They
			//    run in registration order — in practice (b) runs
			//    BEFORE (a) in this build, so a sync select2:open
			//    handler sees the popup not yet attached to the DOM
			//    (returns early, no reposition).
			//
			//  - setTimeout(0) defers to the next task, by which time
			//    the popup IS attached, but the browser has already
			//    painted the popup at Select2's default left-aligned
			//    position. The reposition that follows produces a
			//    visible flash (the original "nháy" bug).
			//
			//  - requestAnimationFrame fires AFTER the current task
			//    (so all sync Select2 work — including the attach in
			//    step (a) — is done) but BEFORE the next paint, so
			//    the browser paints exactly once with our corrected
			//    geometry. No early-return, no flash.
			var POPUP_WIDTH = 200;
			$fontPicker.on("select2:open", function () {
				requestAnimationFrame(function () {
					var $dropdown = $(".select2-dropdown.customify-font-dropdown");
					if (!$dropdown.length) return;
					var $container = $dropdown.closest(".select2-container--open");
					if (!$container.length) return;
					// Visible trigger (closed Select2 container) sits
					// next to the hidden original <select>.
					var $trigger = $fontPicker.next(".select2-container");
					if (!$trigger.length) return;
					var triggerOffset = $trigger.offset();
					var triggerRight = triggerOffset.left + $trigger.outerWidth();
					var newLeft = triggerRight - POPUP_WIDTH;
					if (newLeft < 0) newLeft = 0; // clamp at viewport edge
					$container.css({
						width: POPUP_WIDTH + "px",
						left: newLeft + "px"
					});
				});
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
				$el.attr("data-opening", "opening");
				that.values = $(".customify-typography-input", that.$el).val();
				that.values = JSON.parse(that.values);
				$el.addClass("customify-modal--inside");
				if (!$(".customify-modal-settings", $el).length) {
					var $wrap = $($("#tmpl-customify-modal-settings").html());
					that.container = $wrap;
					that.container.hide();
					this.$el.append($wrap);
					that.ready();
				} else {
					that.container = $(".customify-modal-settings", $el);
					that.container.hide();
				}
				that.container.slideDown(300, function () {
					that.$el.addClass("modal--opening");
					$(".action--reset", that.$el).show();
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
					});
				});
			} else {
				$(".customify-modal-settings", $el).slideUp(300, function () {
					$el.attr("data-opening", "");
					$el.removeClass("modal--opening");
					$(".action--reset", $el).hide();
				});
			}
		},

		reset: function () {
			//this.$el = $el;
			var that = this;
			var $el = that.$el;

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

	var intTypoControls = {};
	var intTypos = function () {
		$document.on(
			"click",
			".customize-control-customify-typography .action--edit, .customize-control-customify-typography .action--reset",
			function () {
				var controlID = $(this).attr("data-control") || "";
				if (_.isUndefined(intTypoControls[controlID])) {
					var c = wpcustomize.control(controlID);
					if (controlID && !_.isUndefined(c)) {
						var m = _.clone(FontSelector);
						m.config = c.params.fields;
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
	};

	return { FontSelector: FontSelector, intTypos: intTypos };
}
