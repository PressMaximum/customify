/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
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

;// external ["wp","element"]
var external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","hooks"]
var external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// external "ReactJSXRuntime"
var external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/HashRouter.jsx
/**
 * Minimal hash router for the dashboard SPA.
 *
 * Why not `@wordpress/router`? At time of extraction WP's package is
 * query-param based (`?path=/welcome`); the kit's SPEC §6.2 commits to a
 * hash-based URL scheme (`#welcome`) so deep links stay bookmark-stable
 * across consumer plugins that don't own the page path. Rolling a thin
 * `hashchange` listener keeps the contract simple.
 *
 * Route-table shape (locked, SPEC §5.1 + §6.3):
 *
 *   { '#welcome': { component, type: 'page' | 'list' | 'editor',
 *                   label?, parent?, ...extra } }
 *
 * Hash format: `#route` or `#route/segment/:id`. `useRoute()` returns
 * `{ route, entry, params }` where `route` is the matching template
 * (e.g. `#conditions/:id`) and `params` resolves the id map.
 *
 * Dirty-state coupling lives behind `NavigationGuardContext` — P3's
 * `useDirtyState` registers a guard via the provider; this module knows
 * nothing about the dirty buffer.
 */



const HASH_PREFIX = '#';
const DEFAULT_INITIAL_ROUTE = '#welcome';

/* -------------------------------------------------------------------------
 * Low-level location helpers
 * ------------------------------------------------------------------------- */

function readHash(fallback = DEFAULT_INITIAL_ROUTE) {
  if (typeof window === 'undefined') {
    return fallback;
  }
  const raw = window.location.hash || fallback;
  return raw.startsWith(HASH_PREFIX) ? raw : HASH_PREFIX + raw;
}
function HashRouter_navigate(hash) {
  if (typeof window === 'undefined') {
    return;
  }
  const target = hash.startsWith(HASH_PREFIX) ? hash : HASH_PREFIX + hash;
  if (window.location.hash !== target) {
    window.location.hash = target;
  }
}
function stripHash(value) {
  return value && value.startsWith(HASH_PREFIX) ? value.slice(1) : value;
}

/* -------------------------------------------------------------------------
 * Hooks — subscription + matching
 * ------------------------------------------------------------------------- */

/**
 * Subscribe to `hashchange` and return the current hash.
 *
 * @param {string} [initialRoute='#welcome'] Default returned when no hash is set.
 */
function useHash(initialRoute = DEFAULT_INITIAL_ROUTE) {
  const [hash, setHash] = (0,external_wp_element_namespaceObject.useState)(() => readHash(initialRoute));
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const handler = () => setHash(readHash(initialRoute));
    window.addEventListener('hashchange', handler);
    return () => window.removeEventListener('hashchange', handler);
  }, [initialRoute]);
  return hash;
}

/**
 * Match the current hash against a route table. Returns the matched
 * entry + extracted `:param` values, or `null` when nothing matches.
 *
 * Pattern: `#conditions/:id` matches `#conditions/42` with
 * `params: { id: '42' }`. Static segments take precedence over params.
 *
 * @param {string}                  hash   Current `#...` hash.
 * @param {Record<string, unknown>} routes Hash → route-entry table.
 * @return {{ route: string, entry: unknown, params: Record<string, string> } | null}
 *         Matched entry + params, or `null` when no pattern matches.
 */
function matchRoute(hash, routes) {
  if (!routes || typeof routes !== 'object') {
    return null;
  }
  if (routes[hash]) {
    return {
      route: hash,
      entry: routes[hash],
      params: {}
    };
  }
  const incoming = stripHash(hash).split('/').filter(Boolean);
  for (const pattern of Object.keys(routes)) {
    const patternSegs = stripHash(pattern).split('/').filter(Boolean);
    if (patternSegs.length !== incoming.length) {
      continue;
    }
    const params = {};
    let ok = true;
    for (let i = 0; i < patternSegs.length; i++) {
      const seg = patternSegs[i];
      if (seg.startsWith(':')) {
        params[seg.slice(1)] = decodeURIComponent(incoming[i]);
      } else if (seg !== incoming[i]) {
        ok = false;
        break;
      }
    }
    if (ok) {
      return {
        route: pattern,
        entry: routes[pattern],
        params
      };
    }
  }
  return null;
}

/**
 * High-level hook consumed by `DashboardShell`. Returns the resolved
 * route or falls back to `initialRoute` when the hash is unknown / empty.
 *
 * @param {Object} routes                    Hash → route-entry table.
 * @param {string} [initialRoute='#welcome'] Default route when nothing matches.
 */
function useRoute(routes, initialRoute = DEFAULT_INITIAL_ROUTE) {
  const hash = useHash(initialRoute);
  const fallback = (0,external_wp_element_namespaceObject.useMemo)(() => matchRoute(initialRoute, routes) || {
    route: initialRoute,
    entry: null,
    params: {}
  }, [routes, initialRoute]);
  const matched = (0,external_wp_element_namespaceObject.useMemo)(() => matchRoute(hash, routes), [hash, routes]);
  const result = matched || fallback;
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!matched && hash !== fallback.route) {
      HashRouter_navigate(fallback.route);
    }
  }, [hash, matched, fallback.route]);
  return result;
}

/**
 * Top-level tab id for highlighting the tab strip.
 *
 *   activeTabId( '#conditions/42' ) === 'conditions'
 *
 * @param {string} route Hash route, with or without leading `#`.
 * @return {string} First path segment (the tab id), or `''` when empty.
 */
function activeTabId(route) {
  const stripped = stripHash(route);
  return stripped.split('/')[0] || '';
}

/* -------------------------------------------------------------------------
 * Navigation guard — pluggable predicate that vetoes navigation.
 * P3's `useDirtyState` will register a guard via the provider; for P1
 * the default is "always allow".
 * ------------------------------------------------------------------------- */

const ALWAYS_ALLOW = () => true;
const NavigationGuardContext = (0,external_wp_element_namespaceObject.createContext)(ALWAYS_ALLOW);

/**
 * Wrap the dashboard tree with a navigation guard so dirty buffers,
 * unsaved edits, or any other "can leave this route" predicate can
 * intercept tab / link clicks. The guard receives no arguments and
 * returns `true` to allow nav, `false` to cancel.
 *
 * @param {Object}                    props
 * @param {() => boolean}             props.guard    Predicate, returns `true` to allow.
 * @param {import('react').ReactNode} props.children
 */
function NavigationGuardProvider({
  guard,
  children
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(NavigationGuardContext.Provider, {
    value: typeof guard === 'function' ? guard : ALWAYS_ALLOW,
    children: children
  });
}

/**
 * Click handler factory for tab strip / Link-style components.
 * `onClick={ useNavigate()( '#welcome' ) }` is the call shape — curried
 * to match the call sites extracted from Blocksify Free's App.js and
 * SubNav. Calls `preventDefault` so the browser doesn't scroll to a
 * named anchor; consults the navigation guard before navigating.
 */
function useNavigate() {
  const guard = (0,external_wp_element_namespaceObject.useContext)(NavigationGuardContext);
  return (0,external_wp_element_namespaceObject.useCallback)(hash => event => {
    if (event) {
      event.preventDefault();
    }
    if (!guard()) {
      return;
    }
    HashRouter_navigate(hash);
  }, [guard]);
}

;// ./node_modules/@pressmaximum/dashboard-kit/src/core/useFocusOnRouteChange.js
/**
 * useFocusOnRouteChange — SPA focus management.
 *
 * When the active route changes, move focus to the new view's main
 * landmark so screen-reader users get a clear signal that content
 * swapped. Without this, focus stays on the clicked tab anchor and AT
 * users don't know the page advanced.
 *
 * The hook returns a ref to attach to the focus target — typically
 * `<main tabIndex={ -1 }>`. The initial mount doesn't focus — we don't
 * want to steal the user's normal browser focus on first paint, only
 * on subsequent route transitions.
 *
 * `preventScroll: true` is critical: without it the browser scrolls the
 * focused `<main>` into view on every route change, which yanks long
 * content to the top of the viewport. AT users still get the landmark
 * announcement because the focus moved.
 */


function useFocusOnRouteChange(route) {
  const ref = (0,external_wp_element_namespaceObject.useRef)(null);
  const initial = (0,external_wp_element_namespaceObject.useRef)(true);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (initial.current) {
      initial.current = false;
      return;
    }
    if (ref.current && typeof ref.current.focus === 'function') {
      ref.current.focus({
        preventScroll: true
      });
    }
  }, [route]);
  return ref;
}
/* harmony default export */ var core_useFocusOnRouteChange = ((/* unused pure expression or super */ null && (useFocusOnRouteChange)));
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/TabStrip.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/core/TabStrip.jsx
/**
 * TabStrip — Tier-1 layout primitive (SPEC §5.13). Zero translatable
 * strings: every label and the `aria-label` arrive via props.
 *
 * Rendered inside `DashboardShell`'s header, but exported standalone so
 * Pro plugins / future consumers can repurpose the visual. DOM uses the
 * SPEC §16.2 locked class names (`pmdk-dashboard__tabs`,
 * `pmdk-dashboard__tab`) — these classes are the kit's public CSS
 * surface and consumers target them for hover / focus restyles.
 *
 * Slot shape:
 *
 *   <TabStrip
 *     items={ [ { id, label, hash } ] }
 *     activeId={ 'welcome' }
 *     ariaLabel={ 'Dashboard sections' }   // already translated
 *     onSelect={ ({ id, hash, event }) => void }  // optional override
 *   />
 *
 * Default click behavior calls `useNavigate()`, which honors any active
 * `NavigationGuardProvider` (P3's dirty-state hook wraps via this).
 * Override `onSelect` to take full control (e.g. custom logging /
 * preventDefault skip).
 */




function TabStrip({
  items,
  activeId,
  ariaLabel,
  onSelect,
  className
}) {
  const onNavigate = useNavigate();
  if (!Array.isArray(items) || items.length === 0) {
    return null;
  }
  const classes = 'pmdk-dashboard__tabs' + (className ? ' ' + className : '');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("nav", {
    className: classes,
    "aria-label": ariaLabel,
    children: items.map(item => {
      const isActive = item.id === activeId;
      const tabClass = 'pmdk-dashboard__tab' + (isActive ? ' is-active' : '');
      const handleClick = event => {
        if (typeof onSelect === 'function') {
          onSelect({
            id: item.id,
            hash: item.hash,
            event
          });
          return;
        }
        onNavigate(item.hash)(event);
      };
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
        href: item.hash,
        className: tabClass,
        "aria-current": isActive ? 'page' : undefined,
        onClick: handleClick,
        children: item.label
      }, item.id);
    })
  });
}
;// external ["wp","components"]
var external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","primitives"]
var external_wp_primitives_namespaceObject = window["wp"]["primitives"];
;// ./node_modules/@wordpress/icons/build-module/library/help.mjs
// packages/icons/src/library/help.tsx


var help_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M12 4a8 8 0 1 1 .001 16.001A8 8 0 0 1 12 4Zm0 1.5a6.5 6.5 0 1 0-.001 13.001A6.5 6.5 0 0 0 12 5.5Zm.75 11h-1.5V15h1.5v1.5Zm-.445-9.234a3 3 0 0 1 .445 5.89V14h-1.5v-1.25c0-.57.452-.958.917-1.01A1.5 1.5 0 0 0 12 8.75a1.5 1.5 0 0 0-1.5 1.5H9a3 3 0 0 1 3.305-2.984Z" }) });

//# sourceMappingURL=help.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/page.mjs
// packages/icons/src/library/page.tsx


var page_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: [
  /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M15.5 7.5h-7V9h7V7.5Zm-7 3.5h7v1.5h-7V11Zm7 3.5h-7V16h7v-1.5Z" }),
  /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M17 4H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2ZM7 5.5h10a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5H7a.5.5 0 0 1-.5-.5V6a.5.5 0 0 1 .5-.5Z" })
] });

//# sourceMappingURL=page.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/chevron-right.mjs
// packages/icons/src/library/chevron-right.tsx


var chevron_right_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z" }) });

//# sourceMappingURL=chevron-right.mjs.map

;// ./node_modules/@wordpress/icons/build-module/library/external.mjs
// packages/icons/src/library/external.tsx


var external_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M19.5 4.5h-7V6h4.44l-5.97 5.97 1.06 1.06L18 7.06v4.44h1.5v-7Zm-13 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3H17v3a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h3V5.5h-3Z" }) });

//# sourceMappingURL=external.mjs.map

;// ./node_modules/@pressmaximum/dashboard-kit/src/core/createI18nBag.js
/**
 * createI18nBag — merge a component's English defaults with consumer
 * overrides. Tier-2 components call this once in their render bodies so
 * the labels prop becomes optional: consumers translate the strings they
 * care about and inherit kit defaults for the rest.
 *
 * Companion to the per-component string templates in `templates/strings/`
 * (kit-generated; see SPEC §5.13 + §6.2). The template files give the
 * consumer something concrete to copy into their own `_kit-strings.js`;
 * this helper is the runtime side.
 *
 * @example
 *   const DEFAULTS = { loading: 'Loading…', noResults: 'No items.' };
 *   function EntityListPage( { labels } ) {
 *       const L = createI18nBag( DEFAULTS, labels );
 *       return <p>{ L.loading }</p>;
 *   }
 *
 * @template {Record<string, string>} T
 * @param {T}          defaults    Kit's English fallback strings.
 * @param {Partial<T>} [overrides] Consumer-supplied translated strings.
 * @return {T} Merged labels.
 */
function createI18nBag(defaults, overrides) {
  if (!overrides || typeof overrides !== 'object') {
    return {
      ...defaults
    };
  }
  return {
    ...defaults,
    ...overrides
  };
}
/* harmony default export */ var core_createI18nBag = ((/* unused pure expression or super */ null && (createI18nBag)));
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/HelpPanel.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/core/HelpPanel.jsx
/**
 * HelpPanel — Tier-2 page component (SPEC §5.13). Compact help popover
 * anchored to a `?` button. Consumers pass `items` (resource links) and
 * optionally `labels` (English fallbacks shipped); the panel handles
 * hash-vs-external link detection + SPA navigation for hash hrefs.
 *
 * Built on `<Dropdown>` from `@wordpress/components` (NOT `<Modal>`):
 * a small anchored popover is right-sized for 4-6 resource links;
 * Dropdown handles open / close + focus management + click-outside +
 * Esc dismissal for free.
 *
 * SPEC §16.2 wraps the popover content in `.pmdk-help-panel`. The
 * `<Dropdown>` toggle is not part of the locked class surface — the
 * trigger lives inside `.pmdk-dashboard__help-trigger` for theme-level
 * targeting consistent with `.pmdk-dashboard__brand` / `__tabs`.
 *
 * Items:
 *
 *   {
 *     id: string,            // unique key for React
 *     label: string,         // already-translated visible text
 *     href: string,          // '#hash' OR 'https://…'
 *     external?: boolean,    // override auto-detect (default: !href.startsWith('#'))
 *   }
 *
 * Hash items navigate via the kit router (honors `NavigationGuardProvider`).
 * External items open in a new tab with `rel="noopener noreferrer"`.
 */







const DEFAULT_LABELS = {
  triggerLabel: 'Open help panel',
  heading: 'Help'
};
function isHashHref(href) {
  return typeof href === 'string' && href.startsWith('#');
}
function HelpPanel({
  items,
  labels,
  icon = help_default,
  itemIcon = page_default
}) {
  const onNavigate = useNavigate();
  if (!Array.isArray(items) || items.length === 0) {
    return null;
  }
  const L = createI18nBag(DEFAULT_LABELS, labels);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Dropdown, {
    className: "pmdk-dashboard__help-trigger",
    contentClassName: "pmdk-help-panel",
    popoverProps: {
      placement: 'bottom-end',
      offset: 8
    },
    renderToggle: ({
      isOpen,
      onToggle
    }) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
      className: "pmdk-dashboard__help-trigger-button",
      icon: icon,
      label: L.triggerLabel,
      onClick: onToggle,
      "aria-expanded": isOpen
    }),
    renderContent: ({
      onClose
    }) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pmdk-help-panel__panel",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
        className: "pmdk-help-panel__heading",
        children: L.heading
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
        className: "pmdk-help-panel__list",
        children: items.map(item => {
          const isHash = isHashHref(item.href);
          const isExternal = typeof item.external === 'boolean' ? item.external : !isHash;
          return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("li", {
            className: "pmdk-help-panel__item",
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
              className: "pmdk-help-panel__link",
              href: item.href,
              target: isExternal ? '_blank' : undefined,
              rel: isExternal ? 'noopener noreferrer' : undefined,
              onClick: event => {
                if (isHash) {
                  onNavigate(item.href)(event);
                }
                onClose();
              },
              children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
                className: "pmdk-help-panel__icon",
                icon: item.icon || itemIcon,
                size: 18
              }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
                className: "pmdk-help-panel__label",
                children: item.label
              }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
                className: "pmdk-help-panel__chevron",
                icon: isHash ? chevron_right_default : external_default,
                size: 16
              })]
            })
          }, item.id);
        })
      })]
    })
  });
}
;// external ["wp","data"]
var external_wp_data_namespaceObject = window["wp"]["data"];
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/SnackbarSlot.jsx
/**
 * SnackbarSlot — bottom-centered transient notices slot bound to WP's
 * `core/notices` data store. Renders snackbar-typed notices in a fixed
 * overlay so they survive route changes without being inside the
 * active page tree.
 *
 * Why the store name as a string instead of `import from
 * '@wordpress/notices'`? Avoids adding a peer dep for what is really
 * just a string handle into a globally-registered data store. WP admin
 * registers `core/notices` on page load; the kit just consumes the
 * descriptor.
 *
 * Consumers create snackbars via the standard WP idiom:
 *
 *   import { dispatch } from '@wordpress/data';
 *   dispatch( 'core/notices' ).createSuccessNotice( __( 'Saved.', 'my-plugin' ), { type: 'snackbar' } );
 *
 * The kit just renders whatever has `type === 'snackbar'` in the store.
 */




const NOTICES_STORE = 'core/notices';
function SnackbarSlot({
  className
}) {
  const notices = (0,external_wp_data_namespaceObject.useSelect)(select => select(NOTICES_STORE)?.getNotices() ?? [], []);
  // `useDispatch` against an unregistered store returns `null` in older
  // WP data versions + in vitest/jsdom (no WP runtime). Default to a
  // no-op so the kit doesn't crash when the consumer happens not to
  // have @wordpress/notices registered (typical in unit tests).
  const dispatchers = (0,external_wp_data_namespaceObject.useDispatch)(NOTICES_STORE) || {};
  const removeNotice = dispatchers.removeNotice || (() => undefined);
  const snackbarNotices = notices.filter(n => n.type === 'snackbar');
  const classes = 'pmdk-dashboard__snackbar' + (className ? ' ' + className : '');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SnackbarList, {
    className: classes,
    notices: snackbarNotices,
    onRemove: removeNotice
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/DashboardShell.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/core/DashboardShell.jsx
/**
 * DashboardShell — Tier-1 layout primitive (SPEC §5.13). Composes the
 * header (brand + tabs + version + help slot) + a focus-managed main
 * region + a fixed-position snackbar slot.
 *
 * Resolves the active route internally via `useRoute( routes,
 * initialRoute )` so consumers don't have to thread the matched entry
 * through props. Renders `entry.component` with `{ route, params,
 * entry }` so consumer components can access arbitrary fields the
 * consumer attached to the route entry (e.g. Blocksify's `proFeature`
 * marker — consumer-specific; kit forwards without inspecting).
 *
 * Every visible string lives behind a prop. The shell renders zero
 * translatable text on its own.
 *
 * SPEC §16.2 locked classes used here:
 *   .pmdk-dashboard
 *   .pmdk-dashboard__header
 *   .pmdk-dashboard__brand
 *   .pmdk-dashboard__main
 * Plus non-locked styling hooks:
 *   .pmdk-dashboard__brand-icon, __brand-text, __brand-link,
 *   __header-right, .pmdk-dashboard__version
 */








function renderMain({
  ActiveComponent,
  NotFound,
  route,
  params,
  entry,
  fallback
}) {
  if (ActiveComponent) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ActiveComponent, {
      route: route,
      params: params,
      entry: entry
    });
  }
  if (NotFound) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(NotFound, {
      route: route,
      params: params
    });
  }
  return fallback || null;
}
function DashboardShell({
  // Brand cluster
  brand,
  // Tabs
  tabs,
  tabsAriaLabel,
  // Routes
  routes,
  initialRoute = '#welcome',
  // Layout — `'narrow'` (default) caps the reading column at 1100px;
  // `'wide'` removes the cap so DataViews-heavy pages can fill the
  // viewport. SPEC §5.1 + §11 hack #3. See DashboardShell.css.
  containerWidth = 'narrow',
  // Optional version anchor
  versionLabel,
  versionHref,
  versionAriaLabel,
  // Optional help cluster
  helpItems,
  helpLabels,
  helpIcon,
  helpItemIcon,
  // Fallbacks when route doesn't resolve a component
  notFoundComponent: NotFound,
  fallback,
  // Optional snackbar override
  snackbar
}) {
  const {
    route,
    entry,
    params
  } = useRoute(routes, initialRoute);
  const onNavigate = useNavigate();
  const mainRef = useFocusOnRouteChange(route);
  const ActiveComponent = entry?.component;
  const activeId = activeTabId(route);
  const brandName = brand?.name;
  const brandIcon = brand?.icon;
  const brandHref = brand?.href;
  const brandAriaLabel = brand?.ariaLabel;
  const safeContainerWidth = containerWidth === 'wide' ? 'wide' : 'narrow';

  // Inner content of the `<h1>` brand cluster. Reused twice so the
  // linked + static variants don't duplicate the icon/text markup.
  const brandContent = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [brandIcon && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-dashboard__brand-icon"
      /* eslint-disable-next-line react/no-danger -- SVG is consumer-controlled boot data, not user input. */,
      dangerouslySetInnerHTML: {
        __html: brandIcon
      }
    }), brandName && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-dashboard__brand-text",
      children: brandName
    })]
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pmdk-dashboard",
    "data-container-width": safeContainerWidth,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("header", {
      className: "pmdk-dashboard__header",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h1", {
        className: "pmdk-dashboard__brand",
        children: brandHref ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
          className: "pmdk-dashboard__brand-link",
          href: brandHref,
          "aria-label": brandAriaLabel,
          onClick: onNavigate(brandHref),
          children: brandContent
        }) : brandContent
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(TabStrip, {
        items: tabs,
        activeId: activeId,
        ariaLabel: tabsAriaLabel
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "pmdk-dashboard__header-right",
        children: [versionLabel && (versionHref ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
          className: "pmdk-dashboard__version",
          href: versionHref,
          "aria-label": versionAriaLabel,
          onClick: onNavigate(versionHref),
          children: versionLabel
        }) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "pmdk-dashboard__version",
          "aria-label": versionAriaLabel,
          children: versionLabel
        })), Array.isArray(helpItems) && helpItems.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(HelpPanel, {
          items: helpItems,
          labels: helpLabels,
          icon: helpIcon,
          itemIcon: helpItemIcon
        })]
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("main", {
      ref: mainRef,
      className: "pmdk-dashboard__main",
      role: "main",
      tabIndex: -1,
      children: renderMain({
        ActiveComponent,
        NotFound,
        route,
        params,
        entry,
        fallback
      })
    }), snackbar !== undefined ? snackbar : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SnackbarSlot, {})]
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/BootDataLoader.jsx
/**
 * BootDataLoader — read the consumer's PHP-localized boot payload off
 * `window[ bootGlobal ]` and ship it down the component tree via React
 * context. Single accessor pattern keeps the rest of the kit ignorant of
 * which `window` key any given consumer chose.
 *
 * Shape contract is consumer-defined (the kit imposes no required keys
 * — that's the consumer's PHP). Components that need a value pull it
 * via `useBoot()`; missing keys are the consumer's bug to surface.
 */



const BootContext = (0,external_wp_element_namespaceObject.createContext)({});

/**
 * Read the boot payload off `window`. Safe under SSR / module-eval —
 * returns `{}` if `window` is undefined or the key isn't set.
 *
 * @param {string} bootGlobal Window key name, e.g. `'customifyDashboard'`.
 * @return {Record<string, unknown>} Boot payload (empty object on miss).
 */
function readBoot(bootGlobal) {
  if (typeof window === 'undefined' || !bootGlobal) {
    return {};
  }
  const raw = window[bootGlobal];
  return raw && typeof raw === 'object' ? raw : {};
}

/**
 * Provider — wraps the dashboard tree with the resolved boot snapshot.
 * `mountDashboard` does this once at the top; everything below reads via
 * `useBoot()`.
 *
 * @param {Object}                    props
 * @param {Record<string, unknown>}   props.boot     Resolved boot payload.
 * @param {import('react').ReactNode} props.children
 */
function BootProvider({
  boot,
  children
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(BootContext.Provider, {
    value: boot || {},
    children: children
  });
}

/** Consume the boot snapshot from anywhere inside the dashboard tree. */
function useBoot() {
  return (0,external_wp_element_namespaceObject.useContext)(BootContext);
}

;// ./node_modules/@pressmaximum/dashboard-kit/src/core/createFilterNamespace.js
/**
 * Build the per-consumer filter channel-name map.
 *
 * Each consumer (Customify Theme, Blocksify Free, future plugins) calls
 * this once with its own short prefix to mint a `{ tabs, routes,
 * settingsPanels, ... }` map of fully-qualified channel names. The kit
 * itself reaches for the channels via `applyFilters(ns.tabs, ...)` etc.
 * — the kit never hardcodes `'blocksify.dashboard.tabs'`-style strings.
 *
 * Returned shape locked at SPEC §5.2. Adding a key here is a minor
 * version bump pre-1.0; removing one is a major bump.
 *
 * @example
 *   const FILTERS = createFilterNamespace( 'customify' );
 *   FILTERS.tabs       // → 'customify.dashboard.tabs'
 *   FILTERS.routes     // → 'customify.dashboard.routes'
 *
 * @param {string} prefix Consumer namespace prefix, e.g. `customify`.
 *                        Must be non-empty. No trailing dot — kit adds
 *                        `.dashboard` itself.
 * @return {Record<string,string>} Channel-name map.
 */
function createFilterNamespace(prefix) {
  if (!prefix || typeof prefix !== 'string') {
    throw new TypeError('createFilterNamespace: `prefix` is required and must be a non-empty string.');
  }
  const base = `${prefix}.dashboard`;
  return {
    boot: `${base}.boot`,
    tabs: `${base}.tabs`,
    tabsLocked: `${base}.tabs.locked`,
    routes: `${base}.routes`,
    welcomeSections: `${base}.welcome.sections`,
    welcomeChecklist: `${base}.welcome.checklist`,
    settingsPanels: `${base}.settings.panels`,
    settingsFieldTypes: `${base}.settings.field-types`,
    changelogSources: `${base}.changelog.sources`,
    versionLabel: `${base}.version-label`
  };
}
/* harmony default export */ var core_createFilterNamespace = ((/* unused pure expression or super */ null && (createFilterNamespace)));
;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/useDirtyState.js
/* unused harmony import specifier */ var useState;
/* unused harmony import specifier */ var useRef;
/* unused harmony import specifier */ var useEffect;
/* unused harmony import specifier */ var useCallback;
/**
 * useDirtyState — shared dirty-tracking hook for editor flows. SPEC §5.4.
 *
 * Pattern: consumer calls `setDirty(true)` on edit (typically wired off a
 * settings store's `isDirty` selector via useEffect), `setDirty(false)`
 * on save. The hook does three jobs:
 *
 *   1. Registers a `beforeunload` listener while dirty so the browser
 *      surfaces its native "leave site?" confirm on accidental close /
 *      reload. (Modern browsers ignore the message text but still
 *      prompt when `event.returnValue` is set.)
 *
 *   2. Keeps a module-level registry so multiple consumers can declare
 *      dirty state under different keys (e.g. `'settings'`,
 *      `'pro/conditions/42'`) — exported `confirmDiscardAny()` walks
 *      the registry once for the kit's `<NavigationGuardProvider>` to
 *      gate cross-tab navigation.
 *
 *   3. Exposes `confirmDiscard()` for the consumer's own intra-tab
 *      checks (e.g. a back button inside an editor that bypasses the
 *      router guard).
 *
 * `options.onDiscard` runs when the user confirms abandoning the dirty
 * buffer — consumers wire their store's `clearDirty` action here so the
 * next mount reads a clean state. Held in a ref so callers don't have
 * to memoize.
 *
 * `options.discardMessage` is the consumer-translated prompt text. The
 * kit ships an English fallback for the case the consumer forgets to
 * wire it. Last-registered message wins when multiple keys are dirty —
 * fine in practice because the copy is generic across keys.
 *
 * @example
 *   const { setDirty } = useDirtyState('settings', {
 *       onDiscard: clearDirty,
 *       discardMessage: __('You have unsaved changes. Discard them?', 'customify'),
 *   });
 *   useEffect(() => setDirty(isDirty), [isDirty, setDirty]);
 */


const REGISTRY = new Map();
const DISCARD_CALLBACKS = new Map();
const DISCARD_MESSAGES = new Map();

// Tier-1 i18n discipline: kit imports no `__()`. English default acts
// as a safety net so consumers that forget to wire `discardMessage`
// still get a sensible prompt instead of `undefined`.
const DEFAULT_DISCARD_MESSAGE = 'You have unsaved changes. Discard them?';
function useDirtyState(key, options = {}) {
  const [dirty, setDirtyState] = useState(() => Boolean(REGISTRY.get(key)));
  const keyRef = useRef(key);
  keyRef.current = key;

  // Latest-callback ref so `confirmDiscardAny()` (called from outside
  // React's render cycle by the navigation guard) always invokes the
  // current closure without forcing consumers to memoize.
  const onDiscardRef = useRef(options.onDiscard);
  onDiscardRef.current = options.onDiscard;
  if (options.discardMessage) {
    DISCARD_MESSAGES.set(key, options.discardMessage);
  }
  useEffect(() => {
    DISCARD_CALLBACKS.set(keyRef.current, () => {
      const cb = onDiscardRef.current;
      if (typeof cb === 'function') {
        try {
          cb();
        } catch (_) {
          // Discard callbacks are best-effort; a thrown store
          // action shouldn't abort the navigation.
        }
      }
    });
    const cleanupKey = keyRef.current;
    return () => {
      DISCARD_CALLBACKS.delete(cleanupKey);
      DISCARD_MESSAGES.delete(cleanupKey);
    };
  }, []);
  const setDirty = useCallback(next => {
    const flag = Boolean(next);
    REGISTRY.set(keyRef.current, flag);
    setDirtyState(flag);
  }, []);
  useEffect(() => {
    if (!dirty) {
      return undefined;
    }
    function onBeforeUnload(event) {
      event.preventDefault();
      // Modern browsers ignore the returnValue text but still
      // surface their native confirm dialog when this is set.
      event.returnValue = '';
      return '';
    }
    window.addEventListener('beforeunload', onBeforeUnload);
    return () => window.removeEventListener('beforeunload', onBeforeUnload);
  }, [dirty]);
  const confirmDiscard = useCallback(() => {
    if (!REGISTRY.get(keyRef.current)) {
      return true;
    }
    const message = DISCARD_MESSAGES.get(keyRef.current) || DEFAULT_DISCARD_MESSAGE;
    // eslint-disable-next-line no-alert -- browser-native confirm is the contract
    const ok = window.confirm(message);
    if (ok) {
      REGISTRY.set(keyRef.current, false);
      setDirtyState(false);
      const cb = DISCARD_CALLBACKS.get(keyRef.current);
      if (cb) {
        cb();
      }
    }
    return ok;
  }, []);
  return {
    isDirty: dirty,
    setDirty,
    confirmDiscard
  };
}

/** True when any registered consumer has flagged itself dirty. */
function isAnyDirty() {
  for (const flag of REGISTRY.values()) {
    if (flag) {
      return true;
    }
  }
  return false;
}

/**
 * Walk the registry; prompt once if any consumer is dirty. Returns
 * `true` when the navigation may proceed (no dirty state OR user
 * confirmed discard). The kit's `<NavigationGuardProvider>` consumes
 * this directly — `mountDashboard` wires it as the default guard so
 * tab-strip clicks + version-anchor clicks honor the dirty buffer
 * without consumer wiring.
 *
 * On accept, invokes each dirty key's `onDiscard` callback so stores
 * that own the actual edit buffer clear themselves.
 */
function confirmDiscardAny() {
  if (!isAnyDirty()) {
    return true;
  }
  // Pick the first dirty key's registered message; falls back to the
  // English default when no consumer registered one. Distinct copy
  // per dirty key is future-friendly but not yet needed.
  let message = DEFAULT_DISCARD_MESSAGE;
  for (const key of REGISTRY.keys()) {
    if (REGISTRY.get(key) && DISCARD_MESSAGES.has(key)) {
      message = DISCARD_MESSAGES.get(key);
      break;
    }
  }
  // eslint-disable-next-line no-alert -- browser-native confirm is the contract
  const ok = window.confirm(message);
  if (ok) {
    for (const key of REGISTRY.keys()) {
      REGISTRY.set(key, false);
      const cb = DISCARD_CALLBACKS.get(key);
      if (cb) {
        cb();
      }
    }
  }
  return ok;
}

/**
 * Test helper — wipe the registry between tests so per-test side-effects
 * don't leak. Not exported from the public surface.
 *
 * @private
 */
function __resetDirtyRegistry() {
  REGISTRY.clear();
  DISCARD_CALLBACKS.clear();
  DISCARD_MESSAGES.clear();
}
/* harmony default export */ var settings_useDirtyState = ((/* unused pure expression or super */ null && (useDirtyState)));
;// ./node_modules/@pressmaximum/dashboard-kit/src/core/mountDashboard.jsx
/**
 * mountDashboard — bootstraps the dashboard SPA inside the consumer's
 * mount node. Called once per page load.
 *
 * Flow:
 *   1. Resolve `rootEl` (selector string OR element).
 *   2. Read the PHP-localized boot payload off `window[ bootGlobal ]`.
 *   3. Mint the consumer's filter channel names + apply the `tabs`,
 *      `routes`, and `version-label` filters so plugin extensions land
 *      before first render.
 *   4. Render `<DashboardShell />` inside a `<BootProvider />`.
 *
 * Returns `{ unmount }` so consumers can tear down the SPA (rarely
 * needed in WP admin; useful for tests).
 *
 * Config shape locked at SPEC §5.1.
 */









/**
 * Normalize a tab entry to the shape `TabStrip` expects. Accepts a
 * string id (treated as `{ id, label: id, hash: '#'+id }`) or a partial
 * object. `hash` is derived from `id` when omitted.
 *
 * @param {string | { id: string, label?: string, hash?: string }} tab Raw tab entry.
 * @return {{ id: string, label: string, hash: string }} Normalized tab.
 */

function toTabShape(tab) {
  if (typeof tab === 'string') {
    return {
      id: tab,
      label: tab,
      hash: '#' + tab
    };
  }
  return {
    ...tab,
    hash: tab.hash || '#' + tab.id
  };
}
function mountDashboard(config) {
  if (!config || typeof config !== 'object') {
    throw new TypeError('mountDashboard: config object is required (SPEC §5.1).');
  }
  const {
    rootEl,
    bootGlobal,
    filterNamespace,
    // `__` is intentionally NOT destructured. SPEC §5.1 documents
    // it as "recommended" pre-1.0 — Tier-2 components own their own
    // label-merging via `createI18nBag`, so no kit code needs the
    // callback yet. Consumers may still pass it in for forward
    // compatibility; it sits in `config` unused.
    brand,
    baseTabs,
    baseRoutes,
    tabsAriaLabel,
    helpItems,
    helpLabels,
    helpIcon,
    helpItemIcon,
    versionLabel,
    versionHref,
    versionAriaLabel,
    initialRoute = '#welcome',
    notFoundComponent,
    fallback,
    // `'narrow'` (default) → 1100px reading column.
    // `'wide'`             → full viewport, DataViews-friendly. SPEC §5.1.
    containerWidth = 'narrow'
  } = config;
  if (!filterNamespace) {
    throw new TypeError('mountDashboard: `filterNamespace` is required (SPEC §5.1).');
  }
  const node = typeof rootEl === 'string' ? document.querySelector(rootEl) : rootEl;
  if (!node) {
    // eslint-disable-next-line no-console
    console.error('[@pressmaximum/dashboard-kit] mountDashboard: rootEl not found:', rootEl);
    return null;
  }
  const boot = readBoot(bootGlobal);
  const FILTERS = createFilterNamespace(filterNamespace);
  const tabs = (0,external_wp_hooks_namespaceObject.applyFilters)(FILTERS.tabs, Array.isArray(baseTabs) ? [...baseTabs] : []).map(toTabShape);
  const routes = (0,external_wp_hooks_namespaceObject.applyFilters)(FILTERS.routes, {
    ...(baseRoutes || {})
  });
  const filteredVersionLabel = versionLabel !== undefined ? (0,external_wp_hooks_namespaceObject.applyFilters)(FILTERS.versionLabel, versionLabel, boot) : undefined;
  const root = (0,external_wp_element_namespaceObject.createRoot)(node);
  root.render(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(BootProvider, {
    boot: boot,
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(NavigationGuardProvider, {
      guard: confirmDiscardAny,
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DashboardShell, {
        brand: brand,
        tabs: tabs,
        tabsAriaLabel: tabsAriaLabel,
        routes: routes,
        initialRoute: initialRoute,
        containerWidth: containerWidth,
        versionLabel: filteredVersionLabel,
        versionHref: versionHref,
        versionAriaLabel: versionAriaLabel,
        helpItems: helpItems,
        helpLabels: helpLabels,
        helpIcon: helpIcon,
        helpItemIcon: helpItemIcon,
        notFoundComponent: notFoundComponent,
        fallback: fallback
      })
    })
  }));
  return {
    unmount: () => root.unmount()
  };
}
/* harmony default export */ var core_mountDashboard = (mountDashboard);
;// external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// ./node_modules/@pressmaximum/dashboard-kit/src/welcome/Hero.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/welcome/Hero.jsx
/**
 * Hero — Welcome page greeting + tagline + primary CTA + optional
 * illustration. SPEC §5.5 + §5.13 Tier-2 page component.
 *
 * Every visible string arrives via props; the kit ships zero
 * translatable copy. SPEC §16.2 locked class: `.pmdk-hero`.
 *
 * Slot shape:
 *
 *   <Hero
 *     greeting={ string }                        // e.g. 'Welcome, Jack'
 *     tagline={ string? }                        // short subhead
 *     primaryCta={ { label: string, href: string }? }
 *     illustration={ ReactNode? }                // brand SVG / image
 *   />
 *
 * Consumer reads the user's display name from the boot payload
 * (`useBoot()`) and formats the greeting before passing it down — keeps
 * the kit free of `sprintf` + text-domain coupling.
 */




function Hero({
  greeting,
  tagline,
  primaryCta,
  illustration
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("section", {
    className: "pmdk-hero",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pmdk-hero__content",
      children: [greeting && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
        className: "pmdk-hero__title",
        children: greeting
      }), tagline && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pmdk-hero__tagline",
        children: tagline
      }), primaryCta && primaryCta.href && primaryCta.label && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
        variant: "primary",
        href: primaryCta.href,
        className: "pmdk-hero__cta",
        children: primaryCta.label
      })]
    }), illustration && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pmdk-hero__illustration",
      "aria-hidden": "true",
      children: illustration
    })]
  });
}
;// ./node_modules/@wordpress/icons/build-module/library/check.mjs
// packages/icons/src/library/check.tsx


var check_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M16.5 7.5 10 13.9l-2.5-2.4-1 1 3.5 3.6 7.5-7.6z" }) });

//# sourceMappingURL=check.mjs.map

;// ./node_modules/@pressmaximum/dashboard-kit/src/welcome/ChecklistItem.jsx
/**
 * ChecklistItem — single row in the Welcome onboarding checklist.
 * SPEC §5.5 + §5.10b. Tier-2 page component.
 *
 * Status resolution: each item ships an optional `check()` callable
 * that returns `boolean | Promise<boolean>`. The kit runs it on mount;
 * a session-scoped module cache keeps subsequent mounts flash-free
 * (the spike's pattern — stale-while-revalidate). When the consumer's
 * onboarding store flips the manual-completion flag, the consumer
 * threads that into `item.manualCompleted`; the check re-runs.
 *
 * The kit doesn't read the consumer's onboarding store directly —
 * `item.manualCompleted` is the contract. Keeps the kit unaware of
 * which store the consumer registered. Consumer typically wires:
 *
 *   const completedIds = useSelect((s) => s(ONBOARDING_STORE).getCompleted());
 *   const items = baseItems.map((i) => ({
 *       ...i,
 *       manualCompleted: completedIds.includes(i.id),
 *   }));
 *
 * Item shape:
 *
 *   {
 *     id: string,
 *     label: string,                  // already-translated
 *     description?: string,
 *     check?: () => boolean | Promise<boolean>,
 *     manualCompleted?: boolean,      // from consumer's onboarding store
 *     ctaLabel?: string,
 *     ctaHref?: string,               // '#tab' OR external URL
 *     icon?: ComponentType,
 *   }
 *
 * Labels (English fallbacks shipped):
 *
 *   checking  'Checking…'
 *   completed 'Completed'   (sr-only)
 *   pending   'Pending'     (sr-only)
 */







const ChecklistItem_DEFAULT_LABELS = {
  checking: 'Checking…',
  completed: 'Completed',
  pending: 'Pending'
};

// Session-scoped cache for auto-detect check results. Survives Welcome
// remounts so subsequent visits render the last-known state instantly
// (no spinner flash). The check still runs in the background and
// updates the cache + visible state when the answer changes —
// stale-while-revalidate.
//
// `undefined` = never checked → first-ever mount shows the spinner;
// every mount after reads a `boolean` directly.
const CHECK_CACHE = new Map();
function ChecklistItem_isHashHref(href) {
  return typeof href === 'string' && href.startsWith('#');
}
function ChecklistItem({
  item,
  labels: callerLabels
}) {
  const labels = createI18nBag(ChecklistItem_DEFAULT_LABELS, callerLabels);
  // Destructure so the effect's dep array reads PRIMITIVE / FUNCTION
  // references instead of the surrounding `item` object — the parent
  // (Welcome page) typically rebuilds the items array on every render
  // to inject `manualCompleted`, so depending on `item` directly would
  // re-run the check on every parent render. With these three deps,
  // the check runs only when (a) the user navigated to a different
  // task, (b) the consumer's store flipped manualCompleted, or
  // (c) the consumer provided a new check function (rare).
  const {
    id,
    check,
    manualCompleted
  } = item;
  const cached = CHECK_CACHE.get(id);
  const hasCached = cached !== undefined;
  const [completed, setCompleted] = (0,external_wp_element_namespaceObject.useState)(hasCached ? cached : false);
  const [checking, setChecking] = (0,external_wp_element_namespaceObject.useState)(!hasCached);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    let cancelled = false;
    try {
      const result = check ? check() : false;
      Promise.resolve(result).then(value => {
        if (cancelled) {
          return;
        }
        const boolValue = Boolean(value);
        CHECK_CACHE.set(id, boolValue);
        setCompleted(boolValue);
        setChecking(false);
      }).catch(() => {
        if (cancelled) {
          return;
        }
        setChecking(false);
      });
    } catch (_) {
      setChecking(false);
    }
    return () => {
      cancelled = true;
    };
  }, [id, check, manualCompleted]);
  const onNavigate = useNavigate();
  const isHash = ChecklistItem_isHashHref(item.ctaHref);
  const className = 'pmdk-checklist__item' + (completed ? ' is-complete' : '') + (checking ? ' is-checking' : '');
  let statusLabel = labels.pending;
  if (checking) {
    statusLabel = labels.checking;
  } else if (completed) {
    statusLabel = labels.completed;
  }
  let statusIndicator = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
    className: "pmdk-checklist__bullet"
  });
  if (checking) {
    statusIndicator = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Spinner, {});
  } else if (completed) {
    statusIndicator = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
      icon: check_default
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
    className: className,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-checklist__status",
      "aria-hidden": "true",
      role: "presentation",
      children: statusIndicator
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "screen-reader-text",
      children: statusLabel
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pmdk-checklist__body",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h3", {
        className: "pmdk-checklist__label",
        children: item.label
      }), item.description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pmdk-checklist__description",
        children: item.description
      })]
    }), item.ctaHref && item.ctaLabel && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "pmdk-checklist__cta",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
        variant: completed ? 'tertiary' : 'secondary',
        href: item.ctaHref,
        onClick: isHash ? onNavigate(item.ctaHref) : undefined,
        children: item.ctaLabel
      })
    })]
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/welcome/Checklist.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/welcome/Checklist.jsx
/**
 * Checklist — Welcome page onboarding-tasks list. SPEC §5.5.
 *
 * Tier-2 page component: renders an `<ol>` with status indicators per
 * item. The Card chrome around it lives in the consumer's tab page
 * (the spike wrapped this in `<Card>` but the locked CSS class is on
 * the kit's container, so the kit owns the semantic + a11y wrapper).
 *
 * SPEC §16.2 locked classes: `.pmdk-checklist`, `.pmdk-checklist__item`,
 * `.pmdk-checklist__status`, `.pmdk-checklist__cta`.
 *
 * Consumer hooks the onboarding store into each item via
 * `item.manualCompleted` (see ChecklistItem docstring) so the kit
 * never directly reads the consumer's store name.
 *
 * Slot shape:
 *
 *   <Checklist
 *     items={ ChecklistItem[] }
 *     ariaLabel={ string }                  // already-translated
 *     itemLabels={ { checking?, completed?, pending? } }
 *   />
 *
 * Returns `null` when `items` is empty — Welcome pages with the
 * checklist dismissed render nothing here, no zero-row stub.
 */




// SPEC §5.10b: English fallback so the section's accessible name is
// never empty when the consumer forgets to wire `ariaLabel`.

const DEFAULT_ARIA_LABEL = 'Onboarding checklist';
function Checklist({
  items,
  ariaLabel,
  itemLabels
}) {
  if (!Array.isArray(items) || items.length === 0) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("section", {
    className: "pmdk-checklist",
    "aria-label": ariaLabel || DEFAULT_ARIA_LABEL,
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "pmdk-checklist__list",
      children: items.map(item => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ChecklistItem, {
        item: item,
        labels: itemLabels
      }, item.id))
    })
  });
}
;// ./src/backend/admin/dashboard-v2/data/customizerLinks.js



/**
 * Returns the Welcome-tab Customizer quick-link list, derived from the
 * PHP-localised boot payload. Each item carries a short description so
 * the 2-col grid renders title + description stacked.
 *
 * Pro / child theme extends via `customify.dashboard.welcome.links`.
 *
 * @param {object} boot Boot payload (from useBoot()).
 * @return {Array<{ id: string, title: string, description: string, href: string }>}
 */
function useCustomizerLinks(boot) {
  const urls = boot?.urls || {};
  const base = [{
    id: 'logoIdentity',
    title: (0,external_wp_i18n_namespaceObject.__)('Logo & site identity', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Upload logo, site title, tagline.', 'customify'),
    href: urls.logoIdentity
  }, {
    id: 'layout',
    title: (0,external_wp_i18n_namespaceObject.__)('Layout settings', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Container width, content layout.', 'customify'),
    href: urls.layout
  }, {
    id: 'headerBuilder',
    title: (0,external_wp_i18n_namespaceObject.__)('Header builder', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('WYSIWYG header drag-and-drop.', 'customify'),
    href: urls.headerBuilder
  }, {
    id: 'footerBuilder',
    title: (0,external_wp_i18n_namespaceObject.__)('Footer builder', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('WYSIWYG footer drag-and-drop.', 'customify'),
    href: urls.footerBuilder
  }, {
    id: 'styling',
    title: (0,external_wp_i18n_namespaceObject.__)('Styling', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Brand colors and site palette.', 'customify'),
    href: urls.styling
  }, {
    id: 'typography',
    title: (0,external_wp_i18n_namespaceObject.__)('Typography', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Font family, size, line-height.', 'customify'),
    href: urls.typography
  }].filter(l => Boolean(l.href));
  return (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.welcome.links', base, boot);
}
;// ./src/backend/admin/dashboard-v2/data/checklist.js



/**
 * Base onboarding checklist items. Each item's `check()` runs against
 * the boot payload (read at mount time) — no network round-trips.
 *
 * Pro / child theme extends via `customify.dashboard.welcome.checklist`.
 *
 * @param {object} boot Boot payload.
 * @return {Array} Checklist item array (kit's ChecklistItem shape).
 */
function useChecklist(boot) {
  const urls = boot?.urls || {};
  const base = [{
    id: 'site-identity',
    label: (0,external_wp_i18n_namespaceObject.__)('Set your site title and logo', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Fill in your brand name, tagline, and upload a logo from the Customizer.', 'customify'),
    ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Open identity settings', 'customify'),
    ctaHref: urls.logoIdentity || urls.customize,
    check: () => false
  }, {
    id: 'header-builder',
    label: (0,external_wp_i18n_namespaceObject.__)('Build your header', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Drag-and-drop rows in the Customizer to compose the header that fits your site.', 'customify'),
    ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Open header builder', 'customify'),
    ctaHref: urls.headerBuilder || urls.customize,
    check: () => false
  }, {
    id: 'footer-builder',
    label: (0,external_wp_i18n_namespaceObject.__)('Build your footer', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Add widget columns, copyright, and any custom rows to your footer.', 'customify'),
    ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Open footer builder', 'customify'),
    ctaHref: urls.footerBuilder || urls.customize,
    check: () => false
  }, {
    id: 'styling',
    label: (0,external_wp_i18n_namespaceObject.__)('Pick your colors and typography', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Set primary, secondary, text, and heading colors plus type scale.', 'customify'),
    ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Open styling', 'customify'),
    ctaHref: urls.styling || urls.customize,
    check: () => false
  }, {
    id: 'homepage',
    label: (0,external_wp_i18n_namespaceObject.__)('Choose a homepage', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Static page or your latest posts — pick what fits your site.', 'customify'),
    ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Configure homepage', 'customify'),
    ctaHref: urls.homepage || urls.customize,
    check: () => false
  }];
  return (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.welcome.checklist', base, boot);
}
;// ./src/backend/admin/dashboard-v2/ui/ThemeGridCard.jsx

/**
 * Clickable card used inside the Customizer quick-link grid. Whole row
 * is the link surface (title + description stacked).
 *
 * Parent wraps a list of these in `.customify-dashboard-theme-grid` to
 * get the 2-col layout with internal hairline borders.
 */

function ThemeGridCard({
  title,
  description,
  href
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("a", {
    className: "customify-dashboard-theme-grid__item",
    href: href || '#',
    target: "_blank",
    rel: "noopener noreferrer",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
      className: "customify-dashboard-theme-grid__title",
      children: title
    }), description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "customify-dashboard-theme-grid__description",
      children: description
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/data/proModules.js


const DOCS_BASE = 'https://pressmaximum.com/docs/customify/customify-pro-modules/';

/**
 * Pro module catalogue rendered on the Welcome tab. Two shapes:
 *
 *   FREE      — marketing list. `docHref` only; no toggle.
 *   PRO       — same rows but with `classKey`, `enabled`, `hasSettings`,
 *               `canToggle`, optional `subModules` (array of `classKey`s).
 *
 * Customify Pro replaces this list via `customify.dashboard.pro.modules`
 * (returning the PRO shape sourced from window.customifyDashboard.proModules
 * / a REST endpoint).
 *
 * @return {Array<object>}
 */
function useProModules() {
  const base = [{
    id: 'header-sticky',
    name: (0,external_wp_i18n_namespaceObject.__)('Header Sticky', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Let your header stay accessible as users scroll.', 'customify'),
    docHref: DOCS_BASE + 'header-sticky/'
  }, {
    id: 'header-footer-booster',
    name: (0,external_wp_i18n_namespaceObject.__)('Header & Footer Builder Booster', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('More header/footer builder items + advanced styling.', 'customify'),
    docHref: DOCS_BASE + 'advanced-header-footer-builder/'
  }, {
    id: 'scroll-to-top',
    name: (0,external_wp_i18n_namespaceObject.__)('Scroll to Top', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Animated scroll-to-top button for a better UX.', 'customify'),
    docHref: DOCS_BASE + 'scroll-to-top/'
  }, {
    id: 'blog-pro',
    name: (0,external_wp_i18n_namespaceObject.__)('Blog Pro', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Multiple post layouts for richer blog presentations.', 'customify'),
    docHref: DOCS_BASE + 'blog-pro/'
  }, {
    id: 'advanced-styling',
    name: (0,external_wp_i18n_namespaceObject.__)('Advanced Styling', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Layout + typography control for page header title and cover.', 'customify'),
    docHref: DOCS_BASE + 'advanced-styling/'
  }, {
    id: 'portfolio',
    name: (0,external_wp_i18n_namespaceObject.__)('Portfolio', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Showcase your best projects in beautiful layouts.', 'customify'),
    docHref: DOCS_BASE + 'portfolio/'
  }, {
    id: 'multiple-headers',
    name: (0,external_wp_i18n_namespaceObject.__)('Multiple Headers', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Unique headers per page, post, archive, or WooCommerce page.', 'customify'),
    docHref: DOCS_BASE + 'multiple-headers/'
  }, {
    id: 'mega-menu',
    name: (0,external_wp_i18n_namespaceObject.__)('Mega Menu', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Mega-menu navigation with more space and visual hierarchy.', 'customify'),
    docHref: DOCS_BASE + 'mega-menu/'
  }, {
    id: 'multilingual',
    name: (0,external_wp_i18n_namespaceObject.__)('Multilingual Integration', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('WPML support plus a built-in language-switcher header item.', 'customify'),
    docHref: DOCS_BASE
  }, {
    id: 'custom-fonts',
    name: (0,external_wp_i18n_namespaceObject.__)('Custom Fonts', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Upload and use self-hosted fonts across your site.', 'customify'),
    docHref: DOCS_BASE + 'custom-fonts/'
  }, {
    id: 'typekit',
    name: (0,external_wp_i18n_namespaceObject.__)('Typekit', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Use Adobe Typekit fonts on your Customify site.', 'customify'),
    docHref: DOCS_BASE + 'typekit-fonts/'
  }, {
    id: 'hooks',
    name: (0,external_wp_i18n_namespaceObject.__)('Customify Hooks', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Add custom hook scripts without touching theme files.', 'customify'),
    docHref: DOCS_BASE + 'customify-hooks/'
  }, {
    id: 'woocommerce-booster',
    name: (0,external_wp_i18n_namespaceObject.__)('WooCommerce Booster', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Creative control of style + layout options for your shop.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-booster/',
    subModules: ['single-product-layouts', 'off-canvas-filter', 'gallery-slider', 'quick-view']
  }, {
    id: 'single-product-layouts',
    name: (0,external_wp_i18n_namespaceObject.__)('Single Product Layouts', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Multiple beautiful single-product layouts.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-single-product-layouts/',
    parent: 'woocommerce-booster'
  }, {
    id: 'off-canvas-filter',
    name: (0,external_wp_i18n_namespaceObject.__)('Off Canvas Filter', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Off-canvas product filter for shop and archive pages.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-off-canvas-filter/',
    parent: 'woocommerce-booster'
  }, {
    id: 'gallery-slider',
    name: (0,external_wp_i18n_namespaceObject.__)('Product Gallery Slider', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Slider for the WooCommerce product gallery.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-product-gallery-slider/',
    parent: 'woocommerce-booster'
  }, {
    id: 'quick-view',
    name: (0,external_wp_i18n_namespaceObject.__)('Quick View', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Modal quick-view for product listings.', 'customify'),
    docHref: DOCS_BASE + 'woocommerce-quick-view/',
    parent: 'woocommerce-booster'
  }, {
    id: 'infinity-scroll',
    name: (0,external_wp_i18n_namespaceObject.__)('Infinity Scroll', 'customify'),
    description: (0,external_wp_i18n_namespaceObject.__)('Auto-load the next posts/products as the reader nears the bottom.', 'customify'),
    docHref: DOCS_BASE + 'infinity-scroll/'
  }];
  return (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.pro.modules', base);
}
;// ./src/backend/admin/dashboard-v2/ui/ModuleList.jsx

/**
 * Module list grid — 2-col layout with internal hairline borders.
 * Ported from the theme-dashboard branch `pm-module-list` pattern.
 *
 *   <ModuleList>
 *     <ModuleRow leading={ <FormToggle ... /> } title="..." description="..." trailing={ ... } />
 *     ...
 *   </ModuleList>
 *
 * Rows with `has-subs` class span the full width and their sub-module
 * group renders inside `.customify-dashboard-module-list__subs` below.
 *
 * `notice` (optional) renders inline beside the title as a small dimmed
 * label — used by the Pro dashboard to flag gated modules (e.g. the
 * WooCommerce Booster row showing "WooCommerce not activated" when
 * WooCommerce isn't installed).
 */

function ModuleList({
  children,
  className
}) {
  const classes = ['customify-dashboard-module-list'];
  if (className) {
    classes.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: classes.join(' '),
    children: children
  });
}
function ModuleRow({
  title,
  description,
  leading,
  trailing,
  notice,
  hasSubs,
  className
}) {
  const classes = ['customify-dashboard-module-row'];
  if (hasSubs) {
    classes.push('has-subs');
  }
  if (className) {
    classes.push(className);
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: classes.join(' '),
    children: [leading && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-module-row__leading",
      children: leading
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-dashboard-module-row__body",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("h4", {
        className: "customify-dashboard-module-row__title",
        children: [title, notice && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "customify-dashboard-module-row__notice",
          children: notice
        })]
      }), description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "customify-dashboard-module-row__description",
        children: description
      })]
    }), trailing && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-module-row__trailing",
      children: trailing
    })]
  });
}
function ModuleSubmodules({
  children
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "customify-dashboard-module-list__subs",
    children: children
  });
}
;// ./src/backend/admin/dashboard-v2/sections/ProModulesSection.jsx
/**
 * Pro Modules card section on the Welcome tab.
 *
 * Two render paths:
 *   1. Pro plugin NOT active (`boot.proActive === false`) → marketing
 *      list. Each row shows title + description + a *disabled* FormToggle
 *      in the leading slot wrapped in a Tooltip ("Available in Pro
 *      version") so the row reads as "something you can flip — in Pro"
 *      while keeping the upsell story tight. Header CTA stays "Upgrade
 *      Now".
 *   2. Pro plugin active → same list (overridden via the
 *      `customify.dashboard.pro.modules` filter from Pro's bundle) plus a
 *      live `@wordpress/components` FormToggle in the leading slot +
 *      Settings trailing link when the module has settings + is enabled.
 *      Toggling calls a handler Pro supplies via the
 *      `customify.dashboard.pro.toggle` filter; the kit ships a no-op +
 *      reject so the marketing path can't accidentally flip server state.
 *      When a module has `canToggle: false` (e.g. WooCommerce Booster
 *      without WooCommerce active) the toggle renders disabled + the row
 *      shows `toggleDisableNotice` inline beside the name.
 *
 * Pro extension surface:
 *   - `customify.dashboard.pro.modules`  — replaces the module catalogue.
 *   - `customify.dashboard.pro.toggle`   — toggle handler `(classKey,
 *                                            nextEnabled) => Promise<{ enabled }>`.
 */











// Same green-circle check glyph used by the Settings tab snackbar so
// module activate / deactivate toasts visually match the Save Settings
// success cue. SnackbarList drops this inside .components-snackbar__icon.

const SUCCESS_GLYPH = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
  className: "customify-dashboard-snackbar__check",
  children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
    icon: check_default,
    size: 14
  })
});
const ProModulesSection_NOTICES_STORE = 'core/notices';
function DocsLink({
  href
}) {
  if (!href) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
    className: "customify-dashboard-module-link",
    href: href,
    target: "_blank",
    rel: "noopener noreferrer",
    children: (0,external_wp_i18n_namespaceObject.__)('Docs', 'customify')
  });
}
function ProModulesSection({
  boot
}) {
  const modules = useProModules();
  const proActive = Boolean(boot?.proActive);

  // Recompute every render — `modules` comes from applyFilters which a
  // later-loading bundle (Pro) may mutate after our first paint.
  const byId = {};
  modules.forEach(m => {
    byId[m.id] = m;
  });
  const topLevel = modules.filter(m => !m.parent);

  // Local optimistic state; on mount seed from `enabled` flags Pro injects.
  const [enabledMap, setEnabledMap] = (0,external_wp_element_namespaceObject.useState)(() => {
    const map = {};
    modules.forEach(m => {
      map[m.id] = !!m.enabled;
    });
    return map;
  });
  const [pendingMap, setPendingMap] = (0,external_wp_element_namespaceObject.useState)({});

  // Recompute every render — Pro bridge's toggle handler registers
  // AFTER the theme's mountDashboard runs. A useMemo over [] would
  // cache the pre-registration `null` value and silently disable
  // the toggle flow.
  const proToggle = (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.pro.toggle', null);
  const handleToggle = (0,external_wp_element_namespaceObject.useCallback)(id => {
    if (typeof proToggle !== 'function') {
      return;
    }
    // Safety net: the disabled FormToggle suppresses onChange in the
    // browser, but a programmatic dispatch here would otherwise let a
    // gated module (e.g. WooCommerce Booster without WooCommerce
    // active) flip its state on the server.
    if (byId[id] && byId[id].canToggle === false) {
      return;
    }
    const current = !!enabledMap[id];
    const next = !current;
    const moduleName = byId[id]?.name || id;
    setEnabledMap(prev => ({
      ...prev,
      [id]: next
    }));
    setPendingMap(prev => ({
      ...prev,
      [id]: true
    }));
    Promise.resolve(proToggle(id, next)).then(res => {
      const flag = !!(res && res.enabled);
      setEnabledMap(prev => ({
        ...prev,
        [id]: flag
      }));
      (0,external_wp_data_namespaceObject.dispatch)(ProModulesSection_NOTICES_STORE).createSuccessNotice((0,external_wp_i18n_namespaceObject.sprintf)(next ?
      // translators: %s module name.
      (0,external_wp_i18n_namespaceObject.__)('"%s" activated.', 'customify') :
      // translators: %s module name.
      (0,external_wp_i18n_namespaceObject.__)('"%s" deactivated.', 'customify'), moduleName), {
        type: 'snackbar',
        isDismissible: true,
        icon: SUCCESS_GLYPH
      });
    }).catch(() => {
      setEnabledMap(prev => ({
        ...prev,
        [id]: current
      }));
      (0,external_wp_data_namespaceObject.dispatch)(ProModulesSection_NOTICES_STORE).createErrorNotice((0,external_wp_i18n_namespaceObject.sprintf)(
      // translators: %s module name.
      (0,external_wp_i18n_namespaceObject.__)('Could not update "%s". Please try again.', 'customify'), moduleName), {
        type: 'snackbar',
        isDismissible: true
      });
    }).finally(() => {
      setPendingMap(prev => {
        const copy = {
          ...prev
        };
        delete copy[id];
        return copy;
      });
    });
  }, [proToggle, enabledMap, byId]);
  const renderRow = (mod, isSub = false) => {
    const checked = !!enabledMap[mod.id];
    const pending = !!pendingMap[mod.id];
    // `proAvailable` flips when the runtime is wired up (Pro plugin
    // active + bridge toggle handler registered). Free path keeps the
    // toggle visible but disabled so the row still reads as
    // "something you can flip — in Pro" and the upgrade CTA above
    // the list has visible context.
    const proAvailable = proActive && typeof proToggle === 'function';
    // `canToggle: false` arrives from Pro when a runtime dependency is
    // missing (WooCommerce Booster + its sub-modules when WooCommerce
    // isn't active). Render the toggle disabled instead of hiding it
    // so the user sees the row state + its notice.
    const allowed = mod.canToggle !== false;
    const toggleDisabled = !proAvailable || pending || !allowed;
    const showSettings = proAvailable && allowed && checked && mod.hasSettings && !pending;
    const showsSubs = !isSub && mod.subModules && mod.subModules.length > 0;
    const notice = proAvailable && !allowed && mod.toggleDisableNotice ? mod.toggleDisableNotice : null;
    const toggle = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FormToggle, {
      checked: proAvailable && checked,
      onChange: () => handleToggle(mod.id),
      disabled: toggleDisabled,
      "aria-label": mod.name
    });

    // Free path: wrap the disabled toggle in a Tooltip so hovering
    // surfaces the "Available in Pro version" hint. The wrap span
    // catches pointer events that the disabled <input> doesn't fire
    // itself.
    const leading = proAvailable ? toggle : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Tooltip, {
      text: (0,external_wp_i18n_namespaceObject.__)('Available in Pro version', 'customify'),
      placement: "top",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: "customify-dashboard-module-row__toggle-wrap",
        children: toggle
      })
    });
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleRow, {
      title: mod.name,
      description: mod.description,
      notice: notice,
      hasSubs: showsSubs,
      leading: leading,
      trailing: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
        children: [showSettings && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("button", {
          type: "button",
          className: "customify-dashboard-module-link customify-dashboard-module-link--settings",
          onClick: () => HashRouter_navigate(`#settings/${mod.id}`),
          children: (0,external_wp_i18n_namespaceObject.__)('Settings', 'customify')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(DocsLink, {
          href: mod.docHref
        })]
      })
    }, mod.id);
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Card, {
    className: "customify-dashboard-welcome__pro",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.CardHeader, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
        className: "customify-dashboard-welcome__checklist-title",
        children: (0,external_wp_i18n_namespaceObject.__)('Customify Pro modules', 'customify')
      }), !proActive && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
        variant: "primary",
        href: boot?.urls?.proUpgrade || '#',
        target: "_blank",
        rel: "noopener noreferrer",
        icon: external_default,
        iconPosition: "right",
        children: (0,external_wp_i18n_namespaceObject.__)('Upgrade now', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleList, {
      children: topLevel.map(mod => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_element_namespaceObject.Fragment, {
        children: [renderRow(mod, false), mod.subModules && mod.subModules.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ModuleSubmodules, {
          children: mod.subModules.map(subId => {
            const sub = byId[subId];
            return sub ? renderRow(sub, true) : null;
          })
        })]
      }, mod.id))
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/tabs/Welcome.jsx
/**
 * Welcome tab — single-page scroll with Hero + Checklist + Customizer
 * quick-link grid + Pro module grid. Composes kit primitives + small
 * theme-owned UI components (`ModuleList`, `ThemeGridCard`).
 */










// Onboarding checklist is hidden until each item's `check()` ships real
// detection (logo set, header configured, etc.). The card + data hook
// stay intact; flip this back to `true` once the detection lands.

const SHOW_CHECKLIST = false;
function Welcome() {
  const boot = useBoot();
  const links = useCustomizerLinks(boot);
  const checklistItems = useChecklist(boot);
  const greeting = (0,external_wp_i18n_namespaceObject.__)('Welcome to Customify', 'customify');
  const tagline = (0,external_wp_i18n_namespaceObject.__)('Lightweight, SEO-optimized, multipurpose WordPress theme. Set up your site identity, header, footer, and styling — all from the Customizer.', 'customify');
  const extraSections = (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.welcome.sections', [], boot);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-dashboard-welcome",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Hero, {
      greeting: greeting,
      tagline: tagline,
      primaryCta: {
        label: (0,external_wp_i18n_namespaceObject.__)('Open the Customizer', 'customify'),
        href: boot?.urls?.customize || '#'
      }
    }), SHOW_CHECKLIST && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Card, {
      className: "customify-dashboard-welcome__checklist",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.CardHeader, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
          className: "customify-dashboard-welcome__checklist-title",
          children: (0,external_wp_i18n_namespaceObject.__)('Get started', 'customify')
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Checklist, {
        items: checklistItems,
        ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Customify onboarding checklist', 'customify'),
        itemLabels: {
          checking: (0,external_wp_i18n_namespaceObject.__)('Checking…', 'customify'),
          completed: (0,external_wp_i18n_namespaceObject.__)('Completed', 'customify'),
          pending: (0,external_wp_i18n_namespaceObject.__)('Pending', 'customify')
        }
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Card, {
      className: "customify-dashboard-welcome__theme-customizer",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.CardHeader, {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
          className: "customify-dashboard-welcome__checklist-title",
          children: (0,external_wp_i18n_namespaceObject.__)('Customizer quick links', 'customify')
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
          className: "customify-dashboard-header-link",
          href: boot?.urls?.customize || '#',
          target: "_blank",
          rel: "noopener noreferrer",
          children: (0,external_wp_i18n_namespaceObject.__)('Go to Customizer', 'customify')
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-dashboard-theme-grid",
        children: links.map(link => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ThemeGridCard, {
          title: link.title,
          description: link.description,
          href: link.href
        }, link.id))
      })]
    }), extraSections.map(section => {
      const Render = section.render;
      return Render ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Render, {
        boot: boot
      }, section.id) : null;
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ProModulesSection, {
      boot: boot
    })]
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/compare/CompareTable.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/compare/CompareTable.jsx
/**
 * CompareTable — Free vs Pro matrix display component. SPEC §5.3b +
 * §5.13 Tier-2.
 *
 * CSS-grid (not `<table>`) so the dashboard chassis doesn't inherit
 * wp-admin's table baseline styles. Columns: feature label (2fr) +
 * Free / Pro check cells (1fr each).
 *
 * Cell dispatch on shape:
 *
 *   true            → green-circle check badge
 *   false / null    → gray-circle em-dash badge
 *   string          → literal text
 *   { value, muted } → muted-variant text
 *
 * SPEC §16.2 locked classes: `.pmdk-compare`, `.pmdk-compare__row`,
 * `.pmdk-compare__check-yes`, `.pmdk-compare__check-no`.
 *
 * Optional `footer` renders an in-card CTA banner so the upgrade prompt
 * sits attached to the matrix it summarizes.
 *
 * Labels (English fallbacks shipped):
 *   headFeature  'Feature'
 *   headFree     'Free'                (SPEC §5.10b: headColumnFree)
 *   headPro      'Pro'                 (SPEC §5.10b: headColumnPro)
 *   cellYes      'Included'            (sr-only)
 *   cellNo       'Not included'        (sr-only)
 */






const CompareTable_DEFAULT_LABELS = {
  headFeature: 'Feature',
  headFree: 'Free',
  headPro: 'Pro',
  cellYes: 'Included',
  cellNo: 'Not included'
};
function Cell({
  value,
  labels
}) {
  if (value === true) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-compare__check-yes",
      "aria-label": labels.cellYes,
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
        icon: check_default,
        size: 16
      })
    });
  }
  if (value === false || value === null || value === undefined) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-compare__check-no",
      "aria-label": labels.cellNo,
      children: "\u2212"
    });
  }
  if (typeof value === 'string') {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-compare__text",
      children: value
    });
  }
  if (value && typeof value === 'object' && 'value' in value) {
    const className = 'pmdk-compare__text' + (value.muted ? ' is-muted' : '');
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: className,
      children: value.value
    });
  }
  return null;
}
function CompareTable({
  sections,
  footer,
  labels: callerLabels
}) {
  if (!Array.isArray(sections) || sections.length === 0) {
    return null;
  }
  const labels = createI18nBag(CompareTable_DEFAULT_LABELS, callerLabels);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "pmdk-compare",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pmdk-compare__head",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pmdk-compare__head-cell",
        children: labels.headFeature
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pmdk-compare__head-cell pmdk-compare__head-cell--center",
        children: labels.headFree
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pmdk-compare__head-cell pmdk-compare__head-cell--center pmdk-compare__head-cell--pro",
        children: labels.headPro
      })]
    }), sections.map(section => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("section", {
      className: "pmdk-compare__section",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h3", {
        className: "pmdk-compare__section-title",
        children: section.label
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "pmdk-compare__rows",
        children: section.rows.map(row => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
          className: "pmdk-compare__row",
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
            className: "pmdk-compare__feature",
            children: row.label
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
            className: "pmdk-compare__cell-wrap",
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Cell, {
              value: row.free,
              labels: labels
            })
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
            className: "pmdk-compare__cell-wrap",
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Cell, {
              value: row.pro,
              labels: labels
            })
          })]
        }, `${section.id}-${row.id}`))
      })]
    }, section.id)), footer && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "pmdk-compare__cta",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        className: "pmdk-compare__cta-text",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h4", {
          className: "pmdk-compare__cta-title",
          children: footer.title
        }), footer.description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
          className: "pmdk-compare__cta-description",
          children: footer.description
        })]
      }), footer.ctaHref && footer.ctaLabel && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
        variant: "primary",
        href: footer.ctaHref,
        target: "_blank",
        rel: "noopener noreferrer",
        children: footer.ctaLabel
      })]
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/data/freeVsPro.js
/**
 * Free vs Pro compare matrix — hand-curated; ships in Customify Free.
 *
 * No REST endpoint, no boot dependency: the matrix is static copy bundled
 * into the dashboard JS. Module names + descriptions mirror the legacy
 * `Customify_Dashboard::pro_modules_box()` upsell grid and the new
 * `data/proModules.js` catalogue so the three surfaces stay consistent.
 *
 * Cell semantics (per kit `<CompareTable>` cell dispatch):
 *
 *   true            → green-circle check badge
 *   false           → gray-circle em-dash badge
 *   string          → literal text
 *   { value, muted } → muted-variant text
 *
 * Returns a function so the `__()` calls run after WordPress i18n hydrates
 * the `customify` text domain (mirrors Blocksify's `buildCompareMatrix()`
 * pattern).
 */


function buildFreeVsProMatrix() {
  return [{
    id: 'site-composition',
    label: (0,external_wp_i18n_namespaceObject.__)('Site composition', 'customify'),
    rows: [{
      id: 'header-builder',
      label: (0,external_wp_i18n_namespaceObject.__)('Drag-and-drop header builder', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'footer-builder',
      label: (0,external_wp_i18n_namespaceObject.__)('Drag-and-drop footer builder', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'layouts',
      label: (0,external_wp_i18n_namespaceObject.__)('Container widths + sidebar layouts', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'block-editor',
      label: (0,external_wp_i18n_namespaceObject.__)('Block editor patterns + alignwide / alignfull', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'multiple-headers',
      label: (0,external_wp_i18n_namespaceObject.__)('Multiple headers per page / post / archive', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'mega-menu',
      label: (0,external_wp_i18n_namespaceObject.__)('Mega menu', 'customify'),
      free: false,
      pro: true
    }]
  }, {
    id: 'header-footer',
    label: (0,external_wp_i18n_namespaceObject.__)('Header & footer', 'customify'),
    rows: [{
      id: 'standard-items',
      label: (0,external_wp_i18n_namespaceObject.__)('Standard items: logo, menu, search, social, HTML', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'header-sticky',
      label: (0,external_wp_i18n_namespaceObject.__)('Sticky header', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'header-footer-booster',
      label: (0,external_wp_i18n_namespaceObject.__)('Header & Footer Builder Booster (extra items + styling)', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'multilingual',
      label: (0,external_wp_i18n_namespaceObject.__)('WPML multilingual switcher header item', 'customify'),
      free: false,
      pro: true
    }]
  }, {
    id: 'typography-styling',
    label: (0,external_wp_i18n_namespaceObject.__)('Typography & styling', 'customify'),
    rows: [{
      id: 'google-fonts',
      label: (0,external_wp_i18n_namespaceObject.__)('Google Fonts', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'typography-tokens',
      label: (0,external_wp_i18n_namespaceObject.__)('Typography tokens (size, weight, line-height)', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'global-colors',
      label: (0,external_wp_i18n_namespaceObject.__)('Global colors (primary, secondary, text, link, heading)', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'custom-fonts',
      label: (0,external_wp_i18n_namespaceObject.__)('Self-hosted custom fonts', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'typekit',
      label: (0,external_wp_i18n_namespaceObject.__)('Adobe Typekit fonts', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'advanced-styling',
      label: (0,external_wp_i18n_namespaceObject.__)('Advanced page-header title + cover layouts', 'customify'),
      free: false,
      pro: true
    }]
  }, {
    id: 'blog-portfolio',
    label: (0,external_wp_i18n_namespaceObject.__)('Blog & portfolio', 'customify'),
    rows: [{
      id: 'blog-standard',
      label: (0,external_wp_i18n_namespaceObject.__)('Blog listing + single-post layouts', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'blog-pro',
      label: (0,external_wp_i18n_namespaceObject.__)('Blog Pro layouts (grid / masonry / mixed)', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'portfolio',
      label: (0,external_wp_i18n_namespaceObject.__)('Portfolio post type + layouts', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'infinity-scroll',
      label: (0,external_wp_i18n_namespaceObject.__)('Infinity scroll for posts and products', 'customify'),
      free: false,
      pro: true
    }]
  }, {
    id: 'woocommerce',
    label: (0,external_wp_i18n_namespaceObject.__)('WooCommerce', 'customify'),
    rows: [{
      id: 'wc-compat',
      label: (0,external_wp_i18n_namespaceObject.__)('WooCommerce compatibility', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'wc-booster',
      label: (0,external_wp_i18n_namespaceObject.__)('WooCommerce Booster (shop styling + layout)', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'single-product-layouts',
      label: (0,external_wp_i18n_namespaceObject.__)('Single product layouts', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'off-canvas-filter',
      label: (0,external_wp_i18n_namespaceObject.__)('Off-canvas product filter', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'gallery-slider',
      label: (0,external_wp_i18n_namespaceObject.__)('Product gallery slider', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'quick-view',
      label: (0,external_wp_i18n_namespaceObject.__)('Product quick-view modal', 'customify'),
      free: false,
      pro: true
    }]
  }, {
    id: 'workflow-support',
    label: (0,external_wp_i18n_namespaceObject.__)('Workflow & support', 'customify'),
    rows: [{
      id: 'page-builders',
      label: (0,external_wp_i18n_namespaceObject.__)('Page builder compatibility (Gutenberg, Elementor, Beaver, Divi)', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'updates',
      label: (0,external_wp_i18n_namespaceObject.__)('Auto-updates', 'customify'),
      free: true,
      pro: true
    }, {
      id: 'scroll-to-top',
      label: (0,external_wp_i18n_namespaceObject.__)('Scroll-to-top button', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'hooks',
      label: (0,external_wp_i18n_namespaceObject.__)('Customify Hooks (custom code snippets)', 'customify'),
      free: false,
      pro: true
    }, {
      id: 'support',
      label: (0,external_wp_i18n_namespaceObject.__)('Support', 'customify'),
      free: {
        value: (0,external_wp_i18n_namespaceObject.__)('Community', 'customify'),
        muted: true
      },
      pro: (0,external_wp_i18n_namespaceObject.__)('Priority', 'customify')
    }]
  }];
}
;// ./src/backend/admin/dashboard-v2/tabs/FreeVsPro.jsx
/**
 * Free vs Pro tab — Customify Free upsell matrix.
 *
 * Tab visibility:
 *   - When Pro inactive: `index.js` pushes `free-vs-pro` into baseTabs +
 *     registers the `#free-vs-pro` route → this component renders.
 *   - When Pro active: `index.js` drops both → kit's HashRouter falls back
 *     to `#welcome` for any leftover deep link (no extra guard needed
 *     here).
 *
 * Layout copies Blocksify Free's pattern (consumer-side heading + tagline
 * above kit's `<CompareTable>`). Matrix data is hand-curated in
 * `../data/freeVsPro.js`; kit's CompareTable handles cell dispatch + CTA
 * banner from a single `footer` prop.
 */





function FreeVsPro() {
  const boot = useBoot();
  const sections = buildFreeVsProMatrix();
  const labels = {
    headFeature: (0,external_wp_i18n_namespaceObject.__)('Feature', 'customify'),
    headFree: (0,external_wp_i18n_namespaceObject.__)('Free', 'customify'),
    headPro: (0,external_wp_i18n_namespaceObject.__)('Pro', 'customify'),
    cellYes: (0,external_wp_i18n_namespaceObject.__)('Included', 'customify'),
    cellNo: (0,external_wp_i18n_namespaceObject.__)('Not included', 'customify')
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-dashboard-free-vs-pro",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("header", {
      className: "customify-dashboard-free-vs-pro__header",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
        className: "customify-dashboard-free-vs-pro__title",
        children: (0,external_wp_i18n_namespaceObject.__)('Free vs Pro', 'customify')
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "customify-dashboard-free-vs-pro__tagline",
        children: (0,external_wp_i18n_namespaceObject.__)('Customify Free covers a complete site build. Pro unlocks the modules and workflow features power-users reach for.', 'customify')
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(CompareTable, {
      sections: sections,
      labels: labels,
      footer: {
        title: (0,external_wp_i18n_namespaceObject.__)('Ready to unlock every module?', 'customify'),
        // Description kept tight so kit's `.pmdk-compare__cta`
        // flex layout doesn't wrap the button to a new line
        // (`flex: 1 1 auto` on `.pmdk-compare__cta-text` —
        // long descriptions force the button down; tracked as
        // kit K-014).
        description: (0,external_wp_i18n_namespaceObject.__)('Sticky headers, WooCommerce Booster, Blog Pro, custom fonts, and priority support.', 'customify'),
        ctaLabel: (0,external_wp_i18n_namespaceObject.__)('Upgrade to Customify Pro', 'customify'),
        ctaHref: boot?.urls?.proUpgrade || 'https://pressmaximum.com/customify/pro-upgrade/'
      }
    })]
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/SchemaField.jsx

/**
 * SchemaField — Tier-1 layout primitive (SPEC §5.13). Dispatches on
 * `field.type` against a consumer-supplied `fieldTypes` map; renders
 * nothing when the type isn't registered (consumer-facing typo / Pro
 * field type not yet loaded).
 *
 * Why prop-injection instead of context / hardcoded map?
 * The consumer applies their `{ns}.dashboard.settings.field-types`
 * filter once at the call site (typically in their Settings tab), spreads
 * the resolved map down. Keeps the kit unaware of any specific filter
 * namespace and lets the SchemaForm caller memoize the map.
 *
 * @example
 *   <SchemaField
 *       field={ { id: 'enable', label: 'Enable feature', type: 'boolean' } }
 *       value={ true }
 *       onChange={ (next) => store.edit('group.enable', next) }
 *       fieldTypes={ FIELD_TYPES }
 *   />
 */

function SchemaField({
  field,
  value,
  onChange,
  fieldTypes
}) {
  if (!field || !fieldTypes) {
    return null;
  }
  const Component = fieldTypes[field.type];
  if (!Component) {
    return null;
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Component, {
    field: field,
    value: value,
    onChange: onChange
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/SchemaForm.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/SchemaForm.jsx
/**
 * SchemaForm — Tier-1 layout primitive (SPEC §5.13). Renders ONE panel
 * — the consumer resolves "which panel is active" externally (route
 * param + sub-nav) so the kit doesn't own that state.
 *
 * SPEC §5.4 panel shape:
 *
 *   { id, label, description?, fields: SchemaField[] }     ← schema-driven
 *   { id, label, component: ComponentType }                ← Pro custom takeover
 *
 * Custom-panel takeover (the `component` branch) is how Pro replaces a
 * whole panel — receives `{ panel, values, onFieldChange }` exactly like
 * SchemaForm itself, so swapping in a custom component is transparent
 * to the parent.
 *
 * `values` is the merged settings snapshot from
 * `createSettingsStore.getSettings()` (saved + dirty). The form reads
 * `values[panel.id][field.id]` for each field — the dotted-path
 * convention `'panelId.fieldId'` is what `store.edit(path, value)`
 * accumulates into the dirty buffer.
 *
 * `fieldTypes` is the resolved map (kit `BASE_FIELD_TYPES` + consumer's
 * filter extensions) — see `fieldTypes.jsx` for the rationale of
 * prop-injection over context.
 */




function getAtPath(target, group, key) {
  if (!target || typeof target !== 'object') {
    return undefined;
  }
  const groupObj = target[group];
  if (!groupObj || typeof groupObj !== 'object') {
    return undefined;
  }
  return groupObj[key];
}

/**
 * Stable id for the panel heading element so external CardHeader copy
 * can reference it via `aria-labelledby`. Consumers that render their
 * own heading outside the form pass the same id to keep the AT chain.
 *
 * @param {string} id Panel id, e.g. `'performance'`.
 * @return {string} DOM id, e.g. `'pmdk-settings-panel-performance'`.
 */
function panelHeadingId(id) {
  return `pmdk-settings-panel-${id}`;
}
function SchemaForm({
  panel,
  values,
  onFieldChange,
  fieldTypes
}) {
  if (!panel) {
    return null;
  }
  const headingId = panelHeadingId(panel.id);

  // Pro full-takeover branch: panel provides its own component instead
  // of a `fields` array. The custom component owns rendering + edit
  // dispatch entirely — kit just supplies the panel + merged values +
  // the onFieldChange callback so it can write back through the same
  // store action the schema-driven branch uses.
  if (panel.component) {
    const Custom = panel.component;
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      role: "group",
      "aria-labelledby": headingId,
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Custom, {
        panel: panel,
        values: values,
        onFieldChange: onFieldChange
      })
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "pmdk-schema-form",
    role: "group",
    "aria-labelledby": headingId,
    children: (panel.fields || []).map(field => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SchemaField, {
      field: field,
      value: getAtPath(values, panel.id, field.id),
      onChange: next => onFieldChange(panel.id, field.id, next),
      fieldTypes: fieldTypes
    }, field.id))
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/SaveBar.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/SaveBar.jsx
/**
 * SaveBar — Tier-2 page component (SPEC §5.13). Left-aligned status text
 * mirrors the store lifecycle (saving / dirty / saved); right cluster is
 * a dirty-gated Save plus a Reset that prompts before dispatching.
 *
 * Locked CSS class per SPEC §16.2: `.pmdk-save-bar`.
 *
 * String surface (SPEC §5.10b — English fallbacks shipped, consumer's
 * `__()` extraction happens at the call site via the `labels` prop):
 *
 *   regionLabel    aria-label on the bar (default 'Settings actions')
 *   saveLabel      primary button copy (default 'Save changes')
 *   savingLabel    primary button copy while saving (default 'Saving…')
 *   resetLabel     reset button copy (default 'Reset to defaults')
 *   statusSaved    left-side status when clean (default 'No pending changes')
 *   statusDirty    left-side status when dirty (default 'Unsaved changes')
 *   statusSaving   left-side status while saving (default 'Saving…')
 *
 * The Reset confirmation prompt lives in the consumer's onReset handler
 * (browser-native `confirm()` with their translated copy) — keeps the
 * kit free of the `confirm()` text. SPEC §5.10b `resetConfirmLabel` is
 * a consumer-side string, not a kit prop.
 *
 * `resetDisabledWhenNotDirty` (default `false`) — when `true`, the
 * Reset button disables alongside Save when the form is clean. Use
 * for consumers where Reset semantically means "discard dirty edits"
 * (per-section forms, modal settings panels). Leave `false` for
 * factory-defaults reset semantics where the button should stay
 * clickable even when nothing is dirty. KIT_ISSUES K-011.
 */






const SaveBar_DEFAULT_LABELS = {
  regionLabel: 'Settings actions',
  saveLabel: 'Save changes',
  savingLabel: 'Saving…',
  resetLabel: 'Reset to defaults',
  // Neutral phrasing instead of the older "All changes saved" — that
  // label read as a confirmation of a save the user never performed on
  // first page load (KIT_ISSUES K-011). The consumer's snackbar covers
  // the actual "just saved" cue; the SaveBar describes state.
  statusSaved: 'No pending changes',
  statusDirty: 'Unsaved changes',
  statusSaving: 'Saving…'
};
function Status({
  isDirty,
  isSaving,
  labels
}) {
  if (isSaving) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("span", {
      className: "pmdk-save-bar__status is-saving",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Spinner, {}), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        children: labels.statusSaving
      })]
    });
  }
  if (isDirty) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      className: "pmdk-save-bar__status is-dirty",
      children: labels.statusDirty
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("span", {
    className: "pmdk-save-bar__status is-saved",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
      icon: check_default,
      size: 16
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
      children: labels.statusSaved
    })]
  });
}
function SaveBar({
  isDirty,
  isSaving,
  onSave,
  onReset,
  labels: callerLabels,
  resetDisabledWhenNotDirty = false
}) {
  const labels = createI18nBag(SaveBar_DEFAULT_LABELS, callerLabels);
  const resetDisabled = isSaving || resetDisabledWhenNotDirty && !isDirty;
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "pmdk-save-bar",
    role: "region",
    "aria-label": labels.regionLabel,
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Flex, {
      justify: "space-between",
      align: "center",
      gap: 3,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Status, {
          isDirty: isDirty,
          isSaving: isSaving,
          labels: labels
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Flex, {
          align: "center",
          gap: 2,
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
              variant: "tertiary",
              isDestructive: true,
              onClick: onReset,
              disabled: resetDisabled,
              children: labels.resetLabel
            })
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
            children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
              variant: "primary",
              onClick: onSave,
              disabled: !isDirty || isSaving,
              children: isSaving ? labels.savingLabel : labels.saveLabel
            })
          })]
        })
      })]
    })
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/fieldTypes.jsx
/**
 * BASE_FIELD_TYPES — the kit's built-in field renderers for SchemaField
 * dispatch. SPEC §5.4 + §9.1 `{ns}.dashboard.settings.field-types` filter.
 *
 * Each renderer receives `{ field, value, onChange }`:
 *   - `field` — the field schema (id, label, description, type, options?,
 *     min/max/step? for numbers, etc.)
 *   - `value` — current resolved value (saved + dirty merge from
 *     `createSettingsStore.getSettings()`)
 *   - `onChange` — emits the next value; the SchemaForm caller wires
 *     this to `store.edit(path, value)`.
 *
 * Consumers extend the map via their own filter — kit doesn't apply the
 * filter itself because it would need to know the consumer's namespace.
 * Typical usage:
 *
 *   import { BASE_FIELD_TYPES } from '@pressmaximum/dashboard-kit';
 *   import { applyFilters } from '@wordpress/hooks';
 *   import { createFilterNamespace } from '@pressmaximum/dashboard-kit';
 *
 *   const FILTERS = createFilterNamespace('customify');
 *   const fieldTypes = applyFilters(
 *       FILTERS.settingsFieldTypes,
 *       { ...BASE_FIELD_TYPES }
 *   );
 *   // ...then pass `fieldTypes` to <SchemaForm>.
 */



function BooleanField({
  field,
  value,
  onChange
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
    __nextHasNoMarginBottom: true,
    label: field.label,
    help: field.description,
    checked: Boolean(value),
    onChange: onChange
  });
}
function SelectField({
  field,
  value,
  onChange
}) {
  const options = Array.isArray(field.options) ? field.options : [];
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    label: field.label,
    help: field.description,
    value: value === null || value === undefined ? '' : String(value),
    options: options.map(opt => ({
      value: opt.value,
      label: opt.label
    })),
    onChange: onChange
  });
}
function RadioField({
  field,
  value,
  onChange
}) {
  const options = Array.isArray(field.options) ? field.options : [];
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.RadioControl, {
    label: field.label,
    help: field.description,
    selected: value === null || value === undefined ? '' : String(value),
    options: options.map(opt => ({
      value: opt.value,
      label: opt.label
    })),
    onChange: onChange
  });
}
function TextField({
  field,
  value,
  onChange
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    label: field.label,
    help: field.description,
    value: value === null || value === undefined ? '' : String(value),
    onChange: onChange,
    pattern: field.pattern,
    maxLength: field.maxLength
  });
}
function NumberField({
  field,
  value,
  onChange
}) {
  const hasRange = Number.isFinite(field.min) || Number.isFinite(field.max);
  if (hasRange) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.RangeControl, {
      __nextHasNoMarginBottom: true,
      __next40pxDefaultSize: true,
      label: field.label,
      help: field.description,
      value: Number(value) || 0,
      min: Number.isFinite(field.min) ? field.min : undefined,
      max: Number.isFinite(field.max) ? field.max : undefined,
      step: Number.isFinite(field.step) ? field.step : 1,
      onChange: onChange
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true,
    type: "number",
    label: field.label,
    help: field.description,
    value: value === null || value === undefined ? '' : String(value),
    step: Number.isFinite(field.step) ? field.step : undefined,
    onChange: next => onChange(next === '' ? null : Number(next))
  });
}
const BASE_FIELD_TYPES = {
  boolean: BooleanField,
  select: SelectField,
  radio: RadioField,
  text: TextField,
  number: NumberField
};
/* harmony default export */ var fieldTypes = ((/* unused pure expression or super */ null && (BASE_FIELD_TYPES)));
;// ./node_modules/@pressmaximum/dashboard-kit/src/layouts/SubNav/editor.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/layouts/SubNav/index.jsx
/**
 * SubNav — Tier-1 vertical nav rail (SPEC §5.3). Two consumer
 * patterns documented in the spec:
 *
 *   1. Settings-style — intra-tab panel switch. Clicking a panel
 *      navigates within the same top-level tab (e.g.
 *      `#settings/:panelId`); the cross-tab dirty-state guard does NOT
 *      fire (consistent with the Settings P7.5 pattern).
 *
 *   2. Multi-source style — Changelog with Pro plugin sources. Hide
 *      the SubNav when fewer than two sources are registered (single
 *      source → render plain content with no rail).
 *
 * The kit handles the "fewer than 2 items" case by returning `null`,
 * matching pattern 2's degrade rule. Consumers that want to force the
 * rail visible at 1 item can render their own wrapper.
 *
 * Slot shape (SPEC §5.3):
 *
 *   <SubNav
 *     items={ [ { id, label, hash } ] }
 *     activeId={ string }
 *     ariaLabel={ string }                // already-translated
 *     onSelect={ ({ id, hash, event }) => void }  // optional
 *   />
 *
 * Default click behavior calls `navigate( hash )` directly — INTRA-tab
 * nav bypasses the `NavigationGuardProvider` (dirty-state guard) per
 * SPEC §5.3 pattern 1. Consumers needing the guard wire `onSelect`
 * themselves and call `useNavigate()(hash)(event)` to reuse the kit's
 * guarded path.
 */




function SubNav({
  items,
  activeId,
  ariaLabel,
  onSelect,
  className
}) {
  if (!Array.isArray(items) || items.length < 2) {
    return null;
  }
  const classes = 'pmdk-subnav' + (className ? ' ' + className : '');
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("nav", {
    className: classes,
    "aria-label": ariaLabel,
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "pmdk-subnav__list",
      children: items.map(item => {
        const isActive = item.id === activeId;
        const itemClass = 'pmdk-subnav__item' + (isActive ? ' is-active' : '');
        const handleClick = event => {
          event.preventDefault();
          if (isActive) {
            return;
          }
          if (typeof onSelect === 'function') {
            onSelect({
              id: item.id,
              hash: item.hash,
              event
            });
            return;
          }
          HashRouter_navigate(item.hash);
        };
        return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("li", {
          children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("a", {
            href: item.hash,
            className: itemClass,
            "aria-current": isActive ? 'page' : undefined,
            onClick: handleClick,
            children: item.label
          })
        }, item.id);
      })
    })
  });
}
;// external ["wp","apiFetch"]
var external_wp_apiFetch_namespaceObject = window["wp"]["apiFetch"];
var external_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_wp_apiFetch_namespaceObject);
;// ./node_modules/@pressmaximum/dashboard-kit/src/settings/createSettingsStore.js
/**
 * createSettingsStore — `@wordpress/data` store factory for schema-driven
 * settings forms. SPEC §5.4.
 *
 * State shape (locked at 0.1.0):
 *
 *   {
 *     saved: Record<string, unknown> | null,  // last server-confirmed values
 *     dirty: Record<string, unknown>,         // local-only edits, deep-merged
 *                                             //  over `saved` by getSettings
 *     loading: boolean,                       // GET in flight
 *     saving:  boolean,                       // POST in flight (save or reset)
 *     error:   unknown | null,                // last load/save/reset error
 *   }
 *
 * Action sequence (also verified by tests):
 *
 *   load()  → START_LOAD  → LOAD_SUCCESS|LOAD_ERROR
 *   edit()  → EDIT                    (mutates dirty buffer)
 *   save()  → START_SAVE → SAVE_SUCCESS|SAVE_ERROR  (clears dirty on success)
 *   reset() → START_SAVE → SAVE_SUCCESS|SAVE_ERROR  (POSTs body={}, clears dirty on success)
 *   clearDirty() → CLEAR_DIRTY        (used by useDirtyState onDiscard)
 *
 * Why an injected `fetch` callable instead of `import @wordpress/api-fetch`?
 * SPEC §3.3 forbids the kit from importing `@wordpress/api-fetch` — the
 * consumer wires its own REST client (typically a thin wrapper around
 * `apiFetch` with nonce + namespace handling) and hands it to the kit.
 * Keeps the kit free of WP-specific REST plumbing and lets consumers
 * point the store at any URL/transport.
 *
 * Why an optional `seedSaved`?
 * First-mount renders run synchronously when the consumer's PHP shipped
 * the settings inside the boot payload — no spinner flash on cold visit
 * to the Settings tab. Falls back to `null` and the consumer dispatches
 * `load()` to fill.
 *
 * @example
 *   import { createSettingsStore } from '@pressmaximum/dashboard-kit';
 *   import { register } from '@wordpress/data';
 *   import apiFetch from '@wordpress/api-fetch';
 *
 *   const { STORE_NAME, store } = createSettingsStore({
 *       storeName: 'customify/settings',
 *       endpoint: '/customify/v1/settings',
 *       fetch: ({ path, method, data }) => apiFetch({ path, method, data }),
 *       seedSaved: boot.settings,
 *   });
 *   register(store);
 */


const TYPES = {
  START_LOAD: 'START_LOAD',
  LOAD_SUCCESS: 'LOAD_SUCCESS',
  LOAD_ERROR: 'LOAD_ERROR',
  EDIT: 'EDIT',
  START_SAVE: 'START_SAVE',
  SAVE_SUCCESS: 'SAVE_SUCCESS',
  SAVE_ERROR: 'SAVE_ERROR',
  CLEAR_DIRTY: 'CLEAR_DIRTY'
};

/**
 * Functional immutable setter — given `{ a: { b: 1 } }` and path `'a.c'`
 * + value `2`, returns `{ a: { b: 1, c: 2 } }`. Used by `edit()` to
 * accumulate edits into the dirty buffer without mutating prior state.
 *
 * @param {Record<string, unknown>} target Source object (never mutated).
 * @param {string}                  path   Dotted path, e.g. `'panel.field'`.
 * @param {unknown}                 value  New value at `path`.
 * @return {Record<string, unknown>} New object with the path set.
 */
function setAtPath(target, path, value) {
  const segments = String(path || '').split('.').filter(Boolean);
  if (segments.length === 0) {
    return target;
  }
  const [head, ...rest] = segments;
  if (rest.length === 0) {
    return {
      ...target,
      [head]: value
    };
  }
  const child = target && typeof target[head] === 'object' ? target[head] : {};
  const nested = setAtPath(child, rest.join('.'), value);
  return {
    ...target,
    [head]: nested
  };
}

/**
 * Deep-merge two plain objects — overlay wins per-key. Arrays are
 * replaced wholesale (not concatenated) because a partial save would
 * otherwise grow arrays unboundedly across reloads. Used by
 * `getSettings()` to project the dirty buffer over the saved snapshot.
 *
 * @param {Record<string, unknown> | null} base    Underlying object.
 * @param {Record<string, unknown> | null} overlay Object whose keys win.
 * @return {Record<string, unknown>} Merged result (always a new object).
 */
function deepMerge(base, overlay) {
  if (!overlay || typeof overlay !== 'object') {
    return base;
  }
  const out = Array.isArray(base) ? [...base] : {
    ...(base || {})
  };
  for (const key of Object.keys(overlay)) {
    const value = overlay[key];
    if (value && typeof value === 'object' && !Array.isArray(value) && out[key] && typeof out[key] === 'object') {
      out[key] = deepMerge(out[key], value);
    } else {
      out[key] = value;
    }
  }
  return out;
}

/**
 * @param {Object}   config
 * @param {string}   config.storeName   wp.data store key, e.g. `'customify/settings'`.
 * @param {string}   config.endpoint    Path passed verbatim to `fetch({ path, ... })`.
 * @param {Function} config.fetch       `({ path, method?, data? }) => Promise<unknown>`.
 *                                      Consumer-owned REST client (forbidden imports
 *                                      in the kit per SPEC §3.3).
 * @param {Object}   [config.seedSaved] Initial `saved` value so first-mount
 *                                      render is synchronous. Defaults to `null`.
 * @return {{ STORE_NAME: string, store: import('@wordpress/data').StoreDescriptor }}
 *         Store descriptor + the resolved store name, ready to `register()`.
 */
function createSettingsStore({
  storeName,
  endpoint,
  fetch,
  seedSaved = null
} = {}) {
  if (!storeName) {
    throw new TypeError('createSettingsStore: `storeName` is required.');
  }
  if (!endpoint) {
    throw new TypeError('createSettingsStore: `endpoint` is required.');
  }
  if (typeof fetch !== 'function') {
    throw new TypeError('createSettingsStore: `fetch` callable is required (SPEC §3.3 — kit cannot import @wordpress/api-fetch).');
  }
  const DEFAULT_STATE = {
    saved: seedSaved && typeof seedSaved === 'object' ? seedSaved : null,
    dirty: {},
    loading: false,
    saving: false,
    error: null
  };
  function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
      case TYPES.START_LOAD:
        return {
          ...state,
          loading: true,
          error: null
        };
      case TYPES.LOAD_SUCCESS:
        return {
          ...state,
          loading: false,
          saved: action.payload,
          error: null
        };
      case TYPES.LOAD_ERROR:
        return {
          ...state,
          loading: false,
          error: action.error
        };
      case TYPES.EDIT:
        return {
          ...state,
          dirty: setAtPath(state.dirty, action.path, action.value)
        };
      case TYPES.START_SAVE:
        return {
          ...state,
          saving: true,
          error: null
        };
      case TYPES.SAVE_SUCCESS:
        return {
          ...state,
          saving: false,
          saved: action.payload,
          dirty: {},
          error: null
        };
      case TYPES.SAVE_ERROR:
        return {
          ...state,
          saving: false,
          error: action.error
        };
      case TYPES.CLEAR_DIRTY:
        return {
          ...state,
          dirty: {}
        };
      default:
        return state;
    }
  }
  const selectors = {
    /**
     * Merged view: saved snapshot + dirty edits projected on top.
     *
     * @param {Object} state Reducer state.
     */
    getSettings(state) {
      return deepMerge(state.saved || {}, state.dirty);
    },
    /**
     * Last server-confirmed values — what a Reset would restore to.
     *
     * @param {Object} state Reducer state.
     */
    getSavedSettings(state) {
      return state.saved;
    },
    /**
     * Local-only edit buffer. Empty after a successful save / reset.
     *
     * @param {Object} state Reducer state.
     */
    getDirty(state) {
      return state.dirty;
    },
    isDirty(state) {
      return Object.keys(state.dirty).length > 0;
    },
    isLoading(state) {
      return state.loading;
    },
    isSaving(state) {
      return state.saving;
    },
    getError(state) {
      return state.error;
    }
  };
  const actions = {
    /**
     * GET the endpoint, populate `saved`. Idempotent — early-returns
     * the cached `saved` when one exists and there's no prior error,
     * so tab remounts don't burn a round-trip on every navigation.
     */
    load() {
      return async ({
        dispatch,
        select
      }) => {
        const saved = select.getSavedSettings();
        if (saved !== null && !select.getError()) {
          return saved;
        }
        dispatch({
          type: TYPES.START_LOAD
        });
        try {
          const data = await fetch({
            path: endpoint
          });
          dispatch({
            type: TYPES.LOAD_SUCCESS,
            payload: data
          });
          return data;
        } catch (error) {
          dispatch({
            type: TYPES.LOAD_ERROR,
            error
          });
          throw error;
        }
      };
    },
    /**
     * Stage an edit into the dirty buffer. Path uses dotted notation
     * (`'panelId.fieldId'` or `'panelId.field.nested'`). The reducer
     * deep-merges into the existing buffer so accumulating edits
     * across panels works without consumer juggling.
     *
     * @param {string}  path  Dotted path to the field.
     * @param {unknown} value Next value at the field.
     */
    edit(path, value) {
      return {
        type: TYPES.EDIT,
        path,
        value
      };
    },
    /**
     * POST the merged settings. On success the server response
     * replaces `saved` wholesale + clears the dirty buffer.
     */
    save() {
      return async ({
        dispatch,
        select
      }) => {
        const merged = select.getSettings();
        dispatch({
          type: TYPES.START_SAVE
        });
        try {
          const data = await fetch({
            path: endpoint,
            method: 'POST',
            data: merged
          });
          dispatch({
            type: TYPES.SAVE_SUCCESS,
            payload: data
          });
          return data;
        } catch (error) {
          dispatch({
            type: TYPES.SAVE_ERROR,
            error
          });
          throw error;
        }
      };
    },
    /**
     * POST an empty body — the server-side `SettingsControllerBase`
     * contract (SPEC §5.10) interprets this as "reset to defaults"
     * and replies with the defaults snapshot, which becomes the new
     * `saved`. Dirty buffer clears on success.
     */
    reset() {
      return async ({
        dispatch
      }) => {
        dispatch({
          type: TYPES.START_SAVE
        });
        try {
          const data = await fetch({
            path: endpoint,
            method: 'POST',
            data: {}
          });
          dispatch({
            type: TYPES.SAVE_SUCCESS,
            payload: data
          });
          return data;
        } catch (error) {
          dispatch({
            type: TYPES.SAVE_ERROR,
            error
          });
          throw error;
        }
      };
    },
    /**
     * Discard the dirty buffer without touching `saved`. Wired into
     * `useDirtyState.onDiscard` so accepting the nav-away confirm
     * clears the buffer instead of letting a future remount restore
     * the discarded edits.
     */
    clearDirty() {
      return {
        type: TYPES.CLEAR_DIRTY
      };
    }
  };
  const store = (0,external_wp_data_namespaceObject.createReduxStore)(storeName, {
    reducer,
    actions,
    selectors
  });
  return {
    STORE_NAME: storeName,
    store
  };
}
/* harmony default export */ var settings_createSettingsStore = ((/* unused pure expression or super */ null && (createSettingsStore)));
;// ./src/backend/admin/dashboard-v2/data/settingsStore.js



const boot = window.customifyDashboard || {};
const restRoot = boot?.rest?.root || '';
const nonce = boot?.rest?.nonce;

// Wire the wp.apiFetch middleware so REST writes carry the nonce. The
// API-fetch middleware is global to wp.data; setting it once on boot
// covers every call the kit's store makes.
if (restRoot) {
  external_wp_apiFetch_default().use(external_wp_apiFetch_default().createRootURLMiddleware(restRoot));
}
if (nonce) {
  external_wp_apiFetch_default().use(external_wp_apiFetch_default().createNonceMiddleware(nonce));
}
const seedSaved = boot?.settings?.values || {};
const {
  STORE_NAME,
  store
} = createSettingsStore({
  storeName: 'customify/dashboard-settings',
  endpoint: '/customify/v1/settings',
  fetch: args => external_wp_apiFetch_default()(args),
  seedSaved
});
(0,external_wp_data_namespaceObject.register)(store);
const CUSTOMIFY_SETTINGS_STORE = STORE_NAME;
;// ./src/backend/admin/dashboard-v2/sections/ProModuleSettingsPanel.jsx
/**
 * Generic Pro-module settings panel.
 *
 * Customify Pro registers per-module panels via the
 * `customify.dashboard.settings.panels` JS filter with a
 * `proPanel: true` flag + their own `fields`, `endpoint`,
 * `seedValues`, and (optionally) `nonce`. The new dashboard's
 * Settings tab detects the flag and routes those panels through
 * this component instead of the standard kit pipeline so the
 * panel can read / write Pro's existing per-module storage
 * (`customify_modules[ClassName]`) without going through the
 * theme's `/customify/v1/settings` endpoint.
 *
 * Owns its own form state + Save button; the global SaveBar at
 * the bottom of the Settings tab only reflects theme-side panels.
 */








const ProModuleSettingsPanel_NOTICES_STORE = 'core/notices';
const ProModuleSettingsPanel_SUCCESS_GLYPH = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
  className: "customify-dashboard-snackbar__check",
  children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
    icon: check_default,
    size: 14
  })
});

/**
 * One section-level action button (e.g. "Regenerate assets"). Each
 * descriptor in `panel.actions[]` ships its own endpoint + labels; the
 * button owns its own busy state so multiple actions in the same panel
 * stay independent. POSTs use `{}` as the body so Pro REST handlers
 * that read `get_json_params()` always see an array.
 */
function PanelActionButton({
  action,
  nonce
}) {
  const [busy, setBusy] = (0,external_wp_element_namespaceObject.useState)(false);
  const handleClick = async () => {
    if (busy) {
      return;
    }
    setBusy(true);
    const method = (action.method || 'POST').toUpperCase();
    const headers = method === 'POST' ? {
      'Content-Type': 'application/json'
    } : {};
    if (nonce) {
      headers['X-WP-Nonce'] = nonce;
    }
    try {
      const res = await fetch(action.endpoint, {
        method,
        credentials: 'same-origin',
        headers,
        body: method === 'POST' ? '{}' : undefined
      });
      if (!res.ok) {
        let message = `HTTP ${res.status}`;
        try {
          const data = await res.json();
          if (data?.message) {
            message = data.message;
          }
        } catch (_) {
          // Non-JSON error body; fall through with HTTP status.
        }
        throw new Error(message);
      }
      (0,external_wp_data_namespaceObject.dispatch)(ProModuleSettingsPanel_NOTICES_STORE).createSuccessNotice(action.successMessage || (0,external_wp_i18n_namespaceObject.__)('Done.', 'customify'), {
        type: 'snackbar',
        isDismissible: true,
        icon: ProModuleSettingsPanel_SUCCESS_GLYPH
      });
    } catch (err) {
      (0,external_wp_data_namespaceObject.dispatch)(ProModuleSettingsPanel_NOTICES_STORE).createErrorNotice(err?.message || (0,external_wp_i18n_namespaceObject.__)('Action failed. Try again.', 'customify'), {
        type: 'snackbar',
        isDismissible: true
      });
    } finally {
      setBusy(false);
    }
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
    variant: action.variant === 'primary' ? 'primary' : 'secondary',
    onClick: handleClick,
    disabled: busy,
    children: busy ? action.busyLabel || (0,external_wp_i18n_namespaceObject.__)('Working…', 'customify') : action.label
  });
}

/**
 * @param {object} props
 * @param {object} props.panel    Panel descriptor — { id, label, description?,
 *                                fields[], endpoint, seedValues?, nonce? }.
 * @param {boolean} [props.scrollIntoView] When true the card scrolls itself
 *                                into view on mount (used when the route
 *                                deep-links to this panel via
 *                                #settings/<panelId>).
 */
function ProModuleSettingsPanel({
  panel,
  scrollIntoView
}) {
  const initialValues = (0,external_wp_element_namespaceObject.useMemo)(() => panel?.seedValues ? {
    ...panel.seedValues
  } : {}, [panel]);
  const [values, setValues] = (0,external_wp_element_namespaceObject.useState)(initialValues);
  const [savedValues, setSavedValues] = (0,external_wp_element_namespaceObject.useState)(initialValues);
  const [saving, setSaving] = (0,external_wp_element_namespaceObject.useState)(false);
  const [error, setError] = (0,external_wp_element_namespaceObject.useState)(null);
  const cardRef = (0,external_wp_element_namespaceObject.useRef)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (scrollIntoView && cardRef.current) {
      cardRef.current.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  }, [scrollIntoView]);
  const isDirty = (0,external_wp_element_namespaceObject.useMemo)(() => JSON.stringify(values) !== JSON.stringify(savedValues), [values, savedValues]);
  const handleChange = (fieldId, next) => {
    setValues(prev => ({
      ...prev,
      [fieldId]: next
    }));
  };
  const handleSave = async () => {
    if (!panel?.endpoint || saving) {
      return;
    }
    setSaving(true);
    setError(null);
    try {
      const headers = {
        'Content-Type': 'application/json'
      };
      if (panel.nonce) {
        headers['X-WP-Nonce'] = panel.nonce;
      }
      const res = await fetch(panel.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify(values)
      });
      if (!res.ok) {
        throw new Error('HTTP ' + res.status);
      }
      const data = await res.json();
      const nextValues = data && typeof data === 'object' && data.values ? data.values : values;
      setSavedValues(nextValues);
      setValues(nextValues);
      (0,external_wp_data_namespaceObject.dispatch)(ProModuleSettingsPanel_NOTICES_STORE).createSuccessNotice((0,external_wp_i18n_namespaceObject.__)('Settings saved.', 'customify'), {
        type: 'snackbar',
        isDismissible: true,
        icon: ProModuleSettingsPanel_SUCCESS_GLYPH
      });
    } catch (err) {
      setError(err);
      (0,external_wp_data_namespaceObject.dispatch)(ProModuleSettingsPanel_NOTICES_STORE).createErrorNotice(err?.message || (0,external_wp_i18n_namespaceObject.__)('Saving settings failed. Try again.', 'customify'), {
        type: 'snackbar',
        isDismissible: true
      });
    } finally {
      setSaving(false);
    }
  };
  const handleDiscard = () => {
    // The kit's SaveBar disables this action via
    // `resetDisabledWhenNotDirty` when the form is clean, so a click
    // arriving here always has something to throw away.
    setValues(savedValues);
    setError(null);
    (0,external_wp_data_namespaceObject.dispatch)(ProModuleSettingsPanel_NOTICES_STORE).createSuccessNotice((0,external_wp_i18n_namespaceObject.__)('Changes discarded.', 'customify'), {
      type: 'snackbar',
      isDismissible: true,
      icon: ProModuleSettingsPanel_SUCCESS_GLYPH
    });
  };
  if (!panel) {
    return null;
  }
  const headingId = panelHeadingId(panel.id);
  const fields = Array.isArray(panel.fields) ? panel.fields : [];
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Card, {
      ref: cardRef,
      className: "customify-dashboard-settings__panel customify-dashboard-settings__pro-panel",
      "data-panel-id": panel.id,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.CardHeader, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
          id: headingId,
          children: panel.label
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.CardBody, {
        children: [panel.description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
          className: "customify-dashboard-settings__description",
          children: panel.description
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "pmdk-schema-form",
          role: "group",
          "aria-labelledby": headingId,
          children: fields.map(field => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SchemaField, {
            field: field,
            value: values[field.id],
            onChange: next => handleChange(field.id, next),
            fieldTypes: BASE_FIELD_TYPES
          }, field.id))
        }), error && error.message && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Notice, {
          status: "error",
          isDismissible: false,
          className: "customify-dashboard-settings__pro-panel-error",
          children: error.message
        }), Array.isArray(panel.actions) && panel.actions.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
          className: "customify-dashboard-settings__panel-actions-inline",
          children: panel.actions.map(action => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(PanelActionButton, {
            action: action,
            nonce: panel.nonce
          }, action.id))
        })]
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-settings__panel-actions",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SaveBar, {
        isDirty: isDirty,
        isSaving: saving,
        onSave: handleSave,
        onReset: handleDiscard
        // Pro module storage uses revert-to-last-saved semantics
        // (no factory-defaults endpoint), so Discard should only
        // fire when there's something dirty to throw away.
        ,
        resetDisabledWhenNotDirty: true,
        labels: {
          regionLabel: (0,external_wp_i18n_namespaceObject.__)('Settings actions', 'customify'),
          saveLabel: (0,external_wp_i18n_namespaceObject.__)('Save changes', 'customify'),
          savingLabel: (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify'),
          // Pro module storage doesn't expose a factory-defaults
          // endpoint today; "Reset" here reverts the form to the
          // last server-confirmed snapshot. Label accordingly so
          // users don't expect a real wipe.
          resetLabel: (0,external_wp_i18n_namespaceObject.__)('Discard changes', 'customify'),
          // Mirror the kit's neutral default through the
          // `customify` text domain so the string lands in the
          // theme POT for translation.
          statusSaved: (0,external_wp_i18n_namespaceObject.__)('No pending changes', 'customify'),
          statusDirty: (0,external_wp_i18n_namespaceObject.__)('Unsaved changes', 'customify'),
          statusSaving: (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify')
        }
      })
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/sections/LicensePanel.jsx
/**
 * License panel for the new dashboard's Settings tab. Specialised
 * renderer for the Customify Pro "Automatic updates" panel — the form
 * runs independently of the page-level SaveBar (no Save button), with
 * dedicated Activate / Deactivate actions that round-trip through
 * Pro's EDD updater via the panel.endpoints map.
 *
 * Panel shape consumed:
 *   {
 *     id, kind: 'license', label, description, nonce,
 *     endpoints: { status, activate, deactivate },
 *     seedValues: { key, status, expires?, customerName?, errorCode? },
 *   }
 *
 * Settings.jsx detects panel.kind === 'license' and renders this
 * component in place of ProModuleSettingsPanel.
 */








const LicensePanel_NOTICES_STORE = 'core/notices';
const LicensePanel_SUCCESS_GLYPH = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
  className: "customify-dashboard-snackbar__check",
  children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
    icon: check_default,
    size: 14
  })
});

/**
 * REST error messages from WP fatal-error catchers arrive as HTML
 * blobs ("<p>There has been a critical error…</p><p><a …>Learn more…
 * </a></p>"). The notice + snackbar render text content literally, so
 * the raw markup ends up in the user's face. Strip tags + decode
 * entities so the human-readable bit comes through (and trim to keep
 * the snackbar tidy).
 */
function readableErrorMessage(raw, fallback) {
  if (!raw || typeof raw !== 'string') {
    return fallback;
  }
  const stripped = raw.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  if (!stripped) {
    return fallback;
  }
  const txt = document.createElement('textarea');
  txt.innerHTML = stripped;
  const decoded = txt.value.trim();
  return decoded.length > 240 ? decoded.slice(0, 237) + '…' : decoded;
}

/**
 * Map EDD status strings → tone for the status pill.
 *
 * EDD returns: 'valid', 'invalid', 'expired', 'inactive',
 * 'disabled', 'site_inactive', 'item_name_mismatch', 'no_activations_left',
 * 'revoked'. We collapse the long tail into 4 visible tones.
 */
function statusTone(status) {
  if ('valid' === status) {
    return 'active';
  }
  if ('expired' === status) {
    return 'expired';
  }
  if (!status || 'inactive' === status || 'site_inactive' === status) {
    return 'inactive';
  }
  return 'error';
}
function statusLabel(status) {
  switch (status) {
    case 'valid':
      return (0,external_wp_i18n_namespaceObject.__)('Active', 'customify');
    case 'expired':
      return (0,external_wp_i18n_namespaceObject.__)('Expired', 'customify');
    case 'invalid':
      return (0,external_wp_i18n_namespaceObject.__)('Invalid', 'customify');
    case 'inactive':
    case '':
      return (0,external_wp_i18n_namespaceObject.__)('Inactive', 'customify');
    case 'site_inactive':
      return (0,external_wp_i18n_namespaceObject.__)('Not active on this site', 'customify');
    case 'no_activations_left':
      return (0,external_wp_i18n_namespaceObject.__)('No activations left', 'customify');
    case 'disabled':
    case 'revoked':
      return (0,external_wp_i18n_namespaceObject.__)('Disabled', 'customify');
    case 'item_name_mismatch':
      return (0,external_wp_i18n_namespaceObject.__)('Wrong product key', 'customify');
    default:
      return status || (0,external_wp_i18n_namespaceObject.__)('Unknown', 'customify');
  }
}
function LicensePanel({
  panel
}) {
  const initial = panel?.seedValues || {};
  const [key, setKey] = (0,external_wp_element_namespaceObject.useState)(initial.key || '');
  const [snapshot, setSnapshot] = (0,external_wp_element_namespaceObject.useState)(initial);
  const [busy, setBusy] = (0,external_wp_element_namespaceObject.useState)(false);
  const [error, setError] = (0,external_wp_element_namespaceObject.useState)(null);
  const isActive = 'valid' === snapshot?.status;
  const headingId = panelHeadingId(panel?.id || 'license');
  const headers = method => {
    const out = method ? {
      'Content-Type': 'application/json'
    } : {};
    if (panel?.nonce) {
      out['X-WP-Nonce'] = panel.nonce;
    }
    return out;
  };
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    setKey(initial.key || '');
    setSnapshot(initial);
  }, [panel?.id]);
  const handleActivate = async () => {
    const trimmed = (key || '').trim();
    if (!trimmed) {
      setError(new Error((0,external_wp_i18n_namespaceObject.__)('Please enter your license key.', 'customify')));
      return;
    }
    setBusy(true);
    setError(null);
    const fallback = (0,external_wp_i18n_namespaceObject.__)('License activation failed. Try again.', 'customify');
    try {
      const res = await fetch(panel.endpoints.activate, {
        method: 'POST',
        credentials: 'same-origin',
        headers: headers('POST'),
        body: JSON.stringify({
          key: trimmed
        })
      });
      let data = null;
      try {
        data = await res.json();
      } catch (_) {
        // Non-JSON body (PHP fatal HTML, gateway timeout, etc.).
      }
      if (!res.ok) {
        throw new Error(readableErrorMessage(data?.message, `${fallback} (HTTP ${res.status})`));
      }
      const next = data?.license || {};
      setSnapshot(next);
      setKey(next.key || trimmed);
      (0,external_wp_data_namespaceObject.dispatch)(LicensePanel_NOTICES_STORE).createSuccessNotice('valid' === next.status ? (0,external_wp_i18n_namespaceObject.__)('License activated.', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Activation returned a non-active status — check the badge below.', 'customify'), {
        type: 'snackbar',
        isDismissible: true,
        icon: 'valid' === next.status ? LicensePanel_SUCCESS_GLYPH : undefined
      });
    } catch (err) {
      const message = readableErrorMessage(err?.message, fallback);
      setError(new Error(message));
      (0,external_wp_data_namespaceObject.dispatch)(LicensePanel_NOTICES_STORE).createErrorNotice(message, {
        type: 'snackbar',
        isDismissible: true
      });
    } finally {
      setBusy(false);
    }
  };
  const handleDeactivate = async () => {
    setBusy(true);
    setError(null);
    const fallback = (0,external_wp_i18n_namespaceObject.__)('Deactivation failed. Try again.', 'customify');
    try {
      const res = await fetch(panel.endpoints.deactivate, {
        method: 'POST',
        credentials: 'same-origin',
        headers: headers('POST'),
        body: '{}'
      });
      let data = null;
      try {
        data = await res.json();
      } catch (_) {
        // Non-JSON body (PHP fatal HTML, gateway timeout, etc.).
      }
      if (!res.ok) {
        throw new Error(readableErrorMessage(data?.message, `${fallback} (HTTP ${res.status})`));
      }
      const next = data?.license || {};
      setSnapshot(next);
      setKey('');
      (0,external_wp_data_namespaceObject.dispatch)(LicensePanel_NOTICES_STORE).createSuccessNotice((0,external_wp_i18n_namespaceObject.__)('License deactivated.', 'customify'), {
        type: 'snackbar',
        isDismissible: true,
        icon: LicensePanel_SUCCESS_GLYPH
      });
    } catch (err) {
      const message = readableErrorMessage(err?.message, fallback);
      setError(new Error(message));
      (0,external_wp_data_namespaceObject.dispatch)(LicensePanel_NOTICES_STORE).createErrorNotice(message, {
        type: 'snackbar',
        isDismissible: true
      });
    } finally {
      setBusy(false);
    }
  };
  const tone = statusTone(snapshot.status);
  const label = statusLabel(snapshot.status);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Card, {
    className: "customify-dashboard-settings__panel customify-dashboard-license",
    "data-panel-id": panel.id,
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.CardHeader, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
        id: headingId,
        children: panel.label
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        className: `customify-dashboard-license__status customify-dashboard-license__status--${tone}`,
        children: label
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.CardBody, {
      children: [panel.description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "customify-dashboard-settings__description",
        children: panel.description
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-dashboard-license__form",
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TextControl, {
          __nextHasNoMarginBottom: true,
          __next40pxDefaultSize: true,
          label: (0,external_wp_i18n_namespaceObject.__)('License key', 'customify'),
          value: key,
          onChange: next => setKey(next),
          disabled: busy || isActive,
          placeholder: (0,external_wp_i18n_namespaceObject.__)('Enter your license key', 'customify')
        })
      }), snapshot.expires && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("p", {
        className: "customify-dashboard-license__meta",
        children: [(0,external_wp_i18n_namespaceObject.__)('Expires:', 'customify'), " ", /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {
          children: snapshot.expires
        })]
      }), snapshot.customerName && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("p", {
        className: "customify-dashboard-license__meta",
        children: [(0,external_wp_i18n_namespaceObject.__)('Customer:', 'customify'), " ", /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {
          children: snapshot.customerName
        })]
      }), error && error.message && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Notice, {
        status: "error",
        isDismissible: false,
        className: "customify-dashboard-license__error",
        children: error.message
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-dashboard-license__actions",
        children: isActive ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
          variant: "secondary",
          onClick: handleDeactivate,
          disabled: busy,
          children: busy ? (0,external_wp_i18n_namespaceObject.__)('Deactivating…', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Deactivate', 'customify')
        }) : /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
          variant: "primary",
          onClick: handleActivate,
          disabled: busy || !key.trim(),
          children: busy ? (0,external_wp_i18n_namespaceObject.__)('Activating…', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Activate', 'customify')
        })
      })]
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/tabs/Settings.jsx
/**
 * Settings tab — SubNav layout. Each panel renders as its own sub-tab
 * (General = theme settings, then one tab per Pro module that
 * registers via `customify.dashboard.settings.panels`).
 *
 * Per-panel save:
 *   - Theme panels (proPanel !== true) share the theme settings store;
 *     the kit's SaveBar is rendered inside the active panel's CardBody
 *     so the page never shows two save clusters at once.
 *   - Pro panels are self-contained (own state + REST endpoint + Save /
 *     Discard cluster baked into ProModuleSettingsPanel).
 *
 * Single-panel case: SubNav hides; the lone panel renders full-width.
 */












const Settings_NOTICES_STORE = 'core/notices';
const Settings_SUCCESS_GLYPH = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
  className: "customify-dashboard-snackbar__check",
  children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Icon, {
    icon: check_default,
    size: 14
  })
});

/**
 * Render one theme-side schema panel + its own SaveBar in the body.
 * Theme panels share the dashboard-v2 settings store so they all
 * read the same `getSettings()` / dispatch the same `edit()` /
 * `save()` actions; only the visible panel's card frames the bar.
 */
function ThemePanelCard({
  panel,
  values,
  fieldTypes,
  isDirty,
  isSaving,
  edit,
  onSave,
  onReset
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Card, {
      className: "customify-dashboard-settings__panel",
      "data-panel-id": panel.id,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.CardHeader, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("h2", {
          id: panelHeadingId(panel.id),
          children: panel.label
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.CardBody, {
        children: [panel.description && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
          className: "customify-dashboard-settings__description",
          children: panel.description
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SchemaForm, {
          panel: panel,
          values: values || {},
          onFieldChange: (panelId, fieldId, next) => edit(`${panelId}.${fieldId}`, next),
          fieldTypes: fieldTypes
        })]
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-settings__panel-actions",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SaveBar, {
        isDirty: isDirty,
        isSaving: isSaving,
        onSave: onSave,
        onReset: onReset,
        labels: {
          regionLabel: (0,external_wp_i18n_namespaceObject.__)('Settings actions', 'customify'),
          saveLabel: (0,external_wp_i18n_namespaceObject.__)('Save changes', 'customify'),
          savingLabel: (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify'),
          resetLabel: (0,external_wp_i18n_namespaceObject.__)('Reset to defaults', 'customify'),
          // Mirror the kit's neutral default ("No pending
          // changes") through the `customify` text domain so
          // the string lands in the theme POT for translation.
          statusSaved: (0,external_wp_i18n_namespaceObject.__)('No pending changes', 'customify'),
          statusDirty: (0,external_wp_i18n_namespaceObject.__)('Unsaved changes', 'customify'),
          statusSaving: (0,external_wp_i18n_namespaceObject.__)('Saving…', 'customify')
        }
      })
    })]
  });
}
function Settings({
  params
}) {
  const boot = useBoot();
  const schema = boot?.settings?.schema || {
    panels: []
  };
  const values = (0,external_wp_data_namespaceObject.useSelect)(select => select(CUSTOMIFY_SETTINGS_STORE).getSettings(), []);
  const isDirty = (0,external_wp_data_namespaceObject.useSelect)(select => select(CUSTOMIFY_SETTINGS_STORE).isDirty(), []);
  const isSaving = (0,external_wp_data_namespaceObject.useSelect)(select => select(CUSTOMIFY_SETTINGS_STORE).isSaving(), []);
  const {
    edit,
    save,
    reset
  } = (0,external_wp_data_namespaceObject.useDispatch)(CUSTOMIFY_SETTINGS_STORE);
  const fieldTypes = (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.settings.field-types', BASE_FIELD_TYPES);

  // NB: do NOT useMemo here — the panels filter list is mutated by
  // Pro / child theme bundles that load *after* this script (theme
  // bundle calls mountDashboard synchronously; Pro's bridge JS
  // registers its filters once its own <script> executes). A memoised
  // applyFilters call captured before Pro registered would never see
  // the appended panels. Recomputing per render is cheap.
  const panels = (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.settings.panels', schema.panels || [], boot);
  const requestedPanelId = params?.panelId || null;
  const activePanel = panels.find(p => p.id === requestedPanelId) || panels[0] || null;

  // Redirect bare `#settings` to the canonical multi-panel path so
  // SubNav has a resolved active row. Skipped when only one panel is
  // registered (Free + Typekit-off case for example).
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!requestedPanelId && panels.length > 1 && activePanel) {
      HashRouter_navigate(`#settings/${activePanel.id}`);
    }
  }, [requestedPanelId, panels, activePanel]);
  const handleSave = async () => {
    try {
      await save();
      (0,external_wp_data_namespaceObject.dispatch)(Settings_NOTICES_STORE).createSuccessNotice((0,external_wp_i18n_namespaceObject.__)('Settings saved.', 'customify'), {
        type: 'snackbar',
        isDismissible: true,
        icon: Settings_SUCCESS_GLYPH
      });
    } catch (err) {
      (0,external_wp_data_namespaceObject.dispatch)(Settings_NOTICES_STORE).createErrorNotice(err?.message || (0,external_wp_i18n_namespaceObject.__)('Saving settings failed. Try again.', 'customify'), {
        type: 'snackbar',
        isDismissible: true
      });
    }
  };
  const handleReset = async () => {
    const confirmed = window.confirm((0,external_wp_i18n_namespaceObject.__)('Reset all settings to their defaults? This cannot be undone.', 'customify'));
    if (!confirmed) {
      return;
    }
    try {
      await reset();
      (0,external_wp_data_namespaceObject.dispatch)(Settings_NOTICES_STORE).createSuccessNotice((0,external_wp_i18n_namespaceObject.__)('Settings reset to defaults.', 'customify'), {
        type: 'snackbar',
        isDismissible: true,
        icon: Settings_SUCCESS_GLYPH
      });
    } catch (err) {
      (0,external_wp_data_namespaceObject.dispatch)(Settings_NOTICES_STORE).createErrorNotice(err?.message || (0,external_wp_i18n_namespaceObject.__)('Reset failed. Try again.', 'customify'), {
        type: 'snackbar',
        isDismissible: true
      });
    }
  };
  if (!panels.length) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Card, {
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.CardBody, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
          children: (0,external_wp_i18n_namespaceObject.__)('No settings registered yet. Pro and child theme add-ons can extend this tab via the customify.dashboard.settings.panels filter.', 'customify')
        })
      })
    });
  }

  // Render a single section (schema / license / future kinds). Used
  // both directly (single-section panels) and by the composite branch
  // (multiple sections stacked in the same panel pane).
  const renderSection = section => {
    if (!section) {
      return null;
    }
    if ('license' === section.kind) {
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LicensePanel, {
        panel: section
      }, section.id);
    }
    // Default: schema-shaped section → ProModuleSettingsPanel.
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ProModuleSettingsPanel, {
      panel: section,
      scrollIntoView: section.id === requestedPanelId
    }, section.id);
  };
  const renderActivePanel = () => {
    if (!activePanel) {
      return null;
    }
    // Composite panel: stack each section vertically inside the
    // same SubNav sub-tab. Sections live independently (each owns
    // its own state + save semantics), the parent just provides
    // the visual grouping.
    if ('composite' === activePanel.kind) {
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
        className: "customify-dashboard-settings__sections",
        children: (activePanel.sections || []).map(renderSection)
      });
    }
    // License panel handles its own activation flow + no SaveBar.
    if ('license' === activePanel.kind) {
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LicensePanel, {
        panel: activePanel
      });
    }
    if (activePanel.proPanel) {
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ProModuleSettingsPanel, {
        panel: activePanel,
        scrollIntoView: activePanel.id === requestedPanelId
      });
    }
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ThemePanelCard, {
      panel: activePanel,
      values: values,
      fieldTypes: fieldTypes,
      isDirty: isDirty,
      isSaving: isSaving,
      edit: edit,
      onSave: handleSave,
      onReset: handleReset
    });
  };
  if (panels.length < 2) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-settings",
      children: renderActivePanel()
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-dashboard-settings customify-dashboard-settings--tabbed",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SubNav, {
      items: panels.map(p => ({
        id: p.id,
        label: p.label,
        hash: `#settings/${p.id}`
      })),
      activeId: activePanel?.id,
      ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Settings panels', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-settings__pane",
      children: renderActivePanel()
    })]
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/changelog/CategoryBadge.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/changelog/CategoryBadge.jsx
/**
 * CategoryBadge — small uppercase pill rendered next to each changelog
 * item. SPEC §5.3b. Tier-2 page component.
 *
 * Maps a lowercase `category` string (typically from a PHP changelog
 * parser) to:
 *   - a display label via the kit's English fallback table + consumer's
 *     `labels` override
 *   - a tone modifier class via the kit's category→tone map + optional
 *     `toneOverrides`
 *
 * CategoryBadge.css owns the color palette per tone — imported here so
 * consumers using the badge standalone (without ReleaseBlock) still get
 * styling. Tones: `new` / `improved` / `fixed` / `updated` / `removed`
 * / `security` / `deprecated` / `neutral`.
 *
 * Unknown categories render with the raw category text (uppercased)
 * and the `neutral` tone — drift-tolerant display.
 */



const CategoryBadge_DEFAULT_LABELS = {
  added: 'New',
  new: 'New',
  changed: 'Improved',
  improved: 'Improved',
  enhancement: 'Improved',
  enhanced: 'Improved',
  fixed: 'Fixed',
  fix: 'Fixed',
  updated: 'Updated',
  update: 'Updated',
  removed: 'Removed',
  deprecated: 'Deprecated',
  security: 'Security'
};
const BASE_TONE = {
  added: 'new',
  new: 'new',
  changed: 'improved',
  improved: 'improved',
  enhancement: 'improved',
  enhanced: 'improved',
  fixed: 'fixed',
  fix: 'fixed',
  updated: 'updated',
  update: 'updated',
  removed: 'removed',
  deprecated: 'deprecated',
  security: 'security'
};
function CategoryBadge({
  category,
  labels: callerLabels,
  toneOverrides
}) {
  if (!category) {
    return null;
  }
  const labels = {
    ...CategoryBadge_DEFAULT_LABELS,
    ...(callerLabels || {})
  };
  const tones = {
    ...BASE_TONE,
    ...(toneOverrides || {})
  };
  const key = String(category).toLowerCase();
  const tone = tones[key] || 'neutral';
  const label = labels[key] || key.toUpperCase();
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
    className: 'pmdk-category-badge pmdk-category-badge--' + tone,
    children: label
  });
}
;// ./node_modules/@pressmaximum/dashboard-kit/src/changelog/ReleaseBlock.css
// extracted by mini-css-extract-plugin

;// ./node_modules/@pressmaximum/dashboard-kit/src/changelog/ReleaseBlock.jsx
/**
 * ReleaseBlock — one release card. SPEC §5.3b + §5.10b. Tier-2 page
 * component.
 *
 * Header: `v{version}` + optional `Current` pill + date. Body: list
 * of items; each item shows its CategoryBadge + text. SPEC §16.2
 * locked class: `.pmdk-release-block`.
 *
 * Release shape:
 *
 *   {
 *     version: string,                  // '1.2.0'
 *     date?: string,                    // already-formatted by consumer
 *     current?: boolean,                // shows the Current pill
 *     items: { category?: string, text: string }[],
 *   }
 *
 * Labels (English fallbacks shipped):
 *   currentBadge   'Current'
 *
 * Consumer passes `categoryLabels` (and optional `toneOverrides`)
 * through to each CategoryBadge — see the badge's docstring.
 */





const ReleaseBlock_DEFAULT_LABELS = {
  currentBadge: 'Current'
};
function ReleaseBlock({
  release,
  labels: callerLabels,
  categoryLabels,
  categoryToneOverrides
}) {
  if (!release) {
    return null;
  }
  const labels = createI18nBag(ReleaseBlock_DEFAULT_LABELS, callerLabels);
  const items = Array.isArray(release.items) ? release.items : [];
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("article", {
    className: "pmdk-release-block",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("header", {
      className: "pmdk-release-block__head",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("h3", {
        className: "pmdk-release-block__version",
        children: ['v' + release.version, release.current && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "pmdk-release-block__current",
          children: labels.currentBadge
        })]
      }), release.date && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
        className: "pmdk-release-block__date",
        children: release.date
      })]
    }), items.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "pmdk-release-block__items",
      children: items.map((item, idx) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
        className: "pmdk-release-block__item",
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(CategoryBadge, {
          category: item.category,
          labels: categoryLabels,
          toneOverrides: categoryToneOverrides
        }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "pmdk-release-block__item-text",
          children: item.text
        })]
      }, idx))
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/tabs/Changelog.jsx
/**
 * Changelog tab — multi-source releases timeline.
 *
 * Free ships a single source (Customify theme releases parsed PHP-side
 * from changelog.txt and shipped on `boot.changelog`). Customify Pro
 * adds itself as a second source via the kit's filter:
 *
 *   addFilter( 'customify.dashboard.changelog.sources', 'customify-pro/changelog',
 *     ( sources ) => sources.concat( [ {
 *       id: 'customify-pro',
 *       label: __( 'Customify Pro', 'customify-pro' ),
 *       fetch: () => apiFetch( { path: '/customify-pro/v1/changelog' } ),
 *     } ] )
 *   );
 *
 * When only one source is registered (Free default), the SubNav rail
 * is hidden — a 1-item rail adds chrome without information. The kit's
 * <ReleaseBlock> ships its own card chrome (background + border +
 * radius), so this file does NOT wrap them in another <Card>.
 */







const RELEASE_LABELS = {
  currentBadge: (0,external_wp_i18n_namespaceObject.__)('Current', 'customify')
};
const CATEGORY_LABELS = {
  new: (0,external_wp_i18n_namespaceObject.__)('New', 'customify'),
  added: (0,external_wp_i18n_namespaceObject.__)('New', 'customify'),
  improved: (0,external_wp_i18n_namespaceObject.__)('Improved', 'customify'),
  fixed: (0,external_wp_i18n_namespaceObject.__)('Fixed', 'customify'),
  updated: (0,external_wp_i18n_namespaceObject.__)('Updated', 'customify'),
  removed: (0,external_wp_i18n_namespaceObject.__)('Removed', 'customify'),
  security: (0,external_wp_i18n_namespaceObject.__)('Security', 'customify'),
  deprecated: (0,external_wp_i18n_namespaceObject.__)('Deprecated', 'customify'),
  neutral: (0,external_wp_i18n_namespaceObject.__)('Note', 'customify')
};
function SourceReleases({
  source
}) {
  const [releases, setReleases] = (0,external_wp_element_namespaceObject.useState)(null);
  const [error, setError] = (0,external_wp_element_namespaceObject.useState)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    let cancelled = false;
    setReleases(null);
    setError(null);
    Promise.resolve(source.fetch()).then(data => {
      if (cancelled) {
        return;
      }
      setReleases(Array.isArray(data) ? data : []);
    }).catch(err => {
      if (cancelled) {
        return;
      }
      setError(err);
    });
    return () => {
      cancelled = true;
    };
  }, [source]);
  if (error) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Notice, {
      status: "error",
      isDismissible: false,
      children: (0,external_wp_i18n_namespaceObject.__)('Could not load the changelog.', 'customify')
    });
  }
  if (releases === null) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
      className: "customify-dashboard-changelog__loading",
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Spinner, {}), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
        children: (0,external_wp_i18n_namespaceObject.__)('Loading changelog…', 'customify')
      })]
    });
  }
  if (releases.length === 0) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Notice, {
      status: "info",
      isDismissible: false,
      children: (0,external_wp_i18n_namespaceObject.__)('No releases yet.', 'customify')
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
    className: "customify-dashboard-changelog__releases",
    children: releases.map(release => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(ReleaseBlock, {
      release: release,
      labels: RELEASE_LABELS,
      categoryLabels: CATEGORY_LABELS
    }, release.version))
  });
}
function Changelog({
  params
}) {
  const boot = useBoot();

  // Recompute on every render — Pro bridge JS registers
  // `customify.dashboard.changelog.sources` AFTER the theme bundle's
  // mountDashboard mounts the React tree. A useMemo over `[boot]`
  // (stable) captured before Pro's filter would never see the appended
  // Pro source. applyFilters is cheap.
  const sourcesBase = [{
    id: 'customify',
    label: (0,external_wp_i18n_namespaceObject.__)('Customify', 'customify'),
    fetch: () => Promise.resolve(Array.isArray(boot?.changelog) ? boot.changelog : [])
  }];
  const sourcesFiltered = (0,external_wp_hooks_namespaceObject.applyFilters)('customify.dashboard.changelog.sources', sourcesBase);
  const sources = Array.isArray(sourcesFiltered) && sourcesFiltered.length > 0 ? sourcesFiltered : sourcesBase;
  const activeId = params?.sourceId || sources[0]?.id;
  const activeSource = sources.find(s => s.id === activeId) || sources[0];

  // Redirect bare `#changelog` to the canonical multi-source path so
  // SubNav has a resolved active row. Skipped when there's only one
  // source — bare `#changelog` is fine for the single-source case.
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!params?.sourceId && sources.length > 1 && activeSource) {
      HashRouter_navigate(`#changelog/${activeSource.id}`);
    }
  }, [params, sources, activeSource]);
  if (!activeSource) {
    return null;
  }
  if (sources.length < 2) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-changelog",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SourceReleases, {
        source: activeSource
      })
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-dashboard-changelog customify-dashboard-changelog--multi",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SubNav, {
      items: sources.map(s => ({
        id: s.id,
        label: s.label,
        hash: `#changelog/${s.id}`
      })),
      activeId: activeId,
      ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Changelog sources', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("div", {
      className: "customify-dashboard-changelog__pane",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(SourceReleases, {
        source: activeSource
      })
    })]
  });
}
;// ./src/backend/admin/dashboard-v2/brand-icon.js
/**
 * Customify brand mark — `fill="currentColor"` so the kit's header
 * foreground token cascades through (matches whatever scheme the user
 * picks in WP Profile).
 *
 * Source SVG lives at src/images/admin/customify-logo.svg; inlined here
 * because wp-scripts processes .svg imports through @svgr/webpack /
 * url-loader rather than as raw text, and the kit's `brand.icon` config
 * expects a string of SVG markup.
 */

const brandIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 3101.46 3275.86" aria-hidden="true" focusable="false">' + '<path fill="currentColor" d="M3549.93,2031c-355.92,238.77-700.38,469.86-1044.87,700.92q-54.73,36.72-109.72,73.1c-109.57,72.46-221.25,72.94-329.28.76Q1304.75,2297,545.42,1785.36c-124.62-89.8-131-256.37-2.19-344.87q762.21-513.76,1527.1-1023.56c92.36-61.7,195.32-77.51,290.13-15.4,396,253.87,788,523,1190.27,790.89-133.38,89.73-279.23,186.54-403.51,271.64h0c-272-181.72-643.72-430.53-914.94-613.34h0c-353.43,240.06-754.8,505.31-1109.42,743.63-7.64,5.14-26.95,18.29-26.95,18.29,311.13,208.64,621.7,418.61,930.58,624,68.56,45.59,213.66,134.89,213.66,134.89s835.92-571.54,902.77-616.37l23.19,17.1h0Z" transform="translate(-449.27 -362.07)"/>' + '<path fill="currentColor" d="M2206.39,3140.47c-391.57-262-1151.43-748-1552.3-1014.5h0c-229.5,154.07-260.26,342-72.82,467.51q733.38,491.26,1466.53,982.87c119.14,80,241.87,83.06,361.18,3.17q380.4-254.71,760.29-510.19c119.19-80,235.16-157.59,364.83-244.48l-379.57-260.3h0c-275.37,184.37-923.63,591-923.63,591h0l-24.51-15Z" transform="translate(-449.27 -362.07)"/>' + '<path fill="currentColor" d="M2581.46,1872.51s29.25-19.93,46-31.8c93.5-66.37,241-164.45,337.7-226.13l3.15-2.15-2.6-1.6c-196.2-128-442.59-293.58-636.46-425.09-66.32-45-129.77-46.18-195.69-1.25-66.56,45.37-134.08,89.32-200.93,134.27-143.26,96.34-286.36,192.92-436.33,294,122.49,82.22,268.13,179.34,382.27,258.16h0s29.19-19.89,42.1-28.64c84.87-57.54,143.16-99,254.24-173,20.8-15.64,54.8-36.49,54.8-36.49Z" transform="translate(-449.27 -362.07)"/>' + '</svg>';
/* harmony default export */ var brand_icon = (brandIcon);
;// ./src/backend/admin/dashboard-v2/index.js
/**
 * Customify Dashboard (v2) — SPA entry.
 *
 * Mounts the @pressmaximum/dashboard-kit shell into #customify-dashboard
 * (rendered by inc/admin/dashboard-v2.php). Pro extends via these
 * filters (hooked BEFORE this script runs):
 *
 *   customify.dashboard.tabs    — append/reorder tabs
 *   customify.dashboard.routes  — register tab routes
 *   customify.dashboard.welcome.checklist — onboarding tasks
 *   customify.dashboard.welcome.sections  — sections below the checklist
 */










function mount() {
  if (!document.getElementById('customify-dashboard')) {
    return;
  }
  const boot = window.customifyDashboard || {};
  // When Customify Pro is active it filters `customify_dashboard_localize`
  // to inject `proVersion`; the header label switches to that version +
  // "Pro version" suffix. Falls back to the theme's own version label
  // for the Free path.
  const proActive = !!boot.proActive;
  const proVersion = (boot.proVersion || '') + '';
  const themeVersion = (boot.themeVersion || '') + '';
  let versionLabel;
  if (proActive && proVersion) {
    versionLabel = (0,external_wp_i18n_namespaceObject.sprintf)(
    // translators: %s is the Customify Pro plugin version (e.g. "0.4.16").
    (0,external_wp_i18n_namespaceObject.__)('v%s — Pro version', 'customify'), proVersion);
  } else if (proActive) {
    versionLabel = (0,external_wp_i18n_namespaceObject.__)('Pro version', 'customify');
  } else if (themeVersion) {
    versionLabel = (0,external_wp_i18n_namespaceObject.sprintf)(
    // translators: %s is the Customify theme version (e.g. "0.4.14").
    (0,external_wp_i18n_namespaceObject.__)('v%s — Free version', 'customify'), themeVersion);
  } else {
    versionLabel = (0,external_wp_i18n_namespaceObject.__)('Free version', 'customify');
  }
  core_mountDashboard({
    rootEl: '#customify-dashboard',
    bootGlobal: 'customifyDashboard',
    filterNamespace: 'customify',
    __: text => (0,external_wp_i18n_namespaceObject.__)(text, 'customify'),
    brand: {
      name: (0,external_wp_i18n_namespaceObject.__)('Customify', 'customify'),
      icon: brand_icon,
      href: '#welcome',
      ariaLabel: (0,external_wp_i18n_namespaceObject.__)('Customify dashboard — go to Welcome', 'customify')
    },
    tabsAriaLabel: (0,external_wp_i18n_namespaceObject.__)('Customify dashboard tabs', 'customify'),
    versionLabel,
    // Land on the Pro changelog source when Pro is active so the
    // `v{pro} — Pro version` anchor points at the matching releases
    // stream. Falls back to the free Customify source otherwise.
    versionHref: proActive ? '#changelog/customify-pro' : '#changelog',
    versionAriaLabel: proActive ? (0,external_wp_i18n_namespaceObject.__)('Customify Pro version — view changelog', 'customify') : (0,external_wp_i18n_namespaceObject.__)('Theme version — view changelog', 'customify'),
    helpItems: [{
      id: 'documentation',
      label: (0,external_wp_i18n_namespaceObject.__)('Documentation', 'customify'),
      href: 'https://pressmaximum.com/docs/customify/'
    }, {
      id: 'changelog',
      label: (0,external_wp_i18n_namespaceObject.__)('Changelog', 'customify'),
      href: '#changelog'
    }, {
      id: 'support',
      label: (0,external_wp_i18n_namespaceObject.__)('Contact support', 'customify'),
      href: 'https://pressmaximum.com/contact/'
    }, {
      id: 'pro',
      label: (0,external_wp_i18n_namespaceObject.__)('Upgrade to Pro', 'customify'),
      href: 'https://pressmaximum.com/customify/pro-upgrade/'
    }],
    helpLabels: {
      triggerLabel: (0,external_wp_i18n_namespaceObject.__)('Open help panel', 'customify'),
      heading: (0,external_wp_i18n_namespaceObject.__)('Help', 'customify')
    },
    // Free vs Pro tab is Free-only: it ships the compare matrix that
    // pitches the Pro upgrade. When Pro is active the matrix becomes
    // noise, so we drop both the tab strip entry AND the
    // `#free-vs-pro` route — kit's HashRouter falls back to
    // `#welcome` for any leftover deep link. Slotted immediately to
    // the right of Settings (between Settings and Changelog) so the
    // "settings-then-upsell" reading order is preserved.
    baseTabs: [{
      id: 'welcome',
      label: (0,external_wp_i18n_namespaceObject.__)('Welcome', 'customify')
    }, {
      id: 'settings',
      label: (0,external_wp_i18n_namespaceObject.__)('Settings', 'customify')
    }, ...(proActive ? [] : [{
      id: 'free-vs-pro',
      label: (0,external_wp_i18n_namespaceObject.__)('Free vs Pro', 'customify')
    }]), {
      id: 'changelog',
      label: (0,external_wp_i18n_namespaceObject.__)('Changelog', 'customify')
    }],
    baseRoutes: {
      '#welcome': {
        component: Welcome,
        type: 'page'
      },
      '#settings': {
        component: Settings,
        type: 'page'
      },
      '#settings/:panelId': {
        component: Settings,
        type: 'page'
      },
      ...(proActive ? {} : {
        '#free-vs-pro': {
          component: FreeVsPro,
          type: 'page'
        }
      }),
      '#changelog': {
        component: Changelog,
        type: 'page'
      },
      '#changelog/:sourceId': {
        component: Changelog,
        type: 'page'
      }
    },
    initialRoute: '#welcome'
  });
}

// Defer to DOMContentLoaded so any Pro / child theme JS that registers
// extender filters (`customify.dashboard.settings.panels`,
// `…changelog.sources`, `…pro.modules`, `…pro.toggle`) has run before
// the React tree mounts and calls applyFilters() for the first time.
// Mirrors Blocksify's bootstrap pattern.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mount);
} else {
  mount();
}
/******/ })()
;