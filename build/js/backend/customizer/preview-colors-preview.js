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
/******/ })()
;