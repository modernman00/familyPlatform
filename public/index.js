/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 	};
/******/
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"/index": 0
/******/ 	};
/******/
/******/
/******/
/******/ 	// script path function
/******/ 	function jsonpScriptSrc(chunkId) {
/******/ 		return __webpack_require__.p + "" + ({}[chunkId]||chunkId) + ".js"
/******/ 	}
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/ 	// This file contains only the entry chunk.
/******/ 	// The chunk loading function for additional chunks
/******/ 	__webpack_require__.e = function requireEnsure(chunkId) {
/******/ 		var promises = [];
/******/
/******/
/******/ 		// JSONP chunk loading for javascript
/******/
/******/ 		var installedChunkData = installedChunks[chunkId];
/******/ 		if(installedChunkData !== 0) { // 0 means "already installed".
/******/
/******/ 			// a Promise means "currently loading".
/******/ 			if(installedChunkData) {
/******/ 				promises.push(installedChunkData[2]);
/******/ 			} else {
/******/ 				// setup Promise in chunk cache
/******/ 				var promise = new Promise(function(resolve, reject) {
/******/ 					installedChunkData = installedChunks[chunkId] = [resolve, reject];
/******/ 				});
/******/ 				promises.push(installedChunkData[2] = promise);
/******/
/******/ 				// start chunk loading
/******/ 				var script = document.createElement('script');
/******/ 				var onScriptComplete;
/******/
/******/ 				script.charset = 'utf-8';
/******/ 				script.timeout = 120;
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.src = jsonpScriptSrc(chunkId);
/******/
/******/ 				// create error before stack unwound to get useful stacktrace later
/******/ 				var error = new Error();
/******/ 				onScriptComplete = function (event) {
/******/ 					// avoid mem leaks in IE.
/******/ 					script.onerror = script.onload = null;
/******/ 					clearTimeout(timeout);
/******/ 					var chunk = installedChunks[chunkId];
/******/ 					if(chunk !== 0) {
/******/ 						if(chunk) {
/******/ 							var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 							var realSrc = event && event.target && event.target.src;
/******/ 							error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 							error.name = 'ChunkLoadError';
/******/ 							error.type = errorType;
/******/ 							error.request = realSrc;
/******/ 							chunk[1](error);
/******/ 						}
/******/ 						installedChunks[chunkId] = undefined;
/******/ 					}
/******/ 				};
/******/ 				var timeout = setTimeout(function(){
/******/ 					onScriptComplete({ type: 'timeout', target: script });
/******/ 				}, 120000);
/******/ 				script.onerror = script.onload = onScriptComplete;
/******/ 				document.head.appendChild(script);
/******/ 			}
/******/ 		}
/******/ 		return Promise.all(promises);
/******/ 	};
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// on error function for async loading
/******/ 	__webpack_require__.oe = function(err) { console.error(err); throw err; };
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/asset/js/components/FormHelper.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/FormHelper.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return FormHelper; });


function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FormHelper = /*#__PURE__*/function () {
  function FormHelper(data) {
    _classCallCheck(this, FormHelper);

    this.data = data;
    this.error = [];
    this.result = 0;
  }

  _createClass(FormHelper, [{
    key: "id",
    value: function id(x) {
      return document.getElementById(x);
    }
    /**
     * general validation; check empty status, at least a single input, mobile length, white space
     */

  }, {
    key: "getData",
    value: function getData() {
      return this.data;
    }
  }, {
    key: "massValidate",
    value: function massValidate() {
      var _this = this;

      var reg = /[a-zA-Z0-9./@]/g;
      this.data.forEach(function (et) {
        var _iterator = _createForOfIteratorHelper(et),
            _step;

        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var post = _step.value;

            // capture the error to a variable
            var errMsg = _this.id("".concat(post.name, "_error")); // rid it off the submit button


            if (post.type == 'submit' || post.name == 'token') {
              continue;
            } // check if there is no value


            if (post.value === '' || post.value === 'select') {
              var postName = post.name.replace('_', ' ');
              errMsg.innerHTML = "<li>Cannot be left empty</li>";

              _this.error.push("<li>Cannot be left empty</li>");
            } else if (post.value.match(reg) === null) {
              errMsg.innerHTML = "<li> only letters and numbers are allowed<li>";

              _this.error.push("<li> only letters and numbers are allowed<li>");
            } else {
              _this.result = 1;
            }
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      });
    }
  }, {
    key: "emailVal",
    value: function emailVal() {
      var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
      var msg = "<li> Please enter a valid email</li>";
      var email = this.id('email').value;

      if (email.match(emailExp) === null) {
        this.id('email_error').innerHTML = msg;
        this.error.push(msg);
      }
    }
  }, {
    key: "clearError",
    value: function clearError() {
      var _this2 = this;

      this.error = []; // empty the array 

      this.data.forEach(function (el) {
        var _iterator2 = _createForOfIteratorHelper(el),
            _step2;

        try {
          var _loop = function _loop() {
            var post = _step2.value;

            if (post.id == 'submit' || post.name == 'token' || post.name == 'submit' || post.name == 'checkbox') {
              return "continue";
            }

            if (post.value != 'select') {
              _this2.id(post.id).addEventListener('keyup', function () {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
              });
            } else {
              _this2.id(post.id).addEventListener('change', function () {
                _this2.id("".concat(post.name, "_error")).innerHTML = '';
              });
            }
          };

          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var _ret = _loop();

            if (_ret === "continue") continue;
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }
      });
    }
    /**
     *
     * @param {input is the id of the input/ this is an array [as, it, it]} input
     * @param {* this is the max policy and it must be an integer} maxi
     */

  }, {
    key: "realTimeCheckLen",
    value: function realTimeCheckLen(input, maxi) {
      var _this3 = this;

      try {
        var _loop2 = function _loop2(i) {
          var theData = _this3.id("".concat(input[i]));

          if (theData == "") throw "empty dataInput";
          var max = maxi[i];

          var error = _this3.id("".concat(input[i], "_error"));

          if (theData) theData.maxLength = parseInt(max + 1);
          theData.addEventListener('keyup', function () {
            error.innerHTML = theData.value.length > max ? "You have reach the maximum limit" : "";
            _this3.id("".concat(input[i], "_help")).style.color = 'red';
            _this3.id("".concat(input[i], "_help")).style.fontSize = '10px';
            setTimeout(function () {
              _this3.id("".concat(input[i], "_help")).style.display = 'none';
            }, 5000);
          });
        };

        for (var i = 0; i < input.length; i++) {
          _loop2(i);
        }
      } catch (error) {
        console.log(error.message);
      }
    }
    /**
     * the id for the password error should be password_help
     * the id for your confirm pasword should confirm_password
     * it will return an error message to the password_help input
     */

  }, {
    key: "matchInput",
    value: function matchInput(first, second) {
      var error, firstInput, secondInput;
      error = this.id("".concat(second, "_error"));
      firstInput = this.id(first);
      secondInput = this.id(second);
      secondInput.addEventListener('keyup', function () {
        error.innerHTML = firstInput.value !== secondInput.value ? 'Your passwords do not match' : "";
      });
    }
    /**
     *
     * @param {the id of the input you want to inject to/ this is an array} idArray
     * @param {*the comment or questions you want o inject} html
     */

  }, {
    key: "injectData",
    value: function injectData(idArray, html) {
      var idData;

      for (var i = 0; i < idArray.length; i++) {
        idData = this.id(idArray[i]);
        idData.innerHTML = html[i];
      }
    }
    /**
     *
     * @param {this is an id and its value is for duplication} firstInput
     * @param {* another id that accepts the value of the firstInput} takeFirstInput
     */

  }, {
    key: "duplicate",
    value: function duplicate(giveInput, takeInput) {
      var giver, taker;
      giver = this.id(giveInput);
      taker = this.id(takeInput);
      giver.addEventListener('keyup', function () {
        taker.value = giver.value;
      });
    }
    /**
     *
     * @param {current input that is being type to. the value is what will be checked realtime. the id is needed} input
     * @param {* the url to get the info to . example is /search?hint} url
     * @param {enter the id of the output element} output
     */

  }, {
    key: "realTimeServer",
    value: function realTimeServer(input, url, outputId) {
      var theInput, inputVal, output;
      theInput = this.id(input);
      output = this.id(outputId);
      theInput.addEventListener('keyup', function () {
        inputVal = theInput.value;

        if (inputVal == 0) {
          output.innerHTML = "";
          return;
        } else {
          var xmlhttp = new XMLHttpRequest();

          xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
              output.innerHTML = this.responseText;
            }
          };

          xmlhttp.open("GET", "".concat(url, "=").concat(inputVal), true);
          xmlhttp.send();
        }
      });
    }
  }, {
    key: "isChecked",
    value: function isChecked(yesId, noId, hiddenInput) {
      var _this4 = this;

      var checked = function checked() {
        if (_this4.id(yesId).checked) {
          alert('check');
          _this4.id(hiddenInput).innerHTML = 'checked';
        } else if (_this4.id(noId).checked) {
          _this4.id(hiddenInput).innerHTML = 'checked';
        }
      };

      this.id(yesId).addEventListener('click', checked);
      this.id(noId).addEventListener('click', checked);
    }
  }, {
    key: "previousAddress",
    value: function previousAddress() {
      var _this5 = this;

      var timeAddy = this.id('time_at_address_id');
      var prevAddy = this.id('previous_address_class');

      var showPrev = function showPrev() {
        if (timeAddy.value != '3 years+') {
          prevAddy.style.display = 'block';
          _this5.id('previous_address_help').innerHTML = "Please enter your full address: House No, Street Name, Town/City and Post Code";
        } else {
          prevAddy.style.display = 'none';
        }
      };

      timeAddy.addEventListener('change', showPrev);
    }
  }]);

  return FormHelper;
}();



/***/ }),

/***/ "./resources/asset/js/components/formBuilder.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/formBuilder.js ***!
  \******************************************************/
/*! exports provided: Input */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Input", function() { return Input; });

/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */

var Input = function Input(objData, htmlId) {
  try {
    // check errors for the input
    if (!objData) throw " data object is needed";
    if (htmlId == "") throw "html id is required";
    objData.map(function (element) {
      if (element.type === 'NORMAL_INPUT') {
        var renderHtml = "\n      <label> <strong>".concat(element.form.toUpperCase(), "</strong> </label>\n      <div class = 'form-group' id=").concat(element.attribute, "_div>\n         <label class='' for =").concat(element.attribute, "> \n         <strong>").concat(element.label.toUpperCase(), "</strong>\n         </label>\n         <input type=\"").concat(element.type, "\" class=\"form-control\" \n         id=\"").concat(element.attribute, "\" \n         name=\"").concat(element.attribute, "\"  placeholder=\"").concat(element.placeholder, "\"\n         />\n        <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n      </div>");
        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml);
      } else if (element.inputType === 'SELECT') {
        var _renderHtml = "\n      <div class = 'form-group' id='".concat(element.attribute, "_div'>\n      <label for =").concat(element.attribute, "> <strong>").concat(element.label.toUpperCase(), "</strong> </label>   \n          <select class=\"form-control\" id=").concat(element.attribute, " name=").concat(element.attribute, ">\n                <option value= 'select'>select</option> \n                ").concat(element.options.map(function (el) {
          return "<option value=".concat(el, ">").concat(el, "</option>");
        }), "               \n          </select>     \n             <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n           </div>\n          ");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml);
      } else if (element.inputType === 'FILE') {
        var _renderHtml2 = "\n      <div class = 'form-group input-group mb-3' id='".concat(element.attribute, "_div'>\n       <div class=\"custom-file\">\n          <input type=\"file\" name=\"").concat(element.attribute, "\" class=\"custom-file-input\" id=").concat(element.attribute, ">\n          <label class=\"custom-file-label\" for=").concat(element.attribute, ">").concat(element.label, "</label>\n             <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n           </div>\n          </div>\n          ");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml2);
      } else if (element.type === 'radio') {
        var _renderHtml3 = "\n      <label> <strong>".concat(element.form.toUpperCase(), "</strong> </label>\n      <div class='form-group col' id='").concat(element.attribute, "_div'>\n         <label for =").concat(element.attribute, "> ").concat(element.label.toUpperCase(), ": </label> \n\n      <div class = 'form-check form-check-inline'>\n      ").concat(element.options.map(function (el) {
          return "\n       <input class='form-check-input' type='radio' name=".concat(element.attribute, " id=").concat(element.attribute, " value=").concat(el, ">\n        <label class= 'form-check-label' > ").concat(el, "\n      </label>");
        }), " \n      </div>  \n        <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n      </div>     \n      ");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml3);
      } else if (element.form === '3-col') {
        var _renderHtml4 = "\n      <label> <strong>".concat(element.label.toUpperCase(), "</strong> </label>\n       <div class = 'form-row' id='").concat(element.form, "_div'>\n       \n          <div class='form-group col-md-4'>\n            <label for='").concat(element.options.attribute[0], "'>\n            ").concat(element.options.label[0], "\n            </label>\n            <input type='").concat(element.options.type[0], "' class='form-control' name='").concat(element.options.attribute[0], "'\n            id='").concat(element.options.attribute[0], "' placeholder='").concat(element.options.placeholder[0], "'>\n         <small id ='").concat(element.options.attribute[0], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[0], "_error class='error text-muted'></small>\n          </div>\n          <div class='form-group col-md-4'>\n            <label for='").concat(element.options.attribute[1], "'>").concat(element.options.label[1], "\n            </label>\n            <input type='").concat(element.options.type[1], "' class='form-control'  \n            id='").concat(element.options.attribute[1], "' name='").concat(element.options.attribute[1], "'\n            placeholder='").concat(element.options.placeholder[1], "'>\n         <small id ='").concat(element.options.attribute[1], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[1], "_error class='error text-muted'></small>\n          </div> \n          <div class='form-group col-md-4'>\n            <label for='").concat(element.options.attribute[2], "'>").concat(element.options.label[2], "\n            </label>\n            <input type='").concat(element.options.type[2], "' class='form-control' \n            id='").concat(element.options.attribute[2], "' name='").concat(element.options.attribute[2], "' \n            placeholder='").concat(element.options.placeholder[2], "'>\n         <small id ='").concat(element.options.attribute[2], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[2], "_error class='error text-muted'></small>\n          </div>  \n      </div>");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml4);
      } else if (element.form === '2-col') {
        var _renderHtml5 = "\n      <label> <strong>".concat(element.label.toUpperCase(), "</strong> </label>\n       <div class = 'form-row' id='").concat(element.form, "_div'>\n       \n          <div class='form-group col-md-6'>\n            <label for='").concat(element.options.attribute[0], "'>\n            ").concat(element.options.label[0], "\n            </label>\n            <input type='").concat(element.options.type[0], "' class='form-control' \n            id='").concat(element.options.attribute[0], "' \n            placeholder='").concat(element.options.placeholder[0], "'\n            name='").concat(element.options.attribute[0], "'\n            >\n         <small id ='").concat(element.options.attribute[0], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[0], "_error class='error text-muted'></small>\n          </div>\n          <div class='form-group col-md-6'>\n            <label for='").concat(element.options.attribute[1], "'>").concat(element.options.label[1], "\n            </label>\n            <input type='").concat(element.options.type[1], "' class='form-control' \n            id='").concat(element.options.attribute[1], "' \n            placeholder='").concat(element.options.placeholder[1], "'\n            name='").concat(element.options.attribute[1], "'\n            >\n         <small id ='").concat(element.options.attribute[1], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[1], "_error class='error text-muted'></small>\n          </div> \n      </div>");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml5);
      }
    });
  } catch (e) {
    console.log(e);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/login/index.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/login/index.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _data_Login__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../data/Login */ "./resources/asset/js/data/Login.js");
/* harmony import */ var _formBuilder__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../formBuilder */ "./resources/asset/js/components/formBuilder.js");



try {
  Object(_formBuilder__WEBPACK_IMPORTED_MODULE_1__["Input"])(_data_Login__WEBPACK_IMPORTED_MODULE_0__["Login"], 'login');
} catch (error) {
  console.log(error);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/profilePage.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// "use strict";
// const displayElement = ()=> {
//   return document.getElementById('formProfilePics').style.display = 'block';
// }
// document.getElementById('formProfilePics').style.display = 'none';
// document.getElementById('profilePics').addEventListener('click', displayElement)

/***/ }),

/***/ "./resources/asset/js/components/register/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/index.js ***!
  \*********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _data_Personal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../data/Personal.js */ "./resources/asset/js/data/Personal.js");
/* harmony import */ var _data_Contact_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../data/Contact.js */ "./resources/asset/js/data/Contact.js");
/* harmony import */ var _data_Work_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/Work.js */ "./resources/asset/js/data/Work.js");
/* harmony import */ var _formBuilder_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../formBuilder.js */ "./resources/asset/js/components/formBuilder.js");
/* harmony import */ var _data_Interest__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../data/Interest */ "./resources/asset/js/data/Interest.js");
/* harmony import */ var _data_Account__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../../data/Account */ "./resources/asset/js/data/Account.js");









try {
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Contact_js__WEBPACK_IMPORTED_MODULE_1__["Contact"], 'contact');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Personal_js__WEBPACK_IMPORTED_MODULE_0__["Personal"], 'personal');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Work_js__WEBPACK_IMPORTED_MODULE_2__["Work"], 'work');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Interest__WEBPACK_IMPORTED_MODULE_4__["Interest"], 'interest');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Account__WEBPACK_IMPORTED_MODULE_5__["Account"], 'account');
} catch (e) {
  console.log(e.message, e.name);
}

/***/ }),

/***/ "./resources/asset/js/components/register/modal/kids.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/register/modal/kids.js ***!
  \**************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../../../global */ "./resources/asset/js/global.js");




var show = function show(e) {
  try {
    var kidsNo = e.target.value; // use the loop to generate the number of input

    for (var i = 0; i < kidsNo; i++) {
      var no = i + 1;
      var msg = no > 1 ? "Please, enter their names and emails" : "Please, enter your child name and email";
      var getSelectHelp = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids_help');
      getSelectHelp.innerHTML = msg;
      getSelectHelp.style.fontSize = '1rem';
      var addKids = " <div class=\"row\">\n            <div class=\"col\">\n            <input type=\"text\" placeholder = \"Enter child's full name - ".concat(no, "\" name =kid_name").concat(no, " class=\"form-control\" id=\"kid_name").concat(no, "\">\n            </div>\n            <div class=\"col\">\n           <input type=\"email\" placeholder = \"Enter child's email - ").concat(no, "\" name=kid_email").concat(no, " class=\"form-control\" id=\"kid_email").concat(no, "\">\n           </div>\n        </div><br>");
      if (!Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("kid".concat(no)) || !Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("kidEmail".concat(no))) Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids_div').insertAdjacentHTML('afterend', addKids);
    }
  } catch (error) {
    console.log(error);
  }
}; // this is to activate the onchange event


Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids').addEventListener('change', show);

/***/ }),

/***/ "./resources/asset/js/components/register/modal/siblings.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/register/modal/siblings.js ***!
  \******************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../../../global */ "./resources/asset/js/global.js");




var show = function show(e) {
  var siblingNo = e.target.value; //    const checkAppend = qSel('.appendLabel')
  //         if(checkAppend || id(`noSiblings${no}`) || id(`noSiblingsEmail${no}`)) {
  //             checkAppend.remove()
  //         }
  // use the loop to generate the number of input

  for (var i = 0; i < siblingNo; i++) {
    //    checkAppend && checkAppend.remove()
    var no = i + 1;
    var msg = no > 1 ? "Please, enter their names and emails" : "Please, enter your child name and email";
    var getSelectHelp = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('noSiblings_help');
    getSelectHelp.innerHTML = msg;
    getSelectHelp.style.fontSize = '1rem';
    var addnoSiblings = " <div class=\"row appendLabel\">\n            <div class=\"col\">\n            <input type=\"text\" placeholder = \"Enter sibling's full name - ".concat(no, "\" name =\"sibling_name").concat(no, "\" class=\"form-control\" id=\"sibling_name").concat(no, "\">\n            </div>\n            <div class=\"col\">\n           <input type=\"email\" placeholder = \"Enter sibling's email - ").concat(no, "\" name=\"sibling_email").concat(no, "\" class=\"form-control\" id=\"sibling_email").concat(no, "\">\n           </div>\n        </div><br>");
    if (!Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("noSiblings".concat(no)) || !Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("noSiblingsEmail".concat(no))) Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('noSiblings_div').insertAdjacentHTML('afterend', addnoSiblings);
  }
}; // this is to activate the onchange event


Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('noSiblings').addEventListener('change', show);

/***/ }),

/***/ "./resources/asset/js/components/register/processForm.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/register/processForm.js ***!
  \***************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");
/* harmony import */ var _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/dataToCheck */ "./resources/asset/js/data/dataToCheck.js");





var formInput = document.querySelectorAll('.register');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_0__["default"](formInputArr);

var process = function process() {
  // clear error from the form
  formData.clearError(); // set the maxlength, check the length of the value, raise error

  formData.realTimeCheckLen(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].maxLength.id, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].maxLength.max); //real time check 

  formData.realTimeServer('spouseMobile', "/search?attribute=spouseMobile&subject=spouse&hint", 'spouseMobile_error');
  formData.realTimeServer('fatherMobile', '/search?attribute=fatherMobile&subject=father&hint', 'fatherMobile_error');
  formData.realTimeServer('motherMobile', '/search?attribute=motherMobile&subject=mother&hint', 'motherMobile_error'); // check if password matches real time

  formData.matchInput(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].password.pwd, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].password.pwd2 // dataToCheck.password.err
  ); // check if they have a father yes
  // formData.isChecked(dataToCheck.familyCheck.father[0],
  // 	dataToCheck.familyCheck.father[1],
  // 	'fatherEmail_error'
  // )
  // // check if they have a mother yes
  // formData.isChecked(dataToCheck.familyCheck.mother[0],
  // 	dataToCheck.familyCheck.mother[1],
  // 	'motherEmail_error'
  // )
  // // check if they have a spouse yes
  // formData.isChecked(dataToCheck.familyCheck.spouse[0],
  // 	dataToCheck.familyCheck.spouse[1],
  // 	'spouseEmail_error'
  // )
};

process();
Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').addEventListener('click', function () {
  try {
    if (Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('checkbox').checked) {
      formData.emailVal(); // sanitise email

      formData.massValidate(); // validate and sanitise data

      Object(_global__WEBPACK_IMPORTED_MODULE_1__["log"])(formData.error);

      if (formData.error.length <= 0) {
        Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').type = 'submit';
        console.log('submitted');
      } else {
        console.log(formData.error);
        alert('The form cannot be submitted. Please check the errors');
        process();
      }
    } else {
      alert('To continue, you need to agree to the Olaoguns handling your information as outlined in our privacy policy');
    }
  } catch (e) {
    console.log(e);
  }
});

/***/ }),

/***/ "./resources/asset/js/cust/main.js":
/*!*****************************************!*\
  !*** ./resources/asset/js/cust/main.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function () {
  var i = "box",
      n = {
    init: function init() {
      this.scroll_animate(), $("#b2top").click(function () {
        $("html, body").stop().animate({
          scrollTop: 0
        });
      }), $("#overview-aside-nav ul").navscroll({
        sec: 1e3,
        url_hash: !1,
        head_hight: 0
      }), $("#overview-mainnav").navscroll({
        sec: 1e3,
        url_hash: !1,
        head_hight: 0
      });
    },
    scroll_animate: function scroll_animate() {
      $(window).scroll(function () {
        for (var i = window.innerHeight, n = $(".anim").length, o = 0; o < n; o++) {
          $(window).scrollTop() > $(".anim").eq(o).offset().top - i / 4 && $(".anim").eq(o).addClass("on");
        }
      });
    }
  };
  n.init();
});

(function ($) {
  /* --------------------------------------------------
   Contact Pages
  -------------------------------------------------- */
  $('.show-map').on('click', function (e) {
    e.preventDefault();
    $('.contact-info-wrapper').toggleClass('map-open');
    $('.show-info-link').toggleClass('info-open');
  });
  $('.show-info-link').on('click', function (e) {
    e.preventDefault();
    $('.contact-info-wrapper').toggleClass('map-open');
    $(this).toggleClass('info-open');
  });
})(jQuery);
/* --------------------------------------------------
	Contact Form JS Validation & AJAX call 
-------------------------------------------------- */


$(function () {
  //	Regular Expressions
  var expEmail = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.[a-z]{2,4})$/;
  var expLettersOnly = /^[A-Za-z ]+$/; //	Checks if a field has the correct length

  function validateLength(fieldValue, minLength) {
    return $.trim(fieldValue).length > minLength;
  } //	Validate form on typing


  $('.form-ajax').on('keyup', 'input.validate-locally', function () {
    validateField($(this));
  }); //	AJAX call

  $('.form-ajax').submit(function (e) {
    e.preventDefault();
    var $this = $(this),
        action = $this.attr('action'); // The AJAX requrest

    $.post(action, $this.serialize(), function (data) {
      $('.ajax-message').html(data);
    });
  }); //	Validates the fileds

  function validateField(field) {
    var errorText = "",
        error = false,
        value = field.val(),
        siblings = field.siblings(".alert-error"); // Test the name field

    if (field.attr("name") === "name") {
      if (!validateLength(value, 2)) {
        error = true;
        errorText += '<i class="fa fa-info-circle"></i> The name is too short!<br>';
        $('input[name="name"]').addClass('input-error');
      } else {
        $('input[name="name"]').removeClass('input-error');
      }

      if (!expLettersOnly.test(value)) {
        error = true;
        errorText += '<i class="fa fa-info-circle"></i> The name can contain only letters and spaces!<br>';
        $('input[name="name"]').addClass('input-error-2');
      } else {
        $('input[name="name"]').removeClass('input-error-2');
      }
    } // Test the email field


    if (field.attr("name") === "email") {
      if (!expEmail.test(value)) {
        error = true;
        errorText += '<i class="fa fa-info-circle"></i> Enter correct email address!<br>';
        $('input[name="email"]').addClass('input-error');
      } else {
        $('input[name="email"]').removeClass('input-error');
      }
    } // Display the errors


    siblings.html(errorText);
  }
}); // Google Map Custom js

var marker;
var image = 'images/map-marker.png';

function initMap() {
  var myLatLng = {
    lat: 39.79,
    lng: -86.14
  }; // Specify features and elements to define styles.

  var styleArray = [{
    "featureType": "administrative",
    "elementType": "all",
    "stylers": [{
      "saturation": "-100"
    }]
  }, {
    "featureType": "administrative.province",
    "elementType": "all",
    "stylers": [{
      "visibility": "off"
    }]
  }, {
    "featureType": "landscape",
    "elementType": "all",
    "stylers": [{
      "saturation": -100
    }, {
      "lightness": 40
    }, {
      "visibility": "on"
    }]
  }, {
    "featureType": "poi",
    "elementType": "all",
    "stylers": [{
      "saturation": -100
    }, {
      "lightness": "50"
    }, {
      "visibility": "simplified"
    }]
  }, {
    "featureType": "road",
    "elementType": "all",
    "stylers": [{
      "saturation": "-100"
    }]
  }, {
    "featureType": "road.highway",
    "elementType": "all",
    "stylers": [{
      "visibility": "simplified"
    }]
  }, {
    "featureType": "road.arterial",
    "elementType": "all",
    "stylers": [{
      "lightness": "30"
    }]
  }, {
    "featureType": "road.local",
    "elementType": "all",
    "stylers": [{
      "lightness": "40"
    }]
  }, {
    "featureType": "transit",
    "elementType": "all",
    "stylers": [{
      "saturation": -100
    }, {
      "visibility": "simplified"
    }]
  }, {
    "featureType": "water",
    "elementType": "geometry",
    "stylers": [{
      "hue": "#ffff00"
    }, {
      "lightness": -20
    }, {
      "saturation": -97
    }]
  }, {
    "featureType": "water",
    "elementType": "labels",
    "stylers": [{
      "lightness": -25
    }, {
      "saturation": -100
    }]
  }];
  var map = new google.maps.Map(document.getElementById('map'), {
    center: myLatLng,
    scrollwheel: false,
    // Apply the map style array to the map.
    styles: styleArray,
    zoom: 9
  });
  var directionsDisplay = new google.maps.DirectionsRenderer({
    map: map
  }); // Create a marker and set its position.

  marker = new google.maps.Marker({
    map: map,
    icon: image,
    draggable: true,
    animation: google.maps.Animation.DROP,
    position: myLatLng
  });
  marker.addListener('click', toggleBounce);
}

function toggleBounce() {
  if (marker.getAnimation() !== null) {
    marker.setAnimation(null);
  } else {
    marker.setAnimation(google.maps.Animation.BOUNCE);
  }
}

/***/ }),

/***/ "./resources/asset/js/data/Account.js":
/*!********************************************!*\
  !*** ./resources/asset/js/data/Account.js ***!
  \********************************************/
/*! exports provided: Account */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Account", function() { return Account; });


var Account = [{
  form: '3-col',
  label: 'Create an account:',
  options: {
    label: ['Password', 'Confirm password', 'Secret word'],
    attribute: ['password', 'confirm_password', 'secretWord'],
    placeholder: ['xxxx', 'xxxx', 'one time security code'],
    type: ['password', 'password', 'password']
  }
}];

/***/ }),

/***/ "./resources/asset/js/data/Contact.js":
/*!********************************************!*\
  !*** ./resources/asset/js/data/Contact.js ***!
  \********************************************/
/*! exports provided: Contact */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Contact", function() { return Contact; });


var Contact = [{
  form: 'Contact Information',
  label: 'Home Address',
  attribute: 'address',
  placeholder: 'Please, enter your first line of address',
  type: 'text',
  inputType: 'NORMAL_INPUT'
}, {
  form: '2-col',
  label: "Area Code and Region",
  options: {
    label: ["Postcode /zip code/area code", "Region / State / District"],
    attribute: ['postcode', 'region'],
    placeholder: ['SN2 3BF / 234', 'London / Lagos state / New York'],
    type: ['text', 'text']
  }
}, {
  form: '2-col',
  label: "How to reach you",
  options: {
    label: ["Email", "Mobile Number"],
    attribute: ['email', 'mobile'],
    placeholder: ['toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
    type: ['email', 'number']
  }
}, // {
//    label: 'Postcode /zip code/area code',
//    attribute: 'postcode',
//    placeholder: 'SN5 5DE',
//    type: 'text'
// },
// {
//    label: 'Region / State / District',
//    attribute: 'region',
//    placeholder: 'London / Lagos state / New York',
//    type: 'text'
// },
{
  label: 'Country',
  attribute: 'country',
  placeholder: null,
  type: 'select',
  options: ['Nigeria', 'UK', 'Canada', 'Europe', 'USA', 'China', 'Asia', 'Latin America'],
  inputType: 'SELECT'
} // {
//    label: 'mobile number',
//    attribute: 'mobile',
//    placeholder: 'include the area code - 234 or 1 or 44 ',
//    type: 'text'
// }
];

/***/ }),

/***/ "./resources/asset/js/data/Interest.js":
/*!*********************************************!*\
  !*** ./resources/asset/js/data/Interest.js ***!
  \*********************************************/
/*! exports provided: Interest */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Interest", function() { return Interest; });


var Interest = [{
  form: '3-col',
  label: 'Interest:',
  options: {
    label: ['Favourite Sport', 'Football team', 'Passion'],
    attribute: ['favSport', 'footballTeam', 'passion'],
    placeholder: ['Football, Tennis, F1', 'Chelsea, Liverpool', 'singing or tech or travelling'],
    type: ['text', 'text', 'text']
  }
} //  {
//   label : 'Favourite sport',
//   attribute: 'favSport',
//   placeholder: 'football, tennis, F1',
//   type : 'text'
// },
// {
//   label : 'Favourite football team',
//   attribute: 'footballTeam',
//   placeholder: 'Barcelona, Chelsea, Liverpool, Bayern Munich',
//   type : 'text'
// },
// {
//   label : "what do you have passion for",
//   attribute: 'passion',
//   placeholder: 'singing or tech or travelling or solving problems, reading etc',
//   type : 'text',
// }      
];

/***/ }),

/***/ "./resources/asset/js/data/Login.js":
/*!******************************************!*\
  !*** ./resources/asset/js/data/Login.js ***!
  \******************************************/
/*! exports provided: Login */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Login", function() { return Login; });


var Login = [{
  form: 'Email address',
  label: 'email',
  attribute: 'email',
  placeholder: 'family@email.com',
  inputType: 'NORMAL_INPUT'
}, {
  form: 'Password',
  label: 'password',
  attribute: 'password',
  placeholder: 'SN55DE',
  type: 'password',
  inputType: 'NORMAL_INPUT'
}];

/***/ }),

/***/ "./resources/asset/js/data/Personal.js":
/*!*********************************************!*\
  !*** ./resources/asset/js/data/Personal.js ***!
  \*********************************************/
/*! exports provided: Personal */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Personal", function() { return Personal; });


var Personal = [{
  form: '3-col',
  label: 'Personal Information:',
  options: {
    label: ['First Name', 'Surname', 'Alias'],
    attribute: ['firstName', 'lastName', 'alias'],
    placeholder: ['Wale', 'Olaogun', 'Modernman'],
    type: ['text', 'text', 'text']
  }
}, {
  form: '2-col',
  label: "Spouse's Details",
  options: {
    label: ["Spouse's name", "Spouse's mobile"],
    attribute: ['spouseName', 'spouseMobile'],
    placeholder: ['Toyin', '23480364168089'],
    type: ['text', 'number']
  }
}, {
  form: '2-col',
  label: "Father's Details",
  options: {
    label: ["Father's name", "Father's mobile"],
    attribute: ['fatherName', 'fatherMobile'],
    placeholder: ['Toyin Olaogun', '23480364168089'],
    type: ['text', 'number']
  }
}, {
  form: '3-col',
  label: "Mother's Details",
  options: {
    label: ["Mother's name", "Mother's mobile", "Mother's maiden name"],
    attribute: ['motherName', 'motherMobile', 'motherMaiden'],
    placeholder: ['Toyin', '23480364168089', "surname before marriage"],
    type: ['text', 'number', 'text']
  }
}, {
  label: "Number of children",
  attribute: 'kids',
  placeholder: null,
  type: 'select',
  options: [0, 1, 2, 3, 4, 5, 6],
  inputType: 'SELECT'
}, {
  label: "Gender",
  attribute: 'gender',
  placeholder: null,
  type: 'select',
  options: ['Male', 'Female'],
  inputType: 'SELECT'
}, {
  label: "Number of siblings (Brothers/Sisters)",
  attribute: 'noSiblings',
  placeholder: null,
  type: 'select',
  options: [0, 1, 2, 3, 4, 5, 6],
  inputType: 'SELECT'
}, {
  label: "Please, upload your picture",
  attribute: 'profileImage',
  placeholder: null,
  type: 'file',
  inputType: 'FILE'
}, {
  form: '3-col',
  label: 'Date of Birth:',
  options: {
    label: ['Day', 'Month', 'Year'],
    attribute: ['day', 'month', 'year'],
    placeholder: ['15', 'July', '1982'],
    type: ['number', 'text', 'number']
  }
} // {
//   form: '3_col_select',
//   label: 'Other details:',
//   options: {
//     label: ['Gender', 'Siblings', 'Children'],
//     attribute: ['gender', 'noSiblings', 'kids'],
//     placeholder: ['15', 'July', '1982'],
//     selectOption1: ['male', 'female'],
//     selectOption2: [1, 2, 3, 4, 5, 6],
//     selectOption3: [1, 2, 3, 4, 5, 6],
//   }
// }
];

/***/ }),

/***/ "./resources/asset/js/data/Work.js":
/*!*****************************************!*\
  !*** ./resources/asset/js/data/Work.js ***!
  \*****************************************/
/*! exports provided: Work */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Work", function() { return Work; });


var Work = [{
  form: "Work details",
  label: 'employment status',
  attribute: 'employmentStatus',
  placeholder: null,
  options: ['Self-employed', 'Unemployed', 'Full-time-employment', 'Student'],
  inputType: 'SELECT'
}, {
  form: "Occupation",
  label: 'Occupation?',
  attribute: 'occupation',
  placeholder: 'Accountant, Housewife, Student, Business man etc',
  inputType: 'NORMAL_INPUT'
}];

/***/ }),

/***/ "./resources/asset/js/data/dataToCheck.js":
/*!************************************************!*\
  !*** ./resources/asset/js/data/dataToCheck.js ***!
  \************************************************/
/*! exports provided: dataToCheck */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "dataToCheck", function() { return dataToCheck; });


var dataToCheck = {
  maxLength: {
    id: ['firstName', 'lastName', 'alias', 'spouse', 'fatherName', 'motherName', 'motherMaiden', 'address', 'postcode', 'region', 'country', 'mobile', 'email', 'favSport', 'footballTeam', 'passion', 'occupation'],
    max: [15, 15, 15, 15, 30, 30, 15, 50, 10, 15, 15, 13, 45, 25, 30, 40, 20]
  },
  duplicate: {
    email: 'email',
    username: 'username'
  },
  password: {
    pwd: 'password',
    pwd2: 'confirm_password'
  },
  familyCheck: {
    father: ["fatherYes", "fatherNo"],
    mother: ["motherYes", "motherNo"],
    spouse: ["spouseYes", "spouseNo"]
  }
};

/***/ }),

/***/ "./resources/asset/js/global.js":
/*!**************************************!*\
  !*** ./resources/asset/js/global.js ***!
  \**************************************/
/*! exports provided: id, idValue, idInnerHTML, qSel, qSelValue, qSelInnerHTML, log, write */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "id", function() { return id; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "idValue", function() { return idValue; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "idInnerHTML", function() { return idInnerHTML; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "qSel", function() { return qSel; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "qSelValue", function() { return qSelValue; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "qSelInnerHTML", function() { return qSelInnerHTML; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "log", function() { return log; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "write", function() { return write; });
var id = function id(_id) {
  return document.getElementById(_id);
};
var idValue = function idValue(id) {
  return id(id).value;
};
var idInnerHTML = function idInnerHTML(id) {
  return id(id).innerHTML;
};
var qSel = function qSel(name) {
  return document.querySelector(name);
};
var qSelValue = function qSelValue(name) {
  return qSel(name).value;
};
var qSelInnerHTML = function qSelInnerHTML(name) {
  return qSel(name).innerHTML;
};
var log = function log(id) {
  return console.log(id);
};
var write = function write(input) {
  return document.write(input);
};

/***/ }),

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./global */ "./resources/asset/js/global.js");
/* harmony import */ var _components_register_index__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/register/index */ "./resources/asset/js/components/register/index.js");
/* harmony import */ var _components_login_index__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/login/index */ "./resources/asset/js/components/login/index.js");
/* harmony import */ var _components_register_processForm__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/register/processForm */ "./resources/asset/js/components/register/processForm.js");
/* harmony import */ var _components_register_modal_kids__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/register/modal/kids */ "./resources/asset/js/components/register/modal/kids.js");
/* harmony import */ var _components_register_modal_siblings__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/register/modal/siblings */ "./resources/asset/js/components/register/modal/siblings.js");
/* harmony import */ var _cust_main__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./cust/main */ "./resources/asset/js/cust/main.js");
/* harmony import */ var _cust_main__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_cust_main__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _components_profilePage__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./components/profilePage */ "./resources/asset/js/components/profilePage.js");
/* harmony import */ var _components_profilePage__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_components_profilePage__WEBPACK_IMPORTED_MODULE_7__);


 //import "./components/small-Input"





 // import "./components/modal/profile"

if (Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('profilePage')) {
  __webpack_require__.e(/*! import() */ 0).then(__webpack_require__.bind(null, /*! ./components/modal/profile */ "./resources/asset/js/components/modal/profile.js")).then(function (result) {
    return result;
  });
} else if (Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('registration')) {
  __webpack_require__.e(/*! import() */ 1).then(__webpack_require__.bind(null, /*! ./components/small-Input */ "./resources/asset/js/components/small-Input.js")).then(function (result) {
    return result;
  });
}

/***/ }),

/***/ "./resources/asset/scss/main.scss":
/*!****************************************!*\
  !*** ./resources/asset/scss/main.scss ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!****************************************************************************!*\
  !*** multi ./resources/asset/js/index.js ./resources/asset/scss/main.scss ***!
  \****************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Applications/MAMP/htdocs/familyPlatform/resources/asset/js/index.js */"./resources/asset/js/index.js");
module.exports = __webpack_require__(/*! /Applications/MAMP/htdocs/familyPlatform/resources/asset/scss/main.scss */"./resources/asset/scss/main.scss");


/***/ })

/******/ });