/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 739:
/***/ (function() {


;// external ["wp","element"]
var external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external "ReactJSXRuntime"
var external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./src/backend/dashboard/ui/Icon/icons.js

/**
 * Central SVG icon paths for the dashboard. Each entry is { viewBox, paths,
 * stroke?, strokeWidth?, fill? }. The <Icon name size /> component reads
 * from this map — never inline SVG in component JSX.
 *
 * Icon shapes are ported directly from samples/Dashboard.html so the built
 * dashboard matches the design source.
 */

const ICONS = {
  'chevron-right': {
    viewBox: '0 0 12 12',
    stroke: 'currentColor',
    strokeWidth: 1.4,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M3 9l6-6M9 3v5M9 3H4",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })
  },
  plus: {
    viewBox: '0 0 16 16',
    stroke: 'currentColor',
    strokeWidth: 2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M8 3v10M3 8h10"
    })
  },
  check: {
    viewBox: '0 0 12 12',
    stroke: 'currentColor',
    strokeWidth: 2.2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M2.5 6.5l2.5 2.5 4.5-5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })
  },
  'check-bold': {
    viewBox: '0 0 14 14',
    stroke: 'currentColor',
    strokeWidth: 2.2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M3 7.5l2.5 2.5L11 4",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })
  },
  'dots-vertical': {
    viewBox: '0 0 16 16',
    fill: 'currentColor',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "8",
        cy: "3",
        r: "1.4"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "8",
        cy: "8",
        r: "1.4"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "8",
        cy: "13",
        r: "1.4"
      })]
    })
  },
  close: {
    viewBox: '0 0 20 20',
    stroke: 'currentColor',
    strokeWidth: 1.8,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M5 5l10 10M15 5L5 15",
      strokeLinecap: "round"
    })
  },
  info: {
    viewBox: '0 0 18 18',
    stroke: 'currentColor',
    strokeWidth: 1.6,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "9",
        cy: "9",
        r: "7"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M9 8v4.5M9 5.5v.5",
        strokeLinecap: "round"
      })]
    })
  },
  'arrow-up-right': {
    viewBox: '0 0 13 13',
    stroke: 'currentColor',
    strokeWidth: 1.8,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M3 10L10 3M5 3h5v5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })
  },
  'eye-off': {
    viewBox: '0 0 14 14',
    stroke: 'currentColor',
    strokeWidth: 1.5,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M2 7s2-4 5-4 5 4 5 4-2 4-5 4-5-4-5-4z"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M2 2l10 10",
        strokeLinecap: "round"
      })]
    })
  },
  clock: {
    viewBox: '0 0 14 14',
    stroke: 'currentColor',
    strokeWidth: 1.7,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "7",
        cy: "7",
        r: "5.5"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M7 4v3.5l2 1.5"
      })]
    })
  },
  star: {
    viewBox: '0 0 16 16',
    fill: 'currentColor',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M8 1l2 5 5 .5-3.7 3.5L12.5 15 8 12 3.5 15l1.2-5L1 6.5 6 6z"
    })
  },
  'star-outline': {
    viewBox: '0 0 16 16',
    stroke: 'currentColor',
    strokeWidth: 1.7,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M8 1l1.8 4 4.7.5-3.5 3 .8 4.5L8 11l-3.8 2 .8-4.5-3-3 4.2-.5z",
      strokeLinejoin: "round"
    })
  },
  doc: {
    viewBox: '0 0 18 18',
    stroke: 'currentColor',
    strokeWidth: 1.5,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M3 2h8l4 4v10H3z"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M11 2v4h4"
      })]
    })
  },
  'clock-large': {
    viewBox: '0 0 18 18',
    stroke: 'currentColor',
    strokeWidth: 1.5,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "9",
        cy: "9",
        r: "6.5"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M9 5v4l2.5 2"
      })]
    })
  },
  'star-large': {
    viewBox: '0 0 18 18',
    stroke: 'currentColor',
    strokeWidth: 1.5,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M9 1.5l2.3 5.2 5.7.5-4.3 3.8 1.3 5.6L9 13.7l-5 2.9 1.3-5.6L1 7.2l5.7-.5z",
      strokeLinejoin: "round"
    })
  },
  mail: {
    viewBox: '0 0 18 18',
    stroke: 'currentColor',
    strokeWidth: 1.5,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M2 4h14v10H2z"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M2 4l7 5 7-5"
      })]
    })
  },
  gauge: {
    viewBox: '0 0 16 16',
    stroke: 'currentColor',
    strokeWidth: 1.6,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M8 1l1.8 1.6 2.4-.4-.4 2.4L13.4 6.5 11.8 8l1.6 1.5L13.8 12l-2.4-.4L9.6 14 8 12.4 6.4 14 4.6 11.6 2.2 12l.4-2.5L1 8l1.6-1.5L2.2 4l2.4.4L6.4 2z"
      })
    })
  },
  'version-arrow': {
    viewBox: '0 0 16 16',
    stroke: 'currentColor',
    strokeWidth: 1.6,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M2 8a6 6 0 1112 0M14 8l-2-2M14 8l2-2"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M8 4v4l2.5 2"
      })]
    })
  },
  'block-section': {
    viewBox: '0 0 32 32',
    stroke: 'currentColor',
    strokeWidth: 2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "4",
        y: "6",
        width: "24",
        height: "20",
        rx: "2"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M4 13h24"
      })]
    })
  },
  'block-container': {
    viewBox: '0 0 32 32',
    stroke: 'currentColor',
    strokeWidth: 2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "4",
        y: "4",
        width: "11",
        height: "11",
        rx: "1.5"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "17",
        y: "4",
        width: "11",
        height: "11",
        rx: "1.5"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "4",
        y: "17",
        width: "11",
        height: "11",
        rx: "1.5"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "17",
        y: "17",
        width: "11",
        height: "11",
        rx: "1.5"
      })]
    })
  },
  'block-heading': {
    viewBox: '0 0 32 32',
    stroke: 'currentColor',
    strokeWidth: 2.5,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
      d: "M7 6v20M25 6v20M7 16h18"
    })
  },
  'block-button': {
    viewBox: '0 0 32 32',
    stroke: 'currentColor',
    strokeWidth: 2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "4",
        y: "10",
        width: "24",
        height: "12",
        rx: "3",
        fill: "currentColor",
        opacity: ".15"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "4",
        y: "10",
        width: "24",
        height: "12",
        rx: "3"
      })]
    })
  },
  'block-image': {
    viewBox: '0 0 32 32',
    stroke: 'currentColor',
    strokeWidth: 2,
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "4",
        y: "4",
        width: "24",
        height: "24",
        rx: "2"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "11",
        cy: "12",
        r: "3",
        fill: "currentColor"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M5 26l8-9 7 6 6-5 5 4"
      })]
    })
  },
  /* GuidePopover hero illustrations — viewBox 240×160 (sample's POPOVERS
   * canvas). Filled rects/paths only, no stroke at SVG level. Inner
   * elements carry their own fill values verbatim from the sample's
   * inline JS constants (ICON_BLOCK_GRID / ICON_DESIGN_KIT / etc.). */
  'guide-block-grid': {
    viewBox: '0 0 240 160',
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "40",
        y: "32",
        width: "68",
        height: "44",
        rx: "4",
        fill: "rgba(255,255,255,.95)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "116",
        y: "32",
        width: "68",
        height: "44",
        rx: "4",
        fill: "rgba(255,255,255,.65)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "40",
        y: "84",
        width: "68",
        height: "44",
        rx: "4",
        fill: "rgba(255,255,255,.65)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "116",
        y: "84",
        width: "68",
        height: "44",
        rx: "4",
        fill: "rgba(255,255,255,.95)"
      })]
    })
  },
  'guide-design-kit': {
    viewBox: '0 0 240 160',
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "36",
        y: "22",
        width: "78",
        height: "46",
        rx: "4",
        fill: "rgba(255,255,255,.95)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "122",
        y: "22",
        width: "78",
        height: "46",
        rx: "4",
        fill: "rgba(255,255,255,.7)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "36",
        y: "76",
        width: "78",
        height: "46",
        rx: "4",
        fill: "rgba(255,255,255,.7)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "122",
        y: "76",
        width: "78",
        height: "46",
        rx: "4",
        fill: "rgba(255,255,255,.95)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "36",
        y: "130",
        width: "164",
        height: "14",
        rx: "3",
        fill: "rgba(255,255,255,.85)"
      })]
    })
  },
  'guide-gauge': {
    viewBox: '0 0 240 160',
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("circle", {
        cx: "120",
        cy: "92",
        r: "44",
        fill: "none",
        stroke: "rgba(255,255,255,.95)",
        strokeWidth: "6"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M120 80 L120 92 L138 100",
        stroke: "rgba(255,255,255,.95)",
        strokeWidth: "6",
        strokeLinecap: "round",
        fill: "none"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M120 24 L132 50 L120 46 L108 50 Z",
        fill: "rgba(255,255,255,.95)"
      })]
    })
  },
  'guide-play': {
    viewBox: '0 0 240 160',
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "40",
        y: "40",
        width: "160",
        height: "80",
        rx: "6",
        fill: "rgba(255,255,255,.92)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("polygon", {
        points: "100,60 100,100 140,80",
        fill: "rgba(56,88,233,.95)"
      })]
    })
  },
  'guide-build': {
    viewBox: '0 0 240 160',
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M60 90 L60 60 L100 40 L140 60 L180 40 L180 90 L140 110 L100 90 Z",
        fill: "rgba(255,255,255,.95)",
        stroke: "rgba(56,88,233,.4)",
        strokeWidth: "2",
        strokeLinejoin: "round"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("path", {
        d: "M100 40 L100 90 M140 60 L140 110",
        stroke: "rgba(56,88,233,.4)",
        strokeWidth: "2"
      })]
    })
  },
  'guide-layers': {
    viewBox: '0 0 240 160',
    fill: 'none',
    paths: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "60",
        y: "40",
        width: "120",
        height: "22",
        rx: "3",
        fill: "rgba(255,255,255,.95)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "50",
        y: "68",
        width: "140",
        height: "22",
        rx: "3",
        fill: "rgba(255,255,255,.75)"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
        x: "40",
        y: "96",
        width: "160",
        height: "22",
        rx: "3",
        fill: "rgba(255,255,255,.55)"
      })]
    })
  }
};
;// ./src/backend/dashboard/ui/Icon/index.js
/**
 * Generic SVG icon. Reads from the central ICONS map (./icons.js) so callers
 * never inline SVG markup. Add a new icon by extending the map, not by
 * adding a new component.
 */



function Icon({
  name,
  size = 16,
  className
}) {
  const icon = ICONS[name];
  if (!icon) {
    return null;
  }
  const {
    viewBox,
    paths,
    stroke,
    strokeWidth,
    fill
  } = icon;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("svg", {
    width: size,
    height: size,
    viewBox: viewBox,
    fill: fill || 'none',
    stroke: stroke,
    strokeWidth: strokeWidth,
    className: className,
    "aria-hidden": "true",
    focusable: "false",
    children: paths
  });
}
;// ./src/backend/dashboard/ui/Pill/index.js

/**
 * Small pill / badge. Variants ported from the sample (default neutral, free
 * green). Brand-specific tints can be added by extending the variant map.
 */

function Pill({
  children,
  variant,
  className
}) {
  const cls = ['pm-pill'];
  if (variant) {
    cls.push(`pm-pill--${variant}`);
  }
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
    className: cls.join(' '),
    children: children
  });
}
;// ./src/backend/dashboard/ui/Tabs/index.js

/**
 * Horizontal pill tab strip — header navigation.
 *
 * Items: [{ id, label }]. The component is uncontrolled in styling but
 * controlled in state: parent owns `active` + `onChange`. Renders <a> with
 * href so middle-click + open-in-new-tab work for hash routes.
 */

function Tabs({
  items,
  active,
  onChange,
  ariaLabel
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("nav", {
    className: "pm-tabs",
    "aria-label": ariaLabel,
    children: items.map(item => {
      const isActive = item.id === active;
      const cls = ['pm-tabs__item'];
      if (isActive) {
        cls.push('pm-tabs__item--active');
      }
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
        href: `#${item.id}`,
        className: cls.join(' '),
        "aria-current": isActive ? 'page' : undefined,
        onClick: e => {
          e.preventDefault();
          onChange(item.id);
        },
        children: item.label
      }, item.id);
    })
  });
}
;// ./src/backend/dashboard/ui/Card/index.js

/**
 * Card — bordered, rounded white surface. Optional header with title slot
 * (h3) + trailing slot (link / dropdown / pill). Body wraps children.
 *
 * Both header slots are optional. Pass nothing to get a bare card; pass a
 * string for a plain title or a node for full custom header content.
 */

function Card({
  title,
  headerRight,
  className,
  children,
  bodyPadding = false
}) {
  const cls = ['pm-card'];
  if (className) {
    cls.push(className);
  }
  const bodyCls = ['pm-card__body'];
  if (bodyPadding) {
    bodyCls.push('pm-card__body--padded');
  }
  const hasHeader = title || headerRight;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: cls.join(' '),
    children: [hasHeader && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-card__header",
      children: [title && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h3", {
        className: "pm-card__title",
        children: title
      }), headerRight && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-card__header-right",
        children: headerRight
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: bodyCls.join(' '),
      children: children
    })]
  });
}
;// external ["wp","components"]
var external_wp_components_namespaceObject = window["wp"]["components"];
;// ./src/backend/dashboard/ui/Button/index.js
/**
 * Thin wrapper around @wordpress/components Button so the dashboard buttons
 * inherit WP admin theme color (Modern / Blue / Coffee / Light etc.) and
 * Gutenberg accessibility behaviors (focus ring, aria-disabled, etc.) for
 * free.
 *
 * Variant alias map (kept so legacy callers don't need to change):
 *   ghost → WP tertiary (text-only, no border)
 *
 * Anything else passes through verbatim, so callers can use any WP variant
 * (`primary` | `secondary` | `tertiary` | `link`) or a custom one (e.g.
 * `minimal`) which WP renders as `class="components-button is-{variant}"`
 * for downstream className-based theming.
 *
 * `href` triggers <a> rendering inside WPButton automatically.
 */



const VARIANT_ALIAS = {
  ghost: 'tertiary'
};
function Button({
  variant = 'primary',
  href,
  onClick,
  disabled,
  type = 'button',
  className,
  children,
  ...rest
}) {
  const wpVariant = VARIANT_ALIAS[variant] || variant;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
    variant: wpVariant,
    href: href,
    onClick: onClick,
    disabled: disabled,
    type: href ? undefined : type,
    className: className,
    ...rest,
    children: children
  });
}
;// external "ReactDOM"
var external_ReactDOM_namespaceObject = window["ReactDOM"];
;// ./src/backend/dashboard/ui/Modal/useBodyLock.js
/**
 * Lock document.body scroll while a modal/overlay is open. Restores the
 * original overflow value on unmount or when `enabled` flips false.
 */


function useBodyLock(enabled) {
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!enabled) {
      return undefined;
    }
    const previous = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = previous;
    };
  }, [enabled]);
}
;// ./src/backend/dashboard/ui/Modal/useEscapeKey.js
/**
 * Reusable hook — call `handler` whenever the Escape key is pressed and the
 * `enabled` flag is true. Detached for use beyond Modal (Dropdown, etc.).
 */


function useEscapeKey(enabled, handler) {
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!enabled) {
      return undefined;
    }
    const onKey = e => {
      if (e.key === 'Escape') {
        handler(e);
      }
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [enabled, handler]);
}
;// ./src/backend/dashboard/ui/Modal/useFocusTrap.js
/**
 * Trap Tab/Shift+Tab focus inside a container. Saves the previously focused
 * element and restores it on cleanup. Auto-focuses the first focusable
 * element inside the container (or [data-autofocus] if present).
 *
 * Usage:
 *   const ref = useRef();
 *   useFocusTrap(ref, isOpen);
 *   return <div ref={ref}>...</div>;
 */


const FOCUSABLE_SELECTOR = ['a[href]', 'button:not([disabled])', 'input:not([disabled])', 'select:not([disabled])', 'textarea:not([disabled])', '[tabindex]:not([tabindex="-1"])'].join(',');
function useFocusTrap(ref, enabled) {
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!enabled || !ref.current) {
      return undefined;
    }
    const root = ref.current;
    const previouslyFocused = document.activeElement;

    // Initial focus.
    const autofocus = root.querySelector('[data-autofocus]');
    const focusables = root.querySelectorAll(FOCUSABLE_SELECTOR);
    const initial = autofocus || focusables[0] || root;
    if (initial && typeof initial.focus === 'function') {
      initial.focus();
    }
    const onKey = e => {
      if (e.key !== 'Tab') {
        return;
      }
      const list = root.querySelectorAll(FOCUSABLE_SELECTOR);
      if (!list.length) {
        e.preventDefault();
        return;
      }
      const first = list[0];
      const last = list[list.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    };
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('keydown', onKey);
      if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
        previouslyFocused.focus();
      }
    };
  }, [ref, enabled]);
}
;// ./src/backend/dashboard/ui/Modal/index.js
/**
 * Generic Modal — sticky header + scrollable body + sticky footer. Renders
 * via portal to document.body so it escapes WP admin layout layers.
 *
 * Usage:
 *   <Modal isOpen={open} onClose={close} size="md" ariaLabel="Settings">
 *     <Modal.Header title="Settings" onClose={close} />
 *     <Modal.Body>{...}</Modal.Body>
 *     <Modal.Footer align="end">
 *       <Button onClick={close}>Cancel</Button>
 *       <Button variant="primary" onClick={save}>Save</Button>
 *     </Modal.Footer>
 *   </Modal>
 *
 * Built-in behaviors: body scroll lock, focus trap, ESC/overlay close,
 * scroll-shadow on header/footer when body overflows.
 */









function Modal({
  isOpen,
  onClose,
  size = 'md',
  closeOnOverlayClick = true,
  closeOnEsc = true,
  ariaLabel,
  ariaLabelledBy,
  className,
  children
}) {
  const modalRef = (0,external_wp_element_namespaceObject.useRef)(null);
  useBodyLock(isOpen);
  useEscapeKey(isOpen && closeOnEsc, onClose);
  useFocusTrap(modalRef, isOpen);
  if (!isOpen) {
    return null;
  }
  const cls = ['pm-modal', `pm-modal--${size}`];
  if (className) {
    cls.push(className);
  }
  const onOverlayClick = e => {
    if (closeOnOverlayClick && e.target === e.currentTarget) {
      onClose();
    }
  };
  return (0,external_ReactDOM_namespaceObject.createPortal)(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "pm-modal-overlay",
    onClick: onOverlayClick,
    role: "presentation",
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      ref: modalRef,
      className: cls.join(' '),
      role: "dialog",
      "aria-modal": "true",
      "aria-label": ariaLabel,
      "aria-labelledby": ariaLabelledBy,
      tabIndex: -1,
      children: children
    })
  }), document.body);
}
function ModalHeader({
  title,
  subtitle,
  onClose,
  children,
  className
}) {
  const cls = ['pm-modal__header'];
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: cls.join(' '),
    children: [children || /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [title && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
        className: "pm-modal__title",
        children: title
      }), subtitle && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pm-modal__subtitle",
        children: subtitle
      })]
    }), onClose && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "pm-modal__close",
      onClick: onClose,
      "aria-label": (0,external_wp_i18n_namespaceObject.__)('Close', 'customify'),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
        name: "close",
        size: 20
      })
    })]
  });
}
function ModalBody({
  children,
  className
}) {
  const cls = ['pm-modal__body'];
  if (className) {
    cls.push(className);
  }
  const ref = (0,external_wp_element_namespaceObject.useRef)(null);
  const [scrolled, setScrolled] = (0,external_wp_element_namespaceObject.useState)(false);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const node = ref.current;
    if (!node) {
      return undefined;
    }
    const onScroll = () => setScrolled(node.scrollTop > 0);
    node.addEventListener('scroll', onScroll);
    onScroll();
    return () => node.removeEventListener('scroll', onScroll);
  }, []);
  if (scrolled) {
    cls.push('pm-modal__body--scrolled');
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    ref: ref,
    className: cls.join(' '),
    children: children
  });
}
function ModalFooter({
  children,
  align = 'end',
  className
}) {
  const cls = ['pm-modal__footer', `pm-modal__footer--${align}`];
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: cls.join(' '),
    children: children
  });
}
Modal.Header = ModalHeader;
Modal.Body = ModalBody;
Modal.Footer = ModalFooter;
/* harmony default export */ var ui_Modal = (Modal);
;// ./src/backend/dashboard/ui/Dropdown/index.js
/**
 * 3-dot dropdown menu (controlled). Items: [{ label, icon?, onClick }].
 * Renders trigger + popout menu. Click-outside + ESC close handled here.
 */





function Dropdown({
  items,
  align = 'right',
  triggerIcon = 'dots-vertical',
  triggerLabel,
  className
}) {
  const [open, setOpen] = (0,external_wp_element_namespaceObject.useState)(false);
  const wrapRef = (0,external_wp_element_namespaceObject.useRef)(null);
  useEscapeKey(open, () => setOpen(false));
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!open) {
      return undefined;
    }
    const onClick = e => {
      if (wrapRef.current && !wrapRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', onClick);
    return () => document.removeEventListener('mousedown', onClick);
  }, [open]);
  const cls = ['pm-dropdown'];
  if (open) {
    cls.push('pm-dropdown--open');
  }
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    ref: wrapRef,
    className: cls.join(' '),
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "pm-dropdown__trigger",
      "aria-haspopup": "menu",
      "aria-expanded": open,
      "aria-label": triggerLabel,
      onClick: () => setOpen(!open),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
        name: triggerIcon,
        size: 16
      })
    }), open && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: `pm-dropdown__menu pm-dropdown__menu--${align}`,
      role: "menu",
      children: items.map((item, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        type: "button",
        role: "menuitem",
        className: "pm-dropdown__item",
        onClick: () => {
          item.onClick();
          setOpen(false);
        },
        children: [item.icon && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: item.icon,
          size: 14
        }), item.label]
      }, i))
    })]
  });
}
;// ./src/backend/dashboard/ui/Hero/index.js

/**
 * Welcome hero — left text block + right preview slot. Both halves are
 * slot-driven so brand-specific content (greeting, title, CTA, preview)
 * flows in via props.
 */

function Hero({
  greeting,
  title,
  description,
  actions,
  preview
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-hero",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-hero__left",
      children: [greeting && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pm-hero__greeting",
        children: greeting
      }), title && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
        className: "pm-hero__title",
        children: title
      }), description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pm-hero__desc description",
        children: description
      }), actions && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-hero__actions",
        children: actions
      })]
    }), preview && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-hero__right",
      children: preview
    })]
  });
}
;// ./src/backend/dashboard/ui/ChecklistRow/index.js
/**
 * Checklist row — circle check + body (h4 + description) + actions slot.
 * State (checked/unchecked) is owned by the parent so the same row renders
 * in both states without re-mounting.
 */



function ChecklistRow({
  checked,
  onToggleCheck,
  title,
  description,
  actions
}) {
  const cls = ['pm-qstart'];
  if (checked) {
    cls.push('pm-qstart--checked');
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: cls.join(' '),
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
      type: "button",
      className: "pm-qstart__check",
      onClick: onToggleCheck,
      "aria-pressed": checked,
      "aria-label": title,
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
        name: "check",
        size: 12
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-qstart__body",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
        className: "pm-qstart__title",
        children: title
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pm-qstart__desc description",
        children: description
      })]
    }), actions && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-qstart__actions",
      children: actions
    })]
  });
}
;// ./src/backend/dashboard/ui/GridCard/index.js
/* unused harmony import specifier */ var GridCard_Icon;
/* unused harmony import specifier */ var _jsxs;
/* unused harmony import specifier */ var _jsx;
/**
 * Two grid card variants used in Welcome:
 *
 *   <ThemeGridCard title description href />
 *     — 2-col grid item with internal hairline borders (theme customizer)
 *
 *   <BlockCard icon name docHref />
 *     — 5-col grid item card with icon + name + meta link (free blocks)
 *
 * Both are link/click surfaces. Wrap the parent in <div class="pm-theme-grid">
 * or <div class="pm-blocks-grid"> to get the grid layout.
 */



function ThemeGridCard({
  title,
  description,
  href,
  onClick
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
    href: href || '#',
    className: "pm-theme-grid__item",
    onClick: onClick,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
      children: title
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "description",
      children: description
    })]
  });
}
function BlockCard({
  icon,
  name,
  docHref,
  docLabel
}) {
  return /*#__PURE__*/_jsxs("div", {
    className: "pm-block-card",
    children: [/*#__PURE__*/_jsx("div", {
      className: "pm-block-card__icon",
      children: /*#__PURE__*/_jsx(GridCard_Icon, {
        name: icon,
        size: 24
      })
    }), /*#__PURE__*/_jsxs("div", {
      children: [/*#__PURE__*/_jsx("h4", {
        children: name
      }), /*#__PURE__*/_jsx("div", {
        className: "pm-block-card__meta",
        children: /*#__PURE__*/_jsx("a", {
          href: docHref || '#',
          children: docLabel
        })
      })]
    })]
  });
}
;// ./src/backend/dashboard/ui/ModuleRow/index.js

/**
 * Module row — used in 2-column grids inside cards (Pro modules listing).
 *
 *   <ModuleRow title description statusPill? trailing />
 *
 * The grid + nth-child borders are owned by the parent <ModuleList>; the
 * row itself just lays out toggle? + body + trailing slot.
 */

function ModuleRow({
  title,
  description,
  statusPill,
  trailing,
  leading,
  className
}) {
  const cls = ['pm-module-row'];
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: cls.join(' '),
    children: [leading && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-module-row__leading",
      children: leading
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-module-row__body",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "pm-module-row__title-row",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
          children: title
        }), statusPill && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "pm-module-row__status",
          children: statusPill
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "description",
        children: description
      })]
    }), trailing && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-module-row__trailing",
      children: trailing
    })]
  });
}
function ModuleList({
  children,
  className
}) {
  const cls = ['pm-module-list'];
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: cls.join(' '),
    children: children
  });
}
;// ./src/backend/dashboard/ui/LicenseCard/index.js
/**
 * License upgrade card — sidebar Pro CTA. Slot-driven so brand-specific
 * pricing/features flow in via props. The CTA renders as a WP Button so it
 * inherits the user's admin color scheme + standard button affordances.
 */




function LicenseCard({
  title,
  tagline,
  features,
  price,
  priceUnit,
  priceFootnote,
  ctaLabel,
  ctaHref,
  onCtaClick
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-license",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("h4", {
      className: "pm-license__title",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
        name: "star-outline",
        size: 16
      }), title]
    }), tagline && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "pm-license__tagline description",
      children: tagline
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "pm-license__features",
      children: features.map((feat, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "check-bold",
          size: 14
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          children: feat
        })]
      }, i))
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-license__price-line",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-license__price",
        children: price
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-license__price-unit",
        children: priceUnit
      })]
    }), priceFootnote && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "pm-license__footnote description",
      children: priceFootnote
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
      variant: "primary",
      className: `pm-button--lg`,
      href: ctaHref,
      onClick: onCtaClick,
      children: ctaLabel
    })]
  });
}
;// ./src/backend/dashboard/ui/ResourceList/index.js
/**
 * Resource list — vertical stack of side-row items inside a Card. Each row
 * is a link with leading icon, label, and trailing external-arrow icon.
 *
 *   <ResourceList items={[{ icon, label, href }]} />
 */



function ResourceList({
  items
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "pm-resource-list",
    children: items.map((item, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
      className: "pm-resource-list__row",
      href: item.href || '#',
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-resource-list__icon",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: item.icon,
          size: 18
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-resource-list__label",
        children: item.label
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-resource-list__ext",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "chevron-right",
          size: 12
        })
      })]
    }, i))
  });
}
;// ./src/backend/dashboard/ui/ProductRow/index.js
/* unused harmony import specifier */ var ProductRow_Icon;
/* unused harmony import specifier */ var ProductRow_jsxs;
/* unused harmony import specifier */ var ProductRow_jsx;
/**
 * Cross-promo product row — gradient initials avatar + name + meta + install
 * link. Used inside Cards to advertise sister products.
 */



function ProductRow({
  initials,
  gradient = 'blue',
  name,
  meta,
  ctaLabel,
  ctaHref,
  onCtaClick
}) {
  return /*#__PURE__*/ProductRow_jsxs("div", {
    className: "pm-product-row",
    children: [/*#__PURE__*/ProductRow_jsx("div", {
      className: `pm-product-row__pic pm-product-row__pic--${gradient}`,
      children: initials
    }), /*#__PURE__*/ProductRow_jsxs("div", {
      className: "pm-product-row__info",
      children: [/*#__PURE__*/ProductRow_jsx("h5", {
        children: name
      }), /*#__PURE__*/ProductRow_jsx("p", {
        className: "description",
        children: meta
      }), /*#__PURE__*/ProductRow_jsxs("a", {
        className: "pm-product-row__cta",
        href: ctaHref || '#',
        onClick: onCtaClick,
        children: [ctaLabel, /*#__PURE__*/ProductRow_jsx(ProductRow_Icon, {
          name: "chevron-right",
          size: 12
        })]
      })]
    })]
  });
}
;// ./src/backend/dashboard/ui/ReviewCard/index.js
/**
 * Review prompt card body — 5 stars + tagline + WP Button CTA. Wrap in a
 * <Card title> for the full sidebar look.
 */




function ReviewCard({
  rating = 5,
  message,
  ctaLabel,
  ctaHref,
  onCtaClick
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-review",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-review__stars",
      children: Array.from({
        length: rating
      }).map((_, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
        name: "star",
        size: 16
      }, i))
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "description",
      children: message
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
      variant: "secondary",
      href: ctaHref,
      onClick: onCtaClick,
      className: 'pm-fullwidth',
      children: ctaLabel
    })]
  });
}
;// ./src/backend/dashboard/ui/Sidebar/index.js

/**
 * Welcome layout shell — main column + sticky sidebar. Sidebar collapses to
 * a 2-col stack at <=1100px and a 1-col stack at <=720px (CSS).
 */

function WelcomeLayout({
  main,
  sidebar
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-welcome",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-welcome__main",
      children: main
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-welcome__sidebar",
      children: sidebar
    })]
  });
}
;// ./src/backend/dashboard/ui/CompareTable/index.js
/**
 * Free vs Pro comparison table primitives.
 *
 *   <CompareTable>
 *     <CompareTable.Section title="Blocks" colLabels={['Free','Pro']} />
 *     <CompareTable.Row name="Section block" detail="..." cells={[true,true]} />
 *     <CompareTable.Row name="Per-block CSS" cells={[true,'1 / Unlimited']} />
 *     <CompareTable.CTA>...</CompareTable.CTA>
 *   </CompareTable>
 *
 * Cell value rules:
 *   true       → green check
 *   false      → gray dash
 *   string     → text-value (default tone)
 *   { value, muted } → text-value with muted modifier
 */



function CompareTable({
  children,
  className
}) {
  const cls = ['pm-compare'];
  if (className) {
    cls.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: cls.join(' '),
    children: children
  });
}
function CompareSection({
  title,
  colLabels = ['Free', 'Pro']
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-compare__section",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      children: title
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-compare__col-label",
      children: colLabels[0]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-compare__col-label pm-compare__col-label--pro",
      children: colLabels[1]
    })]
  });
}
function renderCell(value, key) {
  if (value === true) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-compare__col",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-compare__check-yes",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "check",
          size: 12
        })
      })
    }, key);
  }
  if (value === false) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-compare__col",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-compare__check-no",
        children: "\u2212"
      })
    }, key);
  }
  const text = typeof value === 'object' ? value.value : value;
  const muted = typeof value === 'object' ? value.muted : false;
  const cls = ['pm-compare__text'];
  if (muted) {
    cls.push('pm-compare__text--muted');
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "pm-compare__col",
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: cls.join(' '),
      children: text
    })
  }, key);
}
function CompareRow({
  name,
  detail,
  cells
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-compare__row",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-compare__name",
      children: [name, detail && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "description",
        children: detail
      })]
    }), cells.map((c, i) => renderCell(c, i))]
  });
}
function CompareCTA({
  title,
  description,
  action
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-compare__cta",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-compare__cta-text",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
        children: title
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "description",
        children: description
      })]
    }), action]
  });
}
CompareTable.Section = CompareSection;
CompareTable.Row = CompareRow;
CompareTable.CTA = CompareCTA;
/* harmony default export */ var ui_CompareTable = (CompareTable);
;// ./src/backend/dashboard/ui/ReleaseBlock/index.js
/**
 * Release block — version row + change list. Used inside the Changelog
 * card (multiple releases stacked vertically).
 *
 *   <ReleaseBlock version="0.4.13" date="May 2, 2026" current changes={[
 *     { tag: 'updated', text: 'WooCommerce template files.' },
 *     { tag: 'fixed',   text: <>Fix <code>foo()</code> bug.</> },
 *   ]} />
 *
 * Recognized tags (port of sample): new | added | updated | improved | fixed
 * | changed | breaking. Add new tag colors in style.css.
 */



function ReleaseBlock({
  version,
  date,
  current,
  changes
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-release",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-release__version",
      children: ["v", version, current && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-release__current",
        children: (0,external_wp_i18n_namespaceObject.__)('Current', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-release__date",
        children: date
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "pm-change-list",
      children: changes.map((change, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: `pm-change-tag pm-change-tag--${change.tag}`,
          children: change.tag
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "pm-change-list__text",
          children: change.text
        })]
      }, i))
    })]
  });
}
;// ./src/backend/dashboard/ui/ToggleSwitch/index.js

/**
 * Pill-shaped on/off switch — port of .toggle from sample. Controlled:
 * parent owns `checked` + `onChange`.
 */

function ToggleSwitch({
  checked,
  onChange,
  disabled,
  ariaLabel
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("label", {
    className: "pm-toggle",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
      type: "checkbox",
      checked: !!checked,
      onChange: e => onChange(e.target.checked),
      disabled: disabled,
      "aria-label": ariaLabel
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pm-toggle__slider"
    })]
  });
}
;// ./src/backend/dashboard/ui/Select/index.js

/**
 * Native <select> primitive — port of .select from sample. Background arrow
 * is drawn via CSS (data: URL) so no JS needed for the chevron.
 *
 *   <Select value={v} onChange={setV} options={[
 *     { value: 'page', label: 'Per-page file' },
 *     ...
 *   ]} />
 */

function Select({
  value,
  onChange,
  options,
  disabled,
  ariaLabel
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("select", {
    className: "pm-select",
    value: value,
    onChange: e => onChange(e.target.value),
    disabled: disabled,
    "aria-label": ariaLabel,
    children: options.map(opt => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("option", {
      value: opt.value,
      children: opt.label
    }, opt.value))
  });
}
;// ./src/backend/dashboard/ui/SubNav/index.js
/**
 * Vertical sub-navigation — used in Settings left rail. Items: [{ id, label,
 * icon? }]. Controlled: parent owns `active` + `onChange`.
 *
 * `icon` is rendered through @wordpress/components Icon, so callers can
 * pass any element from @wordpress/icons (e.g. `import { brush } from
 * '@wordpress/icons'; { id, label, icon: brush }`). A plain string is also
 * accepted and forwarded to Icon — useful for dashicon names like
 * `admin-generic`.
 *
 * Renders <a href> with hash so middle-click works for sub-routes (caller
 * is responsible for any URL-state sync; the click handler owns state).
 */



function SubNav({
  items,
  active,
  onChange,
  ariaLabel
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("nav", {
    className: "pm-subnav",
    "aria-label": ariaLabel,
    children: items.map(item => {
      const isActive = item.id === active;
      const cls = ['pm-subnav__item'];
      if (isActive) {
        cls.push('pm-subnav__item--active');
      }
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
        href: `#${item.id}`,
        className: cls.join(' '),
        "aria-current": isActive ? 'page' : undefined,
        onClick: e => {
          e.preventDefault();
          onChange(item.id);
        },
        children: [item.icon && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "pm-subnav__icon",
          children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
            icon: item.icon,
            size: 18
          })
        }), item.label]
      }, item.id);
    })
  });
}
;// ./src/backend/dashboard/ui/SettingRow/index.js

/**
 * Settings field row — title + description on the left, control slot on the
 * right. Stack rows directly inside a Card; the row owns its own border-top
 * (first-of-type strips it).
 *
 *   <SettingRow
 *     title="Per-block asset loading"
 *     description="Only load CSS and JS for blocks that..."
 *     control={<ToggleSwitch checked={v} onChange={setV} />}
 *   />
 */

function SettingRow({
  title,
  description,
  control
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-setting-row",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-setting-row__info",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
        children: title
      }), description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "description",
        children: description
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-setting-row__control",
      children: control
    })]
  });
}
;// ./src/backend/dashboard/ui/SaveBar/index.js
/**
 * Sticky save bar inside a settings Card — status on the left, action
 * buttons on the right. Status states: idle | saving | saved | error.
 */





const STATUS_LABEL = {
  idle: dirty => dirty ? (0,external_wp_i18n_namespaceObject.__)('Unsaved changes', 'customify') : (0,external_wp_i18n_namespaceObject.__)('All changes saved', 'customify'),
  saving: () => (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify'),
  saved: () => (0,external_wp_i18n_namespaceObject.__)('All changes saved', 'customify'),
  error: () => (0,external_wp_i18n_namespaceObject.__)('Save failed — try again', 'customify')
};
function SaveBar({
  dirty,
  status = 'idle',
  onReset,
  onSave,
  saveLabel,
  resetLabel
}) {
  const cls = ['pm-save-bar'];
  if (status === 'error') {
    cls.push('pm-save-bar--error');
  }
  const statusText = STATUS_LABEL[status](dirty);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: cls.join(' '),
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("span", {
      className: "pm-save-bar__status",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
        name: "clock",
        size: 14
      }), statusText]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
      variant: "secondary",
      onClick: onReset,
      disabled: status === 'saving',
      children: resetLabel || (0,external_wp_i18n_namespaceObject.__)('Reset to defaults', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
      variant: "primary",
      onClick: onSave,
      disabled: !dirty || status === 'saving',
      children: saveLabel || (0,external_wp_i18n_namespaceObject.__)('Save changes', 'customify')
    })]
  });
}
;// ./src/backend/dashboard/ui/SettingsLayout/index.js

/**
 * Settings tab layout — left sub-nav (240px) + right content. Collapses to
 * a horizontal scrollable nav above the content at <=1100px.
 */

function SettingsLayout({
  nav,
  children
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-settings",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-settings__nav",
      children: nav
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-settings__content",
      children: children
    })]
  });
}
;// ./src/backend/dashboard/ui/index.js
/**
 * Barrel export for all PressMaximum UI components. Consumers should
 * `import { Card, Button, Tabs } from '../ui'` rather than reaching into
 * subfolders — keeps the import surface small + relocations cheap.
 */

























;// ./src/backend/dashboard/app/config.js
/**
 * Bootstrap values from PHP via wp_localize_script. Server emits a
 * `window.customifyDashboard` object before dashboard.js runs (see
 * Customify_Theme_Dashboard::enqueue_assets() — `wp_localize_script` call).
 *
 * Reads default to safe empty strings so the React app never crashes if a
 * key is missing (e.g. when running components in isolation outside the
 * admin page).
 */

const config = typeof window !== 'undefined' && window.customifyDashboard || {};
const PLUGIN_VERSION = config.pluginVersion || '';
const WP_VERSION = config.wpVersion || '';
const ADMIN_URL = config.adminUrl || '';
const CUSTOMIZER_URL = config.customizerUrl || '';
const BASE_URL = config.baseUrl || '';
const PRO_ACTIVE = !!config.proActive;
const PRO_MODULES_BOOT = Array.isArray(config.proModules) ? config.proModules : [];
const PRO_ASSETS_WRITABLE = config.proAssetsWritable !== false;
const PRO_ASSETS_SAVE_PATH = config.proAssetsSavePath || '';

/**
 * Customify Sites Library plugin state — used by the Welcome sidebar
 * "Customify ready to import sites" card to pick the right CTA.
 *
 *   state:        'active' | 'installed' | 'not-installed'
 *   actionUrl:    where the primary button leads
 *   actionLabel:  primary button copy
 *   detailsUrl:   "Details" link target (GitHub repo)
 *   thumbnailUrl: hero image for the card (empty when missing)
 */
const SITES_PLUGIN = config.sitesPlugin && typeof config.sitesPlugin === 'object' ? config.sitesPlugin : {
  state: 'not-installed',
  actionUrl: '',
  actionLabel: '',
  detailsUrl: '',
  thumbnailUrl: ''
};

/**
 * Recommended free plugins surfaced in the Welcome sidebar. Server has
 * already filtered out plugins the site already has active, so this list
 * only contains rows the user may want to install/activate. `actionUrl`
 * is the standard wp-admin install/activate URL with a valid nonce; the
 * React card fetches it directly to drive an inline state machine.
 *
 *   { slug, name, iconUrl, state: 'installed'|'not-installed',
 *     actionUrl, actionLabel, detailsUrl }
 */
const RECOMMEND_PLUGINS = Array.isArray(config.recommendPlugins) ? config.recommendPlugins : [];

/**
 * Per-user dismissal flag for the Welcome > "Things to do" card. Persisted
 * via user_meta `customify_things_to_do_hidden`. Toggle through the
 * `set_things_to_do_hidden` AJAX task.
 */
const THINGS_TO_DO_HIDDEN = !!config.thingsToDoHidden;

/**
 * Server-detected completion state for the Welcome > Things-to-do checklist.
 * Keys are the IDs from data/things-to-do.js; values are booleans. Missing
 * IDs default to `false` (not done). The dashboard pre-checks rows whose
 * value is true so the user can see what they've already configured.
 */
const THINGS_TO_DO_STATUS = config.thingsToDoStatus && typeof config.thingsToDoStatus === 'object' ? config.thingsToDoStatus : {};

/**
 * Resolve a theme-relative asset path (e.g. `build/images/admin/foo.png`)
 * to an absolute URL using the host theme/plugin base URL.
 */
function assetUrl(relPath = '') {
  if (!BASE_URL) {
    return relPath;
  }
  return BASE_URL + String(relPath).replace(/^\/+/, '');
}

/**
 * Build a Customizer URL with WP's `autofocus` deep-link parameter so a
 * Welcome card can jump straight to the right panel/section.
 *
 *     customizerLink( { panel: 'header_settings' } )
 *     customizerLink( { section: 'title_tagline' } )
 */
function customizerLink(autofocus = {}) {
  const url = CUSTOMIZER_URL;
  if (!url) return '#';
  const params = new URLSearchParams();
  Object.entries(autofocus).forEach(([kind, target]) => {
    params.append(`autofocus[${kind}]`, target);
  });
  const sep = url.includes('?') ? '&' : '?';
  return `${url}${sep}${params.toString()}`;
}
;// ./src/backend/dashboard/app/AppHeader.js
/**
 * Dashboard header — single row: Customify brand (logo + name) on the left,
 * tab nav centered, version pill + tier pill on the right. Layout ported
 * from .bsify-app-header in samples/Dashboard.html.
 */





const LOGO_URL = assetUrl('build/images/admin/customify_logo@2x.png');
function AppHeader({
  tabs,
  activeTab,
  onTabChange
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-app-header",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-app-header__brand",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-app-header__logo",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("img", {
          src: LOGO_URL,
          alt: (0,external_wp_i18n_namespaceObject.__)('Customify', 'customify')
        })
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Tabs, {
      items: tabs,
      active: activeTab,
      onChange: onTabChange,
      ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Dashboard sections', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-app-header__version",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(Pill, {
        children: ["v", PLUGIN_VERSION]
      }), PRO_ACTIVE ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Pill, {
        variant: "pro",
        children: (0,external_wp_i18n_namespaceObject.__)('Pro', 'customify')
      }) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Pill, {
        variant: "free",
        children: (0,external_wp_i18n_namespaceObject.__)('Free', 'customify')
      })]
    })]
  });
}
;// ./src/backend/dashboard/app/AppFooter.js
/**
 * Dashboard footer — one row: WP credit + plugin credit on the left,
 * WP version on the right. Layout ported from .bsify-app-footer.
 */





function AppFooter() {
  const credit = (0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: plugin version. */
  (0,external_wp_i18n_namespaceObject.__)('Thank you for creating with <wp>WordPress</wp> · Customify v%s by <pm>PressMaximum</pm>', 'customify'), PLUGIN_VERSION), {
    wp: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
      href: "https://wordpress.org/"
    }),
    pm: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
      href: "https://pressmaximum.com/"
    })
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-app-footer",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      children: credit
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pm-app-footer__right",
      children: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: WordPress version number. */
      (0,external_wp_i18n_namespaceObject.__)('Get Version %s', 'customify'), WP_VERSION)
    })]
  });
}
;// external ["wp","data"]
var external_wp_data_namespaceObject = window["wp"]["data"];
;// external ["wp","notices"]
var external_wp_notices_namespaceObject = window["wp"]["notices"];
;// ./src/backend/dashboard/app/components/Notices.js
/**
 * Snackbar host — renders any notice dispatched into the @wordpress/notices
 * store with `type: 'snackbar'`. Same primitive the block editor uses, so
 * dashboard toasts inherit Gutenberg's auto-dismiss timing + visual style.
 *
 * Mounted via a portal to document.body so the fixed-position snackbar
 * host escapes the dashboard's #customify-dashboard scroll context. The
 * wrapper div is what we position (bottom-right) — newer
 * @wordpress/components versions sometimes ship the SnackbarList without a
 * fixed-position rule of its own, which is why CSS targeting
 * .components-snackbar-list directly doesn't always reach the toast.
 *
 * Mount once at the root (App.js); any component can dispatch via
 *   const { createNotice } = useDispatch( noticesStore );
 *   createNotice( 'success' | 'error' | 'warning' | 'info', message,
 *                 { type: 'snackbar' } );
 */






function Notices() {
  const notices = (0,external_wp_data_namespaceObject.useSelect)(select => select(external_wp_notices_namespaceObject.store).getNotices(), []);
  const {
    removeNotice
  } = (0,external_wp_data_namespaceObject.useDispatch)(external_wp_notices_namespaceObject.store);
  const snackbarNotices = notices.filter(n => n.type === 'snackbar');
  if (!snackbarNotices.length) {
    return null;
  }
  return (0,external_ReactDOM_namespaceObject.createPortal)(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "pm-snackbar-host",
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SnackbarList, {
      notices: snackbarNotices,
      className: "pm-snackbar-list",
      onRemove: removeNotice
    })
  }), document.body);
}
;// ./src/backend/dashboard/app/routes.js
/**
 * Tab definitions for the dashboard. Single source of truth — AppHeader's
 * Tabs strip and the App's pane switcher both consume this list.
 *
 * Tab IDs map to URL hash fragments (#welcome, #settings, …) for deep links.
 *
 * "Free vs Pro" is dropped when the Customify Pro plugin is active — there's
 * no upsell to make and the user already has every feature on the chart.
 */



const ALL_TABS = [{
  id: 'welcome',
  label: (0,external_wp_i18n_namespaceObject.__)('Dashboard', 'customify')
}, {
  id: 'settings',
  label: (0,external_wp_i18n_namespaceObject.__)('Settings', 'customify')
}, {
  id: 'free-vs-pro',
  label: (0,external_wp_i18n_namespaceObject.__)('Free vs Pro', 'customify')
}, {
  id: 'changelog',
  label: (0,external_wp_i18n_namespaceObject.__)('Changelog', 'customify')
}];
const TABS = PRO_ACTIVE ? ALL_TABS.filter(t => t.id !== 'free-vs-pro') : ALL_TABS;
const DEFAULT_TAB = 'welcome';
function isValidTab(id) {
  return TABS.some(t => t.id === id);
}
;// ./src/backend/dashboard/app/useHashRoute.js
/**
 * Two-way sync between the active tab and window.location.hash so that
 *   a) deep links (?page=customify#settings) land on the right tab
 *   b) tab clicks update the URL without reloading
 *   c) browser back/forward + manual hash edits update the active tab
 *
 * Validation lives in routes.js — invalid hashes fall back to the default.
 */



function readHash() {
  const raw = window.location.hash.replace(/^#/, '');
  return isValidTab(raw) ? raw : DEFAULT_TAB;
}
function useHashRoute() {
  const [tab, setTab] = (0,external_wp_element_namespaceObject.useState)(readHash);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const onHashChange = () => setTab(readHash());
    window.addEventListener('hashchange', onHashChange);
    return () => window.removeEventListener('hashchange', onHashChange);
  }, []);
  const navigate = (0,external_wp_element_namespaceObject.useCallback)(id => {
    if (!isValidTab(id)) {
      return;
    }
    if (window.location.hash !== `#${id}`) {
      window.location.hash = id;
    } else {
      setTab(id);
    }
  }, []);
  return [tab, navigate];
}
;// external ["wp","primitives"]
var external_wp_primitives_namespaceObject = window["wp"]["primitives"];
;// ./node_modules/@wordpress/icons/build-module/library/info.mjs
// packages/icons/src/library/info.tsx


var info_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { fillRule: "evenodd", clipRule: "evenodd", d: "M5.5 12a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0ZM12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4v1.5h-1.5V8h1.5Zm0 8v-5h-1.5v5h1.5Z" }) });

//# sourceMappingURL=info.mjs.map

;// ./src/backend/dashboard/app/data/guides.js
/**
 * Multi-page guides shown in the GuidePopover. Each entry is an array of
 * pages: { iconName, title, body }. The body is JSX so we can interpolate
 * <strong> + <code> safely without dangerouslySetInnerHTML.
 *
 * Icon names map to the guide-* entries in ui/Icon/icons.js — those are
 * the 240×160 illustration SVGs ported verbatim from sample's POPOVERS
 * inline-script constants (ICON_BLOCK_GRID / ICON_DESIGN_KIT / etc.).
 */



const GUIDES = {
  'browse-blocks': [{
    iconName: 'guide-block-grid',
    title: (0,external_wp_i18n_namespaceObject.__)('Getting Started with Customify', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('To start creating a page, you can add blocks by clicking the plus icon located on the left of the top toolbar.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('The Customify category will appear at the top of the block list, with each block highlighted in blue for easy identification.', 'customify')
      })]
    })
  }, {
    iconName: 'guide-layers',
    title: (0,external_wp_i18n_namespaceObject.__)('Section is your full-width wrapper', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("p", {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {
          children: (0,external_wp_i18n_namespaceObject.__)('Section', 'customify')
        }), ' ', (0,external_wp_i18n_namespaceObject.__)('always renders edge-to-edge. Drop one in to define a band of your page — hero, features, footer, anything.', 'customify')]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Use the alignment toolbar to switch between contained, wide, and full width. The background, padding, and overflow are all set on the Section, not on its children.', 'customify')
      })]
    })
  }, {
    iconName: 'guide-build',
    title: (0,external_wp_i18n_namespaceObject.__)('Container handles inner layout', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("p", {
        children: [(0,external_wp_i18n_namespaceObject.__)('Inside every Section sits one or more', 'customify'), ' ', /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {
          children: (0,external_wp_i18n_namespaceObject.__)('Container', 'customify')
        }), ' ', (0,external_wp_i18n_namespaceObject.__)('blocks. Each Container has three modes: Stack (vertical), Grid (responsive columns), or Flex (horizontal).', 'customify')]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Switch modes from the sidebar — the same Container can become a hero column, a 3-up feature grid, or a navigation row without rebuilding it.', 'customify')
      })]
    })
  }, {
    iconName: 'guide-block-grid',
    title: (0,external_wp_i18n_namespaceObject.__)('Heading, Button, Image — the essentials', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Three content blocks cover most needs: Heading with full typography control, Button with built-in icon and hover states, and Image with focal-point cropping and lazy-loading by default.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)("That's it. Five blocks total in Free, plus core WordPress blocks. No bloat, no overlap.", 'customify')
      })]
    })
  }],
  'design-kit': [{
    iconName: 'guide-design-kit',
    title: (0,external_wp_i18n_namespaceObject.__)('Browse the Design Kit library', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('A Design Kit is a curated set of Block Patterns styled for a specific niche — Spa, Handyman, Photography, and more on the way.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Open the kit library from the Welcome tab and preview each kit before you commit. Every pattern in a kit shares the same color palette, type scale, and spacing rhythm.', 'customify')
      })]
    })
  }, {
    iconName: 'guide-layers',
    title: (0,external_wp_i18n_namespaceObject.__)('Import in one click', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Pick a kit, click Import, and Customify drops a complete homepage into your site as a draft. Nothing goes live until you publish.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('The import is non-destructive — your existing pages, posts, and theme settings are untouched.', 'customify')
      })]
    })
  }, {
    iconName: 'guide-build',
    title: (0,external_wp_i18n_namespaceObject.__)('Replace, then publish', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Edit any imported page in the regular block editor. Replace demo headlines, swap images for your own, and tune colors via global styles.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Most users go from import to live homepage in under 10 minutes. You can keep iterating after launch — the patterns are just blocks.', 'customify')
      })]
    })
  }],
  performance: [{
    iconName: 'guide-gauge',
    title: (0,external_wp_i18n_namespaceObject.__)('Per-block asset loading is on by default', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Customify scans each rendered page and only ships the CSS and JS for blocks that actually appear there. A page with three blocks loads three blocks worth of assets — not the whole library.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('This single setting is what gets most sites to PageSpeed 90+ without further tuning.', 'customify')
      })]
    })
  }, {
    iconName: 'guide-layers',
    title: (0,external_wp_i18n_namespaceObject.__)('Inline critical CSS for faster paint', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      children: (0,external_wp_i18n_namespaceObject.__)('For an extra boost, enable Preload critical CSS. Above-the-fold styles are inlined directly into the page <head>, so the browser starts painting before any stylesheet downloads.', 'customify')
    })
  }, {
    iconName: 'guide-gauge',
    title: (0,external_wp_i18n_namespaceObject.__)('Local fonts and CSS output mode', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Toggle Load Google Fonts locally to download fonts to your server — better performance and GDPR-friendly.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Switch the CSS output mode between per-page files (default), inline (zero requests), or a single global file (best for static sites).', 'customify')
      })]
    })
  }],
  'product-tour': [{
    iconName: 'guide-play',
    title: (0,external_wp_i18n_namespaceObject.__)('A 2-minute walkthrough', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      children: (0,external_wp_i18n_namespaceObject.__)('The fastest way to understand Customify is to watch it in action. The video walks through Container modes — Stack, Grid, and Flex — and shows how to build a complete hero from scratch.', 'customify')
    })
  }, {
    iconName: 'guide-build',
    title: (0,external_wp_i18n_namespaceObject.__)('See the editor flow', 'customify'),
    body: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Watch how Section, Container, and content blocks combine in the inserter and the List View.', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Pay attention to how Container modes are switched in the sidebar — that single dropdown is the whole layout system.', 'customify')
      })]
    })
  }]
};
;// ./src/backend/dashboard/app/components/GuidePopover.js
/**
 * Multi-page guide popover — composition of <Modal> + a paged hero/body.
 * Driven by a `guideKey` prop; content lives in app/data/guides.js.
 *
 * Keyboard shortcuts (in addition to Modal's ESC):
 *   ←  previous page
 *   →  next page (or finish on last)
 */






function GuidePopover({
  guideKey,
  isOpen,
  onClose
}) {
  const [page, setPage] = (0,external_wp_element_namespaceObject.useState)(0);

  // Reset to first page each time the popover opens or guideKey changes.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (isOpen) {
      setPage(0);
    }
  }, [isOpen, guideKey]);
  const pages = guideKey ? GUIDES[guideKey] || [] : [];
  const isLast = page === pages.length - 1;
  const current = pages[page];
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!isOpen) {
      return undefined;
    }
    const onKey = e => {
      if (e.key === 'ArrowRight') {
        if (isLast) {
          onClose();
        } else {
          setPage(p => p + 1);
        }
      } else if (e.key === 'ArrowLeft' && page > 0) {
        setPage(p => p - 1);
      }
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [isOpen, page, isLast, onClose]);
  if (!current) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal, {
    isOpen: isOpen,
    onClose: onClose,
    size: "lg",
    ariaLabel: current.title,
    closeOnOverlayClick: true,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal.Header, {
      onClose: onClose,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-guide__hero",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: current.iconName,
          size: 240
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-guide__pagination",
        children: pages.map((_, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
          type: "button",
          className: 'pm-guide__dot' + (i === page ? ' pm-guide__dot--active' : ''),
          onClick: () => setPage(i),
          "aria-label": `Go to page ${i + 1}`
        }, i))
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal.Body, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h3", {
        className: "pm-guide__title",
        children: current.title
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-guide__body",
        children: current.body
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal.Footer, {
      align: "between",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
        variant: "ghost",
        disabled: page === 0,
        onClick: () => setPage(page - 1),
        children: (0,external_wp_i18n_namespaceObject.__)('Previous', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
        variant: "primary",
        onClick: () => isLast ? onClose() : setPage(page + 1),
        children: isLast ? (0,external_wp_i18n_namespaceObject.__)('Finish', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Next', 'customify')
      })]
    })]
  });
}
;// ./src/backend/dashboard/app/data/pro-modules.js
/**
 * Pro modules listing rendered on the Welcome tab and (in compact form) on
 * FreeVsPro. Each row has title + description. `docHref` is optional (not
 * all Pro modules have a public doc page). Optional `statusPill` adds a
 * small status tag next to the title. Optional `subs` nests sub-modules.
 *
 * Source: https://pressmaximum.com/docs/customify/customify-pro-modules/
 * Order matches the documentation index page.
 */


const DOCS_BASE = 'https://pressmaximum.com/docs/customify/customify-pro-modules/';
const PRO_MODULES = [{
  title: (0,external_wp_i18n_namespaceObject.__)('Header Sticky', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Keep your header accessible as users scroll, with a unique animated style.', 'customify'),
  docHref: DOCS_BASE + 'header-sticky/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Header Transparent', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Make your website stand out with a transparent header overlaid on hero sections.', 'customify'),
  docHref: DOCS_BASE + 'header-transparent/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Header and Footer Builder Booster', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Get more header and footer builder items plus advanced styling options.', 'customify'),
  docHref: DOCS_BASE + 'advanced-header-footer-builder/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Scroll To Top', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('A scroll-to-top button with smooth animation for a better reader experience.', 'customify'),
  docHref: DOCS_BASE + 'scroll-to-top/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Blog Pro', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Show off your posts in any layout — grid, list, masonry — with the Blog Pro module.', 'customify'),
  docHref: DOCS_BASE + 'blog-pro/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Portfolio', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Show off your best projects in a beautiful, customizable layout.', 'customify'),
  docHref: DOCS_BASE + 'portfolio/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Multiple Headers', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Create unique headers for each page, post, archive, or WooCommerce surface.', 'customify'),
  docHref: DOCS_BASE + 'multiple-headers/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Mega Menu', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Create mega menus for sites that need more space for navigation.', 'customify'),
  docHref: DOCS_BASE + 'mega-menu/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Advanced Styling', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Control layout and typography for page header title, cover, and titlebar.', 'customify'),
  docHref: DOCS_BASE + 'advanced-styling/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Typekit Fonts', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Add Typekit fonts and use them on Customify-powered websites.', 'customify'),
  docHref: DOCS_BASE + 'typekit-fonts/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Custom Fonts', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Upload self-hosted fonts and use them on Customify-powered websites.', 'customify'),
  docHref: DOCS_BASE + 'custom-fonts/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Customify Hooks', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Add custom hook scripts directly through the dashboard.', 'customify'),
  docHref: DOCS_BASE + 'customify-hooks/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('WooCommerce Booster', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Creative control of style and layout options for your shop.', 'customify'),
  docHref: DOCS_BASE + 'woocommerce-booster/',
  subs: [{
    title: (0,external_wp_i18n_namespaceObject.__)('WC Single Product Layouts', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('More beautiful layouts for your single product page.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-single-product-layouts/'
  }, {
    title: (0,external_wp_i18n_namespaceObject.__)('WC Off Canvas Filter', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Add off-canvas product filtering for shop and product archive pages.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-off-canvas-filter/'
  }, {
    title: (0,external_wp_i18n_namespaceObject.__)('WC Gallery Slider', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Slider gallery for product images.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-gallery-slider/'
  }, {
    title: (0,external_wp_i18n_namespaceObject.__)('WC Quick View', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Product quick-view modal for product listings.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-quick-view/'
  }]
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Infinity Scroll', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Auto-loads the next posts and products as the reader approaches the bottom of the page.', 'customify'),
  docHref: DOCS_BASE + 'infinity-scroll/'
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Cookie Notice', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('GDPR-friendly cookie consent banner with customizable text and styling.', 'customify'),
  docHref: DOCS_BASE + 'cookie-notice/'
}];
;// ./src/backend/dashboard/app/api/ajax.js
/**
 * Shared admin-ajax.php client. All dashboard requests go through a single
 * `action` (customify_dashboard); the `task` field routes to the right
 * handler in PHP (Customify_Theme_Dashboard::ajax_dispatch).
 *
 * Reads URL + nonce from the bootstrap window.customifyDashboard object
 * emitted by Customify_Theme_Dashboard::enqueue_assets.
 *
 * Usage:
 *   const data = await ajaxCall( 'get_settings' );
 *   await ajaxCall( 'save_settings', { payload: state } );
 *
 * Object values are JSON-stringified before being sent — server-side
 * handlers `json_decode` the matching field. Throws on HTTP error or when
 * WP responds with `success: false` so callers can use try/catch.
 */

const ACTION = 'customify_dashboard';
async function ajaxCall(task, data = {}) {
  const config = typeof window !== 'undefined' && window.customifyDashboard || {};
  const body = new FormData();
  body.append('action', ACTION);
  body.append('task', task);
  body.append('nonce', config.nonce || '');
  Object.entries(data).forEach(([key, value]) => {
    body.append(key, typeof value === 'object' ? JSON.stringify(value) : value);
  });
  const res = await fetch(config.ajaxUrl || '', {
    method: 'POST',
    credentials: 'same-origin',
    body
  });
  const json = await res.json();
  if (!json || !json.success) {
    throw new Error(json && json.data || `AJAX task ${task} failed`);
  }
  return json.data;
}
;// ./src/backend/dashboard/app/api/pro-modules.js
/**
 * Pro modules AJAX client. The dashboard talks to the server through a
 * single admin-ajax action (`customify_dashboard`); the `task` field
 * routes to the right handler in PHP.
 *
 * `set_module_state` is handled by Customify::theme_dashboard_handle_pro_task
 * which forwards into Customify_Pro::enable_module / disable_module.
 *
 * Resolves to the persisted state echoed by the server, e.g.
 * `{ classKey, enabled }`. Throws on error so callers can revert UI on
 * failure.
 */


function setModuleState(classKey, enabled) {
  return ajaxCall('set_module_state', {
    class_name: classKey,
    enabled: enabled ? '1' : '0'
  });
}

/**
 * Fetch a Pro module's settings schema + current values. Server resolves
 * the module instance via Customify::resolve_pro_module_instance().
 *
 * @param {string} classKey - Pro module class name.
 * @returns {Promise<{fields: Array, values: Object}>}
 */
function getModuleSettings(classKey) {
  return ajaxCall('get_module_settings', {
    class_name: classKey
  });
}

/**
 * Persist a Pro module's settings via Customify_Pro_Module_Base::save().
 * The payload is JSON-serialized at the network boundary by ajaxCall.
 */
function setModuleSettings(classKey, payload) {
  return ajaxCall('set_module_settings', {
    class_name: classKey,
    payload
  });
}
;// ./src/backend/dashboard/app/components/ModuleSettingsModal.js
/**
 * Pro module settings modal — renders the field schema returned by Pro's
 * `Customify_Pro_Module_Base::settings()` and round-trips edits through the
 * `get_module_settings` / `set_module_settings` AJAX tasks.
 *
 * Schema shape (normalized server-side, see Customify::normalize_pro_module_fields):
 *   { type, name, label, desc, content, options? }
 * Supported types: text, select, html (display-only). Other types render as
 * a text input fallback so unknown future field types don't blank out.
 */








function FieldControl({
  field,
  value,
  onChange
}) {
  const type = field.type || 'text';

  // Display-only types render their content/title and have no input.
  if (type === 'html') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-module-modal__html",
      dangerouslySetInnerHTML: {
        __html: field.content || ''
      }
    });
  }
  if (type === 'heading' || type === 'section' || type === 'panel') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-module-modal__heading",
      children: field.label || field.content || ''
    });
  }
  if (type === 'select') {
    const options = field.options || [];
    const fallback = options.length ? options[0].value : '';
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Select, {
      value: value !== null && value !== undefined ? String(value) : fallback,
      onChange: onChange,
      options: options,
      ariaLabel: field.label
    });
  }
  if (type === 'radio_group' || type === 'text_align' || type === 'text_align_no_justify') {
    const options = field.options || [];
    const current = value !== null && value !== undefined ? String(value) : '';
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-module-modal__radio-group",
      role: "radiogroup",
      children: options.map(opt => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("label", {
        className: 'pm-module-modal__radio' + (current === String(opt.value) ? ' is-active' : ''),
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
          type: "radio",
          name: field.name,
          value: opt.value,
          checked: current === String(opt.value),
          onChange: () => onChange(opt.value)
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          children: opt.label
        })]
      }, opt.value))
    });
  }
  if (type === 'image_select') {
    const options = field.options || [];
    const current = value !== null && value !== undefined ? String(value) : '';
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-module-modal__image-select",
      children: options.map(opt => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        type: "button",
        className: 'pm-module-modal__image-option' + (current === String(opt.value) ? ' is-active' : ''),
        onClick: () => onChange(opt.value),
        "aria-pressed": current === String(opt.value),
        "aria-label": opt.label,
        children: [opt.image && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("img", {
          src: opt.image,
          alt: opt.label
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          children: opt.label
        })]
      }, opt.value))
    });
  }
  if (type === 'checkbox') {
    const checked = value === true || value === 1 || value === '1' || value === 'on' || value === 'true';
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-module-modal__toggle",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ToggleSwitch, {
        checked: checked,
        onChange: next => onChange(next ? 1 : 0),
        ariaLabel: field.label
      }), field.checkbox_label && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "pm-module-modal__toggle-label",
        children: field.checkbox_label
      })]
    });
  }
  if (type === 'number' || type === 'slider') {
    const min = field.min !== undefined ? Number(field.min) : undefined;
    const max = field.max !== undefined ? Number(field.max) : undefined;
    const step = field.step !== undefined ? Number(field.step) : 1;
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
      type: "number",
      className: "pm-module-modal__input",
      value: value !== null && value !== undefined && value !== '' ? Number(value) : '',
      min: min,
      max: max,
      step: step,
      placeholder: field.placeholder,
      onChange: e => onChange(e.target.value),
      "aria-label": field.label
    });
  }
  if (type === 'color') {
    const current = typeof value === 'string' && value !== '' ? value : '#000000';
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-module-modal__color",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
        type: "color",
        value: current.startsWith('#') ? current : '#000000',
        onChange: e => onChange(e.target.value),
        "aria-label": field.label
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
        type: "text",
        className: "pm-module-modal__input",
        value: typeof value === 'string' ? value : '',
        placeholder: "#rrggbb / rgba(...)",
        onChange: e => onChange(e.target.value)
      })]
    });
  }
  if (type === 'textarea' || type === 'custom_html' || type === 'text/html') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("textarea", {
      className: "pm-module-modal__textarea",
      rows: field.rows ? Number(field.rows) : 5,
      value: value !== null && value !== undefined ? String(value) : '',
      placeholder: field.placeholder,
      onChange: e => onChange(e.target.value),
      "aria-label": field.label
    });
  }
  if (type === 'email') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
      type: "email",
      className: "pm-module-modal__input",
      value: value !== null && value !== undefined ? String(value) : '',
      placeholder: field.placeholder,
      onChange: e => onChange(e.target.value),
      "aria-label": field.label
    });
  }
  if (type === 'hidden') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
      type: "hidden",
      value: value !== null && value !== undefined ? String(value) : '',
      readOnly: true
    });
  }

  // `text` and any unknown future type fall through to a text input so
  // new schema additions don't blank out the row before the modal learns
  // how to render them.
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("input", {
    type: "text",
    className: "pm-module-modal__input",
    value: value !== null && value !== undefined ? String(value) : '',
    placeholder: field.placeholder,
    onChange: e => onChange(e.target.value),
    "aria-label": field.label
  });
}

/**
 * Render Pro's `desc` string. Pro stores some descs with inline HTML (e.g.
 * Typekit's link to fonts.adobe.com), so let `<a>` tags through but not
 * arbitrary markup.
 * @param root0
 * @param root0.html
 */
function FieldDescription({
  html
}) {
  if (!html) {
    return null;
  }
  // Pro descriptions are simple — usually plain text or a single anchor.
  // Render via createInterpolateElement when an <a> is present so the
  // link gets target=_blank rel=noreferrer; otherwise plain text.
  const anchorMatch = html.match(/<a\s+[^>]*href=["']([^"']+)["'][^>]*>([\s\S]*?)<\/a>/i);
  if (!anchorMatch) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "description",
      children: html
    });
  }
  const [full, href, label] = anchorMatch;
  const before = html.slice(0, html.indexOf(full));
  const after = html.slice(html.indexOf(full) + full.length);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("p", {
    className: "description",
    children: [before, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
      href: href,
      target: "_blank",
      rel: "noreferrer",
      children: label
    }), after]
  });
}
function ModuleSettingsModal({
  isOpen,
  onClose,
  moduleKey,
  moduleName
}) {
  const {
    createNotice,
    removeNotice
  } = (0,external_wp_data_namespaceObject.useDispatch)(external_wp_notices_namespaceObject.store);
  const [status, setStatus] = (0,external_wp_element_namespaceObject.useState)('idle'); // idle | loading | ready | saving | error
  const [fields, setFields] = (0,external_wp_element_namespaceObject.useState)([]);
  const [values, setValues] = (0,external_wp_element_namespaceObject.useState)({});

  // Load schema + values whenever the modal opens for a given module.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!isOpen || !moduleKey) {
      return undefined;
    }
    let alive = true;
    setStatus('loading');
    getModuleSettings(moduleKey).then(res => {
      if (!alive) {
        return;
      }
      setFields(Array.isArray(res?.fields) ? res.fields : []);
      setValues(res?.values && typeof res.values === 'object' ? res.values : {});
      setStatus('ready');
    }).catch(() => {
      if (alive) {
        setStatus('error');
      }
    });
    return () => {
      alive = false;
    };
  }, [isOpen, moduleKey]);
  const onFieldChange = (0,external_wp_element_namespaceObject.useCallback)((name, next) => {
    setValues(prev => ({
      ...prev,
      [name]: next
    }));
  }, []);
  const onSave = (0,external_wp_element_namespaceObject.useCallback)(() => {
    setStatus('saving');
    setModuleSettings(moduleKey, values).then(res => {
      // Pro modules can return server-rendered notices from
      // after_save() (e.g. Typekit's "Could not load font file"
      // when the kit_id is wrong). Surface each one as a snackbar
      // and keep the modal open if any are errors so the user
      // has a chance to fix the input.
      const notices = Array.isArray(res?.notices) ? res.notices : [];
      let hasError = false;
      notices.forEach(n => {
        if (!n || !n.message) {
          return;
        }
        const type = ['success', 'error', 'warning', 'info'].includes(n.type) ? n.type : 'info';
        if (type === 'error') {
          hasError = true;
        }
        const id = `pm-modal-after-save-${Date.now()}-${Math.random()}`;
        createNotice(type, n.message, {
          type: 'snackbar',
          id
        });
        setTimeout(() => removeNotice(id), 4000);
      });
      if (!hasError) {
        const okId = `pm-modal-${Date.now()}`;
        createNotice('success', (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: module name */
        (0,external_wp_i18n_namespaceObject.__)('"%s" settings saved.', 'customify'), moduleName), {
          type: 'snackbar',
          id: okId
        });
        setTimeout(() => removeNotice(okId), 3000);
      }
      if (res?.values && typeof res.values === 'object') {
        setValues(res.values);
      }
      // Pro modules with dynamic schemas (Typekit's loaded fonts
      // `html` field) re-derive their field list inside settings()
      // after each save. Refresh the schema if the server sent one.
      if (Array.isArray(res?.fields) && res.fields.length) {
        setFields(res.fields);
      }
      setStatus('ready');
      if (!hasError) {
        onClose();
      }
    }).catch(() => {
      setStatus('error');
      createNotice('error', (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: module name */
      (0,external_wp_i18n_namespaceObject.__)('Could not save "%s" settings. Please try again.', 'customify'), moduleName), {
        type: 'snackbar'
      });
    });
  }, [moduleKey, moduleName, values, createNotice, removeNotice, onClose]);
  if (!isOpen) {
    return null;
  }
  const title = (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: module name */
  (0,external_wp_i18n_namespaceObject.__)('%s Settings', 'customify'), moduleName || '');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal, {
    isOpen: isOpen,
    onClose: onClose,
    size: "md",
    ariaLabel: title,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ui_Modal.Header, {
      title: title,
      onClose: onClose
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal.Body, {
      children: [status === 'loading' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Loading settings…', 'customify')
      }), status === 'error' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('Could not load module settings. Make sure the module is enabled, then reopen this dialog.', 'customify')
      }), (status === 'ready' || status === 'saving') && fields.map((field, i) => {
        const isDisplay = ['html', 'heading', 'section', 'panel', 'hidden'].includes(field.type);
        return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "pm-module-modal__field",
          children: [field.label && !isDisplay && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("label", {
            className: "pm-module-modal__label",
            children: field.label
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(FieldControl, {
            field: field,
            value: values[field.name],
            onChange: next => onFieldChange(field.name, next)
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(FieldDescription, {
            html: field.desc
          })]
        }, i);
      }), status === 'ready' && fields.length === 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_i18n_namespaceObject.__)('This module has no editable settings.', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_Modal.Footer, {
      align: "end",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
        variant: "secondary",
        onClick: onClose,
        children: (0,external_wp_i18n_namespaceObject.__)('Cancel', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
        variant: "primary",
        onClick: onSave,
        disabled: status !== 'ready' || fields.length === 0,
        children: status === 'saving' ? (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Save changes', 'customify')
      })]
    })]
  });
}
;// ./src/backend/dashboard/app/components/ProModulesCard.js
/**
 * Pro Modules card on the Welcome tab.
 *
 * Two render paths:
 *   1. Pro plugin not active → show the static marketing list (data/pro-modules.js)
 *      with a "Docs" link per row and an "Upgrade Now" header CTA.
 *   2. Pro plugin active → show the server-supplied registry (window.customifyDashboard.proModules)
 *      with a ToggleSwitch per row that flips Customify_Pro::enable_module /
 *      disable_module via the `set_module_state` AJAX task.
 *
 * Toggle is optimistic: state flips immediately, AJAX in background. On
 * failure we revert and surface a snackbar notice.
 */











const UPGRADE_URL = 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=welcome&utm_campaign=pro_modules';
function labelForScope(scope) {
  switch (scope) {
    case 'inline':
      return (0,external_wp_i18n_namespaceObject.__)('Settings', 'customify');
    case 'cpt':
      return (0,external_wp_i18n_namespaceObject.__)('Manage', 'customify');
    case 'customizer':
      return (0,external_wp_i18n_namespaceObject.__)('Open Customizer', 'customify');
    default:
      return '';
  }
}
function DocsLink({
  href
}) {
  if (!href) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
    className: "pm-module-link",
    href: href,
    target: "_blank",
    rel: "noreferrer",
    children: [(0,external_wp_i18n_namespaceObject.__)('Docs', 'customify'), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
      name: "chevron-right",
      size: 12
    })]
  });
}

/* ---------------------------------------------------------------- */
/* Free path — static marketing list                                */
/* ---------------------------------------------------------------- */

function MarketingList() {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleList, {
    children: PRO_MODULES.map((m, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_element_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleRow, {
        title: m.title,
        description: m.description,
        statusPill: m.statusPill,
        className: m.subs ? 'pm-module-row--has-subs' : undefined,
        trailing: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DocsLink, {
          href: m.docHref
        })
      }), m.subs && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-module-submodules",
        children: m.subs.map((sub, j) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleRow, {
          title: sub.title,
          description: sub.description,
          trailing: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DocsLink, {
            href: sub.docHref
          })
        }, j))
      })]
    }, i))
  });
}

/* ---------------------------------------------------------------- */
/* Pro path — server registry with ToggleSwitch                     */
/* ---------------------------------------------------------------- */

function ProList() {
  const {
    createNotice,
    removeNotice
  } = (0,external_wp_data_namespaceObject.useDispatch)(external_wp_notices_namespaceObject.store);

  // Seed local enabled state from the bootstrap snapshot, keyed by classKey.
  const [enabledMap, setEnabledMap] = (0,external_wp_element_namespaceObject.useState)(() => {
    const m = {};
    PRO_MODULES_BOOT.forEach(mod => {
      m[mod.classKey] = !!mod.enabled;
    });
    return m;
  });
  const [pendingMap, setPendingMap] = (0,external_wp_element_namespaceObject.useState)({});
  const [settingsModule, setSettingsModule] = (0,external_wp_element_namespaceObject.useState)(null);
  const moduleByKey = (0,external_wp_element_namespaceObject.useMemo)(() => {
    const map = {};
    PRO_MODULES_BOOT.forEach(mod => {
      map[mod.classKey] = mod;
    });
    return map;
  }, []);
  const topLevel = (0,external_wp_element_namespaceObject.useMemo)(() => PRO_MODULES_BOOT.filter(m => !m.parent), []);
  const toggle = (0,external_wp_element_namespaceObject.useCallback)(classKey => {
    const current = !!enabledMap[classKey];
    const next = !current;
    const moduleName = moduleByKey[classKey] && moduleByKey[classKey].name || classKey;

    // Optimistic flip + mark pending so the switch shows disabled.
    setEnabledMap(prev => ({
      ...prev,
      [classKey]: next
    }));
    setPendingMap(prev => ({
      ...prev,
      [classKey]: true
    }));
    setModuleState(classKey, next).then(res => {
      // Server is authoritative — sync to its echoed flag.
      setEnabledMap(prev => ({
        ...prev,
        [classKey]: !!(res && res.enabled)
      }));
      const noticeId = `pm-toast-${Date.now()}-${classKey}`;
      createNotice('success', (0,external_wp_i18n_namespaceObject.sprintf)(next ? /* translators: %s: module name. */
      (0,external_wp_i18n_namespaceObject.__)('"%s" activated.', 'customify') : /* translators: %s: module name. */
      (0,external_wp_i18n_namespaceObject.__)('"%s" deactivated.', 'customify'), moduleName), {
        type: 'snackbar',
        id: noticeId
      });
      setTimeout(() => removeNotice(noticeId), 3000);
    }).catch(() => {
      // Revert on failure.
      setEnabledMap(prev => ({
        ...prev,
        [classKey]: current
      }));
      createNotice('error', (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: module name. */
      (0,external_wp_i18n_namespaceObject.__)('Could not update "%s". Please try again.', 'customify'), moduleName), {
        type: 'snackbar'
      });
    }).finally(() => {
      setPendingMap(prev => {
        const copy = {
          ...prev
        };
        delete copy[classKey];
        return copy;
      });
    });
  }, [enabledMap, moduleByKey, createNotice, removeNotice]);
  const renderRow = (mod, isSub = false) => {
    const checked = !!enabledMap[mod.classKey];
    const pending = !!pendingMap[mod.classKey];
    // `settingsScope` (PHP: Customify::resolve_pro_module_meta) decides
    // the affordance. Older Pro builds that don't ship the field still
    // fall back to the legacy hasSettings path.
    const scope = mod.settingsScope || (mod.hasSettings ? 'inline' : 'none');
    const canShowAction = checked && !pending;
    const actionLabel = mod.settingsLabel || labelForScope(scope);
    // `requirementMissing` is set by PHP when a hard dependency (e.g.
    // WooCommerce) is not active. We still show the row so the user
    // can see what's available, but the toggle is locked off and a
    // note is appended to the description.
    const requirement = mod.requirementMissing || '';
    const requirementLabel = requirement === 'woocommerce' ? (0,external_wp_i18n_namespaceObject.__)('Requires WooCommerce plugin', 'customify') : requirement ? (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: requirement name (plugin slug). */
    (0,external_wp_i18n_namespaceObject.__)('Requires %s', 'customify'), requirement) : '';
    // `ownedNote` is set when the theme implements the same feature
    // natively — the Pro module is force-disabled by the compatibility
    // filter, so the toggle would no-op without a visible reason.
    const ownedNote = mod.ownedNote || '';
    const annotation = ownedNote || requirementLabel;
    const description = annotation ? `${mod.description} — ${annotation}` : mod.description;
    let action = null;
    if (canShowAction && scope === 'inline') {
      action = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("button", {
        type: "button",
        className: "pm-module-link pm-module-link--settings",
        onClick: () => setSettingsModule(mod),
        children: [actionLabel, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "chevron-right",
          size: 12
        })]
      });
    } else if (canShowAction && mod.settingsHref && (scope === 'customizer' || scope === 'cpt')) {
      action = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
        className: "pm-module-link pm-module-link--settings",
        href: mod.settingsHref,
        children: [actionLabel, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "chevron-right",
          size: 12
        })]
      });
    }
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleRow, {
      title: mod.name,
      description: description,
      className: !isSub && mod.subModules && mod.subModules.length ? 'pm-module-row--has-subs' : undefined,
      leading: mod.canToggle === false ? null : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ToggleSwitch, {
        checked: checked,
        onChange: () => toggle(mod.classKey),
        ariaLabel: mod.name,
        disabled: pending
      }),
      trailing: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
        children: [action, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DocsLink, {
          href: mod.docHref
        })]
      })
    }, mod.classKey);
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleList, {
      children: topLevel.map(mod => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_element_namespaceObject.Fragment, {
        children: [renderRow(mod, false), mod.subModules && mod.subModules.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "pm-module-submodules",
          children: mod.subModules.map(subKey => {
            const sub = moduleByKey[subKey];
            if (!sub) {
              return null;
            }
            return renderRow(sub, true);
          })
        })]
      }, mod.classKey))
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleSettingsModal, {
      isOpen: !!settingsModule,
      onClose: () => setSettingsModule(null),
      moduleKey: settingsModule ? settingsModule.classKey : null,
      moduleName: settingsModule ? settingsModule.name : ''
    })]
  });
}

/* ---------------------------------------------------------------- */
/* Card shell                                                       */
/* ---------------------------------------------------------------- */

function ProModulesCard() {
  const isPro = PRO_ACTIVE && PRO_MODULES_BOOT.length > 0;
  const headerRight = isPro ?
  /*#__PURE__*/
  // <Pill variant="pro">{ __( 'Pro Active', 'customify' ) }</Pill>
  (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_ReactJSXRuntime_namespaceObject.Fragment, {}) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
    className: "pm-header-link",
    href: UPGRADE_URL,
    target: "_blank",
    rel: "noreferrer",
    children: [(0,external_wp_i18n_namespaceObject.__)('Upgrade Now', 'customify'), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
      name: "chevron-right",
      size: 12
    })]
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
    title: (0,external_wp_i18n_namespaceObject.__)('Customify Pro Modules', 'customify'),
    headerRight: headerRight,
    children: isPro ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ProList, {}) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MarketingList, {})
  });
}
;// ./src/backend/dashboard/app/components/SitesImportCard.js
/**
 * Welcome sidebar card promoting the Customify Sites Library plugin —
 * one-click import of ready-made starter sites. Renders one of three CTA
 * states based on bootstrap data:
 *
 *   not-installed → "Download Plugin" (GitHub releases)
 *   installed     → "Activate Plugin" (nonce-signed admin URL)
 *   active        → "View Site Library" (themes.php?page=customify-sites)
 *
 * Mirrors the legacy Customify_Dashboard::box_plugins() output but lives
 * inside the React dashboard's WelcomeLayout sidebar slot.
 */






function SitesImportCard() {
  const {
    state,
    actionUrl,
    actionLabel,
    detailsUrl,
    thumbnailUrl
  } = SITES_PLUGIN;
  const isActive = state === 'active';
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(Card, {
    title: (0,external_wp_i18n_namespaceObject.__)('Customify ready to import sites', 'customify'),
    children: [thumbnailUrl && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-sites-card__thumb",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("img", {
        src: thumbnailUrl,
        alt: (0,external_wp_i18n_namespaceObject.__)('Customify sites library preview', 'customify')
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-sites-card__body",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        children: (0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.__)('<b>Customify Sites</b> is a free add-on for the Customify theme that lets you browse and import ready-made websites with a few clicks.', 'customify'), {
          b: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {})
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "pm-sites-card__actions",
        children: [actionUrl && actionLabel && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
          variant: isActive ? 'secondary' : 'primary',
          href: actionUrl,
          target: state === 'not-installed' ? '_blank' : undefined,
          rel: state === 'not-installed' ? 'noreferrer' : undefined,
          children: actionLabel
        }), detailsUrl && !isActive && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
          className: "pm-sites-card__details",
          href: detailsUrl,
          target: "_blank",
          rel: "noreferrer",
          children: (0,external_wp_i18n_namespaceObject.__)('Details', 'customify')
        })]
      })]
    })]
  });
}
;// ./src/backend/dashboard/app/api/recommend-plugins.js
/**
 * Inline install/activate flow for the Recommend Plugins card — JS-only,
 * no server-side helpers added in the theme. Both URLs are normal
 * wp-admin pages with a valid `_wpnonce`:
 *
 *   install:  /wp-admin/update.php?action=install-plugin&plugin={slug}&_wpnonce=…
 *   activate: /wp-admin/plugins.php?action=activate&plugin={file}&_wpnonce=…
 *
 * The card fetches each URL with `credentials: 'same-origin'` so the user's
 * cookies authenticate the request, parses the HTML response for known
 * outcome markers, and updates the React state. No tab switch, no redirect.
 *
 * Server already returns the install URL as the initial `actionUrl`. After
 * a successful install the response HTML carries an "Activate Plugin"
 * anchor that points at the activate URL — we extract it from the response
 * and stash it for the next click.
 */

/**
 * Fetch a wp-admin URL as an authenticated browser request and return the
 * raw HTML body. Throws on HTTP error so callers can branch on outcome.
 *
 * @param {string} url Full or relative wp-admin URL.
 * @returns {Promise<string>} Response HTML.
 */
async function fetchAdminPage(url) {
  const resp = await fetch(url, {
    method: 'GET',
    credentials: 'same-origin',
    // Prevent browser/cdn from serving a cached copy that could mask a
    // just-completed state change.
    cache: 'no-store',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  });
  if (!resp.ok) {
    throw new Error(`HTTP ${resp.status} fetching ${url}`);
  }
  return resp.text();
}

/**
 * Look for a plain-text fragment inside the HTML body. Forgiving of
 * surrounding markup: strips tags before matching.
 */
function bodyContains(html, needles) {
  const text = String(html).replace(/<script[\s\S]*?<\/script>/gi, '').replace(/<style[\s\S]*?<\/style>/gi, '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
  return needles.some(n => text.includes(n));
}

/**
 * Extract the activate URL from a successful install response. WP renders:
 *
 *   <a href="…/wp-admin/plugins.php?action=activate&amp;plugin=…&amp;_wpnonce=…">Activate Plugin</a>
 *
 * We pull the href, decode &amp; entities back to & so the URL is usable
 * with fetch() / location, and return it.
 *
 * @param {string} html Install-response body.
 * @returns {string} Activate URL or '' when not present.
 */
function extractActivateUrl(html) {
  const match = html.match(/href=["']([^"']*plugins\.php\?action=activate[^"']*)["']/i);
  if (!match) return '';
  return match[1].replace(/&amp;/g, '&').replace(/&#038;/g, '&');
}

/**
 * Run the install URL and resolve { activateUrl } on success. Throws on
 * any error WP prints in the response body.
 *
 * @param {string} installUrl WP install URL with valid nonce.
 * @param {string} pluginName Human label for error messages.
 * @returns {Promise<{ activateUrl: string }>}
 */
async function runInstall(installUrl, pluginName) {
  const html = await fetchAdminPage(installUrl);
  if (bodyContains(html, ['destination directory already exists'])) {
    throw Object.assign(new Error(`A folder for "${pluginName}" already exists. Delete it from wp-content/plugins/ and try again.`), {
      code: 'destination_exists'
    });
  }
  if (bodyContains(html, ['Sorry, you are not allowed', 'You do not have sufficient permissions'])) {
    throw Object.assign(new Error(`You don't have permission to install "${pluginName}".`), {
      code: 'forbidden'
    });
  }
  if (bodyContains(html, ['Plugin install failed', 'Installation failed'])) {
    throw new Error(`Installation failed for "${pluginName}".`);
  }
  const activateUrl = extractActivateUrl(html);
  if (!activateUrl && !bodyContains(html, ['Successfully installed', 'Plugin already installed'])) {
    // Neither the success marker nor an activate link is present —
    // treat it as an unknown failure so the UI can surface it.
    throw new Error(`Could not confirm "${pluginName}" installed.`);
  }
  return {
    activateUrl
  };
}

/**
 * Run the activate URL. Resolves on success, throws when the response
 * body contains a known WP failure marker.
 *
 * @param {string} activateUrl WP activate URL with valid nonce.
 * @param {string} pluginName  Human label for error messages.
 * @returns {Promise<void>}
 */
async function runActivate(activateUrl, pluginName) {
  if (!activateUrl) {
    throw new Error(`Activate link missing for "${pluginName}". Reload the page and retry.`);
  }
  const html = await fetchAdminPage(activateUrl);
  if (bodyContains(html, ['Plugin could not be activated', 'caused a fatal error', 'Sorry, you are not allowed'])) {
    throw new Error(`Could not activate "${pluginName}".`);
  }
  // On success WP redirects (browser follows it transparently) and the
  // final page is plugins.php with `?activate=true`. Either we land on
  // that listing or on a "Plugin activated" notice — both fine.
}
;// ./src/backend/dashboard/app/components/RecommendPluginsCard.js
/**
 * Welcome sidebar — recommended free plugins from wordpress.org.
 *
 * Server (Customify::theme_dashboard_inject_recommend_plugins) ships each
 * row with a ready-to-use `actionUrl`:
 *
 *   not-installed → /wp-admin/update.php?action=install-plugin&plugin=…&_wpnonce=…
 *   installed     → /wp-admin/plugins.php?action=activate&plugin=…&_wpnonce=…
 *
 * Both URLs are full wp-admin pages with valid nonces. We fetch them
 * directly with `credentials: 'same-origin'` so the user's auth cookies
 * gate the action, parse the HTML response for the relevant outcome
 * markers, and drive a per-row state machine — no PHP-side AJAX handler,
 * no tab switch, no redirect.
 *
 *   not-installed → installing → installed → activating → active
 *                                                ↘ error
 *
 * If `fetch` is unavailable (legacy browser, blocked by extension) the
 * Button still has the original `href`, so the click falls through to a
 * normal browser navigation as a graceful fallback.
 */









function actionFor(state, plugin) {
  switch (state) {
    case 'installing':
      return {
        label: (0,external_wp_i18n_namespaceObject.__)('Installing…', 'customify'),
        busy: true,
        variant: 'secondary',
        disabled: true
      };
    case 'installed':
      return {
        label: (0,external_wp_i18n_namespaceObject.__)('Activate', 'customify'),
        variant: 'primary'
      };
    case 'activating':
      return {
        label: (0,external_wp_i18n_namespaceObject.__)('Activating…', 'customify'),
        busy: true,
        variant: 'primary',
        disabled: true
      };
    case 'active':
      return {
        label: (0,external_wp_i18n_namespaceObject.__)('Active', 'customify'),
        variant: 'tertiary',
        disabled: true
      };
    case 'not-installed':
    default:
      return {
        label: plugin.actionLabel || (0,external_wp_i18n_namespaceObject.__)('Install Now', 'customify'),
        variant: 'secondary'
      };
  }
}
function RecommendPluginsCard() {
  const {
    createNotice
  } = (0,external_wp_data_namespaceObject.useDispatch)(external_wp_notices_namespaceObject.store);

  // Per-slug client state — server-provided state is the initial seed.
  const [rows, setRows] = (0,external_wp_element_namespaceObject.useState)(() => {
    const seed = {};
    RECOMMEND_PLUGINS.forEach(p => {
      seed[p.slug] = {
        state: p.state || 'not-installed',
        // Server only sends an activate URL for already-installed
        // rows. After we install fresh, we extract it from the
        // response HTML and stash it here for the next click.
        activateUrl: p.state === 'installed' ? p.actionUrl : ''
      };
    });
    return seed;
  });
  if (!RECOMMEND_PLUGINS.length) return null;
  function notify(type, message) {
    createNotice(type, message, {
      type: 'snackbar'
    });
  }
  function patch(slug, next) {
    setRows(prev => ({
      ...prev,
      [slug]: {
        ...prev[slug],
        ...next
      }
    }));
  }
  async function handleInstall(plugin) {
    patch(plugin.slug, {
      state: 'installing'
    });
    try {
      const {
        activateUrl
      } = await runInstall(plugin.actionUrl, plugin.name);
      patch(plugin.slug, {
        state: 'installed',
        activateUrl
      });
      notify('success', (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: plugin name */
      (0,external_wp_i18n_namespaceObject.__)('"%s" installed.', 'customify'), plugin.name));
    } catch (err) {
      patch(plugin.slug, {
        state: 'not-installed'
      });
      notify('error', err && err.message || (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: plugin name */
      (0,external_wp_i18n_namespaceObject.__)('Could not install "%s".', 'customify'), plugin.name));
    }
  }
  async function handleActivate(plugin, activateUrl) {
    patch(plugin.slug, {
      state: 'activating'
    });
    try {
      await runActivate(activateUrl, plugin.name);
      patch(plugin.slug, {
        state: 'active'
      });
      notify('success', (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: plugin name */
      (0,external_wp_i18n_namespaceObject.__)('"%s" activated.', 'customify'), plugin.name));
    } catch (err) {
      patch(plugin.slug, {
        state: 'installed'
      });
      notify('error', err && err.message || (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: plugin name */
      (0,external_wp_i18n_namespaceObject.__)('Could not activate "%s".', 'customify'), plugin.name));
    }
  }
  function onClick(plugin, e) {
    const row = rows[plugin.slug] || {};
    const state = row.state || 'not-installed';

    // Fail-open: if fetch isn't available let the anchor follow href.
    if (typeof fetch !== 'function') return;
    if (state === 'not-installed') {
      e.preventDefault();
      handleInstall(plugin);
    } else if (state === 'installed') {
      e.preventDefault();
      handleActivate(plugin, row.activateUrl || plugin.actionUrl);
    } else {
      // installing / activating / active — disabled button shouldn't
      // fire onClick, but if a screen reader or assistive tech does,
      // just block.
      e.preventDefault();
    }
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
    title: (0,external_wp_i18n_namespaceObject.__)('Recommend Plugins', 'customify'),
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "pm-recommend-plugins",
      children: RECOMMEND_PLUGINS.map(plugin => {
        const row = rows[plugin.slug] || {};
        const state = row.state || 'not-installed';
        const action = actionFor(state, plugin);
        const isFinal = state === 'active';
        return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
          className: 'pm-recommend-plugins__item' + (isFinal ? ' pm-recommend-plugins__item--active' : ''),
          children: [plugin.iconUrl && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("img", {
            className: "pm-recommend-plugins__icon",
            src: plugin.iconUrl,
            alt: "",
            loading: "lazy"
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
            className: "pm-recommend-plugins__body",
            children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
              className: "pm-recommend-plugins__name",
              children: plugin.name
            }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
              className: "pm-recommend-plugins__actions",
              children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
                variant: action.variant,
                size: "small",
                href: isFinal ? undefined : plugin.actionUrl,
                disabled: action.disabled,
                isBusy: action.busy,
                onClick: e => onClick(plugin, e),
                children: action.label
              }), plugin.detailsUrl && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
                className: "pm-recommend-plugins__details",
                href: plugin.detailsUrl,
                target: "_blank",
                rel: "noreferrer",
                children: (0,external_wp_i18n_namespaceObject.__)('Details', 'customify')
              })]
            })]
          })]
        }, plugin.slug);
      })
    })
  });
}
;// ./src/backend/dashboard/app/data/things-to-do.js
/**
 * Welcome "Things to do" checklist items. The `guide` field links to a
 * GuidePopover key (data/guides.js).
 */



const THINGS_TO_DO = [{
  id: 'logo',
  title: (0,external_wp_i18n_namespaceObject.__)('Upload your logo', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Set the brand logo and site identity in the WordPress Customizer.', 'customify'),
  guide: 'logo',
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Set Up', 'customify'),
  ctaHref: customizerLink({
    section: 'title_tagline'
  })
}, {
  id: 'header-builder',
  title: (0,external_wp_i18n_namespaceObject.__)('Build the header', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Use the WYSIWYG header builder inside the Customizer to drop in logo, menu, search, cart, and more.', 'customify'),
  guide: 'header-builder',
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Set Up', 'customify'),
  ctaHref: customizerLink({
    panel: 'header_settings'
  })
}, {
  id: 'styling',
  title: (0,external_wp_i18n_namespaceObject.__)('Pick brand colors and typography', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Set primary, secondary, link, and heading colors plus typography presets so every block reads on-brand.', 'customify'),
  guide: 'styling',
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Set Up', 'customify'),
  ctaHref: customizerLink({
    panel: 'styling_panel'
  })
}, {
  id: 'icons',
  title: (0,external_wp_i18n_namespaceObject.__)('Pick a Font Awesome version', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Choose between v4, v6, or the v6 + v4/v5 fallback to keep legacy icons working.', 'customify'),
  guide: 'icons',
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Set Up', 'customify'),
  ctaHref: '#settings'
}];
;// ./src/backend/dashboard/app/data/theme-customizer.js
/**
 * Welcome "Theme Customizer" 2-col grid items. Each links into a panel or
 * section of the WP Customizer using `autofocus` deep-link params.
 */



const THEME_CUSTOMIZER_ITEMS = [{
  title: (0,external_wp_i18n_namespaceObject.__)('Logo & Site Identity', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Upload logo, site title, tagline', 'customify'),
  href: customizerLink({
    section: 'title_tagline'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Layout Settings', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Container width, content layout', 'customify'),
  href: customizerLink({
    section: 'global_layout_section'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Header Builder', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('WYSIWYG header drag-and-drop', 'customify'),
  href: customizerLink({
    panel: 'header_settings'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Footer Builder', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('WYSIWYG footer drag-and-drop', 'customify'),
  href: customizerLink({
    panel: 'footer_settings'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Styling', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Brand colors & site palette', 'customify'),
  href: customizerLink({
    panel: 'styling_panel'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Typography', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Font family, size, line-height', 'customify'),
  href: customizerLink({
    panel: 'typography_panel'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Sidebar Settings', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Sidebar layout per context', 'customify'),
  href: customizerLink({
    section: 'sidebar_layout_section'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Titlebar Settings', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Page header title bar', 'customify'),
  href: customizerLink({
    section: 'titlebar'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Blog Posts', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Blog listing & single post', 'customify'),
  href: customizerLink({
    panel: 'blog_panel'
  })
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Homepage Settings', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Static front page setup', 'customify'),
  href: customizerLink({
    section: 'static_front_page'
  })
}];
;// ./src/backend/dashboard/app/data/sidebar.js
/**
 * Sidebar content shared between Welcome + Changelog tabs:
 *   - License (Pro upgrade) card
 *   - Resources list (Documentation, Changelog, Support)
 *   - Cross-promo product rows
 *   - Review card
 */




const LICENSE_DATA = {
  title: (0,external_wp_i18n_namespaceObject.__)('Unlock the Pro features', 'customify'),
  tagline: (0,external_wp_i18n_namespaceObject.__)('Everything in Free, plus:', 'customify'),
  features: [(0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.__)('<b>Header & Footer Builder Booster</b> with extra items', 'customify'), {
    b: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {})
  }), (0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.__)('<b>Header Transparent</b> & Sticky modules', 'customify'), {
    b: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {})
  }), (0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.__)('<b>WooCommerce Booster</b> with shop layouts', 'customify'), {
    b: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {})
  }), (0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.__)('<b>Mega Menu</b> & Multiple Headers', 'customify'), {
    b: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {})
  }), (0,external_wp_element_namespaceObject.createInterpolateElement)((0,external_wp_i18n_namespaceObject.__)('<b>1 year</b> updates & support', 'customify'), {
    b: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {})
  })],
  price: '$59',
  priceUnit: (0,external_wp_i18n_namespaceObject.__)('/year · 1 site', 'customify'),
  priceFootnote: (0,external_wp_i18n_namespaceObject.__)('15-day money-back', 'customify'),
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Upgrade to Pro →', 'customify'),
  ctaHref: 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=sidebar&utm_campaign=license_card'
};
const RESOURCES = [{
  icon: 'doc',
  label: (0,external_wp_i18n_namespaceObject.__)('Documentation', 'customify'),
  href: 'https://pressmaximum.com/docs/customify/'
}, {
  icon: 'clock-large',
  label: (0,external_wp_i18n_namespaceObject.__)('Changelog', 'customify'),
  href: '#changelog'
}, {
  icon: 'star-large',
  label: (0,external_wp_i18n_namespaceObject.__)('Join Facebook community', 'customify'),
  href: 'https://www.facebook.com/groups/133106770857743'
}, {
  icon: 'mail',
  label: (0,external_wp_i18n_namespaceObject.__)('Contact support', 'customify'),
  href: 'https://pressmaximum.com/support/'
}];
const PRODUCTS = [{
  initials: 'CS',
  gradient: 'blue',
  name: (0,external_wp_i18n_namespaceObject.__)('Customify Sites Library', 'customify'),
  meta: (0,external_wp_i18n_namespaceObject.__)('Ready-made starter sites you can import', 'customify'),
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Get Plugin', 'customify'),
  ctaHref: 'https://github.com/PressMaximum/customify-sites-library'
}, {
  initials: 'OP',
  gradient: 'purple',
  name: (0,external_wp_i18n_namespaceObject.__)('OnePress Theme', 'customify'),
  meta: (0,external_wp_i18n_namespaceObject.__)('Free one-page WordPress theme', 'customify'),
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('View Theme', 'customify'),
  ctaHref: 'https://wordpress.org/themes/onepress/'
}];
const REVIEW = {
  rating: 5,
  message: (0,external_wp_i18n_namespaceObject.__)('A 5-star review on WordPress.org keeps the project alive and helps others find it.', 'customify'),
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Leave a review →', 'customify'),
  ctaHref: 'https://wordpress.org/support/theme/customify/reviews/?filter=5#new-post'
};
;// ./src/backend/dashboard/app/api/preferences.js
/**
 * User preferences AJAX client. Lightweight wrappers for tasks that
 * persist a single per-user toggle (no settings round-trip needed).
 */



/**
 * Persist whether the Welcome > "Things to do" card is dismissed for the
 * current user. Server stores under user_meta `customify_things_to_do_hidden`.
 */
function setThingsToDoHidden(hidden) {
  return ajaxCall('set_things_to_do_hidden', {
    hidden: hidden ? '1' : '0'
  });
}
;// ./src/backend/dashboard/app/tabs/Welcome.js
/**
 * Welcome tab — Hero, Things-to-do checklist, Theme Customizer grid, Pro
 * Modules card, sidebar with License + Resources + Products + Review.
 *
 * Local state:
 *   - which checklist rows are checked
 *   - which guide popover is open (string key or null)
 *   - whether the Things-to-do card is hidden
 */















function HeroPreview() {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pm-hero-preview",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-hero-preview__bar pm-hero-preview__bar--lg"
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-hero-preview__bar pm-hero-preview__bar--md"
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-hero-preview__img"
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-hero-preview__bar pm-hero-preview__bar--sm"
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pm-hero-preview__row",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-hero-preview__pill"
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-hero-preview__pill pm-hero-preview__pill--outline"
      })]
    })]
  });
}
function Welcome() {
  // Seed checklist state from the server-detected completion map so items
  // the user has already configured (logo, primary color, FA version, …)
  // show up pre-checked. Local toggles afterwards override the seed.
  const [checked, setChecked] = (0,external_wp_element_namespaceObject.useState)(() => {
    const seed = {};
    THINGS_TO_DO.forEach(item => {
      seed[item.id] = !!THINGS_TO_DO_STATUS[item.id];
    });
    return seed;
  });
  const [openGuide, setOpenGuide] = (0,external_wp_element_namespaceObject.useState)(null);
  const [todoHidden, setTodoHidden] = (0,external_wp_element_namespaceObject.useState)(THINGS_TO_DO_HIDDEN);
  const toggleCheck = id => setChecked(prev => ({
    ...prev,
    [id]: !prev[id]
  }));

  // Optimistic hide — flip immediately, persist in background. Revert if
  // the AJAX call fails so the user can retry instead of getting stuck
  // with an unsaved preference.
  const onHideTodo = (0,external_wp_element_namespaceObject.useCallback)(() => {
    setTodoHidden(true);
    setThingsToDoHidden(true).catch(() => setTodoHidden(false));
  }, []);
  const main = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Hero, {
      greeting: (0,external_wp_i18n_namespaceObject.__)('Hello admin', 'customify'),
      title: (0,external_wp_i18n_namespaceObject.__)('Welcome to Customify', 'customify'),
      description: (0,external_wp_i18n_namespaceObject.__)('Customify is a fast, multipurpose WordPress theme with a WYSIWYG header & footer builder right inside the Customizer. Set up your branding, layout, typography, and start shipping pages.', 'customify'),
      actions: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(Button, {
        variant: "primary",
        href: CUSTOMIZER_URL || 'customize.php',
        size: "lg",
        className: `pm-button--lg`,
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "plus",
          size: 16
        }), (0,external_wp_i18n_namespaceObject.__)('Open Customizer', 'customify')]
      }),
      preview: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(HeroPreview, {})
    }), !todoHidden && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
      title: (0,external_wp_i18n_namespaceObject.__)('Things to do next', 'customify'),
      headerRight: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Dropdown, {
        triggerLabel: (0,external_wp_i18n_namespaceObject.__)('More options', 'customify'),
        items: [{
          label: (0,external_wp_i18n_namespaceObject.__)('Hide To Do', 'customify'),
          icon: 'eye-off',
          onClick: onHideTodo
        }]
      }),
      children: THINGS_TO_DO.map(item => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ChecklistRow, {
        checked: !!checked[item.id],
        onToggleCheck: () => toggleCheck(item.id),
        title: item.title,
        description: item.description,
        actions: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
            variant: "unstyled",
            className: "pm-info-btn",
            icon: info_default,
            iconSize: 22,
            label: (0,external_wp_i18n_namespaceObject.__)('Learn more', 'customify'),
            onClick: () => setOpenGuide(item.guide)
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(Button, {
            variant: "primary",
            href: item.ctaHref,
            children: [item.ctaLabel, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
              name: "arrow-up-right",
              size: 13
            })]
          })]
        })
      }, item.id))
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
      title: (0,external_wp_i18n_namespaceObject.__)('Theme Customizer', 'customify'),
      headerRight: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
        className: "pm-header-link",
        href: "customize.php",
        children: [(0,external_wp_i18n_namespaceObject.__)('Go to Customizer', 'customify'), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Icon, {
          name: "chevron-right",
          size: 12
        })]
      }),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pm-theme-grid",
        children: THEME_CUSTOMIZER_ITEMS.map((item, i) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ThemeGridCard, {
          ...item
        }, i))
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ProModulesCard, {})]
  });
  const sidebar = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LicenseCard, {
      ...LICENSE_DATA
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SitesImportCard, {}), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
      title: (0,external_wp_i18n_namespaceObject.__)('Resources', 'customify'),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ResourceList, {
        items: RESOURCES
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(RecommendPluginsCard, {}), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
      title: (0,external_wp_i18n_namespaceObject.__)('Loving Customify?', 'customify'),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ReviewCard, {
        ...REVIEW
      })
    })]
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(WelcomeLayout, {
      main: main,
      sidebar: sidebar
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(GuidePopover, {
      guideKey: openGuide,
      isOpen: !!openGuide,
      onClose: () => setOpenGuide(null)
    })]
  });
}
;// ./node_modules/@wordpress/icons/build-module/library/brush.mjs
// packages/icons/src/library/brush.tsx


var brush_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M4 20h8v-1.5H4V20zM18.9 3.5c-.6-.6-1.5-.6-2.1 0l-7.2 7.2c-.4-.1-.7 0-1.1.1-.5.2-1.5.7-1.9 2.2-.4 1.7-.8 2.2-1.1 2.7-.1.1-.2.3-.3.4l-.6 1.1H6c2 0 3.4-.4 4.7-1.4.8-.6 1.2-1.4 1.3-2.3 0-.3 0-.5-.1-.7L19 5.7c.5-.6.5-1.6-.1-2.2zM9.7 14.7c-.7.5-1.5.8-2.4 1 .2-.5.5-1.2.8-2.3.2-.6.4-1 .8-1.1.5-.1 1 .1 1.3.3.2.2.3.5.2.8 0 .3-.1.9-.7 1.3z" }) });

//# sourceMappingURL=brush.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/chart-bar.mjs
// packages/icons/src/library/chart-bar.tsx


var chart_bar_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { fillRule: "evenodd", clipRule: "evenodd", d: "M11.25 5h1.5v15h-1.5V5zM6 10h1.5v10H6V10zm12 4h-1.5v6H18v-6z" }) });

//# sourceMappingURL=chart-bar.mjs.map

;// ./src/backend/dashboard/app/data/settings-schema.js
/**
 * Settings field schema — single source of truth for Settings.js to render
 * each card. Matches the option shape sanitized in PHP
 * (Customify::theme_dashboard_sanitize).
 *
 * Field types: toggle | select | action | static. Each field declares its
 * group + key so the controlled state can read/write the nested option.
 *
 * The Performance card is conditional on the Customify Pro plugin being
 * active — the underlying option (customify_pro_assets_compress) lives in
 * the Pro plugin and Customify mirrors it on save.
 */




const SETTINGS_NAV = [{
  id: 'icons',
  label: (0,external_wp_i18n_namespaceObject.__)('Icons', 'customify'),
  // `brush` reads as "visual styling" — fits a Font Awesome version
  // picker that decides which icon glyphs ship with the theme.
  icon: brush_default
}, ...(PRO_ACTIVE ? [{
  id: 'performance',
  label: (0,external_wp_i18n_namespaceObject.__)('Performance', 'customify'),
  // `chartBar` is the standard performance/metrics glyph
  // in @wordpress/icons.
  icon: chart_bar_default
}] : [])];
const DEFAULT_SUB_TAB = 'icons';
const performanceCard = {
  title: (0,external_wp_i18n_namespaceObject.__)('Performance', 'customify'),
  group: 'pro',
  fields: [{
    type: 'toggle',
    key: 'assets_compress',
    title: (0,external_wp_i18n_namespaceObject.__)('Combine module asset files into 1, reducing HTTP requests', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Customify Pro modules each ship their own CSS and JS files, so each one costs an HTTP request. Enable this to bundle them into a single file (recommended).', 'customify'),
    disabled: !PRO_ASSETS_WRITABLE,
    disabledHint: PRO_ASSETS_SAVE_PATH ? (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: filesystem path of the save directory */
    (0,external_wp_i18n_namespaceObject.__)("Save path %s isn't writable, so this option can't be enabled. Make the directory writable and reload to retry.", 'customify'), PRO_ASSETS_SAVE_PATH) : (0,external_wp_i18n_namespaceObject.__)("The Pro asset save directory isn't writable, so this option can't be enabled.", 'customify')
  }, {
    type: 'static',
    title: (0,external_wp_i18n_namespaceObject.__)('Save path', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Where the combined asset file is written. Must be writable by PHP for combining to work.', 'customify'),
    static: PRO_ASSETS_SAVE_PATH || (0,external_wp_i18n_namespaceObject.__)('— not detected —', 'customify')
  }]
};
const SETTINGS_CARDS = {
  icons: {
    title: (0,external_wp_i18n_namespaceObject.__)('Font Icons', 'customify'),
    group: 'icons',
    fields: [{
      type: 'select',
      key: 'fa_version',
      title: (0,external_wp_i18n_namespaceObject.__)('Font Awesome version', 'customify'),
      description: (0,external_wp_i18n_namespaceObject.__)('Choose which Font Awesome version is loaded across the theme. Pick v6 for new sites; the v4 + v5 fallback exists only for legacy content.', 'customify'),
      options: [{
        value: 'v4',
        label: (0,external_wp_i18n_namespaceObject.__)('Font Awesome 4', 'customify')
      }, {
        value: 'v6',
        label: (0,external_wp_i18n_namespaceObject.__)('Font Awesome 6', 'customify')
      }, {
        value: 'v456',
        label: (0,external_wp_i18n_namespaceObject.__)('Font Awesome 6 with v4 / v5 fallback', 'customify')
      }]
    }]
  },
  ...(PRO_ACTIVE ? {
    performance: performanceCard
  } : {})
};
;// ./src/backend/dashboard/app/api/settings.js
/**
 * Settings AJAX client. Wraps tasks dispatched in
 * Customify_Theme_Dashboard::ajax_dispatch (single admin-ajax action +
 * `task` routing field):
 *
 *   get_settings   → current saved settings (defaults merged)
 *   save_settings  → persist + return sanitized shape
 *   clear_cache    → flush cached per-page CSS (stub for now)
 */


function fetchSettings() {
  return ajaxCall('get_settings');
}
function saveSettings(data) {
  return ajaxCall('save_settings', {
    payload: data
  });
}
function clearCache() {
  return ajaxCall('clear_cache');
}
;// ./src/backend/dashboard/app/tabs/Settings.js
/**
 * Settings tab — left sub-nav + active card with rendered fields driven by
 * the schema in app/data/settings-schema.js. Each field maps to a UI primitive
 * (ToggleSwitch, Select, Button) wired to local controlled state.
 *
 * Settings round-trip:
 *   mount → fetchSettings() → seed local state
 *   user edits → mark dirty
 *   Save → saveSettings(state) → status: saving → saved
 *   Reset → state = defaults from PHP (re-fetched after explicit POST defaults
 *           OR client-side reset using the schema). Here we re-fetch so the
 *           server is authoritative.
 */







function shallowEqual(a, b) {
  if (a === b) return true;
  if (!a || !b) return false;
  const ak = Object.keys(a);
  const bk = Object.keys(b);
  if (ak.length !== bk.length) return false;
  return ak.every(k => {
    if (typeof a[k] === 'object') {
      return shallowEqual(a[k], b[k]);
    }
    return a[k] === b[k];
  });
}
function renderField(field, value, onChange, actionState, runAction) {
  if (field.type === 'toggle') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ToggleSwitch, {
      checked: !!value,
      onChange: v => onChange(field.key, v),
      ariaLabel: field.title,
      disabled: !!field.disabled
    });
  }
  if (field.type === 'select') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Select, {
      value: value || field.options[0].value,
      onChange: v => onChange(field.key, v),
      options: field.options,
      ariaLabel: field.title
    });
  }
  if (field.type === 'action') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
      variant: "secondary",
      onClick: () => runAction(field.action),
      disabled: actionState[field.action] === 'running',
      children: actionState[field.action] === 'running' ? (0,external_wp_i18n_namespaceObject.__)('Working…', 'customify') : field.ctaLabel
    });
  }
  if (field.type === 'static') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Pill, {
      style: {
        fontSize: 13,
        padding: '6px 12px'
      },
      children: field.static
    });
  }
  return null;
}
function Settings() {
  const [subTab, setSubTab] = (0,external_wp_element_namespaceObject.useState)(DEFAULT_SUB_TAB);
  const [initial, setInitial] = (0,external_wp_element_namespaceObject.useState)(null); // server-side baseline
  const [state, setState] = (0,external_wp_element_namespaceObject.useState)(null); // current edits
  const [status, setStatus] = (0,external_wp_element_namespaceObject.useState)('idle'); // idle | saving | saved | error
  const [actionState, setActionState] = (0,external_wp_element_namespaceObject.useState)({});
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    fetchSettings().then(s => {
      setInitial(s);
      setState(s);
    }).catch(() => setStatus('error'));
  }, []);
  const dirty = state && initial && !shallowEqual(state, initial);
  const updateField = (0,external_wp_element_namespaceObject.useCallback)(group => (key, value) => {
    setState(prev => ({
      ...prev,
      [group]: {
        ...prev[group],
        [key]: value
      }
    }));
    setStatus('idle');
  }, []);
  const onSave = (0,external_wp_element_namespaceObject.useCallback)(() => {
    setStatus('saving');
    saveSettings(state).then(saved => {
      setInitial(saved);
      setState(saved);
      setStatus('saved');
    }).catch(() => setStatus('error'));
  }, [state]);
  const onReset = (0,external_wp_element_namespaceObject.useCallback)(() => {
    // Send empty body — PHP fills with defaults via sanitize_settings.
    setStatus('saving');
    saveSettings({}).then(saved => {
      setInitial(saved);
      setState(saved);
      setStatus('saved');
    }).catch(() => setStatus('error'));
  }, []);
  const runAction = (0,external_wp_element_namespaceObject.useCallback)(action => {
    setActionState(s => ({
      ...s,
      [action]: 'running'
    }));
    const promise = action === 'clear-cache' ? clearCache() : Promise.resolve(); // rollback / migration: stub
    promise.then(() => setActionState(s => ({
      ...s,
      [action]: 'done'
    }))).catch(() => setActionState(s => ({
      ...s,
      [action]: 'error'
    })));
  }, []);
  if (!state) {
    // Loading — keep layout stable so the page doesn't jump.
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SettingsLayout, {
      nav: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SubNav, {
        items: SETTINGS_NAV,
        active: subTab,
        onChange: setSubTab,
        ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Settings sections', 'customify')
      }),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
        title: SETTINGS_CARDS[subTab].title,
        bodyPadding: true,
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
          children: (0,external_wp_i18n_namespaceObject.__)('Loading…', 'customify')
        })
      })
    });
  }
  const card = SETTINGS_CARDS[subTab];
  const groupValues = state[card.group] || {};
  const onChange = updateField(card.group);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SettingsLayout, {
    nav: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SubNav, {
      items: SETTINGS_NAV,
      active: subTab,
      onChange: setSubTab,
      ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Settings sections', 'customify')
    }),
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(Card, {
      title: card.title,
      children: [card.fields.map((field, i) => {
        const description = field.disabled && field.disabledHint ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
          children: [field.description, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("br", {}), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("em", {
            className: "pm-setting-row__warning",
            children: field.disabledHint
          })]
        }) : field.description;
        return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SettingRow, {
          title: field.title,
          description: description,
          control: renderField(field, groupValues[field.key], onChange, actionState, runAction)
        }, field.key || field.action || i);
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SaveBar, {
        dirty: dirty,
        status: status,
        onSave: onSave,
        onReset: onReset
      })]
    })
  });
}
;// ./src/backend/dashboard/app/data/compare-matrix.js
/**
 * Free vs Pro comparison matrix. Sections rendered top-down, each with its
 * own rows. Cell value rules (CompareTable.Row):
 *   true     → green check
 *   false    → gray dash
 *   string   → text value
 *   {value, muted: true} → muted text value
 *
 * Source of truth for the Pro module set:
 *   https://pressmaximum.com/docs/customify/customify-pro-modules/
 * Source of truth for pricing & money-back window:
 *   https://pressmaximum.com/customify/pro-upgrade/
 */


const COMPARE_SECTIONS = [{
  title: (0,external_wp_i18n_namespaceObject.__)('Header & Footer Builder', 'customify'),
  rows: [{
    name: (0,external_wp_i18n_namespaceObject.__)('Header drag-and-drop builder', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Logo, menu, search, social, cart, button, and more', 'customify'),
    cells: [true, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Footer drag-and-drop builder', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Up to 6 column footer rows with widgets', 'customify'),
    cells: [true, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Header and Footer Builder Booster', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Extra items + advanced styling for both builders', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Header Sticky', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Animated sticky header on scroll', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Header Transparent', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Overlay header on hero sections', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Mega Menu', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Multi-column drop-down with widget areas', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Multiple Headers', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Different header per page, post, archive, or shop', 'customify'),
    cells: [false, true]
  }]
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Layout & Styling', 'customify'),
  rows: [{
    name: (0,external_wp_i18n_namespaceObject.__)('Sidebar layouts', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Content / Content-Sidebar / Sidebar-Content / 3-column', 'customify'),
    cells: [true, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Container width control', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Customizer-driven container & content widths', 'customify'),
    cells: [true, true]
  },
  // {
  // 	name: __( 'Block patterns library', 'customify' ),
  // 	detail: __(
  // 		'Pre-designed sections to drop into pages',
  // 		'customify'
  // 	),
  // 	cells: [ true, true ],
  // },
  {
    name: (0,external_wp_i18n_namespaceObject.__)('Advanced Styling', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Page header title, cover, and titlebar typography', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Blog Pro layouts', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Grid, list, and masonry blog listings', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Portfolio post type', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Built-in portfolio listing & single layout', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Scroll To Top', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Animated scroll-to-top button', 'customify'),
    cells: [false, true]
  }]
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Typography & Customization', 'customify'),
  rows: [{
    name: (0,external_wp_i18n_namespaceObject.__)('Google Fonts', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Full Google Fonts library available in Customizer', 'customify'),
    cells: [true, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Custom Fonts upload', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Upload self-hosted .woff / .woff2 fonts', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Typekit Fonts', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Connect a Typekit / Adobe Fonts project', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Customify Hooks', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Add custom hook scripts from the dashboard', 'customify'),
    cells: [false, true]
  },
  // {
  // 	name: __( 'Cookie Notice', 'customify' ),
  // 	detail: __(
  // 		'GDPR cookie consent banner',
  // 		'customify'
  // 	),
  // 	cells: [ false, true ],
  // },
  {
    name: (0,external_wp_i18n_namespaceObject.__)('Infinity Scroll', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Auto-loads next posts and products on scroll', 'customify'),
    cells: [false, true]
  }]
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('WooCommerce', 'customify'),
  rows: [{
    name: (0,external_wp_i18n_namespaceObject.__)('WooCommerce compatible', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Shop, cart, checkout, and account templates', 'customify'),
    cells: [true, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('WooCommerce Booster', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Extra style and layout controls for the shop', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('WC Single Product Layouts', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Multiple ready-to-use single-product layouts', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('WC Off Canvas Filter', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Off-canvas product filter for shop archives', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('WC Gallery Slider', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Slider gallery for product images', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('WC Quick View', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Product quick-view modal in product listings', 'customify'),
    cells: [false, true]
  }]
}, {
  title: (0,external_wp_i18n_namespaceObject.__)('Support & Updates', 'customify'),
  rows: [{
    name: (0,external_wp_i18n_namespaceObject.__)('Community support', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('WordPress.org forums & documentation', 'customify'),
    cells: [true, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Updates & email support', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('1 year of updates and direct dev-team support per license', 'customify'),
    cells: [false, true]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Number of sites', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Where you can activate the license', 'customify'),
    cells: [{
      value: (0,external_wp_i18n_namespaceObject.__)('Unlimited', 'customify'),
      muted: true
    }, (0,external_wp_i18n_namespaceObject.__)('1 / 3 / Unlimited', 'customify')]
  }, {
    name: (0,external_wp_i18n_namespaceObject.__)('Renewal discount', 'customify'),
    detail: (0,external_wp_i18n_namespaceObject.__)('Applied automatically on yearly renewal', 'customify'),
    cells: [false, (0,external_wp_i18n_namespaceObject.__)('20% off', 'customify')]
  }]
}];
const COMPARE_CTA = {
  title: (0,external_wp_i18n_namespaceObject.__)('Ready to unlock everything?', 'customify'),
  description: (0,external_wp_i18n_namespaceObject.__)('Personal $59/yr (1 site) · Business $89/yr (3 sites) · Agency $129/yr (unlimited). 15-day money-back guarantee.', 'customify'),
  ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Upgrade to Pro →', 'customify'),
  ctaHref: 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=compare&utm_campaign=upgrade_cta'
};
;// ./src/backend/dashboard/app/tabs/FreeVsPro.js
/**
 * Free vs Pro tab — single Card containing the CompareTable with sections
 * + rows + bottom CTA. Data from app/data/compare-matrix.js.
 */






function FreeVsPro() {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
    title: (0,external_wp_i18n_namespaceObject.__)('Compare Free vs Pro', 'customify'),
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(ui_CompareTable, {
      children: [COMPARE_SECTIONS.map((section, si) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_element_namespaceObject.Fragment, {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ui_CompareTable.Section, {
          title: section.title,
          colLabels: [(0,external_wp_i18n_namespaceObject.__)('Free', 'customify'), (0,external_wp_i18n_namespaceObject.__)('Pro', 'customify')]
        }), section.rows.map((row, ri) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ui_CompareTable.Row, {
          name: row.name,
          detail: row.detail,
          cells: row.cells
        }, ri))]
      }, si)), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ui_CompareTable.CTA, {
        title: COMPARE_CTA.title,
        description: COMPARE_CTA.description,
        action: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
          variant: "primary",
          href: COMPARE_CTA.ctaHref,
          className: 'pm-button--lg',
          children: COMPARE_CTA.ctaLabel
        })
      })]
    })
  });
}
;// ./src/backend/dashboard/app/api/changelog.js
/**
 * Changelog AJAX client. Server returns the raw changelog.txt content
 * (read via WP_Filesystem); parsing into release records lives on the
 * client — see app/tabs/Changelog.js parseChangelog().
 */


function fetchChangelog() {
  return ajaxCall('get_changelog');
}
;// ./src/backend/dashboard/app/tabs/Changelog.js
/**
 * Changelog tab — lazy-loaded. Fetches the raw changelog.txt content via
 * admin-ajax on first mount (which happens when the user activates this
 * tab — App conditionally renders the active pane), then parses it
 * client-side into release records. A module-level memo caches the parsed
 * data so re-mounts (tab switching) don't refetch or re-parse.
 *
 * Format spec for changelog.txt:
 *   = VERSION - DATE [Current] =     header; "[Current]" optional
 *   * TAG: change text                   one entry per line
 *   * TAG: text with `inline code`       backticks render as <code>
 *
 * Recognized tags (case-insensitive, matched to .pm-change-tag-- variants):
 *   New | Added | Updated | Improved | Fixed | Changed | Breaking
 */







const INITIAL_VISIBLE = 5;

// Module-level memo — survives unmount/remount when switching tabs.
let CACHED_RELEASES = null;
function parseInlineCode(text) {
  if (typeof text !== 'string' || text.indexOf('`') === -1) {
    return text;
  }
  const parts = text.split(/`([^`]+)`/g);
  return parts.map((part, i) => i % 2 === 1 ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("code", {
    children: part
  }, i) : part);
}

/**
 * Tag alias map — normalizes raw changelog tags to a smaller set the
 * ReleaseBlock CSS knows how to colorize. Keys are lowercased raw tags;
 * values are the canonical tag (matches `.pm-change-tag--{value}`).
 */
const TAG_ALIASES = {
  change: 'changed',
  changes: 'changed',
  add: 'added',
  adds: 'added',
  addition: 'added',
  additions: 'added',
  update: 'updated',
  updates: 'updated',
  improve: 'improved',
  improvement: 'improved',
  improvements: 'improved',
  enhanced: 'improved',
  enhancement: 'improved',
  enhancements: 'improved',
  fix: 'fixed',
  fixes: 'fixed',
  bugfix: 'fixed',
  bug: 'fixed',
  remove: 'removed',
  removal: 'removed',
  deprecate: 'removed',
  deprecated: 'removed',
  note: 'notes',
  break: 'breaking'
};
function normalizeTag(raw) {
  const key = String(raw || '').toLowerCase();
  return TAG_ALIASES[key] || key;
}

/**
 * Parse a single header string into { version, date, isCurrent }.
 *
 * Accepts either of these formats (date is optional, [Current] is optional
 * and may sit anywhere):
 *
 *   0.4.14
 *   0.4.14 - May 6, 2026
 *   0.4.14 [Current]
 *   0.4.14 - May 6, 2026 [Current]
 *
 * The version is taken from the first whitespace-separated token so it
 * tolerates extra junk after it.
 */
function parseHeader(raw) {
  let header = raw.trim();
  const isCurrent = /\[Current\]/i.test(header);
  if (isCurrent) {
    header = header.replace(/\[Current\]/i, '').trim();
  }
  let version = '';
  let date = '';
  if (header.includes(' - ')) {
    const [v, ...rest] = header.split(' - ');
    version = v.trim();
    date = rest.join(' - ').trim();
  } else {
    // No explicit date separator — treat first token as version, the
    // remainder (if any) as a free-form date string.
    const match = header.match(/^(\S+)\s*(.*)$/);
    if (match) {
      version = match[1].trim();
      date = (match[2] || '').trim();
    } else {
      version = header;
    }
  }
  return {
    version,
    date,
    isCurrent
  };
}

/**
 * Parse the raw changelog.txt content into release records.
 *
 * Header lines start with single `= ` so a `== Title ==` intro line is
 * skipped. Both `= VERSION =` and `= VERSION - DATE =` are accepted; the
 * `[Current]` flag is optional and falls through to "newest release" if
 * never specified explicitly.
 */
function parseChangelog(raw) {
  if (typeof raw !== 'string' || !raw) {
    return [];
  }
  const content = raw.replace(/\r\n/g, '\n');

  // Split on whole-line single-`=` headers, keeping the captured group.
  // Result: [pre-content, header1, body1, header2, body2, ...]
  const parts = content.split(/^=\s*(.+?)\s*=[ \t]*$/m);
  const releases = [];
  let sawExplicitCurrent = false;
  for (let i = 1; i < parts.length - 1; i += 2) {
    const {
      version,
      date,
      isCurrent
    } = parseHeader(parts[i]);
    if (!version) {
      continue;
    }
    const body = (parts[i + 1] || '').trim();
    const changes = [];
    for (const line of body.split('\n')) {
      const trimmed = line.trim();
      if (!trimmed.startsWith('*')) {
        continue;
      }
      const stripped = trimmed.replace(/^\*\s*/, '');
      const tagged = stripped.match(/^([A-Za-z]+)\s*:\s*(.+)$/);
      if (tagged) {
        changes.push({
          tag: normalizeTag(tagged[1]),
          text: parseInlineCode(tagged[2].trim())
        });
      } else if (stripped) {
        // Untagged bullet — surface it as a plain note instead of
        // swallowing the line entirely.
        changes.push({
          tag: 'notes',
          text: parseInlineCode(stripped)
        });
      }
    }
    const release = {
      version,
      date,
      changes
    };
    if (isCurrent) {
      release.current = true;
      sawExplicitCurrent = true;
    }
    releases.push(release);
  }

  // Auto-mark the first release as current when the file doesn't carry an
  // explicit [Current] flag — matches reader expectation that the topmost
  // version is the one you're running.
  if (!sawExplicitCurrent && releases.length > 0) {
    releases[0].current = true;
  }
  return releases;
}
function Changelog() {
  const [releases, setReleases] = (0,external_wp_element_namespaceObject.useState)(CACHED_RELEASES || []);
  const [status, setStatus] = (0,external_wp_element_namespaceObject.useState)(CACHED_RELEASES ? 'ready' : 'loading');
  const [visible, setVisible] = (0,external_wp_element_namespaceObject.useState)(INITIAL_VISIBLE);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (CACHED_RELEASES) {
      return undefined;
    }
    let alive = true;
    fetchChangelog().then(raw => {
      if (!alive) {
        return;
      }
      CACHED_RELEASES = parseChangelog(raw);
      setReleases(CACHED_RELEASES);
      setStatus('ready');
    }).catch(() => {
      if (alive) {
        setStatus('error');
      }
    });
    return () => {
      alive = false;
    };
  }, []);
  const shown = releases.slice(0, visible);
  const hasMore = visible < releases.length;
  const main = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(Card, {
    title: (0,external_wp_i18n_namespaceObject.__)('Latest Releases', 'customify'),
    children: [status === 'loading' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-changelog-more",
      children: (0,external_wp_i18n_namespaceObject.__)('Loading…', 'customify')
    }), status === 'error' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-changelog-more",
      children: (0,external_wp_i18n_namespaceObject.__)('Failed to load changelog.', 'customify')
    }), status === 'ready' && shown.map(r => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ReleaseBlock, {
      ...r
    }, r.version)), status === 'ready' && hasMore && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-changelog-more",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Button, {
        variant: "secondary",
        onClick: () => setVisible(v => v + INITIAL_VISIBLE),
        children: (0,external_wp_i18n_namespaceObject.__)('Load older releases', 'customify')
      })
    })]
  });
  const sidebar = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LicenseCard, {
      ...LICENSE_DATA
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Card, {
      title: (0,external_wp_i18n_namespaceObject.__)('Resources', 'customify'),
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ResourceList, {
        items: RESOURCES
      })
    })]
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(WelcomeLayout, {
    main: main,
    sidebar: sidebar
  });
}
;// ./src/backend/dashboard/app/App.js
/**
 * Dashboard App — layout shell. Header + active tab pane + footer, with
 * URL hash routing. Each tab component is responsible for its own content;
 * App only owns the chrome and the active-tab state.
 */











const PANES = {
  welcome: Welcome,
  settings: Settings,
  'free-vs-pro': FreeVsPro,
  changelog: Changelog
};
function App() {
  const [tab, navigate] = useHashRoute();
  const Pane = PANES[tab] || Welcome;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(AppHeader, {
      tabs: TABS,
      activeTab: tab,
      onTabChange: navigate
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pm-tab-pane",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Pane, {})
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(AppFooter, {}), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Notices, {})]
  });
}
;// ./src/backend/dashboard/index.js
/**
 * Customify Dashboard — admin page entry.
 *
 * Mounts the React app into the #customify-dashboard node printed by
 * Customify_Theme_Dashboard::render_page(). Loaded only on the Customify
 * admin page (toplevel_page_customify) — see inc/admin/class-theme-dashboard.php.
 */





document.addEventListener('DOMContentLoaded', () => {
  const node = document.getElementById('customify-dashboard');
  if (!node) {
    return;
  }
  (0,external_wp_element_namespaceObject.createRoot)(node).render(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(App, {}));
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
/******/ 			861: 0,
/******/ 			57: 0
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
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, [57], function() { return __webpack_require__(739); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;