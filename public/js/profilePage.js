(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["profilePage"],{

/***/ "./resources/asset/js/components/helper/images.js":
/*!********************************************************!*\
  !*** ./resources/asset/js/components/helper/images.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showImageFileUploadFn: function() { return /* binding */ showImageFileUploadFn; }
/* harmony export */ });
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");


// use this in conjunction with the file 
var showImageFileUploadFn = function showImageFileUploadFn(uploadBtn, inputId, fileName) {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(uploadBtn).addEventListener('click', function () {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).click();
  });
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(inputId).addEventListener('change', function () {
    var fileNames = Array.from(this.files).map(function (file) {
      return file.name;
    });
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)(fileName).innerText = fileNames.join(', ');
  });
};

/***/ }),

/***/ "./resources/asset/js/components/kinship/kinshipRadar.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/kinship/kinshipRadar.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initKinshipRadar: function() { return /* binding */ initKinshipRadar; },
/* harmony export */   removeKinCardAcrossWidgets: function() { return /* binding */ removeKinCardAcrossWidgets; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);




var modalInitialized = false;
var settingsModalInitialized = false;
function initKinshipRadar() {
  // The widget markup is included twice (a mobile copy in the feed column and a
  // desktop copy in the right sidebar, toggled via responsive classes) so both
  // exist in the DOM at once. Wire up every copy rather than just the first
  // match.
  var containers = document.querySelectorAll('.kinship-radar-widget');
  containers.forEach(bindKinshipRadarContainer);
  initKinshipDetailsModal();
  initKinshipRadarSettingsModal();

  // Check if Contacts Lead Card was dismissed by the user (RFC-0038)
  try {
    if (localStorage.getItem('fp_contacts_lead_card_dismissed') === 'true') {
      document.querySelectorAll('#kinContactsUploadCard').forEach(function (el) {
        el.style.display = 'none';
      });
    }
  } catch (_) {}
}
function handleContactsLeadCardClick(_x) {
  return _handleContactsLeadCardClick.apply(this, arguments);
}
function _handleContactsLeadCardClick() {
  _handleContactsLeadCardClick = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee5(btn) {
    var _navigator$contacts, _window$bootstrap2;
    var props, opts, contacts, _window$bootstrap, _modalEl, modalEl, _t5;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context5) {
      while (1) switch (_context5.prev = _context5.next) {
        case 0:
          if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
          // If native Contact Picker is supported, trigger it; else open #inviteFamilyModal
          if (!('contacts' in navigator && 'ContactsManager' in window && (_navigator$contacts = navigator.contacts) !== null && _navigator$contacts !== void 0 && _navigator$contacts.select)) {
            _context5.next = 5;
            break;
          }
          _context5.prev = 1;
          props = ['name', 'tel'];
          opts = {
            multiple: true
          };
          _context5.next = 2;
          return navigator.contacts.select(props, opts);
        case 2:
          contacts = _context5.sent;
          if (!(Array.isArray(contacts) && contacts.length > 0)) {
            _context5.next = 3;
            break;
          }
          _modalEl = document.getElementById('inviteFamilyModal');
          if (_modalEl && (_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(_modalEl).show();
          }
          if (typeof window.renderSelectedContacts === 'function') {
            window.selectedContacts = contacts;
            window.renderSelectedContacts();
          }
          if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
          return _context5.abrupt("return");
        case 3:
          _context5.next = 5;
          break;
        case 4:
          _context5.prev = 4;
          _t5 = _context5["catch"](1);
        case 5:
          modalEl = document.getElementById('inviteFamilyModal');
          if (modalEl && (_window$bootstrap2 = window.bootstrap) !== null && _window$bootstrap2 !== void 0 && _window$bootstrap2.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
          }
          if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
        case 6:
        case "end":
          return _context5.stop();
      }
    }, _callee5, null, [[1, 4]]);
  }));
  return _handleContactsLeadCardClick.apply(this, arguments);
}
function getCsrfToken() {
  var _document$querySelect, _document$querySelect2, _document$querySelect3, _document$querySelect4, _document$querySelect5;
  return ((_document$querySelect = document.querySelector('form#kinshipRadarSettingsForm input[name="token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.value) || ((_document$querySelect2 = document.querySelector('input[name="token"]')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.value) || ((_document$querySelect3 = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.getAttribute('content')) || ((_document$querySelect4 = document.querySelector('meta[name="csrf_token"]')) === null || _document$querySelect4 === void 0 ? void 0 : _document$querySelect4.getAttribute('content')) || ((_document$querySelect5 = document.querySelector('meta[name="token"]')) === null || _document$querySelect5 === void 0 ? void 0 : _document$querySelect5.getAttribute('content')) || (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1] || '';
}
function removeKinCardAcrossWidgets(targetUserId) {
  if (!targetUserId) return;
  var cards = document.querySelectorAll(".kinship-radar-widget #kinCard_".concat(targetUserId, ", .kinship-radar-widget [data-user-id=\"").concat(targetUserId, "\"]"));
  var processedCards = new Set();
  cards.forEach(function (el) {
    var card = el.closest('.kinship-item-card');
    if (card && !processedCards.has(card)) {
      processedCards.add(card);
      card.style.opacity = '0';
      card.style.transform = 'scale(0.95)';
      setTimeout(function () {
        var widget = card.closest('.kinship-radar-widget');
        card.remove();
        if (widget && widget.querySelectorAll('.kinship-item-card').length === 0) {
          widget.style.display = 'none';
          var wrapper = widget.parentElement;
          if (wrapper && (wrapper.classList.contains('d-lg-none') || wrapper.classList.contains('d-none') || wrapper.classList.contains('kin-feed-carousel-wrapper'))) {
            wrapper.style.display = 'none';
          }
        }
      }, 250);
    }
  });
}
function sendKinshipRequest(_x2, _x3) {
  return _sendKinshipRequest.apply(this, arguments);
}
function _sendKinshipRequest() {
  _sendKinshipRequest = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee6(targetUserId, connectBtn) {
    var originalHtml, _res$data5, csrfToken, res, Toast, _t6;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context6) {
      while (1) switch (_context6.prev = _context6.next) {
        case 0:
          if (!(!targetUserId || !connectBtn)) {
            _context6.next = 1;
            break;
          }
          return _context6.abrupt("return", false);
        case 1:
          connectBtn.disabled = true;
          originalHtml = connectBtn.innerHTML;
          connectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Connecting...';
          _context6.prev = 2;
          csrfToken = getCsrfToken();
          _context6.next = 3;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/members/familyRequestMgt', {
            approverId: targetUserId,
            token: csrfToken
          }, {
            headers: {
              'X-XSRF-TOKEN': csrfToken,
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
        case 3:
          res = _context6.sent;
          if (!(((_res$data5 = res.data) === null || _res$data5 === void 0 ? void 0 : _res$data5.status) === 'success' || res.status === 200)) {
            _context6.next = 4;
            break;
          }
          connectBtn.classList.remove('btn-primary');
          connectBtn.classList.add('btn-success');
          connectBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Request Sent';

          // Track kinship connect event (fire-and-forget)
          axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/api/analytics/track', {
            event_type: 'kinship_connect',
            target_id: targetUserId
          }).catch(function () {});
          if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_3___default()) !== 'undefined') {
            Toast = sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().mixin({
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true
            });
            Toast.fire({
              icon: 'success',
              title: 'Kinship request sent!'
            });
          }

          // Once connected, fade out and remove card across widgets
          setTimeout(function () {
            removeKinCardAcrossWidgets(targetUserId);
          }, 1000);
          return _context6.abrupt("return", true);
        case 4:
          _context6.next = 6;
          break;
        case 5:
          _context6.prev = 5;
          _t6 = _context6["catch"](2);
          console.error('[KinshipRadar] Connect error:', _t6);
          connectBtn.disabled = false;
          connectBtn.innerHTML = originalHtml;
          (window.swalError || alert)('Could not send kinship request. Please try again.');
        case 6:
          return _context6.abrupt("return", false);
        case 7:
        case "end":
          return _context6.stop();
      }
    }, _callee6, null, [[2, 5]]);
  }));
  return _sendKinshipRequest.apply(this, arguments);
}
function openKinshipDetailsModal(kin) {
  if (!kin || !kin.id) return;
  var modalEl = document.getElementById('kinshipDetailsModal');
  if (!modalEl) {
    // Fallback: navigate directly to organogram
    window.location.href = "/organogram/".concat(kin.id);
    return;
  }

  // Populate Avatar
  var avatarEl = document.getElementById('kinModalAvatar');
  if (avatarEl) {
    avatarEl.src = kin.avatar || '/resources/images/profile/avatarM.png';
    avatarEl.alt = kin.name || 'Kin';
  }

  // Populate Name
  var nameEl = document.getElementById('kinModalName');
  if (nameEl) {
    nameEl.textContent = kin.name || 'Family Relative';
  }

  // Populate Score & Progress
  var scoreVal = parseInt(kin.score, 10) || 75;
  var scoreEl = document.getElementById('kinModalScore');
  if (scoreEl) {
    scoreEl.textContent = "".concat(scoreVal, "% Match");
  }
  var typeEl = document.getElementById('kinModalType');
  if (typeEl) {
    typeEl.textContent = kin.type || 'Potential Kin';
  }
  var percentLabel = document.getElementById('kinModalPercentLabel');
  if (percentLabel) {
    percentLabel.textContent = "".concat(scoreVal, "%");
  }
  var progressBar = document.getElementById('kinModalProgressBar');
  if (progressBar) {
    progressBar.style.width = "".concat(scoreVal, "%");
    progressBar.setAttribute('aria-valuenow', String(scoreVal));
  }

  // Populate Lineage & Match Reasons
  var reasonsListEl = document.getElementById('kinModalReasonsList');
  if (reasonsListEl) {
    reasonsListEl.innerHTML = '';
    var reasons = Array.isArray(kin.reasons) && kin.reasons.length > 0 ? kin.reasons : [kin.primaryReason || 'Shared family heritage network'];
    reasons.forEach(function (reason) {
      var li = document.createElement('li');
      li.className = 'd-flex align-items-start gap-2.5 py-1';
      li.innerHTML = "\n                <div class=\"rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0 mt-0.5\" style=\"width: 24px; height: 24px; background: rgba(163, 22, 33, 0.1); font-size: 0.75rem;\">\n                    <i class=\"bi bi-diagram-2-fill\"></i>\n                </div>\n                <span class=\"kin-reason-text\" style=\"font-size: 0.875rem; line-height: 1.45; color: var(--text-dark, #1e293b);\">\n                    ".concat(escapeHtml(reason), "\n                </span>\n            ");
      reasonsListEl.appendChild(li);
    });
  }

  // Family Branch Details
  var branchBox = document.getElementById('kinModalBranchBox');
  var famCodeEl = document.getElementById('kinModalFamCode');
  if (branchBox && famCodeEl) {
    if (kin.famCode) {
      famCodeEl.textContent = kin.famCode;
      branchBox.classList.remove('d-none');
    } else {
      branchBox.classList.add('d-none');
    }
  }

  // Family Tree Link Button — only show when viewer shares access to that family code
  var treeBtn = document.getElementById('kinModalTreeBtn');
  if (treeBtn) {
    if (kin.famCode) {
      treeBtn.href = "/organogram/".concat(kin.id);
      treeBtn.classList.remove('d-none');
    } else {
      treeBtn.href = '#';
      treeBtn.classList.add('d-none');
    }
  }

  // Connect Button
  var connectBtn = document.getElementById('kinModalConnectBtn');
  var connectLabel = document.getElementById('kinModalConnectLabel');
  if (connectBtn) {
    connectBtn.setAttribute('data-user-id', kin.id);
    connectBtn.disabled = false;
    connectBtn.classList.remove('btn-success');
    connectBtn.classList.add('btn-primary');
    if (connectLabel) {
      connectLabel.textContent = "Connect with ".concat(kin.name || 'Kin');
    }
  }
  if (window.bootstrap && window.bootstrap.Modal) {
    var bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
  }
}
function initKinshipDetailsModal() {
  if (modalInitialized) return;
  var modalEl = document.getElementById('kinshipDetailsModal');
  if (!modalEl) return;
  modalInitialized = true;
  var modalConnectBtn = document.getElementById('kinModalConnectBtn');
  if (modalConnectBtn) {
    modalConnectBtn.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
      var targetUserId, success;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            targetUserId = modalConnectBtn.getAttribute('data-user-id');
            if (targetUserId) {
              _context.next = 1;
              break;
            }
            return _context.abrupt("return");
          case 1:
            _context.next = 2;
            return sendKinshipRequest(targetUserId, modalConnectBtn);
          case 2:
            success = _context.sent;
            if (success) {
              setTimeout(function () {
                if (window.bootstrap && window.bootstrap.Modal) {
                  var bsModal = window.bootstrap.Modal.getInstance(modalEl);
                  if (bsModal) {
                    bsModal.hide();
                  }
                }
              }, 1200);
            }
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    })));
  }
}
function initKinshipRadarSettingsModal() {
  if (settingsModalInitialized) return;
  var modalEl = document.getElementById('kinshipRadarSettingsModal');
  if (!modalEl) return;
  settingsModalInitialized = true;

  // Load settings when modal is opened
  modalEl.addEventListener('show.bs.modal', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
    var _res$data, _res$data2, res, s, _masterSwitch, inlawsSwitch, maternalSwitch, discoverableSwitch, conf, confRadio, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].get('/api/kinship/settings');
        case 1:
          res = _context2.sent;
          if (((_res$data = res.data) === null || _res$data === void 0 ? void 0 : _res$data.status) === 'success' && (_res$data2 = res.data) !== null && _res$data2 !== void 0 && _res$data2.data) {
            s = res.data.data;
            _masterSwitch = document.getElementById('radarMasterSwitch');
            inlawsSwitch = document.getElementById('radarInlawsSwitch');
            maternalSwitch = document.getElementById('radarMaternalSwitch');
            discoverableSwitch = document.getElementById('radarDiscoverableSwitch');
            if (_masterSwitch) _masterSwitch.checked = Boolean(s.radar_enabled);
            if (inlawsSwitch) inlawsSwitch.checked = Boolean(s.include_inlaws);
            if (maternalSwitch) maternalSwitch.checked = Boolean(s.include_maternal);
            if (discoverableSwitch) discoverableSwitch.checked = Boolean(s.discoverable);
            conf = parseInt(s.min_confidence, 10) || 50;
            confRadio = document.getElementById("conf".concat(conf));
            if (confRadio) {
              confRadio.checked = true;
            }
            syncMasterVisuals();
          }
          _context2.next = 3;
          break;
        case 2:
          _context2.prev = 2;
          _t = _context2["catch"](0);
          console.warn('[KinshipRadar] Failed to load radar settings:', _t);
        case 3:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 2]]);
  })));
  var masterSwitch = document.getElementById('radarMasterSwitch');
  function syncMasterVisuals() {
    var isMasterOn = masterSwitch ? masterSwitch.checked : true;
    var subItems = modalEl.querySelectorAll('.radar-sub-setting');
    subItems.forEach(function (el) {
      el.style.opacity = isMasterOn ? '1' : '0.45';
      el.style.pointerEvents = isMasterOn ? 'auto' : 'none';
    });
  }
  if (masterSwitch) {
    masterSwitch.addEventListener('change', syncMasterVisuals);
  }
  var form = document.getElementById('kinshipRadarSettingsForm');
  var saveBtn = document.getElementById('btnSaveRadarSettings');
  if (form) {
    form.addEventListener('submit', /*#__PURE__*/function () {
      var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3(e) {
        var origHtml, _res$data3, csrfToken, _masterSwitch2, inlawsSwitch, maternalSwitch, discoverableSwitch, confChecked, payload, res, bsModal, Toast, _res$data4, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              e.preventDefault();
              if (saveBtn) {
                saveBtn.disabled = true;
                origHtml = saveBtn.innerHTML;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
              }
              _context3.prev = 1;
              csrfToken = getCsrfToken();
              _masterSwitch2 = document.getElementById('radarMasterSwitch');
              inlawsSwitch = document.getElementById('radarInlawsSwitch');
              maternalSwitch = document.getElementById('radarMaternalSwitch');
              discoverableSwitch = document.getElementById('radarDiscoverableSwitch');
              confChecked = document.querySelector('input[name="min_confidence"]:checked');
              payload = {
                radar_enabled: _masterSwitch2 ? _masterSwitch2.checked ? 1 : 0 : 1,
                include_inlaws: inlawsSwitch ? inlawsSwitch.checked ? 1 : 0 : 1,
                include_maternal: maternalSwitch ? maternalSwitch.checked ? 1 : 0 : 1,
                discoverable: discoverableSwitch ? discoverableSwitch.checked ? 1 : 0 : 1,
                min_confidence: confChecked ? parseInt(confChecked.value, 10) : 50,
                token: csrfToken
              };
              _context3.next = 2;
              return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/api/kinship/settings', payload, {
                headers: {
                  'X-XSRF-TOKEN': csrfToken,
                  'X-CSRF-TOKEN': csrfToken,
                  'X-Requested-With': 'XMLHttpRequest'
                }
              });
            case 2:
              res = _context3.sent;
              if (!(((_res$data3 = res.data) === null || _res$data3 === void 0 ? void 0 : _res$data3.status) === 'success' || res.status === 200)) {
                _context3.next = 3;
                break;
              }
              if (window.bootstrap && window.bootstrap.Modal) {
                bsModal = window.bootstrap.Modal.getInstance(modalEl);
                if (bsModal) {
                  bsModal.hide();
                }
              }
              if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_3___default()) !== 'undefined') {
                Toast = sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().mixin({
                  toast: true,
                  position: 'top-end',
                  showConfirmButton: false,
                  timer: 2500,
                  timerProgressBar: true
                });
                Toast.fire({
                  icon: 'success',
                  title: 'Radar preferences updated successfully!'
                });
              }

              // Reload feed to display updated recommendations according to new settings
              setTimeout(function () {
                window.location.reload();
              }, 800);
              _context3.next = 4;
              break;
            case 3:
              throw new Error(((_res$data4 = res.data) === null || _res$data4 === void 0 ? void 0 : _res$data4.message) || 'Failed to save');
            case 4:
              _context3.next = 6;
              break;
            case 5:
              _context3.prev = 5;
              _t2 = _context3["catch"](1);
              console.error('[KinshipRadar] Save settings error:', _t2);
              if (typeof (sweetalert2__WEBPACK_IMPORTED_MODULE_3___default()) !== 'undefined') {
                sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Could not save radar preferences. Please try again.'
                });
              } else {
                alert('Could not save radar preferences. Please try again.');
              }
            case 6:
              _context3.prev = 6;
              if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check2-circle"></i> Save Radar Preferences';
              }
              return _context3.finish(6);
            case 7:
            case "end":
              return _context3.stop();
          }
        }, _callee3, null, [[1, 5, 6, 7]]);
      }));
      return function (_x4) {
        return _ref3.apply(this, arguments);
      };
    }());
  }
}
function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
function bindKinshipRadarContainer(container) {
  container.addEventListener('click', /*#__PURE__*/function () {
    var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4(e) {
      var filterBtn, filterType, cards, prevArrow, track, nextArrow, _track, closeWidgetBtn, seeAllBtn, firstCard, rawData, kin, uploadLeadBtn, dismissLeadBtn, leadCard, connectBtn, targetUserId, dismissBtn, _targetUserId, resumeBtn, csrfToken, card, _rawData, _kin, _t3, _t4;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
        while (1) switch (_context4.prev = _context4.next) {
          case 0:
            // 0. Handle Filter Buttons (Client-side fast tab switching)
            filterBtn = e.target.closest('.btn-filter-kin');
            if (!filterBtn) {
              _context4.next = 1;
              break;
            }
            e.stopPropagation();
            filterType = filterBtn.getAttribute('data-filter') || 'all'; // Update active state on buttons within this widget
            container.querySelectorAll('.btn-filter-kin').forEach(function (b) {
              b.classList.remove('active');
            });
            filterBtn.classList.add('active');

            // Show/hide cards matching category
            cards = container.querySelectorAll('.kinship-item-card');
            cards.forEach(function (card) {
              var cat = card.getAttribute('data-kin-category') || 'all';
              if (filterType === 'all' || cat === filterType) {
                card.style.display = '';
              } else {
                card.style.display = 'none';
              }
            });
            return _context4.abrupt("return");
          case 1:
            // 0b. Handle Carousel Navigation Arrows
            prevArrow = e.target.closest('.kin-carousel-arrow-prev');
            if (!prevArrow) {
              _context4.next = 2;
              break;
            }
            e.stopPropagation();
            track = container.querySelector('.kinship-carousel-track, .kinship-suggestions-scroll');
            if (track) track.scrollBy({
              left: -260,
              behavior: 'smooth'
            });
            return _context4.abrupt("return");
          case 2:
            nextArrow = e.target.closest('.kin-carousel-arrow-next');
            if (!nextArrow) {
              _context4.next = 3;
              break;
            }
            e.stopPropagation();
            _track = container.querySelector('.kinship-carousel-track, .kinship-suggestions-scroll');
            if (_track) _track.scrollBy({
              left: 260,
              behavior: 'smooth'
            });
            return _context4.abrupt("return");
          case 3:
            // 0c. Handle Dismiss Entire Widget Button
            closeWidgetBtn = e.target.closest('.btn-close-kin-widget');
            if (!closeWidgetBtn) {
              _context4.next = 4;
              break;
            }
            e.stopPropagation();
            container.style.transition = 'all 0.25s ease';
            container.style.opacity = '0';
            container.style.transform = 'scale(0.97)';
            setTimeout(function () {
              container.style.display = 'none';
              var wrapper = container.closest('.kin-feed-carousel-wrapper');
              if (wrapper) wrapper.style.display = 'none';
            }, 250);
            return _context4.abrupt("return");
          case 4:
            // 0d. Handle "See all" Button
            seeAllBtn = e.target.closest('.btn-see-all-kin');
            if (!seeAllBtn) {
              _context4.next = 5;
              break;
            }
            e.stopPropagation();
            firstCard = container.querySelector('.kinship-item-card:not([style*="display: none"])');
            if (firstCard) {
              rawData = firstCard.getAttribute('data-kin');
              if (rawData) {
                try {
                  kin = JSON.parse(rawData);
                  openKinshipDetailsModal(kin);
                } catch (err) {
                  console.error('[KinshipRadar] Failed to parse kin payload for See All:', err);
                }
              }
            }
            return _context4.abrupt("return");
          case 5:
            // 0e. Handle "Upload Contacts" Lead Card Action Button (RFC-0038)
            uploadLeadBtn = e.target.closest('.btn-upload-contacts-lead');
            if (!uploadLeadBtn) {
              _context4.next = 6;
              break;
            }
            e.stopPropagation();
            handleContactsLeadCardClick(uploadLeadBtn);
            return _context4.abrupt("return");
          case 6:
            // 0f. Handle Dismiss Contacts Lead Card
            dismissLeadBtn = e.target.closest('.btn-dismiss-contacts-lead');
            if (!dismissLeadBtn) {
              _context4.next = 7;
              break;
            }
            e.stopPropagation();
            leadCard = document.getElementById('kinContactsUploadCard');
            if (leadCard) {
              leadCard.style.transition = 'all 0.2s ease';
              leadCard.style.opacity = '0';
              leadCard.style.transform = 'scale(0.95)';
              setTimeout(function () {
                leadCard.style.display = 'none';
                try {
                  localStorage.setItem('fp_contacts_lead_card_dismissed', 'true');
                } catch (_) {}
              }, 200);
            }
            return _context4.abrupt("return");
          case 7:
            // 1. Handle Connect Button (Stop propagation to prevent opening modal)
            connectBtn = e.target.closest('.btn-connect-kin');
            if (!connectBtn) {
              _context4.next = 9;
              break;
            }
            e.stopPropagation();
            targetUserId = connectBtn.getAttribute('data-user-id');
            if (!targetUserId) {
              _context4.next = 8;
              break;
            }
            _context4.next = 8;
            return sendKinshipRequest(targetUserId, connectBtn);
          case 8:
            return _context4.abrupt("return");
          case 9:
            // 2. Handle Dismiss Button (Stop propagation to prevent opening modal)
            dismissBtn = e.target.closest('.btn-dismiss-kin');
            if (!dismissBtn) {
              _context4.next = 15;
              break;
            }
            e.stopPropagation();
            _targetUserId = dismissBtn.getAttribute('data-user-id');
            if (_targetUserId) {
              _context4.next = 10;
              break;
            }
            return _context4.abrupt("return");
          case 10:
            removeKinCardAcrossWidgets(_targetUserId);
            _context4.prev = 11;
            _context4.next = 12;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/api/kinship/dismiss', {
              dismissed_user_id: _targetUserId
            });
          case 12:
            _context4.next = 14;
            break;
          case 13:
            _context4.prev = 13;
            _t3 = _context4["catch"](11);
            console.warn('[KinshipRadar] Dismiss sync error:', _t3);
          case 14:
            return _context4.abrupt("return");
          case 15:
            // 2b. Handle Resume Radar Button
            resumeBtn = e.target.closest('.btn-resume-radar');
            if (!resumeBtn) {
              _context4.next = 20;
              break;
            }
            e.stopPropagation();
            resumeBtn.disabled = true;
            resumeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Resuming...';
            _context4.prev = 16;
            csrfToken = getCsrfToken();
            _context4.next = 17;
            return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/api/kinship/settings', {
              radar_enabled: 1,
              token: csrfToken
            }, {
              headers: {
                'X-XSRF-TOKEN': csrfToken,
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
              }
            });
          case 17:
            window.location.reload();
            _context4.next = 19;
            break;
          case 18:
            _context4.prev = 18;
            _t4 = _context4["catch"](16);
            console.error('[KinshipRadar] Resume error:', _t4);
            resumeBtn.disabled = false;
            resumeBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i> Resume Radar';
          case 19:
            return _context4.abrupt("return");
          case 20:
            // 3. Handle Card Click -> Open Kinship Details Modal
            card = e.target.closest('.kinship-item-card');
            if (card) {
              _rawData = card.getAttribute('data-kin');
              if (_rawData) {
                try {
                  _kin = JSON.parse(_rawData);
                  openKinshipDetailsModal(_kin);
                } catch (err) {
                  console.error('[KinshipRadar] Failed to parse kin payload:', err);
                }
              }
            }
          case 21:
          case "end":
            return _context4.stop();
        }
      }, _callee4, null, [[11, 13], [16, 18]]);
    }));
    return function (_x5) {
      return _ref4.apply(this, arguments);
    };
  }());

  // Keyboard support: Allow Enter or Space on a focused card to open details modal
  container.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      var card = e.target.closest('.kinship-item-card');
      if (card && !e.target.closest('button, a')) {
        e.preventDefault();
        var rawData = card.getAttribute('data-kin');
        if (rawData) {
          try {
            var kin = JSON.parse(rawData);
            openKinshipDetailsModal(kin);
          } catch (err) {
            console.error('[KinshipRadar] Keydown error:', err);
          }
        }
      }
    }
  });
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initKinshipRadar);
} else {
  initKinshipRadar();
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/comment.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/comment.js ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appendNewComment: function() { return /* binding */ appendNewComment; },
/* harmony export */   commentHTML: function() { return /* binding */ commentHTML; },
/* harmony export */   normalizeEmojiPresentation: function() { return /* binding */ normalizeEmojiPresentation; },
/* harmony export */   showComment: function() { return /* binding */ showComment; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./showEmojiOnComment.js */ "./resources/asset/js/components/profilePage/showEmojiOnComment.js");




var reqId = localStorage.getItem('requesterId');
var normalizeEmojiPresentation = function normalizeEmojiPresentation(str) {
  if (!str || typeof str !== 'string') return '';
  return str.replace(/\u2764(?!\uFE0F)/g, '\u2764\uFE0F').replace(/\u2665(?!\uFE0F)/g, '\u2764\uFE0F').replace(/(^|\s)<3(?=\s|$|[.,!?])/g, '$1❤️');
};
var commentHTML = function commentHTML(data, postId) {
  var _data$reactions$count, _data$reactions, _data$reactions$count2, _data$reactions2, _data$reactions2$coun;
  var profileImg = data.profileImg,
    fullName = data.fullName,
    date_created = data.date_created,
    img = data.img,
    comment = data.comment,
    comment_no = data.comment_no,
    id = data.id;
  var imgURL = profileImg || img;
  // comment + fullName + image path are user-authored — escape for innerHTML (SEC-2).
  var image = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(imgURL ? "/resources/images/profile/".concat(imgURL) : "/resources/images/profile/avatarM.png");
  var nameSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.toSentenceCase)(fullName));
  var commentSafe = normalizeEmojiPresentation((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(comment));
  var counts = (_data$reactions$count = data === null || data === void 0 ? void 0 : (_data$reactions = data.reactions) === null || _data$reactions === void 0 ? void 0 : _data$reactions.counts) !== null && _data$reactions$count !== void 0 ? _data$reactions$count : {};
  var total = (_data$reactions$count2 = data === null || data === void 0 ? void 0 : (_data$reactions2 = data.reactions) === null || _data$reactions2 === void 0 ? void 0 : (_data$reactions2$coun = _data$reactions2.counts) === null || _data$reactions2$coun === void 0 ? void 0 : _data$reactions2$coun.totalReactions) !== null && _data$reactions$count2 !== void 0 ? _data$reactions$count2 : 0;
  return "<div class=\"d-flex mb-3 commentDiv align-items-start\" data-commentDiv-no=\"".concat(comment_no, "\" id=\"commentDiv").concat(comment_no, "\" name=\"commentDiv\">\n\n  <img src=\"").concat(image, "\" alt=\"Avatar\" class=\"rounded-circle me-2 commentImg\" width=\"32\" height=\"32\">\n\n  <div class=\"flex-grow-1\">\n    <div class=\"d-flex justify-content-between align-items-center\">\n      <small><strong>").concat(nameSafe, "</strong></small>\n      <small class=\"text-muted commentTiming\" datetime=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(date_created), "\" title=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(date_created), "\">\n        ").concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date_created), "\n      </small>\n    </div>\n\n    <div class=\"comment-text mb-2 p-3 shadow-sm\" style=\"background-color: var(--hover-color); border-radius: 18px; border-top-left-radius: 4px; display: inline-block;\">\n      <span style=\"font-size: 0.95rem; color: var(--text-color); font-variant-emoji: emoji;\">").concat(commentSafe, "</span>\n    </div>\n\n      <div class=\"d-flex reaction-preview-section align-items-center mb-2 gap-2\"> \n\n        <div class=\"reaction-preview\" id=\"reaction-preview-").concat(comment_no, "\">\n        ").concat((0,_showEmojiOnComment_js__WEBPACK_IMPORTED_MODULE_3__.renderTopReactions)(counts, comment_no), "\n        </div>\n\n         <div class=\"reaction-summary\" data-comment-no=\"").concat(comment_no, "\" role=\"tooltip\" id=\"reaction-summary-").concat(comment_no, "\" style=\"display:none;\">\n        </div>\n\n      </div>\n\n      <div class=\"comment-actions d-flex gap-3\">         \n                <div class=\"reaction-bar\"  id=\"reaction-bar-").concat(comment_no, "\">\n\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Like\" id=\"reaction-option-like-").concat(comment_no, "\" data-reaction=\"like\" data-label=\"likes\"> \uD83D\uDC4D </div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Love\" id=\"reaction-option-love-").concat(comment_no, "\" data-reaction=\"love\" data-label=\"love\">\u2764\uFE0F</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Haha\" id=\"reaction-option-haha-").concat(comment_no, "\" data-reaction=\"haha\" data-label=\"haha\">\uD83D\uDE04</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Wow\" id=\"reaction-option-wow-").concat(comment_no, "\" data-reaction=\"wow\" data-label=\"wow\">\uD83D\uDE2E</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Sad\" id=\"reaction-option-sad-").concat(comment_no, "\" data-reaction=\"sad\" data-label=\"sad\">\uD83D\uDE22</div>\n                    <div class=\"reaction-option\" data-option-no=\"").concat(comment_no, "\" aria-label=\"Angry\" id=\"reaction-option-angry-").concat(comment_no, "\"\n                     data-reaction=\"angry\" data-label=\"angry\">\uD83D\uDE20</div>\n                </div>\n\n                <div class=\"reaction-button like-button-").concat(comment_no, "\" id=\"like-button-").concat(comment_no, "\" data-comment-no=\"").concat(comment_no, "\">\n                    <i class=\"bi bi-hand-thumbs-up reaction-icon\" id=\"like-icon-").concat(comment_no, "\"></i>\n                    <span>Like</span>\n                     <div class=\"reaction-count\" id=\"like-count-").concat(comment_no, "\">").concat(total, "</div>\n                   \n                </div>\n\n                ").concat(reqId == id || reqId == postId ? "<button class=\"btn btn-sm btn-icon text-danger\" id=\"removeComment(".concat(comment_no, ")\" title=\"Remove\">\n                    <i class=\"bi bi-trash\" id=\"removeCommentIcon").concat(comment_no, "\"></i>\n                    </button>") : '', "        \n      </div>\n  </div>\n</div><hr>");
};

// i need the postid to use to show the delete button 
var showComment = function showComment(comment, postId) {
  if (!comment) {
    return "<div id=\"comment\" name=\"commentDiv\"></div>";
  }
  // only run if there is comment
  // USED FOR ALL THE COMMENTS WHEN THE PAGE IS LOADING
  var commentHTMLArray = comment.map(function (commentElement) {
    return commentHTML(commentElement, postId);
  });
  return commentHTMLArray.join(''); // Join the array elements into a single string
};
var appendNewComment = function appendNewComment(commentData) {
  // check if commentData is valid
  if (!commentData) {
    throw new Error('No comment update received');
  }
  var idDiv = "showComment".concat(commentData.post_no);
  // check if the div has been created by the DOM 
  var commentContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.id)(idDiv);
  if (!commentContainer) {
    throw new Error('commetContainer div not found in the DOM');
  }
  var commentHtml = commentHTML(commentData);
  commentContainer.insertAdjacentHTML('beforeend', commentHtml);
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/editProfile.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/editProfile.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_3__);






var FORM_ID = 'editProfileFormModal';
var BTN_ID = 'editProfileBtnModal';
var NOTIFICATION_ID = "".concat(FORM_ID, "_notification");
var el = function el(elId) {
  return document.getElementById(elId);
};

/**
 * Paint the inline notification bar inside the modal.
 * @param {'success'|'danger'} type
 * @param {string} message
 */
var setNotification = function setNotification(type, message) {
  var box = el(NOTIFICATION_ID);
  if (!box) return;
  box.className = "notification alert ".concat(type === 'success' ? 'alert-success bg-success' : 'alert-danger bg-danger', " text-white");
  box.innerHTML = message;
  box.style.display = 'block';
  box.style.setProperty('display', 'block', 'important');
  var body = box.closest('.modal-body');
  if (body) body.scrollTop = 0; // bring the (scrollable) modal body back to the top
};
var clearNotification = function clearNotification() {
  var box = el(NOTIFICATION_ID);
  if (!box) return;
  box.style.display = 'none';
  box.innerHTML = '';
  box.className = '';
};
var setLoader = function setLoader(show) {
  var loader = el('setLoader');
  if (!loader) return;
  loader.innerHTML = show ? "<div class=\"spinner-border spinner-border-sm text-success\" role=\"status\"><span class=\"visually-hidden\">Saving\u2026</span></div> <span class=\"text-white-50 small\">Saving your changes\u2026</span>" : '';
  loader.style.display = show ? 'block' : 'none';
};
var submitting = false;
function handleSave(_x) {
  return _handleSave.apply(this, arguments);
} // Delegated: the "Create Post"/profile chunk can execute before the modal markup
// is parsed, so binding straight to #editProfileBtnModal at load time is racy.
function _handleSave() {
  _handleSave = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(btn) {
    var form, originalBtnText, formData, _response$data, response, message, isPreactProfile, _window$bootstrap, previewImg, detail, modalEl, _error$response, _error$response$data, _error$response2, _error$response2$data, raw, _message, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          if (!submitting) {
            _context.next = 1;
            break;
          }
          return _context.abrupt("return");
        case 1:
          form = el(FORM_ID);
          if (form) {
            _context.next = 2;
            break;
          }
          return _context.abrupt("return");
        case 2:
          submitting = true;
          originalBtnText = btn ? btn.innerHTML : '';
          clearNotification();
          setLoader(true);
          if (btn) {
            btn.disabled = true;
            btn.innerHTML = "<span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span> Saving\u2026";
          }
          formData = new FormData(form);
          formData.delete('submit');
          _context.prev = 3;
          _context.next = 4;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/member/profilePage/editProfile', formData, {
            withCredentials: true,
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN'
          });
        case 4:
          response = _context.sent;
          message = (response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Your profile has been updated.';
          setNotification('success', message);
          sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Profile updated successfully',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
          });
          isPreactProfile = Boolean(document.getElementById('feed-root') || document.getElementById('kin-requests-root'));
          if (isPreactProfile) {
            previewImg = document.getElementById('profilePreview');
            detail = {
              firstName: String(formData.get('firstName') || '').trim() || undefined,
              lastName: String(formData.get('lastName') || '').trim() || undefined,
              country: formData.has('country') ? String(formData.get('country') || '').trim() : undefined,
              avatar: previewImg && previewImg.getAttribute('src') ? previewImg.getAttribute('src') : undefined
            };
            window.dispatchEvent(new CustomEvent('fp:profile-updated', {
              detail
            }));
            modalEl = document.getElementById('editProfileModal');
            if (modalEl && (_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Modal) {
              window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
          } else {
            // Reload so the rest of the page picks up the new values.
            setTimeout(function () {
              return window.location.assign('/profilePage');
            }, 800);
          }
          _context.next = 6;
          break;
        case 5:
          _context.prev = 5;
          _t = _context["catch"](3);
          console.error('Edit Profile Submit Error:', _t);
          raw = (_t === null || _t === void 0 ? void 0 : (_error$response = _t.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || (_t === null || _t === void 0 ? void 0 : (_error$response2 = _t.response) === null || _error$response2 === void 0 ? void 0 : (_error$response2$data = _error$response2.data) === null || _error$response2$data === void 0 ? void 0 : _error$response2$data.error) || (_t === null || _t === void 0 ? void 0 : _t.message) || 'Failed to update profile. Please try again.';
          _message = Array.isArray(raw) ? raw : [raw];
          setNotification('danger', _message.join('<br>'));

          // Toast (not a blocking modal) so the inline notification stays uncovered.
          sweetalert2__WEBPACK_IMPORTED_MODULE_3___default().fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Update failed',
            text: _message.join(' '),
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
          });
          submitting = false;
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
          }
          setLoader(false);
        case 6:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[3, 5]]);
  }));
  return _handleSave.apply(this, arguments);
}
document.addEventListener('click', function (e) {
  var target = e.target instanceof Element ? e.target.closest("#".concat(BTN_ID)) : null;
  if (!target) return;
  e.preventDefault();
  handleSave(target);
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/engagement.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/engagement.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   handlePollVote: function() { return /* binding */ handlePollVote; },
/* harmony export */   handleReaction: function() { return /* binding */ handleReaction; },
/* harmony export */   initEngagementListeners: function() { return /* binding */ initEngagementListeners; },
/* harmony export */   initMemories: function() { return /* binding */ initMemories; },
/* harmony export */   loadMemories: function() { return /* binding */ loadMemories; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/profilePage/html.js");


function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
;

var handleReaction = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee(postNo, reactionType) {
    var token, formData, response, data, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          token = (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)();
          formData = new FormData();
          formData.append('post_no', postNo);
          formData.append('reaction_type', reactionType);
          formData.append('token', token);
          _context.next = 1;
          return fetch('/api/engagement/react', {
            method: 'POST',
            headers: {
              'X-XSRF-TOKEN': token
            },
            body: formData
          });
        case 1:
          response = _context.sent;
          _context.next = 2;
          return response.json();
        case 2:
          data = _context.sent;
          return _context.abrupt("return", data);
        case 3:
          _context.prev = 3;
          _t = _context["catch"](0);
          console.error('Reaction error:', _t);
          return _context.abrupt("return", null);
        case 4:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 3]]);
  }));
  return function handleReaction(_x, _x2) {
    return _ref.apply(this, arguments);
  };
}();
var handlePollVote = /*#__PURE__*/function () {
  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee2(optionId) {
    var postNo,
      token,
      formData,
      response,
      data,
      _args2 = arguments,
      _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          postNo = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : '';
          _context2.prev = 1;
          token = (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)();
          formData = new FormData();
          formData.append('option_id', optionId);
          if (postNo) formData.append('post_no', postNo);
          if (token) formData.append('token', token);
          _context2.next = 2;
          return fetch('/api/poll/vote', {
            method: 'POST',
            headers: _objectSpread(_objectSpread({}, token ? {
              'X-XSRF-TOKEN': token
            } : {}), {}, {
              'X-Requested-With': 'XMLHttpRequest'
            }),
            body: formData
          });
        case 2:
          response = _context2.sent;
          _context2.next = 3;
          return response.json();
        case 3:
          data = _context2.sent;
          return _context2.abrupt("return", data);
        case 4:
          _context2.prev = 4;
          _t2 = _context2["catch"](1);
          console.error('Vote error:', _t2);
          return _context2.abrupt("return", null);
        case 5:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[1, 4]]);
  }));
  return function handlePollVote(_x3) {
    return _ref2.apply(this, arguments);
  };
}();
var loadMemories = /*#__PURE__*/function () {
  var _ref3 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee3() {
    var response, data, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.prev = 0;
          _context3.next = 1;
          return fetch('/api/engagement/memories');
        case 1:
          response = _context3.sent;
          _context3.next = 2;
          return response.json();
        case 2:
          data = _context3.sent;
          return _context3.abrupt("return", (data === null || data === void 0 ? void 0 : data.data) || []);
        case 3:
          _context3.prev = 3;
          _t3 = _context3["catch"](0);
          console.error('Memory load error:', _t3);
          return _context3.abrupt("return", []);
        case 4:
        case "end":
          return _context3.stop();
      }
    }, _callee3, null, [[0, 3]]);
  }));
  return function loadMemories() {
    return _ref3.apply(this, arguments);
  };
}();

var initEngagementListeners = function initEngagementListeners() {
  document.addEventListener('click', /*#__PURE__*/function () {
    var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee4(e) {
      var btn, optionId, res, _btn, postNo, reaction, _res;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context4) {
        while (1) switch (_context4.prev = _context4.next) {
          case 0:
            if (!e.target.closest('.poll-option-btn')) {
              _context4.next = 3;
              break;
            }
            btn = e.target.closest('.poll-option-btn');
            if (!btn.closest('.feed-posts-container')) {
              _context4.next = 1;
              break;
            }
            return _context4.abrupt("return");
          case 1:
            optionId = btn.getAttribute('data-option-id');
            if (!optionId) {
              _context4.next = 3;
              break;
            }
            btn.disabled = true;
            _context4.next = 2;
            return handlePollVote(optionId);
          case 2:
            res = _context4.sent;
            if ((res === null || res === void 0 ? void 0 : res.status) === 'success') {
              // Trigger a reload of the post or re-fetch to show new bars
              window.dispatchEvent(new Event('refresh-feed'));
              window.dispatchEvent(new CustomEvent('fp:feed-refresh'));
            }
            btn.disabled = false;
          case 3:
            if (!e.target.closest('.reaction-btn')) {
              _context4.next = 5;
              break;
            }
            _btn = e.target.closest('.reaction-btn');
            postNo = _btn.getAttribute('data-post-no');
            reaction = _btn.getAttribute('data-reaction');
            if (!(postNo && reaction)) {
              _context4.next = 5;
              break;
            }
            _context4.next = 4;
            return handleReaction(postNo, reaction);
          case 4:
            _res = _context4.sent;
            if ((_res === null || _res === void 0 ? void 0 : _res.status) === 'success') {
              // Trigger a reload of the post or re-fetch to show new bars
              window.dispatchEvent(new CustomEvent('fp:feed-refresh'));
            }
          case 5:
          case "end":
            return _context4.stop();
        }
      }, _callee4);
    }));
    return function (_x4) {
      return _ref4.apply(this, arguments);
    };
  }());
};
var initMemories = /*#__PURE__*/function () {
  var _ref5 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee5() {
    var container, content, memories, htmlStr, _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context5) {
      while (1) switch (_context5.prev = _context5.next) {
        case 0:
          container = document.getElementById('memories-container');
          content = document.getElementById('memories-content');
          if (!(!container || !content)) {
            _context5.next = 1;
            break;
          }
          return _context5.abrupt("return");
        case 1:
          _context5.prev = 1;
          _context5.next = 2;
          return loadMemories();
        case 2:
          memories = _context5.sent;
          if (memories && memories.length > 0) {
            container.style.display = 'block';
            htmlStr = '';
            memories.forEach(function (memory) {
              // Pass an empty array for comments to the html function since memories don't eager load comments initially
              htmlStr += (0,_html__WEBPACK_IMPORTED_MODULE_4__.html)(memory, []);
            });
            content.innerHTML = htmlStr;
          }
          _context5.next = 4;
          break;
        case 3:
          _context5.prev = 3;
          _t4 = _context5["catch"](1);
          console.error('Failed to load memories:', _t4);
        case 4:
        case "end":
          return _context5.stop();
      }
    }, _callee5, null, [[1, 3]]);
  }));
  return function initMemories() {
    return _ref5.apply(this, arguments);
  };
}();

/***/ }),

/***/ "./resources/asset/js/components/profilePage/html.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/html.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   html: function() { return /* binding */ html; }
/* harmony export */ });
/* harmony import */ var _htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./htmlFolder/nameImageTiming */ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js");
/* harmony import */ var _htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./htmlFolder/commentForm */ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js");
/* harmony import */ var _htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./htmlFolder/likeCommentButton */ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js");
/* harmony import */ var _htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./htmlFolder/showPostImages */ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js");
/* harmony import */ var _comment__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./comment */ "./resources/asset/js/components/profilePage/comment.js");
/* harmony import */ var _htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./htmlFolder/engagementHtml */ "./resources/asset/js/components/profilePage/htmlFolder/engagementHtml.js");
/* harmony import */ var _videoParser__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./videoParser */ "./resources/asset/js/components/profilePage/videoParser.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");








var renderVideoPlayer = function renderVideoPlayer(postMessage) {
  var video = (0,_videoParser__WEBPACK_IMPORTED_MODULE_6__.extractVideoFromText)(postMessage);
  if (!video) return '';
  var isShort = Boolean(video.isShort || video.type === 'tiktok' || video.originalUrl && video.originalUrl.includes('/shorts/') || /(?:^|[\s"'(])#(?:shorts?|reels?|tiktok|afv)\b/i.test(String(postMessage || '')));
  if (video.type === 'youtube' || video.type === 'vimeo' || video.type === 'cloudflare' || video.type === 'tiktok') {
    return "\n      <div class=\"video-embed-container feed-video-breakout mb-3\" style=\"border-radius: 14px; overflow: hidden; width: 100%; max-width: 100%; margin: 0 auto; background: #07090e; box-shadow: 0 4px 12px rgba(0,0,0,0.1);\">\n        <div class=\"video-rendition-frame\" style=\"width: 100%; max-width: ".concat(isShort ? 'min(460px, 100%)' : '100%', "; aspect-ratio: ").concat(isShort ? '9 / 16' : '16 / 9', "; max-height: ").concat(isShort ? 'min(820px, 84vh)' : 'min(720px, 80vh)', "; margin: 0 auto;\">\n          <iframe src=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_7__.esc)(video.embedUrl), "\"\n                  title=\"Video Player\"\n                  frameborder=\"0\" \n                  allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" \n                  allowfullscreen \n                  loading=\"lazy\"\n                  style=\"width: 100%; height: 100%; border: none; border-radius: 14px; background: #07090e; display: block;\"></iframe>\n        </div>\n      </div>\n    ");
  } else if (video.type === 'direct') {
    return "\n      <div class=\"video-embed-container feed-video-breakout mb-3 position-relative\" style=\"border-radius: 14px; overflow: hidden; background: #07090e; box-shadow: 0 4px 14px rgba(0,0,0,0.12);\">\n        <div class=\"video-rendition-frame position-relative overflow-hidden\" \n             style=\"width: 100%; max-width: 100%; aspect-ratio: 4 / 5; max-height: 880px; border-radius: 14px; background: radial-gradient(circle at center, rgba(30, 41, 59, 0.55) 0%, rgba(7, 9, 14, 0.98) 100%);\">\n          <video referrerpolicy=\"no-referrer\" class=\"feed-post-video w-100 h-100\" \n                 src=\"".concat((0,_global__WEBPACK_IMPORTED_MODULE_7__.esc)(video.embedUrl), "\" \n                 controls \n                 playsinline \n                 webkit-playsinline \n                 muted \n                 loop \n                 preload=\"metadata\" \n                 onloadedmetadata=\"\n                   if (this.videoWidth && this.videoHeight) {\n                     const r = this.videoWidth / this.videoHeight;\n                     const frame = this.parentElement;\n                     if (r < 0.88) {\n                       frame.style.aspectRatio = '4 / 5';\n                       frame.style.maxHeight = '880px';\n                       this.style.objectPosition = '50% 28%';\n                     } else if (r > 1.15) {\n                       frame.style.aspectRatio = r > 1.6 ? '16 / 10' : (r.toFixed(4) + ' / 1');\n                       frame.style.maxHeight = 'min(680px, 75vh)';\n                       this.style.objectPosition = '50% 50%';\n                     } else {\n                       frame.style.aspectRatio = r.toFixed(4) + ' / 1';\n                       frame.style.maxHeight = 'min(720px, 78vh)';\n                       this.style.objectPosition = '50% 50%';\n                     }\n                     frame.style.maxWidth = '100%';\n                   }\n                 \"\n                 style=\"width: 100%; height: 100%; object-fit: cover; background: transparent; border-radius: 14px; display: block;\"></video>\n        </div>\n      </div>\n    ");
  }
  return '';
};
var html = function html(el) {
  var comment = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  var post_no = el.post_no,
    postMessage = el.postMessage;
  var video = (0,_videoParser__WEBPACK_IMPORTED_MODULE_6__.extractVideoFromText)(postMessage);
  var displayMsg = (0,_videoParser__WEBPACK_IMPORTED_MODULE_6__.cleanPostMessage)(postMessage, video);
  return "<div class=\"w3-container w3-card w3-white w3-round w3-margin post".concat(post_no, "\"><br>\n\n      ").concat((0,_htmlFolder_nameImageTiming__WEBPACK_IMPORTED_MODULE_0__.nameImgTiming)(el), "\n\n    <hr class=\"w3-clear\">\n\n    ").concat(displayMsg ? "<p class=\"postFont\"> ".concat((0,_global__WEBPACK_IMPORTED_MODULE_7__.esc)(displayMsg), " </p>") : '', "\n\n     ").concat(renderVideoPlayer(postMessage), "\n\n     ").concat((0,_htmlFolder_showPostImages__WEBPACK_IMPORTED_MODULE_3__.showPostImg)(el), "\n     \n     ").concat((0,_htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__.renderPoll)(el === null || el === void 0 ? void 0 : el.poll), "\n     ").concat((0,_htmlFolder_engagementHtml__WEBPACK_IMPORTED_MODULE_5__.renderReactions)(el), "\n\n    ").concat((0,_htmlFolder_likeCommentButton__WEBPACK_IMPORTED_MODULE_2__.likeCommentButton)(el), "\n\n    ").concat((0,_htmlFolder_commentForm__WEBPACK_IMPORTED_MODULE_1__.commentForm)(el), "\n\n    <div id = 'showComment").concat(post_no, "'>\n\n      ").concat((0,_comment__WEBPACK_IMPORTED_MODULE_4__.showComment)(comment), "\n      \n    </div><br>\n  </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/commentForm.js":
/*!*****************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/commentForm.js ***!
  \*****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   commentForm: function() { return /* binding */ commentForm; }
/* harmony export */ });
var commentForm = function commentForm(data) {
  var post_no = data.post_no;
  return " <p id=\"formComment".concat(post_no, "_notification\"></p>\n\n  <form \n    action=\"/postCommentProfile\" \n    method=\"post\" id=\"formComment").concat(post_no, "\" \n    style=\"display:none\" \n    enctype=\"multipart/form-data\">\n\n    <input \n      name='post_no' \n      type=\"hidden\" \n      name=\"").concat(post_no, "\" \n      value=").concat(post_no, " />\n\n    <input \n      class=\"w3-input w3-border w3-round-large inputComment\" \n      type=\"text\" \n      placeholder=\"Write a comment\"\n      id=\"inputComment").concat(post_no, "\" \n      value = \"\" name='comment'>\n\n    <br>\n\n    <button \n      type='submit' \n      id=\"submitComment").concat(post_no, "\" \n      class=\"w3-button w3-green submitComment\">\n        Submit\n    </button>\n    \n    <br><br>\n  </form>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/engagementHtml.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/engagementHtml.js ***!
  \********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderPoll: function() { return /* binding */ renderPoll; },
/* harmony export */   renderReactions: function() { return /* binding */ renderReactions; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");


var renderPoll = function renderPoll(poll) {
  if (!poll || !poll.options || !Array.isArray(poll.options)) return '';

  // Poll question + option text are user-authored — escape for innerHTML (SEC-2).
  var questionText = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(poll.question || '');
  var html = "<div class=\"poll-container mt-3 p-3 bg-light rounded border border-light-subtle\">\n        <h6 class=\"fw-bold mb-3\"><i class=\"fa fa-bar-chart me-2 text-primary\"></i>".concat(questionText, "</h6>\n        <div class=\"poll-options\">");
  poll.options.forEach(function (opt) {
    // Multi-select is supported on backend. user_voted_option_id is an array or string/number
    var isVoted = poll.user_voted_option_id && (Array.isArray(poll.user_voted_option_id) ? poll.user_voted_option_id.some(function (v) {
      return String(v) === String(opt.option_id);
    }) : String(poll.user_voted_option_id) === String(opt.option_id));
    var percentage = (opt === null || opt === void 0 ? void 0 : opt.percentage) || 0;
    var barWidth = percentage + '%';
    var bgClass = isVoted ? 'bg-primary' : 'bg-secondary';
    var optionText = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((opt === null || opt === void 0 ? void 0 : opt.option_text) || '');
    html += "\n            <div class=\"poll-option mb-2 position-relative\">\n                <button class=\"poll-option-btn w-100 text-start btn btn-outline-".concat(isVoted ? 'primary' : 'secondary', " position-relative overflow-hidden\" \n                        data-option-id=\"").concat((opt === null || opt === void 0 ? void 0 : opt.option_id) || '', "\" style=\"z-index: 1;\">\n                    <span class=\"position-relative\" style=\"z-index: 2; mix-blend-mode: difference; color: white;\">\n                        ").concat(optionText, "\n                    </span>\n                    <span class=\"float-end position-relative fw-bold\" style=\"z-index: 2; mix-blend-mode: difference; color: white;\">\n                        ").concat(percentage, "%\n                    </span>\n                    <div class=\"position-absolute top-0 start-0 h-100 ").concat(bgClass, "\" \n                         style=\"width: ").concat(barWidth, "; opacity: 0.5; transition: width 0.5s ease; z-index: 0;\"></div>\n                </button>\n            </div>\n        ");
  });
  html += "</div>\n        <small class=\"text-muted mt-2 d-block\">".concat((poll === null || poll === void 0 ? void 0 : poll.total_votes) || 0, " votes</small>\n    </div>");
  return html;
};
var renderReactions = function renderReactions(el) {
  // Emojis: Like 👍, Love ❤️, Haha 😂, Shock 😲, Sad 😢
  var emojis = {
    'like': '👍',
    'love': '❤️',
    'haha': '😂',
    'shock': '😲',
    'sad': '😢'
  };
  var reactions = (el === null || el === void 0 ? void 0 : el.reactions) || [];
  var userReaction = (el === null || el === void 0 ? void 0 : el.user_reaction) || null;
  var postNo = (el === null || el === void 0 ? void 0 : el.post_no) || '';
  if (!postNo) return '';
  var summaryHtml = '';
  var totalReactions = 0;
  var counts = {
    like: 0,
    love: 0,
    haha: 0,
    shock: 0,
    sad: 0
  };
  reactions.forEach(function (r) {
    if (r && r.reaction_type && r.count) {
      counts[r.reaction_type] = parseInt(r.count, 10) || 0;
      totalReactions += counts[r.reaction_type];
    }
  });
  if (totalReactions > 0) {
    summaryHtml = "<div class=\"reaction-summary mt-2 mb-2 px-2 py-1 bg-light rounded d-inline-block shadow-sm\">\n            ".concat(counts.like > 0 ? "\uD83D\uDC4D ".concat(counts.like) : '', "\n            ").concat(counts.love > 0 ? "\u2764\uFE0F ".concat(counts.love) : '', "\n            ").concat(counts.haha > 0 ? "\uD83D\uDE02 ".concat(counts.haha) : '', "\n            ").concat(counts.shock > 0 ? "\uD83D\uDE32 ".concat(counts.shock) : '', "\n            ").concat(counts.sad > 0 ? "\uD83D\uDE22 ".concat(counts.sad) : '', "\n        </div>");
  }
  var barHtml = "<div class=\"reaction-bar d-flex gap-2 mt-2 align-items-center\">";
  for (var _i = 0, _Object$entries = Object.entries(emojis); _i < _Object$entries.length; _i++) {
    var _Object$entries$_i = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_Object$entries[_i], 2),
      type = _Object$entries$_i[0],
      emoji = _Object$entries$_i[1];
    var isActive = userReaction === type;
    barHtml += "\n            <button class=\"btn btn-sm reaction-btn ".concat(isActive ? 'btn-primary' : 'btn-light text-dark', " rounded-pill shadow-sm\" \n                    data-post-no=\"").concat(postNo, "\" data-reaction=\"").concat(type, "\"\n                    style=\"transition: transform 0.2s; ").concat(isActive ? 'transform: scale(1.1);' : '', "\">\n                ").concat(emoji, " <span class=\"d-none d-md-inline ms-1 text-capitalize\" style=\"font-size: 0.8rem;\">").concat(type, "</span>\n            </button>\n        ");
  }
  barHtml += "</div>";
  return "<div class=\"engagement-section mt-3\">\n        ".concat(summaryHtml, "\n        ").concat(barHtml, "\n    </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/likeCommentButton.js ***!
  \***********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   likeCommentButton: function() { return /* binding */ likeCommentButton; }
/* harmony export */ });
var likeCommentButton = function likeCommentButton(data, commentLength) {
  return "\n   <div class=\"reaction-buttons d-flex justify-content-around border-top border-bottom py-2 mb-2 mt-3 gap-2\">\n    <button \n      class=\"btn flex-grow-1 fw-semibold rounded-pill d-flex align-items-center justify-content-center\"\n      style=\"background-color: var(--hover-color); color: var(--text-color); border: none; transition: all 0.2s;\"\n      type=\"button\" \n      id=\"likeButton".concat(data.post_no, "\" \n      name=\"").concat(data.post_no, "\">\n      <i class=\"bi bi-hand-thumbs-up me-2\" style=\"font-size: 1.1rem; color: var(--text-muted);\"></i> \n      Like \n      <span class=\"badge ms-2\" style=\"background-color: var(--border-color); color: var(--text-color);\">\n        <span class=\"likeCounter\" id=\"likeCounter").concat(data.post_no, "\">").concat(data.post_likes, "</span>\n      </span>\n    </button>\n\n    <button \n      class=\"btn flex-grow-1 fw-semibold rounded-pill d-flex align-items-center justify-content-center\"\n      style=\"background-color: var(--hover-color); color: var(--text-color); border: none; transition: all 0.2s;\"\n      type=\"button\" \n      id=\"initComment").concat(data.post_no, "\">\n        <i class=\"bi bi-chat me-2\" style=\"font-size: 1.1rem; color: var(--text-muted);\"></i> \n        Comment \n        <span class=\"badge ms-2\" style=\"background-color: var(--border-color); color: var(--text-color);\">\n          <span class=\"commentCounter\" id=\"commentCounter").concat(data.post_no, "\">").concat(commentLength, "</span>\n        </span>\n    </button>\n   \n    </div>\n    ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js":
/*!*********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/nameImageTiming.js ***!
  \*********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   nameImgTiming: function() { return /* binding */ nameImgTiming; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");


var timeAgo = function timeAgo(x) {
  return x ? (0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(x) : '';
};
var fullName = function fullName(name) {
  return "<h6 id=\"fullName\"><b>".concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(name || 'Unknown User'), "</b> </h6>");
};
var postedAt = function postedAt(date) {
  if (!(date !== null && date !== void 0 && date.date_created) || !(date !== null && date !== void 0 && date.post_time)) return '';
  return "<div class=\"timeago postTimeCal w3-right w3-opacity\" datetime='".concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(date.date_created), "' title='").concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(date.date_created)), "'> ").concat(timeAgo(date.post_time), "</div>");
};
var familyBadge = function familyBadge(famCode) {
  var clean = famCode ? String(famCode).replace(/^#+/, '') : '';
  return clean ? "<span class=\"w3-badge w3-small w3-blue w3-margin-left\" style=\"font-weight: normal; padding: 2px 6px;\">#".concat((0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(clean), "</span>") : '';
};
var nameImgTiming = function nameImgTiming(data) {
  var img = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)(data !== null && data !== void 0 && data.profileImg ? "/resources/images/profile/".concat(data.profileImg) : "/public/avatar/avatarF.png");
  var pId = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((data === null || data === void 0 ? void 0 : data.post_no) || '');
  var uId = (0,_global__WEBPACK_IMPORTED_MODULE_1__.esc)((data === null || data === void 0 ? void 0 : data.id) || '');
  var imgParam = encodeURIComponent((data === null || data === void 0 ? void 0 : data.img) || '');
  return "<a href=\"/profilepage/img?dir=img&pics=".concat(imgParam, "&pID=").concat(pId, "&path=profile&id=").concat(uId, "\"> <img src=\"").concat(img, "\" alt=\"img\" class=\"w3-left w3-circle w3-margin-right postImg\" style=\"width:60px\">\n        </a>\n        ").concat(postedAt(data), "\n        <div style=\"display: flex; align-items: center;\">\n            ").concat(fullName(data === null || data === void 0 ? void 0 : data.fullName), " ").concat(familyBadge(data === null || data === void 0 ? void 0 : data.postFamCode), "\n        </div>");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js":
/*!********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/showPostImages.js ***!
  \********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showPostImg: function() { return /* binding */ showPostImg; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var showPostImg = function showPostImg(data) {
  // GET THE IMAGES WITH VALUES F=IF THERE ARE ANY. FILTER USING THE OBJECT KEY AND THEN MAP THROUGH THE VALUE
  var postImagesWithValues = Object.keys(data).filter(function (key) {
    return key.startsWith('post_img') && data[key] !== null;
  }).map(function (el) {
    return data[el];
  });
  var picsImgHtml = function picsImgHtml(imgElement, i, postNo) {
    var nameSafe = (0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(imgElement);
    return "\n    <a href=\"/profilepage/img?dir=img&pics=".concat(encodeURIComponent(imgElement), "&pID=").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(postNo), "&path=post\">\n      <div class=\"w3-half\">\n        <img src=\"/resources/images/post/").concat(nameSafe, "\" style=\"width:100%\" alt=\"images").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(i), "\" class=\"w3-margin-bottom w3-hover-sepia\" id=\"postImage").concat((0,_global__WEBPACK_IMPORTED_MODULE_0__.esc)(i), "\">\n      </div>\n    </a>\n  ");
  };
  var imgElements = postImagesWithValues.map(function (pics, i) {
    return picsImgHtml(pics, i, data.post_no);
  }).join('');
  return "\n    <div class=\"w3-row-padding\" style=\"margin:0 -16px\">\n      ".concat(imgElements, "\n      <br>\n    </div>\n  ");
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/img.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/profilePage/img.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _helper_images__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helper/images */ "./resources/asset/js/components/helper/images.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");





var profilePics = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePics');
if (profilePics) {
  profilePics.addEventListener('click', function () {
    var formProfile = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('formProfilePics');
    if (formProfile) formProfile.style.display = "block";
  });
}

// FOR PROFILE IMAGE CHANGE
var uploadButtonProfilePics = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('uploadButtonProfilePics');
var profileImageFile = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profileImageFile');
var profileImgFileNames = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profileImgFileNames');
if (uploadButtonProfilePics && profileImageFile && profileImgFileNames) {
  (0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButtonProfilePics', 'profileImageFile', 'profileImgFileNames');
}

// FOR POST MODAL IMAGE UPLOAD  
var uploadButton = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('uploadButton');
var post_img = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('post_img');
var postModalImgFileNames = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postModalImgFileNames');
if (uploadButton && post_img && postModalImgFileNames) {
  (0,_helper_images__WEBPACK_IMPORTED_MODULE_1__.showImageFileUploadFn)('uploadButton', 'post_img', 'postModalImgFileNames');
}
var submitProfilePics = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('submitProfilePics');
if (submitProfilePics) {
  submitProfilePics.addEventListener('click', function () {
    // Get the form element
    var form = document.getElementById("formProfilePics");
    if (!form) return;

    // Create a FormData object and append the form data to it
    var formData = new FormData(form);
    var options = {
      xsrfCookieName: 'XSRF-TOKEN',
      xsrfHeaderName: 'X-XSRF-TOKEN'
    };
    // send form data using axios post method

    axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/member/profilePage/profileImg', formData, options).then(function (response) {
      var profilePicsNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification');
      if (profilePicsNotification) {
        profilePicsNotification.innerHTML = response.data;
        (0,_global__WEBPACK_IMPORTED_MODULE_0__.log)(response.data, "profilePicsNotification");
        if (response.data.message === "Profile image updated") {
          profilePicsNotification.classList.add('w3-green');
          profilePicsNotification.innerHTML = response.data.message;
          // Reload the page
          location.reload();
        }
      }
    }).catch(function (error) {
      var profilePicsNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification');
      if (profilePicsNotification) {
        profilePicsNotification.classList.add('w3-red');
        profilePicsNotification.innerHTML = error.message;
      }
    });
    var profilePicsNotification = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('profilePicsNotification');
    if (profilePicsNotification) {
      profilePicsNotification.innerHTML = "";
    }
  });
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/index.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/index.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./modal */ "./resources/asset/js/components/profilePage/modal.js");
/* harmony import */ var _img__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./img */ "./resources/asset/js/components/profilePage/img.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./rsvpBtn */ "./resources/asset/js/components/profilePage/rsvpBtn.js");
/* harmony import */ var _rsvpBtn__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_rsvpBtn__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _registerPushNotification__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./registerPushNotification */ "./resources/asset/js/components/profilePage/registerPushNotification.js");
/* harmony import */ var _periodicSync__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./periodicSync */ "./resources/asset/js/components/profilePage/periodicSync.js");
/* harmony import */ var _editProfile__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editProfile */ "./resources/asset/js/components/profilePage/editProfile.js");
/* harmony import */ var _engagement__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./engagement */ "./resources/asset/js/components/profilePage/engagement.js");
/* harmony import */ var _pwaShareTarget__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./pwaShareTarget */ "./resources/asset/js/components/profilePage/pwaShareTarget.js");
/* harmony import */ var _kinship_kinshipRadar__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../kinship/kinshipRadar */ "./resources/asset/js/components/kinship/kinshipRadar.js");


localStorage.removeItem('redirect');










// Code-split heavy modal engines (142KB+ of composer media/Tus/emoji/video compression & event creation)
// so mobile first-load & scroll stay feather-light, while preloading on first tap/modal intent or idle window.
var composerBundlePromise = null;
var ensureComposerBundle = function ensureComposerBundle() {
  if (!composerBundlePromise) {
    composerBundlePromise = Promise.all([__webpack_require__.e(/*! import() | profile-composer */ "profile-composer").then(__webpack_require__.bind(__webpack_require__, /*! ./allEvents */ "./resources/asset/js/components/profilePage/allEvents.js")), __webpack_require__.e(/*! import() | profile-composer */ "profile-composer").then(__webpack_require__.bind(__webpack_require__, /*! ./postEmojiImgProcess */ "./resources/asset/js/components/profilePage/postEmojiImgProcess.js"))]).catch(function (err) {
      return console.error('[ComposerChunk]', err);
    });
  }
  return composerBundlePromise;
};
window.ensureComposerBundle = ensureComposerBundle;
var createEventBundlePromise = null;
var ensureCreateEventBundle = function ensureCreateEventBundle() {
  if (!createEventBundlePromise) {
    createEventBundlePromise = Promise.all(/*! import() | profile-create-event */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("profile-create-event")]).then(__webpack_require__.bind(__webpack_require__, /*! ./createEvent */ "./resources/asset/js/components/profilePage/createEvent.js")).catch(function (err) {
      return console.error('[CreateEventChunk]', err);
    });
  }
  return createEventBundlePromise;
};
var runBootHooks = function runBootHooks() {
  (0,_engagement__WEBPACK_IMPORTED_MODULE_6__.initEngagementListeners)();
  (0,_engagement__WEBPACK_IMPORTED_MODULE_6__.initMemories)();
  (0,_pwaShareTarget__WEBPACK_IMPORTED_MODULE_7__.initPwaShareTargetHandler)();
  (0,_pwaShareTarget__WEBPACK_IMPORTED_MODULE_7__.initSmartClipboardPaste)();

  // Eagerly attach intent preloads on composer & event triggers so modal opening feels instantaneous
  ['openPostModalTrigger', 'openPostModalTriggerMobile'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) {
      el.addEventListener('pointerdown', ensureComposerBundle, {
        once: true,
        passive: true
      });
      el.addEventListener('focusin', ensureComposerBundle, {
        once: true,
        passive: true
      });
    }
  });
  document.addEventListener('show.bs.modal', function (e) {
    var _e$target;
    var targetId = e === null || e === void 0 ? void 0 : (_e$target = e.target) === null || _e$target === void 0 ? void 0 : _e$target.id;
    if (targetId === 'postModal') {
      ensureComposerBundle();
    } else if (targetId === 'createEventModal') {
      ensureCreateEventBundle();
    }
  });

  // Warm both chunks quietly during browser idle time after initial mobile scroll/paint settles
  var warmModalChunks = function warmModalChunks() {
    ensureComposerBundle();
    ensureCreateEventBundle();
  };
  if (typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(warmModalChunks, {
      timeout: 2200
    });
  } else {
    setTimeout(warmModalChunks, 1200);
  }
};
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', runBootHooks, {
    once: true
  });
} else {
  runBootHooks();
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/modal.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/modal.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");




// import { postFormData } from "../helper/http"

try {
  var _id, _id2;
  // NEW MESSAGE MODAL
  var showModal = function showModal() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id01').style.display = 'block';
  };

  // CREATE EVENT MODAL
  var showEvent = function showEvent() {
    return (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('id_event_modal').style.display = 'block';
  };

  //EVENT ACTION
  (_id = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('createEvent')) === null || _id === void 0 ? void 0 : _id.addEventListener('click', showEvent);
  (_id2 = (0,_global__WEBPACK_IMPORTED_MODULE_0__.id)('postMsg')) === null || _id2 === void 0 ? void 0 : _id2.addEventListener('click', showModal);

  // handle post message
} catch (e) {
  ;(0,_global__WEBPACK_IMPORTED_MODULE_0__.showError)(e);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/periodicSync.js":
/*!*******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/periodicSync.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);


// utility for periodic sync  
if ('periodicSync' in navigator) {
  (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var status, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.next = 1;
          return navigator.permissions.query({
            name: 'periodic-background-sync'
          });
        case 1:
          status = _context.sent;
          if (status.state === 'granted') {
            console.log('Background sync permission granted');
          } else {
            console.log('Background sync permission denied');
          }
          _context.prev = 2;
          _context.next = 3;
          return navigator.periodicSync.register('content-sync', {
            minInterval: 24 * 60 * 60 * 1000 // Minimum interval in milliseconds (e.g., 1 day)
          });
        case 3:
          console.log('Periodic Sync registered');
          _context.next = 5;
          break;
        case 4:
          _context.prev = 4;
          _t = _context["catch"](2);
          console.error('Periodic Sync registration failed:', _t);
        case 5:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[2, 4]]);
  }))(); // Immediately invoke the async function
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/pwaShareTarget.js":
/*!*********************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/pwaShareTarget.js ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initPwaShareTargetHandler: function() { return /* binding */ initPwaShareTargetHandler; },
/* harmony export */   initSmartClipboardPaste: function() { return /* binding */ initSmartClipboardPaste; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);




function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
var PWA_SHARE_DB = 'FP_PWA_ShareTarget_DB';
var PWA_SHARE_STORE = 'shared_items';
function openPwaShareDB() {
  return new Promise(function (resolve, reject) {
    if (!('indexedDB' in window)) {
      return reject(new Error('IndexedDB not supported'));
    }
    var req = indexedDB.open(PWA_SHARE_DB, 1);
    req.onupgradeneeded = function (e) {
      var db = e.target.result;
      if (!db.objectStoreNames.contains(PWA_SHARE_STORE)) {
        db.createObjectStore(PWA_SHARE_STORE, {
          keyPath: 'id',
          autoIncrement: true
        });
      }
    };
    req.onsuccess = function () {
      return resolve(req.result);
    };
    req.onerror = function () {
      return reject(req.error);
    };
  });
}
function peekSharedFromIndexedDB() {
  return _peekSharedFromIndexedDB.apply(this, arguments);
}
function _peekSharedFromIndexedDB() {
  _peekSharedFromIndexedDB = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
    var db;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.next = 1;
          return openPwaShareDB();
        case 1:
          db = _context2.sent;
          return _context2.abrupt("return", new Promise(function (resolve, reject) {
            var tx = db.transaction(PWA_SHARE_STORE, 'readonly');
            var store = tx.objectStore(PWA_SHARE_STORE);
            var getAllReq = store.getAll();
            getAllReq.onsuccess = function () {
              var items = getAllReq.result || [];
              resolve(items.length > 0 ? items[items.length - 1] : null);
            };
            getAllReq.onerror = function () {
              return reject(getAllReq.error);
            };
          }));
        case 2:
        case "end":
          return _context2.stop();
      }
    }, _callee2);
  }));
  return _peekSharedFromIndexedDB.apply(this, arguments);
}
function clearSharedFromIndexedDB() {
  return _clearSharedFromIndexedDB.apply(this, arguments);
}
function _clearSharedFromIndexedDB() {
  _clearSharedFromIndexedDB = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3() {
    var db;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
      while (1) switch (_context3.prev = _context3.next) {
        case 0:
          _context3.next = 1;
          return openPwaShareDB();
        case 1:
          db = _context3.sent;
          return _context3.abrupt("return", new Promise(function (resolve, reject) {
            var tx = db.transaction(PWA_SHARE_STORE, 'readwrite');
            var store = tx.objectStore(PWA_SHARE_STORE);
            store.clear();
            tx.oncomplete = function () {
              return resolve(true);
            };
            tx.onerror = function () {
              return reject(tx.error);
            };
          }));
        case 2:
        case "end":
          return _context3.stop();
      }
    }, _callee3);
  }));
  return _clearSharedFromIndexedDB.apply(this, arguments);
}
function waitForPostModal() {
  var timeoutMs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 5000;
  return new Promise(function (resolve) {
    var start = Date.now();
    var _check = function check() {
      var _window$bootstrap;
      var el = document.getElementById('postModal');
      if (el && ((_window$bootstrap = window.bootstrap) !== null && _window$bootstrap !== void 0 && _window$bootstrap.Modal || document.querySelector('[data-bs-target="#postModal"]'))) {
        return resolve(el);
      }
      if (Date.now() - start >= timeoutMs) {
        return resolve(el || null);
      }
      setTimeout(_check, 100);
    };
    _check();
  });
}
function reconstructSharedFile(item, index) {
  if (item instanceof File) {
    return item;
  }
  if (item instanceof Blob) {
    var ext = item.type && item.type.split('/')[1] ? item.type.split('/')[1].split(';')[0] : 'jpg';
    return new File([item], "shared-memory-".concat(index, ".").concat(ext), {
      type: item.type || 'image/jpeg'
    });
  }
  if (item && item.buffer) {
    var type = item.type || 'application/octet-stream';
    var name = item.name || "shared-memory-".concat(index);
    return new File([item.buffer], name, {
      type,
      lastModified: item.lastModified || Date.now()
    });
  }
  return null;
}
function initPwaShareTargetHandler() {
  return _initPwaShareTargetHandler.apply(this, arguments);
}

/**
 * Smart Clipboard Camera Roll Bridge (Zero-Install iOS & Desktop Solution)
 */
function _initPwaShareTargetHandler() {
  _initPwaShareTargetHandler = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4() {
    var urlParams, hasPwaShareParam, ticket, sharedData, cameFromIndexedDB, ticketParam, resp, json, cleanUrl, postModalEl, modal, trigger, captionText, textarea, editor, previewContainer, previewList, fileInput, dt, countEl, label, notif, quickBtn, storyBtn, _t4, _t5;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
      while (1) switch (_context4.prev = _context4.next) {
        case 0:
          urlParams = new URLSearchParams(window.location.search);
          hasPwaShareParam = urlParams.has('pwa_shared');
          ticket = urlParams.get('ticket');
          sharedData = null;
          cameFromIndexedDB = false; // 1. Check IndexedDB storage from Service Worker (non-destructive peek first)
          _context4.prev = 1;
          _context4.next = 2;
          return peekSharedFromIndexedDB();
        case 2:
          sharedData = _context4.sent;
          if (sharedData) {
            cameFromIndexedDB = true;
          }
          _context4.next = 4;
          break;
        case 3:
          _context4.prev = 3;
          _t4 = _context4["catch"](1);
          console.warn('[PWA Share Target] IndexedDB lookup error:', _t4);
        case 4:
          if (sharedData) {
            _context4.next = 10;
            break;
          }
          _context4.prev = 5;
          ticketParam = ticket ? "?ticket=".concat(encodeURIComponent(ticket)) : '';
          _context4.next = 6;
          return fetch('/api/media/pwa-shared' + ticketParam, {
            credentials: 'same-origin'
          });
        case 6:
          resp = _context4.sent;
          if (!resp.ok) {
            _context4.next = 8;
            break;
          }
          _context4.next = 7;
          return resp.json();
        case 7:
          json = _context4.sent;
          if (json.status === 'success' && json.data) {
            sharedData = json.data;
          }
        case 8:
          _context4.next = 10;
          break;
        case 9:
          _context4.prev = 9;
          _t5 = _context4["catch"](5);
          console.warn('[PWA Share Target] Server fallback lookup error:', _t5);
        case 10:
          if (!(!sharedData && !hasPwaShareParam && !ticket)) {
            _context4.next = 11;
            break;
          }
          return _context4.abrupt("return");
        case 11:
          // Clean up query param from URL without page reload
          if (hasPwaShareParam || ticket) {
            cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
          }
          if (sharedData) {
            _context4.next = 12;
            break;
          }
          if (hasPwaShareParam && window.Swal) {
            window.Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'info',
              title: 'No shared media was received. Please try sharing again or tap Attach Photos.',
              showConfirmButton: false,
              timer: 4500,
              timerProgressBar: true
            });
          }
          return _context4.abrupt("return");
        case 12:
          _context4.next = 13;
          return waitForPostModal(5000);
        case 13:
          postModalEl = _context4.sent;
          if (postModalEl) {
            _context4.next = 14;
            break;
          }
          return _context4.abrupt("return");
        case 14:
          if (window.bootstrap && window.bootstrap.Modal) {
            modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
            modal.show();
          } else {
            trigger = document.querySelector('[data-bs-target="#postModal"]');
            if (trigger) {
              trigger.click();
            }
          }
          if (cameFromIndexedDB) {
            clearSharedFromIndexedDB().catch(function () {});
          }

          // 1. Populate Caption / Message
          captionText = sharedData.caption || sharedData.text || sharedData.title || '';
          if (captionText) {
            textarea = document.getElementById('postMessage');
            if (textarea) {
              textarea.value = captionText;
              textarea.dispatchEvent(new Event('input', {
                bubbles: true
              }));
            }
            editor = document.getElementById('postMessageEditor');
            if (editor) {
              editor.innerText = captionText;
              editor.dispatchEvent(new Event('input', {
                bubbles: true
              }));
            }
          }

          // 2. Handle Attached Media Files
          previewContainer = document.getElementById('imagePreviewContainer');
          previewList = document.getElementById('imagePreviewList');
          if (sharedData.mediaFiles && sharedData.mediaFiles.length > 0) {
            fileInput = document.getElementById('imageUpload');
            if (fileInput && window.DataTransfer) {
              dt = new DataTransfer();
              sharedData.mediaFiles.forEach(function (item, idx) {
                var file = reconstructSharedFile(item, idx);
                if (file && file.size > 0) {
                  dt.items.add(file);
                }
              });
              if (dt.files.length > 0) {
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change', {
                  bubbles: true
                }));
              }
            }
          } else if (sharedData.files && sharedData.files.length > 0 && previewContainer && previewList) {
            previewContainer.classList.remove('d-none');
            previewList.innerHTML = '';
            previewContainer.querySelectorAll('input[name="shared_temp_images[]"]').forEach(function (el) {
              return el.remove();
            });
            sharedData.files.forEach(function (fileName) {
              var isVideo = /\.(mp4|webm|mov|ogg)$/i.test(fileName);
              if (isVideo) {
                var vid = document.createElement('video');
                vid.src = '/resources/images/post/temp/' + fileName;
                vid.className = 'rounded-3 shadow-sm object-fit-cover';
                vid.style.width = '75px';
                vid.style.height = '75px';
                vid.muted = true;
                vid.playsInline = true;
                previewList.appendChild(vid);
              } else {
                var img = document.createElement('img');
                img.src = '/resources/images/post/temp/' + fileName;
                img.className = 'rounded-3 shadow-sm object-fit-cover';
                img.style.width = '75px';
                img.style.height = '75px';
                previewList.appendChild(img);
              }
              var hidden = document.createElement('input');
              hidden.type = 'hidden';
              hidden.name = 'shared_temp_images[]';
              hidden.value = fileName;
              previewContainer.appendChild(hidden);
            });
            countEl = document.getElementById('postModalImgFileNames');
            if (countEl) {
              label = sharedData.files.length === 1 ? '1 shared memory' : "".concat(sharedData.files.length, " shared memories");
              countEl.textContent = label;
            }
          }

          // 3. User Feedback & 1-Tap Quick Publish Action in Modal
          notif = document.getElementById('formPostMessageModal_notification');
          if (notif) {
            notif.innerHTML = "\n            <div class=\"alert alert-light border border-success-subtle py-2.5 px-3 small rounded-3 mb-3 shadow-xs d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2\" style=\"background: rgba(16, 185, 129, 0.05);\">\n                <div class=\"d-flex align-items-center gap-2\">\n                    <i class=\"bi bi-phone-fill text-success fs-5 flex-shrink-0\"></i>\n                    <div>\n                        <div class=\"fw-bold text-dark\" style=\"font-size: 0.85rem;\">Shared Media Received!</div>\n                        <div class=\"text-muted\" style=\"font-size: 0.78rem;\">Ready to publish to your family timeline.</div>\n                    </div>\n                </div>\n                <div class=\"d-flex align-items-center gap-1.5 flex-shrink-0\">\n                    <button type=\"button\" id=\"quickPublishBtn\" class=\"btn btn-sm btn-primary rounded-pill fw-bold px-3 py-1.5 d-flex align-items-center gap-1 shadow-xs\" style=\"font-size: 0.78rem; background-color: #a31621; border-color: #a31621; min-height: 44px;\">\n                        <i class=\"bi bi-send-fill\"></i> Publish Now\n                    </button>\n                    <button type=\"button\" id=\"addStoryCaptionBtn\" class=\"btn btn-sm btn-outline-secondary rounded-pill fw-medium px-2.5 py-1.5\" style=\"font-size: 0.78rem; min-height: 44px;\">\n                        Add Story\n                    </button>\n                </div>\n            </div>\n        ";
            quickBtn = document.getElementById('quickPublishBtn');
            if (quickBtn) {
              quickBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var submitBtn = document.getElementById('submitPost');
                if (submitBtn) {
                  submitBtn.click();
                }
              });
            }
            storyBtn = document.getElementById('addStoryCaptionBtn');
            if (storyBtn) {
              storyBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var editor = document.getElementById('postMessageEditor') || document.getElementById('postMessage');
                if (editor) {
                  editor.focus();
                }
              });
            }
          }
        case 15:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 3], [5, 9]]);
  }));
  return _initPwaShareTargetHandler.apply(this, arguments);
}
function initSmartClipboardPaste() {
  window.pastePhotoFromClipboard = /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
    var items, files, _iterator, _step, item, _iterator2, _step2, type, blob, ext, file, imageInput, dt, postModalEl, modal, Toast, _t, _t2, _t3;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          if (!(!navigator.clipboard || !navigator.clipboard.read)) {
            _context.next = 1;
            break;
          }
          if (window.Swal) {
            Swal.fire({
              icon: 'info',
              title: 'Clipboard Access',
              text: 'To attach a copied photo, press Cmd+V (Mac) or Ctrl+V (Windows/Android) whilst the composer is open.',
              confirmButtonColor: '#a31621'
            });
          }
          return _context.abrupt("return");
        case 1:
          _context.prev = 1;
          _context.next = 2;
          return navigator.clipboard.read();
        case 2:
          items = _context.sent;
          files = [];
          _iterator = _createForOfIteratorHelper(items);
          _context.prev = 3;
          _iterator.s();
        case 4:
          if ((_step = _iterator.n()).done) {
            _context.next = 13;
            break;
          }
          item = _step.value;
          _iterator2 = _createForOfIteratorHelper(item.types);
          _context.prev = 5;
          _iterator2.s();
        case 6:
          if ((_step2 = _iterator2.n()).done) {
            _context.next = 9;
            break;
          }
          type = _step2.value;
          if (!type.startsWith('image/')) {
            _context.next = 8;
            break;
          }
          _context.next = 7;
          return item.getType(type);
        case 7:
          blob = _context.sent;
          ext = type.split('/')[1] ? type.split('/')[1].replace('+xml', '') : 'png';
          file = new File([blob], "clipboard_photo_".concat(Date.now(), ".").concat(ext), {
            type
          });
          files.push(file);
        case 8:
          _context.next = 6;
          break;
        case 9:
          _context.next = 11;
          break;
        case 10:
          _context.prev = 10;
          _t = _context["catch"](5);
          _iterator2.e(_t);
        case 11:
          _context.prev = 11;
          _iterator2.f();
          return _context.finish(11);
        case 12:
          _context.next = 4;
          break;
        case 13:
          _context.next = 15;
          break;
        case 14:
          _context.prev = 14;
          _t2 = _context["catch"](3);
          _iterator.e(_t2);
        case 15:
          _context.prev = 15;
          _iterator.f();
          return _context.finish(15);
        case 16:
          if (files.length > 0) {
            imageInput = document.getElementById('imageUpload');
            if (imageInput && window.DataTransfer) {
              dt = new DataTransfer();
              files.forEach(function (f) {
                return dt.items.add(f);
              });
              imageInput.files = dt.files;
              imageInput.dispatchEvent(new Event('change', {
                bubbles: true
              }));
            }
            postModalEl = document.getElementById('postModal');
            if (postModalEl && window.bootstrap && window.bootstrap.Modal) {
              modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
              modal.show();
            }
            if (window.Swal) {
              Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
              });
              Toast.fire({
                icon: 'success',
                title: 'Photo attached from camera roll clipboard!'
              });
            }
          } else {
            if (window.Swal) {
              Swal.fire({
                icon: 'info',
                title: 'No Photo in Clipboard',
                text: 'Open Apple Photos or your phone gallery, tap Share &rarr; Copy (or copy a photo), then tap this button again.',
                confirmButtonColor: '#a31621'
              });
            }
          }
          _context.next = 18;
          break;
        case 17:
          _context.prev = 17;
          _t3 = _context["catch"](1);
          console.warn('[Smart Clipboard] Error reading clipboard:', _t3);
          if (window.Swal) {
            Swal.fire({
              icon: 'info',
              title: 'Clipboard Permission',
              text: 'Please allow clipboard access when prompted to paste photos directly from your camera roll.',
              confirmButtonColor: '#a31621'
            });
          }
        case 18:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[1, 17], [3, 14, 15, 16], [5, 10, 11, 12]]);
  }));
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/registerPushNotification.js":
/*!*******************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/registerPushNotification.js ***!
  \*******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   disablePushNotifications: function() { return /* binding */ disablePushNotifications; },
/* harmony export */   enablePushNotifications: function() { return /* binding */ enablePushNotifications; },
/* harmony export */   getPushState: function() { return /* binding */ getPushState; },
/* harmony export */   isPushSubscribed: function() { return /* binding */ isPushSubscribed; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");

var _document$querySelect;

/**
 * Web Push — the single client module (PUSH-1 / PUSH-4).
 *
 * Side effect on import: registers the service worker and, if the browser
 * permission is already `granted`, re-syncs the subscription so the server's
 * record stays fresh.
 *
 * Exports for a Settings toggle (must be called from a real user gesture):
 *   enablePushNotifications()  -> Promise<{ok, reason?}>
 *   disablePushNotifications() -> Promise<{ok}>
 *   getPushState()             -> 'unsupported' | 'default' | 'granted' | 'denied'
 *   isPushSubscribed()         -> Promise<boolean>
 */



var VAPID_PUBLIC_KEY = "BAvqqppvGj5V0DqzieyYq5nGu9EW_db01_7jXO1_Nk-8UZzKJpCs1eGYx5d0yuBe7q3xu6oWaFS8etO9lazRMMo" || (0) || 0;
var PUSH_PROMPT_DISMISSED_KEY = 'push_prompt_dismissed';
var pushSupported = function pushSupported() {
  return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
};
function keyToBase64(subscription, name) {
  return btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey(name))));
}
function syncToServer(_x) {
  return _syncToServer.apply(this, arguments);
} // ---- import-time bootstrap: keep an existing grant in sync -----------------
function _syncToServer() {
  _syncToServer = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2(subscription) {
    var _document$querySelect3;
    var currentEndpoint, currentUserId, now, syncKey, cached, token, payload, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          if (!(!subscription || !subscription.endpoint)) {
            _context2.next = 1;
            break;
          }
          return _context2.abrupt("return");
        case 1:
          currentEndpoint = subscription.endpoint;
          currentUserId = (typeof document !== 'undefined' ? ((_document$querySelect3 = document.querySelector('meta[name="user-id"]')) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.getAttribute('content')) || '' : '') || localStorage.getItem('requesterId') || '';
          now = Date.now();
          syncKey = 'fp_last_push_sync';
          _context2.prev = 2;
          cached = JSON.parse(localStorage.getItem(syncKey) || 'null');
          if (!(cached && cached.userId === currentUserId && cached.endpoint === currentEndpoint && now - cached.time < 12 * 60 * 60 * 1000)) {
            _context2.next = 3;
            break;
          }
          return _context2.abrupt("return");
        case 3:
          _context2.next = 5;
          break;
        case 4:
          _context2.prev = 4;
          _t = _context2["catch"](2);
        case 5:
          token = (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)();
          payload = {
            endpoint: currentEndpoint,
            token: token,
            keys: {
              p256dh: keyToBase64(subscription, 'p256dh'),
              auth: keyToBase64(subscription, 'auth')
            }
          };
          _context2.next = 6;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/pushNotification/subscription', payload, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': token,
              'X-CSRF-TOKEN': token
            }
          });
        case 6:
          try {
            localStorage.setItem(syncKey, JSON.stringify({
              userId: currentUserId,
              endpoint: currentEndpoint,
              time: now
            }));
            localStorage.setItem('fp_push_subscribed', 'true');
          } catch (_) {}
        case 7:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[2, 4]]);
  }));
  return _syncToServer.apply(this, arguments);
}
if (pushSupported()) {
  var runPushSync = function runPushSync() {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.ensureServiceWorkerReady)().then(function (swReg) {
      return swReg.pushManager.getSubscription().then(function (sub) {
        if (sub) return syncToServer(sub).catch(function (e) {
          var _e$response, _e$response$data;
          return console.warn('[push] resync failed', (e === null || e === void 0 ? void 0 : (_e$response = e.response) === null || _e$response === void 0 ? void 0 : (_e$response$data = _e$response.data) === null || _e$response$data === void 0 ? void 0 : _e$response$data.message) || e.message);
        });
        if (Notification.permission === 'granted' && VAPID_PUBLIC_KEY) {
          return doSubscribe(swReg).catch(function (e) {
            var _e$response2, _e$response2$data;
            return console.warn('[push] auto-subscribe skipped', (e === null || e === void 0 ? void 0 : (_e$response2 = e.response) === null || _e$response2 === void 0 ? void 0 : (_e$response2$data = _e$response2.data) === null || _e$response2$data === void 0 ? void 0 : _e$response2$data.message) || e.message);
          });
        }
      });
    }).catch(function (err) {
      return console.warn('[push] SW ready check failed', err);
    });
  };
  if (typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(runPushSync, {
      timeout: 3500
    });
  } else {
    setTimeout(runPushSync, 1500);
  }
}
function doSubscribe(_x2) {
  return _doSubscribe.apply(this, arguments);
}
function _doSubscribe() {
  _doSubscribe = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee4(swReg) {
    var subscribeAttempt, sub, oldSub, _t2;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context4) {
      while (1) switch (_context4.prev = _context4.next) {
        case 0:
          subscribeAttempt = /*#__PURE__*/function () {
            var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3() {
              return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
                while (1) switch (_context3.prev = _context3.next) {
                  case 0:
                    return _context3.abrupt("return", swReg.pushManager.subscribe({
                      userVisibleOnly: true,
                      applicationServerKey: (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.urlBase64ToUint8Array)(VAPID_PUBLIC_KEY)
                    }));
                  case 1:
                  case "end":
                    return _context3.stop();
                }
              }, _callee3);
            }));
            return function subscribeAttempt() {
              return _ref2.apply(this, arguments);
            };
          }();
          _context4.prev = 1;
          _context4.next = 2;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.withPushTimeout)(subscribeAttempt(), 10000, 'Push service subscription timed out');
        case 2:
          sub = _context4.sent;
          _context4.next = 8;
          break;
        case 3:
          _context4.prev = 3;
          _t2 = _context4["catch"](1);
          if (!(_t2 && _t2.name === 'InvalidStateError')) {
            _context4.next = 7;
            break;
          }
          _context4.next = 4;
          return swReg.pushManager.getSubscription();
        case 4:
          oldSub = _context4.sent;
          if (!oldSub) {
            _context4.next = 5;
            break;
          }
          _context4.next = 5;
          return oldSub.unsubscribe();
        case 5:
          _context4.next = 6;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.withPushTimeout)(subscribeAttempt(), 10000, 'Push service subscription timed out');
        case 6:
          sub = _context4.sent;
          _context4.next = 8;
          break;
        case 7:
          throw _t2;
        case 8:
          _context4.next = 9;
          return syncToServer(sub);
        case 9:
          return _context4.abrupt("return", sub);
        case 10:
        case "end":
          return _context4.stop();
      }
    }, _callee4, null, [[1, 3]]);
  }));
  return _doSubscribe.apply(this, arguments);
}
function showPushPrompt() {
  var _document$querySelect2, _document$getElementB, _document$getElementB2;
  var userId = typeof document !== 'undefined' ? (_document$querySelect2 = document.querySelector('meta[name="user-id"]')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.getAttribute('content') : '';
  if (!userId) return;
  if (!VAPID_PUBLIC_KEY || Notification.permission !== 'default') return;
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.isPushDismissedWithinCooldown)(PUSH_PROMPT_DISMISSED_KEY)) return;
  if (document.getElementById('push-permission-banner') || document.getElementById('push-notification-banner')) return;
  var banner = document.createElement('div');
  banner.id = 'push-permission-banner';
  banner.innerHTML = "\n    <div class=\"pwa-banner-card\">\n      <div class=\"pwa-banner-header\">\n        <div class=\"pwa-banner-text\">\n          <h2 class=\"pwa-banner-title\">Stay connected with your family</h2>\n          <p>Allow notifications for new friend requests, events, and family updates.</p>\n        </div>\n        <button type=\"button\" id=\"push-prompt-close\" class=\"pwa-btn-close\" aria-label=\"Dismiss\">&times;</button>\n      </div>\n      <div class=\"pwa-banner-actions\">\n        <button type=\"button\" id=\"push-prompt-enable\" class=\"pwa-btn pwa-btn-primary\">Allow notifications</button>\n      </div>\n    </div>\n  ";
  document.body.appendChild(banner);
  setTimeout(function () {
    return banner.classList.add('show');
  }, 100);
  var dismiss = function dismiss() {
    (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.recordPushDismissal)(PUSH_PROMPT_DISMISSED_KEY);
    banner.remove();
  };
  (_document$getElementB = document.getElementById('push-prompt-close')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.addEventListener('click', dismiss);
  (_document$getElementB2 = document.getElementById('push-prompt-enable')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.addEventListener('click', /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(event) {
      var button, result;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            button = event.currentTarget;
            button.disabled = true;
            button.textContent = 'Enabling...';
            _context.next = 1;
            return enablePushNotifications();
          case 1:
            result = _context.sent;
            if (!result.ok) {
              _context.next = 2;
              break;
            }
            ;(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.clearPushDismissal)(PUSH_PROMPT_DISMISSED_KEY);
            banner.remove();
            return _context.abrupt("return");
          case 2:
            button.disabled = false;
            button.textContent = 'Allow notifications';
            if (result.reason === 'denied') dismiss();
          case 3:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    return function (_x3) {
      return _ref.apply(this, arguments);
    };
  }());
}
if (pushSupported()) {
  window.addEventListener('load', showPushPrompt, {
    once: true
  });
}

// ---- public API ----------------------------------------------------------
function getPushState() {
  if (!pushSupported()) return 'unsupported';
  return Notification.permission; // 'default' | 'granted' | 'denied'
}
function isPushSubscribed() {
  return _isPushSubscribed.apply(this, arguments);
}
function _isPushSubscribed() {
  _isPushSubscribed = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee5() {
    var swReg, _t3, _t4;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context5) {
      while (1) switch (_context5.prev = _context5.next) {
        case 0:
          if (pushSupported()) {
            _context5.next = 1;
            break;
          }
          return _context5.abrupt("return", false);
        case 1:
          _context5.prev = 1;
          _context5.next = 2;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.ensureServiceWorkerReady)();
        case 2:
          swReg = _context5.sent;
          _context5.next = 3;
          return swReg.pushManager.getSubscription();
        case 3:
          _t3 = _context5.sent;
          return _context5.abrupt("return", _t3 !== null);
        case 4:
          _context5.prev = 4;
          _t4 = _context5["catch"](1);
          return _context5.abrupt("return", false);
        case 5:
        case "end":
          return _context5.stop();
      }
    }, _callee5, null, [[1, 4]]);
  }));
  return _isPushSubscribed.apply(this, arguments);
}
function enablePushNotifications() {
  return _enablePushNotifications.apply(this, arguments);
}
function _enablePushNotifications() {
  _enablePushNotifications = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee6() {
    var permission, swReg, existing, _t5;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context6) {
      while (1) switch (_context6.prev = _context6.next) {
        case 0:
          if (pushSupported()) {
            _context6.next = 1;
            break;
          }
          return _context6.abrupt("return", {
            ok: false,
            reason: 'unsupported'
          });
        case 1:
          if (VAPID_PUBLIC_KEY) {
            _context6.next = 2;
            break;
          }
          return _context6.abrupt("return", {
            ok: false,
            reason: 'misconfigured'
          });
        case 2:
          permission = Notification.permission;
          if (!(permission === 'default')) {
            _context6.next = 4;
            break;
          }
          _context6.next = 3;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.withPushTimeout)(Notification.requestPermission(), 20000, 'Notification permission prompt timed out');
        case 3:
          permission = _context6.sent;
        case 4:
          if (!(permission !== 'granted')) {
            _context6.next = 5;
            break;
          }
          if (typeof window.showPushPermissionRecovery === 'function') {
            window.showPushPermissionRecovery();
          }
          return _context6.abrupt("return", {
            ok: false,
            reason: permission
          });
        case 5:
          _context6.prev = 5;
          _context6.next = 6;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.ensureServiceWorkerReady)();
        case 6:
          swReg = _context6.sent;
          _context6.next = 7;
          return swReg.pushManager.getSubscription();
        case 7:
          existing = _context6.sent;
          if (!existing) {
            _context6.next = 9;
            break;
          }
          _context6.next = 8;
          return syncToServer(existing);
        case 8:
          _context6.next = 10;
          break;
        case 9:
          _context6.next = 10;
          return doSubscribe(swReg);
        case 10:
          return _context6.abrupt("return", {
            ok: true
          });
        case 11:
          _context6.prev = 11;
          _t5 = _context6["catch"](5);
          console.error('[push] enable failed', _t5);
          return _context6.abrupt("return", {
            ok: false,
            reason: 'error',
            message: (_t5 === null || _t5 === void 0 ? void 0 : _t5.message) || String(_t5)
          });
        case 12:
        case "end":
          return _context6.stop();
      }
    }, _callee6, null, [[5, 11]]);
  }));
  return _enablePushNotifications.apply(this, arguments);
}
function disablePushNotifications() {
  return _disablePushNotifications.apply(this, arguments);
}
function _disablePushNotifications() {
  _disablePushNotifications = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee7() {
    var swReg, sub, endpoint, _t6;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context7) {
      while (1) switch (_context7.prev = _context7.next) {
        case 0:
          if (pushSupported()) {
            _context7.next = 1;
            break;
          }
          return _context7.abrupt("return", {
            ok: true
          });
        case 1:
          _context7.prev = 1;
          _context7.next = 2;
          return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_4__.ensureServiceWorkerReady)();
        case 2:
          swReg = _context7.sent;
          _context7.next = 3;
          return swReg.pushManager.getSubscription();
        case 3:
          sub = _context7.sent;
          if (!sub) {
            _context7.next = 5;
            break;
          }
          endpoint = sub.endpoint;
          _context7.next = 4;
          return sub.unsubscribe();
        case 4:
          _context7.next = 5;
          return axios__WEBPACK_IMPORTED_MODULE_2__["default"].post('/pushNotification/unsubscribe', {
            endpoint
          }, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
              'X-CSRF-TOKEN': (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
            }
          }).catch(function (e) {
            return console.warn('[push] server unsubscribe failed', e);
          });
        case 5:
          return _context7.abrupt("return", {
            ok: true
          });
        case 6:
          _context7.prev = 6;
          _t6 = _context7["catch"](1);
          console.error('[push] disable failed', _t6);
          return _context7.abrupt("return", {
            ok: false
          });
        case 7:
        case "end":
          return _context7.stop();
      }
    }, _callee7, null, [[1, 6]]);
  }));
  return _disablePushNotifications.apply(this, arguments);
}

/***/ }),

/***/ "./resources/asset/js/components/profilePage/rsvpBtn.js":
/*!**************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/rsvpBtn.js ***!
  \**************************************************************/
/***/ (function() {

// RSVP Buttons
var rsvpButtons = document.querySelectorAll('.rsvp-buttons .btn');
rsvpButtons.forEach(function (button) {
  button.addEventListener('click', function () {
    // Remove active class from all buttons in the same container
    this.parentElement.querySelectorAll('.btn').forEach(function (btn) {
      btn.classList.remove('active');
      if (btn.classList.contains('btn-outline-primary')) {
        btn.classList.replace('btn-primary', 'btn-outline-primary');
      } else if (btn.classList.contains('btn-outline-secondary')) {
        btn.classList.replace('btn-secondary', 'btn-outline-secondary');
      } else if (btn.classList.contains('btn-outline-danger')) {
        btn.classList.replace('btn-danger', 'btn-outline-danger');
      }
    });

    // Add active class to clicked button
    this.classList.add('active');
    if (this.classList.contains('btn-outline-primary')) {
      this.classList.replace('btn-outline-primary', 'btn-primary');
    } else if (this.classList.contains('btn-outline-secondary')) {
      this.classList.replace('btn-outline-secondary', 'btn-secondary');
    } else if (this.classList.contains('btn-outline-danger')) {
      this.classList.replace('btn-outline-danger', 'btn-danger');
    }
  });
});

/***/ }),

/***/ "./resources/asset/js/components/profilePage/showEmojiOnComment.js":
/*!*************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/showEmojiOnComment.js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderTopReactions: function() { return /* binding */ renderTopReactions; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



/**
 * 🧠 Extracts the top 3 reaction types by count.
 * Filters out metadata keys like 'comment_no' and 'total', then sorts descending.
 *
 * @param {Object} counts - Reaction summary object including totals and metadata
 * @returns {Array} - Array of top 3 reactions like [ ['love', 5], ['wow', 3], ['likes', 2] ]
 */
var getTopReactions = function getTopReactions() {
  var counts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  return Object.entries(counts).filter(function (_ref) {
    var _ref2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref, 2),
      label = _ref2[0],
      count = _ref2[1];
    return !['comment_no', 'total', 'totalReactions'].includes(label) && count > 0;
  }) // 🧼 Remove metadata and zero-count reactions
  .sort(function (_ref3, _ref4) {
    var _ref5 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref3, 2),
      a = _ref5[1];
    var _ref6 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref4, 2),
      b = _ref6[1];
    return b - a;
  }) // 🔢 Sort descending by count
  .slice(0, 3);
}; // 🎯 Return top 3 reactions only

/**
 * 🧠 Renders the top 3 reactions into the preview section of a comment.
 * Uses emoji map for visual clarity and teaches contributors how to safely update the DOM.
 *
 * @param {Object} counts - Reaction counts object from the server
 * @param {string|number} commentNo - Unique identifier for the comment block
 */
var renderTopReactions = function renderTopReactions(counts) {
  var top = getTopReactions(counts); // 🧠 Get top 3 reactions

  var html = top.map(function (_ref7) {
    var _emojiMap$label;
    var _ref8 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_ref7, 2),
      label = _ref8[0],
      count = _ref8[1];
    if (typeof window !== 'undefined' && typeof window.__getHeirloomGlassSvg === 'function') {
      var svg = window.__getHeirloomGlassSvg(label, 20);
      return "<span class=\"d-inline-flex align-items-center gap-1\">".concat(svg, "<span>").concat(count, "</span></span>");
    }
    // 🧩 Fallback map
    var emojiMap = {
      likes: '👍',
      like: '👍',
      love: '❤️',
      haha: '😄',
      wow: '😮',
      shock: '😮',
      sad: '😢',
      angry: '😠',
      celebrate: '✨'
    };
    var emoji = (_emojiMap$label = emojiMap[label]) !== null && _emojiMap$label !== void 0 ? _emojiMap$label : ''; // 🧼 Fallback if label missing
    return "".concat(emoji, " ").concat(count); // 🖼️ Render emoji + count
  }).join(' '); // 🧵 Combine into single HTML string
  return html;
};

/***/ }),

/***/ "./resources/asset/js/components/profilePage/videoParser.js":
/*!******************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/videoParser.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   cleanPostMessage: function() { return /* binding */ cleanPostMessage; },
/* harmony export */   extractVideoFromText: function() { return /* binding */ extractVideoFromText; },
/* harmony export */   extractVideosFromText: function() { return /* binding */ extractVideosFromText; },
/* harmony export */   formatVideoDescriptor: function() { return /* binding */ formatVideoDescriptor; },
/* harmony export */   isLikelyShortContext: function() { return /* binding */ isLikelyShortContext; },
/* harmony export */   isPortraitRatioString: function() { return /* binding */ isPortraitRatioString; },
/* harmony export */   normaliseDropboxStreamUrl: function() { return /* binding */ normaliseDropboxStreamUrl; },
/* harmony export */   parseVideoUrl: function() { return /* binding */ parseVideoUrl; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/**
 * Video URL Parser & Embed Helper for Social Posts
 * Supports:
 * - YouTube (Standard, Shortened youtu.be, Shorts, Live, Unlisted, with or without '=' in query)
 * - Vimeo
 * - Cloudflare Stream
 * - TikTok (full video URLs)
 * - Dropbox shared video files
 * - Direct MP4 / WebM video streams
 */




function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function normaliseDropboxStreamUrl(url) {
  if (!url || typeof url !== 'string') return url;
  var trimmed = url.trim();
  var dropboxRegex = /^https?:\/\/(?:www\.|dl\.)?dropbox(?:usercontent)?\.com\/(?:s|scl\/fi)\/[^\s"'<>?#]+\.(?:mp4|webm|mov|ogg|m4v)(?:\?[^\s"'<>#]*)?$/i;
  if (!dropboxRegex.test(trimmed)) return trimmed;
  try {
    var dbUrl = new URL(trimmed);
    dbUrl.searchParams.delete('dl');
    dbUrl.searchParams.set('raw', '1');
    return dbUrl.toString();
  } catch (_) {
    return trimmed;
  }
}
function isLikelyShortContext() {
  var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  var url = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var u = String(url || '').trim();
  if (/\/(?:shorts|reels?)\//i.test(u) || /tiktok\.com\//i.test(u) || /[?&](?:ig|fb)=/i.test(u)) {
    return true;
  }
  var t = String(text || '').trim();
  if (!t) return false;
  return /(?:^|[\s"'(])#(?:shorts?|reels?|tiktok|afv)\b/i.test(t);
}
function isPortraitRatioString(ratio) {
  if (!ratio || typeof ratio !== 'string') return false;
  var m = ratio.trim().match(/^(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)$/);
  if (!m) return false;
  var w = parseFloat(m[1]);
  var h = parseFloat(m[2]);
  return Number.isFinite(w) && Number.isFinite(h) && h > 0 && w / h < 0.88;
}
function parseVideoUrl(url) {
  var contextText = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  if (!url || typeof url !== 'string') return null;
  var trimmed = url.trim();
  if (!trimmed) return null;

  // 0. Already-proxied sovereign stream URL (/resources/videos/external-stream?(url|ig|fb)=... or /member/stream-video?url=...)
  if (trimmed.startsWith('/resources/videos/external-stream?url=') || trimmed.startsWith('/resources/videos/external-stream?ig=') || trimmed.startsWith('/resources/videos/external-stream?fb=') || trimmed.startsWith('/member/stream-video?url=')) {
    if (trimmed.startsWith('/resources/videos/external-stream?ig=')) {
      var igQuery = trimmed.split('?ig=')[1] || '';
      var igCode = decodeURIComponent(igQuery.split('&')[0] || '');
      return {
        type: 'direct',
        provider: 'instagram',
        videoId: igCode ? "ig_".concat(igCode) : null,
        thumbnailUrl: null,
        embedUrl: trimmed,
        originalUrl: igCode ? "https://www.instagram.com/reel/".concat(igCode, "/") : trimmed,
        isShort: true,
        aspect_ratio: '9 / 16',
        trimStart: null,
        trimEnd: null
      };
    }
    if (trimmed.startsWith('/resources/videos/external-stream?fb=')) {
      var fbQuery = trimmed.split('?fb=')[1] || '';
      var fbId = decodeURIComponent(fbQuery.split('&')[0] || '');
      return {
        type: 'direct',
        provider: 'facebook',
        videoId: fbId ? "fb_".concat(fbId) : null,
        thumbnailUrl: null,
        embedUrl: trimmed,
        originalUrl: fbId ? "https://www.facebook.com/reel/".concat(fbId, "/") : trimmed,
        isShort: true,
        aspect_ratio: '9 / 16',
        trimStart: null,
        trimEnd: null
      };
    }
    var rawQuery = trimmed.split('?url=')[1] || '';
    var rawParam = rawQuery.split('&')[0] || '';
    var decoded = rawParam;
    try {
      decoded = decodeURIComponent(rawParam);
    } catch (_) {}
    var poster = decoded.includes('googleusercontent.com') ? decoded.replace(/=.*$/i, '=s1600-no') : null;
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: poster,
      embedUrl: trimmed.startsWith('/resources/videos/external-stream') ? trimmed : decoded || trimmed,
      originalUrl: decoded || trimmed,
      trimStart: null,
      trimEnd: null
    };
  }

  // 1. YouTube Matcher
  // Matches: youtube.com/watch?v=ID, youtube.com/watch?vID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID, youtube.com/live/ID
  var ytRegex = /(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
  var ytMatch = trimmed.match(ytRegex);
  if (ytMatch && ytMatch[1]) {
    var isYtShort = /\/shorts\//i.test(trimmed) || isLikelyShortContext(contextText, trimmed);
    return _objectSpread({
      type: 'youtube',
      videoId: ytMatch[1],
      thumbnailUrl: "https://i.ytimg.com/vi/".concat(ytMatch[1], "/hqdefault.jpg"),
      embedUrl: "https://www.youtube-nocookie.com/embed/".concat(ytMatch[1], "?rel=0&modestbranding=1&autoplay=0"),
      originalUrl: trimmed
    }, isYtShort ? {
      isShort: true,
      aspect_ratio: '9 / 16'
    } : {});
  }

  // 2. Vimeo Matcher
  // Matches: vimeo.com/ID, player.vimeo.com/video/ID
  var vimeoRegex = /(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
  var vimeoMatch = trimmed.match(vimeoRegex);
  if (vimeoMatch && (vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1])) {
    var vimeoId = vimeoMatch[3] || vimeoMatch[2] || vimeoMatch[1];
    return {
      type: 'vimeo',
      videoId: vimeoId,
      thumbnailUrl: null,
      embedUrl: "https://player.vimeo.com/video/".concat(vimeoId, "?dnt=1&title=0&byline=0&portrait=0"),
      originalUrl: trimmed
    };
  }

  // 3. Cloudflare Stream Matcher
  // Matches: iframe.videodelivery.net/UID, videodelivery.net/UID, watch.cloudflarestream.com/UID, customer-*.cloudflarestream.com/UID
  var cfStreamRegex = /(?:https?:\/\/)?(?:iframe\.videodelivery\.net|videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)\/([a-zA-Z0-9]{32})/;
  var cfMatch = trimmed.match(cfStreamRegex);
  if (cfMatch && cfMatch[1]) {
    return {
      type: 'cloudflare',
      videoId: cfMatch[1],
      thumbnailUrl: "https://videodelivery.net/".concat(cfMatch[1], "/thumbnails/thumbnail.jpg"),
      embedUrl: "https://iframe.videodelivery.net/".concat(cfMatch[1]),
      hlsUrl: "https://videodelivery.net/".concat(cfMatch[1], "/manifest/video.m3u8"),
      originalUrl: trimmed
    };
  }

  // 3b. TikTok (tiktok.com/@user/video/ID or tiktok.com/embed/v2/ID) — embeddable player
  var tiktokMatch = trimmed.match(/^https?:\/\/(?:www\.|m\.)?tiktok\.com\/(?:@[\w.-]+\/video\/|embed\/v2\/)(\d{8,25})/i);
  if (tiktokMatch) {
    return {
      type: 'tiktok',
      videoId: tiktokMatch[1],
      thumbnailUrl: null,
      embedUrl: "https://www.tiktok.com/embed/v2/".concat(tiktokMatch[1]),
      originalUrl: trimmed,
      isShort: true,
      aspect_ratio: '9 / 16'
    };
  }

  // 3c. Dropbox shared video files — force raw streaming (dl=0 returns an HTML page, raw=1 returns the file)
  var dropboxRegex = /^https?:\/\/(?:www\.|dl\.)?dropbox(?:usercontent)?\.com\/(?:s|scl\/fi)\/[^\s"'<>?#]+\.(?:mp4|webm|mov|ogg|m4v)(?:\?[^\s"'<>#]*)?$/i;
  if (dropboxRegex.test(trimmed)) {
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: normaliseDropboxStreamUrl(trimmed),
      originalUrl: trimmed,
      trimStart: null,
      trimEnd: null
    };
  }

  // 4. Direct HTML5 Video (.mp4, .webm, .ogg, .mov, .m4v, .m3u8) — supports absolute http(s), sovereign /resources/videos/ or /public/uploads/, and bare filenames
  var directVideoRegex = /^(?:https?:\/\/[^\s"'<>]+|\/(?:resources\/(?:videos|images)|public\/uploads)\/[^\s"'<>]+|[a-zA-Z0-9_-]+\.(?:mp4|webm|ogg|mov|m4v|m3u8))(?:\.(mp4|webm|ogg|mov|m4v|m3u8))?(?:\?[^\s"'<>#]*)?(?:#t=([0-9.]+)(?:,([0-9.]+))?)?$/i;
  if (/\.(mp4|webm|ogg|mov|m4v|m3u8)(?:\?[^\s"'<>#]*)?(?:#t=[0-9.]+(?:,[0-9.]+)?)?$/i.test(trimmed)) {
    var directMatch = trimmed.match(directVideoRegex);
    var isHls = /\.m3u8(?:\?|#|$)/i.test(trimmed);
    var trimStart = directMatch && directMatch[2] !== undefined ? parseFloat(directMatch[2]) : null;
    var trimEnd = directMatch && directMatch[3] !== undefined ? parseFloat(directMatch[3]) : null;
    var resolvedEmbed = trimmed.startsWith('/') || trimmed.startsWith('http') ? trimmed : "/resources/videos/posts/".concat(trimmed);
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: resolvedEmbed,
      hlsUrl: isHls ? resolvedEmbed : null,
      originalUrl: trimmed,
      trimStart: Number.isFinite(trimStart) ? trimStart : null,
      trimEnd: Number.isFinite(trimEnd) ? trimEnd : null
    };
  }

  // 5. Google Photos Video Stream (=m18, =m22, =m37, =dv) or Google Video Stream
  var gpVideoRegex = /^(https?:\/\/(?:lh[3-6]\.googleusercontent\.com|[^/]+\.googlevideo\.com)[^\s"'<>]+(?:=(?:m18|m22|m37|dv)|videoplayback[^\s"'<>]*))$/i;
  var gpMatch = trimmed.match(gpVideoRegex);
  if (gpMatch) {
    var _poster = trimmed.includes('googleusercontent.com') ? trimmed.replace(/=.*$/i, '=s1600-no') : null;
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: _poster,
      embedUrl: trimmed,
      originalUrl: trimmed,
      trimStart: null,
      trimEnd: null
    };
  }

  // 6. Facebook Video / Reel / Watch (in-platform iframe via official Facebook video plugin)
  // Note: /share/r/ and /share/v/ URLs are excluded so they render as Rich Link Previews rather than "Video unavailable"
  var fbVideoRegex = /^https?:\/\/(?:www\.|m\.|web\.)?(?:facebook\.com\/(?:[^\/\s]+\/videos?\/\d+|watch\/?\?v=\d+|reels?\/\d+|video\.php\?v=\d+)|fb\.watch\/[^\s"'<>]+|fb\.com\/(?:watch|reels?|[^\/\s]+\/videos?)\/[^\s"'<>]+)/i;
  if (fbVideoRegex.test(trimmed)) {
    var isFbReel = /(?:facebook\.com|fb\.com)\/reels?\/\d+/i.test(trimmed);
    return _objectSpread({
      type: 'facebook',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: "https://www.facebook.com/plugins/video.php?href=".concat(encodeURIComponent(trimmed), "&show_text=false&autoplay=0"),
      originalUrl: trimmed
    }, isFbReel ? {
      isShort: true,
      aspect_ratio: '9 / 16'
    } : {});
  }

  // 7. Google Drive Video (drive.google.com/file/d/{id}/... or drive.google.com/open?id={id})
  var gdMatch = trimmed.match(/^https?:\/\/drive\.google\.com\/(?:file\/d\/([a-zA-Z0-9_-]{10,})|open\?id=([a-zA-Z0-9_-]{10,}))/i);
  if (gdMatch && (gdMatch[1] || gdMatch[2])) {
    var gdId = gdMatch[1] || gdMatch[2];
    return {
      type: 'googledrive',
      videoId: gdId,
      thumbnailUrl: null,
      embedUrl: "https://drive.google.com/file/d/".concat(gdId, "/preview"),
      originalUrl: trimmed
    };
  }

  // 8. OneDrive Video / Embed (onedrive.live.com/embed, onedrive.live.com/redir, 1drv.ms/v/)
  if (/^https?:\/\/(?:onedrive\.live\.com\/(?:embed|redir|\?)|1drv\.ms\/v\/)[^\s"'<>]+/i.test(trimmed)) {
    var odEmbed = /^https?:\/\/onedrive\.live\.com\/(?:redir|view\.aspx)?\?(.+)$/i.test(trimmed) ? "https://onedrive.live.com/embed?".concat(trimmed.replace(/^https?:\/\/onedrive\.live\.com\/(?:redir|view\.aspx)?\?/i, '')) : trimmed;
    return {
      type: 'onedrive',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: odEmbed,
      originalUrl: trimmed
    };
  }
  return null;
}

/**
 * Normalize a raw video URL or existing video descriptor object into a canonical video descriptor.
 */
function formatVideoDescriptor(videoOrUrl) {
  var contextText = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  if (!videoOrUrl) return null;
  if (typeof videoOrUrl === 'string') {
    var parsed = parseVideoUrl(videoOrUrl, contextText);
    if (parsed) return parsed;
    var resolved = videoOrUrl.startsWith('/') || videoOrUrl.startsWith('http') ? videoOrUrl : "/resources/videos/posts/".concat(videoOrUrl);
    return {
      type: 'direct',
      videoId: null,
      thumbnailUrl: null,
      embedUrl: resolved,
      originalUrl: videoOrUrl,
      trimStart: null,
      trimEnd: null
    };
  }
  if (typeof videoOrUrl === 'object') {
    var candidateUrl = videoOrUrl.originalUrl || videoOrUrl.embedUrl || '';
    var mergedContext = [contextText, videoOrUrl.hook, videoOrUrl.hookText, videoOrUrl.title, videoOrUrl.sourceTitle].filter(Boolean).join(' ');
    var _parsed = parseVideoUrl(candidateUrl, mergedContext);
    var hasProxiedEmbed = typeof videoOrUrl.embedUrl === 'string' && videoOrUrl.embedUrl.startsWith('/resources/videos/');
    var explicitRatio = videoOrUrl.aspect_ratio || videoOrUrl.aspectRatio || _parsed && _parsed.aspect_ratio || undefined;
    var isShort = Boolean(videoOrUrl.isShort || _parsed && _parsed.isShort || isPortraitRatioString(explicitRatio) || isLikelyShortContext(mergedContext, candidateUrl));
    if (_parsed) {
      var _ref, _videoOrUrl$trimStart, _ref2, _videoOrUrl$trimEnd;
      return _objectSpread(_objectSpread({}, _parsed), {}, {
        embedUrl: hasProxiedEmbed ? videoOrUrl.embedUrl : _parsed.embedUrl,
        thumbnailUrl: videoOrUrl.thumbnailUrl || _parsed.thumbnailUrl || null,
        trimStart: (_ref = (_videoOrUrl$trimStart = videoOrUrl.trimStart) !== null && _videoOrUrl$trimStart !== void 0 ? _videoOrUrl$trimStart : _parsed.trimStart) !== null && _ref !== void 0 ? _ref : null,
        trimEnd: (_ref2 = (_videoOrUrl$trimEnd = videoOrUrl.trimEnd) !== null && _videoOrUrl$trimEnd !== void 0 ? _videoOrUrl$trimEnd : _parsed.trimEnd) !== null && _ref2 !== void 0 ? _ref2 : null,
        hook: videoOrUrl.hook || videoOrUrl.hookText || null
      }, isShort ? {
        isShort: true,
        aspect_ratio: explicitRatio || '9 / 16'
      } : explicitRatio ? {
        aspect_ratio: explicitRatio
      } : {});
    }
    if (isShort && !videoOrUrl.isShort) {
      return _objectSpread(_objectSpread({}, videoOrUrl), {}, {
        isShort: true,
        aspect_ratio: explicitRatio || '9 / 16'
      });
    }
    return videoOrUrl;
  }
  return null;
}

/**
 * Extract first video URL from post message text if present (backwards-compatible)
 */
function extractVideoFromText(text) {
  var vids = extractVideosFromText(text, 1);
  return vids.length > 0 ? vids[0] : null;
}

/**
 * Extract up to max video URLs from post message text
 */
function extractVideosFromText(text) {
  var max = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 6;
  if (!text || typeof text !== 'string') return [];
  var urlRegex = /((?:https?:\/\/|\/resources\/(?:videos|images)\/)[^\s"'<>]+)/g;
  var matches = text.match(urlRegex);
  if (!matches) return [];
  var videos = [];
  var seenUrls = new Set();
  var _iterator = _createForOfIteratorHelper(matches),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var url = _step.value;
      if (videos.length >= max) break;
      var cleanUrl = url.replace(/[.,;:!?)]+$/, '');
      if (seenUrls.has(cleanUrl)) continue;
      var parsed = parseVideoUrl(cleanUrl, text);
      if (parsed) {
        videos.push(parsed);
        seenUrls.add(cleanUrl);
      }
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return videos;
}

/**
 * Strip raw video URLs from post text if players are rendered,
 * leaving only user-written caption/comment text.
 */
function cleanPostMessage(text, videoOrVideos) {
  if (!text || typeof text !== 'string') return '';
  var cleaned = text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '');
  if (!videoOrVideos) return cleaned.trim();
  if (Array.isArray(videoOrVideos)) {
    videoOrVideos.forEach(function (v) {
      if (v !== null && v !== void 0 && v.originalUrl) {
        cleaned = cleaned.replace(v.originalUrl, '');
      }
    });
  } else if (videoOrVideos !== null && videoOrVideos !== void 0 && videoOrVideos.originalUrl) {
    cleaned = cleaned.replace(videoOrVideos.originalUrl, '');
  }
  return cleaned.trim();
}

/***/ })

}]);
//# sourceMappingURL=profilePage.js.map?v=47591577