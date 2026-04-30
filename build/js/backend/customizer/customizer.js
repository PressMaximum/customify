/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/backend/customizer/js/customizer.js":
/*!*************************************************!*\
  !*** ./src/backend/customizer/js/customizer.js ***!
  \*************************************************/
/***/ (function() {

/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

(function ($, api) {
  var $document = $(document);

  /**
   * Dispatch Event
   *
   *
   * @param eventName
   * @param options
   */
  var dispatchEvent = function (element, eventName, options) {
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
  var header_changed = function (partial_id, remove_items) {
    if (_.isUndefined(remove_items)) {
      remove_items = false;
    }
    //console.log( 'partial_id', partial_id );
    if (partial_id === "header_builder_panel" || partial_id === "customify_customize_render_header") {
      var is_drop_down = $("body").hasClass("menu_sidebar_dropdown");
      $(".close-sidebar-panel").not(":last").remove();
      if (!is_drop_down) {
        $(".header-menu-sidebar").not(":last").remove();
      }
      if (remove_items) {
        $("body > .header-menu-sidebar, #page > .header-menu-sidebar").remove();
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
      search_form.addClass("mobile-search-form-sidebar").removeClass("hide-on-mobile hide-on-tablet");
      $("body").prepend(search_form);
    }
    $document.trigger("header_builder_panel_changed", [partial_id]);
    /**
     * @since 0.2.6 Add Vanila JS dispatch event.
     */
    dispatchEvent(document, "header_builder_panel_changed", {
      bubbles: true,
      detail: {
        partial_id: partial_id
      }
    });
  };

  // Header text color.
  wp.customize("header_textcolor", function (settings) {
    settings.bind(function (to) {
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
  wp.customize("header_sidebar_animate", function (settings) {
    settings.bind(function (to) {
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
        $(".menu-mobile-toggle, .menu-mobile-toggle .hamburger").addClass("is-active");
      } else {
        $(".menu-mobile-toggle, .menu-mobile-toggle .hamburger").removeClass("is-active");
      }
    });
  });
  api.bind("preview-ready", function () {
    var defaultTarget = window.parent === window ? null : window.parent;

    // When focus section
    defaultTarget.wp.customize.state("expandedSection").bind(function (section) {
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
        $document.trigger("customize_section_opened", ["__no_section_selected"]);
        /**
         * @since 0.2.6 Add Vanila JS dispatch event.
         */
        dispatchEvent(document, "customize_section_opened", {
          bubbles: true,
          detail: "__no_section_selected"
        });
      }
    });
    $document.on("click", "#masthead .customize-partial-edit-shortcut-header_panel", function (e) {
      e.preventDefault();
      defaultTarget.wp.customize.panel("header_settings").focus();
    });

    // for custom when click on preview
    $document.on("click", ".builder-item-focus .item--preview-name", function (e) {
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
    });

    // When selective refresh re-rendered content
    wp.customize.selectiveRefresh.bind("partial-content-rendered", function (settings) {
      $document.trigger("selective-refresh-content-rendered", [settings.partial.id]);
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
      $(".customify-grid .has_menu.builder-item-focus").each(function () {
        var parentPos = $(this).closest(".customify-grid").offset();
        var childPos = $(this).offset();
        var h = $(this).innerHeight();
        var top = childPos.top - parentPos.top;
        $(this).find(".item--preview-name").css({
          top: top + h
        });
      });
    }
    setupPreviewNamePosition();
    $document.on("selective-refresh-content-rendered  after_auto_render_css", function (event, id, field_name) {
      setupPreviewNamePosition();
    });
  });
  var skips_to_add_shortcut = {
    customify_customize_render_header: 1,
    customify_customize_render_footer: 1
  };

  /**
   * Do not focus to header, footer customize control
   * @see /wp-includes/js/customize-selective-refresh.js
   */
  wp.customize.selectiveRefresh.Partial.prototype.ready = function () {
    var partial = this;
    if (_.isUndefined(skips_to_add_shortcut[partial.id])) {
      _.each(partial.placements(), function (placement) {
        $(placement.container).attr("title", wp.customize.selectiveRefresh.data.l10n.shiftClickToEdit);
        partial.createEditShortcutForPlacement(placement);
      });
      $(document).on("click", partial.params.selector, function (e) {
        if (!e.shiftKey) {
          return;
        }
        e.preventDefault();
        _.each(partial.placements(), function (placement) {
          if ($(placement.container).is(e.currentTarget)) {
            partial.showControl();
          }
        });
      });
    }
  };
  // Live preview for footer row col_layout (postMessage transport).
  function applyFooterColLayout(rowSelector, valueStr) {
    var data;
    try {
      data = typeof valueStr === 'string' ? JSON.parse(valueStr) : valueStr;
    } catch (e) {
      return;
    }
    if (!data || typeof data !== 'object') {
      return;
    }
    var styleId = 'customify-footer-col-layout-' + rowSelector.replace(/[^a-z0-9]/g, '-');
    var styleEl = document.getElementById(styleId);
    if (!styleEl) {
      styleEl = document.createElement('style');
      styleEl.id = styleId;
      document.head.appendChild(styleEl);
    }
    var breakpoints = {
      desktop: '',
      tablet: '(max-width: 1024px)',
      mobile: '(max-width: 767px)'
    };
    var css = '';
    Object.keys(breakpoints).forEach(function (device) {
      var d = data[device];
      if (!d || !Array.isArray(d.fr) || !d.fr.length) {
        return;
      }
      var cols = d.fr.map(function (v) {
        return parseInt(v, 10) + 'fr';
      }).join(' ');
      var gap = parseInt(d.gap, 10) || 0;
      var padding = parseInt(d.padding, 10) || 0;
      var rules = rowSelector + ' .row-v2 { display: grid !important; grid-template-columns: ' + cols + '; column-gap: ' + gap + 'px; }';
      rules += ' ' + rowSelector + ' .col-v2 { padding-left: ' + padding + 'px; padding-right: ' + padding + 'px; }';
      css += breakpoints[device] ? '@media ' + breakpoints[device] + ' { ' + rules + ' } ' : rules + ' ';
    });
    styleEl.textContent = css;
  }
  wp.customize('footer_main_col_layout', function (setting) {
    setting.bind(function (value) {
      applyFooterColLayout('#cb-row--footer-main', value);
    });
  });
  wp.customize('footer_bottom_col_layout', function (setting) {
    setting.bind(function (value) {
      applyFooterColLayout('#cb-row--footer-bottom', value);
    });
  });
})(jQuery, wp.customize);

/***/ }),

/***/ "./src/backend/customizer/preview-colors/preview.js":
/*!**********************************************************!*\
  !*** ./src/backend/customizer/preview-colors/preview.js ***!
  \**********************************************************/
/***/ (function() {

/*
 * Customify Preview Colors — Customizer preview-iframe live update.
 *
 * Runs INSIDE the Customizer's preview iframe. With the bound settings on
 * `transport: 'postMessage'`, every time the user touches the panel in the
 * controls pane (preset switch / slot edit), wp.customize fires a `change`
 * for the matching setting; this script catches it and rewrites the
 * `<style id="customify-preview-colors-vars">:root{ … }</style>` block —
 * same shape as what PHP's `output_root_vars()` emits on a normal page load,
 * so the override layer compiled into style-theme.css picks up the new vars
 * without an iframe refresh.
 *
 * No persistence here. Saving still happens through the standard Customizer
 * Publish flow on the controls side; this bundle only paints the preview.
 */
(function () {
  'use strict';

  if (!window.wp || !wp.customize) return;
  var cfg = window.CustomifyPreviewColorsPreview || {};
  var STYLE_ID = cfg.styleId || 'customify-preview-colors-vars';
  var SETTING_ACTIVE = cfg.settingIds && cfg.settingIds.active || 'customify_preview_active_palette';
  var SETTING_PALETTES = cfg.settingIds && cfg.settingIds.palettes || 'customify_preview_user_palettes';
  var SLOTS = cfg.slots || ['base', 'text', 'primary', 'secondary', 'accent', 'surface'];
  var THEME_PALETTES = cfg.themePresets || [];

  // ---------------------------------------------------------------- helpers

  function hexToRgbArray(hex) {
    var c = String(hex || '').replace('#', '');
    if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    if (!/^[0-9A-Fa-f]{6}$/.test(c)) return null;
    return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)];
  }
  function hexToRgb(hex) {
    var rgb = hexToRgbArray(hex);
    return rgb ? rgb.join(', ') : null;
  }
  function luminance(rgb) {
    var f = function (v) {
      v /= 255;
      return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    };
    return 0.2126 * f(rgb[0]) + 0.7152 * f(rgb[1]) + 0.0722 * f(rgb[2]);
  }
  function pickOn(hex) {
    var rgb = hexToRgbArray(hex);
    if (!rgb) return '#FFFFFF';
    return luminance(rgb) > 0.45 ? '#1A1A1A' : '#FFFFFF';
  }
  function ensureStyleEl() {
    var el = document.getElementById(STYLE_ID);
    if (el) return el;
    el = document.createElement('style');
    el.id = STYLE_ID;
    document.head.appendChild(el);
    return el;
  }

  // Build the `:root { … }` block. Mirrors output_root_vars() in PHP.
  function buildCss(palette) {
    if (!palette || !palette.colors) return '';
    var decls = '';
    for (var i = 0; i < SLOTS.length; i++) {
      var slot = SLOTS[i];
      var hex = palette.colors[slot];
      if (!hex) continue;
      decls += '--customify-color-' + slot + ':' + hex + ';';
    }
    if (palette.colors.primary) decls += '--customify-color-on-primary:' + pickOn(palette.colors.primary) + ';';
    if (palette.colors.secondary) decls += '--customify-color-on-secondary:' + pickOn(palette.colors.secondary) + ';';
    if (palette.colors.surface) decls += '--customify-color-on-surface:' + pickOn(palette.colors.surface) + ';';
    if (palette.colors.text) {
      var t = palette.colors.text;
      decls += '--customify-color-text-muted:color-mix(in srgb, ' + t + ' 55%, transparent);';
      decls += '--customify-color-text-subtle:color-mix(in srgb, ' + t + ' 35%, transparent);';
      decls += '--customify-color-border-default:color-mix(in srgb, ' + t + ' 12%, transparent);';
    }
    if (palette.colors.primary) {
      decls += '--customify-color-primary-hover:color-mix(in srgb, ' + palette.colors.primary + ', #000 15%);';
      if (palette.colors.base) {
        decls += '--customify-color-primary-subtle:color-mix(in srgb, ' + palette.colors.primary + ', ' + palette.colors.base + ' 92%);';
      }
    }
    return ':root{' + decls + '}';
  }
  function getCurrentPalettes() {
    var raw = wp.customize(SETTING_PALETTES);
    var userPalettes = raw && typeof raw.get === 'function' ? raw.get() : [];
    if (!Array.isArray(userPalettes)) userPalettes = [];
    return THEME_PALETTES.concat(userPalettes);
  }
  function getActivePalette() {
    var activeSetting = wp.customize(SETTING_ACTIVE);
    var activeId = activeSetting && typeof activeSetting.get === 'function' ? activeSetting.get() : '';
    var all = getCurrentPalettes();
    for (var i = 0; i < all.length; i++) {
      if (all[i] && all[i].id === activeId) return all[i];
    }
    return all[0] || null;
  }
  function refresh() {
    var pal = getActivePalette();
    if (!pal) return;
    ensureStyleEl().textContent = buildCss(pal);
  }
  wp.customize(SETTING_ACTIVE, function (setting) {
    setting.bind(refresh);
  });
  wp.customize(SETTING_PALETTES, function (setting) {
    setting.bind(refresh);
  });
})();

/***/ }),

/***/ "./src/backend/customizer/scss/customizer.scss":
/*!*****************************************************!*\
  !*** ./src/backend/customizer/scss/customizer.scss ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
!function() {
"use strict";
/*!**********************************************!*\
  !*** ./src/backend/customizer/customizer.js ***!
  \**********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _scss_customizer_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./scss/customizer.scss */ "./src/backend/customizer/scss/customizer.scss");
/* harmony import */ var _js_customizer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./js/customizer.js */ "./src/backend/customizer/js/customizer.js");
/* harmony import */ var _js_customizer_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_js_customizer_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _preview_colors_preview_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./preview-colors/preview.js */ "./src/backend/customizer/preview-colors/preview.js");
/* harmony import */ var _preview_colors_preview_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_preview_colors_preview_js__WEBPACK_IMPORTED_MODULE_2__);


// Preview-colors live update — same iframe context (customize-preview),
// merged here so the iframe loads one combined bundle instead of two.

}();
/******/ })()
;
//# sourceMappingURL=customizer.js.map