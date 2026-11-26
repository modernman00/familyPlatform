"use strict";
(self["webpackChunkfamily"] = self["webpackChunkfamily"] || []).push([["all_members"],{

/***/ "./resources/asset/js/components/allMembers/allEvents.js":
/*!***************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/allEvents.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../global.js */ "./resources/asset/js/components/global.js");
/* harmony import */ var _navbar__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../navbar */ "./resources/asset/js/components/navbar.js");
/* harmony import */ var _profilePage_htmlFolder_friendRequestCard__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../profilePage/htmlFolder/friendRequestCard */ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js");


function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
;







// Attach a click event listener to the document
/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = /*#__PURE__*/function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee(e) {
    var btn, targetId, _initialDrafts$, targetUserId, targetName, firstName, kinshipPillars, selectedCategory, selectedRole, hasUserManuallyEditedNote, getDraftTemplates, renderPillarCards, renderRoleChips, renderDraftPills, initialDrafts, initialDefaultNote, targetInitials, modalHtml, _yield$Swal$fire, isConfirmed, value, originalText, approverDetails, familyRequestData, result, _requestErr$response, _requestErr$response$, errText, userId, url, _result, memberCard, csrfToken, response, container, remaining, countStr, memberCountBadge, memberCountDisplay, _response$data, _errText, _err$response, _err$response$data, _errText2, _userId, _userId2, _targetUserId, _targetName, safeTargetName, _yield$Swal$fire2, note, _isConfirmed, _originalText, _csrfToken, _response, _response$data2, _response$data3, _response$data4, _err$response2, _err$response2$data, _err$response3, _err$response3$data, errMsg, _targetUserId2, _targetName2, targetFirstName, _safeTargetName, safeFirstName, _yield$Swal$fire3, _note, _isConfirmed2, _originalText2, _csrfToken2, _response2, _response2$data, _response2$data2, _response2$data3, _err$response4, _err$response4$data, _err$response5, _err$response5$data, _errMsg, _t, _t2, _t3, _t4, _t5;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          // Get the target element's ID (defensively checking closest button or element with ID)
          btn = e.target.closest('button') || e.target.closest('[id]');
          targetId = btn ? btn.id : e.target.id || '';
          if (targetId) {
            _context.next = 1;
            break;
          }
          return _context.abrupt("return");
        case 1:
          if (!targetId.includes('addFamily')) {
            _context.next = 8;
            break;
          }
          targetUserId = btn.getAttribute('data-target-id') || targetId.replace('addFamily', '');
          targetName = btn.getAttribute('data-target-name') || 'this relative';
          firstName = targetName.split(' ')[0] || targetName; // 3-Pillar Taxonomy Presets for Connected Kin (Artboards 18, 19, 20 — Line Icons, Zero Emoji)
          kinshipPillars = {
            in_laws: {
              key: 'in_laws',
              icon: "<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"9\" cy=\"12\" r=\"5\"/><circle cx=\"15\" cy=\"12\" r=\"5\"/></svg>",
              title: 'In-laws',
              desc: 'Connected by marriage',
              roles: ['Brother-in-Law', 'Sister-in-Law', 'Parent-in-Law', 'Son-in-Law / Daughter-in-Law', 'Spouse’s Relative', 'Other In-Law']
            },
            extended_lineage: {
              key: 'extended_lineage',
              icon: "<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"12\" cy=\"5\" r=\"2.5\"/><circle cx=\"6\" cy=\"19\" r=\"2.5\"/><circle cx=\"18\" cy=\"19\" r=\"2.5\"/><path d=\"M12 7.5V12m0 0H6v4.5m6-4.5h6v4.5\"/></svg>",
              title: 'Extended lineage',
              desc: 'Maternal or blood branch',
              roles: ['Maternal Cousin', 'Paternal Cousin', 'Aunt / Uncle', 'Niece / Nephew', 'Grandparent / Elder', 'Other Blood Relative']
            },
            family_friend: {
              key: 'family_friend',
              icon: "<svg width=\"22\" height=\"22\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z\"/></svg>",
              title: 'Family friend',
              desc: 'Godparent, close to family',
              roles: ['Lifelong Family Friend', 'Godparent / Godchild', 'Family Elder / Mentor', 'Neighbour / Associate', 'Other Family Friend']
            }
          };
          selectedCategory = 'extended_lineage';
          selectedRole = 'Maternal Cousin';
          hasUserManuallyEditedNote = false; // Smart Contextual Draft Templates per Pillar & Role
          getDraftTemplates = function getDraftTemplates(catKey, role, name) {
            var cleanName = name || 'there';
            if (catKey === 'in_laws') {
              if (role.includes('Parent')) {
                return [{
                  label: 'Respectful',
                  text: "Dear ".concat(cleanName, ", sending warm regards and respect. Delighted to connect with you here on FamilyPlatform.")
                }, {
                  label: 'Family Circle',
                  text: "Hello ".concat(cleanName, ", wishing you good health and joy. Excited to connect our family circles and share updates.")
                }, {
                  label: 'Warm Hello',
                  text: "Dear ".concat(cleanName, ", hope you are doing wonderfully. Looking forward to keeping in touch.")
                }];
              }
              if (role.includes('Son-in-Law') || role.includes('Daughter-in-Law')) {
                return [{
                  label: 'Warm Welcome',
                  text: "Dear ".concat(cleanName, ", so wonderful to connect with you on FamilyPlatform! Sending warm family love.")
                }, {
                  label: 'Family Updates',
                  text: "Hello ".concat(cleanName, ", excited to connect as family and share milestones and celebrations.")
                }, {
                  label: 'Warm Hello',
                  text: "Hi ".concat(cleanName, ", hope all is well with you. Delighted to stay in touch on here.")
                }];
              }
              return [{
                label: 'Warm Hello',
                text: "Hi ".concat(cleanName, ", hope you're doing well! Reaching out to connect our families on the platform and stay in touch.")
              }, {
                label: 'Family News',
                text: "Hello ".concat(cleanName, ", wonderful to see you here! Let's connect as in-laws to share celebrations and family updates.")
              }, {
                label: 'Household Regards',
                text: "Hi ".concat(cleanName, ", sending warmest greetings from our household! Looking forward to staying connected.")
              }];
            }
            if (catKey === 'extended_lineage') {
              if (role.includes('Cousin')) {
                return [{
                  label: 'Catching Up',
                  text: "Hi ".concat(cleanName, ", great to find you here! Let's stay in touch and share updates across our family branches.")
                }, {
                  label: 'Family Branches',
                  text: "Hello ".concat(cleanName, "! Reaching out from our branch of the family. Wonderful to connect with you here.")
                }, {
                  label: 'Milestones',
                  text: "Hi ".concat(cleanName, ", hope all is well! Let's connect so we don't miss any family news and celebrations.")
                }];
              }
              if (role.includes('Aunt') || role.includes('Uncle') || role.includes('Elder') || role.includes('Grandparent')) {
                return [{
                  label: 'Warm Respect',
                  text: "Dear ".concat(cleanName, ", sending you our deepest respect and warmest family love. Delighted to be connected.")
                }, {
                  label: 'Family Regards',
                  text: "Hello ".concat(cleanName, ", so happy to see you on the platform! Sending much love from our branch of the family.")
                }, {
                  label: 'Staying in Touch',
                  text: "Dear ".concat(cleanName, ", hope you are doing wonderfully. Looking forward to keeping in touch with you here.")
                }];
              }
              return [{
                label: 'Reconnecting',
                text: "Hi ".concat(cleanName, ", so great to discover family here! Let's connect our branches and stay in touch.")
              }, {
                label: 'Lineage Bridge',
                text: "Hello ".concat(cleanName, ", reaching out from our side of the family. Delighted to connect on FamilyPlatform!")
              }, {
                label: 'Warm Wishes',
                text: "Hi ".concat(cleanName, ", sending warm wishes from our household. Excited to share family updates with you.")
              }];
            }

            // family_friend
            if (role.includes('Godparent') || role.includes('Godchild')) {
              return [{
                label: 'Special Bond',
                text: "Dear ".concat(cleanName, ", so wonderful to connect with you here! Thank you for always being such a special part of our lives.")
              }, {
                label: 'Warm Blessings',
                text: "Hello ".concat(cleanName, ", sending warmest family love and blessings. Great to have you in our connected circle!")
              }, {
                label: 'Catch Up',
                text: "Dear ".concat(cleanName, ", hope you are keeping well. Looking forward to sharing our family updates with you.")
              }];
            }
            return [{
              label: 'Warm Hello',
              text: "Hi ".concat(cleanName, ", so lovely to see you on FamilyPlatform! You've always been like family to our household.")
            }, {
              label: 'Family Moments',
              text: "Hello ".concat(cleanName, ", hope you're doing great! Let's stay connected and share our family moments and news.")
            }, {
              label: 'Household Regards',
              text: "Hi ".concat(cleanName, ", sending warmest greetings from all of us! Delighted to connect with you on here.")
            }];
          };
          renderPillarCards = function renderPillarCards() {
            return Object.values(kinshipPillars).map(function (p) {
              var isActive = p.key === selectedCategory;
              return "\n          <div class=\"kinship-pillar-card ".concat(isActive ? 'active' : '', "\" data-pillar=\"").concat(p.key, "\" style=\"\n            border: 1.5px solid ").concat(isActive ? '#a31621' : '#e6dfd5', ";\n            background: ").concat(isActive ? '#fdf2f2' : '#ffffff', ";\n            border-radius: 14px;\n            padding: 12px 10px;\n            cursor: pointer;\n            text-align: left;\n            transition: all 0.18s ease;\n            user-select: none;\n          \">\n            <div class=\"kinship-pillar-icon\" style=\"color: ").concat(isActive ? '#a31621' : '#5c5049', "; margin-bottom: 6px; display: inline-flex;\">").concat(p.icon, "</div>\n            <div style=\"font-weight: 700; font-size: 0.86rem; color: #1c1412; margin-bottom: 2px;\">").concat(p.title, "</div>\n            <div style=\"font-size: 0.72rem; color: #786c65; line-height: 1.3;\">").concat(p.desc, "</div>\n          </div>\n        ");
            }).join('');
          };
          renderRoleChips = function renderRoleChips(categoryKey) {
            var pillar = kinshipPillars[categoryKey] || kinshipPillars.extended_lineage;
            return pillar.roles.map(function (r) {
              var isActive = r === selectedRole;
              return "\n          <button type=\"button\" class=\"kinship-role-chip ".concat(isActive ? 'active' : '', "\" data-role=\"").concat(r, "\" style=\"\n            padding: 7px 14px;\n            border-radius: 9999px;\n            border: 1px solid ").concat(isActive ? '#1c1412' : '#e2d9cd', ";\n            background: ").concat(isActive ? '#1c1412' : '#ffffff', ";\n            color: ").concat(isActive ? '#ffffff' : '#3d322c', ";\n            font-size: 0.81rem;\n            font-weight: ").concat(isActive ? '700' : '600', ";\n            cursor: pointer;\n            transition: all 0.15s ease;\n            white-space: nowrap;\n            min-height: 44px;\n            display: inline-flex;\n            align-items: center;\n            justify-content: center;\n          \">").concat(r, "</button>\n        ");
            }).join('');
          };
          renderDraftPills = function renderDraftPills(categoryKey, roleName) {
            var drafts = getDraftTemplates(categoryKey, roleName, firstName);
            return drafts.map(function (d, idx) {
              return "\n          <button type=\"button\" class=\"kinship-draft-pill\" data-draft-idx=\"".concat(idx, "\" style=\"\n            padding: 6px 13px;\n            border-radius: 9999px;\n            border: 1.5px solid ").concat(idx === 0 ? '#a31621' : '#e2d9cd', ";\n            background: ").concat(idx === 0 ? '#fdf2f2' : '#ffffff', ";\n            color: ").concat(idx === 0 ? '#a31621' : '#3d322c', ";\n            font-size: 0.76rem;\n            font-weight: ").concat(idx === 0 ? '700' : '600', ";\n            cursor: pointer;\n            transition: all 0.15s ease;\n            white-space: nowrap;\n            min-height: 36px;\n            display: inline-flex;\n            align-items: center;\n            gap: 5px;\n          \">\n            ").concat(d.label, "\n          </button>\n        ");
            }).join('');
          };
          initialDrafts = getDraftTemplates(selectedCategory, selectedRole, firstName);
          initialDefaultNote = ((_initialDrafts$ = initialDrafts[0]) === null || _initialDrafts$ === void 0 ? void 0 : _initialDrafts$.text) || '';
          targetInitials = targetName.split(' ').filter(Boolean).slice(0, 2).map(function (part) {
            var _part$;
            return ((_part$ = part[0]) === null || _part$ === void 0 ? void 0 : _part$.toUpperCase()) || '';
          }).join('') || 'FK';
          modalHtml = "\n        <style>\n          .swal2-popup.swal-kinship-modal {\n            width: 780px !important;\n            max-width: 95vw !important;\n            max-height: 92vh !important;\n            max-height: 92dvh !important;\n            display: flex !important;\n            flex-direction: column !important;\n            overflow: hidden !important;\n            padding: 22px 24px 16px 24px !important;\n            border-radius: 24px !important;\n            background: #ffffff !important;\n            border: 1px solid #e6dfd5 !important;\n            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;\n          }\n          .swal2-popup.swal-kinship-modal .swal2-html-container {\n            overflow-y: auto !important;\n            overflow-x: hidden !important;\n            flex: 1 1 auto !important;\n            max-height: calc(92dvh - 110px) !important;\n            margin: 0 !important;\n            padding: 2px 4px 4px 2px !important;\n            scrollbar-width: thin;\n            scrollbar-color: #d5c8b8 transparent;\n          }\n          .swal-kinship-body-grid {\n            display: grid;\n            grid-template-columns: 1.08fr 0.92fr;\n            gap: 24px;\n            align-items: start;\n          }\n          @media (max-width: 859.98px) {\n            .swal2-popup.swal-kinship-modal {\n              width: 560px !important;\n            }\n            .swal-kinship-body-grid {\n              grid-template-columns: 1fr;\n              gap: 18px;\n            }\n          }\n          .kinship-step-badge {\n            width: 22px;\n            height: 22px;\n            border-radius: 50%;\n            background: #fdf2f2;\n            color: #a31621;\n            font-size: 0.74rem;\n            font-weight: 800;\n            display: inline-flex;\n            align-items: center;\n            justify-content: center;\n            flex-shrink: 0;\n          }\n          .kinship-pillar-card:hover {\n            border-color: #a31621 !important;\n          }\n          .kinship-role-chip:hover:not(.active) {\n            background-color: #faf6f0 !important;\n            border-color: #c5b8a8 !important;\n            color: #1c1412 !important;\n          }\n          .kinship-draft-pill:hover {\n            border-color: #a31621 !important;\n          }\n          .swal2-popup.swal-kinship-modal .swal2-actions {\n            display: flex !important;\n            justify-content: flex-end !important;\n            gap: 10px !important;\n            margin: 14px 0 0 0 !important;\n            padding: 14px 0 0 0 !important;\n            border-top: 1px solid #efe8de !important;\n            width: 100% !important;\n            flex-shrink: 0 !important;\n          }\n          .swal2-popup.swal-kinship-modal .swal2-confirm {\n            min-height: 46px !important;\n            border-radius: 12px !important;\n            font-weight: 700 !important;\n            padding: 10px 22px !important;\n            font-size: 0.9rem !important;\n            background: #a31621 !important;\n            box-shadow: 0 6px 16px rgba(163, 22, 33, 0.22) !important;\n            white-space: nowrap !important;\n            flex-shrink: 0 !important;\n          }\n          .swal2-popup.swal-kinship-modal .swal2-cancel {\n            min-height: 46px !important;\n            border-radius: 12px !important;\n            font-weight: 700 !important;\n            padding: 10px 18px !important;\n            font-size: 0.9rem !important;\n            background: #ffffff !important;\n            color: #3d322c !important;\n            border: 1px solid #dfd7cc !important;\n            white-space: nowrap !important;\n            flex-shrink: 0 !important;\n          }\n          @media (max-width: 640px) {\n            .swal2-popup.swal-kinship-modal {\n              padding: 16px 14px 12px 14px !important;\n              max-height: 94dvh !important;\n              border-radius: 20px !important;\n            }\n            .swal2-popup.swal-kinship-modal .swal2-actions {\n              margin-top: 10px !important;\n              padding-top: 10px !important;\n              gap: 8px !important;\n            }\n            .swal2-popup.swal-kinship-modal .swal2-confirm,\n            .swal2-popup.swal-kinship-modal .swal2-cancel {\n              flex: 1 !important;\n              min-height: 44px !important;\n              padding: 10px 12px !important;\n              font-size: 0.86rem !important;\n            }\n            .kinship-pillar-grid {\n              gap: 8px !important;\n            }\n          }\n        </style>\n\n        <div class=\"text-start\" style=\"font-family: inherit; color: #1c1412;\">\n          <!-- Header Row -->\n          <div class=\"d-flex align-items-center justify-content-between pb-3 mb-3\" style=\"border-bottom: 1px solid #efe8de;\">\n            <div class=\"d-flex align-items-center gap-3 min-w-0\">\n              <div class=\"d-flex align-items-center justify-content-center rounded-circle fw-bold flex-shrink-0\" style=\"width: 44px; height: 44px; background: #f4d8cd; color: #8a1019; font-size: 0.95rem;\">\n                ".concat(targetInitials, "\n              </div>\n              <div class=\"min-w-0\">\n                <h5 class=\"mb-0 fw-bold text-truncate\" style=\"font-family: 'Outfit', sans-serif; font-size: 1.25rem; color: #1c1412; letter-spacing: -0.02em;\">Connect with ").concat(targetName, "</h5>\n                <div style=\"font-size: 0.82rem; color: #786c65;\">\n                  Extended family \xB7 Connect as kin\n                </div>\n              </div>\n            </div>\n          </div>\n\n          <div class=\"swal-kinship-body-grid\">\n            <!-- Left Column: Step 1 (Category) + Step 2 (Role) -->\n            <div>\n              <!-- Step 1: Select Kinship Category -->\n              <div class=\"d-flex align-items-center gap-2 mb-2\">\n                <span class=\"kinship-step-badge\">1</span>\n                <span class=\"fw-bold\" style=\"font-size: 0.9rem; color: #1c1412;\">How are you connected to ").concat(firstName, "?</span>\n              </div>\n              <div id=\"kinshipPillarContainer\" class=\"kinship-pillar-grid\" style=\"display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px;\">\n                ").concat(renderPillarCards(), "\n              </div>\n\n              <!-- Step 2: Specific Role -->\n              <div class=\"d-flex align-items-center gap-2 mb-2\">\n                <span class=\"kinship-step-badge\">2</span>\n                <span class=\"fw-bold\" style=\"font-size: 0.9rem; color: #1c1412;\">Who is ").concat(firstName, " to you?</span>\n              </div>\n              <div id=\"kinshipRoleContainer\" style=\"display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;\">\n                ").concat(renderRoleChips(selectedCategory), "\n              </div>\n\n              <div class=\"mt-2\" id=\"customRoleWrapper\" style=\"display: none;\">\n                <input type=\"text\" id=\"kinshipCustomRole\" class=\"form-control\" placeholder=\"Specify relationship (e.g. Second Cousin)\" maxlength=\"100\" style=\"border-radius: 10px; font-size: 0.85rem; padding: 8px 12px; min-height: 44px; border-color: #dfd7cc;\">\n              </div>\n            </div>\n\n            <!-- Right Column: Step 3 (Note with drafts) + Condensed 2-Line Privacy Card -->\n            <div>\n              <!-- Step 3: Optional Personal Note with Smart Drafts -->\n              <div class=\"mb-3\">\n                <div class=\"d-flex justify-content-between align-items-center mb-2\">\n                  <div class=\"d-flex align-items-center gap-2\">\n                    <span class=\"kinship-step-badge\">3</span>\n                    <span class=\"fw-bold\" style=\"font-size: 0.9rem; color: #1c1412;\">Add a note <span class=\"fw-normal\" style=\"font-size: 0.8rem; color: #786c65;\">(optional)</span></span>\n                  </div>\n                  <button type=\"button\" id=\"kinshipClearNoteBtn\" class=\"btn btn-link p-0 small text-decoration-none fw-semibold\" style=\"font-size: 0.76rem; color: #786c65;\">\n                    Clear\n                  </button>\n                </div>\n\n                <!-- Contextual Draft Pills -->\n                <div id=\"kinshipDraftPillsContainer\" style=\"display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px;\">\n                  ").concat(renderDraftPills(selectedCategory, selectedRole), "\n                </div>\n                <span id=\"kinshipDraftRoleLabel\" class=\"d-none\">").concat(selectedRole, "</span>\n\n                <div class=\"position-relative\">\n                  <textarea id=\"kinshipNote\" class=\"form-control\" rows=\"3\" maxlength=\"255\" placeholder=\"Pick a draft above or write your own message...\" style=\"border-radius: 14px; font-size: 0.85rem; line-height: 1.5; padding: 12px 14px 26px; resize: none; border: 1px solid #dfd7cc; background: #ffffff; color: #1c1412;\">").concat(initialDefaultNote, "</textarea>\n                  <span id=\"kinshipCharCount\" class=\"font-monospace position-absolute\" style=\"bottom: 8px; right: 12px; font-size: 0.72rem; color: #8c7e75;\">").concat(initialDefaultNote.length, "/255</span>\n                </div>\n              </div>\n\n              <!-- Condensed 2-Line Privacy Block (Artboards 18, 19, 20) -->\n              <div class=\"p-3 rounded-3 d-flex align-items-start gap-2.5\" style=\"background: #faf6f0; border: 1px solid #e6dfd5; color: #3d322c;\">\n                <i class=\"bi bi-shield-check flex-shrink-0 mt-0.5\" style=\"color: #a31621; font-size: 1rem;\"></i>\n                <div style=\"font-size: 0.78rem; line-height: 1.48;\">\n                  <div><strong style=\"color: #1c1412;\">You'll share:</strong> family feed, stories and tree.</div>\n                  <div><strong style=\"color: #1c1412;\">Stays private:</strong> phone, email, address and birth year \u2014 change anytime in Settings.</div>\n                </div>\n              </div>\n            </div>\n          </div>\n        </div>\n      ");
          _context.next = 2;
          return sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
            html: modalHtml,
            showCancelButton: true,
            confirmButtonColor: '#a31621',
            cancelButtonColor: '#ffffff',
            confirmButtonText: 'Send connection request',
            cancelButtonText: 'Cancel',
            width: 780,
            customClass: {
              popup: 'rounded-4 shadow-lg border-0 swal-kinship-modal'
            },
            didOpen: function didOpen() {
              var popup = sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().getPopup();
              var pillarCards = popup.querySelectorAll('.kinship-pillar-card');
              var roleContainer = popup.querySelector('#kinshipRoleContainer');
              var customWrapper = popup.querySelector('#customRoleWrapper');
              var customInput = popup.querySelector('#kinshipCustomRole');
              var draftPillsContainer = popup.querySelector('#kinshipDraftPillsContainer');
              var draftRoleLabel = popup.querySelector('#kinshipDraftRoleLabel');
              var noteTextarea = popup.querySelector('#kinshipNote');
              var charCount = popup.querySelector('#kinshipCharCount');
              var clearNoteBtn = popup.querySelector('#kinshipClearNoteBtn');
              var updateCharCount = function updateCharCount() {
                if (charCount && noteTextarea) {
                  charCount.textContent = "".concat(noteTextarea.value.length, "/255");
                }
              };
              var highlightActivePill = function highlightActivePill(activeIdx) {
                if (!draftPillsContainer) return;
                var pills = draftPillsContainer.querySelectorAll('.kinship-draft-pill');
                pills.forEach(function (p, idx) {
                  if (idx === activeIdx) {
                    p.style.background = '#fdf2f2';
                    p.style.color = '#a31621';
                    p.style.borderColor = '#a31621';
                    p.style.fontWeight = '700';
                  } else {
                    p.style.background = '#ffffff';
                    p.style.color = '#3d322c';
                    p.style.borderColor = '#e2d9cd';
                    p.style.fontWeight = '600';
                  }
                });
              };
              var updateDraftsUI = function updateDraftsUI() {
                var preserveIfUserEdited = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
                if (draftRoleLabel) draftRoleLabel.textContent = selectedRole;
                var drafts = getDraftTemplates(selectedCategory, selectedRole, firstName);
                if (draftPillsContainer) {
                  draftPillsContainer.innerHTML = renderDraftPills(selectedCategory, selectedRole);
                  var pills = draftPillsContainer.querySelectorAll('.kinship-draft-pill');
                  pills.forEach(function (pill, idx) {
                    pill.onclick = function (ev) {
                      ev.preventDefault();
                      var targetDraft = drafts[idx];
                      if (targetDraft && noteTextarea) {
                        noteTextarea.value = targetDraft.text;
                        hasUserManuallyEditedNote = false;
                        updateCharCount();
                        highlightActivePill(idx);

                        // Tactile visual feedback on insertion
                        noteTextarea.style.transition = 'box-shadow 0.25s ease';
                        noteTextarea.style.boxShadow = '0 0 0 3px rgba(2, 132, 199, 0.25)';
                        setTimeout(function () {
                          if (noteTextarea) noteTextarea.style.boxShadow = 'none';
                        }, 500);
                      }
                    };
                  });
                }

                // Auto-populate default draft if user hasn't typed their own custom note
                if (!preserveIfUserEdited || !hasUserManuallyEditedNote) {
                  if (noteTextarea && drafts[0]) {
                    noteTextarea.value = drafts[0].text;
                    updateCharCount();
                    highlightActivePill(0);
                  }
                } else {
                  highlightActivePill(-1);
                }
              };
              var updateRoleListeners = function updateRoleListeners() {
                var roleChips = roleContainer.querySelectorAll('.kinship-role-chip');
                roleChips.forEach(function (chip) {
                  chip.onclick = function () {
                    roleChips.forEach(function (c) {
                      c.classList.remove('active');
                      c.style.background = '#ffffff';
                      c.style.color = '#3d322c';
                      c.style.borderColor = '#e2d9cd';
                      c.style.fontWeight = '600';
                    });
                    chip.classList.add('active');
                    chip.style.background = '#1c1412';
                    chip.style.color = '#ffffff';
                    chip.style.borderColor = '#1c1412';
                    chip.style.fontWeight = '700';
                    selectedRole = chip.getAttribute('data-role') || '';
                    if (selectedRole.includes('Other')) {
                      customWrapper.style.display = 'block';
                      customInput.focus();
                    } else {
                      customWrapper.style.display = 'none';
                    }
                    updateDraftsUI(true);
                  };
                });
              };
              pillarCards.forEach(function (card) {
                card.onclick = function () {
                  pillarCards.forEach(function (c) {
                    c.classList.remove('active');
                    c.style.borderColor = '#e6dfd5';
                    c.style.background = '#ffffff';
                    var iconEl = c.querySelector('.kinship-pillar-icon');
                    if (iconEl) iconEl.style.color = '#5c5049';
                  });
                  card.classList.add('active');
                  card.style.borderColor = '#a31621';
                  card.style.background = '#fdf2f2';
                  var activeIconEl = card.querySelector('.kinship-pillar-icon');
                  if (activeIconEl) activeIconEl.style.color = '#a31621';
                  selectedCategory = card.getAttribute('data-pillar') || 'extended_lineage';

                  // Set default role for new category
                  var defaultPillar = kinshipPillars[selectedCategory];
                  selectedRole = (defaultPillar === null || defaultPillar === void 0 ? void 0 : defaultPillar.roles[0]) || 'Connected Kin';
                  roleContainer.innerHTML = renderRoleChips(selectedCategory);
                  customWrapper.style.display = 'none';
                  updateRoleListeners();
                  updateDraftsUI(true);
                };
              });
              if (noteTextarea) {
                noteTextarea.oninput = function () {
                  hasUserManuallyEditedNote = true;
                  updateCharCount();
                  highlightActivePill(-1);
                };
              }
              if (clearNoteBtn) {
                clearNoteBtn.onclick = function (ev) {
                  ev.preventDefault();
                  if (noteTextarea) {
                    noteTextarea.value = '';
                    hasUserManuallyEditedNote = true;
                    updateCharCount();
                    highlightActivePill(-1);
                    noteTextarea.focus();
                  }
                };
              }
              updateRoleListeners();
              updateDraftsUI(false);
            },
            preConfirm: function preConfirm() {
              var popup = sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().getPopup();
              var customRoleEl = popup.querySelector('#kinshipCustomRole');
              var noteEl = popup.querySelector('#kinshipNote');
              var finalRole = selectedRole;
              if (selectedRole.includes('Other') && customRoleEl && customRoleEl.value.trim() !== '') {
                finalRole = customRoleEl.value.trim();
              }
              return {
                category: selectedCategory,
                role: finalRole,
                note: noteEl ? noteEl.value.trim() : ''
              };
            }
          });
        case 2:
          _yield$Swal$fire = _context.sent;
          isConfirmed = _yield$Swal$fire.isConfirmed;
          value = _yield$Swal$fire.value;
          if (!(isConfirmed && value)) {
            _context.next = 7;
            break;
          }
          btn.disabled = true;
          originalText = btn.innerHTML;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
          _context.prev = 3;
          _context.next = 4;
          return fetchApproverData(targetUserId);
        case 4:
          approverDetails = _context.sent;
          familyRequestData = {
            approver: approverDetails,
            relationship_category: value.category,
            relationship_role: value.role,
            relationship_note: value.note,
            emailPath: 'msg/request_premium'
          }; // Send the family request data to the server
          _context.next = 5;
          return sendFamilyRequest(familyRequestData);
        case 5:
          result = _context.sent;
          if (result.data.status === 'success' && result.data.message === 'Request sent') {
            btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i> Request Sent';
            btn.classList.remove('btn-stitch-connect');
            btn.classList.add('btn-outline-success');
            btn.disabled = true;
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
              icon: 'success',
              title: 'Request Sent!',
              html: "\n                <p class=\"text-muted small mb-0\">\n                  Your connection request has been sent to <strong>".concat(targetName, "</strong> as <strong>").concat(value.role, "</strong>.\n                  They will receive a notification capturing this relationship and your respective family trees will remain private.\n                </p>\n              "),
              confirmButtonColor: '#059669',
              confirmButtonText: 'Great!'
            });
          } else if (result.data.status === 'error' && result.data.message === 'Request already pending') {
            btn.innerHTML = 'Request Pending';
            btn.disabled = true;
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire('Request Pending', 'You already have an active request pending with this member.', 'info');
          } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire('Error', result.data.message || 'Unable to send request. Please try again.', 'error');
          }
          _context.next = 7;
          break;
        case 6:
          _context.prev = 6;
          _t = _context["catch"](3);
          btn.innerHTML = originalText;
          btn.disabled = false;
          (0,_shared__WEBPACK_IMPORTED_MODULE_5__.showError)(_t);
          errText = ((_requestErr$response = _t.response) === null || _requestErr$response === void 0 ? void 0 : (_requestErr$response$ = _requestErr$response.data) === null || _requestErr$response$ === void 0 ? void 0 : _requestErr$response$.message) || _t.message || 'Error sending connection request';
          sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire('Error', errText, 'error');
        case 7:
          _context.next = 27;
          break;
        case 8:
          if (!targetId.includes('removeProfile')) {
            _context.next = 14;
            break;
          }
          // Extract the user ID from the target ID
          userId = targetId.replace('removeProfile', '');
          url = "/allMembers/removeProfile/".concat(userId); // Confirm with SweetAlert warning modal
          _context.next = 9;
          return sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
            title: 'Are you sure?',
            text: 'You will no longer see this profile and associated posts. Are you sure you want to remove this connection?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#a31621',
            confirmButtonText: 'Yes, remove connection!'
          });
        case 9:
          _result = _context.sent;
          if (!_result.isConfirmed) {
            _context.next = 13;
            break;
          }
          memberCard = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.qSel)(".member_profile_".concat(userId)) || (0,_shared__WEBPACK_IMPORTED_MODULE_5__.id)(userId) || (btn ? btn.closest('.member-card') : null);
          _context.prev = 10;
          csrfToken = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.getCsrfToken)();
          _context.next = 11;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].delete(url, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            params: {
              token: csrfToken
            },
            data: {
              token: csrfToken
            }
          });
        case 11:
          response = _context.sent;
          if (response.data && (response.data.status === 'success' || response.data.message === 'success')) {
            if (memberCard) {
              memberCard.remove();
            }

            // Update member count badge
            container = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.id)('allMembers');
            if (container) {
              remaining = container.querySelectorAll('.member-card').length;
              countStr = remaining.toLocaleString();
              memberCountBadge = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.id)('memberCount');
              memberCountDisplay = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.id)('memberCountDisplay');
              if (memberCountBadge) memberCountBadge.textContent = countStr;
              if (memberCountDisplay) memberCountDisplay.textContent = countStr;
              if (remaining === 0) {
                container.innerHTML = "\n                  <div class=\"col-12 text-center py-5 bg-white rounded-4 border w-100\" style=\"grid-column: 1 / -1; border-radius: var(--stitch-radius-lg);\">\n                      <div class=\"mx-auto mb-3 d-flex align-items-center justify-content-center\" style=\"width: 64px; height: 64px; font-size: 1.8rem; background: var(--stitch-primary-container); color: var(--stitch-primary); border-radius: 50%;\">\n                          <i class=\"bi bi-people\"></i>\n                      </div>\n                      <h5 class=\"fw-bold text-dark mb-1\">No Members in View</h5>\n                      <p class=\"text-muted small mb-3\" style=\"max-width: 440px; margin: 0 auto;\">\n                          There are no matching relatives in this directory category yet. Invite or connect with your family members to build your network.\n                      </p>\n                      <a href=\"/familyStudio\" class=\"btn btn-primary btn-sm fw-bold px-4 py-2\" style=\"border-radius: var(--stitch-radius-pill);\">\n                          <i class=\"bi bi-plus-circle-fill me-1\"></i> Open Family Studio\n                      </a>\n                  </div>\n                ";
              }
            }
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
              title: 'Removed!',
              text: 'Member connection has been removed successfully.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            _errText = ((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Error deleting profile';
            (0,_shared__WEBPACK_IMPORTED_MODULE_5__.msgException)(_errText);
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire('Error', _errText, 'error');
          }
          _context.next = 13;
          break;
        case 12:
          _context.prev = 12;
          _t2 = _context["catch"](10);
          (0,_shared__WEBPACK_IMPORTED_MODULE_5__.showError)(_t2);
          _errText2 = ((_err$response = _t2.response) === null || _err$response === void 0 ? void 0 : (_err$response$data = _err$response.data) === null || _err$response$data === void 0 ? void 0 : _err$response$data.message) || _t2.message || 'Error deleting profile';
          sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire('Error', _errText2, 'error');
        case 13:
          _context.next = 27;
          break;
        case 14:
          if (!targetId.includes('seeProfile')) {
            _context.next = 15;
            break;
          }
          // Extract the user ID from the target ID
          _userId = targetId.replace('seeProfile', ''); // redirect to 'allMembers/setProfile/'+userId
          window.location.href = "/allMembers/seeProfile/".concat(_userId);
          _context.next = 27;
          break;
        case 15:
          if (!targetId.includes('familyTree')) {
            _context.next = 16;
            break;
          }
          // Extract the user ID from the target ID
          _userId2 = targetId.replace('familyTree', ''); // redirect to 'allMembers/setProfile/'+userId
          window.location.href = "/organogram/".concat(_userId2);
          _context.next = 27;
          break;
        case 16:
          if (!targetId.includes('nudgeMigrate')) {
            _context.next = 22;
            break;
          }
          _targetUserId = btn.getAttribute('data-target-id') || targetId.replace('nudgeMigrate', '');
          _targetName = btn.getAttribute('data-target-name') || 'this relative';
          safeTargetName = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.esc)(_targetName);
          _context.next = 17;
          return sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
            title: "Nudge ".concat(safeTargetName, "?"),
            html: "\n          <p class=\"text-muted small mb-3\">\n            Invite ".concat(safeTargetName, " to migrate to your family network so you can share your family tree, events, and memories.\n          </p>\n          <div class=\"form-group text-start\">\n            <label for=\"swal-nudge-note\" class=\"form-label small fw-semibold text-secondary\">Optional Note:</label>\n            <textarea id=\"swal-nudge-note\" class=\"form-control form-control-sm\" rows=\"2\" placeholder=\"e.g. Hi! I saw your profile in the directory. Let's unite our family trees!\"></textarea>\n          </div>\n        "),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8a1019',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Send Nudge',
            cancelButtonText: 'Cancel',
            preConfirm: function preConfirm() {
              var noteEl = document.getElementById('swal-nudge-note');
              return noteEl ? noteEl.value.trim() : '';
            }
          });
        case 17:
          _yield$Swal$fire2 = _context.sent;
          note = _yield$Swal$fire2.value;
          _isConfirmed = _yield$Swal$fire2.isConfirmed;
          if (!_isConfirmed) {
            _context.next = 21;
            break;
          }
          btn.disabled = true;
          _originalText = btn.innerHTML;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
          _context.prev = 18;
          _csrfToken = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.getCsrfToken)();
          _context.next = 19;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/api/family-migration/nudge', {
            target_user_id: _targetUserId,
            note: note || null
          }, {
            headers: {
              'X-CSRF-TOKEN': _csrfToken,
              'X-XSRF-TOKEN': _csrfToken,
              'Content-Type': 'application/json'
            }
          });
        case 19:
          _response = _context.sent;
          if (_response.data && _response.data.success) {
            btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i> Nudge Sent';
            btn.classList.remove('btn-stitch-tonal');
            btn.classList.add('btn-outline-success');
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
              icon: 'success',
              title: 'Nudge Sent!',
              text: ((_response$data2 = _response.data) === null || _response$data2 === void 0 ? void 0 : _response$data2.email_sent) === false ? "".concat(_targetName, " has been invited and will see it in the app, but the email could not be delivered.") : "We have sent a migration invitation to ".concat(_targetName, ". They will receive a notification to review and accept."),
              confirmButtonColor: '#10b981'
            });
          } else {
            btn.disabled = false;
            btn.innerHTML = _originalText;
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
              icon: 'warning',
              title: 'Notice',
              text: ((_response$data3 = _response.data) === null || _response$data3 === void 0 ? void 0 : _response$data3.message) || ((_response$data4 = _response.data) === null || _response$data4 === void 0 ? void 0 : _response$data4.error) || 'Unable to send nudge.',
              confirmButtonColor: '#f59e0b'
            });
          }
          _context.next = 21;
          break;
        case 20:
          _context.prev = 20;
          _t3 = _context["catch"](18);
          btn.disabled = false;
          btn.innerHTML = _originalText;
          errMsg = ((_err$response2 = _t3.response) === null || _err$response2 === void 0 ? void 0 : (_err$response2$data = _err$response2.data) === null || _err$response2$data === void 0 ? void 0 : _err$response2$data.error) || ((_err$response3 = _t3.response) === null || _err$response3 === void 0 ? void 0 : (_err$response3$data = _err$response3.data) === null || _err$response3$data === void 0 ? void 0 : _err$response3$data.message) || 'A network error occurred. Please try again.';
          sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
            icon: 'error',
            title: 'Error',
            text: errMsg,
            confirmButtonColor: '#ef4444'
          });
        case 21:
          _context.next = 27;
          break;
        case 22:
          if (!targetId.includes('requestMigrate')) {
            _context.next = 27;
            break;
          }
          _targetUserId2 = btn.getAttribute('data-target-id') || targetId.replace('requestMigrate', '');
          _targetName2 = btn.getAttribute('data-target-name') || 'this family';
          targetFirstName = btn.getAttribute('data-target-first-name') || _targetName2.split(' ')[0] || 'this';
          _safeTargetName = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.esc)(_targetName2);
          safeFirstName = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.esc)(targetFirstName);
          _context.next = 23;
          return sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
            title: "Join ".concat(safeFirstName, " famcode?"),
            html: "\n          <p class=\"text-muted small mb-3\">\n            Request to migrate your account into <strong>".concat(_safeTargetName, "</strong>'s family network.\n            <br><br>\n            <span class=\"badge bg-primary text-white px-2 py-1\"><i class=\"bi bi-info-circle me-1\"></i> Subject to Family Approval</span>\n            <br>\n            <small class=\"text-muted mt-2 d-block\">Existing members of ").concat(safeFirstName, "'s family will be notified to approve your transfer.</small>\n          </p>\n          <div class=\"form-group text-start\">\n            <label for=\"swal-req-note\" class=\"form-label small fw-semibold text-secondary\">Optional Note / Kinship explanation:</label>\n            <textarea id=\"swal-req-note\" class=\"form-control form-control-sm\" rows=\"2\" placeholder=\"e.g. Hello, I am relative to ").concat(_safeTargetName, ", registering to connect with our family!\"></textarea>\n          </div>\n        "),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#a31621',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-box-arrow-in-right me-1"></i> Submit Request',
            cancelButtonText: 'Cancel',
            preConfirm: function preConfirm() {
              var noteEl = document.getElementById('swal-req-note');
              return noteEl ? noteEl.value.trim() : '';
            }
          });
        case 23:
          _yield$Swal$fire3 = _context.sent;
          _note = _yield$Swal$fire3.value;
          _isConfirmed2 = _yield$Swal$fire3.isConfirmed;
          if (!_isConfirmed2) {
            _context.next = 27;
            break;
          }
          btn.disabled = true;
          _originalText2 = btn.innerHTML;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
          _context.prev = 24;
          _csrfToken2 = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.getCsrfToken)();
          _context.next = 25;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/api/family-migration/request', {
            target_user_id: _targetUserId2,
            note: _note || null
          }, {
            headers: {
              'X-CSRF-TOKEN': _csrfToken2,
              'X-XSRF-TOKEN': _csrfToken2,
              'Content-Type': 'application/json'
            }
          });
        case 25:
          _response2 = _context.sent;
          if (_response2.data && _response2.data.success) {
            btn.innerHTML = '<i class="bi bi-hourglass-split text-primary me-1"></i> Request Pending';
            btn.classList.remove('btn-stitch-tonal');
            btn.classList.add('btn-outline-primary');
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
              icon: 'success',
              title: 'Request Submitted!',
              text: ((_response2$data = _response2.data) === null || _response2$data === void 0 ? void 0 : _response2$data.email_sent) === false ? "Your request to join ".concat(targetFirstName, "'s family was submitted and members will see it in the app, but the email could not be delivered.") : "Your migration request to join ".concat(targetFirstName, "'s family has been sent! You will be notified as soon as a family member approves."),
              confirmButtonColor: '#a31621'
            });
          } else {
            btn.disabled = false;
            btn.innerHTML = _originalText2;
            sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
              icon: 'warning',
              title: 'Notice',
              text: ((_response2$data2 = _response2.data) === null || _response2$data2 === void 0 ? void 0 : _response2$data2.message) || ((_response2$data3 = _response2.data) === null || _response2$data3 === void 0 ? void 0 : _response2$data3.error) || 'Unable to submit request.',
              confirmButtonColor: '#f59e0b'
            });
          }
          _context.next = 27;
          break;
        case 26:
          _context.prev = 26;
          _t4 = _context["catch"](24);
          btn.disabled = false;
          btn.innerHTML = _originalText2;
          _errMsg = ((_err$response4 = _t4.response) === null || _err$response4 === void 0 ? void 0 : (_err$response4$data = _err$response4.data) === null || _err$response4$data === void 0 ? void 0 : _err$response4$data.error) || ((_err$response5 = _t4.response) === null || _err$response5 === void 0 ? void 0 : (_err$response5$data = _err$response5.data) === null || _err$response5$data === void 0 ? void 0 : _err$response5$data.message) || 'A network error occurred. Please try again.';
          sweetalert2__WEBPACK_IMPORTED_MODULE_4___default().fire({
            icon: 'error',
            title: 'Error',
            text: _errMsg,
            confirmButtonColor: '#ef4444'
          });
        case 27:
          _context.next = 29;
          break;
        case 28:
          _context.prev = 28;
          _t5 = _context["catch"](0);
          // Handle any errors that occur during execution
          (0,_shared__WEBPACK_IMPORTED_MODULE_5__.showError)(_t5);
        case 29:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 28], [3, 6], [10, 12], [18, 20], [24, 26]]);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
}();

// Function to fetch approver data based on user ID
function fetchApproverData(_x2) {
  return _fetchApproverData.apply(this, arguments);
} // Function to send family request data to the server
function _fetchApproverData() {
  _fetchApproverData = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee2(userId) {
    var result, approverDetails, _t6;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function (_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          _context2.prev = 0;
          _context2.next = 1;
          return axios__WEBPACK_IMPORTED_MODULE_3__["default"].get("/members/familyRequestMgt/getApprover?id=".concat(userId));
        case 1:
          result = _context2.sent;
          approverDetails = {
            approverFirstName: result.data.message.firstName,
            approverLastName: result.data.message.lastName,
            approverEmail: result.data.message.email,
            approverId: result.data.message.id,
            approverCode: result.data.message.famCode
          };
          return _context2.abrupt("return", approverDetails);
        case 2:
          _context2.prev = 2;
          _t6 = _context2["catch"](0);
          console.error(_t6);
          throw _t6;
        case 3:
        case "end":
          return _context2.stop();
      }
    }, _callee2, null, [[0, 2]]);
  }));
  return _fetchApproverData.apply(this, arguments);
}
function sendFamilyRequest(data) {
  try {
    var csrfToken = (0,_global_js__WEBPACK_IMPORTED_MODULE_6__.getCsrfToken)();
    return axios__WEBPACK_IMPORTED_MODULE_3__["default"].post('/members/familyRequestMgt', _objectSpread(_objectSpread({}, data), {}, {
      token: csrfToken
    }), {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-XSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
      }
    });
  } catch (error) {
    ;(0,_shared__WEBPACK_IMPORTED_MODULE_5__.showError)(error);
  }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
  var theTargetId = (0,_shared__WEBPACK_IMPORTED_MODULE_5__.id)(targetId);
  theTargetId.innerHTML = newHTML;
  theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB

/***/ }),

/***/ "./resources/asset/js/components/allMembers/api.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/api.js ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   applyFilter: function() { return /* binding */ applyFilter; },
/* harmony export */   renderMembers: function() { return /* binding */ renderMembers; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _html__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./html */ "./resources/asset/js/components/allMembers/html.js");
/* harmony import */ var _handleInput__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./handleInput */ "./resources/asset/js/components/allMembers/handleInput.js");
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");




function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
// resources/js/allMembers/api.js
;


var allMembersContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("allMembers");
var memberCountBadge = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberCount");
var memberCountDisplay = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberCountDisplay");
var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("searchFamily");
var NO_MEMBER_HTML = "\n  <div class=\"col-12 text-center py-5 bg-white rounded-4 border w-100\" style=\"grid-column: 1 / -1; border-radius: var(--stitch-radius-lg);\">\n      <div class=\"mx-auto mb-3 d-flex align-items-center justify-content-center\" style=\"width: 64px; height: 64px; font-size: 1.8rem; background: var(--stitch-primary-container); color: var(--stitch-primary); border-radius: 50%;\">\n          <i class=\"bi bi-people\"></i>\n      </div>\n      <h5 class=\"fw-bold text-dark mb-1\">No Members in View</h5>\n      <p class=\"text-muted small mb-3\" style=\"max-width: 440px; margin: 0 auto;\">\n          There are no matching relatives in this directory category yet. Invite or connect with your family members to build your network.\n      </p>\n      <a href=\"/familyStudio\" class=\"btn btn-primary btn-sm fw-bold px-4 py-2\" style=\"border-radius: var(--stitch-radius-pill);\">\n          <i class=\"bi bi-plus-circle-fill me-1\"></i> Open Family Studio\n      </a>\n  </div>\n";

/**
 * Render a list of members into the main container.
 * Also updates the member count badge.
 *
 * @param {Array<object>} members
 */
var renderMembers = function renderMembers() {
  var members = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
  allMembersContainer.innerHTML = "";
  if (!members.length) {
    allMembersContainer.innerHTML = NO_MEMBER_HTML;
    if (memberCountBadge) memberCountBadge.textContent = "0";
    if (memberCountDisplay) memberCountDisplay.textContent = "0";
    return;
  }

  // Render each member card
  members.forEach(_html__WEBPACK_IMPORTED_MODULE_4__.renderHtml);

  // Update member count badges
  var countStr = members.length.toLocaleString();
  if (memberCountBadge) memberCountBadge.textContent = countStr;
  if (memberCountDisplay) memberCountDisplay.textContent = countStr;
};

// Client-side category pill filtering
function applyFilter() {
  var filterVal = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "all";
  var cards = allMembersContainer.querySelectorAll(".member-card");
  var visibleCount = 0;
  cards.forEach(function (card) {
    var cat = card.getAttribute("data-category");
    if (filterVal === "all") {
      // "all" shows all connected members in network (family + connected kin)
      if (cat === "family" || cat === "connected") {
        card.style.display = "";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    } else if (cat === filterVal) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });
  var emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
  if (visibleCount === 0 && cards.length > 0) {
    if (!emptyPlaceholder) {
      var message = filterVal === "explore" ? "No additional members in the public directory." : "No members in this category.";
      allMembersContainer.insertAdjacentHTML("beforeend", "<div class=\"empty-filter-placeholder col-12 text-center py-4 text-muted small\" style=\"grid-column: 1 / -1;\">".concat(message, "</div>"));
    }
  } else if (emptyPlaceholder) {
    emptyPlaceholder.remove();
  }

  // Toggle Connected Kin Privacy Guidance Banner
  var privacyBanner = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("connectedKinPrivacyBanner");
  if (privacyBanner) {
    if (filterVal === "connected") {
      privacyBanner.classList.remove("d-none");
      privacyBanner.classList.add("d-flex");
    } else {
      privacyBanner.classList.add("d-none");
      privacyBanner.classList.remove("d-flex");
    }
  }
  return visibleCount;
}
function setupFilterPills() {
  var pillsContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberFilterPills");
  if (!pillsContainer) return;
  pillsContainer.addEventListener("click", function (e) {
    var btn = e.target.closest(".filter-pill-btn");
    if (!btn) return;
    pillsContainer.querySelectorAll(".filter-pill-btn").forEach(function (b) {
      return b.classList.remove("active");
    });
    btn.classList.add("active");
    var filterVal = btn.getAttribute("data-filter") || "all";
    var visibleCount = applyFilter(filterVal);
    if (memberCountBadge) {
      memberCountBadge.textContent = visibleCount.toLocaleString();
    }
  });
}
(function () {
  var _bootstrapAllMembers = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_2__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().mark(function _callee() {
    var url1, url2, _yield$Promise$all, _yield$Promise$all2, famCodeData, allUsersData, familyMembers, directoryMembers, normalizedDirMembers, seenIds, allLoadedMembers, loader, resetViewToCurrentFilter, handleSearch, _t;
    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_3___default().wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          url1 = "/allMembers/processApiData";
          url2 = "/allMembers/allData"; // Fetch caller's network (family/connected) and wider directory in parallel
          _context.next = 1;
          return Promise.all([(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.getApiData)(url1).catch(function () {
            return {
              message: []
            };
          }), (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.getApiData)(url2).catch(function () {
            return {
              message: []
            };
          })]);
        case 1:
          _yield$Promise$all = _context.sent;
          _yield$Promise$all2 = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_1__["default"])(_yield$Promise$all, 2);
          famCodeData = _yield$Promise$all2[0];
          allUsersData = _yield$Promise$all2[1];
          familyMembers = [];
          if (Array.isArray(famCodeData)) {
            familyMembers = famCodeData;
          } else if (Array.isArray(famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.message)) {
            familyMembers = famCodeData.message;
          } else if (Array.isArray(famCodeData === null || famCodeData === void 0 ? void 0 : famCodeData.data)) {
            familyMembers = famCodeData.data;
          }
          directoryMembers = [];
          if (Array.isArray(allUsersData)) {
            directoryMembers = allUsersData;
          } else if (Array.isArray(allUsersData === null || allUsersData === void 0 ? void 0 : allUsersData.message)) {
            directoryMembers = allUsersData.message;
          } else if (Array.isArray(allUsersData === null || allUsersData === void 0 ? void 0 : allUsersData.data)) {
            directoryMembers = allUsersData.data;
          }

          // Tag directory members with relationType 'other' unless already set
          normalizedDirMembers = directoryMembers.map(function (m) {
            return _objectSpread(_objectSpread({}, m), {}, {
              relationType: m.relationType || "other"
            });
          }); // Deduplicate any overlap by ID (prioritizing family/connected)
          seenIds = new Set();
          allLoadedMembers = [];
          familyMembers.forEach(function (m) {
            if (m && m.id && !seenIds.has(m.id)) {
              seenIds.add(m.id);
              allLoadedMembers.push(m);
            }
          });
          normalizedDirMembers.forEach(function (m) {
            if (m && m.id && !seenIds.has(m.id)) {
              seenIds.add(m.id);
              allLoadedMembers.push(m);
            }
          });

          // Pre-render all members (family + connected + directory) into DOM
          renderMembers(allLoadedMembers);

          // Initial view: show connected network by default
          applyFilter("all");
          if (memberCountBadge) {
            memberCountBadge.textContent = familyMembers.length.toLocaleString();
          }

          // Remove loading spinner
          loader = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("setLoader");
          if (loader) {
            loader.classList.remove("loader");
            loader.classList.add("d-none");
            loader.style.display = "none";
          }

          // Setup filter pills
          setupFilterPills();

          // Wire up search handler across both client-side loaded members & server endpoint
          if (searchInput) {
            resetViewToCurrentFilter = function resetViewToCurrentFilter() {
              var _id;
              renderMembers(allLoadedMembers);
              var activeBtn = (_id = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.id)("memberFilterPills")) === null || _id === void 0 ? void 0 : _id.querySelector(".filter-pill-btn.active");
              var activeFilter = (activeBtn === null || activeBtn === void 0 ? void 0 : activeBtn.getAttribute("data-filter")) || "all";
              applyFilter(activeFilter);
              if (memberCountBadge) {
                var count = activeFilter === "all" ? familyMembers.length : activeFilter === "explore" ? normalizedDirMembers.length : allLoadedMembers.length;
                memberCountBadge.textContent = count.toLocaleString();
              }
            };
            handleSearch = (0,_handleInput__WEBPACK_IMPORTED_MODULE_5__.createSearchHandler)({
              familyMembers,
              directoryMembers: normalizedDirMembers,
              allLoadedMembers,
              renderMembers,
              container: allMembersContainer,
              searchUrl: "/allMembers/search",
              onResetView: resetViewToCurrentFilter
            });
            searchInput.addEventListener("input", handleSearch);
            searchInput.addEventListener("search", handleSearch);
          }
          _context.next = 3;
          break;
        case 2:
          _context.prev = 2;
          _t = _context["catch"](0);
          (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_6__.showError)(_t);
        case 3:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 2]]);
  }));
  function bootstrapAllMembers() {
    return _bootstrapAllMembers.apply(this, arguments);
  }
  return bootstrapAllMembers;
})()();

/***/ }),

/***/ "./resources/asset/js/components/allMembers/handleInput.js":
/*!*****************************************************************!*\
  !*** ./resources/asset/js/components/allMembers/handleInput.js ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createSearchHandler: function() { return /* binding */ createSearchHandler; }
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @modernman00/shared-js-lib */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! axios */ "./node_modules/axios/lib/axios.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! sweetalert2 */ "./node_modules/sweetalert2/dist/sweetalert2.all.js");
/* harmony import */ var sweetalert2__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(sweetalert2__WEBPACK_IMPORTED_MODULE_5__);


// resources/js/allMembers/handleInput.js





/**
 * Render the modern Google Stitch "Invite Relative" card when search returns 0 results.
 * Wraps the entire block in a single container spanning the grid so children do not scatter.
 *
 * @param {HTMLElement} container
 * @param {string} rawQuery
 * @param {Function} [onResetView]
 */
var renderInviteBlock = function renderInviteBlock(container, rawQuery, onResetView) {
  var famCode = localStorage.getItem("requesterFamCode") || "";
  var yourName = localStorage.getItem("yourName") || "";
  var trimmedQuery = (rawQuery || "").trim();
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  var mobileRegex = /^\+?[1-9]\d{6,14}$/;
  var isQueryEmail = emailRegex.test(trimmedQuery);
  var isQueryPhone = mobileRegex.test(trimmedQuery);
  var initialName = !isQueryEmail && !isQueryPhone ? trimmedQuery : "";
  var initialEmail = isQueryEmail || isQueryPhone ? trimmedQuery : "";
  container.innerHTML = "\n    <div class=\"member-invite-container w-100\" style=\"grid-column: 1 / -1;\">\n      <div class=\"member-invite-card mx-auto\">\n        <div class=\"member-invite-cover\">\n          <div class=\"member-invite-icon-wrapper\">\n            <i class=\"bi bi-person-plus-fill\"></i>\n          </div>\n        </div>\n\n        <div class=\"member-invite-body\">\n          <div class=\"d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2\">\n            <span class=\"invite-badge\">\n              <i class=\"bi bi-search me-1\"></i> Member Not Found\n            </span>\n            ".concat(famCode ? "\n            <span class=\"invite-fam-code\" title=\"Your registered family code\">\n              <i class=\"bi bi-shield-check text-success me-1\"></i> Family Code: <strong>#".concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(famCode), "</strong>\n            </span>") : '', "\n          </div>\n\n          <h3 class=\"invite-title\">Invite Relative to Platform</h3>\n          <p class=\"invite-desc\">\n            We could not find anyone matching <strong class=\"text-dark\">\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(trimmedQuery), "\"</strong> in your family network. Enter their details below to send a personalised invitation to join your family space on FamilyPlatform.\n          </p>\n\n          <form id=\"newMemberInviteForm\" onsubmit=\"return false;\" novalidate>\n            <div class=\"mb-3\">\n              <label for=\"newMemberRequestName\" class=\"form-label invite-field-label\">\n                Relative's Full Name <span class=\"text-danger\">*</span>\n              </label>\n              <div class=\"input-group invite-input-group\">\n                <span class=\"input-group-text invite-addon\">\n                  <i class=\"bi bi-person-fill\"></i>\n                </span>\n                <input type=\"text\"\n                       id=\"newMemberRequestName\"\n                       name=\"newMemberRequestName\"\n                       class=\"form-control invite-input\"\n                       placeholder=\"e.g. Grandma Rose or Uncle David\"\n                       value=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(initialName), "\"\n                       autocomplete=\"name\"\n                       required>\n              </div>\n            </div>\n\n            <div class=\"mb-2\">\n              <label for=\"newMemberRequestEmail\" class=\"form-label invite-field-label\">\n                Email Address <span class=\"text-danger\">*</span>\n              </label>\n              <div class=\"input-group invite-input-group\">\n                <span class=\"input-group-text invite-addon\">\n                  <i class=\"bi bi-envelope-at-fill\"></i>\n                </span>\n                <input type=\"email\"\n                       id=\"newMemberRequestEmail\"\n                       name=\"newMemberRequestEmail\"\n                       class=\"form-control invite-input\"\n                       placeholder=\"e.g. rose.family@example.co.uk\"\n                       value=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(initialEmail), "\"\n                       autocomplete=\"email\"\n                       required>\n              </div>\n              <div class=\"form-text invite-help-hint\">\n                <i class=\"bi bi-shield-lock-fill text-muted me-1\"></i> A secure cryptographic invitation link will be sent to their email inbox.\n              </div>\n            </div>\n\n            <div id=\"newMemberRequest_help\" class=\"invite-error-msg d-none\" role=\"alert\"></div>\n\n            <div class=\"invite-actions-row\">\n              <button type=\"button\"\n                      class=\"btn invite-btn-secondary\"\n                      id=\"newMemberCancelBtn\"\n                      title=\"Clear search and return to directory\">\n                <i class=\"bi bi-arrow-left me-1\"></i> Clear Search\n              </button>\n\n              <button type=\"button\"\n                      class=\"btn invite-btn-primary\"\n                      id=\"newMemberRequestBtn\"\n                      title=\"Send invitation link\">\n                <i class=\"bi bi-send-fill me-1\"></i> Send Invitation\n              </button>\n            </div>\n\n            ").concat(famCode ? "\n            <div class=\"invite-share-footer\">\n              <div class=\"d-flex align-items-center justify-content-between flex-wrap gap-2\">\n                <span class=\"text-muted small\">Prefer instant messaging?</span>\n                <button type=\"button\" class=\"btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary\" id=\"btnShareInviteLink\">\n                  <i class=\"bi bi-share-fill me-1\"></i> Share Invite Link via WhatsApp\n                </button>\n              </div>\n            </div>" : '', "\n          </form>\n        </div>\n      </div>\n    </div>\n  ");
  var nameInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestName");
  var emailInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestEmail");
  var submitBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequestBtn");
  var cancelBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberCancelBtn");
  var helpMsg = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("newMemberRequest_help");
  var shareBtn = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("btnShareInviteLink");

  // Focus appropriate field based on pre-fill
  if (nameInput && emailInput) {
    if (initialName && !initialEmail) {
      emailInput.focus();
    } else if (!initialName && initialEmail) {
      nameInput.focus();
    } else if (nameInput) {
      nameInput.focus();
    }
  }

  // Clear Search button handler
  if (cancelBtn) {
    cancelBtn.addEventListener("click", function () {
      var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("searchFamily");
      if (searchInput) searchInput.value = "";
      if (typeof onResetView === "function") {
        onResetView();
      }
    });
  }

  // WhatsApp / System Share Link handler
  if (shareBtn && famCode) {
    shareBtn.addEventListener("click", /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee() {
      var joinUrl, shareText, _window$Swal, _t, _t2;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            joinUrl = "".concat(window.location.origin, "/register?famCode=").concat(encodeURIComponent(famCode));
            shareText = "Hi! Join our family space on FamilyPlatform so we can stay connected. Use our family code #".concat(famCode, ": ").concat(joinUrl);
            if (!navigator.share) {
              _context.next = 4;
              break;
            }
            _context.prev = 1;
            _context.next = 2;
            return navigator.share({
              title: "Join our Family on FamilyPlatform",
              text: shareText,
              url: joinUrl
            });
          case 2:
            return _context.abrupt("return");
          case 3:
            _context.prev = 3;
            _t = _context["catch"](1);
          case 4:
            _context.prev = 4;
            _context.next = 5;
            return navigator.clipboard.writeText(shareText);
          case 5:
            if (window.swalToast) {
              window.swalToast("Family invitation link copied to clipboard!", "success");
            } else if ((_window$Swal = window.Swal) !== null && _window$Swal !== void 0 && _window$Swal.fire) {
              window.Swal.fire({
                icon: "success",
                title: "Copied to Clipboard!",
                text: "Family invite link and code copied. You can now paste it into WhatsApp or Messages.",
                timer: 2500,
                showConfirmButton: false
              });
            } else {
              alert("Invitation link copied to clipboard!");
            }
            _context.next = 7;
            break;
          case 6:
            _context.prev = 6;
            _t2 = _context["catch"](4);
            prompt("Copy this invite link to share via WhatsApp:", joinUrl);
          case 7:
          case "end":
            return _context.stop();
        }
      }, _callee, null, [[1, 3], [4, 6]]);
    })));
  }

  // Submit button handler with spinner & SweetAlert
  if (submitBtn) {
    submitBtn.addEventListener("click", /*#__PURE__*/(0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee2() {
      var _window$ScoreTenantSp;
      var recipientName, recipientEmail, showValidationWarning, _response$data, _response$data2, postObj, response, status, message, swalLib, searchInput, errMsg, _swalLib, _error$response, _error$response$data, _errMsg, _swalLib2, _window$ScoreTenantSp2, _t3;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context2) {
        while (1) switch (_context2.prev = _context2.next) {
          case 0:
            recipientName = nameInput ? nameInput.value.trim() : "";
            recipientEmail = emailInput ? emailInput.value.trim() : "";
            showValidationWarning = function showValidationWarning(msg, inputToFocus) {
              if (helpMsg) {
                helpMsg.textContent = msg;
                helpMsg.classList.remove("d-none");
              }
              if (inputToFocus) inputToFocus.focus();
            };
            if (!(recipientName.length < 2)) {
              _context2.next = 1;
              break;
            }
            showValidationWarning("Please enter your relative's name (at least 2 characters).", nameInput);
            return _context2.abrupt("return");
          case 1:
            if (recipientEmail) {
              _context2.next = 2;
              break;
            }
            showValidationWarning("Please enter an email address so we can deliver their secure registration link.", emailInput);
            return _context2.abrupt("return");
          case 2:
            if (emailRegex.test(recipientEmail)) {
              _context2.next = 3;
              break;
            }
            showValidationWarning("Please enter a valid email address (e.g. relative@example.co.uk).", emailInput);
            return _context2.abrupt("return");
          case 3:
            if (helpMsg) {
              helpMsg.textContent = "";
              helpMsg.classList.add("d-none");
            }

            // Universal Button Spinner Start
            if ((_window$ScoreTenantSp = window.ScoreTenantSpinner) !== null && _window$ScoreTenantSp !== void 0 && _window$ScoreTenantSp.start) {
              window.ScoreTenantSpinner.start(submitBtn);
            } else {
              submitBtn.disabled = true;
              submitBtn.setAttribute("aria-busy", "true");
              submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
            }
            _context2.prev = 4;
            postObj = {
              mobile: "",
              viewPath: "msg/contactNewMember",
              data: {
                email: recipientEmail,
                mobile: "",
                name: recipientName,
                familyCode: famCode,
                yourName: yourName || "A family member"
              },
              familyCode: famCode,
              subject: "".concat(yourName || 'A family member', " Wants You: Experience the Magic of your Family Network Today!")
            };
            _context2.next = 5;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].post("/register/contactNewMember", postObj, {
              headers: {
                "X-CSRF-TOKEN": (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)(),
                "X-XSRF-TOKEN": (0,_global__WEBPACK_IMPORTED_MODULE_3__.getCsrfToken)()
              }
            });
          case 5:
            response = _context2.sent;
            status = (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.status;
            message = (_response$data2 = response.data) === null || _response$data2 === void 0 ? void 0 : _response$data2.message;
            if (!(status === "success")) {
              _context2.next = 9;
              break;
            }
            swalLib = window.Swal || (sweetalert2__WEBPACK_IMPORTED_MODULE_5___default());
            if (!(swalLib !== null && swalLib !== void 0 && swalLib.fire)) {
              _context2.next = 7;
              break;
            }
            _context2.next = 6;
            return swalLib.fire({
              icon: "success",
              title: "Invitation Dispatched!",
              html: "<div class=\"text-start small text-muted\">\n                       <p class=\"mb-2\">A personalised invitation has been sent to <strong>".concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(recipientName), "</strong> at <code>").concat((0,_global__WEBPACK_IMPORTED_MODULE_3__.esc)(recipientEmail), "</code>.</p>\n                       <p class=\"mb-0\">They will receive a secure cryptographic registration link to join your family space.</p>\n                     </div>"),
              confirmButtonColor: "#a31621",
              confirmButtonText: "Return to Directory",
              customClass: {
                confirmButton: "btn btn-primary px-4 py-2"
              }
            });
          case 6:
            _context2.next = 8;
            break;
          case 7:
            if (window.swalSuccess) {
              window.swalSuccess("Invitation dispatched to ".concat(recipientName, "!"));
            } else {
              alert("Invitation dispatched to ".concat(recipientName, "!"));
            }
          case 8:
            // Clear search input and restore full directory
            searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("searchFamily");
            if (searchInput) searchInput.value = "";
            if (typeof onResetView === "function") {
              onResetView();
            }
            _context2.next = 10;
            break;
          case 9:
            errMsg = message || "Unable to send invitation. Please verify the email address and try again.";
            _swalLib = window.Swal || (sweetalert2__WEBPACK_IMPORTED_MODULE_5___default());
            if (_swalLib !== null && _swalLib !== void 0 && _swalLib.fire) {
              _swalLib.fire({
                icon: "error",
                title: "Invitation Not Sent",
                text: errMsg,
                confirmButtonColor: "#a31621"
              });
            } else if (window.swalError) {
              window.swalError(errMsg);
            } else {
              alert(errMsg);
            }
          case 10:
            _context2.next = 12;
            break;
          case 11:
            _context2.prev = 11;
            _t3 = _context2["catch"](4);
            _errMsg = ((_error$response = _t3.response) === null || _error$response === void 0 ? void 0 : (_error$response$data = _error$response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message) || _t3.message || "Failed to deliver invitation. Please try again.";
            _swalLib2 = window.Swal || (sweetalert2__WEBPACK_IMPORTED_MODULE_5___default());
            if (_swalLib2 !== null && _swalLib2 !== void 0 && _swalLib2.fire) {
              _swalLib2.fire({
                icon: "error",
                title: "Delivery Error",
                text: _errMsg,
                confirmButtonColor: "#a31621"
              });
            } else if (window.swalError) {
              window.swalError(_errMsg);
            } else {
              alert(_errMsg);
            }
          case 12:
            _context2.prev = 12;
            // Universal Button Spinner Stop
            if ((_window$ScoreTenantSp2 = window.ScoreTenantSpinner) !== null && _window$ScoreTenantSp2 !== void 0 && _window$ScoreTenantSp2.stop) {
              window.ScoreTenantSpinner.stop(submitBtn);
            } else {
              submitBtn.disabled = false;
              submitBtn.removeAttribute("aria-busy");
              submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Invitation';
            }
            return _context2.finish(12);
          case 13:
          case "end":
            return _context2.stop();
        }
      }, _callee2, null, [[4, 11, 12, 13]]);
    })));
  }
};

/**
 * Factory that returns a debounced search handler using both client-side loaded members
 * and backend /allMembers/search endpoint for complete directory discovery.
 *
 * @param {object} options
 * @param {Array<object>} options.familyMembers
 * @param {Array<object>} options.directoryMembers
 * @param {Array<object>} options.allLoadedMembers
 * @param {Function} options.renderMembers
 * @param {HTMLElement} options.container
 * @param {string} options.searchUrl
 * @param {Function} [options.onResetView]
 * @returns {() => void}
 */
var createSearchHandler = function createSearchHandler(_ref3) {
  var _ref3$familyMembers = _ref3.familyMembers,
    familyMembers = _ref3$familyMembers === void 0 ? [] : _ref3$familyMembers,
    _ref3$directoryMember = _ref3.directoryMembers,
    directoryMembers = _ref3$directoryMember === void 0 ? [] : _ref3$directoryMember,
    _ref3$allLoadedMember = _ref3.allLoadedMembers,
    allLoadedMembers = _ref3$allLoadedMember === void 0 ? [] : _ref3$allLoadedMember,
    renderMembers = _ref3.renderMembers,
    container = _ref3.container,
    searchUrl = _ref3.searchUrl,
    onResetView = _ref3.onResetView;
  var searchInput = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("searchFamily");
  var debounceTimer = null;
  var performLocalFilter = function performLocalFilter(rawQuery) {
    var q = rawQuery.toLowerCase();
    var cleanDigits = q.replace(/[^0-9]/g, "");
    return (allLoadedMembers || []).filter(function (m) {
      var fName = (m.firstName || "").toLowerCase();
      var lName = (m.lastName || "").toLowerCase();
      var fullName = "".concat(fName, " ").concat(lName).trim();
      var fullNameRev = "".concat(lName, " ").concat(fName).trim();
      var email = (m.email || "").toLowerCase();
      var mobile = (m.mobile || "").toLowerCase();
      var mobileDigits = mobile.replace(/[^0-9]/g, "");
      var famCode = m.relationType === "family" ? (m.famCode || "").toLowerCase() : "";
      var country = (m.country || "").toLowerCase();
      return fName.includes(q) || lName.includes(q) || fullName.includes(q) || fullNameRev.includes(q) || email.includes(q) || mobile.includes(q) || cleanDigits.length >= 3 && mobileDigits.includes(cleanDigits) || famCode.includes(q) || country.includes(q);
    });
  };
  var performSearch = /*#__PURE__*/function () {
    var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().mark(function _callee3() {
      var rawQuery, pillsContainer, memberCountBadge, memberCountDisplay, resetPillsToAll, localMatches, response, data, matches, swalLib, _t4;
      return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_1___default().wrap(function (_context3) {
        while (1) switch (_context3.prev = _context3.next) {
          case 0:
            rawQuery = searchInput.value.trim();
            pillsContainer = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("memberFilterPills");
            memberCountBadge = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("memberCount");
            memberCountDisplay = (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.id)("memberCountDisplay");
            resetPillsToAll = function resetPillsToAll() {
              if (pillsContainer) {
                pillsContainer.querySelectorAll(".filter-pill-btn").forEach(function (b) {
                  return b.classList.remove("active");
                });
                var allBtn = pillsContainer.querySelector('[data-filter="all"]');
                if (allBtn) allBtn.classList.add("active");
              }
            }; // empty query = back to original view
            if (rawQuery) {
              _context3.next = 1;
              break;
            }
            if (typeof onResetView === "function") {
              onResetView();
            } else {
              resetPillsToAll();
              renderMembers(familyMembers);
            }
            return _context3.abrupt("return");
          case 1:
            // 1. Instant client-side matches across all loaded directory & family members
            localMatches = performLocalFilter(rawQuery);
            if (localMatches.length > 0) {
              resetPillsToAll();
              renderMembers(localMatches);
            }

            // 2. Server-side debounced search for full platform directory coverage
            _context3.prev = 2;
            _context3.next = 3;
            return axios__WEBPACK_IMPORTED_MODULE_4__["default"].get(searchUrl, {
              params: {
                q: rawQuery
              }
            });
          case 3:
            response = _context3.sent;
            data = response.data || {};
            matches = data.message || [];
            if (!(!matches.length && localMatches.length === 0)) {
              _context3.next = 4;
              break;
            }
            if (memberCountBadge) memberCountBadge.textContent = "0";
            if (memberCountDisplay) memberCountDisplay.textContent = "0";
            renderInviteBlock(container, rawQuery, onResetView);
            return _context3.abrupt("return");
          case 4:
            if (matches.length > 0) {
              resetPillsToAll();
              renderMembers(matches);
            }
            _context3.next = 6;
            break;
          case 5:
            _context3.prev = 5;
            _t4 = _context3["catch"](2);
            if (localMatches.length === 0) {
              swalLib = window.Swal || (sweetalert2__WEBPACK_IMPORTED_MODULE_5___default());
              if (swalLib !== null && swalLib !== void 0 && swalLib.fire) {
                swalLib.fire({
                  icon: "warning",
                  title: "Directory Search Interrupted",
                  text: "Search failed. Please check your internet connection and try again.",
                  confirmButtonColor: "#a31621"
                });
              } else if (window.swalError) {
                window.swalError("Search query failed. Please try again.");
              } else {
                (0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.showNotification)("allMembers", "is-danger", "Search failed. Please try again.");
              }
              ;(0,_modernman00_shared_js_lib__WEBPACK_IMPORTED_MODULE_2__.msgException)(_t4);
            }
          case 6:
          case "end":
            return _context3.stop();
        }
      }, _callee3, null, [[2, 5]]);
    }));
    return function performSearch() {
      return _ref4.apply(this, arguments);
    };
  }();
  return function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(performSearch, 150);
  };
};

/***/ }),

/***/ "./resources/asset/js/components/allMembers/html.js":
/*!**********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/html.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderHtml: function() { return /* binding */ renderHtml; }
/* harmony export */ });
/* harmony import */ var timeago_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! timeago.js */ "./node_modules/timeago.js/esm/index.js");
/* harmony import */ var _shared__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @shared */ "./node_modules/@modernman00/shared-js-lib/index.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../global */ "./resources/asset/js/components/global.js");



var toSentenceCase = function toSentenceCase(str) {
  if (!str || typeof str !== 'string') return '';
  return str.toLowerCase().split(' ').map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
  }).join(' ');
};
var renderHtml = function renderHtml(el) {
  try {
    var _el$status;
    if (!el) {
      throw new Error('there is no data');
    }
    var theImg = el.img ? "/resources/images/profile/".concat(el.img) : '/resources/images/profile/avatarM.png';
    var status = ((_el$status = el.status) === null || _el$status === void 0 ? void 0 : _el$status.toLowerCase()) || null;
    var statusButtonHTML = 'Connect';
    var tooltip = status === 'rejected' ? 'You can send another request' : 'Send connection request';
    if (status === 'request sent') {
      statusButtonHTML = 'Request Sent';
    }
    var disableButton = status === 'request sent' ? 'disabled' : '';

    // Determine Relation Category & Human-Friendly Status Chip
    var relType = el.relationType || 'other';
    var roleChipHtml = '';
    var categoryKey = 'explore';
    if (relType === 'family') {
      categoryKey = 'family';
      roleChipHtml = "<span class=\"role-chip role-chip-family\"><i class=\"bi bi-house-heart-fill\"></i> Same Family</span>";
    } else if (relType === 'approved_you' || relType === 'you_approved') {
      categoryKey = 'connected';
      var roleDetail = el.relationship_role ? " (".concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.relationship_role), ")") : '';
      roleChipHtml = "<span class=\"role-chip role-chip-connected\"><i class=\"bi bi-check2-circle\"></i> Connected Kin".concat(roleDetail, "</span>");
    } else {
      categoryKey = 'explore';
      roleChipHtml = "<span class=\"role-chip role-chip-directory\"><i class=\"bi bi-globe2\"></i> Directory</span>";
    }

    // el.* fields are user-authored — escape every one before it hits innerHTML (SEC-2).
    var idSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.id);
    var firstName = toSentenceCase(el.firstName || '');
    var lastName = toSentenceCase(el.lastName || '');
    var fullNameRaw = "".concat(firstName, " ").concat(lastName).trim();
    var fullName = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(fullNameRaw);
    var firstNameSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(firstName || fullNameRaw || 'Relative');
    var location = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.country ? toSentenceCase(el.country) : 'Location not set');
    var showFamCode = relType === 'family' && Boolean(el.famCode);
    var famCode = showFamCode ? (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(String(el.famCode).trim().toUpperCase()) : '';
    var emailSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(el.email || 'Email not provided');
    var imgSafe = (0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(theImg);
    var migrationButtonsHtml = "\n                <button class=\"btn-stitch-tonal btn-nudge-family w-100 mt-2\" \n                        id=\"nudgeMigrate".concat(idSafe, "\" \n                        data-target-id=\"").concat(idSafe, "\" \n                        data-target-name=\"").concat(fullName, "\" \n                        data-target-first-name=\"").concat(firstNameSafe, "\" \n                        style=\"white-space: nowrap; flex-shrink: 0;\"\n                        title=\"Invite relative to migrate to your family network\">\n                    <i class=\"bi bi-send-check text-primary me-1\" style=\"pointer-events: none;\"></i> Nudge to Join Your Family Code\n                </button>\n                <button class=\"btn-stitch-tonal btn-request-migration w-100 mt-1\" \n                        id=\"requestMigrate").concat(idSafe, "\" \n                        data-target-id=\"").concat(idSafe, "\" \n                        data-target-name=\"").concat(fullName, "\" \n                        data-target-first-name=\"").concat(firstNameSafe, "\" \n                        style=\"font-size: 0.82rem; opacity: 0.85; white-space: nowrap; flex-shrink: 0;\"\n                        title=\"Request to join ").concat(firstNameSafe, "'s family network\">\n                    <i class=\"bi bi-box-arrow-in-right me-1\" style=\"pointer-events: none;\"></i> Join ").concat(firstNameSafe, " famcode\n                </button>\n    ");
    var html = "\n    <div class=\"member-card member_profile_".concat(idSafe, "\" id=\"").concat(idSafe, "\" data-category=\"").concat(categoryKey, "\" data-search=\"").concat((0,_global__WEBPACK_IMPORTED_MODULE_2__.esc)(fullNameRaw.toLowerCase() + ' ' + (el.email || '')), "\">\n        <div class=\"card-cover\"></div>\n\n        <div class=\"avatar-wrapper\">\n             <img src=\"").concat(imgSafe, "\" alt=\"").concat(fullName, "\" loading=\"lazy\">\n             <span class=\"avatar-online-dot\"></span>\n        </div>\n\n        <div class=\"member-card-body\">\n            ").concat(roleChipHtml, "\n            <h4 class=\"member-name text-truncate\" title=\"").concat(fullName, "\">").concat(fullName, "</h4>\n\n            <span class=\"member-location-chip\">\n                <i class=\"bi bi-geo-alt-fill text-danger\"></i> ").concat(location, "\n            </span>\n\n            <div class=\"member-details\">\n                ").concat(showFamCode ? "\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-hash\"></i>\n                    <span class=\"fw-bold\">".concat(famCode, "</span>\n                </div>\n                ") : '', "\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-envelope\"></i>\n                    <span class=\"text-truncate\">").concat(emailSafe, "</span>\n                </div>\n                ").concat(relType !== 'other' ? "\n                <div class=\"member-detail\">\n                    <i class=\"bi bi-calendar-check\"></i> \n                    <span>Connected ".concat((0,timeago_js__WEBPACK_IMPORTED_MODULE_0__.format)(el.created_at), "</span>\n                </div>\n                ") : '', "\n            </div>\n\n            <div class=\"member-interests\">\n                ").concat(relType === 'family' ? "\n                <button class=\"btn-stitch-primary\" id=\"seeProfile".concat(idSafe, "\">\n                    <i class=\"bi bi-person-badge-fill\" style=\"pointer-events: none;\"></i> View Profile\n                </button>\n                <div class=\"d-flex gap-2\">\n                    <button class=\"btn-stitch-tonal flex-grow-1\" id=\"familyTree").concat(idSafe, "\" title=\"View in Family Tree\">\n                        <i class=\"bi bi-diagram-3-fill\" style=\"pointer-events: none;\"></i> Tree\n                    </button>\n                    <button class=\"btn-stitch-danger flex-grow-1\" id=\"removeProfile").concat(idSafe, "\" title=\"Remove from Family\">\n                        <i class=\"bi bi-person-dash-fill\" style=\"pointer-events: none;\"></i> Remove\n                    </button>\n                </div>\n                ") : relType === 'approved_you' || relType === 'you_approved' ? "\n                <button class=\"btn-stitch-primary\" id=\"seeProfile".concat(idSafe, "\">\n                    <i class=\"bi bi-person-badge-fill\" style=\"pointer-events: none;\"></i> View Profile\n                </button>\n                <button class=\"btn-stitch-danger w-100\" id=\"removeProfile").concat(idSafe, "\" title=\"Remove Connection\" style=\"white-space: nowrap; flex-shrink: 0;\">\n                    <i class=\"bi bi-person-dash-fill\" style=\"pointer-events: none;\"></i> Remove\n                </button>\n                ").concat(migrationButtonsHtml, "\n                ") : "\n                <button class=\"btn-stitch-connect w-100\" \n                        data-user-id=\"addFamily".concat(idSafe, "\" \n                        id=\"addFamily").concat(idSafe, "\"\n                        data-target-id=\"").concat(idSafe, "\"\n                        data-target-name=\"").concat(fullName, "\"\n                        data-target-img=\"").concat(imgSafe, "\"\n                        ").concat(disableButton, ">\n                    <i class=\"bi bi-person-plus-fill\" style=\"pointer-events: none;\"></i> ").concat(statusButtonHTML, "\n                </button>\n                ").concat(migrationButtonsHtml, "\n                <small class=\"text-muted text-center mt-1 d-block\" style=\"font-size: 0.72rem; font-weight: 500;\">").concat(tooltip, "</small>\n                "), "\n            </div>\n        </div>\n    </div>\n    ");
    var container = (0,_shared__WEBPACK_IMPORTED_MODULE_1__.id)('allMembers');
    if (container) {
      container.insertAdjacentHTML('beforeend', html);
    }
  } catch (error) {
    ;(0,_shared__WEBPACK_IMPORTED_MODULE_1__.showError)(error);
  }
};

/***/ }),

/***/ "./resources/asset/js/components/allMembers/index.js":
/*!***********************************************************!*\
  !*** ./resources/asset/js/components/allMembers/index.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./api */ "./resources/asset/js/components/allMembers/api.js");
/* harmony import */ var _allEvents__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./allEvents */ "./resources/asset/js/components/allMembers/allEvents.js");



/***/ }),

/***/ "./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js":
/*!***********************************************************************************!*\
  !*** ./resources/asset/js/components/profilePage/htmlFolder/friendRequestCard.js ***!
  \***********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   friendRequestCard: function() { return /* binding */ friendRequestCard; }
/* harmony export */ });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/components/global.js");

var appUrl = "https://olaogun.test/";
var approverId = encodeURIComponent(localStorage.getItem('requesterId'));
var friendRequestCard = function friendRequestCard(data) {
  var _data$img, _data$firstName, _data$lastName, _data$id, _data$famCode;
  var imageUrl = "/resources/images/profile/".concat(encodeURIComponent((_data$img = data.img) !== null && _data$img !== void 0 ? _data$img : data.requesterProfileImg));
  var firstName = encodeURIComponent((_data$firstName = data.firstName) !== null && _data$firstName !== void 0 ? _data$firstName : data.requesterFirstName);
  var lastName = encodeURIComponent((_data$lastName = data.lastName) !== null && _data$lastName !== void 0 ? _data$lastName : data.requesterLastName);
  var requestId = encodeURIComponent((_data$id = data.id) !== null && _data$id !== void 0 ? _data$id : data.requesterId);
  var requestCode = encodeURIComponent((_data$famCode = data.famCode) !== null && _data$famCode !== void 0 ? _data$famCode : data.requesterFamCode);
  var html = "<p id=".concat(requestId, "_linkRequestCard></p>\n\n\n  <img src=\"").concat(imageUrl, "\" alt=\"Avatar\" style=\"width:50%\"><br>\n\n   <span>").concat(firstName, " ").concat(lastName, "</span>\n\n\n    <div class=\"w3-row w3-opacity\">\n      <div class=\"w3-half\">\n        <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/50/").concat(requestCode, "/pp\" style=\"text-decoration: none;\">\n          <button class=\"w3-button w3-block w3-green w3-section\" title=\"Accept\"><i class=\"fa fa-check\"></i></button>\n        </a>\n      </div>\n      \n      <div class=\"w3-half\">\n        <a href=\"").concat(appUrl, "member/request/").concat(requestId, "/").concat(approverId, "/10\" style=\"text-decoration: none;\">\n          <button class=\"w3-button w3-block w3-red w3-section\" title=\"Decline\"><i class=\"fa fa-remove\"></i></button>\n        </a>\n      </div>\n    </div>\n  ");
  (0,_global__WEBPACK_IMPORTED_MODULE_0__.qSel)('.requestFriendClass').insertAdjacentHTML('afterbegin', html);
};

/***/ })

}]);
//# sourceMappingURL=all_members.js.map?v=055819d8