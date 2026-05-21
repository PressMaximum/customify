/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 565:
/***/ (function() {


;// external ["wp","element"]
var external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","components"]
var external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external ["wp","primitives"]
var external_wp_primitives_namespaceObject = window["wp"]["primitives"];
;// external "ReactJSXRuntime"
var external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./node_modules/@wordpress/icons/build-module/library/settings.mjs
// packages/icons/src/library/settings.tsx


var settings_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: [
  /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "m19 7.5h-7.628c-.3089-.87389-1.1423-1.5-2.122-1.5-.97966 0-1.81309.62611-2.12197 1.5h-2.12803v1.5h2.12803c.30888.87389 1.14231 1.5 2.12197 1.5.9797 0 1.8131-.62611 2.122-1.5h7.628z" }),
  /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "m19 15h-2.128c-.3089-.8739-1.1423-1.5-2.122-1.5s-1.8131.6261-2.122 1.5h-7.628v1.5h7.628c.3089.8739 1.1423 1.5 2.122 1.5s1.8131-.6261 2.122-1.5h2.128z" })
] });

//# sourceMappingURL=settings.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/close.mjs
// packages/icons/src/library/close.tsx


var close_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "m13.06 12 6.47-6.47-1.06-1.06L12 10.94 5.53 4.47 4.47 5.53 10.94 12l-6.47 6.47 1.06 1.06L12 13.06l6.47 6.47 1.06-1.06L13.06 12Z" }) });

//# sourceMappingURL=close.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/drag-handle.mjs
// packages/icons/src/library/drag-handle.tsx


var drag_handle_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M8 7h2V5H8v2zm0 6h2v-2H8v2zm0 6h2v-2H8v2zm6-14v2h2V5h-2zm0 8h2v-2h-2v2zm0 6h2v-2h-2v2z" }) });

//# sourceMappingURL=drag-handle.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/plus.mjs
// packages/icons/src/library/plus.tsx


var plus_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M11 12.5V17.5H12.5V12.5H17.5V11H12.5V6H11V11H6V12.5H11Z" }) });

//# sourceMappingURL=plus.mjs.map

;// ./src/backend/header-builder/TemplatesPanel.jsx
/**
 * Customify Templates Panel — React port of the legacy Save / Load / Remove
 * template UI for the header & footer builders.
 *
 * Mounts via createPortal into a placeholder rendered by the PHP custom_html
 * control inside the `{builderId}_templates` Customizer section. Initial
 * template list is read from the builder config localized on
 * window.Customify_Layout_Builder.builders.{id}.saved_templates.
 */




const AJAX_ACTION = 'customify_builder_save_template';
function encodeValue(value) {
  return encodeURI(JSON.stringify(value));
}

/**
 * Best-effort decode of a wp.customize setting value into a plain JS shape.
 *
 * Customify stores most settings as encodeURIComponent(JSON.stringify(value))
 * once the user edits them, but the initial value (loaded from theme_mod) is
 * the raw decoded shape. Try the URL+JSON path first, then plain JSON, then
 * fall back to the raw value (typical for plain string settings like colors).
 */
function decodeSettingValue(raw) {
  if (raw === null || raw === undefined) return raw;
  if (typeof raw === 'object') return raw;
  if (typeof raw !== 'string') return raw;
  try {
    return JSON.parse(decodeURIComponent(raw));
  } catch (_) {}
  try {
    return JSON.parse(raw);
  } catch (_) {}
  return raw;
}
function isEmptySetting(v) {
  if (v === null || v === undefined || v === '' || v === false) return true;
  if (Array.isArray(v)) return v.length === 0;
  if (typeof v === 'object') return Object.keys(v).length === 0;
  return false;
}
function postAjax(params) {
  const ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
  const body = new URLSearchParams();
  Object.entries(params).forEach(([k, v]) => body.set(k, v));
  return fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    },
    body: body.toString()
  }).then(r => r.json());
}
function TemplatesPanel({
  builderId,
  controlId,
  mountId,
  onApplyLayout,
  layoutSettingKey
}) {
  const [container, setContainer] = (0,external_wp_element_namespaceObject.useState)(() => document.getElementById(mountId));
  const [templates, setTemplates] = (0,external_wp_element_namespaceObject.useState)(() => window.Customify_Layout_Builder?.builders?.[builderId]?.saved_templates || {});
  const [name, setName] = (0,external_wp_element_namespaceObject.useState)('');
  const [saving, setSaving] = (0,external_wp_element_namespaceObject.useState)(false);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const el = document.getElementById(mountId);
    if (el) {
      setContainer(el);
      return;
    }
    const observer = new MutationObserver(() => {
      const found = document.getElementById(mountId);
      if (found) {
        setContainer(found);
        observer.disconnect();
      }
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
    return () => observer.disconnect();
  }, [mountId]);
  if (!container) return null;
  const nonce = window.Customify_Layout_Builder?.nonce || '';
  const onSave = () => {
    const tplName = name.trim();
    if (!tplName || saving) return;

    // Capture the live wp.customize values for every builder setting — this
    // includes pending edits the user has not yet Published (which would be
    // invisible to a server-side get_theme_mod() call).
    const defaults = window.Customify_Layout_Builder?.builders?.[builderId]?.setting_defaults || {};
    const wpc = window.wp?.customize;
    const payload = {};
    if (wpc) {
      Object.keys(defaults).forEach(key => {
        const setting = wpc(key);
        if (!setting) return;
        const value = decodeSettingValue(setting.get?.());
        if (isEmptySetting(value)) return;
        payload[key] = value;
      });
    }
    setSaving(true);
    postAjax({
      action: AJAX_ACTION,
      name: tplName,
      id: builderId,
      control: controlId,
      nonce,
      data: JSON.stringify(payload)
    }).then(res => {
      setSaving(false);
      if (!res?.success) return;
      const {
        key_id: keyId,
        name: savedName,
        data
      } = res.data || {};
      if (!keyId) return;
      setTemplates(prev => {
        const rest = Object.fromEntries(Object.entries(prev).filter(([k]) => k !== keyId));
        return {
          [keyId]: {
            name: savedName,
            image: '',
            data: data || {}
          },
          ...rest
        };
      });
      setName('');
    }).catch(() => setSaving(false));
  };
  const onRemove = key => {
    const snapshot = templates;
    setTemplates(prev => {
      const next = {
        ...prev
      };
      delete next[key];
      return next;
    });
    postAjax({
      action: AJAX_ACTION,
      id: builderId,
      remove: key,
      nonce
    }).then(res => {
      if (!res?.success) setTemplates(snapshot);
    }).catch(() => setTemplates(snapshot));
  };
  const onLoad = tpl => {
    if (!window.wp?.customize) return;
    const wpc = window.wp.customize;
    const data = tpl?.data || {};
    const defaults = window.Customify_Layout_Builder?.builders?.[builderId]?.setting_defaults || {};

    // Loading a template fully replaces the builder state: every known builder
    // setting gets either the template's value (when present) or its default
    // (when absent).
    const allKeys = new Set([...Object.keys(defaults), ...Object.keys(data)]);
    allKeys.forEach(key => {
      const setting = wpc(key);
      if (!setting) return;
      const hasTemplateValue = Object.prototype.hasOwnProperty.call(data, key);
      const newValue = hasTemplateValue ? data[key] : defaults[key] ?? '';
      setting.set(encodeValue(newValue));
    });

    // Belt-and-braces: also push the layout payload into the Builder's React
    // state directly. wp.customize fires bind handlers on .set(), but we rely
    // on a deep-equal check inside that path which can no-op in subtle cases
    // (e.g. layout setting unchanged at the encoded-string level). The direct
    // callback guarantees the visible grid updates immediately.
    if (typeof onApplyLayout === 'function' && layoutSettingKey) {
      const layoutValue = Object.prototype.hasOwnProperty.call(data, layoutSettingKey) ? data[layoutSettingKey] : defaults[layoutSettingKey] ?? {};
      onApplyLayout(layoutValue);
    }
  };
  const entries = Object.entries(templates);
  const hasTemplates = entries.length > 0;
  return (0,external_wp_element_namespaceObject.createPortal)(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "save-template-form",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
        type: "text",
        className: "template-input-name change-by-js",
        value: name,
        placeholder: (0,external_wp_i18n_namespaceObject.__)('Template name', 'customify'),
        onChange: e => setName(e.target.value),
        onKeyDown: e => {
          if (e.key === 'Enter') {
            e.preventDefault();
            onSave();
          }
        },
        disabled: saving
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        type: "button",
        className: "button button-secondary save-builder-template",
        onClick: onSave,
        disabled: saving || !name.trim(),
        children: saving ? (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Save', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "customize-control-title",
      children: (0,external_wp_i18n_namespaceObject.__)('Saved Templates', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("ul", {
      className: `list-saved-templates list-boxed ${hasTemplates ? 'has-templates' : 'no-templates'}`,
      children: [entries.map(([key, tpl]) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
        className: "saved_template li-boxed",
        "data-id": key,
        children: [tpl?.name || (0,external_wp_i18n_namespaceObject.__)('Untitled', 'customify'), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
          href: "#",
          className: "load-tpl",
          onClick: e => {
            e.preventDefault();
            onLoad(tpl);
          },
          children: (0,external_wp_i18n_namespaceObject.__)('Load', 'customify')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
          href: "#",
          className: "remove-tpl",
          onClick: e => {
            e.preventDefault();
            onRemove(key);
          },
          children: (0,external_wp_i18n_namespaceObject.__)('Remove', 'customify')
        })]
      }, key)), !hasTemplates && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("li", {
        className: "no_template",
        children: (0,external_wp_i18n_namespaceObject.__)('No saved templates.', 'customify')
      })]
    })]
  }), container);
}
;// ./src/backend/header-builder/Builder.jsx
/**
 * Customify Layout Builder — generic React component tree (header & footer).
 *
 * Data flow:
 *   wp.customize Setting  ←read/write→  React state (normalizeData shape)
 *
 * JSON schema (stored in the builder's react_control_id setting):
 * {
 *   desktop: { <row>: { left:[{id}], center:[{id}], right:[{id}] }, … },
 *   mobile:  { <row>: {…}, sidebar: { sidebar:[{id}] } }   // header only
 * }
 * The setting value is stored as encodeURIComponent(JSON.stringify(data)).
 */







// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const COLS = ['left', 'center', 'right'];
const ALL_COLS = ['left', 'center', 'right', 'col4', 'col5'];

// ---------------------------------------------------------------------------
// wp.customize bridge
// ---------------------------------------------------------------------------

function parseValue(raw) {
  if (!raw) return {};
  if (typeof raw === 'object') return raw;
  try {
    return JSON.parse(decodeURIComponent(raw));
  } catch (_) {}
  try {
    return JSON.parse(raw);
  } catch (_) {}
  return {};
}
function readSetting(controlId) {
  try {
    const setting = wp.customize(controlId);
    return setting ? parseValue(setting.get()) : {};
  } catch (_) {
    return {};
  }
}
function writeSetting(data, controlId) {
  try {
    const setting = wp.customize(controlId);
    if (setting) {
      setting.set(encodeURIComponent(JSON.stringify(data)));
    }
  } catch (_) {}
}

// ---------------------------------------------------------------------------
// Data helpers
// ---------------------------------------------------------------------------

// Strip a raw items collection down to entries shaped like `{id: <non-empty string>}`.
// Mirrors `Customify_Layout_Builder_Frontend_V2::normalize_layout_items()` so the
// JS and PHP renderers can never disagree on what counts as a valid layout entry.
function normalizeItems(raw) {
  if (!Array.isArray(raw)) return [];
  const out = [];
  for (const item of raw) {
    if (item && typeof item === 'object' && typeof item.id === 'string' && item.id) {
      out.push({
        id: item.id
      });
    }
  }
  return out;
}
function normalizeData(raw, deviceIds, rows, hasSidebar) {
  const data = {};
  const safe = raw && typeof raw === 'object' && !Array.isArray(raw) ? raw : {};
  for (const dev of deviceIds) {
    data[dev] = {};
    const devData = safe[dev] && typeof safe[dev] === 'object' ? safe[dev] : {};
    for (const row of rows) {
      data[dev][row] = {};
      const rowData = devData[row] && typeof devData[row] === 'object' ? devData[row] : {};
      for (const col of ALL_COLS) {
        data[dev][row][col] = normalizeItems(rowData[col]);
      }
    }
  }
  if (hasSidebar) {
    data.mobile = data.mobile || {};
    const sidebar = safe?.mobile?.sidebar;
    const sidebarItems = sidebar && typeof sidebar === 'object' ? sidebar.sidebar : null;
    data.mobile.sidebar = {
      sidebar: normalizeItems(sidebarItems)
    };
  }
  return data;
}
function getAllPlacedIds(data) {
  const ids = new Set();
  for (const dev of Object.keys(data)) {
    for (const row of Object.keys(data[dev])) {
      for (const col of Object.keys(data[dev][row])) {
        for (const item of data[dev][row][col] || []) {
          ids.add(item.id);
        }
      }
    }
  }
  return ids;
}
function getDevicePlacedIds(data, device) {
  const ids = new Set();
  for (const row of Object.keys(data[device] || {})) {
    for (const col of Object.keys(data[device][row] || {})) {
      for (const item of data[device][row][col] || []) {
        ids.add(item.id);
      }
    }
  }
  return ids;
}
function permanentlyHideSection(section) {
  if (!section) return;
  section.active.set(false);
  // Store the handler on the section object so openSection can unbind it by reference.
  if (!section._customifyForceHide) {
    section._customifyForceHide = function (active) {
      if (active) section.active.set(false);
    };
  }
  section.active.bind(section._customifyForceHide);
}
function hideAllBuilderSections(allItems, infraSections, alwaysVisibleSections) {
  if (!wp?.customize?.section) return;
  for (const id of infraSections) {
    permanentlyHideSection(wp.customize.section(id));
  }
  for (const item of Object.values(allItems)) {
    // Hide the dedicated layout section (margin/padding/etc.) if present.
    if (item.layout_section) {
      permanentlyHideSection(wp.customize.section(item.layout_section));
    }
    if (!item.section || alwaysVisibleSections.has(item.section)) continue;
    permanentlyHideSection(wp.customize.section(item.section));
  }
}

// ---------------------------------------------------------------------------
// Builder (root)
// ---------------------------------------------------------------------------

function Builder({
  config
}) {
  const builderId = config?.id || 'header';
  const panelId = config?.panel || 'header_settings';
  const controlId = config?.react_control_id || config?.control_id || 'header_builder_panel_v2';
  const allItems = config?.items || {};
  const rowLabels = config?.rows || {};
  const rowLayoutKeys = config?.row_layout_keys || {};
  const deviceMap = config?.devices || {
    desktop: (0,external_wp_i18n_namespaceObject.__)('Desktop', 'customify'),
    mobile: (0,external_wp_i18n_namespaceObject.__)('Mobile / Tablet', 'customify')
  };
  const deviceIds = Object.keys(deviceMap);
  const hasMobile = deviceIds.includes('mobile');
  const hasSidebar = hasMobile && Object.keys(rowLabels).includes('sidebar');
  const rows = Object.keys(rowLabels).filter(r => r !== 'sidebar');
  const DEVICES_LIST = deviceIds.map(id => ({
    id,
    label: deviceMap[id]
  }));
  const panelItemsContainerId = config?.panel_items_container || `customify-${builderId.charAt(0)}b-panel-items`;
  const builderTitle = config?.title || builderId;

  // Sections that belong to the builder infrastructure — always hidden.
  const infraSections = new Set([config?.section, ...Object.keys(rowLabels).map(r => `${builderId}_${r}`)].filter(Boolean));
  // Sections that are always visible in WP Customizer (e.g. templates panel).
  const alwaysVisibleSections = new Set([`${builderId}_templates`]);
  const initialData = normalizeData(readSetting(controlId), deviceIds, rows, hasSidebar);
  const [panelExpanded, setPanelExpanded] = (0,external_wp_element_namespaceObject.useState)(false);
  const [builderOpen, setBuilderOpen] = (0,external_wp_element_namespaceObject.useState)(false);
  const [device, setDevice] = (0,external_wp_element_namespaceObject.useState)(deviceIds[0] || 'desktop');
  const [data, setData] = (0,external_wp_element_namespaceObject.useState)(initialData);
  const [innerLeft, setInnerLeft] = (0,external_wp_element_namespaceObject.useState)(0);
  const [popover, setPopover] = (0,external_wp_element_namespaceObject.useState)(null);
  const lastSaved = (0,external_wp_element_namespaceObject.useRef)(initialData);
  const dragRef = (0,external_wp_element_namespaceObject.useRef)(null);

  // Show/hide when the panel expands.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const panel = wp.customize?.panel?.(panelId);
    if (!panel) return;
    if (panel.expanded.get()) {
      setPanelExpanded(true);
      setBuilderOpen(true);
    }
    const handler = expanded => {
      setPanelExpanded(expanded);
      if (expanded) setBuilderOpen(true);else setBuilderOpen(false);
    };
    panel.expanded.bind(handler);
    return () => panel.expanded.unbind(handler);
  }, [panelId]);

  // Stay in sync with the WP device switcher (header / mobile only).
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!hasMobile || !wp?.customize?.previewedDevice) return;
    const handler = d => setDevice(d === 'desktop' ? 'desktop' : 'mobile');
    wp.customize.previewedDevice.bind(handler);
    return () => wp.customize.previewedDevice.unbind(handler);
  }, [hasMobile]);

  // Track Customizer sidebar width to offset the builder inner panel.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    function getSidebarWidth() {
      const paneVisible = wp.customize?.state?.('paneVisible')?.get?.();
      if (!paneVisible) return 0;
      return document.getElementById('customize-controls')?.offsetWidth || 0;
    }
    function update() {
      setInnerLeft(getSidebarWidth() - 1);
    }
    update();
    const paneState = wp.customize?.state?.('paneVisible');
    if (paneState) paneState.bind(update);
    window.addEventListener('resize', update);
    return () => {
      if (paneState) paneState.unbind(update);
      window.removeEventListener('resize', update);
    };
  }, []);

  // Adjust preview iframe bottom to match builder height, updated live via ResizeObserver.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const preview = document.getElementById('customize-preview');
    if (!preview) return;
    if (!builderOpen) {
      preview.classList.remove('cb--preview-panel-show');
      preview.style.bottom = '';
      return;
    }
    const root = document.getElementById(`customify-${builderId}-builder-root`);
    // .customify-hb is position:fixed so root.offsetHeight is 0 — watch the inner panel instead.
    const panel = root?.querySelector('.customify-hb');
    const updateBottom = () => {
      if (panel) preview.style.bottom = panel.offsetHeight + 'px';
    };
    preview.classList.add('cb--preview-panel-show');
    updateBottom();
    const observer = new ResizeObserver(updateBottom);
    if (panel) observer.observe(panel);
    return () => observer.disconnect();
  }, [builderOpen, builderId]);

  // Permanently hide all builder sections on mount.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    hideAllBuilderSections(allItems, infraSections, alwaysVisibleSections);
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  // Persist data when user changes layout.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (data === lastSaved.current) return;
    lastSaved.current = data;
    writeSetting(data, controlId);
  }, [data]); // eslint-disable-line react-hooks/exhaustive-deps

  // Sync React state from external wp.customize setting changes (e.g. Load Template,
  // Multiple Headers variant switch). Two sources are honoured:
  //
  //   1. `setting.bind(handler)` — fires when wp.customize.Setting.set() detects a
  //      deep-unequal value. Echo-protected: skip when the new normalized data
  //      matches the value we just wrote ourselves (otherwise writeSetting →
  //      bind → setData → effect → writeSetting loops forever).
  //
  //   2. `customify/builder/external-update` window event — emitted by extensions
  //      (e.g. Customify Pro's useVariantSwitcher) AFTER they have applied a batch
  //      of set() calls. Carries the explicit intent "re-read this setting now,
  //      do NOT consult the echo guard" because the extension already knows the
  //      value changed at the source even if the local `lastSaved` cache happens
  //      to look identical (variant switching can produce values that normalize
  //      to the same shape as a prior state, e.g. when a variant has no override
  //      and falls back to default).
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const setting = wp.customize?.(controlId);
    if (!setting) return;
    const applyRaw = (newRaw, force) => {
      const newData = normalizeData(parseValue(newRaw), deviceIds, rows, hasSidebar);
      if (!force && JSON.stringify(newData) === JSON.stringify(lastSaved.current)) {
        return;
      }
      lastSaved.current = newData;
      setData(newData);
    };
    const settingHandler = newRaw => applyRaw(newRaw, false);
    setting.bind(settingHandler);
    const eventHandler = e => {
      const target = e?.detail?.controlId;
      if (target && target !== controlId) return;
      applyRaw(setting.get(), true);
    };
    window.addEventListener('customify/builder/external-update', eventHandler);
    return () => {
      setting.unbind(settingHandler);
      window.removeEventListener('customify/builder/external-update', eventHandler);
    };
  }, [controlId]); // eslint-disable-line react-hooks/exhaustive-deps

  const switchDevice = (0,external_wp_element_namespaceObject.useCallback)(d => {
    setDevice(d);
    const selector = d === 'desktop' ? '#customize-footer-actions .preview-desktop' : '#customize-footer-actions .preview-mobile';
    document.querySelector(selector)?.click();
  }, []);
  const moveItem = (0,external_wp_element_namespaceObject.useCallback)((itemId, from, to) => {
    setData(prev => {
      const next = JSON.parse(JSON.stringify(prev));
      if (from !== 'available') {
        const {
          device: fd,
          row: fr,
          col: fc
        } = from;
        next[fd][fr][fc] = next[fd][fr][fc].filter(i => i.id !== itemId);
      }
      if (to === 'available') {
        return next;
      }
      const {
        device: td,
        row: tr,
        col: tc
      } = to;
      for (const row of Object.keys(next[td])) {
        for (const col of Object.keys(next[td][row])) {
          next[td][row][col] = next[td][row][col].filter(i => i.id !== itemId);
        }
      }
      next[td][tr][tc].push({
        id: itemId
      });
      return next;
    });
  }, []);
  const openSection = (0,external_wp_element_namespaceObject.useCallback)(sectionId => {
    if (!sectionId) return;
    const section = wp.customize?.section?.(sectionId);
    if (!section) return;
    if (section._customifyForceHide) {
      section.active.unbind(section._customifyForceHide);
    }
    section.active.set(true);
    section.focus();
    function onExpandChange(expanded) {
      if (!expanded) {
        section.expanded.unbind(onExpandChange);
        section.active.set(false);
        if (section._customifyForceHide) {
          section.active.bind(section._customifyForceHide);
        }
      }
    }
    section.expanded.bind(onExpandChange);
  }, []);
  const openRowSection = (0,external_wp_element_namespaceObject.useCallback)(rowId => {
    openSection(builderId + '_' + rowId);
  }, [openSection, builderId]);

  // Expose openSection globally so the preview iframe's JS can call it
  // when the user clicks item--preview-name (bypasses section.focus() which
  // fails because _customifyForceHide prevents active.set(true)).
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    window.customifyBuilderOpenSection = openSection;
    return () => {
      delete window.customifyBuilderOpenSection;
    };
  }, [openSection]);

  // Expose a global refresh helper for extensions that mutate the layout
  // setting externally (Multiple Headers variant switch, programmatic
  // template apply). Callers may pass a specific controlId to scope the
  // refresh; omitting it refreshes every mounted builder via the event
  // fan-out.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const prev = window.customifyBuilderRefresh;
    window.customifyBuilderRefresh = target => {
      window.dispatchEvent(new CustomEvent('customify/builder/external-update', {
        detail: {
          controlId: target || null
        }
      }));
    };
    return () => {
      window.customifyBuilderRefresh = prev;
    };
  }, []);
  const openPopover = (0,external_wp_element_namespaceObject.useCallback)((location, anchorRect) => {
    setPopover({
      location,
      anchorRect
    });
  }, []);
  const closePopover = (0,external_wp_element_namespaceObject.useCallback)(() => setPopover(null), []);
  const addItemFromPopover = (0,external_wp_element_namespaceObject.useCallback)(itemId => {
    if (!popover) return;
    moveItem(itemId, 'available', popover.location);
    setPopover(null);
  }, [popover, moveItem]);
  const placedInDevice = getDevicePlacedIds(data, device);
  const availableItems = Object.values(allItems).filter(i => !placedInDevice.has(i.id)).sort((a, b) => {
    const pa = wp.customize?.section?.(a.section)?.priority?.get() ?? 999;
    const pb = wp.customize?.section?.(b.section)?.priority?.get() ?? 999;
    return pa - pb;
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [builderOpen && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-hb customify--panel-v2",
      style: {
        left: innerLeft
      },
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "customify-hb__inner",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "customify-hb__header",
          children: [builderTitle && builderId !== 'header' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
            className: "customify-hb__title",
            children: builderTitle
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
            className: "customify-hb__devices",
            children: DEVICES_LIST.length > 1 && DEVICES_LIST.map(d => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
              className: `customify-hb__device-btn${device === d.id ? ' is-active' : ''}`,
              onClick: () => switchDevice(d.id),
              children: [d.id === 'desktop' ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
                className: "dashicons dashicons-desktop"
              }) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
                className: "dashicons dashicons-smartphone"
              }), d.label]
            }, d.id))
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
            className: "customify-hb__actions",
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
              type: "button",
              className: "customify-hb__close button button-secondary",
              onClick: () => setBuilderOpen(false),
              children: (0,external_wp_i18n_namespaceObject.__)('Close', 'customify')
            })
          })]
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "customify-hb__body",
          children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
            className: `customify-hb__grid${device === 'mobile' ? ' customify-hb__grid--mobile' : ''}`,
            children: [hasSidebar && device === 'mobile' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(OffCanvasRow, {
              items: data.mobile.sidebar.sidebar,
              allItems: allItems,
              dragRef: dragRef,
              onMove: moveItem,
              onOpenSection: openSection,
              onOpenRowSection: openRowSection,
              onOpenPopover: openPopover
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
              className: "customify-hb__rows",
              children: rows.map(rowId => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(BuilderRow, {
                rowId: rowId,
                rowLabel: rowLabels[rowId],
                cols: data[device][rowId],
                device: device,
                allItems: allItems,
                dragRef: dragRef,
                onMove: moveItem,
                onOpenSection: openSection,
                onOpenRowSection: openRowSection,
                onOpenPopover: openPopover,
                colLayoutKey: rowLayoutKeys[rowId] || null
              }, rowId))
            })]
          })
        }), popover && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ItemPickerPopover, {
          items: availableItems,
          anchorRect: popover.anchorRect,
          onAdd: addItemFromPopover,
          onClose: closePopover
        })]
      })
    }), panelExpanded && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(PanelItemsListPortal, {
      data: data,
      device: device,
      allItems: allItems,
      availableItems: availableItems,
      dragRef: dragRef,
      containerId: panelItemsContainerId,
      builderTitle: config?.title || builderId,
      builderOpen: builderOpen,
      onOpenBuilder: () => setBuilderOpen(true),
      onOpenSection: openSection,
      onRemove: itemId => moveItem(itemId, findItemLocation(data, device, itemId), 'available'),
      onAdd: itemId => moveItem(itemId, 'available', {
        device,
        row: rows[1] || rows[0],
        col: 'center'
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(TemplatesPanel, {
      builderId: builderId,
      controlId: `${builderId}_templates_save`,
      mountId: `customify-${builderId}-templates-mount`,
      layoutSettingKey: controlId,
      onApplyLayout: raw => {
        const newData = normalizeData(parseValue(raw), deviceIds, rows, hasSidebar);
        lastSaved.current = newData;
        setData(newData);
      }
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Popover.Slot, {})]
  });
}

// ---------------------------------------------------------------------------
// Helper: find an item's location within a device
// ---------------------------------------------------------------------------

function findItemLocation(data, device, itemId) {
  for (const row of Object.keys(data[device] || {})) {
    for (const col of Object.keys(data[device][row] || {})) {
      if ((data[device][row][col] || []).some(i => i.id === itemId)) {
        return {
          device,
          row,
          col
        };
      }
    }
  }
  return 'available';
}

// ---------------------------------------------------------------------------
// PanelItemsListPortal — renders into the builder panel items container
// ---------------------------------------------------------------------------

function PanelItemsListPortal({
  data,
  device,
  allItems,
  availableItems,
  dragRef,
  containerId,
  builderTitle,
  builderOpen,
  onOpenBuilder,
  onOpenSection,
  onRemove,
  onAdd
}) {
  // The container lives inside a WP Customizer Underscore.js template rendered
  // lazily (only when the panel first opens) — watch for it via MutationObserver.
  const [container, setContainer] = (0,external_wp_element_namespaceObject.useState)(() => document.getElementById(containerId));
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const el = document.getElementById(containerId);
    if (el) {
      setContainer(el);
      return;
    }
    const observer = new MutationObserver(() => {
      const found = document.getElementById(containerId);
      if (found) {
        setContainer(found);
        observer.disconnect();
      }
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
    return () => observer.disconnect();
  }, [containerId]);
  if (!container) return null;
  const placedIds = getDevicePlacedIds(data, device);
  const placed = [...placedIds].map(id => allItems[id] || {
    id,
    name: id,
    section: ''
  }).sort((a, b) => {
    const pa = wp.customize?.section?.(a.section)?.priority?.get() ?? 999;
    const pb = wp.customize?.section?.(b.section)?.priority?.get() ?? 999;
    return pa - pb;
  });
  return (0,external_wp_element_namespaceObject.createPortal)(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [!builderOpen && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "customify-hb__open-builder button button-primary",
      onClick: onOpenBuilder,
      children: (0,external_wp_i18n_namespaceObject.__)('Open Builder', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-hb__panel-section",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-hb__panel-items",
        children: placed.length === 0 ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "customify-hb__panel-items-empty",
          children: (0,external_wp_i18n_namespaceObject.__)('No items placed yet.', 'customify')
        }) : placed.map(item => {
          const settingsSection = item.layout_section || item.section;
          return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
            className: `customify-hb__panel-item${item.section ? ' is-clickable' : ''}`,
            onClick: () => item.section && onOpenSection(item.section),
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
              className: "customify-hb__panel-item-name",
              children: item.name
            }), settingsSection && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
              type: "button",
              className: "customify-hb__panel-item-btn customify-hb__panel-item-settings",
              title: (0,external_wp_i18n_namespaceObject.__)('Item Layout', 'customify'),
              onClick: e => {
                e.stopPropagation();
                onOpenSection(settingsSection);
              },
              children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
                icon: settings_default
              })
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
              type: "button",
              className: "customify-hb__panel-item-btn customify-hb__panel-item-remove",
              title: (0,external_wp_i18n_namespaceObject.__)('Remove', 'customify'),
              onClick: e => {
                e.stopPropagation();
                onRemove(item.id);
              },
              children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
                icon: close_default
              })
            })]
          }, item.id);
        })
      })
    }), availableItems.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-hb__panel-section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-hb__panel-section-label",
        children: (0,external_wp_i18n_namespaceObject.__)('Available', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-hb__panel-items",
        children: availableItems.map(item => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "customify-hb__panel-item customify-hb__panel-item--available",
          draggable: true,
          title: (0,external_wp_i18n_namespaceObject.__)('Drag to add to builder', 'customify'),
          onDragStart: e => {
            dragRef.current = {
              id: item.id,
              from: 'available'
            };
            e.dataTransfer.effectAllowed = 'move';
          },
          onDragEnd: () => {
            dragRef.current = null;
          },
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
            icon: drag_handle_default,
            className: "customify-hb__drag-handle"
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
            className: "customify-hb__panel-item-name",
            children: item.name
          })]
        }, item.id))
      })]
    })]
  }), container);
}

// ---------------------------------------------------------------------------
// BuilderRow
// ---------------------------------------------------------------------------

function parseColLayout(raw) {
  if (!raw) return null;
  try {
    return typeof raw === 'string' ? JSON.parse(raw) : raw;
  } catch (_) {
    return null;
  }
}
function BuilderRow({
  rowId,
  rowLabel,
  cols,
  device,
  allItems,
  dragRef,
  onMove,
  onOpenSection,
  onOpenRowSection,
  onOpenPopover,
  colLayoutKey
}) {
  const [hovered, setHovered] = (0,external_wp_element_namespaceObject.useState)(false);
  const rowRef = (0,external_wp_element_namespaceObject.useRef)(null);
  const [colLayoutValue, setColLayoutValue] = (0,external_wp_element_namespaceObject.useState)(() => {
    if (!colLayoutKey) return null;
    return parseColLayout(window.wp?.customize?.(colLayoutKey)?.get?.());
  });
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!colLayoutKey) return;
    const setting = window.wp?.customize?.(colLayoutKey);
    if (!setting) return;
    const handler = val => setColLayoutValue(parseColLayout(val));
    setting.bind(handler);
    return () => setting.unbind(handler);
  }, [colLayoutKey]);

  // Derive active columns and grid proportions from col_layout (always use desktop for builder view).
  let activeCols = COLS;
  let colsStyle = {};
  if (colLayoutValue) {
    // count is global; fr is per-device (fall back to desktop).
    const count = Math.max(1, Math.min(5, colLayoutValue.count || colLayoutValue.desktop?.count || 3));
    const d = colLayoutValue.desktop || {};
    const fr = Array.isArray(d.fr) && d.fr.length === count ? d.fr : Array(count).fill(1);
    activeCols = ALL_COLS.slice(0, count);
    colsStyle = {
      display: 'grid',
      gridTemplateColumns: fr.map(v => `${v}fr`).join(' ')
    };
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    ref: rowRef,
    className: `customify-hb__row customify-hb__row--${rowId}`,
    onMouseEnter: () => setHovered(true),
    onMouseLeave: () => setHovered(false),
    children: [hovered && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Popover, {
      anchor: rowRef.current,
      placement: "top-start",
      noArrow: true,
      focusOnMount: false,
      className: "customify-hb__row-tooltip",
      offset: 0,
      flip: false,
      children: rowLabel
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "customify-hb__row-label",
      title: rowLabel,
      onClick: () => onOpenRowSection(rowId),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
        icon: settings_default
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-hb__cols",
      style: colsStyle,
      children: activeCols.map(colId => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DropZone, {
        colId: colId,
        rowId: rowId,
        device: device,
        items: cols[colId] || [],
        allItems: allItems,
        dragRef: dragRef,
        onMove: onMove,
        onOpenSection: onOpenSection,
        onOpenPopover: onOpenPopover
      }, colId))
    })]
  });
}

// ---------------------------------------------------------------------------
// OffCanvasRow (mobile sidebar — header only)
// ---------------------------------------------------------------------------

function OffCanvasRow({
  items,
  allItems,
  dragRef,
  onMove,
  onOpenSection,
  onOpenRowSection,
  onOpenPopover
}) {
  const [isDragOver, setIsDragOver] = (0,external_wp_element_namespaceObject.useState)(false);
  const location = {
    device: 'mobile',
    row: 'sidebar',
    col: 'sidebar'
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-hb__row customify-hb__row--sidebar",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-hb__row-header",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
        type: "button",
        className: "customify-hb__row-label",
        title: (0,external_wp_i18n_namespaceObject.__)('Off Canvas Settings', 'customify'),
        onClick: () => onOpenRowSection('sidebar'),
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
          icon: settings_default
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "customify-hb__row-title",
        children: (0,external_wp_i18n_namespaceObject.__)('Off Canvas', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: `customify-hb__offcanvas${isDragOver ? ' is-drag-over' : ''}`,
      onDragOver: e => {
        e.preventDefault();
        setIsDragOver(true);
      },
      onDragLeave: () => setIsDragOver(false),
      onDrop: e => {
        e.preventDefault();
        setIsDragOver(false);
        if (!dragRef.current) return;
        const {
          id,
          from
        } = dragRef.current;
        onMove(id, from, location);
        dragRef.current = null;
      },
      onClick: e => {
        if (e.target.closest('.customify-hb__item')) return;
        onOpenPopover(location, e.currentTarget.getBoundingClientRect());
      },
      children: [items.map(item => {
        const info = allItems[item.id] || {
          name: item.id,
          section: ''
        };
        return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ItemChip, {
          id: item.id,
          name: info.name,
          section: info.section,
          layoutSection: info.layout_section,
          from: location,
          dragRef: dragRef,
          onRemove: id => onMove(id, location, 'available'),
          onOpenSection: onOpenSection
        }, item.id);
      }), items.length === 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "customify-hb__drop-hint",
        style: {
          pointerEvents: 'none'
        },
        children: (0,external_wp_i18n_namespaceObject.__)('Click to add items', 'customify')
      })]
    })]
  });
}

// ---------------------------------------------------------------------------
// DropZone (column)
// ---------------------------------------------------------------------------

function DropZone({
  colId,
  rowId,
  device,
  items,
  allItems,
  dragRef,
  onMove,
  onOpenSection,
  onOpenPopover
}) {
  const [isDragOver, setIsDragOver] = (0,external_wp_element_namespaceObject.useState)(false);
  const location = {
    device,
    row: rowId,
    col: colId
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: `customify-hb__col customify-hb__col--${colId}${isDragOver ? ' is-drag-over' : ''}`,
    onDragOver: e => {
      e.preventDefault();
      setIsDragOver(true);
    },
    onDragLeave: () => setIsDragOver(false),
    onDrop: e => {
      e.preventDefault();
      setIsDragOver(false);
      if (!dragRef.current) return;
      const {
        id,
        from
      } = dragRef.current;
      onMove(id, from, location);
      dragRef.current = null;
    },
    onClick: e => {
      if (e.target.closest('.customify-hb__item')) return;
      onOpenPopover(location, e.currentTarget.getBoundingClientRect());
    },
    children: [items.map(item => {
      const info = allItems[item.id] || {
        name: item.id,
        section: ''
      };
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ItemChip, {
        id: item.id,
        name: info.name,
        section: info.section,
        layoutSection: info.layout_section,
        from: location,
        dragRef: dragRef,
        onRemove: id => onMove(id, location, 'available'),
        onOpenSection: onOpenSection
      }, item.id);
    }), items.length === 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "customify-hb__col-empty",
      style: {
        pointerEvents: 'none'
      },
      children: "+"
    })]
  });
}

// ---------------------------------------------------------------------------
// ItemChip
// ---------------------------------------------------------------------------

function ItemChip({
  id,
  name,
  section,
  layoutSection,
  from,
  dragRef,
  onRemove,
  onOpenSection
}) {
  const settingsTarget = layoutSection || section;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-hb__item",
    draggable: true,
    onDragStart: e => {
      dragRef.current = {
        id,
        from
      };
      e.dataTransfer.effectAllowed = 'move';
    },
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
      icon: drag_handle_default,
      className: "customify-hb__item-handle"
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "customify-hb__item-name",
      children: name
    }), settingsTarget && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "customify-hb__item-btn customify-hb__item-settings",
      title: (0,external_wp_i18n_namespaceObject.__)('Settings', 'customify'),
      onClick: e => {
        e.stopPropagation();
        onOpenSection(settingsTarget);
      },
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
        icon: settings_default
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "customify-hb__item-btn customify-hb__item-remove",
      title: (0,external_wp_i18n_namespaceObject.__)('Remove', 'customify'),
      onClick: e => {
        e.stopPropagation();
        onRemove(id);
      },
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
        icon: close_default
      })
    })]
  });
}

// ---------------------------------------------------------------------------
// ItemPickerPopover
// ---------------------------------------------------------------------------

const ARROW_SIZE = 8;
const POPOVER_W = 300;
const POPOVER_H = 240;
function ItemPickerPopover({
  items,
  anchorRect,
  onAdd,
  onClose
}) {
  const ref = (0,external_wp_element_namespaceObject.useRef)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    function handler(e) {
      if (ref.current && !ref.current.contains(e.target)) {
        onClose();
      }
    }
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [onClose]);
  const anchorCenterX = anchorRect.left + anchorRect.width / 2;
  const effectiveW = Math.min(POPOVER_W, window.innerWidth - 8);
  const rawLeft = anchorCenterX - effectiveW / 2;
  const popoverLeft = Math.max(4, Math.min(rawLeft, window.innerWidth - effectiveW - 4));
  const arrowLeft = Math.max(12, Math.min(anchorCenterX - popoverLeft - ARROW_SIZE, effectiveW - 12 - ARROW_SIZE * 2));
  const isAbove = anchorRect.top >= POPOVER_H + ARROW_SIZE + 8;
  const popoverStyle = {
    left: popoverLeft,
    width: POPOVER_W
  };
  if (isAbove) {
    popoverStyle.bottom = window.innerHeight - anchorRect.top + ARROW_SIZE + 2;
  } else {
    popoverStyle.top = anchorRect.bottom + ARROW_SIZE + 2;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    ref: ref,
    className: `customify-hb__popover${isAbove ? ' is-above' : ' is-below'}`,
    style: popoverStyle,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-hb__popover-arrow",
      style: {
        left: arrowLeft
      }
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-hb__popover-list",
      children: items.length === 0 ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "customify-hb__popover-empty",
        children: (0,external_wp_i18n_namespaceObject.__)('All items are placed in the layout', 'customify')
      }) : items.map(item => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        className: "customify-hb__popover-item",
        onClick: () => onAdd(item.id),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
          icon: plus_default,
          className: "customify-hb__popover-item-icon"
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "customify-hb__popover-item-label",
          children: item.name
        })]
      }, item.id))
    })]
  });
}
;// ./src/backend/footer-row-layout/presets.js
/**
 * Column layout presets for footer rows.
 * fr: flex-fraction values for grid-template-columns.
 */
const PRESETS = {
  1: [{
    fr: [1]
  }],
  2: [{
    fr: [1, 1]
  }, {
    fr: [1, 2]
  }, {
    fr: [2, 1]
  }, {
    fr: [1, 3]
  }],
  3: [{
    fr: [1, 1, 1]
  }, {
    fr: [1, 2, 1]
  }, {
    fr: [2, 1, 1]
  }, {
    fr: [1, 1, 2]
  }, {
    fr: [1, 3, 1]
  }, {
    fr: [3, 1, 1]
  }, {
    fr: [1, 1, 3]
  }, {
    stacked: true
  }],
  4: [{
    fr: [1, 1, 1, 1]
  }, {
    fr: [1, 2, 2, 1]
  }, {
    stacked: true
  }],
  5: [{
    fr: [1, 1, 1, 1, 1]
  }, {
    stacked: true
  }]
};

// count is global; fr, gap, padding are per-device.
const DEFAULT_VALUE = {
  count: 4,
  desktop: {
    fr: [1, 1, 1, 1],
    gap: 0,
    padding: 0
  },
  tablet: {
    fr: [1, 1, 1, 1],
    gap: 0,
    padding: 0
  },
  mobile: {
    fr: [1],
    gap: 0,
    padding: 0
  }
};
;// ./src/backend/footer-row-layout/RowLayout.jsx




const DEVICES = [{
  id: 'desktop',
  icon: 'dashicons-desktop'
}, {
  id: 'tablet',
  icon: 'dashicons-tablet'
}, {
  id: 'mobile',
  icon: 'dashicons-smartphone'
}];
function DeviceSwitcher({
  device,
  onChange
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "cb-row-layout__devices",
    children: DEVICES.map(d => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: `cb-row-layout__device-btn${device === d.id ? ' is-active' : ''}`,
      title: d.id,
      onClick: () => onChange(d.id),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: `dashicons ${d.icon}`
      })
    }, d.id))
  });
}
function LayoutSvg({
  fr,
  stacked,
  count
}) {
  const W = 48;
  const H = 30;
  const GAP = 2;
  if (stacked) {
    const bars = count || 3;
    const totalGaps = GAP * (bars - 1);
    const barH = (H - 4 - totalGaps) / bars;
    const rects = Array.from({
      length: bars
    }, (_, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
      x: 0,
      y: 2 + i * (barH + GAP),
      width: W,
      height: Math.max(barH, 1),
      rx: 2
    }, i));
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("svg", {
      width: W,
      height: H,
      viewBox: `0 0 ${W} ${H}`,
      fill: "currentColor",
      xmlns: "http://www.w3.org/2000/svg",
      children: rects
    });
  }
  const total = fr.reduce((a, b) => a + b, 0);
  const totalG = GAP * (fr.length - 1);
  let x = 0;
  const rects = fr.map((f, i) => {
    const w = f / total * (W - totalG);
    const rect = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
      x: x,
      y: 2,
      width: Math.max(w, 1),
      height: H - 4,
      rx: 2
    }, i);
    x += w + GAP;
    return rect;
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("svg", {
    width: W,
    height: H,
    viewBox: `0 0 ${W} ${H}`,
    fill: "currentColor",
    xmlns: "http://www.w3.org/2000/svg",
    children: rects
  });
}
function RowLayout_parseValue(raw) {
  if (!raw) return {
    ...DEFAULT_VALUE
  };
  try {
    const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
    if (!parsed || typeof parsed !== 'object') return {
      ...DEFAULT_VALUE
    };

    // Migrate old format where count was stored per-device.
    // parseInt handles both number 3 and string "3" (sanitizer turns ints to strings).
    const count = parseInt(parsed.count ?? parsed.desktop?.count ?? DEFAULT_VALUE.count, 10) || DEFAULT_VALUE.count;
    const globalGap = parsed.gap ?? 0;
    const globalPadding = parsed.padding ?? 0;
    const parseDevice = (d, def) => ({
      fr: (d?.fr || def.fr).map(v => parseInt(v, 10) || 1),
      gap: parseInt(d?.gap ?? globalGap, 10) || 0,
      padding: parseInt(d?.padding ?? globalPadding, 10) || 0
    });
    return {
      count,
      desktop: parseDevice(parsed.desktop, DEFAULT_VALUE.desktop),
      tablet: parseDevice(parsed.tablet, DEFAULT_VALUE.tablet),
      mobile: parseDevice(parsed.mobile, DEFAULT_VALUE.mobile)
    };
  } catch (e) {
    return {
      ...DEFAULT_VALUE
    };
  }
}
function RowLayout({
  settingKey
}) {
  const [device, setDevice] = (0,external_wp_element_namespaceObject.useState)('desktop');
  const [value, setValue] = (0,external_wp_element_namespaceObject.useState)(() => {
    const raw = window.wp?.customize?.(settingKey)?.get?.();
    return RowLayout_parseValue(raw);
  });
  const isCommitting = (0,external_wp_element_namespaceObject.useRef)(false);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const setting = window.wp?.customize?.(settingKey);
    if (!setting) return;
    // Re-sync after mount in case the setting value loads asynchronously.
    const raw = setting.get?.();
    if (raw) setValue(RowLayout_parseValue(raw));
    // Sync on external changes (e.g. undo/redo), but not our own commits.
    const onChange = newRaw => {
      if (!isCommitting.current) setValue(RowLayout_parseValue(newRaw));
    };
    setting.bind(onChange);
    return () => setting.unbind(onChange);
  }, [settingKey]);

  // count is global; fr is per-device.
  const count = value.count || 1;
  const deviceData = value[device] || {
    fr: Array(count).fill(1)
  };
  const fr = deviceData.fr || Array(count).fill(1);
  const presets = PRESETS[count] || [{
    fr: Array(count).fill(1)
  }];
  const commit = newValue => {
    setValue(newValue);
    isCommitting.current = true;
    window.wp?.customize?.(settingKey)?.set?.(JSON.stringify(newValue));
    isCommitting.current = false;
  };
  const handleCountChange = n => {
    const firstPreset = (PRESETS[n] || [{
      fr: Array(n).fill(1)
    }])[0];
    const newFr = firstPreset.fr || Array(n).fill(1);
    commit({
      ...value,
      count: n,
      [device]: {
        ...deviceData,
        fr: newFr
      }
    });
  };
  const handlePreset = preset => {
    const newFr = preset.stacked ? [1] : preset.fr;
    commit({
      ...value,
      [device]: {
        ...deviceData,
        fr: newFr
      }
    });
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "cb-row-layout",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "cb-row-layout__count-section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "cb-row-layout__label",
        children: (0,external_wp_i18n_namespaceObject.__)('Columns', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "cb-row-layout__count-buttons",
        children: [1, 2, 3, 4, 5].map(n => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
          type: "button",
          className: `cb-row-layout__count-btn${count === n ? ' is-active' : ''}`,
          onClick: () => handleCountChange(n),
          children: n
        }, n))
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "cb-row-layout__field",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "cb-row-layout__field-header",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "cb-row-layout__label",
          children: (0,external_wp_i18n_namespaceObject.__)('Layout', 'customify')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DeviceSwitcher, {
          device: device,
          onChange: setDevice
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "cb-row-layout__preset-grid",
        children: presets.map((preset, idx) => {
          const isStacked = !!preset.stacked;
          const active = isStacked ? fr.length === 1 : JSON.stringify(fr) === JSON.stringify(preset.fr);
          return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
            type: "button",
            className: `cb-row-layout__preset-btn${active ? ' is-active' : ''}`,
            title: isStacked ? 'stacked' : preset.fr.join(':'),
            onClick: () => handlePreset(preset),
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LayoutSvg, {
              fr: preset.fr || [1],
              stacked: isStacked,
              count: count
            })
          }, idx);
        })
      })]
    })]
  });
}
;// ./src/backend/footer-builder/index.js
/**
 * Customify Footer Builder — React-powered Customizer panel.
 *
 * Reuses the generic Builder component from header-builder.
 * Config is read from window.Customify_Layout_Builder.builders.footer
 * (injected by PHP alongside the header builder data).
 */






// Row layout control — CSS + mounting logic bundled here so we don't need
// a separate script enqueue for it.



function mountRowLayouts() {
  document.querySelectorAll('[id^="cb-row-layout-"]:not([data-rl-mounted])').forEach(el => {
    const settingKey = el.dataset.setting;
    if (!settingKey) return;
    el.setAttribute('data-rl-mounted', '1');
    (0,external_wp_element_namespaceObject.render)(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(RowLayout, {
      settingKey: settingKey
    }), el);
  });
}
wp.customize.bind('ready', () => {
  const config = window.Customify_Layout_Builder?.builders?.footer || {};
  const container = document.createElement('div');
  container.id = 'customify-footer-builder-root';
  document.querySelector('body .wp-full-overlay')?.appendChild(container);
  (0,external_wp_element_namespaceObject.render)(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SlotFillProvider, {
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Builder, {
      config: config
    })
  }), container);

  // Mount Columns Layout React controls into the footer row sections.
  mountRowLayouts();

  // Re-mount when any section expands (row sections open via gear icon).
  try {
    wp.customize.section.each(section => {
      section.expanded.bind(isExpanded => {
        if (isExpanded) setTimeout(mountRowLayouts, 100);
      });
    });
  } catch (_e) {}
});

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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			79: 0,
/******/ 			733: 0,
/******/ 			207: 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkcustomify"] = self["webpackChunkcustomify"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, [733,207], function() { return __webpack_require__(565); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;