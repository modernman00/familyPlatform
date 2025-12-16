"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["accountSetting"],{

/***/ "./resources/asset/js/components/accountSetting.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/accountSetting.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _register_onChangeKidSibling__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./register/onChangeKidSibling */ "./resources/asset/js/components/register/onChangeKidSibling.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _FormHelper__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FormHelper */ "./resources/asset/js/components/FormHelper.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _kidsAndSiblings__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./kidsAndSiblings */ "./resources/asset/js/components/kidsAndSiblings.js");
/* harmony import */ var _api_index__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./api/index */ "./resources/asset/js/components/api/index.js");








var formInput = document.querySelectorAll('.accountSettingForm');
var formInputArr = Array.from(formInput);
var formData = new _FormHelper__WEBPACK_IMPORTED_MODULE_2__["default"](formInputArr);
var options = {
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
};
var process = function process(e) {
  try {
    e.preventDefault();
    var notificationDiv = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('accountSettingForm_notification');
    var notificationMsg = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('accountSettingForm_notification_error');
    notificationMsg.innerHTML = ""; // may not be needed
    formData.massValidate();
    // log(formData.error)
    if (formData.error.length <= 0) {
      // get the form data
      var eventForm = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('accountSettingForm');
      var eventFormEntries = new FormData(eventForm);
      // post the form data to the database and get the last posted event no
      axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/accountSetting", eventFormEntries, options).then(function (response) {
        notificationDiv.style.display = "block"; // unblock the notification
        notificationDiv.classList.add('is-success'); // add the success class
        notificationMsg.innerHTML = response.data.message;
      });
      // window.location.replace("/member/profilePage")
    } else {
      alert('The form cannot be submitted. Please check the errors');
      formData.clearError();
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  }
};

// show spouse once select is Yes
// Add event listeners
// Function to show spouse information based on marital status
var showSpouse = function showSpouse() {
  // Get the value of the marital status dropdown
  var maritalStatus = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('maritalStatus_id').value;

  // Check marital status and show relevant elements
  if (maritalStatus === "Yes - Add Husband") {
    // Display spouse section if adding husband
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseName_id', 'set', 'name', 'spouseName');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseEmail_id', 'set', 'name', 'spouseEmail');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMobile_id', 'set', 'name', 'spouseMobile');
  } else if (maritalStatus === "Yes - Add Wife") {
    // Display spouse section if adding wife
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouseMaidenName_div');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.showElement)('spouse');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseName_id', 'set', 'name', 'spouseName');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseEmail_id', 'set', 'name', 'spouseEmail');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMobile_id', 'set', 'name', 'spouseMobile');
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMaidenName_id', 'set', 'name', 'spouseMaidenName');
    // Display maiden name and spouse sections if adding wife
  } else {
    // Hide spouse section if marital status is not "Yes"
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouse');
  }
};

// Add event listener to marital status dropdown to trigger showSpouse function
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('maritalStatus_id').addEventListener('change', showSpouse);

// Hide spouse and maiden name elements by default
(0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouse');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.hideElement)('spouseMaidenName_div');

// remove input name attritube by default
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseName_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMaidenName_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseEmail_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.manipulateAttribute)('spouseMobile_id', 'remove', 'name');
(0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('submit').addEventListener('click', process);
// GET ALL EMAILS 

// Call the fetchData function to initiate the request

// const emailData = []
var fName = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('fName').textContent;
var lastName = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('lName').textContent;
var famCode = localStorage.getItem('requesterFamCode');
(0,_api_index__WEBPACK_IMPORTED_MODULE_5__.fetchEmailData)().then(function (data) {
  // Do something with the fetched data
  var emailData = data;

  // SEND EMAIL TO THE KIDS AND processKidsSibling
  (0,_kidsAndSiblings__WEBPACK_IMPORTED_MODULE_4__.processKidsSiblings)(emailData, fName, lastName, famCode);
}).catch(function (error) {
  // Handle any errors that occurred during the request or processing
  console.error('Error:', error);
});

// Call the getEmailData function somewhere in your code

/***/ }),

/***/ "./resources/asset/js/components/api/index.js":
/*!****************************************************!*\
  !*** ./resources/asset/js/components/api/index.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   fetchEmailData: function() { return /* binding */ fetchEmailData; },
/* harmony export */   getAllData: function() { return /* binding */ getAllData; },
/* harmony export */   postData: function() { return /* binding */ postData; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




var config = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
var URL = "https://olaogun.test/";
// https: //laravel.com/docs/5.4/mix#environment-variables

var getAllData = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var response, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          _context.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("".concat(URL, "allMembers/processApiData2"), config);
        case 1:
          response = _context.sent;
          return _context.abrupt("return", response.data);
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t);
          // You can perform additional error handling actions if needed
          throw _t;
        case 3:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 2]]);
  }));
  return function getAllData() {
    return _ref.apply(this, arguments);
  };
}();
var postData = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(url, object) {
    var response, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post(url, object);
        case 1:
          response = _context2.sent;
          console.log(response);
          _context2.next = 3;
          break;
        case 2:
          _context2.prev = 2;
          _t2 = _context2["catch"](0);
          (0,_global__WEBPACK_IMPORTED_MODULE_3__.showError)(_t2);
          // You can perform additional error handling actions if needed
          throw _t2;
        case 3:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return function postData(_x, _x2) {
    return _ref2.apply(this, arguments);
  };
}();
var fetchEmailData = function fetchEmailData() {
  // Make a GET request and return the promise
  return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get("".concat(URL, "getEmails")).then(function (response) {
    // Extract the data from the response
    var data = response.data;
    // Return the data or do further processing
    return data;
  }).catch(function (error) {
    // Handle any errors that occur during the request
    console.error('Error fetching data:', error);
    throw error; // Rethrow the error to be handled by the caller
  });
};

/***/ }),

/***/ "./resources/asset/js/components/kidsAndSiblings.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/kidsAndSiblings.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   processKidsSiblings: function() { return /* binding */ processKidsSiblings; }
/* harmony export */ });
/* harmony import */ var _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../data/kidsSibling */ "./resources/asset/js/data/kidsSibling.js");
/* harmony import */ var _components_global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");




var processKidsSiblings = function processKidsSiblings(checkEmailExists, firstName, lastName) {
  var famCode = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
  var chooseEmail = [];
  var chooseName = [];
  var helpHTML = "";
  // let errorHTML = ""; // Show error if applicant's email is registered

  document.onkeydown = function (e) {
    //. use the onclick to get the id of the element that was clicked
    // 2. use event listener to get the email value (if it is not empty)
    // 3. use the email value to check if it is in the array
    // 4. if it is in the array, show the Yes or No Radio
    // 5. click yes to send email to the kid or sibling

    try {
      // create an object with the data to check
      var elementId = e.target.id; // id of the element that was clicked or press down
      var emailInput = e.target.value;
      // this phase checks the id of what is being typed
      if (!elementId) throw new Error("target id is null and empty");

      // if the elementId indicate that it is a kid, then choosemail inherits all the kids array from the checkEmailObj and vis a versa

      if (_data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidEmailInput.includes(elementId)) {
        chooseEmail = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidEmailInput;
        chooseName = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.kidNameInput;
        helpHTML = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "_help"));
      } else if (_data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingEmail.includes(elementId)) {
        chooseEmail = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingEmail;
        chooseName = _data_kidsSibling__WEBPACK_IMPORTED_MODULE_0__.checkEmailObj.siblingName;
        helpHTML = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "_help"));
      }
      var checkFamilyEmail = function checkFamilyEmail() {
        //this checks the value of what is being typed

        helpHTML.innerHTML = emailInput.length > 5 && emailInput.length < 7 ? "Email may be too small" : "";

        // use the elementid to find the exact email value and name value
        var index = chooseEmail.indexOf(elementId);
        var email = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)(chooseEmail[index]);
        var emailValue = email.value;
        var name = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)(chooseName[index]);
        var nameValue = name.value;

        // if (!emailValue)
        //     throw new Error("another round of email is empty");
        // if (!nameValue) throw new Error("name is empty");
        // if (!getData.length) throw new Error("data is faulty");
        // checking family email 
        helpHTML.style.display = "block";
        helpHTML.innerHTML = checkEmailExists.includes(emailInput) ? "Great news! ".concat(nameValue, " is already registered on the platform") : "".concat(nameValue, " is not on the platform.Do you want us to send ").concat(nameValue, " a email to register to the platform ? ").concat((0,_helper_general__WEBPACK_IMPORTED_MODULE_2__.checkBox)(elementId));

        // send the email to family membersa

        var setFamCode;
        var famCodeElement = (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)('famCode_id');
        if (famCodeElement) {
          setFamCode = famCodeElement.value;
        } else {
          // Handle the case where the element is not found or not yet loaded
          setFamCode = famCode; // Use a default value (famCode) or handle the situation accordingly
        }
        var processKidRadio = function processKidRadio() {
          var postObj = {
            mobile: "",
            // is this needed?
            viewPath: "msg/contactNewMember",
            data: {
              email: emailValue,
              name: nameValue,
              yourName: "".concat(firstName, " ").concat(lastName),
              familyCode: setFamCode
            },
            subject: "".concat(firstName, " ").concat(lastName, " Wants You: Experience the Magic of your Family Network Today!")
          };
          axios__WEBPACK_IMPORTED_MODULE_3__["default"].post("/register/contactNewMember", postObj).then(function (response) {
            helpHTML.innerHTML = response.data.message;
            setTimeout(function () {
              helpHTML.style.display = "none";
            }, 5000);
          }).catch(function (error) {
            (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
          });
        };
        (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "Yes")).addEventListener("click", processKidRadio);
        (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "No")).addEventListener("click", function () {
          return (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.id)("".concat(elementId, "No")).style.display = "none";
        });
      };
      if (chooseEmail.includes(elementId)) {
        checkFamilyEmail();

        // id(elementId).addEventListener("keyup", checkFamilyEmail);
      }
    } catch (error) {
      (0,_components_global__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
    }
  };
};

/***/ }),

/***/ "./resources/asset/js/components/register/html/kids_Sibling.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/register/html/kids_Sibling.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtmlFamily: function() { return /* binding */ renderHtmlFamily; }
/* harmony export */ });
var renderHtmlFamily = function renderHtmlFamily(family, no) {
  if (no) {
    var kids_sib = family == "addChildren" ? "kid" : "sibling";
    var optionsHtml = "\n      <option value='Choose'>Choose</option>\n      <option value='With Spouse'>With Spouse</option>\n      <option value='Not With Spouse'>Not With Spouse</option>\n    ";
    if (family === "sibling") {
      optionsHtml = "\n                <option value='Choose'>Choose</option>\n                <option value='Same_Mother_Father'>Same Mother & Father</option>\n                <option value='Same_Father'>Only Same Father</option>\n                <option value='Same_Mother'>Only Same Mother</option>";
    }
    return "\n            <div class=\"field-body\">\n                <div class=\"field\">\n                    <p class=\"control is-expanded\">\n                        <span class=\"select is-normal is-success is-fullwidth\">\n                            <select name=\"".concat(kids_sib, "_option").concat(no, "\" id=\"").concat(kids_sib, "_option").concat(no, "\">\n                                ").concat(optionsHtml, "\n                            </select>\n                        </span>\n                    </p>\n                </div>\n\n                <div class=\"field\">\n                    <p class=\"control is-expanded\">\n                        <input type=\"text\" placeholder = \"Enter ").concat(kids_sib, "'s full name - ").concat(no, "\"  name =").concat(kids_sib, "_name").concat(no, " class=\"input input is-normal \" id=\"").concat(kids_sib, "_name").concat(no, "\">\n                    </p>\n                </div>          \n\n                <div class=\"field\">\n                    <p class=\"control is-expanded has-icons-left\">\n                        <input type=\"email\" placeholder = \"Enter ").concat(kids_sib, "'s email - ").concat(no, "\" value = \"Please, provide email address\" name=").concat(kids_sib, "_email").concat(no, " class=\"input input is-normal \" id=\"").concat(kids_sib, "_email").concat(no, "\">\n\n                        <span class=\"icon is-small is-left\">\n                            <i class=\"fas fa-envelope\"></i>\n                        </span>\n                    </p>\n                    <p class=\"help is-danger\" id=\"").concat(kids_sib, "_email").concat(no, "_help\"></p>\n                </div>\n\n            </div>");
  }
};

/***/ }),

/***/ "./resources/asset/js/components/register/onChangeKidSibling.js":
/*!**********************************************************************!*\
  !*** ./resources/asset/js/components/register/onChangeKidSibling.js ***!
  \**********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   show: function() { return /* binding */ show; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_general__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/general */ "./resources/asset/js/components/helper/general.js");
/* harmony import */ var _html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./html/kids_Sibling */ "./resources/asset/js/components/register/html/kids_Sibling.js");


// import { getEnvironmentVariable as env} from 'environment-variable-reader'




// let childrenOnchangeValue = 0;
// let childrenOnchangeValue = 0;

var show = function show(kids_or_sib, event) {
  try {
    // what was picked or selected
    var value = Number(event.target.value) || 0;

    // childrenOnchangeValue = value;
    var addDiv = kids_or_sib == "children" ? "children" : "sibling";

    // remove the div 
    (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.removeDiv)(addDiv);
    if (value == 0) {
      (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help")).innerHTML = "";
    }
    if (value > 0) {
      // create and append the div element 
      var parent = "".concat(kids_or_sib, "_div");
      (0,_helper_general__WEBPACK_IMPORTED_MODULE_1__.createAndAppendElement)('div', addDiv, parent);
      // use the loop to generate the number of input
      for (var i = 0; i < value; i++) {
        var no = i + 1;
        var msg = no > 1 ? "Please, enter their names and emails below" : "Please, enter the name and email below";
        var getSelectHelp = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("".concat(kids_or_sib, "_help"));
        getSelectHelp.innerHTML = msg;
        getSelectHelp.style.fontSize = '1rem';
        getSelectHelp.style.color = '#fc2003';
        var html = (0,_html_kids_Sibling__WEBPACK_IMPORTED_MODULE_2__.renderHtmlFamily)(addDiv, no);
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)(addDiv).insertAdjacentHTML('beforeEnd', html);
      }
    }
  } catch (error) {
    (0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(error);
  }
};

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
var onChangeKidAndSiblings = function onChangeKidAndSiblings() {
  var kidInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("children_id");
  var sibInput = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)("sibling_id");
  if (kidInput) kidInput.addEventListener('change', function (event) {
    return show('children', event);
  });
  if (sibInput) sibInput.addEventListener('change', function (event) {
    return show('sibling', event);
  });
};
onChangeKidAndSiblings();

/***/ }),

/***/ "./resources/asset/js/data/kidsSibling.js":
/*!************************************************!*\
  !*** ./resources/asset/js/data/kidsSibling.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkEmailObj: function() { return /* binding */ checkEmailObj; }
/* harmony export */ });
var checkEmailObj = {
  kidEmailInput: ["kid_email1", "kid_email2", "kid_email3", "kid_email4", "kid_email5", "kid_email6", "kid_email7", "kid_email8", "kid_email9", "kid_email10"],
  kidNameInput: ["kid_name1", "kid_name2", "kid_name3", "kid_name4", "kid_name5", "kid_name6", "kid_name7", "kid_name8", "kid_name9", "kid_name10"],
  siblingEmail: ["sibling_email1", "sibling_email2", "sibling_email3", "sibling_email4", "sibling_email5", "sibling_email6", "sibling_email7", "sibling_email8", "sibling_email9", "sibling_email10"],
  siblingName: ["sibling_name1", "sibling_name2", "sibling_name3", "sibling_name4", "sibling_name5", "sibling_name6", "sibling_name7", "sibling_name8", "sibling_name9", "sibling_name10"]
};

/***/ })

}]);
//# sourceMappingURL=accountSetting.js.map