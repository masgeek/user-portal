/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/dom-ready":
/*!**********************************!*\
  !*** external ["wp","domReady"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["domReady"];

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
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*************************!*\
  !*** ./src/frontend.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/dom-ready */ "@wordpress/dom-ready");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0__);

window.createKBSlides = function (id) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  if (window.jQuery && jQuery().slick !== undefined) {
    jQuery(function ($) {
      $(".kb-slides-".concat(id, " .kb-slides-inner-container")).slick(Object.assign({
        appendDots: ".kb-slides-".concat(id, " .kb-slides-dots"),
        customPaging: function customPaging(slider, i) {
          return '<span class="kb-slide-dot"></span>';
        }
      }, options));
    });
  }
};
window.createKBParticles = function (id, options, override) {
  if (window.particlesJS) {
    var getColorValue = function getColorValue(color) {
      if (!color || color === '') {
        return '';
      }
      if (color.indexOf('var') > -1) {
        var value = getComputedStyle(document.documentElement).getPropertyValue(color.replace(/var\(/, '').replace(/\)/, '')).trim().replace(/\s/g, '');
        if (value.indexOf('#') === -1 && value.indexOf('rgb') === -1) {
          return "rgb(".concat(value, ")");
        }
        return value;
      }
      return color;
    };
    var overrideOptions = function overrideOptions(options, override) {
      var particle_color = override.particle_color,
        line_color = override.line_color,
        detect_on = override.detect_on,
        shape = override.shape,
        quantity = override.quantity,
        speed = override.speed,
        size = override.size;
      particle_color = getColorValue(particle_color);
      line_color = getColorValue(line_color || particle_color);
      if ('' !== size && undefined !== size && Number(size) > 0) {
        options.particles.size.value = Number(size);
      }
      if ('default' !== detect_on && '' !== detect_on && undefined !== detect_on) {
        options.interactivity.detect_on = detect_on;
      }
      if ('' !== quantity && undefined !== quantity && Number(quantity) > 0) {
        options.particles.number.value = Number(quantity);
      }
      if ('' !== speed && undefined !== speed && Number(speed) > 0) {
        options.particles.move.speed = Number(speed);
      }
      if ('__INITIAL_VALUE__' !== particle_color && '' !== particle_color && undefined !== particle_color) {
        options.particles.color.value = particle_color;
      }
      if ('__INITIAL_VALUE__' !== line_color && '' !== line_color && undefined !== line_color) {
        if (options.particles.line_linked) {
          // for particles.js
          options.particles.line_linked.color = line_color;
        } else {
          // for tsParticles
          if (options.particles.links) {
            options.particles.links.color = line_color;
          } else {
            options.particles.links = {
              color: line_color
            };
          }
        }
      }
      if ('default' !== shape && '' !== shape && undefined !== shape) {
        options.particles.shape.type = shape;
      }

      // for tsParticles
      options.background = {};
      options.fullScreen = {
        enable: false
      };
      return options;
    };
    _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default()(function () {
      particlesJS("kb-particles-canvas-".concat(id), overrideOptions(options, override));
    });
  }
};
})();

/******/ })()
;