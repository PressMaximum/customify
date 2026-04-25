/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 919:
/***/ (function() {

/*
 * Customify Preview Colors — frontend sidebar (`?preview-colors=1`).
 *
 * Mounts inside a Shadow DOM attached to `#customify-preview-colors-root` so
 * theme styles cannot bleed in. The CSS bundle is loaded as a <link> *inside*
 * the shadow root (URL passed via `cfg.cssUrl`); the host gets only the empty
 * div from PHP.
 *
 * State (user palettes, active id) is loaded from window.CustomifyPreviewColors
 * and persisted via wp_ajax endpoints. Edits affect only the panel UI in this
 * phase — live page preview lands in phase 2.
 */
(function () {
  'use strict';

  var cfg = window.CustomifyPreviewColors;
  if (!cfg) return;
  var host = document.getElementById(cfg.rootId);
  if (!host) return;

  // Attach (or reuse) shadow root.
  var shadow = host.shadowRoot || host.attachShadow({
    mode: 'open'
  });
  var root = shadow; // queries below scope to the shadow tree.

  var SLOTS = cfg.slots;
  var SLOT_DESC = cfg.slotDesc || {};
  var THEME_PALETTES = (cfg.themePresets || []).map(function (p) {
    return {
      id: p.id,
      name: p.name,
      colors: assign({}, p.colors)
    };
  });
  var SETTINGS = cfg.settingsRows || [];
  var userPalettes = (cfg.userPalettes || []).map(function (p) {
    return {
      id: p.id,
      name: p.name,
      colors: assign({}, p.colors)
    };
  });
  var activeId = cfg.activeId || THEME_PALETTES[0] && THEME_PALETTES[0].id || '';
  if (!findById(activeId)) {
    activeId = THEME_PALETTES[0] ? THEME_PALETTES[0].id : '';
  }
  var activeKind = findKind(activeId);

  // ---------------------------------------------------------------- helpers

  function assign(target) {
    for (var i = 1; i < arguments.length; i++) {
      var src = arguments[i];
      if (!src) continue;
      for (var k in src) if (Object.prototype.hasOwnProperty.call(src, k)) target[k] = src[k];
    }
    return target;
  }
  function $(sel) {
    return root.querySelector(sel);
  }
  function $$(sel) {
    return root.querySelectorAll(sel);
  }
  function el(tag, opts, html) {
    var n = document.createElement(tag);
    if (opts) {
      for (var k in opts) {
        if (k === 'class') n.className = opts[k];else if (k === 'dataset') for (var d in opts.dataset) n.dataset[d] = opts.dataset[d];else if (k === 'style') n.setAttribute('style', opts[k]);else n.setAttribute(k, opts[k]);
      }
    }
    if (html != null) n.innerHTML = html;
    return n;
  }
  function escHtml(s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function isLight(hex) {
    var c = String(hex || '').replace('#', '');
    if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    var r = parseInt(c.substr(0, 2), 16);
    var g = parseInt(c.substr(2, 2), 16);
    var b = parseInt(c.substr(4, 2), 16);
    return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.7;
  }
  function getAllPalettes() {
    return THEME_PALETTES.concat(userPalettes);
  }
  function findById(id) {
    return getAllPalettes().filter(function (p) {
      return p.id === id;
    })[0];
  }
  function findKind(id) {
    return THEME_PALETTES.some(function (p) {
      return p.id === id;
    }) ? 'theme' : 'user';
  }
  function getActive() {
    return findById(activeId);
  }

  // Emit the active palette in the export-friendly shape ({name, colors}).
  // Called whenever the Current palette content changes — preset switch or
  // per-slot edit.
  function logActive() {
    var pal = getActive();
    if (!pal) return;
    console.log('[Customify Preview Colors] Current palette:', {
      name: pal.name,
      colors: assign({}, pal.colors)
    });
  }

  // Parse "#RRGGBB" / "#RGB" → array [r, g, b] of integers, or null.
  function hexToRgbArray(hex) {
    var c = String(hex || '').replace('#', '');
    if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    if (!/^[0-9A-Fa-f]{6}$/.test(c)) return null;
    return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)];
  }

  // "r, g, b" string for `rgba(var(--customify-color-X-rgb), <alpha>)` consumers.
  function hexToRgb(hex) {
    var rgb = hexToRgbArray(hex);
    return rgb ? rgb.join(', ') : null;
  }

  // WCAG relative luminance.
  function luminance(rgb) {
    var f = function (v) {
      v /= 255;
      return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    };
    return 0.2126 * f(rgb[0]) + 0.7152 * f(rgb[1]) + 0.0722 * f(rgb[2]);
  }

  // Threshold 0.45 — slightly nudged from the WCAG 0.5 default toward white,
  // which reads better on warm tones (terracotta, amber). Per Style Pack note.
  function pickOn(hex) {
    var rgb = hexToRgbArray(hex);
    if (!rgb) return '#FFFFFF';
    return luminance(rgb) > 0.45 ? '#1A1A1A' : '#FFFFFF';
  }

  // Mirror the active palette onto :root as CSS custom properties.
  //
  // Six user-picked slots (Style Pack vocabulary):
  //   --customify-color-<slot>      (hex)
  //   --customify-color-<slot>-rgb  ("r, g, b" — feeds rgba() consumers)
  //
  // Auto-computed companion vars (theme-derived; user never picks):
  //   --customify-color-on-primary / on-secondary / on-surface
  //       JS WCAG luminance → "#1A1A1A" or "#FFFFFF" for max contrast.
  //   --customify-color-text-muted    rgba(text-rgb, 0.55)   meta / breadcrumbs
  //   --customify-color-text-subtle   rgba(text-rgb, 0.35)   disabled state
  //   --customify-color-border-default rgba(text-rgb, 0.12)  dividers
  //   --customify-color-primary-hover  color-mix(primary, #000 15%)  link/btn hover
  //   --customify-color-primary-subtle color-mix(primary, base 92%)  primary wash
  function applyColorVars() {
    var pal = getActive();
    if (!pal) return;
    var docRoot = document.documentElement;

    // 1) Six user-picked slots.
    for (var i = 0; i < SLOTS.length; i++) {
      var slot = SLOTS[i];
      var val = pal.colors[slot];
      if (!val) continue;
      docRoot.style.setProperty('--customify-color-' + slot, val);
      var rgb = hexToRgb(val);
      if (rgb) docRoot.style.setProperty('--customify-color-' + slot + '-rgb', rgb);
    }

    // 2) Contrast-aware on-* companions for slots used as backgrounds.
    var onSlots = ['primary', 'secondary', 'surface'];
    for (var k = 0; k < onSlots.length; k++) {
      var s = onSlots[k];
      if (pal.colors[s]) {
        docRoot.style.setProperty('--customify-color-on-' + s, pickOn(pal.colors[s]));
      }
    }

    // 3) Text + border alpha derivatives — universal browser support.
    var textRgb = hexToRgb(pal.colors.text);
    if (textRgb) {
      docRoot.style.setProperty('--customify-color-text-muted', 'rgba(' + textRgb + ', 0.55)');
      docRoot.style.setProperty('--customify-color-text-subtle', 'rgba(' + textRgb + ', 0.35)');
      docRoot.style.setProperty('--customify-color-border-default', 'rgba(' + textRgb + ', 0.12)');
    }

    // 4) Primary hover / subtle — color-mix (Chrome 111+ / FF 113+ / Safari 16.2+).
    // Browsers that lack `color-mix` resolve the var to the value below; the
    // override stylesheet supplies a per-rule hex fallback.
    if (pal.colors.primary) {
      docRoot.style.setProperty('--customify-color-primary-hover', 'color-mix(in srgb, ' + pal.colors.primary + ', #000 15%)');
      if (pal.colors.base) {
        docRoot.style.setProperty('--customify-color-primary-subtle', 'color-mix(in srgb, ' + pal.colors.primary + ', ' + pal.colors.base + ' 92%)');
      }
    }
  }

  // --------------------------------------------------------------- ajax I/O

  function ajaxPost(action, data) {
    var body = new FormData();
    body.append('action', action);
    body.append('nonce', cfg.nonce);
    Object.keys(data || {}).forEach(function (k) {
      body.append(k, data[k]);
    });
    return fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body
    }).then(function (r) {
      return r.json();
    }).catch(function () {
      return {
        success: false
      };
    });
  }
  var savePalettesPending = null;
  function persistPalettes() {
    if (savePalettesPending) clearTimeout(savePalettesPending);
    savePalettesPending = setTimeout(function () {
      savePalettesPending = null;
      ajaxPost('customify_preview_colors_save_palettes', {
        palettes: JSON.stringify(userPalettes)
      });
    }, 350);
  }
  var saveActivePending = null;
  function persistActive() {
    if (saveActivePending) clearTimeout(saveActivePending);
    saveActivePending = setTimeout(function () {
      saveActivePending = null;
      ajaxPost('customify_preview_colors_set_active', {
        id: activeId
      });
    }, 200);
  }

  // ------------------------------------------------------ initial markup

  // CSS link goes first so styles apply before the panel paints.
  root.innerHTML = [cfg.cssUrl ? '<link rel="stylesheet" href="' + cfg.cssUrl + '">' : '', '<div class="sidebar">', '  <div class="sb-header">', '    <button class="sb-back" aria-label="Back" type="button">', '      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 11L4.5 7l4-4"/></svg>', '    </button>', '    <div class="sb-crumb">', '      <small>Customizing › Colors</small>', '      <strong>Colors</strong>', '    </div>', '    <span class="sb-help">?</span>', '  </div>', '  <div class="section">', '    <div class="sec-row"><h3>Current palette</h3></div>', '    <div class="hero-card">', '      <div class="deck-wrap" data-deck></div>', '      <div class="deck-footer">', '        <span class="deck-name"><span data-active-name></span> <span class="tag-theme" data-active-tag>theme</span></span>', '        <span class="deck-sub" data-active-meta>6 slots</span>', '      </div>', '      <div class="deck-hint">Click a card to edit that slot</div>', '      <div class="popover" data-popover>', '        <div class="popover-head">', '          <span data-pop-slot></span>', '          <button class="modal-close" data-pop-close type="button" style="width:20px;height:20px;">', '            <svg width="10" height="10" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3l8 8M11 3l-8 8"/></svg>', '          </button>', '        </div>', '        <label class="popover-preview" data-pop-preview>', '          <input type="color" data-pop-picker value="#000000">', '        </label>', '        <div class="popover-hex" data-pop-hex></div>', '        <div class="popover-desc" data-pop-desc></div>', '        <div class="popover-copy-hint" data-pop-note style="display:none;">Editing creates a copy in Custom palettes</div>', '      </div>', '    </div>', '  </div>', '  <div class="section">', '    <div class="sec-row">', '      <h3>Theme presets</h3>', '      <button class="icon-btn" data-open-modal type="button" aria-label="Browse all palettes" title="Browse all palettes">', '        <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="4" height="4"/><rect x="8" y="2" width="4" height="4"/><rect x="2" y="8" width="4" height="4"/><rect x="8" y="8" width="4" height="4"/></svg>', '      </button>', '    </div>', '    <div class="preset-grid" data-theme-grid></div>', '  </div>', '  <div class="section">', '    <div class="sec-row">', '      <h3>Custom palettes <span class="badge-count" data-user-count>0</span></h3>', '      <div style="display:flex;gap:4px;">', '        <button class="icon-btn" data-toggle-export type="button" aria-label="Export palettes" title="Export JSON">', '          <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 12V5M4 8l3-3 3 3M2.5 2.5h9"/></svg>', '        </button>', '        <button class="icon-btn" data-toggle-import type="button" aria-label="Import palette" title="Import JSON">', '          <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 2v7M4 6l3 3 3-3M2.5 11.5h9"/></svg>', '        </button>', '        <button class="icon-btn" data-toggle-add type="button" aria-label="Add palette" title="Add new palette">', '          <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M7 3v8M3 7h8"/></svg>', '        </button>', '      </div>', '    </div>', '    <div data-user-grid-wrap>', '      <div class="preset-grid" data-user-grid></div>', '      <div class="empty-user" data-empty-user style="display:none;">', '        No custom palettes yet.<br>Click <strong>+</strong> to create one, or <strong>↓</strong> to import JSON.', '      </div>', '    </div>', '    <div class="add-form" data-add-form>', '      <div class="form-title">Create new palette</div>', '      <div class="form-field">', '        <label data-for-new-name>Palette title</label>', '        <input type="text" data-new-name placeholder="e.g. My brand" autocomplete="off">', '      </div>', '      <div class="form-field">', '        <label data-for-extend>Extend from</label>', '        <select data-extend-from></select>', '      </div>', '      <div class="form-actions">', '        <button class="btn-cancel" data-cancel-add type="button">Cancel</button>', '        <button class="btn-add" data-confirm-add type="button">', '          <svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M7 3v8M3 7h8"/></svg> Add', '        </button>', '      </div>', '    </div>', '    <div class="add-form" data-import-form>', '      <div class="form-title">Import palette(s) from JSON', '        <button class="paste-example" data-paste-example type="button">Use example</button>', '      </div>', '      <div class="form-field">', '        <label data-for-json>Paste JSON</label>', '        <textarea data-json-input spellcheck="false"></textarea>', '        <p class="form-hint">Accepts a single palette object or an array. Requires <code>name</code> + all 6 slots.</p>', '        <div class="form-error" data-json-error></div>', '      </div>', '      <div class="form-actions">', '        <button class="btn-cancel" data-cancel-import type="button">Cancel</button>', '        <button class="btn-add" data-confirm-import type="button">', '          <svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 2v7M4 6l3 3 3-3"/></svg> Import', '        </button>', '      </div>', '    </div>', '    <div class="add-form" data-export-form>', '      <div class="form-title">Export custom palettes</div>', '      <div class="form-field" data-export-select-field></div>', '      <div class="form-field" data-export-output-field style="display:none;">', '        <label>JSON output</label>', '        <div class="export-output" data-export-output>//  No palettes selected</div>', '        <p class="form-hint">Re-importable via the <code>↓</code> button above or on another site.</p>', '      </div>', '      <div class="form-actions">', '        <span class="copied-flash" data-copied-flash>✓ Copied</span>', '        <button class="btn-cancel" data-cancel-export type="button">Close</button>', '        <button class="btn-ghost" data-download-export type="button" disabled>', '          <svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M7 2v7M4 6l3 3 3-3M2.5 11.5h9"/></svg> Download', '        </button>', '        <button class="btn-add" data-copy-export type="button" disabled>', '          <svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="8" height="8" rx="1"/><path d="M10 4V3a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h1"/></svg> Copy', '        </button>', '      </div>', '    </div>', '  </div>', '  <div class="section">', '    <div class="group-title">', '      <h4>Theme color</h4>', '      <button class="reset" type="button" aria-label="Reset all colors" title="Reset to defaults">', '        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10"/></svg>', '      </button>', '    </div>', '    <div data-theme-settings></div>', '  </div>', '  <div class="section">', '    <div class="group-title">', '      <h4>Auto-computed</h4>', '    </div>', '    <div class="auto-computed-hint">Theme generates these from the 6 slots above.</div>', '    <div data-auto-computed></div>', '  </div>', '</div>', '<div class="modal-overlay" data-modal-overlay>', '  <div class="modal" role="dialog">', '    <div class="modal-head">', '      <h3>Choose a palette</h3>', '      <button class="modal-close" data-close-modal type="button" aria-label="Close">', '        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 3l8 8M11 3l-8 8"/></svg>', '      </button>', '    </div>', '    <div class="modal-body" data-modal-body></div>', '  </div>', '</div>', '<button class="cpc-reopen" data-cpc-reopen type="button" aria-label="Open color panel" title="Open color panel">', '  <svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 3l4 4-4 4"/></svg>', '</button>'].join('\n');

  // Element refs.
  var deck = $('[data-deck]');
  var activeName = $('[data-active-name]');
  var activeTag = $('[data-active-tag]');
  var activeMeta = $('[data-active-meta]');
  var themeGrid = $('[data-theme-grid]');
  var userGrid = $('[data-user-grid]');
  var emptyUser = $('[data-empty-user]');
  var userCount = $('[data-user-count]');
  var themeSettings = $('[data-theme-settings]');
  var autoComputed = $('[data-auto-computed]');
  var modalOverlay = $('[data-modal-overlay]');
  var modalBody = $('[data-modal-body]');
  var popover = $('[data-popover]');
  var popSlot = $('[data-pop-slot]');
  var popPicker = $('[data-pop-picker]');
  var popHex = $('[data-pop-hex]');
  var popPreview = $('[data-pop-preview]');
  var popDesc = $('[data-pop-desc]');
  var popNote = $('[data-pop-note]');
  var addForm = $('[data-add-form]');
  var newName = $('[data-new-name]');
  var extendFrom = $('[data-extend-from]');
  var importForm = $('[data-import-form]');
  var jsonInput = $('[data-json-input]');
  var jsonError = $('[data-json-error]');
  var exportForm = $('[data-export-form]');
  var exportSelectField = $('[data-export-select-field]');
  var exportOutputField = $('[data-export-output-field]');
  var exportOutput = $('[data-export-output]');
  var copiedFlash = $('[data-copied-flash]');
  var copyExportBtn = $('[data-copy-export]');
  var downloadExportBtn = $('[data-download-export]');

  // JSON input placeholder uses HTML entities; set via property to avoid escaping noise.
  jsonInput.placeholder = '{\n  "name": "My palette",\n  "colors": {\n    "base": "#F9F3E4",\n    "text": "#1A3A28",\n    "primary": "#B35932",\n    "secondary": "#1C2147",\n    "accent": "#F5DE9A",\n    "surface": "#FFFFFF"\n  }\n}';

  // ------------------------------------------------------ fanned deck

  var ANGLES = [-20, -12, -4, 4, 12, 20];
  var OFFSETS = [-60, -36, -12, 12, 36, 60];
  function buildDeck(pal, firstTime) {
    if (firstTime) {
      deck.innerHTML = '';
      SLOTS.forEach(function (slot, i) {
        var c = pal.colors[slot];
        var node = el('div', {
          'class': 'deck-card' + (!isLight(c) ? ' is-dark' : '')
        });
        var tx = OFFSETS[i] + 'px';
        var r = ANGLES[i] + 'deg';
        node.style.setProperty('--tx', tx);
        node.style.setProperty('--r', r);
        node.style.setProperty('--base-transform', 'translateX(' + tx + ') rotate(' + r + ')');
        node.style.transform = 'translateX(' + tx + ') rotate(' + r + ')';
        node.style.zIndex = i + 1;
        node.style.background = c;
        node.dataset.slot = slot;
        node.title = slot + ' · ' + c.toUpperCase();
        node.innerHTML = '<span class="slot-chip">' + slot + '</span>';
        node.style.animationDelay = i * 40 + 'ms';
        node.classList.add('animating');
        node.addEventListener('click', function (e) {
          e.stopPropagation();
          openPopover(slot);
        });
        deck.appendChild(node);
      });
    } else {
      [].slice.call(deck.children).forEach(function (node, i) {
        var slot = SLOTS[i];
        var c = pal.colors[slot];
        node.style.background = c;
        node.title = slot + ' · ' + c.toUpperCase();
        node.classList.toggle('is-dark', !isLight(c));
        node.classList.remove('animating');
        void node.offsetWidth;
        node.style.animationDelay = i * 40 + 'ms';
        node.classList.add('animating');
      });
    }
    activeName.textContent = pal.name;
    activeTag.textContent = activeKind;
    activeTag.classList.toggle('tag-user', activeKind === 'user');
    activeMeta.textContent = SLOTS.length + ' slots';
  }

  // ------------------------------------------------------ preset cards

  function renderPresetCard(pal, kind) {
    var c = pal.colors;
    var card = el('div', {
      'class': 'preset-card' + (pal.id === activeId ? ' active' : '') + (kind === 'user' ? ' is-user' : '')
    });
    card.innerHTML = ['<p class="preset-name">', '  <span>' + escHtml(pal.name) + '</span>', '  <span class="slot-count">' + SLOTS.length + '</span>', '</p>', '<div class="palette-strip">', SLOTS.map(function (s) {
      return '<div class="chip" style="background:' + c[s] + '" title="' + s + '"></div>';
    }).join(''), '</div>', kind === 'user' ? '<button class="del" type="button" aria-label="Delete palette" title="Delete"><svg width="10" height="10" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 3l8 8M11 3l-8 8"/></svg></button>' : ''].join('\n');
    card.addEventListener('click', function (e) {
      if (e.target.closest('.del')) return;
      setActive(pal.id);
    });
    var del = card.querySelector('.del');
    if (del) {
      del.addEventListener('click', function (e) {
        e.stopPropagation();
        deleteUserPalette(pal.id);
      });
    }
    return card;
  }
  function buildThemeGrid() {
    themeGrid.innerHTML = '';
    THEME_PALETTES.forEach(function (pal) {
      themeGrid.appendChild(renderPresetCard(pal, 'theme'));
    });
  }
  function buildUserGrid() {
    userCount.textContent = userPalettes.length;
    userGrid.innerHTML = '';
    if (userPalettes.length === 0) {
      userGrid.style.display = 'none';
      emptyUser.style.display = 'block';
    } else {
      userGrid.style.display = '';
      emptyUser.style.display = 'none';
      userPalettes.forEach(function (pal) {
        userGrid.appendChild(renderPresetCard(pal, 'user'));
      });
    }
  }

  // ------------------------------------------------------ settings rows

  function buildSettings() {
    var pal = getActive();
    themeSettings.innerHTML = '';
    SETTINGS.forEach(function (cfgRow) {
      var wrap = el('div', {
        'class': 'setting-row'
      });
      wrap.innerHTML = '<div class="label-wrap"><span class="label">' + escHtml(cfgRow.label) + '</span><span class="sublabel">' + escHtml(cfgRow.sublabel) + '</span></div><div class="right" data-row-right></div>';
      var right = wrap.querySelector('[data-row-right]');
      right.appendChild(el('button', {
        'class': 'reset-mini',
        'type': 'button'
      }, '<svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10"/></svg>'));
      (cfgRow.slots || []).forEach(function (slotName) {
        var col = pal.colors[slotName];
        var dot = el('span', {
          'class': 'color-dot' + (isLight(col) ? ' light' : ''),
          'style': 'background:' + col,
          'title': slotName + ' · ' + col.toUpperCase()
        }, '<svg class="globe" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="6" cy="6" r="4.5"/><path d="M1.5 6h9M6 1.5c1.5 1.5 2.2 3 2.2 4.5S7.5 9 6 10.5M6 1.5C4.5 3 3.8 4.5 3.8 6S4.5 9 6 10.5"/></svg>');
        dot.addEventListener('click', function (e) {
          e.stopPropagation();
          openPopover(slotName);
        });
        right.appendChild(dot);
      });
      themeSettings.appendChild(wrap);
    });
  }

  // ------------------------------------------------------ auto-computed group

  // Read-only chips listing the eight Style-Pack-derived companion vars the
  // theme synthesises from the six user-picked slots. Updated whenever the
  // active palette / a slot changes (via the same callbacks that re-emit
  // applyColorVars()).
  var AUTO_COMPUTED_ROWS = [{
    slot: 'on-primary',
    label: 'Text on primary'
  }, {
    slot: 'on-secondary',
    label: 'Text on secondary'
  }, {
    slot: 'on-surface',
    label: 'Text on surface'
  }, {
    slot: 'text-muted',
    label: 'Muted text'
  }, {
    slot: 'text-subtle',
    label: 'Subtle text'
  }, {
    slot: 'border-default',
    label: 'Default border'
  }, {
    slot: 'primary-hover',
    label: 'Primary hover'
  }, {
    slot: 'primary-subtle',
    label: 'Primary subtle'
  }];
  function buildAutoComputed() {
    if (!autoComputed) return;
    var rootStyle = getComputedStyle(document.documentElement);
    autoComputed.innerHTML = '';
    AUTO_COMPUTED_ROWS.forEach(function (row) {
      var resolved = rootStyle.getPropertyValue('--customify-color-' + row.slot).trim() || '—';
      var chip = el('div', {
        'class': 'auto-computed-row',
        'title': row.slot + ' · ' + resolved
      });
      chip.innerHTML = '<span class="auto-computed-swatch" style="background:' + resolved + ';"></span>' + '<span class="auto-computed-label">' + escHtml(row.label) + '</span>' + '<span class="auto-computed-slot">' + escHtml(row.slot) + '</span>';
      autoComputed.appendChild(chip);
    });
  }

  // ------------------------------------------------------ active

  function setActive(id) {
    if (id === activeId) return;
    activeId = id;
    activeKind = findKind(id);
    var pal = getActive();
    if (!pal) return;
    buildDeck(pal, false);
    buildThemeGrid();
    buildUserGrid();
    buildSettings();
    buildModalList();
    persistActive();
    applyColorVars();
    buildAutoComputed();
    logActive();
  }

  // ------------------------------------------------------ modal

  function buildModalList() {
    modalBody.innerHTML = '';
    var themeTitle = el('div', {
      'class': 'modal-group-title'
    }, 'Theme presets (' + THEME_PALETTES.length + ')');
    modalBody.appendChild(themeTitle);
    THEME_PALETTES.forEach(function (pal) {
      modalBody.appendChild(renderModalRow(pal, 'theme'));
    });
    var userTitle = el('div', {
      'class': 'modal-group-title'
    }, 'Custom palettes (' + userPalettes.length + ')');
    modalBody.appendChild(userTitle);
    if (userPalettes.length === 0) {
      modalBody.appendChild(el('div', {
        'class': 'palette-row-empty'
      }, 'No custom palettes yet'));
    } else {
      userPalettes.forEach(function (pal) {
        modalBody.appendChild(renderModalRow(pal, 'user'));
      });
    }
  }
  function renderModalRow(pal, kind) {
    var c = pal.colors;
    var row = el('div', {
      'class': 'palette-row' + (pal.id === activeId ? ' active' : '')
    });
    row.innerHTML = ['<div class="palette-row-name">' + escHtml(pal.name) + '<small>' + kind + '</small></div>', '<div class="palette-row-swatches">', SLOTS.map(function (s) {
      return '<span class="swatch" style="background:' + c[s] + '"></span>';
    }).join(''), '</div>'].join('');
    row.addEventListener('click', function () {
      setActive(pal.id);
      setTimeout(closeModal, 260);
    });
    return row;
  }
  function openModal() {
    modalOverlay.classList.add('open');
  }
  function closeModal() {
    modalOverlay.classList.remove('open');
  }

  // ------------------------------------------------------ add palette

  function buildExtendDropdown() {
    extendFrom.innerHTML = '';
    THEME_PALETTES.forEach(function (p) {
      var o = document.createElement('option');
      o.value = p.id;
      o.textContent = p.name + ' (theme)';
      extendFrom.appendChild(o);
    });
    userPalettes.forEach(function (p) {
      var o = document.createElement('option');
      o.value = p.id;
      o.textContent = p.name + ' (user)';
      extendFrom.appendChild(o);
    });
    extendFrom.value = activeId;
  }
  function openAddForm() {
    closeImportForm();
    closeExportForm();
    buildExtendDropdown();
    newName.value = '';
    addForm.classList.add('open');
    setTimeout(function () {
      newName.focus();
    }, 100);
  }
  function closeAddForm() {
    addForm.classList.remove('open');
  }
  function confirmAdd() {
    var name = newName.value.trim() || 'Palette ' + (userPalettes.length + 1);
    var sourceId = extendFrom.value;
    var source = findById(sourceId);
    if (!source) return;
    var id = 'user_' + Date.now();
    userPalettes.push({
      id: id,
      name: name,
      colors: assign({}, source.colors)
    });
    closeAddForm();
    setActive(id);
    persistPalettes();
  }
  function deleteUserPalette(id) {
    userPalettes = userPalettes.filter(function (p) {
      return p.id !== id;
    });
    if (activeId === id) {
      activeId = THEME_PALETTES[0] ? THEME_PALETTES[0].id : '';
      activeKind = 'theme';
      persistActive();
    }
    buildUserGrid();
    buildThemeGrid();
    var pal = getActive();
    if (pal) buildDeck(pal, false);
    buildSettings();
    buildModalList();
    persistPalettes();
  }

  // ------------------------------------------------------ import

  var EXAMPLE_JSON = JSON.stringify([{
    name: 'Sunset',
    colors: {
      base: '#FFF4E6',
      text: '#2B1810',
      primary: '#E85D04',
      secondary: '#3D1F0A',
      accent: '#FFD56B',
      surface: '#FFFFFF'
    }
  }, {
    name: 'Frost',
    colors: {
      base: '#F0F7FA',
      text: '#0A2540',
      primary: '#0284C7',
      secondary: '#0F3E5C',
      accent: '#BAE6FD',
      surface: '#FFFFFF'
    }
  }], null, 2);
  function openImportForm() {
    closeAddForm();
    closeExportForm();
    jsonInput.value = '';
    jsonError.classList.remove('show');
    importForm.classList.add('open');
    setTimeout(function () {
      jsonInput.focus();
    }, 100);
  }
  function closeImportForm() {
    importForm.classList.remove('open');
    jsonError.classList.remove('show');
  }
  function showImportError(msg) {
    jsonError.textContent = msg;
    jsonError.classList.add('show');
  }
  function validateAndImport() {
    var raw = jsonInput.value.trim();
    if (!raw) {
      showImportError('Paste JSON code first.');
      return;
    }
    var parsed;
    try {
      parsed = JSON.parse(raw);
    } catch (err) {
      showImportError('Invalid JSON: ' + err.message);
      return;
    }
    var items = Array.isArray(parsed) ? parsed : [parsed];
    var validated = [];
    for (var i = 0; i < items.length; i++) {
      var it = items[i];
      if (!it || typeof it !== 'object') {
        showImportError('Item ' + (i + 1) + ': must be an object.');
        return;
      }
      if (!it.name || typeof it.name !== 'string') {
        showImportError('Item ' + (i + 1) + ': missing "name" field.');
        return;
      }
      if (!it.colors || typeof it.colors !== 'object') {
        showImportError('Item ' + (i + 1) + ': missing "colors" object.');
        return;
      }
      var normalized = {};
      for (var s = 0; s < SLOTS.length; s++) {
        var slot = SLOTS[s];
        var v = it.colors[slot];
        if (!v || typeof v !== 'string' || !/^#([0-9A-F]{3}|[0-9A-F]{6})$/i.test(v.trim())) {
          showImportError('Item ' + (i + 1) + ' ("' + it.name + '"): slot "' + slot + '" missing or not a valid hex.');
          return;
        }
        normalized[slot] = v.trim().toUpperCase();
      }
      validated.push({
        id: 'user_' + Date.now() + '_' + i,
        name: it.name.trim(),
        colors: normalized
      });
    }
    userPalettes.push.apply(userPalettes, validated);
    closeImportForm();
    setActive(validated[0].id);
    persistPalettes();
  }

  // ------------------------------------------------------ export

  var exportSelected = {};
  function buildExportList() {
    if (userPalettes.length === 0) {
      exportSelectField.innerHTML = '<label>Select palettes</label><div class="export-empty">No custom palettes to export yet.<br>Create or import one first.</div>';
      exportOutputField.style.display = 'none';
      copyExportBtn.disabled = true;
      downloadExportBtn.disabled = true;
      return;
    }
    exportSelectField.innerHTML = '<label>Select palettes</label><div class="export-list" data-export-list><div class="select-all-row"><input type="checkbox" data-export-select-all> <span>All custom palettes</span></div><div data-export-items></div></div>';
    var itemsHost = exportSelectField.querySelector('[data-export-items]');
    var selectAllEl = exportSelectField.querySelector('[data-export-select-all]');
    exportOutputField.style.display = '';
    copyExportBtn.disabled = false;
    downloadExportBtn.disabled = false;
    userPalettes.forEach(function (pal) {
      var label = document.createElement('label');
      label.className = 'export-item';
      label.innerHTML = ['<input type="checkbox" data-id="' + pal.id + '"' + (exportSelected[pal.id] ? ' checked' : '') + '>', '<span class="ex-name">' + escHtml(pal.name) + '</span>', '<span class="mini-strip">' + SLOTS.map(function (s) {
        return '<span style="background:' + pal.colors[s] + '"></span>';
      }).join('') + '</span>'].join('');
      var cb = label.querySelector('input');
      cb.addEventListener('change', function () {
        if (cb.checked) exportSelected[pal.id] = true;else delete exportSelected[pal.id];
        syncSelectAll();
        updateExportOutput();
      });
      itemsHost.appendChild(label);
    });
    var count = Object.keys(exportSelected).length;
    selectAllEl.checked = count === userPalettes.length;
    selectAllEl.indeterminate = count > 0 && count < userPalettes.length;
    selectAllEl.onchange = function () {
      if (selectAllEl.checked) userPalettes.forEach(function (p) {
        exportSelected[p.id] = true;
      });else exportSelected = {};
      buildExportList();
      updateExportOutput();
    };
  }
  function syncSelectAll() {
    var sa = exportSelectField.querySelector('[data-export-select-all]');
    if (!sa) return;
    var count = Object.keys(exportSelected).length;
    sa.checked = count === userPalettes.length && userPalettes.length > 0;
    sa.indeterminate = count > 0 && count < userPalettes.length;
  }
  function buildExportJson() {
    var chosen = userPalettes.filter(function (p) {
      return exportSelected[p.id];
    });
    if (chosen.length === 0) return null;
    var payload = chosen.map(function (p) {
      return {
        name: p.name,
        colors: assign({}, p.colors)
      };
    });
    return JSON.stringify(payload.length === 1 ? payload[0] : payload, null, 2);
  }
  function updateExportOutput() {
    if (userPalettes.length === 0) return;
    var json = buildExportJson();
    if (!json) {
      exportOutput.textContent = '//  No palettes selected';
      copyExportBtn.disabled = true;
      downloadExportBtn.disabled = true;
    } else {
      exportOutput.textContent = json;
      copyExportBtn.disabled = false;
      downloadExportBtn.disabled = false;
    }
  }
  function openExportForm() {
    closeAddForm();
    closeImportForm();
    exportSelected = {};
    userPalettes.forEach(function (p) {
      exportSelected[p.id] = true;
    });
    copiedFlash.classList.remove('show');
    buildExportList();
    updateExportOutput();
    exportForm.classList.add('open');
  }
  function closeExportForm() {
    exportForm.classList.remove('open');
    copiedFlash.classList.remove('show');
  }
  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
    } catch (e) {}
    document.body.removeChild(ta);
  }
  function copyExportJson() {
    var json = buildExportJson();
    if (!json) return;
    var done = function () {
      copiedFlash.classList.add('show');
      setTimeout(function () {
        copiedFlash.classList.remove('show');
      }, 1600);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(json).then(done).catch(function () {
        fallbackCopy(json);
        done();
      });
    } else {
      fallbackCopy(json);
      done();
    }
  }
  function downloadExportJson() {
    var json = buildExportJson();
    if (!json) return;
    var chosen = userPalettes.filter(function (p) {
      return exportSelected[p.id];
    });
    var filename = chosen.length === 1 ? 'customify-palette-' + chosen[0].name.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '.json' : 'customify-palettes-' + chosen.length + '.json';
    var blob = new Blob([json], {
      type: 'application/json'
    });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function () {
      URL.revokeObjectURL(url);
    }, 1000);
  }

  // ------------------------------------------------------ popover

  var currentEditSlot = null;
  function openPopover(slot) {
    var pal = getActive();
    if (!pal) return;
    currentEditSlot = slot;
    popSlot.textContent = slot;
    popPicker.value = pal.colors[slot];
    popHex.textContent = pal.colors[slot].toUpperCase();
    popPreview.style.background = pal.colors[slot];
    popDesc.innerHTML = SLOT_DESC[slot] || '';
    popNote.style.display = activeKind === 'theme' ? 'block' : 'none';
    popover.classList.add('open');
  }
  function closePopover() {
    popover.classList.remove('open');
    currentEditSlot = null;
  }
  popPicker.addEventListener('input', function (e) {
    if (!currentEditSlot) return;
    var newHex = e.target.value.toUpperCase();
    if (activeKind === 'theme') {
      var src = getActive();
      var id = 'user_' + Date.now();
      var copy = {
        id: id,
        name: src.name + ' (copy)',
        colors: assign({}, src.colors)
      };
      copy.colors[currentEditSlot] = newHex;
      userPalettes.push(copy);
      activeId = id;
      activeKind = 'user';
      popNote.style.display = 'none';
      persistActive();
    } else {
      getActive().colors[currentEditSlot] = newHex;
    }
    popHex.textContent = newHex;
    popPreview.style.background = newHex;
    buildDeck(getActive(), false);
    buildThemeGrid();
    buildUserGrid();
    buildSettings();
    buildModalList();
    persistPalettes();
    applyColorVars();
    buildAutoComputed();
    logActive();
  });

  // ------------------------------------------------------ wiring

  $('[data-toggle-add]').addEventListener('click', function () {
    if (addForm.classList.contains('open')) closeAddForm();else openAddForm();
  });
  $('[data-cancel-add]').addEventListener('click', closeAddForm);
  $('[data-confirm-add]').addEventListener('click', confirmAdd);
  newName.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') confirmAdd();
  });
  $('[data-toggle-import]').addEventListener('click', function () {
    if (importForm.classList.contains('open')) closeImportForm();else openImportForm();
  });
  $('[data-cancel-import]').addEventListener('click', closeImportForm);
  $('[data-confirm-import]').addEventListener('click', validateAndImport);
  $('[data-paste-example]').addEventListener('click', function () {
    jsonInput.value = EXAMPLE_JSON;
    jsonError.classList.remove('show');
  });
  $('[data-toggle-export]').addEventListener('click', function () {
    if (exportForm.classList.contains('open')) closeExportForm();else openExportForm();
  });
  $('[data-cancel-export]').addEventListener('click', closeExportForm);
  copyExportBtn.addEventListener('click', copyExportJson);
  downloadExportBtn.addEventListener('click', downloadExportJson);
  $('[data-pop-close]').addEventListener('click', closePopover);
  $('[data-open-modal]').addEventListener('click', openModal);
  $('[data-close-modal]').addEventListener('click', closeModal);
  modalOverlay.addEventListener('click', function (e) {
    if (e.target === modalOverlay) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeModal();
      closePopover();
      closeAddForm();
      closeImportForm();
      closeExportForm();
    }
  });
  // Document-level click → close popover when clicking outside it.
  // e.target is retargeted to the host element for clicks inside shadow DOM,
  // so we use composedPath() to inspect the real path through the shadow
  // boundary and look for popover / deck-card / color-dot ancestors.
  document.addEventListener('click', function (e) {
    if (!popover.classList.contains('open')) return;
    var path = typeof e.composedPath === 'function' ? e.composedPath() : [];
    var hit = false;
    for (var i = 0; i < path.length; i++) {
      var n = path[i];
      if (n === popover) {
        hit = true;
        break;
      }
      if (n.classList && (n.classList.contains('deck-card') || n.classList.contains('color-dot'))) {
        hit = true;
        break;
      }
    }
    if (!hit) closePopover();
  });

  // Collapse / reopen — `.sb-back` hides the sidebar and reveals a small
  // floating button; clicking that re-expands it. State persists per-browser
  // via localStorage so the choice survives reloads.
  var COLLAPSE_KEY = 'customify_preview_colors_collapsed';
  function setCollapsed(v) {
    host.classList.toggle('cpc-closed', !!v);
    try {
      localStorage.setItem(COLLAPSE_KEY, v ? '1' : '0');
    } catch (e) {}
  }
  try {
    if (localStorage.getItem(COLLAPSE_KEY) === '1') host.classList.add('cpc-closed');
  } catch (e) {}
  $('.sb-back').addEventListener('click', function () {
    setCollapsed(true);
    closePopover();
  });
  $('[data-cpc-reopen]').addEventListener('click', function () {
    setCollapsed(false);
  });

  // ------------------------------------------------------ init

  var initPal = getActive();
  if (initPal) buildDeck(initPal, true);
  buildThemeGrid();
  buildUserGrid();
  buildSettings();
  buildModalList();
  applyColorVars();
  buildAutoComputed();
  logActive();
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
/* harmony import */ var _preview_colors_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(919);
/* harmony import */ var _preview_colors_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_preview_colors_js__WEBPACK_IMPORTED_MODULE_0__);
/*
 * Webpack entry for the Preview Colors module.
 *
 * Bundles SCSS + JS into:
 *   build/css/frontend/preview-colors.css (+ .min.css, -rtl.css, -rtl.min.css)
 *   build/js/frontend/preview-colors.js   (+ .min.js)
 */


}();
/******/ })()
;