(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["codeSplit/changePW"],{

/***/ "./resources/asset/js/components/forgotPwd/changePW.js":
/*!*************************************************************!*\
  !*** ./resources/asset/js/components/forgotPwd/changePW.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

Promise.all(/*! import() */[__webpack_require__.e("/vendor"), __webpack_require__.e("resources_asset_js_components_helper_general_js")]).then(__webpack_require__.bind(__webpack_require__, /*! ../helper/general */ "./resources/asset/js/components/helper/general.js")).then(function (res) {
  return res.matchInput('password_id', 'confirm_password_id', 'changePasswordErr');
});
Promise.resolve(/*! import() */).then(__webpack_require__.bind(__webpack_require__, /*! ../global */ "./resources/asset/js/components/global.js")).then(function (response) {
  response.id("setLoader").style.display = "none";

  var submitChangePW = function submitChangePW(e) {
    try {
      e.preventDefault();
      var password = response.id('password_id').value; // just in case there was an earlier error notification - remove it

      response.id('changePassword_notification').classList.remove('is-danger');
      response.id('error').innerHTML = "";

      if (password !== null) {
        response.id("setLoader").style.display = "block";
        response.id('loader').classList.add('loader');
        Promise.resolve(/*! import() */).then(__webpack_require__.bind(__webpack_require__, /*! ../helper/http */ "./resources/asset/js/components/helper/http.js")).then(function (res) {
          return res.postFormData("/login/changePW", "changePassword", "/login");
        }); // window.location.replace("/login")
      }
    } catch (error) {
      response.showError(error);
    }
  };

  response.id('submit').addEventListener('click', submitChangePW);
});

/***/ }),

/***/ "./resources/asset/js/components/helper/http.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/helper/http.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "postFormData": () => (/* binding */ postFormData),
/* harmony export */   "getApiData": () => (/* binding */ getApiData),
/* harmony export */   "getMultipleApiData": () => (/* binding */ getMultipleApiData),
/* harmony export */   "setCookie": () => (/* binding */ setCookie),
/* harmony export */   "getCookie": () => (/* binding */ getCookie),
/* harmony export */   "checkCookie": () => (/* binding */ checkCookie)
/* harmony export */ });
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var axios_retry__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios-retry */ "./node_modules/axios-retry/lib/esm/index.js");


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }



 // import Cookies from 'js-cookie'

(0,axios_retry__WEBPACK_IMPORTED_MODULE_3__["default"])((axios__WEBPACK_IMPORTED_MODULE_2___default()), {
  retries: 3
});
/**
 * 
 * @param {the url to post the data to} url 
 * @param {* the id or class of the form} formElement 
 * @param {* the redirect to another page /code or /admin/register} redirect 
 NOTICE:::Make sure you set the notification id as the formId_notification
 */

var postFormData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee(url, formId) {
    var redirect,
        css,
        notificationId,
        processFormDataAction,
        addClassByCSS,
        form,
        formEntries,
        options,
        _args = arguments;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            redirect = _args.length > 2 && _args[2] !== undefined ? _args[2] : null;
            css = _args.length > 3 && _args[3] !== undefined ? _args[3] : null;
            notificationId = "".concat(formId, "_notification"); // the notification function

            processFormDataAction = function processFormDataAction(addClass, data) {
              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(notificationId).style.display = "block"; // unblock the notification

              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(notificationId).classList.add(addClass); // add the success class

              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('error').innerHTML = data; // error element

              (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('loader').classList.remove('loader'); // remove loader
            };

            addClassByCSS = function addClassByCSS(theCss, status) {
              if (theCss === "W3css") {
                return status == 'green' ? "w3-green" : "w3-red";
              } else if (theCss === 'bulma') {
                return status == 'green' ? "is-success" : "is-danger";
              } else {
                return status == 'green' ? "is-success" : "is-danger";
              }
            }; // extract the form entries


            form = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)(formId);
            formEntries = new FormData(form);
            formEntries["delete"]('submit');
            formEntries["delete"]('checkbox_id'); // formEntries.delete('token')

            options = {
              xsrfCookieName: 'XSRF-TOKEN',
              xsrfHeaderName: 'X-XSRF-TOKEN'
            }; // AXIOS POST FUNCTIONALITY

            _context.next = 12;
            return axios__WEBPACK_IMPORTED_MODULE_2___default().post(url, formEntries, options).then(function (response) {
              // TO DECIDE ON THE NOTIFICATION
              var theClass = addClassByCSS(css, 'green');
              processFormDataAction(theClass, response.data.message); // set timer to redirect to the homepage

              // set timer to redirect to the homepage
              if (redirect) {
                setTimeout(function () {
                  //window.location.replace(redirect)
                  window.location.assign(redirect);
                }, 2000);
              } //it clears all the contents
              // formData.clearHtml()

            })["catch"](function (error) {
              (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(error);
              var theClass = addClassByCSS(css, 'red');

              if (error.response) {
                (0,_global__WEBPACK_IMPORTED_MODULE_1__.log)(error.response);
                processFormDataAction(theClass, error.response.data.message);
              }
            });

          case 12:
          case "end":
            return _context.stop();
        }
      }
    }, _callee);
  }));

  return function postFormData(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();
/**
 * 
 * @param { the url you want to get} URL 
 * @returns 
 // now we can use that data from the outside!
axiosTest()
    .then(data => {
        response.json({ message: 'Request received!', data })
    })
    .catch(err => console.log(err))
 */

var getApiData = /*#__PURE__*/function () {
  var _ref2 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee2(URL) {
    var token,
        config,
        fetch,
        _args2 = arguments;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee2$(_context2) {
      while (1) {
        switch (_context2.prev = _context2.next) {
          case 0:
            token = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : null;
            _context2.prev = 1;
            config = {
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
              }
            };
            _context2.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_2___default().get(URL, config);

          case 5:
            fetch = _context2.sent;
            return _context2.abrupt("return", fetch.data);

          case 9:
            _context2.prev = 9;
            _context2.t0 = _context2["catch"](1);
            return _context2.abrupt("return", _context2.t0);

          case 12:
          case "end":
            return _context2.stop();
        }
      }
    }, _callee2, null, [[1, 9]]);
  }));

  return function getApiData(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var getMultipleApiData = /*#__PURE__*/function () {
  var _ref3 = _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().mark(function _callee3(url1, url2) {
    var token,
        config,
        fetch,
        _args3 = arguments;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default().wrap(function _callee3$(_context3) {
      while (1) {
        switch (_context3.prev = _context3.next) {
          case 0:
            token = _args3.length > 2 && _args3[2] !== undefined ? _args3[2] : null;
            _context3.prev = 1;
            config = {
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
              }
            };
            _context3.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_2___default().all([axios__WEBPACK_IMPORTED_MODULE_2___default().get(url1, config), axios__WEBPACK_IMPORTED_MODULE_2___default().get(url2, config)]);

          case 5:
            fetch = _context3.sent;
            return _context3.abrupt("return", fetch);

          case 9:
            _context3.prev = 9;
            _context3.t0 = _context3["catch"](1);
            return _context3.abrupt("return", _context3.t0);

          case 12:
          case "end":
            return _context3.stop();
        }
      }
    }, _callee3, null, [[1, 9]]);
  }));

  return function getMultipleApiData(_x4, _x5) {
    return _ref3.apply(this, arguments);
  };
}();
/**
 * 
 * @param { name} cname 
 * @param {* value} cvalue 
 * @param {* no of days 365} exdays 
 */

var setCookie = function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
};
var getCookie = function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');

  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];

    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }

    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }

  return "";
};
var checkCookie = function checkCookie() {
  var user = getCookie("username");

  if (user != "") {
    alert("Welcome again " + user);
  } else {
    user = prompt("Please enter your name:", "");

    if (user != "" && user != null) {
      setCookie("username", user, 365);
    }
  }
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "html": () => (/* binding */ html),
/* harmony export */   "allPost": () => (/* binding */ allPost),
/* harmony export */   "appendNewPost": () => (/* binding */ appendNewPost),
/* harmony export */   "commentHTML": () => (/* binding */ commentHTML)
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



var timeAgo = function timeAgo(x) {
  return (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x);
};

var name = function name(fullName) {
  return "<h6 id=\"fullName\"><b>".concat(fullName, "</b> </h6>");
};

var postedAt = function postedAt(date) {
  return "<div class=\"timeago w3-right w3-opacity\"  datetime='".concat(date.date_created, "' title='").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created), "'> ").concat(timeAgo(date.post_time), "</div>");
};

var nameImgTiming = function nameImgTiming(data) {
  var img = data.img ? "/img/profile/".concat(data.img) : "/avatar/avatarF.png";
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(data.img, "&pID=").concat(data.post_no, "&path=profile&id=").concat(data.id, "\"> <img src=").concat(img, " alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), " ").concat(name(data.fullName));
};

var commentForm = function commentForm(data) {
  return " <p id=\"formComment".concat(data.post_no, "_notification\"></p>\n\n  <form action=\"/postCommentProfile\" method=\"post\" id=\"formComment").concat(data.post_no, "\" style=\"display:none\" enctype=\"multipart/form-data\">\n\n    <input name='post_no' type=\"hidden\" name=\"").concat(data.post_no, "\" value=").concat(data.post_no, " />\n\n    <input class=\"w3-input w3-border w3-round-large inputComment\" type=\"text\" placeholder=\"Write a comment\"\n      id=\"inputComment").concat(data.post_no, " \" name='comment'>\n\n    <br>\n\n    <button type='submit' id=\"submitComment").concat(data.post_no, "\" class=\"w3-button w3-green submitComment\">Submit</button>\n  </form>");
};

var button = function button(data) {
  return "<button type=\"button\" id=\"likeButton".concat(data.post_no, "\" name=\"").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-green w3-margin-bottom\">\n    <em class=\"fa fa-thumbs-up\"></em>\n    \xA0Like <b><span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">").concat(data.post_likes, "</span></b>\n  </button>\n\n   <button type=\"button\" id=\"initComment").concat(data.post_no, "\"\n    class=\"w3-button w3-tiny w3-theme-d2 w3-margin-bottom\"><em class=\"fa fa-comment\"></em> Comment </button>\n    ");
};

var showPostImg = function showPostImg(data) {
  var postImg = [];

  for (var i = 0; i < 6; i++) {
    var images = 'post_img' + i;

    if (data[images]) {
      postImg.push(data[images]);
    }
  }

  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    return "<a href=\"/profilepage/img?dir=img&pics=".concat(imgElement, "&pID=").concat(postNo, "&path=post\"> <div class=\"w3-half\">\n            <img src=\"/img/post/").concat(imgElement, "\" style=\"width:100%\" alt=\"images").concat(i, "\"\n              class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat(i, "\">\n          </div>\n        </a>");
  };

  return "<div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n\n      ".concat(postImg.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }), "\n        <br>\n      </div>");
};

var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin\"><br>\n\n      ".concat(nameImgTiming(el), "\n\n    <hr class=\"w3-clear\">\n\n    <p class=\"postFont\"> ").concat(el.postMessage, " </p>\n\n     ").concat(showPostImg(el), "\n\n    ").concat(button(el), "\n\n    ").concat(commentForm(el), "\n\n    <div id = 'showComment").concat(el.post_no, "'>\n\n      ").concat(showComment(comment), "\n      \n    </div><br>\n  </div>");
};
var allPost = function allPost(el, commentData) {
  if (!el) {
    return false;
  }

  var postNo = parseInt(el.post_no);
  var filterComment = commentData.filter(function (comm) {
    return postNo === parseInt(comm.post_no);
  }); // filter the comment to an array

  var postHtml = html(el, filterComment);
  (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('beforeend', postHtml);
};
var appendNewPost = function appendNewPost(el) {
  if (!el) {
    return false;
  }

  var commentForm1 = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var inputComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));
  var submitComment = (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)("formComment".concat(el.post_no));

  if (!commentForm1 || !inputComment || !submitComment) {
    var appendHTML = html(el);
    (0,_global__WEBPACK_IMPORTED_MODULE_1__.id)('postIt').insertAdjacentHTML('afterbegin', appendHTML);
  }
};
var commentHTML = function commentHTML(data) {
  var img = data.img ? "/img/profile/".concat(data.img) : "/avatar/avatarF.png";
  return "<div class=\"w3-ul w3-border\" id=\"comment".concat(data.comment_no, "\" name='commentDiv'>\n      <div class=\"w3-container commentDiv\">\n      <img src=\"").concat(img, "\" alt=\"Avatar\" class=\"w3-left w3-circle w3-margin-right commentImg\" style=\"width:60px; height:60px\">\n       <p class=\"w3-right w3-opacity commentTiming\" datetime='").concat(data.date_created, "' title='").concat(data.date_created, "'> ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(data.date_created), " </p> \n         <p class=\"commentFont\"> ").concat(data.comment, "</p>\n    </div>\n</div>");
};

var showComment = function showComment(comment) {
  if (!comment) {
    return "<div class=\"w3-ul w3-border\" id=\"comment\" name='commentDiv'></div>";
  } // only run if there is comment


  return comment.map(function (commentElement) {
    return commentHTML(commentElement);
  });
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/imgViewer.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/imgViewer.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");
/* harmony import */ var _helper_http__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/http */ "./resources/asset/js/components/helper/http.js");



/***/ })

}]);