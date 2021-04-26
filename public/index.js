(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/index"],{

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


            if (post.type == 'submit' || post.name == 'token' || postName == "spouseName" || postName == "spouseMobile") {
              continue;
            } // check if there is no value


            var postName = post.name.replace('_', ' ');

            if (postName == "spouseName" || postName == "spouseMobile" || postName == "fatherMobile" || postName == "motherMobile") {
              if (post.value === "") {
                post.value = "11";
              }
            }

            if (post.value === '' || post.value === 'select') {
              errMsg.innerHTML = "<li style=color:'red';>".concat(postName, " cannot be left empty</li>");

              _this.error.push("<li style=color:'red';>".concat(postName, " cannot be left empty</li>"));
            } else if (post.value.match(reg) === null) {
              errMsg.innerHTML = "<li style=color:'red';> only letters and numbers are allowed<li>";

              _this.error.push("<li style=color:'red';> only letters and numbers are allowed<li>");
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
      var msg = "<li style=color:'red';> Please enter a valid email</li>";
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

/***/ "./resources/asset/js/components/hidden.js":
/*!*************************************************!*\
  !*** ./resources/asset/js/components/hidden.js ***!
  \*************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/global.js");


try {
  // id('spouse_div').style.display="none";
  Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "none";
  Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('id_event_modal').style.display = "none"; // id("firstName_help").innerHTML = 
} catch (error) {
  Object(_global__WEBPACK_IMPORTED_MODULE_0__["log"])(error.message);
}

/***/ }),

/***/ "./resources/asset/js/components/login/submitFormLogin.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/login/submitFormLogin.js ***!
  \****************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");
/* harmony import */ var _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/dataToCheck */ "./resources/asset/js/data/dataToCheck.js");





var formInput = document.querySelectorAll('.login');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_0__["default"](formInputArr);

var process = function process() {
  // clear error from the form
  formData.clearError(); // set the maxlength, check the length of the value, raise error

  formData.realTimeCheckLen(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheckLogin"].maxLength.id, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheckLogin"].maxLength.max);
};

process();

var processFormSubmission = function processFormSubmission() {
  try {
    console.log('it worked');
    formData.emailVal(); // sanitise email

    formData.massValidate(); // validate and sanitise data

    Object(_global__WEBPACK_IMPORTED_MODULE_1__["log"])(formData.error);
    Object(_global__WEBPACK_IMPORTED_MODULE_1__["log"])("it worked");

    if (formData.error.length <= 0) {
      Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').type = 'submit'; //console.log('submitted')
    } else {
      Object(_global__WEBPACK_IMPORTED_MODULE_1__["log"])(formData.error);
      alert('The form cannot be submitted. Please check the errors');
      process();
    }
  } catch (e) {
    Object(_global__WEBPACK_IMPORTED_MODULE_1__["showError"])(e);
  }
};

var testAlert = function testAlert() {
  return alert('it worked login');
};

document.querySelector('.button').addEventListener('click', testAlert);

/***/ }),

/***/ "./resources/asset/js/components/modal/profile.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/modal/profile.js ***!
  \********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");


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
    // what was picked or selected
    var kidsNo = e.target.value; // use the loop to generate the number of input

    for (var i = 0; i < kidsNo; i++) {
      var no = i + 1;
      var msg = no > 1 ? "Please, enter their names and emails" : "Please, enter your child name and email";
      var getSelectHelp = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids_help');
      getSelectHelp.innerHTML = msg;
      getSelectHelp.style.fontSize = '1rem';
      var addKids = " <div class=\"field is-horizontal\">\n            <div class=\"field \">\n        \n            <div class=\"control is-expanded has-icons-left\">\n            <input type=\"text\" placeholder = \"Enter child's full name - ".concat(no, "\" name =kid_name").concat(no, " class=\"input input is-medium\" id=\"kid_name").concat(no, "\">\n            </div></div>\n            <div class=\"field \">\n            <div class=\"control is-expanded has-icons-left\">\n           <input type=\"email\" placeholder = \"Enter child's email - ").concat(no, "\" name=kid_email").concat(no, " class=\"input input is-medium\" id=\"kid_email").concat(no, "\">\n           </div>\n        </div></div><br>");
      var insertedContent = document.querySelector(".kid".concat(no));

      if (insertedContent) {
        insertedContent.parentNode.removeChild(insertedContent);
      }

      Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('addChildren').insertAdjacentHTML('afterend', addKids);
    }
  } catch (error) {
    console.log(error.message);
  }
}; // this is to activate the onchange event


Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids_id').addEventListener('change', show);

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

  formData.realTimeCheckLen(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheckRegister"].maxLength.id, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheckRegister"].maxLength.max); //real time check 

  formData.realTimeServer('spouseMobile_id', "/search?attribute=spouseMobile&subject=spouse&hint", 'spouseMobile_error');
  formData.realTimeServer('fatherMobile_id', '/search?attribute=fatherMobile&subject=father&hint', 'fatherMobile_error');
  formData.realTimeServer('motherMobile_id', '/search?attribute=motherMobile&subject=mother&hint', 'motherMobile_error'); // check if password matches real time

  formData.matchInput(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheckRegister"].password.pwd, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheckRegister"].password.pwd2 // dataToCheckRegister.password.err
  ); // check if they have a father yes
  // formData.isChecked(dataToCheckRegister.familyCheck.father[0],
  // 	dataToCheckRegister.familyCheck.father[1],
  // 	'fatherEmail_error'
  // )
  // // check if they have a mother yes
  // formData.isChecked(dataToCheckRegister.familyCheck.mother[0],
  // 	dataToCheckRegister.familyCheck.mother[1],
  // 	'motherEmail_error'
  // )
  // // check if they have a spouse yes
  // formData.isChecked(dataToCheckRegister.familyCheck.spouse[0],
  // 	dataToCheckRegister.familyCheck.spouse[1],
  // 	'spouseEmail_error'
  // )
};

process();
Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').addEventListener('click', function () {
  try {
    if (Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('checkbox').checked) {
      formData.emailVal(); // sanitise email

      formData.massValidate(); // validate and sanitise data
      //log(formData.error)

      if (formData.error.length <= 0) {
        Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').type = 'submit'; //console.log('submitted')
      } else {
        Object(_global__WEBPACK_IMPORTED_MODULE_1__["log"])(formData.error);
        alert('The form cannot be submitted. Please check the errors');
        process();
      }
    } else {
      alert('To continue, you need to agree to the Olaoguns handling your information as outlined in our privacy policy');
    }
  } catch (e) {
    Object(_global__WEBPACK_IMPORTED_MODULE_1__["showError"])(e);
  }
});

/***/ }),

/***/ "./resources/asset/js/components/smallInput.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/smallInput.js ***!
  \*****************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/global.js");

var maiden = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('motherMaiden_help');
maiden.innerHTML = "Good to identify your family from mum's side";
var mobile = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('mobile_help');
mobile.innerHTML = "Nigeria: 2348036517179, UK: 447871717809";
var password = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('password_help');
password.innerHTML = 'Must be 8-20 characters long.';
Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse').style.display = "none";
Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('children2').style.display = "none";

var showSpouse = function showSpouse(e) {
  if (e.target.value === "Yes") {
    Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse').style.display = "block";
  } else {
    Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse').style.display = "none";
  }
};

Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('maritalStatus_id').addEventListener('change', showSpouse);

/***/ }),

/***/ "./resources/asset/js/data/dataToCheck.js":
/*!************************************************!*\
  !*** ./resources/asset/js/data/dataToCheck.js ***!
  \************************************************/
/*! exports provided: dataToCheckRegister, dataToCheckLogin */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "dataToCheckRegister", function() { return dataToCheckRegister; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "dataToCheckLogin", function() { return dataToCheckLogin; });


var dataToCheckRegister = {
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
var dataToCheckLogin = {
  maxLength: {
    id: ['email', 'password'],
    max: [20, 15],
    min: [5, 2]
  }
};

/***/ }),

/***/ "./resources/asset/js/data/server/AllMembers.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/data/server/AllMembers.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");
// import axios from 'axios'
 // axios.get('/allMembers')
// .then((response) => {
//     log(response)
// })
// .catch((error)=> {
//     console.log(error)
// })

document.getElementById('searchFamily').addEventListener('keyup', function (e) {
  return console.log(e.target.value);
});

/***/ }),

/***/ "./resources/asset/js/global.js":
/*!**************************************!*\
  !*** ./resources/asset/js/global.js ***!
  \**************************************/
/*! exports provided: id, idValue, idInnerHTML, qSel, qSelValue, qSelInnerHTML, log, write, showError */
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
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "showError", function() { return showError; });
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
var showError = function showError(e) {
  console.log(e instanceof TypeError); // true

  console.log(e.message); // "null has no properties"

  console.log(e.name); // "TypeError"

  console.log(e.fileName); // "Scratchpad/1"

  console.log(e.lineNumber); // 2

  console.log(e.columnNumber); // 2

  console.log(e.stack);
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
/* harmony import */ var _components_smallInput__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/smallInput */ "./resources/asset/js/components/smallInput.js");
/* harmony import */ var _components_register_processForm__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/register/processForm */ "./resources/asset/js/components/register/processForm.js");
/* harmony import */ var _components_register_modal_kids__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/register/modal/kids */ "./resources/asset/js/components/register/modal/kids.js");
/* harmony import */ var _components_register_modal_siblings__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/register/modal/siblings */ "./resources/asset/js/components/register/modal/siblings.js");
/* harmony import */ var _data_server_AllMembers__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./data/server/AllMembers */ "./resources/asset/js/data/server/AllMembers.js");
/* harmony import */ var _components_profilePage__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/profilePage */ "./resources/asset/js/components/profilePage.js");
/* harmony import */ var _components_profilePage__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_components_profilePage__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _components_modal_profile__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./components/modal/profile */ "./resources/asset/js/components/modal/profile.js");
/* harmony import */ var _components_hidden__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components/hidden */ "./resources/asset/js/components/hidden.js");
/* harmony import */ var _components_login_submitFormLogin__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./components/login/submitFormLogin */ "./resources/asset/js/components/login/submitFormLogin.js");


 // import "./components/register/index"
//import "./components/login/index"





 // import "./components/search"
// import "./components/submitForm"

 //import "./components/FilePreview"
//import "./components/organogram"



 // if(window.location.pathname === '/register') {
//     console.log("it worked well")
//      import(
//          /* webpackChunkName: '/codeSplit/smallInput' */ 
//         /* webpackPrefetch: true */
//         './components/smallinput')
//         .then((module)=> module.default())
//         .catch((err)=> console.log("MAD ERROR!! " + err.message))
// }
// CODE SPLITTING BASED ON ROUTE

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

},[[0,"/manifest","/vendor"]]]);