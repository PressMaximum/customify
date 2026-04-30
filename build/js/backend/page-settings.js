/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 300:
/***/ (function() {


;// external ["wp","plugins"]
var external_wp_plugins_namespaceObject = window["wp"]["plugins"];
;// external ["wp","editor"]
var external_wp_editor_namespaceObject = window["wp"]["editor"];
;// external ["wp","coreData"]
var external_wp_coreData_namespaceObject = window["wp"]["coreData"];
;// external ["wp","data"]
var external_wp_data_namespaceObject = window["wp"]["data"];
;// external ["wp","i18n"]
var external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external ["wp","components"]
var external_wp_components_namespaceObject = window["wp"]["components"];
;// external "ReactJSXRuntime"
var external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./src/backend/page-settings/index.js
/**
 * Customify Page Settings — block editor plugin.
 *
 * Renders a PluginDocumentSettingPanel with a TabPanel UI so the content
 * fits compactly in the Document sidebar without excessive padding.
 *
 * Tabs: Layout | Page Header  (+ Breadcrumb merged into Page Header when active)
 * Disable Elements uses ToggleControl for a cleaner on/off UX.
 */









/** Config object injected by wp_localize_script in page-settings.php */

const config = window.customifyPageSettings || {};

// ---------------------------------------------------------------------------
// Option lists
// ---------------------------------------------------------------------------

const CONTENT_LAYOUT_OPTIONS = [{
  label: (0,external_wp_i18n_namespaceObject.__)('Default', 'customify'),
  value: ''
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Full Width', 'customify'),
  value: 'full-width'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Full Width – Stretched', 'customify'),
  value: 'full-stretched'
}];
const SIDEBAR_OPTIONS = [{
  label: (0,external_wp_i18n_namespaceObject.__)('Inherit from Customizer', 'customify'),
  value: ''
}, ...Object.entries(config.sidebarLayouts || {}).map(([value, label]) => ({
  label,
  value
}))];
const PAGE_HEADER_OPTIONS = [{
  label: (0,external_wp_i18n_namespaceObject.__)('Inherit from Customizer', 'customify'),
  value: 'default'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Default', 'customify'),
  value: 'normal'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Cover', 'customify'),
  value: 'cover'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Titlebar', 'customify'),
  value: 'titlebar'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Hide', 'customify'),
  value: 'none'
}];
const BREADCRUMB_OPTIONS = [{
  label: (0,external_wp_i18n_namespaceObject.__)('Inherit from Customizer', 'customify'),
  value: 'default'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Hide', 'customify'),
  value: 'hide'
}, {
  label: (0,external_wp_i18n_namespaceObject.__)('Show', 'customify'),
  value: 'show'
}];

// ---------------------------------------------------------------------------
// Sub-components
// ---------------------------------------------------------------------------

/** A single ToggleControl bound to a _customify_* meta key ('1' / ''). */
function MetaToggle({
  label,
  metaKey,
  meta,
  setMeta
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ToggleControl, {
    label: label,
    checked: meta[`_customify_${metaKey}`] === '1',
    onChange: on => setMeta({
      [`_customify_${metaKey}`]: on ? '1' : ''
    })
  });
}

/** "Layout" tab content. */
function LayoutTab({
  meta,
  setMeta
}) {
  const get = key => meta[`_customify_${key}`] ?? '';
  const set = (key, v) => setMeta({
    [`_customify_${key}`]: v
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-ps-tab-content",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
      label: (0,external_wp_i18n_namespaceObject.__)('Content Layout', 'customify'),
      value: get('content_layout'),
      options: CONTENT_LAYOUT_OPTIONS,
      onChange: v => set('content_layout', v)
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
      label: (0,external_wp_i18n_namespaceObject.__)('Sidebar', 'customify'),
      value: get('sidebar'),
      options: SIDEBAR_OPTIONS,
      onChange: v => set('sidebar', v)
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "customify-ps-section-label",
      children: (0,external_wp_i18n_namespaceObject.__)('Disable Elements', 'customify')
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Header', 'customify'),
      metaKey: "disable_header",
      meta: meta,
      setMeta: setMeta
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Page Title', 'customify'),
      metaKey: "disable_page_title",
      meta: meta,
      setMeta: setMeta
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Header Top', 'customify'),
      metaKey: "disable_header_top",
      meta: meta,
      setMeta: setMeta
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Header Main', 'customify'),
      metaKey: "disable_header_main",
      meta: meta,
      setMeta: setMeta
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Header Bottom', 'customify'),
      metaKey: "disable_header_bottom",
      meta: meta,
      setMeta: setMeta
    }), config.hasProFeatures && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Footer Top', 'customify'),
      metaKey: "disable_footer_top",
      meta: meta,
      setMeta: setMeta
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Footer Main', 'customify'),
      metaKey: "disable_footer_main",
      meta: meta,
      setMeta: setMeta
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(MetaToggle, {
      label: (0,external_wp_i18n_namespaceObject.__)('Footer Bottom', 'customify'),
      metaKey: "disable_footer_bottom",
      meta: meta,
      setMeta: setMeta
    })]
  });
}

/** "Page Header" tab content — also contains Breadcrumb when the plugin is active. */
function PageHeaderTab({
  meta,
  setMeta
}) {
  const get = key => meta[`_customify_${key}`] ?? '';
  const set = (key, v) => setMeta({
    [`_customify_${key}`]: v
  });
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
    className: "customify-ps-tab-content",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
      label: (0,external_wp_i18n_namespaceObject.__)('Display', 'customify'),
      value: get('page_header_display') || 'default',
      options: PAGE_HEADER_OPTIONS,
      onChange: v => set('page_header_display', v)
    }), config.hasBreadcrumb && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
      label: (0,external_wp_i18n_namespaceObject.__)('Breadcrumb', 'customify'),
      value: get('breadcrumb_display') || 'default',
      options: BREADCRUMB_OPTIONS,
      onChange: v => set('breadcrumb_display', v)
    })]
  });
}

// ---------------------------------------------------------------------------
// Main component
// ---------------------------------------------------------------------------

function CustomifyPageSettings() {
  const postType = (0,external_wp_data_namespaceObject.useSelect)(select => select('core/editor').getCurrentPostType(), []);
  const [meta, setMeta] = (0,external_wp_coreData_namespaceObject.useEntityProp)('postType', postType, 'meta');
  if (!meta) return null;
  const tabs = [{
    name: 'layout',
    title: (0,external_wp_i18n_namespaceObject.__)('Layout', 'customify')
  }, {
    name: 'page-header',
    title: (0,external_wp_i18n_namespaceObject.__)('Page Header', 'customify')
  }];
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.TabPanel, {
    className: "customify-ps-tabs",
    tabs: tabs,
    children: tab => {
      if (tab.name === 'layout') {
        return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(LayoutTab, {
          meta: meta,
          setMeta: setMeta
        });
      }
      return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(PageHeaderTab, {
        meta: meta,
        setMeta: setMeta
      });
    }
  });
}

// ---------------------------------------------------------------------------
// Register plugin
// ---------------------------------------------------------------------------

(0,external_wp_plugins_namespaceObject.registerPlugin)('customify-page-settings', {
  render: () => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_editor_namespaceObject.PluginDocumentSettingPanel, {
    name: "customify-page-settings-panel",
    title: (0,external_wp_i18n_namespaceObject.__)('Customify Page Settings', 'customify'),
    className: "customify-page-settings-panel",
    icon: "admin-appearance",
    children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(CustomifyPageSettings, {})
  })
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
/******/ 			597: 0,
/******/ 			549: 0
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
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, [549], function() { return __webpack_require__(300); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;