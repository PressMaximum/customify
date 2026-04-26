/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 296:
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

/***/ 623:
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
  var DARK_BASELINES = cfg.darkBaselines || {
    scss: {},
    hex: {}
  };
  var LEGACY_MODS = cfg.legacyMods || {};

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

  // HSL helpers + dark-slot derivation — mirrors customizer.js + PHP.
  function rgbToHsl(rgb) {
    var r = rgb[0] / 255,
      g = rgb[1] / 255,
      b = rgb[2] / 255;
    var max = Math.max(r, g, b),
      min = Math.min(r, g, b);
    var l = (max + min) / 2;
    if (max === min) return [0, 0, l * 100];
    var d = max - min;
    var s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    var h;
    if (max === r) h = (g - b) / d + (g < b ? 6 : 0);else if (max === g) h = (b - r) / d + 2;else h = (r - g) / d + 4;
    return [h * 60, s * 100, l * 100];
  }
  function hslToRgb(hsl) {
    var h = hsl[0] / 360,
      s = hsl[1] / 100,
      l = hsl[2] / 100;
    if (s === 0) return [l * 255, l * 255, l * 255];
    var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    var p = 2 * l - q;
    var hue = function (t) {
      if (t < 0) t += 1;
      if (t > 1) t -= 1;
      if (t < 1 / 6) return p + (q - p) * 6 * t;
      if (t < 1 / 2) return q;
      if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
      return p;
    };
    return [hue(h + 1 / 3) * 255, hue(h) * 255, hue(h - 1 / 3) * 255];
  }
  function rgbToHex(rgb) {
    var c = function (v) {
      var n = Math.max(0, Math.min(255, Math.round(v))).toString(16);
      return (n.length === 1 ? '0' + n : n).toUpperCase();
    };
    return '#' + c(rgb[0]) + c(rgb[1]) + c(rgb[2]);
  }
  function clamp(v, lo, hi) {
    return Math.max(lo, Math.min(hi, v));
  }
  function deriveDarkSlot(slot, srcHex, palette) {
    var rgb = hexToRgbArray(srcHex);
    if (!rgb) return srcHex;
    var hsl = rgbToHsl(rgb),
      h = hsl[0],
      s = hsl[1],
      l = hsl[2];
    switch (slot) {
      case 'base':
        l = clamp(100 - l, 5, 12);
        break;
      case 'text':
        l = clamp(100 - l, 88, 96);
        s = Math.min(s, 30);
        break;
      case 'surface':
        var baseSrc = palette && palette.colors && palette.colors.base;
        var baseDark = baseSrc ? deriveDarkSlot('base', baseSrc, palette) : '#0B0D10';
        var bRgb = hexToRgbArray(baseDark),
          bHsl = rgbToHsl(bRgb);
        h = bHsl[0];
        s = bHsl[1];
        l = Math.min(bHsl[2] + 6, 18);
        break;
      case 'primary':
        l = clamp(l, 55, 70);
        break;
      case 'secondary':
        var surfHex = palette && palette.colors && palette.colors.surface || '#FFFFFF';
        var surfRgb = hexToRgbArray(surfHex);
        var lumSrc = luminance(rgb),
          lumSurf = surfRgb ? luminance(surfRgb) : 1;
        if (lumSrc < lumSurf) {
          l = clamp(100 - l, 80, 95);
          s = Math.max(s - 10, 0);
        } else {
          l = Math.max(l - 10, 20);
        }
        break;
      case 'accent':
        s = Math.min(s + 10, 95);
        l = clamp(l, 55, 80);
        break;
    }
    return rgbToHex(hslToRgb([h, s, l]));
  }
  function resolveDarkSlot(slot, palette) {
    if (palette && palette.dark && palette.dark[slot]) return palette.dark[slot];
    if (palette && palette.colors && palette.colors[slot]) {
      return deriveDarkSlot(slot, palette.colors[slot], palette);
    }
    var legacyMap = {
      text: 'text',
      primary: 'primary',
      secondary: 'secondary'
    };
    var lk = legacyMap[slot];
    if (lk && LEGACY_MODS[lk]) return deriveDarkSlot(slot, LEGACY_MODS[lk], palette);
    if (DARK_BASELINES.scss && DARK_BASELINES.scss[slot]) return DARK_BASELINES.scss[slot];
    if (DARK_BASELINES.hex && DARK_BASELINES.hex[slot]) return DARK_BASELINES.hex[slot];
    return '#000000';
  }
  function ensureStyleEl() {
    var el = document.getElementById(STYLE_ID);
    if (el) return el;
    el = document.createElement('style');
    el.id = STYLE_ID;
    document.head.appendChild(el);
    return el;
  }

  // Build the `:root { … }` block + dark trigger. Mirrors output_root_vars() in PHP.
  function buildCss(palette) {
    if (!palette || !palette.colors) return '';
    var decls = '';

    // Light slots.
    for (var i = 0; i < SLOTS.length; i++) {
      var slot = SLOTS[i];
      var hex = palette.colors[slot];
      if (!hex) continue;
      decls += '--customify-color-' + slot + ':' + hex + ';';
      var rgb = hexToRgb(hex);
      if (rgb) decls += '--customify-color-' + slot + '-rgb:' + rgb + ';';
    }
    if (palette.colors.primary) decls += '--customify-color-on-primary:' + pickOn(palette.colors.primary) + ';';
    if (palette.colors.secondary) decls += '--customify-color-on-secondary:' + pickOn(palette.colors.secondary) + ';';
    if (palette.colors.surface) decls += '--customify-color-on-surface:' + pickOn(palette.colors.surface) + ';';
    var textRgb = hexToRgb(palette.colors.text);
    if (textRgb) {
      decls += '--customify-color-text-muted:rgba(' + textRgb + ', 0.55);';
      decls += '--customify-color-text-subtle:rgba(' + textRgb + ', 0.35);';
      decls += '--customify-color-border-default:rgba(' + textRgb + ', 0.12);';
    }
    if (palette.colors.primary) {
      decls += '--customify-color-primary-hover:color-mix(in srgb, ' + palette.colors.primary + ', #000 15%);';
      if (palette.colors.base) {
        decls += '--customify-color-primary-subtle:color-mix(in srgb, ' + palette.colors.primary + ', ' + palette.colors.base + ' 92%);';
      }
    }

    // Dark slots — full 5-tier resolve chain.
    var dark = {};
    for (var di = 0; di < SLOTS.length; di++) {
      dark[SLOTS[di]] = resolveDarkSlot(SLOTS[di], palette);
    }
    for (var ds = 0; ds < SLOTS.length; ds++) {
      var dslot = SLOTS[ds],
        dhex = dark[dslot];
      if (!dhex) continue;
      decls += '--customify-color-' + dslot + '-dark:' + dhex + ';';
      var drgb = hexToRgb(dhex);
      if (drgb) decls += '--customify-color-' + dslot + '-dark-rgb:' + drgb + ';';
    }
    if (dark.primary) decls += '--customify-color-on-primary-dark:' + pickOn(dark.primary) + ';';
    if (dark.secondary) decls += '--customify-color-on-secondary-dark:' + pickOn(dark.secondary) + ';';
    if (dark.surface) decls += '--customify-color-on-surface-dark:' + pickOn(dark.surface) + ';';
    var dTextRgb = hexToRgb(dark.text);
    if (dTextRgb) {
      decls += '--customify-color-text-muted-dark:rgba(' + dTextRgb + ', 0.55);';
      decls += '--customify-color-text-subtle-dark:rgba(' + dTextRgb + ', 0.35);';
      decls += '--customify-color-border-default-dark:rgba(' + dTextRgb + ', 0.12);';
    }
    if (dark.primary) {
      // Direction flip vs light: blend toward white.
      decls += '--customify-color-primary-hover-dark:color-mix(in srgb, ' + dark.primary + ', #fff 12%);';
      if (dark.base) {
        decls += '--customify-color-primary-subtle-dark:color-mix(in srgb, ' + dark.primary + ', ' + dark.base + ' 92%);';
      }
    }

    // Trigger block — rebind active vars to the -dark companions inside
    // any subtree carrying one of the dark-mode trigger classes.
    var trigger = '';
    for (var ti = 0; ti < SLOTS.length; ti++) {
      var ts = SLOTS[ti];
      trigger += '--customify-color-' + ts + ':var(--customify-color-' + ts + '-dark);';
      trigger += '--customify-color-' + ts + '-rgb:var(--customify-color-' + ts + '-dark-rgb);';
    }
    var autoKeys = ['on-primary', 'on-secondary', 'on-surface', 'text-muted', 'text-subtle', 'border-default', 'primary-hover', 'primary-subtle'];
    for (var ai = 0; ai < autoKeys.length; ai++) {
      trigger += '--customify-color-' + autoKeys[ai] + ':var(--customify-color-' + autoKeys[ai] + '-dark);';
    }
    return ':root{' + decls + '}.dark-mode,.is-dark-mode,[data-theme="dark"]{' + trigger + '}';
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
/************************************************************************/
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
!function() {
"use strict";
/* harmony import */ var _js_customizer_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(296);
/* harmony import */ var _js_customizer_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_js_customizer_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _preview_colors_preview_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(623);
/* harmony import */ var _preview_colors_preview_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_preview_colors_preview_js__WEBPACK_IMPORTED_MODULE_1__);


// Preview-colors live update — same iframe context (customize-preview),
// merged here so the iframe loads one combined bundle instead of two.

}();
/******/ })()
;