/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

(function($, api) {
	var $document = $(document);

	/**
	 * Dispatch Event
	 *
	 *
	 * @param eventName
	 * @param options
	 */
	var dispatchEvent = function(element, eventName, options) {
		var event;

		/**
		 * https://stackoverflow.com/questions/2490825/how-to-trigger-event-in-javascript
		 */
		if (window.CustomEvent) {
			event = new CustomEvent(eventName, options);
		} else {
			event = document.createEvent("CustomEvent");
			event.initCustomEvent(eventName, true, true, options);
		}

		element.dispatchEvent(event);
	};

	var header_changed = function(partial_id, remove_items) {
		if (_.isUndefined(remove_items)) {
			remove_items = false;
		}
		//console.log( 'partial_id', partial_id );
		if (
			partial_id === "header_builder_panel" ||
			partial_id === "customify_customize_render_header"
		) {
			var is_drop_down = $("body").hasClass("menu_sidebar_dropdown");

			$(".close-sidebar-panel")
				.not(":last")
				.remove();
			if (!is_drop_down) {
				$(".header-menu-sidebar")
					.not(":last")
					.remove();
			}

			if (remove_items) {
				$(
					"body > .header-menu-sidebar, #page > .header-menu-sidebar"
				).remove();
			}

			if (is_drop_down) {
				$("#masthead").append($("#header-menu-sidebar"));
				if ($("body").hasClass("is-menu-sidebar")) {
					$("#header-menu-sidebar").css({
						display: "block",
						height: "auto"
					});
				}
			} else {
				$("body").prepend($("#header-menu-sidebar"));
			}
		}

		var header = $("#masthead");
		if ($(".search-form--mobile", header).length) {
			if (remove_items) {
				$(".mobile-search-form-sidebar").remove();
			}
			var search_form = $(".search-form--mobile").eq(0);
			search_form
				.addClass("mobile-search-form-sidebar")
				.removeClass("hide-on-mobile hide-on-tablet");
			$("body").prepend(search_form);
		}

		$document.trigger("header_builder_panel_changed", [partial_id]);
		/**
		 * @since 0.2.6 Add Vanila JS dispatch event.
		 */
		dispatchEvent(document, "header_builder_panel_changed", {
			bubbles: true,
			detail: { partial_id: partial_id }
		});
	};

	// Header text color.
	wp.customize("header_textcolor", function(settings) {
		settings.bind(function(to) {
			if ("blank" === to) {
				$(".site-title, .site-description").css({
					clip: "rect(1px, 1px, 1px, 1px)",
					position: "absolute"
				});
			} else {
				$(".site-title, .site-description").css({
					clip: "auto",
					position: "relative"
				});
				$(".site-title a, .site-description").css({
					color: to
				});
			}
		});
	});

	wp.customize("header_sidebar_animate", function(settings) {
		settings.bind(function(to) {
			header_changed("header_builder_panel", false);
			$document.trigger("customize_section_opened", ["header_sidebar"]);
			/**
			 * @since 0.2.6 Add Vanila JS dispatch event.
			 */
			dispatchEvent(document, "customize_section_opened", {
				bubbles: true,
				detail: 'header_sidebar'
			});

			if (to.indexOf("menu_sidebar_dropdown") > 1) {
				$(
					".menu-mobile-toggle, .menu-mobile-toggle .hamburger"
				).addClass("is-active");
			} else {
				$(
					".menu-mobile-toggle, .menu-mobile-toggle .hamburger"
				).removeClass("is-active");
			}
		});
	});

	api.bind("preview-ready", function() {
		var defaultTarget = window.parent === window ? null : window.parent;

		// When focus section
		defaultTarget.wp.customize
			.state("expandedSection")
			.bind(function(section) {
				if (section && !_.isUndefined(section.id)) {
					$document.trigger("customize_section_opened", [section.id]);
					/**
					 * @since 0.2.6 Add Vanila JS dispatch event.
					 */
					dispatchEvent(document, "customize_section_opened", {
						bubbles: true,
						detail: section.id
					});
				} else {
					$document.trigger("customize_section_opened", [
						"__no_section_selected"
					]);
					/**
					 * @since 0.2.6 Add Vanila JS dispatch event.
					 */
					dispatchEvent(document, "customize_section_opened", {
						bubbles: true,
						detail: "__no_section_selected"
					});
				}
			});

		$document.on(
			"click",
			"#masthead .customize-partial-edit-shortcut-header_panel",
			function(e) {
				e.preventDefault();
				defaultTarget.wp.customize.panel("header_settings").focus();
			}
		);

		// for custom when click on preview
		$document.on(
			"click",
			".builder-item-focus .item--preview-name",
			function(e) {
				e.preventDefault();
				var p = $(this).closest(".builder-item-focus");
				var section_id = p.attr("data-section") || "";
				if (section_id) {
					// Use builder's openSection if available — it properly unbinds
					// permanentlyHideSection before activating the section.
					if (typeof defaultTarget.customifyBuilderOpenSection === "function") {
						defaultTarget.customifyBuilderOpenSection(section_id);
					} else if (defaultTarget.wp.customize.section(section_id)) {
						defaultTarget.wp.customize.section(section_id).focus();
					}
				}
			}
		);

		// When selective refresh re-rendered content
		wp.customize.selectiveRefresh.bind("partial-content-rendered", function(
			settings
		) {
			$document.trigger("selective-refresh-content-rendered", [
				settings.partial.id
			]);
			/**
			 * @since 0.2.6 Add Vanila JS dispatch event.
			 */
			dispatchEvent(document, "selective-refresh-content-rendered", {
				bubbles: true,
				detail: settings.partial.id
			});
			header_changed(settings.partial.id);
		});

		function setupPreviewNamePosition() {
			$(".customify-grid .has_menu.builder-item-focus").each(function() {
				var parentPos = $(this)
					.closest(".customify-grid")
					.offset();
				var childPos = $(this).offset();
				var h = $(this).innerHeight();
				var top = childPos.top - parentPos.top;
				$(this)
					.find(".item--preview-name")
					.css({ top: top + h });
			});
		}

		setupPreviewNamePosition();

		$document.on(
			"selective-refresh-content-rendered  after_auto_render_css",
			function(event, id, field_name) {
				setupPreviewNamePosition();
			}
		);
	});

	var skips_to_add_shortcut = {
		customify_customize_render_header: 1,
		customify_customize_render_footer: 1
	};

	/**
	 * Do not focus to header, footer customize control
	 * @see /wp-includes/js/customize-selective-refresh.js
	 */
	wp.customize.selectiveRefresh.Partial.prototype.ready = function() {
		var partial = this;

		if (_.isUndefined(skips_to_add_shortcut[partial.id])) {
			_.each(partial.placements(), function(placement) {
				$(placement.container).attr(
					"title",
					wp.customize.selectiveRefresh.data.l10n.shiftClickToEdit
				);
				partial.createEditShortcutForPlacement(placement);
			});
			$(document).on("click", partial.params.selector, function(e) {
				if (!e.shiftKey) {
					return;
				}
				e.preventDefault();
				_.each(partial.placements(), function(placement) {
					if ($(placement.container).is(e.currentTarget)) {
						partial.showControl();
					}
				});
			});
		}
	};

	// Builder item sections (logo, copyright, menus, …) are kept inactive by
	// `_customifyForceHide` so they only surface via the gear icon on the
	// builder UI. WP's default partial.showControl() → control.focus() path
	// re-activates the section, but the force-hide binding flips it back to
	// false before the expand can complete — so the pencil edit-shortcut
	// silently does nothing. Route those clicks through
	// customifyBuilderOpenSection (parent window) which unbinds the marker,
	// expands the section, and re-installs the deferred re-hide listener;
	// then focus the matching control so the sidebar scrolls to it.
	var defaultShowControl = wp.customize.selectiveRefresh.Partial.prototype.showControl;
	wp.customize.selectiveRefresh.Partial.prototype.showControl = function() {
		var partial    = this;
		var parentWin  = (window.parent && window.parent !== window) ? window.parent : null;
		var settingIds = (typeof partial.settings === "function") ? partial.settings() : [];
		var settingId  = settingIds && settingIds.length ? settingIds[0] : null;

		if (
			settingId &&
			parentWin &&
			parentWin.wp &&
			parentWin.wp.customize &&
			typeof parentWin.customifyBuilderOpenSection === "function"
		) {
			var matchedControl;
			parentWin.wp.customize.control.each(function(control) {
				if (matchedControl) return;
				var settings = control.settings || {};
				for (var key in settings) {
					if (
						Object.prototype.hasOwnProperty.call(settings, key) &&
						settings[key] &&
						settings[key].id === settingId
					) {
						matchedControl = control;
						break;
					}
				}
			});
			if (matchedControl) {
				var sectionId = (typeof matchedControl.section === "function")
					? matchedControl.section()
					: matchedControl.section;
				var section   = sectionId && parentWin.wp.customize.section(sectionId);
				if (section && section._customifyForceHide) {
					parentWin.customifyBuilderOpenSection(sectionId);
					// Wait out the section's slide-down before asking WP to
					// scroll the sidebar to the specific control — focusing
					// mid-transition leaves the control off-screen.
					setTimeout(function() {
						var c = parentWin.wp.customize.control(matchedControl.id);
						if (c && typeof c.focus === "function") {
							c.focus();
						}
					}, 350);
					return;
				}
			} else {
				// No instantiated control matched. This is the common case for a
				// widget partial (e.g. widget[nav_menu-2]): WP creates widget form
				// controls lazily, and the footer/header sidebar section that holds
				// them is force-hidden — so control.each above finds nothing and
				// the pencil would silently no-op (the widget-title edit shortcut
				// "does nothing" while the sibling nav_menu_instance shortcut still
				// opens the Menus panel). Derive the containing sidebar section from
				// the widget→sidebar map and open it via the builder, then scroll to
				// that section's widgets-area control.
				var widgetMatch = /^widget_(.+)\[(\d+)\]$/.exec(settingId);
				if (widgetMatch) {
					var widgetId = widgetMatch[1] + "-" + widgetMatch[2];
					var sidebarId;
					parentWin.wp.customize.each(function(setting) {
						if (sidebarId) return;
						var sidebarMatch = /^sidebars_widgets\[(.+)\]$/.exec(setting.id);
						if (
							sidebarMatch &&
							Array.isArray(setting.get()) &&
							setting.get().indexOf(widgetId) !== -1
						) {
							sidebarId = sidebarMatch[1];
						}
					});
					var sbSectionId = sidebarId ? "sidebar-widgets-" + sidebarId : null;
					var sbSection   = sbSectionId && parentWin.wp.customize.section(sbSectionId);
					if (sbSection && sbSection._customifyForceHide) {
						parentWin.customifyBuilderOpenSection(sbSectionId);
						setTimeout(function() {
							var wc = parentWin.wp.customize.control(
								"sidebars_widgets[" + sidebarId + "]"
							);
							if (wc && typeof wc.focus === "function") {
								wc.focus();
							}
						}, 350);
						return;
					}
				}
			}
		}

		return defaultShowControl.apply(partial, arguments);
	};
	// Live preview for footer row col_layout (postMessage transport).
	function applyFooterColLayout(rowSelector, valueStr) {
		var data;
		try {
			data = typeof valueStr === 'string' ? JSON.parse(valueStr) : valueStr;
		} catch (e) { return; }
		if (!data || typeof data !== 'object') { return; }

		var styleId = 'customify-footer-col-layout-' + rowSelector.replace(/[^a-z0-9]/g, '-');
		var styleEl = document.getElementById(styleId);
		if (!styleEl) {
			styleEl = document.createElement('style');
			styleEl.id = styleId;
			document.head.appendChild(styleEl);
		}

		// Breakpoints match Customify_Customizer_Auto_CSS::$media_queries
		// and PHP customify_footer_row_layout_css() so the live-preview
		// CSS is byte-equivalent to what ships on the published frontend.
		var breakpoints = { desktop: '', tablet: '(max-width: 1024px)', mobile: '(max-width: 568px)' };
		// Normalize fr length to row's column count. Matches the PHP
		// `customify_footer_row_layout_css()` defensive truncation so legacy
		// data (fr longer than count after a count reduction) renders the
		// same on customizer preview as it does on the published frontend.
		var rawCount = parseInt(data.count, 10);
		var count    = ( rawCount >= 1 && rawCount <= 5 ) ? rawCount : 0;
		var css = '';

		// Hide column placeholders beyond the active count. The HTML in the
		// preview iframe was rendered server-side with the previous count, so
		// when the user shrinks count via postMessage the stale col4/col5
		// divs would otherwise wrap onto a new grid row below. PHP mirrors
		// this exact rule for non-customizer page loads as a defensive guard.
		var ALL_COL_SLOTS = ['left', 'center', 'right', 'col4', 'col5'];
		if (count > 0 && count < ALL_COL_SLOTS.length) {
			var hideSelectors = ALL_COL_SLOTS.slice(count).map(function(c) {
				return rowSelector + ' .col-v2-' + c;
			});
			css += hideSelectors.join(', ') + ' { display: none !important; } ';
		}
		Object.keys(breakpoints).forEach(function(device) {
			var d = data[device];
			if (!d || !Array.isArray(d.fr) || !d.fr.length) {
				// Mobile fallback: emit a stacked rule so phones don't inherit
				// the tablet/desktop multi-column track when the user never
				// configured mobile. Fires ONLY for missing / null / empty
				// data — any explicit array (even [1,1,1,1] that matches
				// desktop) is treated as the user's choice and rendered below.
				if (device === 'mobile') {
					var dm       = data.mobile || {};
					var gapM     = parseInt(dm.gap, 10) || 0;
					var paddingM = parseInt(dm.padding, 10) || 0;
					var rulesM   = rowSelector + ' .row-v2 { display: grid !important; grid-template-columns: 1fr; column-gap: ' + gapM + 'px; }';
					rulesM      += ' ' + rowSelector + ' .col-v2 { padding-left: ' + paddingM + 'px; padding-right: ' + paddingM + 'px; }';
					css += '@media ' + breakpoints.mobile + ' { ' + rulesM + ' } ';
				}
				return;
			}
			var fr;
			var frLen = d.fr.length;
			// Preserve fr as-is when it represents an intentional layout:
			//   length 1            = stacked preset
			//   length == count     = normal one-row layout
			//   length divides count = wrap preset (e.g. fr=[1,1] with count=4
			//                          → 2×2 grid via grid auto-flow)
			//   mobile device       = respect user's explicit mobile choice
			// Other lengths are legacy stale data — slice/pad to count.
			var isIntentional = frLen === 1
				|| frLen === count
				|| ( frLen > 1 && count > 0 && frLen < count && count % frLen === 0 );
			if (isIntentional || device === 'mobile') {
				fr = d.fr.slice();
			} else {
				fr = count > 0 ? d.fr.slice(0, count) : d.fr.slice();
				if (count > 0) {
					while (fr.length < count) { fr.push(1); }
				}
			}
			var cols    = fr.map(function(v) { return parseInt(v, 10) + 'fr'; }).join(' ');
			var gap     = parseInt(d.gap, 10) || 0;
			var padding = parseInt(d.padding, 10) || 0;
			var rules   = rowSelector + ' .row-v2 { display: grid !important; grid-template-columns: ' + cols + '; column-gap: ' + gap + 'px; }';
			rules      += ' ' + rowSelector + ' .col-v2 { padding-left: ' + padding + 'px; padding-right: ' + padding + 'px; }';
			css += breakpoints[device]
				? '@media ' + breakpoints[device] + ' { ' + rules + ' } '
				: rules + ' ';
		});
		styleEl.textContent = css;
	}

	// Bind a live-preview handler for every footer row's col_layout
	// setting. The row list is sourced from Customify_Preview_Config
	// (PHP customify_get_footer_row_ids()), so Pro / child-theme / 3rd-
	// party rows added via the `customify/builder/footer/rows` filter
	// pick up the same live-update treatment without further JS changes.
	var __footerRows = ( window.Customify_Preview_Config && window.Customify_Preview_Config.footer_rows )
		|| [ 'main', 'bottom' ];
	__footerRows.forEach( function ( rowId ) {
		wp.customize( 'footer_' + rowId + '_col_layout', function ( setting ) {
			setting.bind( function ( value ) {
				applyFooterColLayout( '#cb-row--footer-' + rowId, value );
			} );
		} );
	} );
})(jQuery, wp.customize);
