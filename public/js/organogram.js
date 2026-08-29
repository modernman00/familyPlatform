"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["organogram"],{

/***/ "./resources/asset/js/components/familyTree/events.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/familyTree/events.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "centerOnElement": function() { return /* binding */ centerOnElement; },
/* harmony export */   "fitToScreen": function() { return /* binding */ fitToScreen; },
/* harmony export */   "initTree": function() { return /* binding */ initTree; },
/* harmony export */   "setPosition": function() { return /* binding */ setPosition; },
/* harmony export */   "updateTransform": function() { return /* binding */ updateTransform; }
/* harmony export */ });
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");

var zoomInBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('zoomIn');
var zoomOutBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('zoomOut');
var resetZoomBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('resetZoom');
var fitScreenBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('fitScreenBtn');
var recenterRootBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('recenterRootBtn');
var familyTree = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('familyTree');
var treeWrapper = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('treeWrapper');

// Initial zoom scale & position
var scale = 1;
var position = {
  x: 0,
  y: 0
};
var startPosition = {
  x: 0,
  y: 0
};
var isDragging = false;

// Applies zoom and pan transformations
function updateTransform() {
  if (!familyTree) return;
  familyTree.style.transform = "translate(".concat(position.x, "px, ").concat(position.y, "px) scale(").concat(scale, ")");
}
function setPosition(x, y) {
  var newScale = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  position.x = x;
  position.y = y;
  if (newScale !== null) scale = newScale;
  updateTransform();
}

// Center the viewport on a specific node element
function centerOnElement(element) {
  if (!element || !treeWrapper || !familyTree) return;
  var wrapperRect = treeWrapper.getBoundingClientRect();
  var elemRect = element.getBoundingClientRect();

  // Reset scale moderately to 1 for focus
  scale = 1;
  var currentTransform = familyTree.getBoundingClientRect();
  var offsetX = wrapperRect.width / 2 - (elemRect.left - currentTransform.left + elemRect.width / 2);
  var offsetY = wrapperRect.height / 3 - (elemRect.top - currentTransform.top + elemRect.height / 2);
  position.x = offsetX;
  position.y = offsetY;
  updateTransform();

  // Add highlight pulse
  element.classList.add('highlighted');
  setTimeout(function () {
    return element.classList.remove('highlighted');
  }, 3000);
}

// Fit tree to screen
function fitToScreen() {
  if (!familyTree || !treeWrapper) return;
  var wrapperRect = treeWrapper.getBoundingClientRect();
  var treeRect = familyTree.scrollWidth || 1200;
  scale = Math.min(Math.max(0.4, (wrapperRect.width - 80) / treeRect), 1.2);
  position = {
    x: 40,
    y: 30
  };
  updateTransform();
}

// Event Listeners for Interaction
var addEventListeners = function addEventListeners() {
  zoomInBtn === null || zoomInBtn === void 0 ? void 0 : zoomInBtn.addEventListener('click', function () {
    scale = Math.min(3.0, scale + 0.15);
    updateTransform();
  });
  zoomOutBtn === null || zoomOutBtn === void 0 ? void 0 : zoomOutBtn.addEventListener('click', function () {
    scale = Math.max(0.3, scale - 0.15);
    updateTransform();
  });
  resetZoomBtn === null || resetZoomBtn === void 0 ? void 0 : resetZoomBtn.addEventListener('click', function () {
    scale = 1;
    position = {
      x: 0,
      y: 0
    };
    updateTransform();
  });
  fitScreenBtn === null || fitScreenBtn === void 0 ? void 0 : fitScreenBtn.addEventListener('click', fitToScreen);
  recenterRootBtn === null || recenterRootBtn === void 0 ? void 0 : recenterRootBtn.addEventListener('click', function () {
    var rootNode = document.querySelector('.tree-node[data-role="Me"]') || document.querySelector('.tree-node');
    if (rootNode) centerOnElement(rootNode);
  });

  // Mouse wheel zoom
  treeWrapper === null || treeWrapper === void 0 ? void 0 : treeWrapper.addEventListener('wheel', function (e) {
    e.preventDefault();
    var delta = -e.deltaY * 0.0015;
    scale = Math.min(Math.max(0.3, scale + delta), 3.0);
    updateTransform();
  }, {
    passive: false
  });

  // Mouse Dragging
  treeWrapper === null || treeWrapper === void 0 ? void 0 : treeWrapper.addEventListener('mousedown', function (e) {
    // Only drag if left click and not interacting with a button
    if (e.button !== 0 || e.target.closest('button') || e.target.closest('input')) return;
    isDragging = true;
    startPosition = {
      x: e.clientX - position.x,
      y: e.clientY - position.y
    };
    treeWrapper.classList.add('grabbing');
  });
  document.addEventListener('mousemove', function (e) {
    if (!isDragging) return;
    position.x = e.clientX - startPosition.x;
    position.y = e.clientY - startPosition.y;
    updateTransform();
  });
  document.addEventListener('mouseup', function () {
    if (isDragging) {
      isDragging = false;
      treeWrapper === null || treeWrapper === void 0 ? void 0 : treeWrapper.classList.remove('grabbing');
    }
  });

  // Touch Events for Mobile
  treeWrapper === null || treeWrapper === void 0 ? void 0 : treeWrapper.addEventListener('touchstart', function (e) {
    if (e.touches.length === 1) {
      isDragging = true;
      startPosition = {
        x: e.touches[0].clientX - position.x,
        y: e.touches[0].clientY - position.y
      };
    }
  }, {
    passive: true
  });
  document.addEventListener('touchmove', function (e) {
    if (!isDragging || e.touches.length !== 1) return;
    position.x = e.touches[0].clientX - startPosition.x;
    position.y = e.touches[0].clientY - startPosition.y;
    updateTransform();
  }, {
    passive: true
  });
  document.addEventListener('touchend', function () {
    isDragging = false;
  });
};
var initTree = function initTree() {
  updateTransform();
  addEventListeners();
};

/***/ }),

/***/ "./resources/asset/js/components/familyTree/index.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/familyTree/index.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./events */ "./resources/asset/js/components/familyTree/events.js");
/* harmony import */ var _showModal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./showModal */ "./resources/asset/js/components/familyTree/showModal.js");




// Initialize zoom & pan engine
(0,_events__WEBPACK_IMPORTED_MODULE_1__.initTree)();
var memberSearchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('memberSearchInput');
var searchDropdown = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('searchDropdown');

// 1. Delegated Click Listener for Any Tree Node
document.addEventListener('click', function (e) {
  var _node$querySelector, _node$querySelector$t, _node$querySelector2, _node$querySelector2$, _node$querySelector3;
  var node = e.target.closest('.tree-node');
  if (!node) return;
  e.preventDefault();
  e.stopPropagation();
  var nodeFn = function nodeFn(nodeClass) {
    var _first$dataset;
    var first = node.querySelector(nodeClass);
    return (first === null || first === void 0 ? void 0 : (_first$dataset = first.dataset) === null || _first$dataset === void 0 ? void 0 : _first$dataset.id) || null;
  };
  var name = node.dataset.name || ((_node$querySelector = node.querySelector('.node-name')) === null || _node$querySelector === void 0 ? void 0 : (_node$querySelector$t = _node$querySelector.textContent) === null || _node$querySelector$t === void 0 ? void 0 : _node$querySelector$t.trim()) || nodeFn('.node-fullName') || 'Family Member';
  var role = node.dataset.role || ((_node$querySelector2 = node.querySelector('.node-title')) === null || _node$querySelector2 === void 0 ? void 0 : (_node$querySelector2$ = _node$querySelector2.textContent) === null || _node$querySelector2$ === void 0 ? void 0 : _node$querySelector2$.trim()) || nodeFn('.node-relation-hidden') || 'Relative';
  var img = node.dataset.img || ((_node$querySelector3 = node.querySelector('.profile-image')) === null || _node$querySelector3 === void 0 ? void 0 : _node$querySelector3.src) || nodeFn('.node-img') || '/resources/images/profile/avatarM.png';
  var email = node.dataset.email || nodeFn('.node-email') || '';
  var maritalStatus = node.dataset.maritalStatus || nodeFn('.node-maritalStatus') || '';
  var spouseName = node.dataset.spouseName || nodeFn('.node-spouseName') || '';
  var occupation = node.dataset.occupation || nodeFn('.node-occupation') || '';
  var country = node.dataset.country || nodeFn('.node-country') || '';
  var bio = node.dataset.bio || nodeFn('.node-bio') || '';
  var personId = node.dataset.personid || nodeFn('.node-id') || null;
  var nodeData = {
    fullName: name,
    personId: personId && personId !== '' ? personId : null,
    email: email,
    relation: role,
    img: img,
    maritalStatus: maritalStatus,
    spouseName: spouseName,
    occupation: occupation,
    country: country,
    bio: bio,
    isDeceased: node.dataset.deceased === '1' || node.classList.contains('deceased'),
    isRegistered: node.dataset.registered === 'true',
    familyCode: window.__FAMILY_CODE__ || node.dataset.familycode || ''
  };
  (0,_showModal__WEBPACK_IMPORTED_MODULE_2__.showPersonDetails)(nodeData);
});

// 2. Search Box & Instant Spotlight (Supports Balkan FamilyTree and DOM nodes)
memberSearchInput === null || memberSearchInput === void 0 ? void 0 : memberSearchInput.addEventListener('input', function (e) {
  var _window$graphData;
  var query = e.target.value.trim().toLowerCase();
  if (!searchDropdown) return;
  if (query.length === 0) {
    searchDropdown.style.display = 'none';
    searchDropdown.innerHTML = '';
    return;
  }
  var matches = [];

  // 1. Search Balkan FamilyTree node data
  if (Array.isArray(window.familyTreeNodes) && window.familyTreeNodes.length > 0) {
    matches = window.familyTreeNodes.filter(function (n) {
      var name = (n.name || '').toLowerCase();
      var title = (n.title || '').toLowerCase();
      return name.includes(query) || title.includes(query);
    }).map(function (n) {
      return {
        id: n.id,
        name: n.name || 'Relative',
        role: n.title || 'Family Member',
        img: n.img || '/resources/images/profile/avatarM.png',
        nodeType: 'balkan'
      };
    });
  } else if ((_window$graphData = window.graphData) !== null && _window$graphData !== void 0 && _window$graphData.nodes && Array.isArray(window.graphData.nodes)) {
    matches = window.graphData.nodes.filter(function (n) {
      var name = (n.full_name || "".concat(n.first_name || '', " ").concat(n.last_name || '')).toLowerCase();
      var bio = (n.bio || '').toLowerCase();
      return name.includes(query) || bio.includes(query);
    }).map(function (n) {
      return {
        id: n.id,
        name: n.full_name || "".concat(n.first_name || '', " ").concat(n.last_name || '').trim() || 'Relative',
        role: n.bio || 'Family Member',
        img: n.avatar_url || '/resources/images/profile/avatarM.png',
        nodeType: 'balkan'
      };
    });
  }

  // 2. Fallback to DOM elements
  if (matches.length === 0) {
    var allNodes = Array.from(document.querySelectorAll('.tree-node, [data-n-id]'));
    matches = allNodes.filter(function (n) {
      var text = n.textContent.toLowerCase();
      return text.includes(query);
    }).map(function (n) {
      var _n$querySelector, _n$querySelector$text, _n$querySelector2, _n$querySelector2$tex, _n$querySelector3;
      var name = ((_n$querySelector = n.querySelector('.node-name')) === null || _n$querySelector === void 0 ? void 0 : (_n$querySelector$text = _n$querySelector.textContent) === null || _n$querySelector$text === void 0 ? void 0 : _n$querySelector$text.trim()) || n.dataset.name || 'Relative';
      var role = ((_n$querySelector2 = n.querySelector('.node-title')) === null || _n$querySelector2 === void 0 ? void 0 : (_n$querySelector2$tex = _n$querySelector2.textContent) === null || _n$querySelector2$tex === void 0 ? void 0 : _n$querySelector2$tex.trim()) || n.dataset.role || '';
      var img = ((_n$querySelector3 = n.querySelector('.profile-image')) === null || _n$querySelector3 === void 0 ? void 0 : _n$querySelector3.src) || n.dataset.img || '/resources/images/profile/avatarM.png';
      var id = n.dataset.nId || n.dataset.id || '';
      return {
        id,
        name,
        role,
        img,
        nodeType: 'dom'
      };
    });
  }
  if (matches.length === 0) {
    searchDropdown.innerHTML = '<div style="padding: 12px 16px; color: #64748b; font-size: 0.85rem;">No relatives found</div>';
    searchDropdown.style.display = 'block';
    return;
  }
  var resultsHtml = '';
  matches.slice(0, 6).forEach(function (item) {
    resultsHtml += "\n      <div class=\"search-result-item\" data-id=\"".concat(item.id, "\" data-name=\"").concat(item.name, "\">\n        <img src=\"").concat(item.img, "\" alt=\"").concat(item.name, "\">\n        <div class=\"search-result-info\">\n          <div class=\"search-result-name\">").concat(item.name, "</div>\n          <div class=\"search-result-meta\">").concat(item.role, "</div>\n        </div>\n      </div>\n    ");
  });
  searchDropdown.innerHTML = resultsHtml;
  searchDropdown.style.display = 'block';
});

// Select from search dropdown
searchDropdown === null || searchDropdown === void 0 ? void 0 : searchDropdown.addEventListener('click', function (e) {
  var item = e.target.closest('.search-result-item');
  if (!item) return;
  var targetId = item.dataset.id;
  var targetName = item.dataset.name;
  searchDropdown.style.display = 'none';
  if (memberSearchInput) memberSearchInput.value = targetName;

  // If Balkan FamilyTree instance exists, zoom & center directly
  if (window.family && targetId) {
    try {
      window.family.center(targetId);
      if (typeof window.family.highlight === 'function') {
        window.family.highlight(targetId);
      }
    } catch (err) {
      console.log('[FamilyTree Search] Balkan center:', err);
    }
  }

  // Also add pulse highlight effect to the SVG node
  var targetElem = document.querySelector("[data-n-id=\"".concat(targetId, "\"]")) || Array.from(document.querySelectorAll('.tree-node, [data-n-id]')).find(function (el) {
    return el.textContent.includes(targetName);
  });
  if (targetElem) {
    targetElem.classList.add('highlighted');
    setTimeout(function () {
      return targetElem.classList.remove('highlighted');
    }, 3500);
  }
});

// Close search dropdown on outside click
document.addEventListener('click', function (e) {
  if (!e.target.closest('.search-box-wrapper')) {
    if (searchDropdown) searchDropdown.style.display = 'none';
  }
});

// 3. Navigation Guide Toggle (Default to Collapsed)
var instructionsToggle = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('instructionsToggle');
var instructions = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_0__.id)('instructions');

// Always keep collapsed by default unless user explicitly opened it
var instructionsState = localStorage.getItem('instructionsCollapsed');
if (instructionsState === 'false' && instructions) {
  instructions.classList.remove('collapsed');
} else if (instructions) {
  instructions.classList.add('collapsed');
}
instructionsToggle === null || instructionsToggle === void 0 ? void 0 : instructionsToggle.addEventListener('click', function () {
  if (!instructions) return;
  instructions.classList.toggle('collapsed');
  var isCollapsed = instructions.classList.contains('collapsed');
  localStorage.setItem('instructionsCollapsed', isCollapsed);
  var icon = instructionsToggle.querySelector('i');
  if (icon) {
    icon.className = isCollapsed ? 'bi bi-question-circle' : 'bi bi-x-circle-fill';
  }
});

// Auto-fit to screen after initial render
setTimeout(function () {
  (0,_events__WEBPACK_IMPORTED_MODULE_1__.fitToScreen)();
}, 200);

/***/ }),

/***/ "./resources/asset/js/components/familyTree/showModal.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/familyTree/showModal.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "showPersonDetails": function() { return /* binding */ showPersonDetails; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");



var personModal = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('personModal');
var modalBody = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('modalBody');
var closeModal = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('closeModal');

/**
 * Open the Person Details Modal for a selected person
 * @param {Object} personData
 */
var showPersonDetails = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee(personData) {
    var _ref2, _ref2$fullName, fullName, _ref2$email, email, _ref2$img, img, _ref2$relation, relation, _ref2$maritalStatus, maritalStatus, _ref2$spouseName, spouseName, _ref2$occupation, occupation, _ref2$country, country, _ref2$personId, personId, _ref2$nodeId, nodeId, _ref2$isRegistered, isRegistered, _ref2$familyCode, familyCode, _ref2$isDeceased, isDeceased, _ref2$bio, bio, targetModal, targetModalBody, inviteMessage, claimSpotHtml, closeActionBtn;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _ref2 = personData || {}, _ref2$fullName = _ref2.fullName, fullName = _ref2$fullName === void 0 ? 'Family Member' : _ref2$fullName, _ref2$email = _ref2.email, email = _ref2$email === void 0 ? '' : _ref2$email, _ref2$img = _ref2.img, img = _ref2$img === void 0 ? '/resources/images/profile/avatarM.png' : _ref2$img, _ref2$relation = _ref2.relation, relation = _ref2$relation === void 0 ? 'Relative' : _ref2$relation, _ref2$maritalStatus = _ref2.maritalStatus, maritalStatus = _ref2$maritalStatus === void 0 ? '' : _ref2$maritalStatus, _ref2$spouseName = _ref2.spouseName, spouseName = _ref2$spouseName === void 0 ? '' : _ref2$spouseName, _ref2$occupation = _ref2.occupation, occupation = _ref2$occupation === void 0 ? '' : _ref2$occupation, _ref2$country = _ref2.country, country = _ref2$country === void 0 ? '' : _ref2$country, _ref2$personId = _ref2.personId, personId = _ref2$personId === void 0 ? null : _ref2$personId, _ref2$nodeId = _ref2.nodeId, nodeId = _ref2$nodeId === void 0 ? null : _ref2$nodeId, _ref2$isRegistered = _ref2.isRegistered, isRegistered = _ref2$isRegistered === void 0 ? false : _ref2$isRegistered, _ref2$familyCode = _ref2.familyCode, familyCode = _ref2$familyCode === void 0 ? '' : _ref2$familyCode, _ref2$isDeceased = _ref2.isDeceased, isDeceased = _ref2$isDeceased === void 0 ? false : _ref2$isDeceased, _ref2$bio = _ref2.bio, bio = _ref2$bio === void 0 ? '' : _ref2$bio;
          targetModal = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('personModal');
          targetModalBody = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('modalBody');
          if (!(!targetModal || !targetModalBody)) {
            _context.next = 1;
            break;
          }
          return _context.abrupt("return");
        case 1:
          inviteMessage = encodeURIComponent("Hey! I just started building our family tree on FamilyPlatform. Our family code is ".concat(familyCode, ". Click here to join and explore our family lineage! ").concat(window.location.origin, "/register"));
          claimSpotHtml = !isRegistered ? "\n    <div class=\"claim-spot-section mt-4\" style=\"background: var(--gold-light); padding: 18px; border-radius: 18px; text-align: center; border: 1px dashed var(--gold-accent);\">\n      <h4 style=\"color: var(--primary-color); font-size: 1rem; font-weight: 700; margin-bottom: 6px;\">Invite Relative to Claim This Spot</h4>\n      <p style=\"font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;\">Send a personal invitation so they can join and share family memories.</p>\n      <div style=\"display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;\">\n        <a href=\"whatsapp://send?text=".concat(inviteMessage, "\" class=\"btn\" style=\"background: #25D366; color: white; border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; text-decoration: none; font-weight: 600;\">\n          <i class=\"bi bi-whatsapp\"></i> WhatsApp\n        </a>\n        <a href=\"sms:?body=").concat(inviteMessage, "\" class=\"btn\" style=\"background: var(--primary-color); color: white; border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; text-decoration: none; font-weight: 600;\">\n          <i class=\"bi bi-chat-text\"></i> SMS\n        </a>\n      </div>\n    </div>\n  ") : ''; // Inject content into modal body
          targetModalBody.innerHTML = "\n    <div class=\"person-detail\">\n      <img src=\"".concat(img, "\" alt=\"").concat(fullName, "\" class=\"person-image\">\n      <div class=\"person-info\">\n        <h2 class=\"person-name\">").concat(fullName, "</h2>\n        <div class=\"person-relation\">").concat(relation || 'Relative', "</div>\n        ").concat(isDeceased ? '<span class="badge bg-secondary ms-2" style="font-size: 0.75rem;"><i class="bi bi-flower1"></i> Deceased</span>' : '', "\n      </div>\n    </div>\n\n    <div class=\"detail-grid\">\n      <div class=\"detail-item\">\n        <div class=\"detail-label\">Relationship</div>\n        <div class=\"detail-value\">").concat(relation || 'Family Member', "</div>\n      </div>\n\n      <div class=\"detail-item\">\n        <div class=\"detail-label\">Marital Status</div>\n        <div class=\"detail-value\">").concat(maritalStatus || 'N/A', "</div>\n      </div>\n\n      <div class=\"detail-item\">\n        <div class=\"detail-label\">Occupation</div>\n        <div class=\"detail-value\">").concat(occupation || 'Not specified', "</div>\n      </div>\n\n      <div class=\"detail-item\">\n        <div class=\"detail-label\">Location / Origin</div>\n        <div class=\"detail-value\">").concat(country || 'N/A', "</div>\n      </div>\n\n      ").concat(spouseName ? "\n        <div class=\"detail-item\" style=\"grid-column: span 2;\">\n          <div class=\"detail-label\">Spouse / Partner</div>\n          <div class=\"detail-value\" style=\"display: flex; align-items: center; justify-content: space-between;\">\n            <span>".concat(spouseName, "</span>\n            <span class=\"badge\" style=\"background: var(--gold-light); color: var(--primary-dark); font-size: 0.75rem;\"><i class=\"bi bi-heart-fill text-danger\"></i> Partner</span>\n          </div>\n        </div>\n      ") : '', "\n\n      ").concat(email ? "\n        <div class=\"detail-item\" style=\"grid-column: span 2;\">\n          <div class=\"detail-label\">Email Address</div>\n          <div class=\"detail-value\">".concat(email, "</div>\n        </div>\n      ") : '', "\n    </div>\n\n    ").concat(bio ? "\n      <div class=\"detail-item mt-3\" style=\"width: 100%;\">\n        <div class=\"detail-label\">Notes & Biography</div>\n        <div class=\"detail-value\" style=\"font-size: 0.9rem; font-weight: 500;\">".concat(bio, "</div>\n      </div>\n    ") : '', "\n\n    <div id=\"extraUnionsSection\"></div>\n\n    ").concat(claimSpotHtml, "\n\n    <div class=\"modal-actions mt-4\" style=\"display: flex; gap: 10px; justify-content: flex-end;\">\n      ").concat(personId ? "\n        <a href=\"/allMembers/seeProfile/".concat(personId, "\" class=\"btn\" style=\"background: var(--primary-color); color: white; border-radius: 14px; padding: 8px 20px; font-weight: 600; text-decoration: none;\">\n          <i class=\"bi bi-person-fill\"></i> View Profile\n        </a>\n      ") : '', "\n      <button type=\"button\" class=\"btn\" id=\"modalCloseActionBtn\" style=\"background: #e2e8f0; color: #334155; border-radius: 14px; padding: 8px 18px; font-weight: 600;\">\n        Close\n      </button>\n    </div>\n  ");

          // Attach close handlers
          closeActionBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('modalCloseActionBtn');
          closeActionBtn === null || closeActionBtn === void 0 ? void 0 : closeActionBtn.addEventListener('click', function () {
            targetModal.style.display = 'none';
          });

          // Display modal
          targetModal.style.display = 'flex';
        case 2:
        case "end":
          return _context.stop();
      }
    }, _callee);
  }));
  return function showPersonDetails(_x) {
    return _ref.apply(this, arguments);
  };
}();

// Global modal close handlers
var initModalClosers = function initModalClosers() {
  var modal = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('personModal');
  var closeBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)('closeModal');
  closeBtn === null || closeBtn === void 0 ? void 0 : closeBtn.addEventListener('click', function () {
    if (modal) modal.style.display = 'none';
  });
  window.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
};
initModalClosers();
window.showPersonDetails = showPersonDetails;

/***/ })

}]);
//# sourceMappingURL=organogram.js.map