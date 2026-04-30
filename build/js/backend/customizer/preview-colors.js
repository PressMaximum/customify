/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/backend/customizer/preview-colors/customizer.js":
/*!*************************************************************!*\
  !*** ./src/backend/customizer/preview-colors/customizer.js ***!
  \*************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);
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

// ────────────────────────────────────────────────────────────────── hooks

/**
 * Bridge a wp.customize() setting ↔ React state. The hook reads the initial
 * value via .get(), subscribes to .bind() so external changes (e.g. preview
 * iframe via postMessage) re-render, and exposes a setter that writes back
 * with .set().
 */
function useCustomizeSetting(settingId, defaultValue) {
  const [value, setValue] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(() => {
    const s = window.wp?.customize?.(settingId);
    const v = s && typeof s.get === 'function' ? s.get() : undefined;
    return v === undefined || v === null ? defaultValue : v;
  });
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const s = window.wp?.customize?.(settingId);
    if (!s || typeof s.bind !== 'function') return undefined;
    const handler = newVal => setValue(newVal);
    s.bind(handler);
    return () => {
      if (typeof s.unbind === 'function') s.unbind(handler);
    };
  }, [settingId]);
  const set = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(newVal => {
    const s = window.wp?.customize?.(settingId);
    if (s && typeof s.set === 'function') s.set(newVal);
  }, [settingId]);
  return [value, set];
}

/**
 * Mirror the active palette onto :root as the Style-Pack-aligned token set.
 *
 * Six user-picked slots plus eight auto-computed companions (on-*,
 * text-muted/subtle, border-default, primary-hover/subtle).
 */
function useColorVars(palette, slots) {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!palette || !palette.colors) return;
    const root = document.documentElement;

    // 1) Slot vars.
    slots.forEach(slot => {
      const lightHex = palette.colors[slot];
      if (lightHex) root.style.setProperty(`--customify-color-${slot}`, lightHex);
    });

    // 2) Auto-computed — color-mix replaces rgba(rgb-triplet, alpha).
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
  }, [palette, slots]);
}

// ─────────────────────────────────────────────────────────────── icons

const Icon = ({
  d,
  size = 14,
  sw = 1.5
}) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("svg", {
  width: size,
  height: size,
  viewBox: "0 0 14 14",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: sw,
  strokeLinecap: "round",
  strokeLinejoin: "round",
  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("path", {
    d: d
  })
});
const IconExport = () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(Icon, {
  d: "M7 12V5M4 8l3-3 3 3M2.5 2.5h9"
});
const IconImport = () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(Icon, {
  d: "M7 2v7M4 6l3 3 3-3M2.5 11.5h9"
});
const IconAdd = () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(Icon, {
  d: "M7 3v8M3 7h8",
  sw: 1.6
});
const IconReset = () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(Icon, {
  d: "M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10"
});
const IconClose = ({
  size = 10
}) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("svg", {
  width: size,
  height: size,
  viewBox: "0 0 14 14",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "2",
  strokeLinecap: "round",
  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("path", {
    d: "M3 3l8 8M11 3l-8 8"
  })
});
const IconGlobe = () => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("svg", {
  className: "customify-globe",
  viewBox: "0 0 12 12",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: "1.2",
  children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("circle", {
    cx: "6",
    cy: "6",
    r: "4.5"
  }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("path", {
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
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
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: 'customify-deck-card is-animating' + (!isLight(c) ? ' is-dark' : ''),
        style: style,
        title: `${slot} · ${c.toUpperCase()}`,
        onClick: e => {
          e.stopPropagation();
          onSlotClick(slot);
        },
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "customify-popover is-open",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-popover-head",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
        children: slot
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        className: "customify-modal-close",
        type: "button",
        style: {
          width: 20,
          height: 20
        },
        onClick: onClose,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconClose, {})
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
      className: "customify-popover-preview",
      style: {
        background: hex
      },
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
        type: "color",
        value: hex,
        onChange: e => onChange(e.target.value.toUpperCase())
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "customify-popover-hex",
      children: hex.toUpperCase()
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
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
  const [editing, setEditing] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [draft, setDraft] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(palette.name);
  const inputRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (editing && inputRef.current) inputRef.current.select();
  }, [editing]);

  // Reset draft if the upstream name changes (e.g. import overwrites).
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: className,
    onClick: e => {
      if (e.target.closest('.customify-del')) return;
      if (e.target.closest('.customify-preset-name-edit')) return;
      onPick(palette.id);
    },
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("p", {
      className: "customify-preset-name",
      children: [editing ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
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
      }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
        className: isUser && onRename ? 'customify-preset-name-text is-editable' : 'customify-preset-name-text',
        title: isUser && onRename ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Click to rename', 'customify') : undefined,
        onClick: startEdit,
        children: palette.name
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
        className: "customify-slot-count",
        children: slots.length
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "customify-palette-strip",
      children: slots.map(s => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: "customify-chip",
        style: {
          background: c[s]
        },
        title: s
      }, s))
    }), isUser && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
      className: "customify-del",
      type: "button",
      "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Delete palette', 'customify'),
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Delete', 'customify'),
      onClick: e => {
        e.stopPropagation();
        onDelete(palette.id);
      },
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconClose, {})
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
    className: "customify-preset-grid",
    children: palettes.map(p => {
      // A theme-preset card is considered active when either the preset
      // itself or its shadow palette is the current active palette.
      const isActive = kind === 'theme' ? p.id === activeId || SHADOW_PREFIX_GRID + p.id === activeId : p.id === activeId;
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(PresetCard, {
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
  const [name, setName] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [extendFrom, setExtendFrom] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(defaultExtendId);
  const inputRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    setTimeout(() => inputRef.current?.focus(), 100);
  }, []);
  const submit = () => {
    // translators: %d is the next sequential number for the palette
    const finalName = name.trim() || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Palette %d', 'customify'), userCount + 1);
    onConfirm(finalName, extendFrom);
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "customify-add-form is-open",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "customify-form-title",
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Create new palette', 'customify')
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Palette title', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
        ref: inputRef,
        type: "text",
        value: name,
        placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('e.g. My brand', 'customify'),
        autoComplete: "off",
        onChange: e => setName(e.target.value),
        onKeyDown: e => {
          if (e.key === 'Enter') submit();
        }
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Extend from', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("select", {
        value: extendFrom,
        onChange: e => setExtendFrom(e.target.value),
        children: allPalettes.map(p => {
          const isTheme = themePresets.some(t => t.id === p.id);
          const tag = isTheme ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('theme', 'customify') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('user', 'customify');
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("option", {
            value: p.id,
            children: `${p.name} (${tag})`
          }, p.id);
        })
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-actions",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        className: "customify-btn--cancel",
        type: "button",
        onClick: onCancel,
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Cancel', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("button", {
        className: "customify-btn--add",
        type: "button",
        onClick: submit,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconAdd, {}), " ", (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add', 'customify')]
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
  const [text, setText] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const [error, setError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  const inputRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    setTimeout(() => inputRef.current?.focus(), 100);
  }, []);
  const submit = () => {
    const raw = text.trim();
    if (!raw) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Paste JSON code first.', 'customify'));
      return;
    }
    let parsed;
    try {
      parsed = JSON.parse(raw);
    } catch (err) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Invalid JSON: %s', 'customify'), err.message));
      return;
    }
    const items = Array.isArray(parsed) ? parsed : [parsed];
    const validated = [];
    for (let i = 0; i < items.length; i++) {
      const it = items[i];
      if (!it || typeof it !== 'object') {
        return setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item %d: must be an object.', 'customify'), i + 1));
      }
      if (!it.name || typeof it.name !== 'string') {
        return setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item %d: missing "name" field.', 'customify'), i + 1));
      }
      if (!it.colors || typeof it.colors !== 'object') {
        return setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item %d: missing "colors" object.', 'customify'), i + 1));
      }
      const colors = {};
      for (const slot of slots) {
        const v = it.colors[slot];
        if (!v || typeof v !== 'string' || !/^#([0-9A-F]{3}|[0-9A-F]{6})$/i.test(v.trim())) {
          return setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)(/* translators: 1: item index, 2: palette name, 3: slot key */
          (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Item %1$d ("%2$s"): slot "%3$s" missing or not a valid hex.', 'customify'), i + 1, it.name, slot));
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "customify-add-form is-open",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-title",
      children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Import palette(s) from JSON', 'customify'), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        className: "customify-paste-example",
        type: "button",
        onClick: () => {
          setText(EXAMPLE_JSON);
          setError('');
        },
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Use example', 'customify')
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Paste JSON', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("textarea", {
        ref: inputRef,
        value: text,
        spellCheck: false,
        onChange: e => setText(e.target.value)
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("p", {
        className: "customify-form-hint",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Accepts a single palette object or an array. Requires "name" + all 6 slots.', 'customify')
      }), error && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: "customify-form-error is-show",
        children: error
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-actions",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        className: "customify-btn--cancel",
        type: "button",
        onClick: onCancel,
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Cancel', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("button", {
        className: "customify-btn--add",
        type: "button",
        onClick: submit,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconImport, {}), " ", (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Import', 'customify')]
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
  const [selected, setSelected] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(() => new Set(palettes.map(p => p.id)));
  const [copied, setCopied] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const json = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => {
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
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-add-form is-open",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: "customify-form-title",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Export custom palettes', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
        className: "customify-form-field",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select palettes', 'customify')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
          className: "customify-export-empty",
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('No custom palettes to export yet. Create or import one first.', 'customify')
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: "customify-form-actions",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
          className: "customify-btn--cancel",
          type: "button",
          onClick: onClose,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Close', 'customify')
        })
      })]
    });
  }
  const allChecked = selected.size === palettes.length;
  const someChecked = selected.size > 0 && selected.size < palettes.length;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "customify-add-form is-open",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "customify-form-title",
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Export custom palettes', 'customify')
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select palettes', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
        className: "customify-export-list",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
          className: "customify-select-all",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
            type: "checkbox",
            checked: allChecked,
            ref: el => {
              if (el) el.indeterminate = someChecked;
            },
            onChange: e => toggleAll(e.target.checked)
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('All custom palettes', 'customify')
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
          children: palettes.map(p => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("label", {
            className: "customify-export-item",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
              type: "checkbox",
              checked: selected.has(p.id),
              onChange: () => toggle(p.id)
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
              className: "customify-ex-name",
              children: p.name
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
              className: "customify-mini-strip",
              children: slots.map(s => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
                style: {
                  background: p.colors[s]
                }
              }, s))
            })]
          }, p.id))
        })]
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-field",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("label", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('JSON output', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
        className: "customify-export-output",
        children: json || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('//  No palettes selected', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("p", {
        className: "customify-form-hint",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Re-importable via the ↓ button above or on another site.', 'customify')
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-form-actions",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
        className: 'customify-copied-flash' + (copied ? ' is-show' : ''),
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('✓ Copied', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        className: "customify-btn--cancel",
        type: "button",
        onClick: onClose,
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Close', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("button", {
        className: "customify-btn--ghost",
        type: "button",
        disabled: !json,
        onClick: doDownload,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconExport, {}), " ", (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Download', 'customify')]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        className: "customify-btn--add",
        type: "button",
        disabled: !json,
        onClick: doCopy,
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Copy', 'customify')
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
    children: settings.map((cfg, i) => {
      const slotsArr = cfg.slots || [];
      const isAtDefault = slotsArr.every(s => palette.colors[s] === defaults[s]);
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
        className: "customify-setting-row",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
          className: "customify-label-wrap",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
            className: "customify-label",
            children: cfg.label
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
            className: "customify-sublabel",
            children: cfg.sublabel
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
          className: "customify-right",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
            className: "customify-reset-mini",
            type: "button",
            onClick: () => resetRow(slotsArr),
            title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Reset to default', 'customify'),
            "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Reset to default', 'customify'),
            disabled: isAtDefault,
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(Icon, {
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
              (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("label", {
                className: 'customify-color-dot' + (isLight(col) ? ' is-light' : ''),
                style: {
                  background: col
                },
                title: `${slotName} · ${col.toUpperCase()}`,
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
                  type: "color",
                  value: col,
                  onInput: e => onSlotChange(slotName, e.target.value.toUpperCase()),
                  onClick: e => e.stopPropagation()
                }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconGlobe, {})]
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
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Text on primary', 'customify')
}, {
  slot: 'on-secondary',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Text on secondary', 'customify')
}, {
  slot: 'on-surface',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Text on surface', 'customify')
}, {
  slot: 'text-muted',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Muted text', 'customify')
}, {
  slot: 'text-subtle',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Subtle text', 'customify')
}, {
  slot: 'border-default',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Default border', 'customify')
}, {
  slot: 'primary-hover',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Primary hover', 'customify')
}, {
  slot: 'primary-subtle',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Primary subtle', 'customify')
}];
function AutoComputed({
  trigger
}) {
  // Read computed values from :root each time `trigger` (the active palette
  // signature) changes — useColorVars has already updated those vars in a
  // sibling effect.
  const rows = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => {
    const rs = getComputedStyle(document.documentElement);
    return AUTO_COMPUTED_ROWS.map(row => ({
      ...row,
      value: rs.getPropertyValue(`--customify-color-${row.slot}`).trim() || '—'
    }));
  }, [trigger]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      className: "customify-auto-hint",
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Theme generates these from the 6 slots above.', 'customify')
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
      children: rows.map(r => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
        className: "customify-auto-row",
        title: `${r.slot} · ${r.value}`,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
          className: "customify-auto-swatch",
          style: {
            background: r.value
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
          className: "customify-auto-label",
          children: r.label
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
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
  const [editSlot, setEditSlot] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [openForm, setOpenForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null); // 'add' | 'import' | 'export' | null

  const slots = cfg.slots;
  const themePresets = cfg.themePresets || [];
  const userArr = Array.isArray(userPalettes) ? userPalettes : [];

  // Shadow palettes are auto-created when the user edits a theme-preset slot.
  // They use a stable ID prefix so edits accumulate on the same object instead
  // of spawning a new "(copy)" each time. They are kept out of the visible
  // Custom palettes grid — they're an implementation detail, not named palettes.
  const SHADOW_PREFIX = 'user_theme_';
  const visibleUserArr = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => userArr.filter(p => !p.id.startsWith(SHADOW_PREFIX)), [userArr]);

  // allPalettes includes shadows so activePalette lookup always finds them.
  const allPalettes = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => [...themePresets, ...userArr], [themePresets, userArr]);
  const activePalette = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => {
    return allPalettes.find(p => p.id === activeId) || allPalettes[0] || null;
  }, [allPalettes, activeId]);
  const activeKind = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useMemo)(() => themePresets.some(p => p.id === activePalette?.id) ? 'theme' : 'user', [themePresets, activePalette]);
  useColorVars(activePalette, slots);

  // Esc closes popover / open form.
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
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
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
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
  const editSlotColor = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((slot, newHex) => {
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
  const addPalette = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((name, sourceId) => {
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
  const pickThemePreset = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(themeId => {
    const shadowId = SHADOW_PREFIX + themeId;
    const hasShadow = userArr.some(p => p.id === shadowId);
    setActiveId(hasShadow ? shadowId : themeId);
  }, [userArr, setActiveId]);
  const deletePalette = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(id => {
    setUserPalettes(userArr.filter(p => p.id !== id));
    if (activeId === id) {
      setActiveId(themePresets[0]?.id || '');
    }
  }, [userArr, setUserPalettes, activeId, setActiveId, themePresets]);
  const renamePalette = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((id, newName) => {
    setUserPalettes(userArr.map(p => p.id === id ? {
      ...p,
      name: newName
    } : p));
  }, [userArr, setUserPalettes]);
  const importPalettes = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)(items => {
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "customify-sidebar",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("h3", {
        className: "customify-control--heading",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Current palette', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
        className: "customify-hero-card",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(HeroDeck, {
          palette: activePalette,
          slots: slots,
          onSlotClick: setEditSlot
        }, activePalette.id /* re-mount to retrigger animation on switch */), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
          className: "customify-deck-footer",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("span", {
            className: "customify-deck-name",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
              children: activePalette.name
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
              className: 'customify-tag' + (activeKind === 'user' ? ' customify-tag--user' : ''),
              children: activeKind === 'theme' ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('theme', 'customify') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('user', 'customify')
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
            className: "customify-deck-sub",
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.sprintf)(/* translators: %d is the number of color slots in a palette */
            (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('%d slots', 'customify'), slots.length)
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("div", {
          className: "customify-deck-hint",
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Click a card to edit that slot', 'customify')
        }), editSlot && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(Popover, {
          slot: editSlot,
          palette: activePalette,
          kind: activeKind,
          slotDesc: cfg.slotDesc || {},
          onChange: hex => editSlotColor(editSlot, hex),
          onClose: () => setEditSlot(null)
        })]
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("h3", {
        className: "customify-control--heading",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Theme presets', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(PresetGrid, {
        palettes: themePresets,
        kind: "theme",
        activeId: activeId,
        slots: slots,
        onPick: pickThemePreset
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("h3", {
        className: "customify-control--heading",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("span", {
          children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Custom palettes', 'customify'), " ", /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("span", {
            className: "customify-badge",
            children: visibleUserArr.length
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("span", {
          style: {
            display: 'flex',
            gap: 4
          },
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
            className: "customify-icon-btn",
            type: "button",
            "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Export palettes', 'customify'),
            title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Export JSON', 'customify'),
            onClick: () => setOpenForm(openForm === 'export' ? null : 'export'),
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconExport, {})
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
            className: "customify-icon-btn",
            type: "button",
            "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Import palette', 'customify'),
            title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Import JSON', 'customify'),
            onClick: () => setOpenForm(openForm === 'import' ? null : 'import'),
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconImport, {})
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
            className: "customify-icon-btn",
            type: "button",
            "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add palette', 'customify'),
            title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Add new palette', 'customify'),
            onClick: () => setOpenForm(openForm === 'add' ? null : 'add'),
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(IconAdd, {})
          })]
        })]
      }), visibleUserArr.length === 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
        className: "customify-empty",
        children: [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('No custom palettes yet.', 'customify'), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("br", {}), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Click + to create one, or ↓ to import JSON.', 'customify')]
      }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(PresetGrid, {
        palettes: visibleUserArr,
        kind: "user",
        activeId: activeId,
        slots: slots,
        onPick: setActiveId,
        onDelete: deletePalette,
        onRename: renamePalette
      }), openForm === 'add' && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(AddForm, {
        allPalettes: [...themePresets, ...visibleUserArr],
        themePresets: themePresets,
        defaultExtendId: activeId,
        userCount: visibleUserArr.length,
        onConfirm: addPalette,
        onCancel: () => setOpenForm(null)
      }), openForm === 'import' && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(ImportForm, {
        slots: slots,
        onConfirm: importPalettes,
        onCancel: () => setOpenForm(null)
      }), openForm === 'export' && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(ExportForm, {
        palettes: visibleUserArr,
        slots: slots,
        onClose: () => setOpenForm(null)
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "customify-section",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("h4", {
        className: "customify-control--heading",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Theme color', 'customify')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(SettingsRows, {
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
    const root = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createRoot)(host);
    root.render(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(App, {
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

/***/ }),

/***/ "./src/backend/customizer/preview-colors/customizer.scss":
/*!***************************************************************!*\
  !*** ./src/backend/customizer/preview-colors/customizer.scss ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ (function(module) {

module.exports = window["ReactJSXRuntime"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

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
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
!function() {
/*!********************************************************!*\
  !*** ./src/backend/customizer/preview-colors/index.js ***!
  \********************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _customizer_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./customizer.scss */ "./src/backend/customizer/preview-colors/customizer.scss");
/* harmony import */ var _customizer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./customizer.js */ "./src/backend/customizer/preview-colors/customizer.js");
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


}();
/******/ })()
;
//# sourceMappingURL=preview-colors.js.map