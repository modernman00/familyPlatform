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
        Promise.all(/*! import() */[__webpack_require__.e("/vendor"), __webpack_require__.e("resources_asset_js_components_helper_http_js")]).then(__webpack_require__.bind(__webpack_require__, /*! ../helper/http */ "./resources/asset/js/components/helper/http.js")).then(function (res) {
          return res.postFormData("/login/changePW", "changePassword", "/login");
        }); // window.location.replace("/login")
      }
    } catch (error) {
      response.showError(error);
    }
  };

  response.id('submit').addEventListener('click', submitChangePW);
});

/***/ })

}]);