"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["/js/index"],{

/***/ "./resources/asset/js/components/pwa/offlineSync.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/pwa/offlineSync.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_4__);
/**
 * FamilyPlatform Background Sync & Offline Queue Engine
 * - IndexedDB persistent store for offline post submissions & drafts
 * - Background Sync API registration with fallback to window 'online' event
 * - Automatic exponential backoff & retry mechanism
 * - User-facing success feedback upon queue drain
 */






function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t.return || t.return(); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }


var DB_NAME = 'familyplatform_offline_db';
var DB_VERSION = 1;
var STORE_NAME = 'offline_queue';
var OfflineSyncManager = /*#__PURE__*/function () {
  function OfflineSyncManager() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, OfflineSyncManager);
    this.db = null;
    this.isDraining = false;
    this.initDB();
    this.setupListeners();
  }

  /**
   * 1. Initialize IndexedDB
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(OfflineSyncManager, [{
    key: "initDB",
    value: function initDB() {
      var _this = this;
      return new Promise(function (resolve, reject) {
        var request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = function (e) {
          var db = e.target.result;
          if (!db.objectStoreNames.contains(STORE_NAME)) {
            db.createObjectStore(STORE_NAME, {
              keyPath: 'id',
              autoIncrement: true
            });
          }
        };
        request.onsuccess = function (e) {
          _this.db = e.target.result;
          resolve(_this.db);
        };
        request.onerror = function (e) {
          console.warn('[OfflineSync] IndexedDB failed to open:', e);
          reject(e);
        };
      });
    }

    /**
     * 2. Setup Reconnection & SW Message Listeners
     */
  }, {
    key: "setupListeners",
    value: function setupListeners() {
      var _this2 = this;
      // When browser returns online, trigger queue drain
      window.addEventListener('online', function () {
        console.log('[OfflineSync] Online event detected. Draining queue...');
        _this2.drainQueue();
      });

      // Listen for Service Worker background sync notification
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', function (event) {
          var _event$data;
          if (((_event$data = event.data) === null || _event$data === void 0 ? void 0 : _event$data.type) === 'DRAIN_OFFLINE_QUEUE') {
            console.log('[OfflineSync] SW message: DRAIN_OFFLINE_QUEUE received');
            _this2.drainQueue();
          }
        });
      }
    }

    /**
     * 3. Queue an action when offline
     *
     * @param {string} url Endpoint URL
     * @param {Object|FormData} payload Data payload
     * @param {string} actionDescription User-friendly text (e.g. "Family Post")
     */
  }, {
    key: "enqueue",
    value: (function () {
      var _enqueue = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee2(url, payload) {
        var _this3 = this;
        var actionDescription,
          serializedData,
          item,
          _args2 = arguments;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              actionDescription = _args2.length > 2 && _args2[2] !== undefined ? _args2[2] : 'Action';
              if (this.db) {
                _context2.next = 1;
                break;
              }
              _context2.next = 1;
              return this.initDB();
            case 1:
              // Convert FormData to plain object for IndexedDB serialization if needed
              serializedData = payload;
              if (payload instanceof FormData) {
                serializedData = {};
                payload.forEach(function (value, key) {
                  serializedData[key] = value;
                });
              }
              item = {
                url,
                payload: serializedData,
                description: actionDescription,
                timestamp: Date.now(),
                retries: 0
              };
              return _context2.abrupt("return", new Promise(function (resolve, reject) {
                var tx = _this3.db.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);
                var req = store.add(item);
                req.onsuccess = /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
                  var reg, _t;
                  return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
                    while (1) switch (_context.prev = _context.next) {
                      case 0:
                        console.log('[OfflineSync] Item queued successfully:', item);

                        // Register Background Sync if supported
                        if (!('serviceWorker' in navigator && 'SyncManager' in window)) {
                          _context.next = 5;
                          break;
                        }
                        _context.prev = 1;
                        _context.next = 2;
                        return navigator.serviceWorker.ready;
                      case 2:
                        reg = _context.sent;
                        _context.next = 3;
                        return reg.sync.register('sync-family-posts');
                      case 3:
                        _context.next = 5;
                        break;
                      case 4:
                        _context.prev = 4;
                        _t = _context["catch"](1);
                        console.log('[OfflineSync] Sync registration fallback:', _t);
                      case 5:
                        // Show friendly user toast
                        sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                          icon: 'info',
                          title: 'Saved to Offline Queue',
                          text: "You are currently offline. Your ".concat(actionDescription.toLowerCase(), " has been safely saved and will publish automatically when you reconnect."),
                          timer: 4000,
                          timerProgressBar: true,
                          confirmButtonColor: '#2563eb'
                        });
                        resolve(true);
                      case 6:
                      case "end":
                        return _context.stop();
                    }
                  }, _callee, null, [[1, 4]]);
                }));
                req.onerror = function (err) {
                  console.error('[OfflineSync] Error saving to queue:', err);
                  reject(err);
                };
              }));
            case 2:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this);
      }));
      function enqueue(_x, _x2) {
        return _enqueue.apply(this, arguments);
      }
      return enqueue;
    }()
    /**
     * 4. Drain and replay queued requests
     */
    )
  }, {
    key: "drainQueue",
    value: (function () {
      var _drainQueue = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee3() {
        var _this4 = this;
        var _document$querySelect, items, processedCount, csrfToken, _iterator, _step, _loop, _t3, _t4;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              if (!(this.isDraining || !navigator.onLine)) {
                _context4.next = 1;
                break;
              }
              return _context4.abrupt("return");
            case 1:
              this.isDraining = true;
              if (this.db) {
                _context4.next = 2;
                break;
              }
              _context4.next = 2;
              return this.initDB();
            case 2:
              _context4.prev = 2;
              _context4.next = 3;
              return this.getAllItems();
            case 3:
              items = _context4.sent;
              if (!(items.length === 0)) {
                _context4.next = 4;
                break;
              }
              this.isDraining = false;
              return _context4.abrupt("return");
            case 4:
              console.log("[OfflineSync] Processing ".concat(items.length, " queued action(s)..."));
              processedCount = 0;
              csrfToken = ((_document$querySelect = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('content')) || '';
              _iterator = _createForOfIteratorHelper(items);
              _context4.prev = 5;
              _loop = /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _loop() {
                var item, formData, key, controller, timeoutId, response, _t2;
                return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context3) {
                  while (1) switch (_context3.prev = _context3.next) {
                    case 0:
                      item = _step.value;
                      _context3.prev = 1;
                      formData = new FormData();
                      for (key in item.payload) {
                        formData.append(key, item.payload[key]);
                      }
                      if (csrfToken && !item.payload['token']) {
                        formData.append('token', csrfToken);
                      }

                      // AbortController timeout safeguard (David's Gate 3: 8s limit)
                      controller = new AbortController();
                      timeoutId = setTimeout(function () {
                        return controller.abort();
                      }, 8000);
                      _context3.next = 2;
                      return fetch(item.url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'X-CSRF-TOKEN': csrfToken
                        },
                        signal: controller.signal
                      });
                    case 2:
                      response = _context3.sent;
                      clearTimeout(timeoutId);
                      if (!(response.ok || response.status === 200 || response.status === 302)) {
                        _context3.next = 4;
                        break;
                      }
                      _context3.next = 3;
                      return _this4.deleteItem(item.id);
                    case 3:
                      processedCount++;
                      _context3.next = 7;
                      break;
                    case 4:
                      if (!(response.status >= 400 && response.status < 500)) {
                        _context3.next = 6;
                        break;
                      }
                      // Client error (validation / unauthorized) - discard to avoid infinite loop
                      console.warn("[OfflineSync] Server returned ".concat(response.status, " for item ").concat(item.id, ". Discarding."));
                      _context3.next = 5;
                      return _this4.deleteItem(item.id);
                    case 5:
                      _context3.next = 7;
                      break;
                    case 6:
                      _context3.next = 7;
                      return _this4.incrementRetries(item);
                    case 7:
                      _context3.next = 9;
                      break;
                    case 8:
                      _context3.prev = 8;
                      _t2 = _context3["catch"](1);
                      console.warn('[OfflineSync] Network error during drain:', _t2);
                      return _context3.abrupt("return", 1);
                    case 9:
                    case "end":
                      return _context3.stop();
                  }
                }, _loop, null, [[1, 8]]);
              });
              _iterator.s();
            case 6:
              if ((_step = _iterator.n()).done) {
                _context4.next = 9;
                break;
              }
              return _context4.delegateYield(_loop(), "t0", 7);
            case 7:
              if (!_context4.t0) {
                _context4.next = 8;
                break;
              }
              return _context4.abrupt("continue", 9);
            case 8:
              _context4.next = 6;
              break;
            case 9:
              _context4.next = 11;
              break;
            case 10:
              _context4.prev = 10;
              _t3 = _context4["catch"](5);
              _iterator.e(_t3);
            case 11:
              _context4.prev = 11;
              _iterator.f();
              return _context4.finish(11);
            case 12:
              if (processedCount > 0) {
                sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: "Synced ".concat(processedCount, " offline update(s) successfully!"),
                  showConfirmButton: false,
                  timer: 3500,
                  timerProgressBar: true
                });

                // If on profile/feed page, refresh view to show new content
                if (window.location.pathname.includes('profilePage')) {
                  setTimeout(function () {
                    return window.location.reload();
                  }, 1500);
                }
              }
              _context4.next = 14;
              break;
            case 13:
              _context4.prev = 13;
              _t4 = _context4["catch"](2);
              console.error('[OfflineSync] Error during drainQueue:', _t4);
            case 14:
              _context4.prev = 14;
              this.isDraining = false;
              return _context4.finish(14);
            case 15:
            case "end":
              return _context4.stop();
          }
        }, _callee3, this, [[2, 13, 14, 15], [5, 10, 11, 12]]);
      }));
      function drainQueue() {
        return _drainQueue.apply(this, arguments);
      }
      return drainQueue;
    }())
  }, {
    key: "getAllItems",
    value: function getAllItems() {
      var _this5 = this;
      return new Promise(function (resolve) {
        var tx = _this5.db.transaction(STORE_NAME, 'readonly');
        var store = tx.objectStore(STORE_NAME);
        var req = store.getAll();
        req.onsuccess = function () {
          return resolve(req.result || []);
        };
        req.onerror = function () {
          return resolve([]);
        };
      });
    }
  }, {
    key: "deleteItem",
    value: function deleteItem(id) {
      var _this6 = this;
      return new Promise(function (resolve) {
        var tx = _this6.db.transaction(STORE_NAME, 'readwrite');
        var store = tx.objectStore(STORE_NAME);
        var req = store.delete(id);
        req.onsuccess = function () {
          return resolve(true);
        };
        req.onerror = function () {
          return resolve(false);
        };
      });
    }
  }, {
    key: "incrementRetries",
    value: function incrementRetries(item) {
      var _this7 = this;
      return new Promise(function (resolve) {
        var tx = _this7.db.transaction(STORE_NAME, 'readwrite');
        var store = tx.objectStore(STORE_NAME);
        item.retries = (item.retries || 0) + 1;
        if (item.retries > 5) {
          // Discard after 5 failed retries
          store.delete(item.id);
        } else {
          store.put(item);
        }
        tx.oncomplete = function () {
          return resolve(true);
        };
      });
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (OfflineSyncManager);

/***/ }),

/***/ "./resources/asset/js/components/pwa/pushManager.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/pwa/pushManager.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/**
 * FamilyPlatform Push Notification Manager
 * - Manages PushManager subscription lifecycle
 * - Subscribes user using VAPID public key
 * - Encodes keys and syncs to /pushNotification/subscription
 * - Handles permission requests & deep links
 */








var PushManagerClient = /*#__PURE__*/function () {
  function PushManagerClient() {
    var _document$querySelect;
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, PushManagerClient);
    this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    this.vapidPublicKey = ((_document$querySelect = document.querySelector('meta[name="vapid-public-key"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.getAttribute('content')) || '';
  }

  /**
   * Convert URL-safe base64 string to Uint8Array for PushManager
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(PushManagerClient, [{
    key: "subscribe",
    value: (
    /**
     * Request permission and subscribe to Web Push
     */
    function () {
      var _subscribe = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
        var vapidKey,
          key,
          permission,
          reg,
          subscription,
          applicationServerKey,
          _args = arguments,
          _t;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              vapidKey = _args.length > 0 && _args[0] !== undefined ? _args[0] : null;
              if (this.isSupported) {
                _context.next = 1;
                break;
              }
              console.warn('[PushManager] Web Push is not supported on this browser.');
              return _context.abrupt("return", false);
            case 1:
              key = vapidKey || this.vapidPublicKey;
              if (key) {
                _context.next = 2;
                break;
              }
              console.warn('[PushManager] No VAPID public key available.');
              return _context.abrupt("return", false);
            case 2:
              _context.prev = 2;
              _context.next = 3;
              return Notification.requestPermission();
            case 3:
              permission = _context.sent;
              if (!(permission !== 'granted')) {
                _context.next = 4;
                break;
              }
              console.log('[PushManager] Notification permission denied by user.');
              return _context.abrupt("return", false);
            case 4:
              _context.next = 5;
              return navigator.serviceWorker.ready;
            case 5:
              reg = _context.sent;
              _context.next = 6;
              return reg.pushManager.getSubscription();
            case 6:
              subscription = _context.sent;
              if (subscription) {
                _context.next = 8;
                break;
              }
              applicationServerKey = PushManagerClient.urlBase64ToUint8Array(key);
              _context.next = 7;
              return reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey
              });
            case 7:
              subscription = _context.sent;
            case 8:
              _context.next = 9;
              return this.saveSubscription(subscription);
            case 9:
              console.log('[PushManager] Push subscription active and synced with server.');
              return _context.abrupt("return", true);
            case 10:
              _context.prev = 10;
              _t = _context["catch"](2);
              console.error('[PushManager] Error during push subscription:', _t);
              return _context.abrupt("return", false);
            case 11:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[2, 10]]);
      }));
      function subscribe() {
        return _subscribe.apply(this, arguments);
      }
      return subscribe;
    }()
    /**
     * Sync subscription object with PHP backend
     */
    )
  }, {
    key: "saveSubscription",
    value: (function () {
      var _saveSubscription = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee2(subscription) {
        var _document$querySelect2, _subJSON$keys, _subJSON$keys2, subJSON, csrfToken, payload, _t2;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              _context2.prev = 0;
              subJSON = subscription.toJSON();
              csrfToken = ((_document$querySelect2 = document.querySelector('meta[name="csrf-token"]')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.getAttribute('content')) || '';
              payload = {
                endpoint: subJSON.endpoint,
                keys: {
                  p256dh: (_subJSON$keys = subJSON.keys) === null || _subJSON$keys === void 0 ? void 0 : _subJSON$keys.p256dh,
                  auth: (_subJSON$keys2 = subJSON.keys) === null || _subJSON$keys2 === void 0 ? void 0 : _subJSON$keys2.auth
                }
              };
              _context2.next = 1;
              return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post('/pushNotification/subscription', payload, {
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken
                }
              });
            case 1:
              _context2.next = 3;
              break;
            case 2:
              _context2.prev = 2;
              _t2 = _context2["catch"](0);
              console.warn('[PushManager] Failed to sync subscription to backend:', _t2);
            case 3:
            case "end":
              return _context2.stop();
          }
        }, _callee2, null, [[0, 2]]);
      }));
      function saveSubscription(_x) {
        return _saveSubscription.apply(this, arguments);
      }
      return saveSubscription;
    }()
    /**
     * Unsubscribe from Web Push
     */
    )
  }, {
    key: "unsubscribe",
    value: (function () {
      var _unsubscribe = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee3() {
        var reg, subscription, _t3;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              if (this.isSupported) {
                _context3.next = 1;
                break;
              }
              return _context3.abrupt("return", false);
            case 1:
              _context3.prev = 1;
              _context3.next = 2;
              return navigator.serviceWorker.ready;
            case 2:
              reg = _context3.sent;
              _context3.next = 3;
              return reg.pushManager.getSubscription();
            case 3:
              subscription = _context3.sent;
              if (!subscription) {
                _context3.next = 5;
                break;
              }
              _context3.next = 4;
              return subscription.unsubscribe();
            case 4:
              console.log('[PushManager] Unsubscribed from Web Push successfully.');
              return _context3.abrupt("return", true);
            case 5:
              return _context3.abrupt("return", false);
            case 6:
              _context3.prev = 6;
              _t3 = _context3["catch"](1);
              console.error('[PushManager] Error unsubscribing:', _t3);
              return _context3.abrupt("return", false);
            case 7:
            case "end":
              return _context3.stop();
          }
        }, _callee3, this, [[1, 6]]);
      }));
      function unsubscribe() {
        return _unsubscribe.apply(this, arguments);
      }
      return unsubscribe;
    }())
  }], [{
    key: "urlBase64ToUint8Array",
    value: function urlBase64ToUint8Array(base64String) {
      var padding = '='.repeat((4 - base64String.length % 4) % 4);
      var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
      var rawData = window.atob(base64);
      var outputArray = new Uint8Array(rawData.length);
      for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
      }
      return outputArray;
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (PushManagerClient);

/***/ }),

/***/ "./resources/asset/js/components/pwa/pwaManager.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/pwa/pwaManager.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _offlineSync__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./offlineSync */ "./resources/asset/js/components/pwa/offlineSync.js");
/* harmony import */ var _pushManager__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./pushManager */ "./resources/asset/js/components/pwa/pushManager.js");
/**
 * FamilyPlatform PWA Manager
 * World-Class Progressive Web App Engine:
 * - In-app installation banner (Android/Chrome/Edge)
 * - iOS Safari animated 'Add to Home Screen' visual tutorial
 * - Service worker lifecycle & seamless update notification
 * - Real-time Online/Offline connection HUD
 * - Native Badging API integration
 */









var PWAManager = /*#__PURE__*/function () {
  function PWAManager() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, PWAManager);
    this.deferredPrompt = null;
    this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    this.offlineSync = new _offlineSync__WEBPACK_IMPORTED_MODULE_4__["default"]();
    this.push = new _pushManager__WEBPACK_IMPORTED_MODULE_5__["default"]();
    window.offlineSync = this.offlineSync;
    window.pushManager = this.push;
    this.init();
  }
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(PWAManager, [{
    key: "init",
    value: function init() {
      this.registerServiceWorker();
      this.setupInstallPrompt();
      this.setupIOSPrompt();
      this.setupNetworkHUD();
      this.setupLogoutCachePurge();
    }

    /**
     * Setup Logout Listener to purge dynamic user cache
     */
  }, {
    key: "setupLogoutCachePurge",
    value: function setupLogoutCachePurge() {
      document.addEventListener('click', function (e) {
        var _navigator$serviceWor;
        var link = e.target.closest('a[href*="signout"], a[href*="logout"]');
        if (link && (_navigator$serviceWor = navigator.serviceWorker) !== null && _navigator$serviceWor !== void 0 && _navigator$serviceWor.controller) {
          navigator.serviceWorker.controller.postMessage({
            action: 'clearUserCache'
          });
        }
      });
    }

    /**
     * 1. Register Service Worker & Handle Updates
     */
  }, {
    key: "registerServiceWorker",
    value: function registerServiceWorker() {
      var _this = this;
      if (!('serviceWorker' in navigator)) return;
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('/service-worker.js', {
          scope: '/'
        }).then(function (registration) {
          // Check for SW updates
          registration.addEventListener('updatefound', function () {
            var newWorker = registration.installing;
            if (!newWorker) return;
            newWorker.addEventListener('statechange', function () {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                _this.showUpdateToast(newWorker);
              }
            });
          });
        }).catch(function (err) {
          console.warn('[PWA] Service Worker registration failed:', err);
        });

        // Handle controllerchange to auto-reload upon skipWaiting
        var refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', function () {
          if (!refreshing) {
            refreshing = true;
            window.location.reload();
          }
        });
      });
    }

    /**
     * Show a non-intrusive Update Notification Toast
     */
  }, {
    key: "showUpdateToast",
    value: function showUpdateToast(newWorker) {
      var _document$getElementB, _document$getElementB2;
      if (document.getElementById('pwa-update-toast')) return;
      var toast = document.createElement('div');
      toast.id = 'pwa-update-toast';
      toast.innerHTML = "\n      <div class=\"pwa-toast-card\">\n        <div class=\"pwa-toast-icon\">\u26A1</div>\n        <div class=\"pwa-toast-content\">\n          <h6>Update Available</h6>\n          <p>A fresh version of FamilyPlatform is ready.</p>\n        </div>\n        <button id=\"pwa-update-btn\" class=\"pwa-btn pwa-btn-primary\">Update</button>\n        <button id=\"pwa-update-close\" class=\"pwa-btn-close\" aria-label=\"Close\">&times;</button>\n      </div>\n    ";
      document.body.appendChild(toast);
      (_document$getElementB = document.getElementById('pwa-update-btn')) === null || _document$getElementB === void 0 ? void 0 : _document$getElementB.addEventListener('click', function () {
        newWorker.postMessage({
          action: 'skipWaiting'
        });
        toast.remove();
      });
      (_document$getElementB2 = document.getElementById('pwa-update-close')) === null || _document$getElementB2 === void 0 ? void 0 : _document$getElementB2.addEventListener('click', function () {
        toast.remove();
      });
    }

    /**
     * 2. Android / Chromium Custom Install Banner
     */
  }, {
    key: "setupInstallPrompt",
    value: function setupInstallPrompt() {
      var _this2 = this;
      window.addEventListener('beforeinstallprompt', function (e) {
        // Prevent standard browser mini-infobar
        e.preventDefault();
        _this2.deferredPrompt = e;

        // Don't show if user dismissed within last 7 days or already installed
        var dismissed = localStorage.getItem('pwa_install_dismissed');
        if (dismissed && Date.now() - parseInt(dismissed, 10) < 7 * 86400000) {
          return;
        }
        _this2.showInstallBanner();
      });
      window.addEventListener('appinstalled', function () {
        var _document$getElementB3;
        _this2.deferredPrompt = null;
        (_document$getElementB3 = document.getElementById('pwa-install-banner')) === null || _document$getElementB3 === void 0 ? void 0 : _document$getElementB3.remove();
        console.log('[PWA] FamilyPlatform successfully installed!');
      });
    }
  }, {
    key: "showInstallBanner",
    value: function showInstallBanner() {
      var _document$getElementB4,
        _this3 = this,
        _document$getElementB5;
      if (document.getElementById('pwa-install-banner') || this.isStandalone) return;
      var banner = document.createElement('div');
      banner.id = 'pwa-install-banner';
      banner.innerHTML = "\n      <div class=\"pwa-banner-card\">\n        <div class=\"pwa-banner-header\">\n          <img src=\"/public/img/favicon/android-chrome-192x192.png\" alt=\"FamilyPlatform\" class=\"pwa-banner-logo\" onerror=\"this.src='/resources/images/avatarM.png'\">\n          <div class=\"pwa-banner-text\">\n            <h6>Install FamilyPlatform</h6>\n            <p>Get instant alerts, fast family updates & offline access.</p>\n          </div>\n          <button id=\"pwa-banner-close\" class=\"pwa-btn-close\" aria-label=\"Dismiss\">&times;</button>\n        </div>\n        <div class=\"pwa-banner-actions\">\n          <button id=\"pwa-install-btn\" class=\"pwa-btn pwa-btn-primary\">\n            <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"me-1\"><path d=\"M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4\"></path><polyline points=\"7 10 12 15 17 10\"></polyline><line x1=\"12\" y1=\"15\" x2=\"12\" y2=\"3\"></line></svg>\n            Add to Home Screen\n          </button>\n        </div>\n      </div>\n    ";
      document.body.appendChild(banner);

      // Trigger slide-up animation
      setTimeout(function () {
        return banner.classList.add('show');
      }, 100);
      (_document$getElementB4 = document.getElementById('pwa-install-btn')) === null || _document$getElementB4 === void 0 ? void 0 : _document$getElementB4.addEventListener('click', /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
        var _yield$_this3$deferre, outcome;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              if (_this3.deferredPrompt) {
                _context.next = 1;
                break;
              }
              return _context.abrupt("return");
            case 1:
              banner.remove();
              _this3.deferredPrompt.prompt();
              _context.next = 2;
              return _this3.deferredPrompt.userChoice;
            case 2:
              _yield$_this3$deferre = _context.sent;
              outcome = _yield$_this3$deferre.outcome;
              console.log("[PWA] Install prompt outcome: ".concat(outcome));
              _this3.deferredPrompt = null;
            case 3:
            case "end":
              return _context.stop();
          }
        }, _callee);
      })));
      (_document$getElementB5 = document.getElementById('pwa-banner-close')) === null || _document$getElementB5 === void 0 ? void 0 : _document$getElementB5.addEventListener('click', function () {
        localStorage.setItem('pwa_install_dismissed', Date.now().toString());
        banner.classList.remove('show');
        setTimeout(function () {
          return banner.remove();
        }, 400);
      });
    }

    /**
     * 3. iOS Safari "Add to Home Screen" Visual Overlay Tutorial
     */
  }, {
    key: "setupIOSPrompt",
    value: function setupIOSPrompt() {
      var _this4 = this;
      if (!this.isIOS || this.isStandalone) return;
      var dismissed = localStorage.getItem('pwa_ios_dismissed');
      if (dismissed && Date.now() - parseInt(dismissed, 10) < 7 * 86400000) {
        return;
      }

      // Delay prompt to not overwhelm user on initial landing
      setTimeout(function () {
        _this4.showIOSOverlay();
      }, 4000);
    }
  }, {
    key: "showIOSOverlay",
    value: function showIOSOverlay() {
      var _document$getElementB6;
      if (document.getElementById('pwa-ios-overlay') || this.isStandalone) return;
      var overlay = document.createElement('div');
      overlay.id = 'pwa-ios-overlay';
      overlay.innerHTML = "\n      <div class=\"pwa-ios-card\">\n        <button id=\"pwa-ios-close\" class=\"pwa-btn-close\" aria-label=\"Close\">&times;</button>\n        <div class=\"pwa-ios-header\">\n          <img src=\"/public/img/favicon/apple-touch-icon.png\" alt=\"FamilyPlatform\" class=\"pwa-ios-logo\" onerror=\"this.src='/resources/images/avatarM.png'\">\n          <div>\n            <h6>Install FamilyPlatform on iOS</h6>\n            <p>Install this app on your iPhone for the full native experience.</p>\n          </div>\n        </div>\n        <div class=\"pwa-ios-steps\">\n          <div class=\"pwa-step\">\n            <span class=\"step-num\">1</span>\n            <span>Tap the <strong>Share</strong> button <svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#007aff\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"vertical-align: middle;\"><path d=\"M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8\"/><polyline points=\"16 6 12 2 8 6\"/><line x1=\"12\" y1=\"2\" x2=\"12\" y2=\"15\"/></svg> at the bottom of Safari.</span>\n          </div>\n          <div class=\"pwa-step\">\n            <span class=\"step-num\">2</span>\n            <span>Scroll down and tap <strong>Add to Home Screen</strong> <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"vertical-align: middle;\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><line x1=\"12\" y1=\"8\" x2=\"12\" y2=\"16\"/><line x1=\"8\" y1=\"12\" x2=\"16\" y2=\"12\"/></svg>.</span>\n          </div>\n        </div>\n        <div class=\"pwa-ios-arrow-pointer\">\n          <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#007aff\" stroke-width=\"2.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><line x1=\"12\" y1=\"5\" x2=\"12\" y2=\"19\"></line><polyline points=\"19 12 12 19 5 12\"></polyline></svg>\n        </div>\n      </div>\n    ";
      document.body.appendChild(overlay);
      setTimeout(function () {
        return overlay.classList.add('show');
      }, 100);
      (_document$getElementB6 = document.getElementById('pwa-ios-close')) === null || _document$getElementB6 === void 0 ? void 0 : _document$getElementB6.addEventListener('click', function () {
        localStorage.setItem('pwa_ios_dismissed', Date.now().toString());
        overlay.classList.remove('show');
        setTimeout(function () {
          return overlay.remove();
        }, 400);
      });
    }

    /**
     * 4. Network Online/Offline HUD Banner
     */
  }, {
    key: "setupNetworkHUD",
    value: function setupNetworkHUD() {
      var updateNetworkStatus = function updateNetworkStatus(online) {
        var hud = document.getElementById('pwa-network-hud');
        if (!hud) {
          hud = document.createElement('div');
          hud.id = 'pwa-network-hud';
          document.body.appendChild(hud);
        }
        if (online) {
          hud.className = 'pwa-hud online show';
          hud.innerHTML = "<span>\uD83D\uDFE2 Connected \u2014 Real-time family updates active</span>";
          setTimeout(function () {
            hud.classList.remove('show');
          }, 3000);
        } else {
          hud.className = 'pwa-hud offline show';
          hud.innerHTML = "<span>\u26A0\uFE0F Offline Mode \u2014 Changes are queued and will sync automatically</span>";
        }
      };
      window.addEventListener('online', function () {
        return updateNetworkStatus(true);
      });
      window.addEventListener('offline', function () {
        return updateNetworkStatus(false);
      });

      // Initial check
      if (!navigator.onLine) {
        updateNetworkStatus(false);
      }
    }

    /**
     * 5. App Badging API
     */
  }], [{
    key: "setBadge",
    value: function setBadge() {
      var count = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
      if ('setAppBadge' in navigator) {
        if (count > 0) {
          navigator.setAppBadge(count).catch(function () {});
        } else {
          navigator.clearAppBadge().catch(function () {});
        }
      }
    }
  }]);
}();
/* harmony default export */ __webpack_exports__["default"] = (PWAManager);

/***/ }),

/***/ "./resources/asset/js/index.js":
/*!*************************************!*\
  !*** ./resources/asset/js/index.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var alpinejs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! alpinejs */ "./node_modules/alpinejs/dist/module.esm.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_pwa_pwaManager__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/pwa/pwaManager */ "./resources/asset/js/components/pwa/pwaManager.js");







window.Swal = (sweetalert2__WEBPACK_IMPORTED_MODULE_2___default());
window.Alpine = alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"];
window.pwaManager = new _components_pwa_pwaManager__WEBPACK_IMPORTED_MODULE_3__["default"]();
var routePromise = Promise.resolve();

// The server occasionally finds the session's CSRF token missing (e.g. session
// data expired/evicted between page load and this request) and responds 401
// with "We are not familiar with the nature of your activities.". That same
// response always carries a fresh Set-Cookie: XSRF-TOKEN, which the browser
// applies immediately — so a single automatic retry of the exact same request
// picks up the new cookie and succeeds transparently instead of surfacing a
// dead-end error the user has to work around by reloading the page.
axios__WEBPACK_IMPORTED_MODULE_4__["default"].interceptors.response.use(function (response) {
  return response;
}, function (error) {
  var _error$response, _error$response$data, _error$response2;
  var config = error.config;
  var message = (_error$response = error.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message;
  var isStaleCsrfToken = ((_error$response2 = error.response) === null || _error$response2 === void 0 ? void 0 : _error$response2.status) === 401 && typeof message === 'string' && message.includes('not familiar with the nature of your activities') && config && !config._csrfRetried;
  if (isStaleCsrfToken) {
    config._csrfRetried = true;
    if (config.headers) {
      delete config.headers['X-XSRF-TOKEN'];
      delete config.headers['x-xsrf-token'];
      delete config.headers['X-CSRF-TOKEN'];
      delete config.headers['x-csrf-token'];
    }
    return (0,axios__WEBPACK_IMPORTED_MODULE_4__["default"])(config);
  }
  return Promise.reject(error);
});

/**
 * Tests if the current URL matches the given route.
 *
 * @param {string} url A URL route to test.
 *
 * @returns {boolean} True if the URL matches, otherwise false.
 */
var checkURL = function checkURL(url) {
  return !!window.location.pathname.match(new RegExp("^/".concat(url, "(?:/[^/]+)*$")));
};
try {
  var $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  // Add a click event on each of them
  $navbarBurgers.forEach(function (el) {
    el.addEventListener('click', function () {
      // Get the target from the "data-target" attribute
      var target = el.dataset.target;
      var $target = document.getElementById(target);

      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      el.classList.toggle('is-active');
      $target.classList.toggle('is-active');
    });
  });
} catch (error) {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
}

// Get all "navbar-burger" elements

if (window.location.pathname === '/register') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.registerNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.registerNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | register */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("register")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/ */ "./resources/asset/js/components/register/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMemberNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMemberNav').style.display = 'none';
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMembersNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.allMembersNav').style.display = 'none';
  routePromise = Promise.all(/*! import() | all_members */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("all_members")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/allMembers/ */ "./resources/asset/js/components/allMembers/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | login */ "login").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/login */ "./resources/asset/js/components/acctMgt/login.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/lasu') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | adminLogin */ "adminLogin").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/adminLogin */ "./resources/asset/js/components/acctMgt/adminLogin.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/forgot') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | forgotPwd */ "forgotPwd").then(__webpack_require__.bind(__webpack_require__, /*! ./components/forgotPwd/ */ "./resources/asset/js/components/forgotPwd/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/code') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | code */ "code").then(__webpack_require__.bind(__webpack_require__, /*! ./components/acctMgt/code */ "./resources/asset/js/components/acctMgt/code.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname.toLowerCase().startsWith('/member/profilepage') || window.location.pathname.toLowerCase().startsWith('/profilepage') || checkURL('member/ProfilePage') || checkURL('member/profilePage') || checkURL('profilePage')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.profilePageNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.profilePageNav').style.display = 'none'; // navbar mgt

  routePromise = Promise.all(/*! import() | profilePage */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("profilePage")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/ */ "./resources/asset/js/components/profilePage/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/login/changePW' || window.location.pathname === '/changePW') {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login').style.display = 'none'; // navbar mgt
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp').style.display = 'none'; // navbar mgt
  // qSel('#loader').style.display ="none" // loader
  routePromise = __webpack_require__.e(/*! import() | changePW */ "changePW").then(__webpack_require__.bind(__webpack_require__, /*! ./components/changePW/ */ "./resources/asset/js/components/changePW/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/profilepage/img') {
  // qSel('.login').style.display ="none" // navbar mgt
  routePromise = Promise.all(/*! import() | img */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("img")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/profilePage/imgViewer */ "./resources/asset/js/components/profilePage/imgViewer.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/createFamilyCode') {
  routePromise = __webpack_require__.e(/*! import() | familyCode */ "familyCode").then(__webpack_require__.bind(__webpack_require__, /*! ./components/register/familyCode */ "./resources/asset/js/components/register/familyCode.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/register/nextStep') {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.login').style.display = 'none'; // navbar mgt
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.signUp').style.display = 'none'; // navbar mgt
} else if (checkURL('accountSetting')) {
  routePromise = Promise.all(/*! import() | accountSetting */[__webpack_require__.e("/js/vendor"), __webpack_require__.e("accountSetting")]).then(__webpack_require__.bind(__webpack_require__, /*! ./components/accountSetting */ "./resources/asset/js/components/accountSetting.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (checkURL('organogram') || window.location.pathname.startsWith('/organogram')) {
  if ((0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav')) (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav').style.display = 'none'; // navbar mgt

  routePromise = __webpack_require__.e(/*! import() | organogram */ "organogram").then(__webpack_require__.bind(__webpack_require__, /*! ./components/familyTree/index.js */ "./resources/asset/js/components/familyTree/index.js")).then(function (module) {
    return module.default;
  }).catch(function (err) {
    return (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.showError)(err);
  });
} else if (window.location.pathname === '/allMembers/getProfile') {
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.familyTreeNav').style.display = 'none'; // navbar mgt
  (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_1__.qSel)('.notification_count').style.display = 'none'; // navbar mgt

  // import (
  // import { setCookie } from '../../../node_modules/y/Cookie';
  /* webpackChunkName: 'getProfile' */
  //     /* webpackPrefetch: true */
  //     './components/familyTree/index.js'
  // )
  // .then((module) => module.default)
  //     .catch((err) => showError(err))
}
routePromise.finally(function () {
  alpinejs__WEBPACK_IMPORTED_MODULE_0__["default"].start();
});

/***/ }),

/***/ "./resources/asset/scss/main.scss":
/*!****************************************!*\
  !*** ./resources/asset/scss/main.scss ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/startup prefetch */
/******/ !function() {
/******/ 	__webpack_require__.O(0, ["/js/index"], function() {
/******/ 		["/js/vendor","register","all_members","login","adminLogin","forgotPwd","code","profilePage","changePW","img","familyCode","accountSetting","organogram"].map(__webpack_require__.E);
/******/ 	}, 5);
/******/ }();
/******/ 
/******/ var __webpack_exec__ = function(moduleId) { return __webpack_require__(__webpack_require__.s = moduleId); }
/******/ __webpack_require__.O(0, ["css/main","/js/vendor"], function() { return __webpack_exec__("./resources/asset/js/index.js"), __webpack_exec__("./resources/asset/scss/main.scss"); });
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=index.js.map