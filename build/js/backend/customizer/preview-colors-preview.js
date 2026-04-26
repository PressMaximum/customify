/******/ (function() { // webpackBootstrap
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
/******/ })()
;