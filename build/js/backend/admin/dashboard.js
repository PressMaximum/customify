/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 522:
/***/ (function() {

jQuery(document).ready(function ($) {
  var _timeout;
  var close_toast = function () {
    _timeout = setTimeout(function () {
      $('#toast-container').hide();
    }, 1500);
  };
  var toast = function (msg, type) {
    if (_.isUndefined(type)) {
      type = 'success';
    }
    if (_timeout) {
      clearTimeout(_timeout);
    }
    if ($('#toast-container').length <= 0) {
      $('body').append('<div id="toast-container" class="toast-top-right"></div>');
    }
    const item = $('<div class="toast-message toast-' + type + '">' + msg + '<button type="button" class="toast-close-button" role="button">×</button></div>');
    $('#toast-container').html(item);
    $('#toast-container').show();
    item.on('click', function (e) {
      $('#toast-container').hide();
    });
    close_toast();
  };
  $(document).on('change', '.auto-save', function (e) {
    e.preventDefault();
    var input = $(this);
    if (!input.is(':disabled')) {
      var name = input.attr('name');
      var t = input.attr('type');
      input.attr('disabled', 'disabled');
      let value = '';
      switch (t) {
        case 'checkbox':
          value = input.is(':checked') ? 'on' : 'off';
          break;
        case 'radio':
          value = $('input[name="' + name + '"]:checked').val();
          break;
        default:
          value = input.val();
      }
      toast(Customify_Dashboard.updating, 'info');
      $.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
          action: 'customify_dashboard_settings',
          option: name,
          value: value,
          _nonce: Customify_Dashboard._nonce
        }
      }).done(function (data) {
        toast(Customify_Dashboard.updated, 'success');
      }).fail(function (data) {
        toast(Customify_Dashboard.error, 'warning');
      }).always(function () {
        input.removeAttr('disabled');
      });
    }
  });
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
/* harmony import */ var _js_dashboard_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(522);
/* harmony import */ var _js_dashboard_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_js_dashboard_js__WEBPACK_IMPORTED_MODULE_0__);


}();
/******/ })()
;