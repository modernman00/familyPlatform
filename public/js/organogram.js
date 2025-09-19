"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["organogram"],{

/***/ "./resources/asset/js/components/familyTree/events.js":
/*!************************************************************!*\
  !*** ./resources/asset/js/components/familyTree/events.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initTree: () => (/* binding */ initTree)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _showModal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./showModal */ "./resources/asset/js/components/familyTree/showModal.js");


var zoomInBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('zoomIn');
var zoomOutBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('zoomOut');
var resetZoomBtn = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('resetZoom');
var personModal = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('personModal');
var modalBody = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('modalBody');
var closeModal = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('closeModal');
var familyTree = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('familyTree');
var treeWrapper = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('treeWrapper');

// Initial zoom scale for the family tree
var scale = 1;
// Current position of the tree (used for panning)
var position = {
  x: 0,
  y: 0
};
// Starting position when dragging begins
var startPosition = {
  x: 0,
  y: 0
};
// Flag to track whether the user is currently dragging
var isDragging = false;

// Applies zoom and pan transformations to the tree container
function updateTransform() {
  familyTree.style.transform = "translate(".concat(position.x, "px, ").concat(position.y, "px) scale(").concat(scale, ")");
}

// Attaches all event listeners for zooming, dragging, and interaction
var addEventListeners = function addEventListeners() {
  // Zoom in when the "+" button is clicked
  zoomInBtn.addEventListener('click', function () {
    scale += 0.1;
    updateTransform();
  });

  // Zoom out when the "−" button is clicked, but not below 0.5x
  zoomOutBtn.addEventListener('click', function () {
    scale = Math.max(0.5, scale - 0.1);
    updateTransform();
  });

  // Reset zoom and position to default
  resetZoomBtn.addEventListener('click', function () {
    scale = 1;
    position = {
      x: 0,
      y: 0
    };
    updateTransform();
  });

  // Zoom using mouse wheel (scroll up/down)
  treeWrapper.addEventListener('wheel', function (e) {
    e.preventDefault(); // Prevent page scroll
    var delta = -e.deltaY * 0.01;
    scale = Math.min(Math.max(0.5, scale + delta), 3); // Clamp between 0.5 and 3
    updateTransform();
  });

  // Start dragging when mouse is pressed
  treeWrapper.addEventListener('mousedown', function (e) {
    isDragging = true;
    startPosition = {
      x: e.clientX - position.x,
      y: e.clientY - position.y
    };
    treeWrapper.style.cursor = 'grabbing';
  });

  // Update position while dragging
  document.addEventListener('mousemove', function (e) {
    if (!isDragging) return;
    position.x = e.clientX - startPosition.x;
    position.y = e.clientY - startPosition.y;
    updateTransform();
  });

  // Stop dragging when mouse is released
  document.addEventListener('mouseup', function () {
    isDragging = false;
    treeWrapper.style.cursor = 'grab';
  });

  // Touch start for mobile drag
  treeWrapper.addEventListener('touchstart', function (e) {
    if (e.touches.length === 1) {
      isDragging = true;
      startPosition = {
        x: e.touches[0].clientX - position.x,
        y: e.touches[0].clientY - position.y
      };
    }
  });

  // Touch move for mobile drag
  document.addEventListener('touchmove', function (e) {
    if (!isDragging || e.touches.length !== 1) return;
    position.x = e.touches[0].clientX - startPosition.x;
    position.y = e.touches[0].clientY - startPosition.y;
    updateTransform();
  });

  // Touch end to stop dragging
  document.addEventListener('touchend', function () {
    isDragging = false;
  });
};

// Entry point: initializes the tree when the page loads
var initTree = function initTree() {
  updateTransform(); // Apply initial zoom and position
  addEventListeners(); // Attach all interaction handlers
};

/***/ }),

/***/ "./resources/asset/js/components/familyTree/familyData.js":
/*!****************************************************************!*\
  !*** ./resources/asset/js/components/familyTree/familyData.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   familyData: () => (/* binding */ familyData)
/* harmony export */ });
// Sample data for family members
var familyData = {
  "SHOLA OLAOGUN": {
    image: "https://randomuser.me/api/portraits/men/75.jpg",
    relation: "Father",
    fullName: "SHOLA OLAOGUN",
    birthDate: "January 15, 1950",
    birthPlace: "Lagos, Nigeria",
    occupation: "Business Owner",
    education: "Bachelor of Commerce",
    email: "shola.olaogun@example.com",
    phone: "+44 7800 123456"
  },
  "ADEBOLA OLAOGUN": {
    image: "https://randomuser.me/api/portraits/women/75.jpg",
    relation: "Mother",
    fullName: "ADEBOLA OLAOGUN",
    birthDate: "March 22, 1955",
    birthPlace: "Ibadan, Nigeria",
    occupation: "Teacher (Retired)",
    education: "Bachelor of Education",
    email: "adebola.olaogun@example.com",
    phone: "+44 7800 123457"
  },
  "Lafane OLAOGUN": {
    image: "https://randomuser.me/api/portraits/men/32.jpg",
    relation: "Self",
    fullName: "Lafane OLAOGUN",
    birthDate: "June 15, 1975",
    birthPlace: "Lagos, Nigeria",
    occupation: "Business Owner",
    education: "MSc. Business Administration",
    email: "lafane.olaogun@example.com",
    phone: "+44 7805 262504",
    residence: "London, United Kingdom"
  }
  // Add more family members as needed
};

/***/ }),

/***/ "./resources/asset/js/components/familyTree/index.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/familyTree/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _events__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./events */ "./resources/asset/js/components/familyTree/events.js");
/* harmony import */ var _showModal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./showModal */ "./resources/asset/js/components/familyTree/showModal.js");




var modalBody = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('modalBody');
var closeModal = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('closeModal');
var personModal = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('personModal');
(0,_shared__WEBPACK_IMPORTED_MODULE_0__.log)('tree loaded');
(0,_events__WEBPACK_IMPORTED_MODULE_1__.initTree)();
(0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('familyTree').addEventListener('click', function (e) {
  var _node$querySelector;
  var node = e.target.closest('.tree-node');
  if (!node) return;
  var name = (_node$querySelector = node.querySelector('.node-name')) === null || _node$querySelector === void 0 || (_node$querySelector = _node$querySelector.textContent) === null || _node$querySelector === void 0 ? void 0 : _node$querySelector.trim();
  var nodeFn = function nodeFn(nodeClass) {
    var _first$dataset;
    var first = node.querySelector(nodeClass);
    return (first === null || first === void 0 || (_first$dataset = first.dataset) === null || _first$dataset === void 0 ? void 0 : _first$dataset.id) || null;
  };
  var email = nodeFn('.node-email');
  var id = nodeFn('.node-id');
  var img = nodeFn('.node-img');
  var fullName = nodeFn('.node-fullName');
  var maritalStatus = nodeFn('.node-maritalStatus');
  var spouseName = nodeFn('.node-spouseName');
  var occupation = nodeFn('.node-occupation');
  var detailData = {
    fullName: fullName,
    email: email,
    img: img,
    maritalStatus: maritalStatus,
    spouseName: spouseName,
    occupation: occupation
  };
  (0,_showModal__WEBPACK_IMPORTED_MODULE_2__.showPersonDetails)(detailData);
});

// When a person node is clicked, show their details
// document.querySelectorAll('.tree-node').forEach(node => {
//   node.addEventListener('click', (e) => {
//     e.stopPropagation(); // Prevent bubbling to parent

//      // Get the name from the .node-name span
//   const name = node.querySelector('.node-name')?.textContent?.trim();

//         // Get the data-id from the .node-id span
//   const idSpan = node.querySelector('.node-id');
//   const dataId = idSpan?.dataset?.id || null;
//     showPersonDetails(name);
//   });
// });

// Close modal when "X" button is clicked
closeModal.addEventListener('click', function () {
  personModal.style.display = 'none';
});

// Close modal when clicking outside the modal content
window.addEventListener('click', function (e) {
  if (e.target === personModal) {
    personModal.style.display = 'none';
  }
});

/***/ }),

/***/ "./resources/asset/js/components/familyTree/showModal.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/familyTree/showModal.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   showPersonDetails: () => (/* binding */ showPersonDetails)
/* harmony export */ });
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _familyData__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./familyData */ "./resources/asset/js/components/familyTree/familyData.js");

 // Import the family data from './familydata'

var personModal = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('personModal');
var modalBody = (0,_shared__WEBPACK_IMPORTED_MODULE_0__.id)('modalBody');

// Displays detailed info about a person in the modal
var showPersonDetails = function showPersonDetails(personData) {
  // destroy the modal if it's already open
  if (personModal.classList.contains('show')) {
    personModal.classList.remove('show');
  }

  // deconstruct the data object and assign it to variables
  var img = personData.img,
    relation = personData.relation,
    fullName = personData.fullName,
    spouseName = personData.spouseName,
    occupation = personData.occupation,
    country = personData.country;

  // Inject HTML into modal body with person's details
  modalBody.innerHTML = "\n    <div class=\"person-detail\">\n      <img src=\"".concat(img, "\" alt=\"").concat(fullName, "\" class=\"person-image\">\n      <div class=\"person-info\">\n        <h2 class=\"person-name\">").concat(fullName, "</h2>\n        <div class=\"person-relation\">").concat(relation, "</div>\n      </div>\n    </div>\n    <div class=\"detail-grid\">\n      <div class=\"detail-item\">\n        <div class=\"detail-label\">Spouse</div>\n        <div class=\"detail-value\">").concat(spouseName || 'N/A', "</div>\n      </div>\n \n      <div class=\"detail-item\">\n        <div class=\"detail-label\">Occupation</div>\n        <div class=\"detail-value\">").concat(occupation, "</div>\n      </div>\n      ").concat(country ? "\n        <div class=\"detail-item\">\n          <div class=\"detail-label\">country</div>\n          <div class=\"detail-value\">".concat(country, "</div>\n        </div>\n      ") : '', "\n    </div>\n  ");

  // Show the modal
  personModal.style.display = 'flex';
};

/***/ })

}]);
//# sourceMappingURL=organogram.js.map