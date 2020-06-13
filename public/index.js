/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/asset/js/components/formBuilder.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/formBuilder.js ***!
  \******************************************************/
/*! exports provided: Input, Select */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Input", function() { return Input; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Select", function() { return Select; });
/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */
var Input = function Input(objData, htmlId) {
  objData.map(function (element) {
    if (element.type != 'select' && !element.options) {
      var renderHtml = "\n             <label for =".concat(element.attribute, "> ").concat(element.label, "</label>\n            <input type=\"").concat(element.type, "\" class=\"form-control\" id=\"").concat(element.attribute, "\" name=\"").concat(element.attribute, "\" placeholder=\"").concat(element.placeholder, "\"/>\n            <small id =").concat(element.attribute, "-small></small><br>\n          ");
      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml);
    } else if (element.type === 'select') {
      var _renderHtml = "<label for =".concat(element.attribute, "> ").concat(element.label, "</label>   \n          <select class=\"form-control\" id=").concat(element.attribute, ">\n                <option>SELECT</option> \n                ").concat(element.options.map(function (el) {
        return "<option>".concat(el, "</option>");
      }), "             \n             \n          </select>     \n           <small id =").concat(element.attribute, "-small></small><br>\n          ");

      document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml);
    }
  });
};
var Select = function Select(arg, htmlId) {
  arg.forEach(function (element) {
    var renderHtml = "\n     <label>".concat(element.label, "</label>\n     <select class=form-group>\n     <option value>Select</option>\n     ").concat(arg.options.forEach(function (el) {
      return "<option value =".concat(el, ">").concat(el, "</option>");
    }), "\n     </select>");
    document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml);
  });
};

/***/ }),

/***/ "./resources/asset/js/components/login/login.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/login/login.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _data_Login__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../data/Login */ "./resources/asset/js/data/Login.js");
/* harmony import */ var _formBuilder__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./../formBuilder */ "./resources/asset/js/components/formBuilder.js");



try {
  Object(_formBuilder__WEBPACK_IMPORTED_MODULE_1__["Input"])(_data_Login__WEBPACK_IMPORTED_MODULE_0__["Login"], 'login');
} catch (error) {
  console.log(error);
}

/***/ }),

/***/ "./resources/asset/js/components/register/register.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/register/register.js ***!
  \************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _data_Personal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../data/Personal.js */ "./resources/asset/js/data/Personal.js");
/* harmony import */ var _data_Contact_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../data/Contact.js */ "./resources/asset/js/data/Contact.js");
/* harmony import */ var _formBuilder_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../formBuilder.js */ "./resources/asset/js/components/formBuilder.js");




try {
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_2__["Input"])(_data_Contact_js__WEBPACK_IMPORTED_MODULE_1__["Contact"], 'contact');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_2__["Input"])(_data_Personal_js__WEBPACK_IMPORTED_MODULE_0__["Personal"], 'personal'); //  InputSelect(Contact, 'contact')
} catch (e) {
  console.log(e);
}

/***/ }),

/***/ "./resources/asset/js/components/small-Input.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/small-Input.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../global */ "./resources/asset/js/global.js");

var fatherName = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('fatherName-small');
fatherName.innerHTML = "Please, include your father's first and last name";
fatherName.style.color = 'blue';
fatherName.style.fontSize = '0.5rem';

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
  label: 'Home Address?',
  attribute: 'address',
  placeholder: 'Please, enter your first line of address',
  type: 'text'
}, {
  label: 'Postcode?',
  attribute: 'postcode',
  placeholder: 'SN55DE',
  type: 'number'
}, {
  label: 'Email',
  attribute: 'email',
  placeholder: 'olutobi@gmail.com',
  type: 'email'
}, {
  label: 'Country',
  attribute: 'region',
  placeholder: 'Your country of residence',
  type: 'text'
}];

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
  label: 'Username',
  attribute: 'username',
  placeholder: 'Your username will be mainly your email',
  type: 'text'
}, {
  label: 'password',
  attribute: 'password',
  placeholder: 'SN55DE',
  type: 'password'
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
  label: 'What is your name?',
  attribute: 'firstName',
  placeholder: 'James',
  type: 'text'
}, {
  label: 'What is your last name?',
  attribute: 'lastName',
  placeholder: 'Edwards',
  type: 'text'
}, {
  label: "Father's name?",
  attribute: 'fatherName',
  placeholder: 'Your Father name and last name',
  type: 'text'
}, {
  label: "Mother's name?",
  attribute: 'motherName',
  placeholder: 'Your Mother name and last name',
  type: 'text'
}, {
  label: "date?",
  attribute: 'date',
  placeholder: 'date',
  type: 'date'
}, {
  label: "Kids",
  attribute: 'kids',
  placeholder: null,
  type: 'select',
  options: [0, 1, 2, 3, 4, 5, 6]
}];

/***/ }),

/***/ "./resources/asset/js/global.js":
/*!**************************************!*\
  !*** ./resources/asset/js/global.js ***!
  \**************************************/
/*! exports provided: id, idValue, idInnerHTML, name, nameValue, nameInnerHTML, log, write */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "id", function() { return id; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "idValue", function() { return idValue; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "idInnerHTML", function() { return idInnerHTML; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "name", function() { return name; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "nameValue", function() { return nameValue; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "nameInnerHTML", function() { return nameInnerHTML; });
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
var name = function name(_name) {
  return document.querySelector(_name);
};
var nameValue = function nameValue(name) {
  return name(name).value;
};
var nameInnerHTML = function nameInnerHTML(name) {
  return name(name).innerHTML;
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
/* harmony import */ var _components_register_register__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/register/register */ "./resources/asset/js/components/register/register.js");
/* harmony import */ var _components_login_login__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/login/login */ "./resources/asset/js/components/login/login.js");
/* harmony import */ var _components_small_Input__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/small-Input */ "./resources/asset/js/components/small-Input.js");





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

__webpack_require__(/*! C:\xampp\htdocs\familyPlatform\resources\asset\js\index.js */"./resources/asset/js/index.js");
module.exports = __webpack_require__(/*! C:\xampp\htdocs\familyPlatform\resources\asset\scss\main.scss */"./resources/asset/scss/main.scss");


/***/ })

/******/ });