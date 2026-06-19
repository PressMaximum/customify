/**
 * Shared popover chrome — the open/close/position/dismiss lifecycle for
 * composite controls (typography, styling) whose settings panel floats
 * over the controls below instead of expanding inline.
 *
 * Extracted verbatim from the Phase-1 typography runtime so the styling
 * chrome reuses it instead of carrying a third copy of the modal code.
 *
 * attachPopoverChrome() mixes the methods into a runtime BASE object
 * (clones made afterwards inherit them). The runtime must expose:
 *   $el        – the control <li>: the positioning context, carries the
 *                `customify-modal--inside` / `modal--opening` classes and
 *                the `data-opening` state attribute
 *   container  – the .customify-modal-settings jQuery node
 * and opts provides:
 *   $          – jQuery
 *   anchor     – function(runtime) -> jQuery of the trigger the popover
 *                anchors to. Resolved at position time, so runtimes with
 *                several triggers (styling tabs) return the active row.
 *   onClose    – optional hook fired after the popover closes (e.g. to
 *                clear a row's is-open state)
 */

// Only one popover may be open at a time — ACROSS control types. The
// module-scoped ref is shared by every runtime this factory touches, so
// opening a styling popover closes an open typography one and vice versa.
var activePopover = null;

export function attachPopoverChrome(runtime, opts) {
	var $ = opts.$;

	// The settings panel floats over the controls below (absolute within
	// the control li) instead of the legacy inline accordion. Open/close
	// is class-driven (`is-open`) so the CSS transition in _control.scss
	// animates opacity/transform; jQuery slide* would jump because it
	// animates height inline.
	runtime.openPopover = function () {
		var that = this;
		if (activePopover && activePopover !== that) {
			activePopover.closePopover();
		}
		activePopover = that;

		that.$el.attr("data-opening", "opening");
		that.$el.addClass("modal--opening");
		$(".action--reset", that.$el).show();
		that.positionPopover();

		// A freshly-appended panel needs its initial (hidden) styles
		// committed before the class flips, or the transition jumps
		// straight to the final state. Force a synchronous reflow
		// instead of requestAnimationFrame: rAF never fires while the
		// tab is hidden (background window / tab switch), so the class
		// would land arbitrarily late — even after a closePopover() —
		// resurrecting a dismissed popover.
		if (that.container[0]) {
			void that.container[0].offsetWidth;
		}
		that.container.addClass("is-open");
		that.bindDismiss();
	};

	runtime.closePopover = function () {
		var that = this;
		var $el = that.$el;
		if (activePopover === that) {
			activePopover = null;
		}
		that.unbindDismiss();
		$el.attr("data-opening", "");
		$el.removeClass("modal--opening");
		$(".action--reset", $el).hide();
		if (that.container) {
			that.container.removeClass("is-open");
		}
		if (typeof opts.onClose === "function") {
			opts.onClose(that);
		}
	};

	// Anchor the popover right under the trigger (or above it when the
	// viewport space below is too small). `top` is relative to the
	// control li — the positioning context set by the
	// `customify-modal--inside` class.
	runtime.positionPopover = function () {
		var that = this;
		var $trigger = opts.anchor(that);
		if (
			!$trigger ||
			!$trigger.length ||
			!that.container ||
			!that.container.length
		) {
			return;
		}
		var GAP = 8;
		var liRect = that.$el[0].getBoundingClientRect();
		var tRect = $trigger[0].getBoundingClientRect();
		var popH = that.container.outerHeight();
		var spaceBelow = window.innerHeight - tRect.bottom;
		var top;
		if (spaceBelow < popH + 24 && tRect.top - popH - 24 > 0) {
			top = tRect.top - liRect.top - popH - GAP;
			that.container.addClass("is-above");
		} else {
			top = tRect.bottom - liRect.top + GAP;
			that.container.removeClass("is-above");
		}
		that.container.css("top", Math.round(top) + "px");
	};

	// Close on outside click / ESC while open. Native capture-phase
	// listeners: outside-click must win over handlers that
	// stopPropagation, and ESC must fire before the Customizer's own
	// document-level ESC (which would also collapse the section).
	runtime.bindDismiss = function () {
		var that = this;
		that.unbindDismiss();
		that._onOutside = function (e) {
			if ($(e.target).closest(that.$el[0]).length) {
				return;
			}
			// Select2 renders its dropdown at <body> level — clicks
			// there belong to the popover even though they land
			// outside the control li.
			if (
				$(e.target).closest(
					".select2-container, .select2-dropdown"
				).length
			) {
				return;
			}
			that.closePopover();
		};
		that._onEsc = function (e) {
			if ("Escape" === e.key || 27 === e.keyCode) {
				e.stopPropagation();
				that.closePopover();
			}
		};
		// Clicks inside the preview iframe never reach this document's
		// mousedown listener — but focusing the iframe blurs the top
		// window, so treat window blur as an outside click.
		that._onBlur = function () {
			that.closePopover();
		};
		document.addEventListener("mousedown", that._onOutside, true);
		document.addEventListener("keydown", that._onEsc, true);
		window.addEventListener("blur", that._onBlur);
	};

	runtime.unbindDismiss = function () {
		var that = this;
		if (that._onOutside) {
			document.removeEventListener(
				"mousedown",
				that._onOutside,
				true
			);
			that._onOutside = null;
		}
		if (that._onEsc) {
			document.removeEventListener("keydown", that._onEsc, true);
			that._onEsc = null;
		}
		if (that._onBlur) {
			window.removeEventListener("blur", that._onBlur);
			that._onBlur = null;
		}
	};
}
