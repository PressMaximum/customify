/******/ (function() { // webpackBootstrap
/******/ 	"use strict";

;// external ["wp","element"]
var external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external "ReactJSXRuntime"
var external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./src/backend/customizer/preview-colors/customizer.js
/* unused harmony import specifier */ var useMemo;
/* unused harmony import specifier */ var Fragment;
/* unused harmony import specifier */ var __;
/* unused harmony import specifier */ var _jsx;
/* unused harmony import specifier */ var _jsxs;
/*
 * Customify Preview Colors — Customizer control bundle (React).
 *
 * Mounted in light DOM directly inside a WP_Customize_Control container.
 * Uses @wordpress/element (React) for declarative state management; the
 * rendered markup keeps the same class names as the SCSS source so styles
 * work unchanged.
 *
 * State is synced bidirectionally with two `option`-type Customizer settings
 * (`customify_preview_user_palettes` + `customify_preview_active_palette`)
 * via `wp.customize(id).get()` / `.set()` / `.bind()`. `transport: 'postMessage'`
 * means the preview iframe receives the changes live (handled by
 * preview-colors-preview.js — that bundle rewrites :root CSS vars).
 *
 * Skipped vs the frontend overlay: no shadow DOM, no `.sb-header`, no
 * collapse / reopen chrome, no browse-all modal — the inline Customizer
 * layout doesn't use them.
 */




// ────────────────────────────────────────────────────────────────── helpers

function hexToRgbArray(hex) {
  let c = String(hex || '').replace('#', '');
  if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
  if (!/^[0-9A-Fa-f]{6}$/.test(c)) return null;
  return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)];
}
function hexToRgb(hex) {
  const rgb = hexToRgbArray(hex);
  return rgb ? rgb.join(', ') : null;
}
function luminance(rgb) {
  const f = v => {
    v /= 255;
    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
  };
  return 0.2126 * f(rgb[0]) + 0.7152 * f(rgb[1]) + 0.0722 * f(rgb[2]);
}
function pickOn(hex) {
  const rgb = hexToRgbArray(hex);
  if (!rgb) return '#FFFFFF';
  return luminance(rgb) > 0.45 ? '#1A1A1A' : '#FFFFFF';
}
function isLight(hex) {
  const rgb = hexToRgbArray(hex);
  if (!rgb) return true;
  return (0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2]) / 255 > 0.7;
}

// ─────────────────────── HSL helpers (for dark-mode auto-derivation) ───
//
// Mirrors PHP `Customify_Preview_Colors_Dark::rgb_to_hsl` / `hsl_to_rgb`
// so the live preview picks the same hex the server would render.

function rgbToHsl(rgb) {
  const r = rgb[0] / 255,
    g = rgb[1] / 255,
    b = rgb[2] / 255;
  const max = Math.max(r, g, b),
    min = Math.min(r, g, b);
  const l = (max + min) / 2;
  if (max === min) return [0, 0, l * 100];
  const d = max - min;
  const s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
  let h;
  switch (max) {
    case r:
      h = (g - b) / d + (g < b ? 6 : 0);
      break;
    case g:
      h = (b - r) / d + 2;
      break;
    default:
      h = (r - g) / d + 4;
  }
  h *= 60;
  return [h, s * 100, l * 100];
}
function hslToRgb(hsl) {
  const h = hsl[0] / 360,
    s = hsl[1] / 100,
    l = hsl[2] / 100;
  if (s === 0) return [l * 255, l * 255, l * 255];
  const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
  const p = 2 * l - q;
  const hue = t => {
    if (t < 0) t += 1;
    if (t > 1) t -= 1;
    if (t < 1 / 6) return p + (q - p) * 6 * t;
    if (t < 1 / 2) return q;
    if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
    return p;
  };
  return [hue(h + 1 / 3) * 255, hue(h) * 255, hue(h - 1 / 3) * 255];
}
function rgbArrToHex(rgb) {
  const c = v => {
    const n = Math.max(0, Math.min(255, Math.round(v))).toString(16);
    return n.length === 1 ? '0' + n : n;
  };
  return '#' + c(rgb[0]).toUpperCase() + c(rgb[1]).toUpperCase() + c(rgb[2]).toUpperCase();
}
const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));

/**
 * HSL-based per-slot derivation. Matches PHP `derive_slot()` (PHASE-7 §5).
 * `palette` is passed for slots that need cross-slot context (`surface`
 * looks at `base`; `secondary` compares luminance against `surface`).
 */
function deriveDarkSlot(slot, srcHex, palette) {
  const rgb = hexToRgbArray(srcHex);
  if (!rgb) return srcHex;
  let [h, s, l] = rgbToHsl(rgb);
  switch (slot) {
    case 'base':
      l = clamp(100 - l, 5, 12);
      break;
    case 'text':
      l = clamp(100 - l, 88, 96);
      s = Math.min(s, 30);
      break;
    case 'surface':
      {
        const baseSrc = palette?.colors?.base;
        const baseDark = baseSrc ? deriveDarkSlot('base', baseSrc, palette) : '#0B0D10';
        const baseRgb = hexToRgbArray(baseDark);
        const [bh, bs, bl] = rgbToHsl(baseRgb);
        h = bh;
        s = bs;
        l = Math.min(bl + 6, 18);
        break;
      }
    case 'primary':
      l = clamp(l, 55, 70);
      break;
    case 'secondary':
      {
        const surfHex = palette?.colors?.surface || '#FFFFFF';
        const surfRgb = hexToRgbArray(surfHex);
        const lumSrc = luminance(rgb);
        const lumSurf = surfRgb ? luminance(surfRgb) : 1;
        if (lumSrc < lumSurf) {
          l = clamp(100 - l, 80, 95);
          s = Math.max(s - 10, 0);
        } else {
          l = Math.max(l - 10, 20);
        }
        break;
      }
    case 'accent':
      s = Math.min(s + 10, 95);
      l = clamp(l, 55, 80);
      break;
  }
  return rgbArrToHex(hslToRgb([h, s, l]));
}

/**
 * 5-tier resolve chain (PHASE-7 §10). Mirrors PHP `resolve_slot()`.
 * `cfg` is `window.CustomifyPreviewColors` — shipped via wp_localize_script.
 */
function resolveDarkSlot(slot, palette, cfg) {
  if (palette?.dark?.[slot]) return palette.dark[slot];
  if (palette?.colors?.[slot]) {
    return deriveDarkSlot(slot, palette.colors[slot], palette);
  }
  const legacyMap = {
    text: 'text',
    primary: 'primary',
    secondary: 'secondary'
  };
  const legacyKey = legacyMap[slot];
  if (legacyKey && cfg?.legacyMods?.[legacyKey]) {
    return deriveDarkSlot(slot, cfg.legacyMods[legacyKey], palette);
  }
  const baselines = cfg?.darkBaselines || {};
  if (baselines.scss?.[slot]) return baselines.scss[slot];
  if (baselines.hex?.[slot]) return baselines.hex[slot];
  return '#000000';
}

// ────────────────────────────────────────────────────────────────── hooks

/**
 * Bridge a wp.customize() setting ↔ React state. The hook reads the initial
 * value via .get(), subscribes to .bind() so external changes (e.g. preview
 * iframe via postMessage) re-render, and exposes a setter that writes back
 * with .set().
 */
function useCustomizeSetting(settingId, defaultValue) {
  const [value, setValue] = (0,external_wp_element_namespaceObject.useState)(() => {
    const s = window.wp?.customize?.(settingId);
    const v = s && typeof s.get === 'function' ? s.get() : undefined;
    return v === undefined || v === null ? defaultValue : v;
  });
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const s = window.wp?.customize?.(settingId);
    if (!s || typeof s.bind !== 'function') return undefined;
    const handler = newVal => setValue(newVal);
    s.bind(handler);
    return () => {
      if (typeof s.unbind === 'function') s.unbind(handler);
    };
  }, [settingId]);
  const set = (0,external_wp_element_namespaceObject.useCallback)(newVal => {
    const s = window.wp?.customize?.(settingId);
    if (s && typeof s.set === 'function') s.set(newVal);
  }, [settingId]);
  return [value, set];
}

/**
 * Mirror the active palette onto :root as the Style-Pack-aligned token set.
 *
 * Emits BOTH the light and dark var sets every render so the trigger block
 * in the override CSS (`.dark-mode { … }`) can rebind elements that opt in
 * via the trigger class without an extra round trip from the iframe.
 *
 * Six user-picked slots (hex + rgb triplet) plus eight auto-computed
 * companions per mode (on-*, text-muted/subtle, border-default,
 * primary-hover/subtle).
 */
function useColorVars(palette, slots) {
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!palette || !palette.colors) return;
    const root = document.documentElement;
    const cfg = window.CustomifyPreviewColors || {};

    // Resolve dark companion via the shared 5-tier chain.
    const dark = {};
    slots.forEach(slot => {
      dark[slot] = resolveDarkSlot(slot, palette, cfg);
    });

    // 1) Light + dark slot vars.
    slots.forEach(slot => {
      const lightHex = palette.colors[slot];
      if (lightHex) root.style.setProperty(`--customify-color-${slot}`, lightHex);
      const darkHex = dark[slot];
      if (darkHex) root.style.setProperty(`--customify-color-${slot}-dark`, darkHex);
    });

    // 2) Auto-computed (light) — color-mix replaces rgba(rgb-triplet, alpha).
    ['primary', 'secondary', 'surface'].forEach(s => {
      if (palette.colors[s]) root.style.setProperty(`--customify-color-on-${s}`, pickOn(palette.colors[s]));
    });
    if (palette.colors.text) {
      const t = palette.colors.text;
      root.style.setProperty('--customify-color-text-muted', `color-mix(in srgb, ${t} 55%, transparent)`);
      root.style.setProperty('--customify-color-text-subtle', `color-mix(in srgb, ${t} 35%, transparent)`);
      root.style.setProperty('--customify-color-border-default', `color-mix(in srgb, ${t} 12%, transparent)`);
    }
    if (palette.colors.primary) {
      root.style.setProperty('--customify-color-primary-hover', `color-mix(in srgb, ${palette.colors.primary}, #000 15%)`);
      if (palette.colors.base) {
        root.style.setProperty('--customify-color-primary-subtle', `color-mix(in srgb, ${palette.colors.primary}, ${palette.colors.base} 92%)`);
      }
    }

    // 2b) Auto-computed (dark).
    ['primary', 'secondary', 'surface'].forEach(s => {
      if (dark[s]) root.style.setProperty(`--customify-color-on-${s}-dark`, pickOn(dark[s]));
    });
    if (dark.text) {
      const dt = dark.text;
      root.style.setProperty('--customify-color-text-muted-dark', `color-mix(in srgb, ${dt} 55%, transparent)`);
      root.style.setProperty('--customify-color-text-subtle-dark', `color-mix(in srgb, ${dt} 35%, transparent)`);
      root.style.setProperty('--customify-color-border-default-dark', `color-mix(in srgb, ${dt} 12%, transparent)`);
    }
    if (dark.primary) {
      root.style.setProperty('--customify-color-primary-hover-dark', `color-mix(in srgb, ${dark.primary}, #fff 12%)`);
      if (dark.base) {
        root.style.setProperty('--customify-color-primary-subtle-dark', `color-mix(in srgb, ${dark.primary}, ${dark.base} 92%)`);
      }
    }
  }, [palette, slots]);
}

// ─────────────────────────────────────────────────────────────── icons

const Icon = ({
  d,
  size = 14,
  sw = 1.5
}) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("svg", {
  width: size,
  height: size,
  viewBox: "0 0 14 14",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: sw,
  strokeLinecap: "round",
  strokeLinejoin: "round",
  children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
    d: d
  })
});
const IconExport = () => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
  d: "M7 12V5M4 8l3-3 3 3M2.5 2.5h9"
});
const IconImport = () => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
  d: "M7 2v7M4 6l3 3 3-3M2.5 11.5h9"
});
const IconAdd = () => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
  d: "M7 3v8M3 7h8",
  sw: 1.6
});
const IconReset = () => /*#__PURE__*/_jsx(Icon, {
  d: "M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10"
});
const IconClose = ({
  size = 10
}) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("svg", {
  width: size,
  height: size,
  viewBox: "0 0 14 14",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
    d: "M3 3l8 8M11 3l-8 8"
  })
});
const IconGlobe = () => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("svg", {
  className: "customify-globe",
  viewBox: "0 0 12 12",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "1.2",
  children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
    cx: "6",
    cy: "6",
    r: "4.5"
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
    d: "M1.5 6h9M6 1.5c1.5 1.5 2.2 3 2.2 4.5S7.5 9 6 10.5M6 1.5C4.5 3 3.8 4.5 3.8 6S4.5 9 6 10.5"
  })]
});

// ─────────────────────────────────────────────────────────── HeroDeck

const ANGLES = [-20, -12, -4, 4, 12, 20];
const OFFSETS = [-60, -36, -12, 12, 36, 60];
function HeroDeck({
  palette,
  slots,
  onSlotClick
}) {
  const colors = palette.colors;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "customify-deck-wrap",
    children: slots.map((slot, i) => {
      const c = colors[slot];
      if (!c) return null;
      const tx = OFFSETS[i] + 'px';
      const r = ANGLES[i] + 'deg';
      const style = {
        '--tx': tx,
        '--r': r,
        '--base-transform': `translateX(${tx}) rotate(${r})`,
        transform: `translateX(${tx}) rotate(${r})`,
        zIndex: i + 1,
        background: c,
        animationDelay: `${i * 40}ms`
      };
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: 'customify-deck-card is-animating' + (!isLight(c) ? ' is-dark' : ''),
        style: style,
        title: `${slot} · ${c.toUpperCase()}`,
        onClick: e => {
          e.stopPropagation();
          onSlotClick(slot);
        },
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "customify-slot-chip",
          children: slot
        })
      }, slot);
    })
  });
}

// ─────────────────────────────────────────────────────────── Popover

function Popover({
  slot,
  palette,
  kind,
  slotDesc,
  onChange,
  onClose
}) {
  const hex = palette.colors[slot] || '#000000';
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-popover is-open",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-popover-head",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        children: slot
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        className: "customify-modal-close",
        type: "button",
        style: {
          width: 20,
          height: 20
        },
        onClick: onClose,
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconClose, {})
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
      className: "customify-popover-preview",
      style: {
        background: hex
      },
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
        type: "color",
        value: hex,
        onChange: e => onChange(e.target.value.toUpperCase())
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-popover-hex",
      children: hex.toUpperCase()
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-popover-desc",
      dangerouslySetInnerHTML: {
        __html: slotDesc[slot] || ''
      }
    })]
  });
}

// ─────────────────────────────────────────────────────────── PresetCard / Grid

function PresetCard({
  palette,
  kind,
  isActive,
  slots,
  onPick,
  onDelete,
  onRename
}) {
  const c = palette.colors;
  const isUser = kind === 'user';
  const [editing, setEditing] = (0,external_wp_element_namespaceObject.useState)(false);
  const [draft, setDraft] = (0,external_wp_element_namespaceObject.useState)(palette.name);
  const inputRef = (0,external_wp_element_namespaceObject.useRef)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (editing && inputRef.current) inputRef.current.select();
  }, [editing]);

  // Reset draft if the upstream name changes (e.g. import overwrites).
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!editing) setDraft(palette.name);
  }, [palette.name, editing]);
  const startEdit = e => {
    if (!isUser || !onRename) return;
    e.stopPropagation();
    setDraft(palette.name);
    setEditing(true);
  };
  const commit = () => {
    setEditing(false);
    const trimmed = draft.trim();
    if (trimmed && trimmed !== palette.name && onRename) onRename(palette.id, trimmed);
  };
  const cancel = () => {
    setDraft(palette.name);
    setEditing(false);
  };
  const className = 'customify-preset-card' + (isActive ? ' is-active' : '') + (isUser ? ' is-user' : '');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: className,
    onClick: e => {
      if (e.target.closest('.customify-del')) return;
      if (e.target.closest('.customify-preset-name-edit')) return;
      onPick(palette.id);
    },
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("p", {
      className: "customify-preset-name",
      children: [editing ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
        ref: inputRef,
        className: "customify-preset-name-edit",
        type: "text",
        value: draft,
        maxLength: 60,
        onChange: e => setDraft(e.target.value),
        onBlur: commit,
        onKeyDown: e => {
          if (e.key === 'Enter') {
            e.preventDefault();
            e.target.blur();
          } else if (e.key === 'Escape') {
            e.preventDefault();
            cancel();
          }
        },
        onClick: e => e.stopPropagation()
      }) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: isUser && onRename ? 'customify-preset-name-text is-editable' : 'customify-preset-name-text',
        title: isUser && onRename ? (0,external_wp_i18n_namespaceObject.__)('Click to rename', 'customify') : undefined,
        onClick: startEdit,
        children: palette.name
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "customify-slot-count",
        children: slots.length
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-palette-strip",
      children: slots.map(s => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-chip",
        style: {
          background: c[s]
        },
        title: s
      }, s))
    }), isUser && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      className: "customify-del",
      type: "button",
      "aria-label": (0,external_wp_i18n_namespaceObject.__)('Delete palette', 'customify'),
      title: (0,external_wp_i18n_namespaceObject.__)('Delete', 'customify'),
      onClick: e => {
        e.stopPropagation();
        onDelete(palette.id);
      },
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconClose, {})
    })]
  });
}
const SHADOW_PREFIX_GRID = 'user_theme_';
function PresetGrid({
  palettes,
  kind,
  activeId,
  slots,
  onPick,
  onDelete,
  onRename
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "customify-preset-grid",
    children: palettes.map(p => {
      // A theme-preset card is considered active when either the preset
      // itself or its shadow palette is the current active palette.
      const isActive = kind === 'theme' ? p.id === activeId || SHADOW_PREFIX_GRID + p.id === activeId : p.id === activeId;
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(PresetCard, {
        palette: p,
        kind: kind,
        isActive: isActive,
        slots: slots,
        onPick: onPick,
        onDelete: onDelete,
        onRename: onRename
      }, p.id);
    })
  });
}

// ─────────────────────────────────────────────────────────── AddForm

function AddForm({
  allPalettes,
  themePresets,
  defaultExtendId,
  userCount,
  onConfirm,
  onCancel
}) {
  const [name, setName] = (0,external_wp_element_namespaceObject.useState)('');
  const [extendFrom, setExtendFrom] = (0,external_wp_element_namespaceObject.useState)(defaultExtendId);
  const inputRef = (0,external_wp_element_namespaceObject.useRef)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    setTimeout(() => inputRef.current?.focus(), 100);
  }, []);
  const submit = () => {
    // translators: %d is the next sequential number for the palette
    const finalName = name.trim() || (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Palette %d', 'customify'), userCount + 1);
    onConfirm(finalName, extendFrom);
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-add-form is-open",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-form-title",
      children: (0,external_wp_i18n_namespaceObject.__)('Create new palette', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
        children: (0,external_wp_i18n_namespaceObject.__)('Palette title', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
        ref: inputRef,
        type: "text",
        value: name,
        placeholder: (0,external_wp_i18n_namespaceObject.__)('e.g. My brand', 'customify'),
        autoComplete: "off",
        onChange: e => setName(e.target.value),
        onKeyDown: e => {
          if (e.key === 'Enter') submit();
        }
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
        children: (0,external_wp_i18n_namespaceObject.__)('Extend from', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("select", {
        value: extendFrom,
        onChange: e => setExtendFrom(e.target.value),
        children: allPalettes.map(p => {
          const isTheme = themePresets.some(t => t.id === p.id);
          const tag = isTheme ? (0,external_wp_i18n_namespaceObject.__)('theme', 'customify') : (0,external_wp_i18n_namespaceObject.__)('user', 'customify');
          return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("option", {
            value: p.id,
            children: `${p.name} (${tag})`
          }, p.id);
        })
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-actions",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        className: "customify-btn--cancel",
        type: "button",
        onClick: onCancel,
        children: (0,external_wp_i18n_namespaceObject.__)('Cancel', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        className: "customify-btn--add",
        type: "button",
        onClick: submit,
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconAdd, {}), " ", (0,external_wp_i18n_namespaceObject.__)('Add', 'customify')]
      })]
    })]
  });
}

// ─────────────────────────────────────────────────────────── ImportForm

const EXAMPLE_JSON = JSON.stringify([{
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
function ImportForm({
  slots,
  onConfirm,
  onCancel
}) {
  const [text, setText] = (0,external_wp_element_namespaceObject.useState)('');
  const [error, setError] = (0,external_wp_element_namespaceObject.useState)('');
  const inputRef = (0,external_wp_element_namespaceObject.useRef)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    setTimeout(() => inputRef.current?.focus(), 100);
  }, []);
  const submit = () => {
    const raw = text.trim();
    if (!raw) {
      setError((0,external_wp_i18n_namespaceObject.__)('Paste JSON code first.', 'customify'));
      return;
    }
    let parsed;
    try {
      parsed = JSON.parse(raw);
    } catch (err) {
      setError((0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Invalid JSON: %s', 'customify'), err.message));
      return;
    }
    const items = Array.isArray(parsed) ? parsed : [parsed];
    const validated = [];
    for (let i = 0; i < items.length; i++) {
      const it = items[i];
      if (!it || typeof it !== 'object') {
        return setError((0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Item %d: must be an object.', 'customify'), i + 1));
      }
      if (!it.name || typeof it.name !== 'string') {
        return setError((0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Item %d: missing "name" field.', 'customify'), i + 1));
      }
      if (!it.colors || typeof it.colors !== 'object') {
        return setError((0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Item %d: missing "colors" object.', 'customify'), i + 1));
      }
      const colors = {};
      for (const slot of slots) {
        const v = it.colors[slot];
        if (!v || typeof v !== 'string' || !/^#([0-9A-F]{3}|[0-9A-F]{6})$/i.test(v.trim())) {
          return setError((0,external_wp_i18n_namespaceObject.sprintf)(/* translators: 1: item index, 2: palette name, 3: slot key */
          (0,external_wp_i18n_namespaceObject.__)('Item %1$d ("%2$s"): slot "%3$s" missing or not a valid hex.', 'customify'), i + 1, it.name, slot));
        }
        colors[slot] = v.trim().toUpperCase();
      }
      validated.push({
        name: it.name.trim(),
        colors
      });
    }
    onConfirm(validated);
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-add-form is-open",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-title",
      children: [(0,external_wp_i18n_namespaceObject.__)('Import palette(s) from JSON', 'customify'), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        className: "customify-paste-example",
        type: "button",
        onClick: () => {
          setText(EXAMPLE_JSON);
          setError('');
        },
        children: (0,external_wp_i18n_namespaceObject.__)('Use example', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
        children: (0,external_wp_i18n_namespaceObject.__)('Paste JSON', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("textarea", {
        ref: inputRef,
        value: text,
        spellCheck: false,
        onChange: e => setText(e.target.value)
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "customify-form-hint",
        children: (0,external_wp_i18n_namespaceObject.__)('Accepts a single palette object or an array. Requires "name" + all 6 slots.', 'customify')
      }), error && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-form-error is-show",
        children: error
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-actions",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        className: "customify-btn--cancel",
        type: "button",
        onClick: onCancel,
        children: (0,external_wp_i18n_namespaceObject.__)('Cancel', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        className: "customify-btn--add",
        type: "button",
        onClick: submit,
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconImport, {}), " ", (0,external_wp_i18n_namespaceObject.__)('Import', 'customify')]
      })]
    })]
  });
}

// ─────────────────────────────────────────────────────────── ExportForm

function ExportForm({
  palettes,
  slots,
  onClose
}) {
  const [selected, setSelected] = (0,external_wp_element_namespaceObject.useState)(() => new Set(palettes.map(p => p.id)));
  const [copied, setCopied] = (0,external_wp_element_namespaceObject.useState)(false);
  const json = (0,external_wp_element_namespaceObject.useMemo)(() => {
    const chosen = palettes.filter(p => selected.has(p.id));
    if (!chosen.length) return null;
    const payload = chosen.map(p => ({
      name: p.name,
      colors: {
        ...p.colors
      }
    }));
    return JSON.stringify(payload.length === 1 ? payload[0] : payload, null, 2);
  }, [palettes, selected]);
  const toggle = id => {
    setSelected(prev => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);else next.add(id);
      return next;
    });
  };
  const toggleAll = checked => {
    setSelected(checked ? new Set(palettes.map(p => p.id)) : new Set());
  };
  const doCopy = () => {
    if (!json) return;
    const fallback = () => {
      const ta = document.createElement('textarea');
      ta.value = json;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand('copy');
      } catch (e) {}
      document.body.removeChild(ta);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(json).then(() => null).catch(fallback);
    } else {
      fallback();
    }
    setCopied(true);
    setTimeout(() => setCopied(false), 1600);
  };
  const doDownload = () => {
    if (!json) return;
    const chosen = palettes.filter(p => selected.has(p.id));
    const filename = chosen.length === 1 ? `customify-palette-${chosen[0].name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.json` : `customify-palettes-${chosen.length}.json`;
    const blob = new Blob([json], {
      type: 'application/json'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  };
  if (palettes.length === 0) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-add-form is-open",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-form-title",
        children: (0,external_wp_i18n_namespaceObject.__)('Export custom palettes', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "customify-form-field",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
          children: (0,external_wp_i18n_namespaceObject.__)('Select palettes', 'customify')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "customify-export-empty",
          children: (0,external_wp_i18n_namespaceObject.__)('No custom palettes to export yet. Create or import one first.', 'customify')
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-form-actions",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
          className: "customify-btn--cancel",
          type: "button",
          onClick: onClose,
          children: (0,external_wp_i18n_namespaceObject.__)('Close', 'customify')
        })
      })]
    });
  }
  const allChecked = selected.size === palettes.length;
  const someChecked = selected.size > 0 && selected.size < palettes.length;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-add-form is-open",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-form-title",
      children: (0,external_wp_i18n_namespaceObject.__)('Export custom palettes', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
        children: (0,external_wp_i18n_namespaceObject.__)('Select palettes', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "customify-export-list",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "customify-select-all",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
            type: "checkbox",
            checked: allChecked,
            ref: el => {
              if (el) el.indeterminate = someChecked;
            },
            onChange: e => toggleAll(e.target.checked)
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
            children: (0,external_wp_i18n_namespaceObject.__)('All custom palettes', 'customify')
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          children: palettes.map(p => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("label", {
            className: "customify-export-item",
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
              type: "checkbox",
              checked: selected.has(p.id),
              onChange: () => toggle(p.id)
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
              className: "customify-ex-name",
              children: p.name
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
              className: "customify-mini-strip",
              children: slots.map(s => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
                style: {
                  background: p.colors[s]
                }
              }, s))
            })]
          }, p.id))
        })]
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
        children: (0,external_wp_i18n_namespaceObject.__)('JSON output', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-export-output",
        children: json || (0,external_wp_i18n_namespaceObject.__)('//  No palettes selected', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "customify-form-hint",
        children: (0,external_wp_i18n_namespaceObject.__)('Re-importable via the ↓ button above or on another site.', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-form-actions",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: 'customify-copied-flash' + (copied ? ' is-show' : ''),
        children: (0,external_wp_i18n_namespaceObject.__)('✓ Copied', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        className: "customify-btn--cancel",
        type: "button",
        onClick: onClose,
        children: (0,external_wp_i18n_namespaceObject.__)('Close', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        className: "customify-btn--ghost",
        type: "button",
        disabled: !json,
        onClick: doDownload,
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconExport, {}), " ", (0,external_wp_i18n_namespaceObject.__)('Download', 'customify')]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        className: "customify-btn--add",
        type: "button",
        disabled: !json,
        onClick: doCopy,
        children: (0,external_wp_i18n_namespaceObject.__)('Copy', 'customify')
      })]
    })]
  });
}

// ─────────────────────────────────────────────────────────── SettingsRows

function SettingsRows({
  palette,
  settings,
  defaults,
  onSlotChange
}) {
  // Per-row reset reverts each slot in the row to its `defaults` value
  // (typically the first theme preset's colour for that slot — the
  // canonical default supplied by App).
  const resetRow = slotsToReset => {
    slotsToReset.forEach(slot => {
      const defaultHex = defaults[slot];
      if (!defaultHex) return;
      if (palette.colors[slot] === defaultHex) return;
      onSlotChange(slot, defaultHex);
    });
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    children: settings.map((cfg, i) => {
      const slotsArr = cfg.slots || [];
      const isAtDefault = slotsArr.every(s => palette.colors[s] === defaults[s]);
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "customify-setting-row",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "customify-label-wrap",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
            className: "customify-label",
            children: cfg.label
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
            className: "customify-sublabel",
            children: cfg.sublabel
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "customify-right",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
            className: "customify-reset-mini",
            type: "button",
            onClick: () => resetRow(slotsArr),
            title: (0,external_wp_i18n_namespaceObject.__)('Reset to default', 'customify'),
            "aria-label": (0,external_wp_i18n_namespaceObject.__)('Reset to default', 'customify'),
            disabled: isAtDefault,
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
              d: "M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10",
              size: 12
            })
          }), slotsArr.map(slotName => {
            const col = palette.colors[slotName];
            if (!col) return null;
            return (
              /*#__PURE__*/
              // Click anywhere on the dot opens the browser's
              // native colour picker (the input is layered on
              // top, fully transparent). The label keeps the
              // existing dot visual + globe icon.
              (0,external_ReactJSXRuntime_namespaceObject.jsxs)("label", {
                className: 'customify-color-dot' + (isLight(col) ? ' is-light' : ''),
                style: {
                  background: col
                },
                title: `${slotName} · ${col.toUpperCase()}`,
                children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
                  type: "color",
                  value: col,
                  onInput: e => onSlotChange(slotName, e.target.value.toUpperCase()),
                  onClick: e => e.stopPropagation()
                }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconGlobe, {})]
              }, slotName)
            );
          })]
        })]
      }, i);
    })
  });
}

// ─────────────────────────────────────────────────────────── AutoComputed

// Labels are wrapped in __() so the strings get extracted for translation
// even though the section is currently hidden in App() — re-enabling the
// render is just an uncomment.
const AUTO_COMPUTED_ROWS = [{
  slot: 'on-primary',
  label: (0,external_wp_i18n_namespaceObject.__)('Text on primary', 'customify')
}, {
  slot: 'on-secondary',
  label: (0,external_wp_i18n_namespaceObject.__)('Text on secondary', 'customify')
}, {
  slot: 'on-surface',
  label: (0,external_wp_i18n_namespaceObject.__)('Text on surface', 'customify')
}, {
  slot: 'text-muted',
  label: (0,external_wp_i18n_namespaceObject.__)('Muted text', 'customify')
}, {
  slot: 'text-subtle',
  label: (0,external_wp_i18n_namespaceObject.__)('Subtle text', 'customify')
}, {
  slot: 'border-default',
  label: (0,external_wp_i18n_namespaceObject.__)('Default border', 'customify')
}, {
  slot: 'primary-hover',
  label: (0,external_wp_i18n_namespaceObject.__)('Primary hover', 'customify')
}, {
  slot: 'primary-subtle',
  label: (0,external_wp_i18n_namespaceObject.__)('Primary subtle', 'customify')
}];
function AutoComputed({
  trigger
}) {
  // Read computed values from :root each time `trigger` (the active palette
  // signature) changes — useColorVars has already updated those vars in a
  // sibling effect.
  const rows = useMemo(() => {
    const rs = getComputedStyle(document.documentElement);
    return AUTO_COMPUTED_ROWS.map(row => ({
      ...row,
      value: rs.getPropertyValue(`--customify-color-${row.slot}`).trim() || '—'
    }));
  }, [trigger]);
  return /*#__PURE__*/_jsxs(Fragment, {
    children: [/*#__PURE__*/_jsx("div", {
      className: "customify-auto-hint",
      children: __('Theme generates these from the 6 slots above.', 'customify')
    }), /*#__PURE__*/_jsx("div", {
      children: rows.map(r => /*#__PURE__*/_jsxs("div", {
        className: "customify-auto-row",
        title: `${r.slot} · ${r.value}`,
        children: [/*#__PURE__*/_jsx("span", {
          className: "customify-auto-swatch",
          style: {
            background: r.value
          }
        }), /*#__PURE__*/_jsx("span", {
          className: "customify-auto-label",
          children: r.label
        }), /*#__PURE__*/_jsx("span", {
          className: "customify-auto-slot",
          children: r.slot
        })]
      }, r.slot))
    })]
  });
}

// ─────────────────────────────────────────────────────────── App

function App({
  cfg
}) {
  const [userPalettes, setUserPalettes] = useCustomizeSetting(cfg.settingIds.palettes, []);
  const [activeId, setActiveId] = useCustomizeSetting(cfg.settingIds.active, '');
  const [editSlot, setEditSlot] = (0,external_wp_element_namespaceObject.useState)(null);
  const [openForm, setOpenForm] = (0,external_wp_element_namespaceObject.useState)(null); // 'add' | 'import' | 'export' | null

  const slots = cfg.slots;
  const themePresets = cfg.themePresets || [];
  const userArr = Array.isArray(userPalettes) ? userPalettes : [];

  // Shadow palettes are auto-created when the user edits a theme-preset slot.
  // They use a stable ID prefix so edits accumulate on the same object instead
  // of spawning a new "(copy)" each time. They are kept out of the visible
  // Custom palettes grid — they're an implementation detail, not named palettes.
  const SHADOW_PREFIX = 'user_theme_';
  const visibleUserArr = (0,external_wp_element_namespaceObject.useMemo)(() => userArr.filter(p => !p.id.startsWith(SHADOW_PREFIX)), [userArr]);

  // allPalettes includes shadows so activePalette lookup always finds them.
  const allPalettes = (0,external_wp_element_namespaceObject.useMemo)(() => [...themePresets, ...userArr], [themePresets, userArr]);
  const activePalette = (0,external_wp_element_namespaceObject.useMemo)(() => {
    return allPalettes.find(p => p.id === activeId) || allPalettes[0] || null;
  }, [allPalettes, activeId]);
  const activeKind = (0,external_wp_element_namespaceObject.useMemo)(() => themePresets.some(p => p.id === activePalette?.id) ? 'theme' : 'user', [themePresets, activePalette]);
  useColorVars(activePalette, slots);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (activePalette) {
      console.log('[Customify Preview Colors] Current palette:', {
        name: activePalette.name,
        colors: {
          ...activePalette.colors
        }
      });
    }
  }, [activePalette]);

  // Esc closes popover / open form.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const handler = e => {
      if (e.key === 'Escape') {
        setEditSlot(null);
        setOpenForm(null);
      }
    };
    document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, []);

  // Click outside popover closes it.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!editSlot) return undefined;
    const handler = e => {
      const path = e.composedPath ? e.composedPath() : [];
      for (const n of path) {
        if (!n || !n.classList) continue;
        if (n.classList.contains('customify-popover')) return;
        if (n.classList.contains('customify-deck-card')) return;
        if (n.classList.contains('customify-color-dot')) return;
      }
      setEditSlot(null);
    };
    document.addEventListener('click', handler);
    return () => document.removeEventListener('click', handler);
  }, [editSlot]);
  const editSlotColor = (0,external_wp_element_namespaceObject.useCallback)((slot, newHex) => {
    if (!activePalette) return;
    if (activeKind === 'theme') {
      // Use a stable shadow ID so repeated edits accumulate on the
      // same user palette rather than spawning a new copy each time.
      const shadowId = SHADOW_PREFIX + activePalette.id;
      const shadowExists = userArr.some(p => p.id === shadowId);
      if (shadowExists) {
        setUserPalettes(userArr.map(p => p.id === shadowId ? {
          ...p,
          colors: {
            ...p.colors,
            [slot]: newHex
          }
        } : p));
      } else {
        setUserPalettes([...userArr, {
          id: shadowId,
          name: activePalette.name,
          colors: {
            ...activePalette.colors,
            [slot]: newHex
          }
        }]);
      }
      setActiveId(shadowId);
    } else {
      setUserPalettes(userArr.map(p => p.id === activePalette.id ? {
        ...p,
        colors: {
          ...p.colors,
          [slot]: newHex
        }
      } : p));
    }
  }, [activePalette, activeKind, userArr, setUserPalettes, setActiveId]);
  const addPalette = (0,external_wp_element_namespaceObject.useCallback)((name, sourceId) => {
    const source = allPalettes.find(p => p.id === sourceId);
    if (!source) return;
    const id = 'user_' + Date.now();
    setUserPalettes([...userArr, {
      id,
      name,
      colors: {
        ...source.colors
      }
    }]);
    setActiveId(id);
    setOpenForm(null);
  }, [allPalettes, userArr, setUserPalettes, setActiveId]);

  // When clicking a theme preset that already has a shadow (i.e. the user
  // previously edited it), restore the shadow so edits are preserved instead
  // of reverting to the unmodified preset.
  const pickThemePreset = (0,external_wp_element_namespaceObject.useCallback)(themeId => {
    const shadowId = SHADOW_PREFIX + themeId;
    const hasShadow = userArr.some(p => p.id === shadowId);
    setActiveId(hasShadow ? shadowId : themeId);
  }, [userArr, setActiveId]);
  const deletePalette = (0,external_wp_element_namespaceObject.useCallback)(id => {
    setUserPalettes(userArr.filter(p => p.id !== id));
    if (activeId === id) {
      setActiveId(themePresets[0]?.id || '');
    }
  }, [userArr, setUserPalettes, activeId, setActiveId, themePresets]);
  const renamePalette = (0,external_wp_element_namespaceObject.useCallback)((id, newName) => {
    setUserPalettes(userArr.map(p => p.id === id ? {
      ...p,
      name: newName
    } : p));
  }, [userArr, setUserPalettes]);
  const importPalettes = (0,external_wp_element_namespaceObject.useCallback)(items => {
    const validated = items.map((item, i) => ({
      id: 'user_' + Date.now() + '_' + i,
      name: item.name,
      colors: {
        ...item.colors
      }
    }));
    setUserPalettes([...userArr, ...validated]);
    setActiveId(validated[0].id);
    setOpenForm(null);
  }, [userArr, setUserPalettes, setActiveId]);
  if (!activePalette) return null;
  const triggerKey = activePalette.id + '|' + slots.map(s => activePalette.colors[s]).join(',');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-sidebar",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h3", {
        className: "customify-control--heading",
        children: (0,external_wp_i18n_namespaceObject.__)('Current palette', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "customify-hero-card",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(HeroDeck, {
          palette: activePalette,
          slots: slots,
          onSlotClick: setEditSlot
        }, activePalette.id /* re-mount to retrigger animation on switch */), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "customify-deck-footer",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("span", {
            className: "customify-deck-name",
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
              children: activePalette.name
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
              className: 'customify-tag' + (activeKind === 'user' ? ' customify-tag--user' : ''),
              children: activeKind === 'theme' ? (0,external_wp_i18n_namespaceObject.__)('theme', 'customify') : (0,external_wp_i18n_namespaceObject.__)('user', 'customify')
            })]
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
            className: "customify-deck-sub",
            children: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %d is the number of color slots in a palette */
            (0,external_wp_i18n_namespaceObject.__)('%d slots', 'customify'), slots.length)
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "customify-deck-hint",
          children: (0,external_wp_i18n_namespaceObject.__)('Click a card to edit that slot', 'customify')
        }), editSlot && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Popover, {
          slot: editSlot,
          palette: activePalette,
          kind: activeKind,
          slotDesc: cfg.slotDesc || {},
          onChange: hex => editSlotColor(editSlot, hex),
          onClose: () => setEditSlot(null)
        })]
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h3", {
        className: "customify-control--heading",
        children: (0,external_wp_i18n_namespaceObject.__)('Theme presets', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(PresetGrid, {
        palettes: themePresets,
        kind: "theme",
        activeId: activeId,
        slots: slots,
        onPick: pickThemePreset
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("h3", {
        className: "customify-control--heading",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("span", {
          children: [(0,external_wp_i18n_namespaceObject.__)('Custom palettes', 'customify'), " ", /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
            className: "customify-badge",
            children: visibleUserArr.length
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("span", {
          style: {
            display: 'flex',
            gap: 4
          },
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
            className: "customify-icon-btn",
            type: "button",
            "aria-label": (0,external_wp_i18n_namespaceObject.__)('Export palettes', 'customify'),
            title: (0,external_wp_i18n_namespaceObject.__)('Export JSON', 'customify'),
            onClick: () => setOpenForm(openForm === 'export' ? null : 'export'),
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconExport, {})
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
            className: "customify-icon-btn",
            type: "button",
            "aria-label": (0,external_wp_i18n_namespaceObject.__)('Import palette', 'customify'),
            title: (0,external_wp_i18n_namespaceObject.__)('Import JSON', 'customify'),
            onClick: () => setOpenForm(openForm === 'import' ? null : 'import'),
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconImport, {})
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
            className: "customify-icon-btn",
            type: "button",
            "aria-label": (0,external_wp_i18n_namespaceObject.__)('Add palette', 'customify'),
            title: (0,external_wp_i18n_namespaceObject.__)('Add new palette', 'customify'),
            onClick: () => setOpenForm(openForm === 'add' ? null : 'add'),
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(IconAdd, {})
          })]
        })]
      }), visibleUserArr.length === 0 ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "customify-empty",
        children: [(0,external_wp_i18n_namespaceObject.__)('No custom palettes yet.', 'customify'), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("br", {}), (0,external_wp_i18n_namespaceObject.__)('Click + to create one, or ↓ to import JSON.', 'customify')]
      }) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(PresetGrid, {
        palettes: visibleUserArr,
        kind: "user",
        activeId: activeId,
        slots: slots,
        onPick: setActiveId,
        onDelete: deletePalette,
        onRename: renamePalette
      }), openForm === 'add' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(AddForm, {
        allPalettes: [...themePresets, ...visibleUserArr],
        themePresets: themePresets,
        defaultExtendId: activeId,
        userCount: visibleUserArr.length,
        onConfirm: addPalette,
        onCancel: () => setOpenForm(null)
      }), openForm === 'import' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ImportForm, {
        slots: slots,
        onConfirm: importPalettes,
        onCancel: () => setOpenForm(null)
      }), openForm === 'export' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ExportForm, {
        palettes: visibleUserArr,
        slots: slots,
        onClose: () => setOpenForm(null)
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
        className: "customify-control--heading",
        children: (0,external_wp_i18n_namespaceObject.__)('Theme color', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SettingsRows, {
        palette: activePalette,
        settings: cfg.settingsRows || [],
        defaults: themePresets[0]?.colors || {},
        onSlotChange: editSlotColor
      })]
    })]
  });
}

// ─────────────────────────────────────────────────────────── Mount

(function () {
  const cfg = window.CustomifyPreviewColors;
  if (!cfg) return;
  let mounted = false;
  function tryMount() {
    if (mounted) return true;
    const host = document.getElementById(cfg.rootId);
    if (!host) return false;
    mounted = true;
    const root = (0,external_wp_element_namespaceObject.createRoot)(host);
    root.render(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(App, {
      cfg: cfg
    }));
    return true;
  }
  if (!tryMount() && typeof MutationObserver === 'function') {
    const observer = new MutationObserver(() => {
      if (tryMount()) observer.disconnect();
    });
    observer.observe(document.body || document.documentElement, {
      childList: true,
      subtree: true
    });
  }
})();
;// ./src/backend/customizer/preview-colors/index.js
/*
 * Webpack entry for the Customizer-only Color palette control.
 *
 * Bundles a clone of the frontend overlay's JS + SCSS, restyled to match
 * the WP Customizer's native chrome. Independent from the frontend bundle:
 * touching this version does not affect `?preview-colors=1`, and vice versa.
 *
 * Output:
 *   build/js/backend/customizer/preview-colors.js   (+ .min.js)
 *   build/css/backend/customizer/preview-colors.css (+ .min.css, -rtl variants)
 */


/******/ })()
;