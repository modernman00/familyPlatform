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

/***/ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./node_modules/file-upload-with-preview/dist/file-upload-with-preview.min.css":
/*!*********************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/postcss-loader/src??ref--6-2!./node_modules/file-upload-with-preview/dist/file-upload-with-preview.min.css ***!
  \*********************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, ".custom-file-container {\n  box-sizing: border-box;\n  position: relative;\n  display: block; }\n  .custom-file-container__custom-file {\n    box-sizing: border-box;\n    position: relative;\n    display: inline-block;\n    width: 100%;\n    height: calc(2.25rem + 2px);\n    margin-bottom: 0;\n    margin-top: 5px; }\n    .custom-file-container__custom-file:hover {\n      cursor: pointer; }\n    .custom-file-container__custom-file__custom-file-input {\n      box-sizing: border-box;\n      min-width: 14rem;\n      max-width: 100%;\n      height: calc(2.25rem + 2px);\n      margin: 0;\n      opacity: 0; }\n      .custom-file-container__custom-file__custom-file-input:focus ~ span {\n        outline: 1px dotted #212121;\n        outline: 5px auto -webkit-focus-ring-color; }\n    .custom-file-container__custom-file__custom-file-control {\n      box-sizing: border-box;\n      position: absolute;\n      top: 0;\n      right: 0;\n      left: 0;\n      z-index: 5;\n      height: calc(2.25rem + 2px);\n      padding: .5rem .75rem;\n      overflow: hidden;\n      line-height: 1.5;\n      color: #333;\n      -webkit-user-select: none;\n         -moz-user-select: none;\n          -ms-user-select: none;\n              user-select: none;\n      background-color: #fff;\n      background-clip: padding-box;\n      border: 1px solid #c0c0af;\n      border-radius: .25rem; }\n      .custom-file-container__custom-file__custom-file-control__button {\n        box-sizing: border-box;\n        position: absolute;\n        top: 0;\n        right: 0;\n        z-index: 6;\n        display: block;\n        height: calc(2.25rem + 2px);\n        padding: .5rem .75rem;\n        line-height: 1.25;\n        color: #333;\n        background-color: #EDEDE8;\n        border-left: 1px solid #c0c0af;\n        box-sizing: border-box; }\n  .custom-file-container__image-preview {\n    box-sizing: border-box;\n    transition: all 0.2s ease;\n    margin-top: 20px;\n    margin-bottom: 40px;\n    height: 250px;\n    width: 100%;\n    border-radius: 4px;\n    background-size: cover;\n    background-position: center center;\n    background-repeat: no-repeat;\n    background-color: #fff;\n    overflow: scroll; }\n  .custom-file-container__image-multi-preview {\n    position: relative;\n    box-sizing: border-box;\n    transition: all 0.2s ease;\n    border-radius: 6px;\n    background-size: cover;\n    background-position: center center;\n    background-repeat: no-repeat;\n    float: left;\n    margin: 1.858736059%;\n    width: 29.615861214%;\n    height: 90px;\n    box-shadow: 0 4px 10px 0 rgba(51, 51, 51, 0.25); }\n    .custom-file-container__image-multi-preview__single-image-clear {\n      left: -6px;\n      background: #EDEDE8;\n      position: absolute;\n      width: 20px;\n      height: 20px;\n      border-radius: 50%;\n      text-align: center;\n      margin-top: -6px;\n      box-shadow: 0 4px 10px 0 rgba(51, 51, 51, 0.25); }\n      .custom-file-container__image-multi-preview__single-image-clear:hover {\n        background: #cbcbbd;\n        cursor: pointer; }\n      .custom-file-container__image-multi-preview__single-image-clear__icon {\n        color: #6a6a53;\n        display: block;\n        margin-top: -2px; }\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/lib/css-base.js":
/*!*************************************************!*\
  !*** ./node_modules/css-loader/lib/css-base.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),

/***/ "./node_modules/file-upload-with-preview/dist/file-upload-with-preview.esm.js":
/*!************************************************************************************!*\
  !*** ./node_modules/file-upload-with-preview/dist/file-upload-with-preview.esm.js ***!
  \************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function createCommonjsModule(fn, module) {
	return module = { exports: {} }, fn(module, module.exports), module.exports;
}

var runtime_1 = createCommonjsModule(function (module) {
/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

var runtime = (function (exports) {

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var undefined$1; // More compressible than void 0.
  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  function define(obj, key, value) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
    return obj[key];
  }
  try {
    // IE 8 has a broken Object.defineProperty that only works on DOM objects.
    define({}, "");
  } catch (err) {
    define = function(obj, key, value) {
      return obj[key] = value;
    };
  }

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []);

    // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.
    generator._invoke = makeInvokeMethod(innerFn, self, context);

    return generator;
  }
  exports.wrap = wrap;

  // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.
  function tryCatch(fn, obj, arg) {
    try {
      return { type: "normal", arg: fn.call(obj, arg) };
    } catch (err) {
      return { type: "throw", arg: err };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed";

  // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.
  var ContinueSentinel = {};

  // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}

  // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.
  var IteratorPrototype = {};
  IteratorPrototype[iteratorSymbol] = function () {
    return this;
  };

  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
  if (NativeIteratorPrototype &&
      NativeIteratorPrototype !== Op &&
      hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype =
    Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
  GeneratorFunctionPrototype.constructor = GeneratorFunction;
  GeneratorFunction.displayName = define(
    GeneratorFunctionPrototype,
    toStringTagSymbol,
    "GeneratorFunction"
  );

  // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.
  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function(method) {
      define(prototype, method, function(arg) {
        return this._invoke(method, arg);
      });
    });
  }

  exports.isGeneratorFunction = function(genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor
      ? ctor === GeneratorFunction ||
        // For the native GeneratorFunction constructor, the best we can
        // do is to check its .name property.
        (ctor.displayName || ctor.name) === "GeneratorFunction"
      : false;
  };

  exports.mark = function(genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      define(genFun, toStringTagSymbol, "GeneratorFunction");
    }
    genFun.prototype = Object.create(Gp);
    return genFun;
  };

  // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.
  exports.awrap = function(arg) {
    return { __await: arg };
  };

  function AsyncIterator(generator, PromiseImpl) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);
      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;
        if (value &&
            typeof value === "object" &&
            hasOwn.call(value, "__await")) {
          return PromiseImpl.resolve(value.__await).then(function(value) {
            invoke("next", value, resolve, reject);
          }, function(err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return PromiseImpl.resolve(value).then(function(unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration.
          result.value = unwrapped;
          resolve(result);
        }, function(error) {
          // If a rejected Promise was yielded, throw the rejection back
          // into the async generator function so it can be handled there.
          return invoke("throw", error, resolve, reject);
        });
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new PromiseImpl(function(resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise =
        // If enqueue has been called before, then we want to wait until
        // all previous Promises have been resolved before calling invoke,
        // so that results are always delivered in the correct order. If
        // enqueue has not been called before, then it is important to
        // call invoke immediately, without waiting on a callback to fire,
        // so that the async generator function has the opportunity to do
        // any necessary setup in a predictable way. This predictability
        // is why the Promise constructor synchronously invokes its
        // executor callback, and why async functions synchronously
        // execute code before the first await. Since we implement simple
        // async functions in terms of async generators, it is especially
        // important to get this right, even though it requires care.
        previousPromise ? previousPromise.then(
          callInvokeWithMethodAndArg,
          // Avoid propagating failures to Promises returned by later
          // invocations of the iterator.
          callInvokeWithMethodAndArg
        ) : callInvokeWithMethodAndArg();
    }

    // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).
    this._invoke = enqueue;
  }

  defineIteratorMethods(AsyncIterator.prototype);
  AsyncIterator.prototype[asyncIteratorSymbol] = function () {
    return this;
  };
  exports.AsyncIterator = AsyncIterator;

  // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.
  exports.async = function(innerFn, outerFn, self, tryLocsList, PromiseImpl) {
    if (PromiseImpl === void 0) PromiseImpl = Promise;

    var iter = new AsyncIterator(
      wrap(innerFn, outerFn, self, tryLocsList),
      PromiseImpl
    );

    return exports.isGeneratorFunction(outerFn)
      ? iter // If outerFn is a generator, return the full iterator.
      : iter.next().then(function(result) {
          return result.done ? result.value : iter.next();
        });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;

    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        }

        // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;
        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);
          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;

        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);

        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;

        var record = tryCatch(innerFn, self, context);
        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done
            ? GenStateCompleted
            : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };

        } else if (record.type === "throw") {
          state = GenStateCompleted;
          // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.
          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  }

  // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.
  function maybeInvokeDelegate(delegate, context) {
    var method = delegate.iterator[context.method];
    if (method === undefined$1) {
      // A .throw or .return when the delegate iterator has no .throw
      // method always terminates the yield* loop.
      context.delegate = null;

      if (context.method === "throw") {
        // Note: ["return"] must be used for ES3 parsing compatibility.
        if (delegate.iterator["return"]) {
          // If the delegate iterator has a return method, give it a
          // chance to clean up.
          context.method = "return";
          context.arg = undefined$1;
          maybeInvokeDelegate(delegate, context);

          if (context.method === "throw") {
            // If maybeInvokeDelegate(context) changed context.method from
            // "return" to "throw", let that override the TypeError below.
            return ContinueSentinel;
          }
        }

        context.method = "throw";
        context.arg = new TypeError(
          "The iterator does not provide a 'throw' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (! info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value;

      // Resume execution at the desired location (see delegateYield).
      context.next = delegate.nextLoc;

      // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.
      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined$1;
      }

    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    }

    // The delegate iterator is finished, so forget it and continue with
    // the outer generator.
    context.delegate = null;
    return ContinueSentinel;
  }

  // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.
  defineIteratorMethods(Gp);

  define(Gp, toStringTagSymbol, "Generator");

  // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.
  Gp[iteratorSymbol] = function() {
    return this;
  };

  Gp.toString = function() {
    return "[object Generator]";
  };

  function pushTryEntry(locs) {
    var entry = { tryLoc: locs[0] };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{ tryLoc: "root" }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  exports.keys = function(object) {
    var keys = [];
    for (var key in object) {
      keys.push(key);
    }
    keys.reverse();

    // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.
    return function next() {
      while (keys.length) {
        var key = keys.pop();
        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      }

      // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.
      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];
      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1, next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined$1;
          next.done = true;

          return next;
        };

        return next.next = next;
      }
    }

    // Return an iterator with no values.
    return { next: doneResult };
  }
  exports.values = values;

  function doneResult() {
    return { value: undefined$1, done: true };
  }

  Context.prototype = {
    constructor: Context,

    reset: function(skipTempReset) {
      this.prev = 0;
      this.next = 0;
      // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.
      this.sent = this._sent = undefined$1;
      this.done = false;
      this.delegate = null;

      this.method = "next";
      this.arg = undefined$1;

      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" &&
              hasOwn.call(this, name) &&
              !isNaN(+name.slice(1))) {
            this[name] = undefined$1;
          }
        }
      }
    },

    stop: function() {
      this.done = true;

      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;
      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },

    dispatchException: function(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;
      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined$1;
        }

        return !! caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }

          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },

    abrupt: function(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc <= this.prev &&
            hasOwn.call(entry, "finallyLoc") &&
            this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry &&
          (type === "break" ||
           type === "continue") &&
          finallyEntry.tryLoc <= arg &&
          arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },

    complete: function(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" ||
          record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },

    finish: function(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },

    "catch": function(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;
          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }
          return thrown;
        }
      }

      // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.
      throw new Error("illegal catch attempt");
    },

    delegateYield: function(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined$1;
      }

      return ContinueSentinel;
    }
  };

  // Regardless of whether this script is executing as a CommonJS module
  // or not, return the runtime object so that we can declare the variable
  // regeneratorRuntime in the outer scope, which allows this module to be
  // injected easily by `bin/regenerator --include-runtime script.js`.
  return exports;

}(
  // If this script is executing as a CommonJS module, use module.exports
  // as the regeneratorRuntime namespace. Otherwise create a new empty
  // object. Either way, the resulting object will be used to initialize
  // the regeneratorRuntime variable at the top of this file.
   module.exports 
));

try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  // This module should not be running in strict mode, so the above
  // assignment should always work unless something is misconfigured. Just
  // in case runtime.js accidentally runs in strict mode, we can escape
  // strict mode using a global Function call. This could conceivably fail
  // if a Content Security Policy forbids using Function, but in that case
  // the proper solution is to fix the accidental strict mode problem. If
  // you've misconfigured your bundler to force strict mode and applied a
  // CSP to forbid Function, and you're not willing to fix either of those
  // problems, please detail your unique predicament in a GitHub issue.
  Function("r", "regeneratorRuntime = r")(runtime);
}
});

var regenerator = runtime_1;

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

var asyncToGenerator = _asyncToGenerator;

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

var classCallCheck = _classCallCheck;

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

var createClass = _createClass;

/* eslint-disable */
// this is for matches in older ie browsers
if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.matchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.webkitMatchesSelector || function (s) {
    var matches = (this.document || this.ownerDocument).querySelectorAll(s),
        i = matches.length;

    while (--i >= 0 && matches.item(i) !== this) {}

    return i > -1;
  };
} // https://tc39.github.io/ecma262/#sec-array.prototype.findindex


if (!Array.prototype.findIndex) {
  Object.defineProperty(Array.prototype, 'findIndex', {
    value: function value(predicate) {
      // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this); // 2. Let len be ? ToLength(? Get(O, "length")).

      var len = o.length >>> 0; // 3. If IsCallable(predicate) is false, throw a TypeError exception.

      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
      } // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.


      var thisArg = arguments[1]; // 5. Let k be 0.

      var k = 0; // 6. Repeat, while k < len

      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return k.
        var kValue = o[k];

        if (predicate.call(thisArg, kValue, k, o)) {
          return k;
        } // e. Increase k by 1.


        k++;
      } // 7. Return -1.


      return -1;
    },
    configurable: true,
    writable: true
  });
} // this is for custom events in older ie browsers
// https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/CustomEvent


(function () {
  if (typeof window.CustomEvent === "function") return false;

  function CustomEvent(event, params) {
    params = params || {
      bubbles: false,
      cancelable: false,
      detail: null
    };
    var evt = document.createEvent('CustomEvent');
    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
    return evt;
  }

  CustomEvent.prototype = window.Event.prototype;
  window.CustomEvent = CustomEvent;
})();
/* eslint-enable */

var FileUploadWithPreview = /*#__PURE__*/function () {
  function FileUploadWithPreview(uploadId, options) {
    classCallCheck(this, FileUploadWithPreview);

    // Make sure uploadId was specified
    if (!uploadId) {
      throw new Error('No uploadId found. You must initialize file-upload-with-preview with a unique uploadId.');
    } // Set initial variables


    this.uploadId = uploadId;
    this.options = options || {};
    this.options.showDeleteButtonOnImages = this.options.hasOwnProperty('showDeleteButtonOnImages') ? this.options.showDeleteButtonOnImages : true;
    this.options.text = this.options.hasOwnProperty('text') ? this.options.text : {
      chooseFile: 'Choose file...'
    };
    this.options.text.chooseFile = this.options.text.hasOwnProperty('chooseFile') ? this.options.text.chooseFile : 'Choose file...';
    this.options.text.browse = this.options.text.hasOwnProperty('browse') ? this.options.text.browse : 'Browse';
    this.options.text.selectedCount = this.options.text.hasOwnProperty('selectedCount') ? this.options.text.selectedCount : 'files selected';
    this.options.maxFileCount = this.options.hasOwnProperty('maxFileCount') ? this.options.maxFileCount : 0;
    this.cachedFileArray = [];
    this.currentFileCount = 0; // Grab the custom file container elements

    this.el = document.querySelector(".custom-file-container[data-upload-id=\"".concat(this.uploadId, "\"]"));

    if (!this.el) {
      throw new Error("Could not find a 'custom-file-container' with the id of: ".concat(this.uploadId));
    }

    this.input = this.el.querySelector('input[type="file"]');
    this.inputLabel = this.el.querySelector('.custom-file-container__custom-file__custom-file-control');
    this.imagePreview = this.el.querySelector('.custom-file-container__image-preview');
    this.clearButton = this.el.querySelector('.custom-file-container__image-clear');
    this.inputLabel.innerHTML = this.options.text.chooseFile;
    this.addBrowseButton(this.options.text.browse); // Make sure all elements have been attached

    if (!this.el || !this.input || !this.inputLabel || !this.imagePreview || !this.clearButton) {
      throw new Error("Cannot find all necessary elements. Please make sure you have all the necessary elements in your html for the id: ".concat(this.uploadId));
    } // Check if images option is set


    this.options.images = this.options.hasOwnProperty('images') ? this.options.images : {}; // Set the base64 background images

    this.baseImage = this.options.images.hasOwnProperty('baseImage') ? this.options.images.baseImage : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAiQAAAD6CAMAAACmhqw0AAAA+VBMVEUAAAD29u3u7unt7ent7enu7uju7uihoqCio6Gio6KjpKOkpaSmpqSmp6WoqKaqq6mqq6qrq6qsrautrauur62wsa6xsa+xsrCys7GztLK0tbK1trS2t7S3t7W4uba5ure6u7e7vLm8vbu9vrvAwL3Awb3DxMHFxcPGxsPHx8TIycXLzMjLzMnMzMnNzsrPz8vP0MzQ0M3S0s/U1NDV1dLX19TY2NTY2NXZ2dba2tXb29bc3Nfc3Njc3dnd3dre3tre39vg4Nvh4dzi4t3i4t7j497k5N/k5ODl5eDl5eHl5uLm5uHn5+Lo6OPp6eTq6uXr6+bs7Oft7eh54KxIAAAAB3RSTlMAHKbl5uztvql9swAABA1JREFUeNrt3VlT01AYgOG0oEEE910URNzFBVFcqCgKirLU/P8fI3QYbEOSdtrMyJzzvHfMlFx833NBQuY0SRrN8UwqabzZSJLGaYNQVacaSdMUVF0zGTMEVTeWmIH6BYkgESSCRJAIEkEiSCRIBIkgESSCRJAIEkEiQSJIBIkgESSCRJAIEgkSQSJIBIkgESSCRJBIkAgSQSJIBIkgESSCRIJEkAgSQSJIBIkgkSARJIJEkAgSQSJIBIkEiSARJIJEkAgSQSJIJEgEiSARJIJEkAgSQSJBIkgEiSARJIJEkAgSCRJBIkgEiSARJIJEgkSQ5PvxbdS+tyEJuZVb0+noTV579geSQGs/SOvqxiYkYfYwra+rbUhC7NNEjUjSJ5CE2P06jaTnIAmxKwe7vb468t3N14WOki1IAuzMwWrf1HCh3Q6S95AEWGe1b0/WlSCBBBJIIAkdSXvt1aNXa21IICld7dJU5+epJUggKV7tzuzRA4/ZHUggKVrtfNdjsXlIIClY7XLPw9NlSCA5vtqLPUguQgLJsdX+zv0fZhsSSPKrXckhWSn5jV8zG5DEiuR1DsnrEiOX0vMbkESKZDWHZLXMSFqsBJIIkOz1vn40sVdqpFgJJDHc3dzsQXKzwkihEkhiQLI+2f3y+3qVkSIlkMSAJFvsQrJYbaRACSRRIMlenj0UcPZlPyPHlUASB5Jsc+7cwevMc5v9jRxTAkkkSPbb+riVZYMYySuBJB4kJRUYySmBJHYkhUZ6lUASOZISIz1KIIkbSamRbiWQxIZkvT2YkS4lkESGpDV9tz2YkX9KIIkLSWs6TY+U9DFypASSqJC0OicfHSrpa2T/k5BEh6R1eDpWR8kARtIZSGJD0jo6QW1fySBGIIkOSavrlL27PwcxAklsSFo9JzFOppBAkl9ta5jTOiGJCslQRiCJCslwRiCJCcmQRiCJCMmwRiCJB8mXoU+YhyQaJM9TSCCBBBJIIIEEEkgggQQSSCCJAsnyzLA9hiQWJCfnSpBAAgkkkATXxFCnPxfU7iB5B0mAXT5Y7Z3t0Y087SDZgCTA7tX6bZ5TGSQBtlwrkgVIgmy+RiMXdiEJsp3b9Rn5nEESaC/O1/P3yMJuBkm4bX94O2rvNiKbWXRIBIkgESSCRJAIEkEiQSJIBIkgESSCRJAIEgkSQSJIBIkgESSCRIJEkAgSQSJIBIkgESQSJIJEkAgSQSJIBIkgkSARJIJEkAgSQSJIBIkEiSARJIJEkAgSQSJIJEgEiSARJIJEkAgSCRJBIkgEiSARJIJEkEiQCBJBIkgEiSARJIJEgkSQCBJBIkgEiSARJBIkgkSQ6P8gGTMDVTeWNA1B1TWTxmlTUFWnGknSaI4bhMoabzaSv+4BHFVoHZzfAAAAAElFTkSuQmCC';
    this.successPdfImage = this.options.images.hasOwnProperty('successPdfImage') ? this.options.images.successPdfImage : 'data:image/png;base64,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';
    this.successVideoImage = this.options.images.hasOwnProperty('successVideoImage') ? this.options.images.successVideoImage : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAD6CAYAAABXq7VOAAAABGdBTUEAALGPC/xhBQAAEpNJREFUeAHt3UtsXOd1B/BvJPFlWaRIPWxZcQLHUSorsEwLltMEMRzYQJBsvEi66C4IkFW7aldZpkHQbRdFu6nX3dVAgGyCAOkiruvIcfwCEtlyJAe2RSUUORJpmZRIccIrWX6QQ3Ie987cOfc3G0szc7/vO79zjL9mho/a3NylRnIjQIAAAQIEBlpg10Cf3uEJECBAgACBWwIC3SAQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC3QwQIECAAIEAAgI9QBOVQIAAAQIEBLoZIECAAAECAQQEeoAmKoEAAQIECAh0M0CAAAECBAIICPQATVQCAQIECBAQ6GaAAAECBAgEEBDoAZqoBAIECBAgINDNAAECBAgQCCAg0AM0UQkECBAgQECgmwECBAgQIBBAQKAHaKISCBAgQICAQDcDBAgQIEAggIBAD9BEJRAgQIAAAYFuBggQIECAQAABgR6giUogQIAAAQIC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';
    this.successFileAltImage = this.options.images.hasOwnProperty('successFileAltImage') ? this.options.images.successFileAltImage : 'data:image/png;base64,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';
    this.backgroundImage = this.options.images.hasOwnProperty('backgroundImage') ? this.options.images.backgroundImage : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAiQAAAD6CAYAAACRWFwGAAAABGdBTUEAALGPC/xhBQAADFNJREFUeAHt2jFOG1EUQNH5VpBGLnEVxB7CDpJVZRHZSLaRLIE9IFy5tSwRaTKjpEOU1i18XFmA7OfzXnEFjGl9nM+nx8vlz49lmb5N0/J5+5oHAQIECBAgQOB6AuM4xvRrnj993+8PL+NfjLw9rzFyf7039coECBAgQIAAgfcCa5Sc5vnuaff/NyNi5L2RrxAgQIAAAQJXFlh/IXLYWmS3Pvl65ffy8gQIECBAgACBDwW2Ftmt/zPy8OFP+AYBAgQIECBA4OoCy8MaJB4ECBAgQIAAgVZAkLT+3p0AAQIECBBYBQSJMyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQCwiSfAUGIECAAAECBASJGyBAgAABAgRyAUGSr8AABAgQIECAgCBxAwQIECBAgEAuIEjyFRiAAAECBAgQECRugAABAgQIEMgFBEm+AgMQIECAAAECgsQNECBAgAABArmAIMlXYAACBAgQIEBAkLgBAgQIECBAIBcQJPkKDECAAAECBAgIEjdAgAABAgQI5AKCJF+BAQgQIECAAAFB4gYIECBAgACBXECQ5CswAAECBAgQICBI3AABAgQIECCQC6xBMl7zKQxAgAABAgQI3LDAeN2NMf2+YQEfnQABAgQIEIgFthYZ5/Pp8XJ5e16W6T6ex9sTIECAAAECNyawxshpnu+edvv94WV98mWM8XP9883xxhx8XAIECBAgQCARGMetPbYY2VrkL3ZQPayX+qtWAAAAAElFTkSuQmCC'; // Set click events

    this.bindClickEvents(); // Let's set the placeholder image

    this.imagePreview.style.backgroundImage = "url(\"".concat(this.baseImage, "\")"); // Check for any preset files and process these if provided

    this.options.presetFiles = this.options.hasOwnProperty('presetFiles') ? this.options.presetFiles : null;

    if (this.options.presetFiles) {
      // Using thenable promises because we're in the constructor
      this.addImagesFromPath(this.options.presetFiles).then(function () {}).catch(function (error) {
        console.log('Error - ' + error.toString());
        console.log('Warning - An image you added from a path is not able to be added to the cachedFileArray.');
      });
    }
  }

  createClass(FileUploadWithPreview, [{
    key: "bindClickEvents",
    value: function bindClickEvents() {
      var _this = this;

      // Grab the current instance
      var self = this; // Deal with the change event on the input

      self.input.addEventListener('change', function () {
        self.addFiles(this.files);
      }, true); // Listen for the clear button

      this.clearButton.addEventListener('click', function () {
        _this.clearPreviewPanel();
      }, true); // Listen for individual clear buttons on images

      this.imagePreview.addEventListener('click', function (event) {
        // Listen for the specific click of a clear button
        if (event.target.matches('.custom-file-container__image-multi-preview__single-image-clear__icon')) {
          // Grab the clicked function
          var clearFileButton = event.target; // Get its token

          var fileToken = clearFileButton.getAttribute('data-upload-token'); // Get the index of the file

          var selectedFileIndex = _this.cachedFileArray.findIndex(function (x) {
            return x.token === fileToken;
          });

          _this.deleteFileAtIndex(selectedFileIndex);
        }
      });
    } // Populate the cachedFileArray with images as File objects

  }, {
    key: "addFiles",
    value: function addFiles(files) {
      // Grab the current instance
      var self = this; // In this case, the user most likely had hit cancel - which does something
      // a little strange if they had already selected a single or multiple images -
      // it acts like they now have *no* files - which isn't true. We'll just check here
      // for any cached images already captured,
      // and proceed normally. If something *does* want
      // to clear their images, they'll use the clear button on the label we provide.

      if (files.length === 0) {
        return;
      }

      console.log(self); // Check for file count limit

      var adjustedFilesLength = files.length;

      if (self.options.maxFileCount > 0) {
        if (files.length + self.cachedFileArray.length > self.options.maxFileCount) {
          // Limit exceeded.  Truncate list.
          adjustedFilesLength = self.options.maxFileCount - self.cachedFileArray.length;
        }
      } // If the input is set to allow multiple files, then we'll add to
      // the existing file count and keep the cachedFileArray. If not,
      // then we'll reset the file count and reset the cachedFileArray


      if (self.input.multiple) {
        self.currentFileCount += adjustedFilesLength;
      } else {
        self.currentFileCount = adjustedFilesLength;
        self.cachedFileArray = [];
      } // Now let's loop over the added images and
      // act accordingly based on there were multiple images or not


      for (var x = 0; x < adjustedFilesLength; x++) {
        // Grab this index's file
        var file = files[x]; // To make sure each image can be treated individually, let's give
        // each file a unique token

        file.token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15); // File/files added.

        self.cachedFileArray.push(file); // Process file

        self.processFile(file);
      } // Send out our event


      var imagesAddedEvent = new CustomEvent('fileUploadWithPreview:imagesAdded', {
        detail: {
          files: files,
          uploadId: self.uploadId,
          cachedFileArray: self.cachedFileArray,
          addedFilesCount: adjustedFilesLength
        }
      });
      window.dispatchEvent(imagesAddedEvent);
    } // Take a single File object and append it to the image preview panel

  }, {
    key: "processFile",
    value: function processFile(file) {
      var _this2 = this;

      // Update our input label here based on instance currentFileCount
      if (this.currentFileCount === 0) {
        this.inputLabel.innerHTML = this.options.text.chooseFile;
      } else if (this.currentFileCount === 1) {
        this.inputLabel.innerHTML = file.name;
      } else {
        this.inputLabel.innerHTML = "".concat(this.currentFileCount, " ").concat(this.options.text.selectedCount);
      }

      this.addBrowseButton(this.options.text.browse); // Apply the 'custom-file-container__image-preview--active' class

      this.imagePreview.classList.add('custom-file-container__image-preview--active'); // Set up our reader

      var reader = new FileReader();
      reader.readAsDataURL(file); // Check the file and act accordingly

      reader.onload = function () {
        // We'll pivot here and go through our cases.
        // The logic we've set is basically as follows:
        // If this is an input that only accepts a single image, then just show
        // back that single image each time, and their file count is always 1.
        // If they have `multiple` set on the input, then what we'll do is ADD
        // images to the `cachedImageArray`. We'll show the images in a grid style at all times when
        // `multiple` is set on the input. If the user wants to get rid of all the
        // images they'll used the `x` button near the input, or the `x` button on the image.
        ////////////////////////////////////////////////////
        // First, we'll deal with a single selected image //
        ////////////////////////////////////////////////////
        if (!_this2.input.multiple) {
          //If png, jpg/jpeg, gif, use the actual image
          if (file.type.match('image/png') || file.type.match('image/jpeg') || file.type.match('image/gif')) {
            _this2.imagePreview.style.backgroundImage = "url(\"".concat(reader.result, "\")");
          } else if (file.type.match('application/pdf')) {
            //PDF Upload
            _this2.imagePreview.style.backgroundImage = "url(\"".concat(_this2.successPdfImage, "\")");
          } else if (file.type.match('video/*')) {
            //Video upload
            _this2.imagePreview.style.backgroundImage = "url(\"".concat(_this2.successVideoImage, "\")");
          } else {
            //Everything else
            _this2.imagePreview.style.backgroundImage = "url(\"".concat(_this2.successFileAltImage, "\")");
          }
        } //////////////////////////////////////////////////////////
        // The next logic set is for a multiple situation, and  //
        // they want to show multiple images                    //
        //////////////////////////////////////////////////////////


        if (_this2.input.multiple) {
          // Set the main panel's background image to the blank one here
          _this2.imagePreview.style.backgroundImage = "url(\"".concat(_this2.backgroundImage, "\")"); //If png, jpg/jpeg, gif, use the actual image

          if (file.type.match('image/png') || file.type.match('image/jpeg') || file.type.match('image/gif')) {
            if (_this2.options.showDeleteButtonOnImages) {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                data-upload-token=\"".concat(file.token, "\"\n                                style=\"background-image: url('").concat(reader.result, "'); \"\n                            >\n                                <span class=\"custom-file-container__image-multi-preview__single-image-clear\">\n                                    <span\n                                        class=\"custom-file-container__image-multi-preview__single-image-clear__icon\"\n                                        data-upload-token=\"").concat(file.token, "\"\n                                    >&times;</span>\n                                </span>\n                            </div>\n                        ");
            } else {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                data-upload-token=\"".concat(file.token, "\"\n                                style=\"background-image: url('").concat(reader.result, "'); \"\n                            ></div>\n                        ");
            }
          } else if (file.type.match('application/pdf')) {
            //PDF Upload
            if (_this2.options.showDeleteButtonOnImages) {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                data-upload-token=\"".concat(file.token, "\"\n                                style=\"background-image: url('").concat(_this2.successPdfImage, "'); \"\n                            >\n                                <span class=\"custom-file-container__image-multi-preview__single-image-clear\">\n                                    <span\n                                        class=\"custom-file-container__image-multi-preview__single-image-clear__icon\"\n                                        data-upload-token=\"").concat(file.token, "\"\n                                    >&times;</span>\n                                </span>\n                            </div>\n                        ");
            } else {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                data-upload-token=\"".concat(file.token, "\"\n                                style=\"background-image: url('").concat(_this2.successPdfImage, "'); \"\n                            ></div>\n                        ");
            }
          } else if (file.type.match('video/*')) {
            //Video upload
            if (_this2.options.showDeleteButtonOnImages) {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                style=\"background-image: url('".concat(_this2.successVideoImage, "'); \"\n                                data-upload-token=\"").concat(file.token, "\"\n                            >\n                                <span class=\"custom-file-container__image-multi-preview__single-image-clear\">\n                                    <span\n                                        class=\"custom-file-container__image-multi-preview__single-image-clear__icon\"\n                                        data-upload-token=\"").concat(file.token, "\"\n                                    >&times;</span>\n                                </span>\n                            </div>\n                        ");
            } else {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                style=\"background-image: url('".concat(_this2.successVideoImage, "'); \"\n                                data-upload-token=\"").concat(file.token, "\"\n                            ></div>\n                        ");
            }
          } else {
            //Everything else
            if (_this2.options.showDeleteButtonOnImages) {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                style=\"background-image: url('".concat(_this2.successFileAltImage, "'); \"\n                                data-upload-token=\"").concat(file.token, "\"\n                            >\n                                <span class=\"custom-file-container__image-multi-preview__single-image-clear\">\n                                    <span\n                                        class=\"custom-file-container__image-multi-preview__single-image-clear__icon\"\n                                        data-upload-token=\"").concat(file.token, "\"\n                                    >&times;</span>\n                                </span>\n                            </div>\n                        ");
            } else {
              _this2.imagePreview.innerHTML += "\n                            <div\n                                class=\"custom-file-container__image-multi-preview\"\n                                style=\"background-image: url('".concat(_this2.successFileAltImage, "'); \"\n                                data-upload-token=\"").concat(file.token, "\"\n                            ></div>\n                        ");
            }
          }
        }
      };
    } // Take an array of image paths, convert them to File objects,
    // and display them in the image preview panel
    // https://stackoverflow.com/questions/25046301/convert-url-to-file-or-blob-for-filereader-readasdataurl

  }, {
    key: "addImagesFromPath",
    value: function addImagesFromPath(files) {
      var _this3 = this;

      return new Promise( /*#__PURE__*/function () {
        var _ref = asyncToGenerator( /*#__PURE__*/regenerator.mark(function _callee(resolve, reject) {
          var presetFiles, x, response, blob, presetFile;
          return regenerator.wrap(function _callee$(_context) {
            while (1) {
              switch (_context.prev = _context.next) {
                case 0:
                  presetFiles = [];
                  x = 0;

                case 2:
                  if (!(x < files.length)) {
                    _context.next = 24;
                    break;
                  }

                  /* eslint-disable no-await-in-loop */
                  response = void 0;
                  blob = void 0;
                  _context.prev = 5;
                  _context.next = 8;
                  return fetch(files[x], {
                    mode: 'cors'
                  });

                case 8:
                  response = _context.sent;
                  _context.next = 11;
                  return response.blob();

                case 11:
                  blob = _context.sent;
                  _context.next = 18;
                  break;

                case 14:
                  _context.prev = 14;
                  _context.t0 = _context["catch"](5);
                  reject(_context.t0);
                  return _context.abrupt("continue", 21);

                case 18:
                  /* eslint-enable no-await-in-loop */
                  // Create blob and added
                  presetFile = new Blob([blob], {
                    type: blob.type
                  });
                  presetFile.name = files[x].split('/').pop();
                  presetFiles.push(presetFile);

                case 21:
                  x++;
                  _context.next = 2;
                  break;

                case 24:
                  _this3.addFiles(presetFiles);

                  resolve();

                case 26:
                case "end":
                  return _context.stop();
              }
            }
          }, _callee, null, [[5, 14]]);
        }));

        return function (_x, _x2) {
          return _ref.apply(this, arguments);
        };
      }());
    } // Take an array of File objects and display them in the image preview panel

  }, {
    key: "replaceFiles",
    value: function replaceFiles(files) {
      if (!files.length) {
        throw new Error('Array must contain at least one file.');
      } // Replace the cachedFileArray with the new files


      this.cachedFileArray = files; // Call function to refresh the preview

      this.refreshPreviewPanel();
    } // Take a single File object and index, replace existing file at that index

  }, {
    key: "replaceFileAtIndex",
    value: function replaceFileAtIndex(file, index) {
      if (!file) {
        throw new Error('No file found.');
      }

      if (!this.cachedFileArray[index]) {
        throw new Error('There is no file at index', index);
      } // Replace the cachedFileArray with the new files


      this.cachedFileArray[index] = file; // Call function to refresh the preview

      this.refreshPreviewPanel();
    } // Delete specified File from `cachedFileArray`

  }, {
    key: "deleteFileAtIndex",
    value: function deleteFileAtIndex(index) {
      // check if index exists
      if (!this.cachedFileArray[index]) {
        throw new Error('There is no file at index', index);
      } // Remove the file from the array


      this.cachedFileArray.splice(index, 1); // Call function to refresh the preview

      this.refreshPreviewPanel(); // Send out our deletion event

      var imageDeletedEvent = new CustomEvent('fileUploadWithPreview:imageDeleted', {
        detail: {
          index: index,
          uploadId: this.uploadId,
          cachedFileArray: this.cachedFileArray,
          currentFileCount: this.currentFileCount
        }
      });
      window.dispatchEvent(imageDeletedEvent);
    } // Refresh image preview panel with current cachedFileArray

  }, {
    key: "refreshPreviewPanel",
    value: function refreshPreviewPanel() {
      var _this4 = this;

      // Clear the imagePreview pane
      this.imagePreview.innerHTML = ''; // Reset our currentFileCount with the new proper count

      this.currentFileCount = this.cachedFileArray.length; // Load back up the images in the pane with the new updated cachedFileArray

      this.cachedFileArray.forEach(function (file) {
        return _this4.processFile(file);
      }); // If there's no images left after the latest deletion event,
      // then let's reset the panel entirely

      if (!this.cachedFileArray.length) {
        this.clearPreviewPanel();
      }
    } // Appends a browse button to input with custom button text

  }, {
    key: "addBrowseButton",
    value: function addBrowseButton(text) {
      this.inputLabel.innerHTML += "<span class=\"custom-file-container__custom-file__custom-file-control__button\"> ".concat(text, " </span>");
    } // Open the image browser

  }, {
    key: "emulateInputSelection",
    value: function emulateInputSelection() {
      this.input.click();
    } // Clear the cachedFileArray and a

  }, {
    key: "clearPreviewPanel",
    value: function clearPreviewPanel() {
      this.input.value = '';
      this.inputLabel.innerHTML = this.options.text.chooseFile;
      this.addBrowseButton(this.options.text.browse);
      this.imagePreview.style.backgroundImage = "url(\"".concat(this.baseImage, "\")");
      this.imagePreview.classList.remove('custom-file-container__image-preview--active');
      this.cachedFileArray = [];
      this.imagePreview.innerHTML = '';
      this.currentFileCount = 0;
    }
  }]);

  return FileUploadWithPreview;
}();

/* harmony default export */ __webpack_exports__["default"] = (FileUploadWithPreview);


/***/ }),

/***/ "./node_modules/file-upload-with-preview/dist/file-upload-with-preview.min.css":
/*!*************************************************************************************!*\
  !*** ./node_modules/file-upload-with-preview/dist/file-upload-with-preview.min.css ***!
  \*************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../css-loader??ref--6-1!../../postcss-loader/src??ref--6-2!./file-upload-with-preview.min.css */ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./node_modules/file-upload-with-preview/dist/file-upload-with-preview.min.css");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/google-charts/dist/googleCharts.esm.js":
/*!*************************************************************!*\
  !*** ./node_modules/google-charts/dist/googleCharts.esm.js ***!
  \*************************************************************/
/*! exports provided: default, GoogleCharts */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "GoogleCharts", function() { return GoogleCharts; });
/* googleCharts.js Version: 1.5.0 Built On: 2018-12-30 */
const loadScript = Symbol('loadScript');
const instance = Symbol('instance');
let _instance;

class GoogleChartsManager {
    get [instance]() {
        return _instance
    }

    set [instance](value) {
        _instance = value;
    }

    constructor() {
        if (this[instance]) {
            return this[instance]
        }

        this[instance] = this;
    }

    reset() {
        _instance = null;
    }

    [loadScript]() {
        if (!this.scriptPromise) {
            this.scriptPromise = new Promise(resolve => {
                const body = document.getElementsByTagName('body')[0];
                const script = document.createElement('script');
                script.type = 'text/javascript';
                script.onload = function() {
                    GoogleCharts.api = window.google;
                    GoogleCharts.api.charts.load('current', {
                        packages: ['corechart', 'table'],
                    });
                    GoogleCharts.api.charts.setOnLoadCallback(() => {
                        resolve();
                    });
                };
                script.src = 'https://www.gstatic.com/charts/loader.js';
                body.appendChild(script);
            });
        }
        return this.scriptPromise
    }

    load(callback, type) {
        return this[loadScript]().then(() => {
            if (type) {
                let config = {};
                if (type instanceof Object) {
                    config = type;
                } else if (Array.isArray(type)) {
                    config = { packages: type };
                } else {
                    config = { packages: [type] };
                }
                this.api.charts.load('current', config);
                this.api.charts.setOnLoadCallback(callback);
            } else {
                if(typeof callback != 'function') {
                    throw('callback must be a function');
                } else {
                    callback();               
                }
            }
        })
    }
}

const GoogleCharts = new GoogleChartsManager();

/* harmony default export */ __webpack_exports__["default"] = (GoogleChartsManager);



/***/ }),

/***/ "./node_modules/style-loader/lib/addStyles.js":
/*!****************************************************!*\
  !*** ./node_modules/style-loader/lib/addStyles.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/

var stylesInDom = {};

var	memoize = function (fn) {
	var memo;

	return function () {
		if (typeof memo === "undefined") memo = fn.apply(this, arguments);
		return memo;
	};
};

var isOldIE = memoize(function () {
	// Test for IE <= 9 as proposed by Browserhacks
	// @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
	// Tests for existence of standard globals is to allow style-loader
	// to operate correctly into non-standard environments
	// @see https://github.com/webpack-contrib/style-loader/issues/177
	return window && document && document.all && !window.atob;
});

var getTarget = function (target, parent) {
  if (parent){
    return parent.querySelector(target);
  }
  return document.querySelector(target);
};

var getElement = (function (fn) {
	var memo = {};

	return function(target, parent) {
                // If passing function in options, then use it for resolve "head" element.
                // Useful for Shadow Root style i.e
                // {
                //   insertInto: function () { return document.querySelector("#foo").shadowRoot }
                // }
                if (typeof target === 'function') {
                        return target();
                }
                if (typeof memo[target] === "undefined") {
			var styleTarget = getTarget.call(this, target, parent);
			// Special case to return head of iframe instead of iframe itself
			if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
				try {
					// This will throw an exception if access to iframe is blocked
					// due to cross-origin restrictions
					styleTarget = styleTarget.contentDocument.head;
				} catch(e) {
					styleTarget = null;
				}
			}
			memo[target] = styleTarget;
		}
		return memo[target]
	};
})();

var singleton = null;
var	singletonCounter = 0;
var	stylesInsertedAtTop = [];

var	fixUrls = __webpack_require__(/*! ./urls */ "./node_modules/style-loader/lib/urls.js");

module.exports = function(list, options) {
	if (typeof DEBUG !== "undefined" && DEBUG) {
		if (typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};

	options.attrs = typeof options.attrs === "object" ? options.attrs : {};

	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (!options.singleton && typeof options.singleton !== "boolean") options.singleton = isOldIE();

	// By default, add <style> tags to the <head> element
        if (!options.insertInto) options.insertInto = "head";

	// By default, add <style> tags to the bottom of the target
	if (!options.insertAt) options.insertAt = "bottom";

	var styles = listToStyles(list, options);

	addStylesToDom(styles, options);

	return function update (newList) {
		var mayRemove = [];

		for (var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];

			domStyle.refs--;
			mayRemove.push(domStyle);
		}

		if(newList) {
			var newStyles = listToStyles(newList, options);
			addStylesToDom(newStyles, options);
		}

		for (var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];

			if(domStyle.refs === 0) {
				for (var j = 0; j < domStyle.parts.length; j++) domStyle.parts[j]();

				delete stylesInDom[domStyle.id];
			}
		}
	};
};

function addStylesToDom (styles, options) {
	for (var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];

		if(domStyle) {
			domStyle.refs++;

			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}

			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];

			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}

			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles (list, options) {
	var styles = [];
	var newStyles = {};

	for (var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = options.base ? item[0] + options.base : item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};

		if(!newStyles[id]) styles.push(newStyles[id] = {id: id, parts: [part]});
		else newStyles[id].parts.push(part);
	}

	return styles;
}

function insertStyleElement (options, style) {
	var target = getElement(options.insertInto)

	if (!target) {
		throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
	}

	var lastStyleElementInsertedAtTop = stylesInsertedAtTop[stylesInsertedAtTop.length - 1];

	if (options.insertAt === "top") {
		if (!lastStyleElementInsertedAtTop) {
			target.insertBefore(style, target.firstChild);
		} else if (lastStyleElementInsertedAtTop.nextSibling) {
			target.insertBefore(style, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			target.appendChild(style);
		}
		stylesInsertedAtTop.push(style);
	} else if (options.insertAt === "bottom") {
		target.appendChild(style);
	} else if (typeof options.insertAt === "object" && options.insertAt.before) {
		var nextSibling = getElement(options.insertAt.before, target);
		target.insertBefore(style, nextSibling);
	} else {
		throw new Error("[Style Loader]\n\n Invalid value for parameter 'insertAt' ('options.insertAt') found.\n Must be 'top', 'bottom', or Object.\n (https://github.com/webpack-contrib/style-loader#insertat)\n");
	}
}

function removeStyleElement (style) {
	if (style.parentNode === null) return false;
	style.parentNode.removeChild(style);

	var idx = stylesInsertedAtTop.indexOf(style);
	if(idx >= 0) {
		stylesInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement (options) {
	var style = document.createElement("style");

	if(options.attrs.type === undefined) {
		options.attrs.type = "text/css";
	}

	if(options.attrs.nonce === undefined) {
		var nonce = getNonce();
		if (nonce) {
			options.attrs.nonce = nonce;
		}
	}

	addAttrs(style, options.attrs);
	insertStyleElement(options, style);

	return style;
}

function createLinkElement (options) {
	var link = document.createElement("link");

	if(options.attrs.type === undefined) {
		options.attrs.type = "text/css";
	}
	options.attrs.rel = "stylesheet";

	addAttrs(link, options.attrs);
	insertStyleElement(options, link);

	return link;
}

function addAttrs (el, attrs) {
	Object.keys(attrs).forEach(function (key) {
		el.setAttribute(key, attrs[key]);
	});
}

function getNonce() {
	if (false) {}

	return __webpack_require__.nc;
}

function addStyle (obj, options) {
	var style, update, remove, result;

	// If a transform function was defined, run it on the css
	if (options.transform && obj.css) {
	    result = typeof options.transform === 'function'
		 ? options.transform(obj.css) 
		 : options.transform.default(obj.css);

	    if (result) {
	    	// If transform returns a value, use that instead of the original css.
	    	// This allows running runtime transformations on the css.
	    	obj.css = result;
	    } else {
	    	// If the transform function returns a falsy value, don't add this css.
	    	// This allows conditional loading of css
	    	return function() {
	    		// noop
	    	};
	    }
	}

	if (options.singleton) {
		var styleIndex = singletonCounter++;

		style = singleton || (singleton = createStyleElement(options));

		update = applyToSingletonTag.bind(null, style, styleIndex, false);
		remove = applyToSingletonTag.bind(null, style, styleIndex, true);

	} else if (
		obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function"
	) {
		style = createLinkElement(options);
		update = updateLink.bind(null, style, options);
		remove = function () {
			removeStyleElement(style);

			if(style.href) URL.revokeObjectURL(style.href);
		};
	} else {
		style = createStyleElement(options);
		update = applyToTag.bind(null, style);
		remove = function () {
			removeStyleElement(style);
		};
	}

	update(obj);

	return function updateStyle (newObj) {
		if (newObj) {
			if (
				newObj.css === obj.css &&
				newObj.media === obj.media &&
				newObj.sourceMap === obj.sourceMap
			) {
				return;
			}

			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;

		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag (style, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (style.styleSheet) {
		style.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = style.childNodes;

		if (childNodes[index]) style.removeChild(childNodes[index]);

		if (childNodes.length) {
			style.insertBefore(cssNode, childNodes[index]);
		} else {
			style.appendChild(cssNode);
		}
	}
}

function applyToTag (style, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		style.setAttribute("media", media)
	}

	if(style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		while(style.firstChild) {
			style.removeChild(style.firstChild);
		}

		style.appendChild(document.createTextNode(css));
	}
}

function updateLink (link, options, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	/*
		If convertToAbsoluteUrls isn't defined, but sourcemaps are enabled
		and there is no publicPath defined then lets turn convertToAbsoluteUrls
		on by default.  Otherwise default to the convertToAbsoluteUrls option
		directly
	*/
	var autoFixUrls = options.convertToAbsoluteUrls === undefined && sourceMap;

	if (options.convertToAbsoluteUrls || autoFixUrls) {
		css = fixUrls(css);
	}

	if (sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = link.href;

	link.href = URL.createObjectURL(blob);

	if(oldSrc) URL.revokeObjectURL(oldSrc);
}


/***/ }),

/***/ "./node_modules/style-loader/lib/urls.js":
/*!***********************************************!*\
  !*** ./node_modules/style-loader/lib/urls.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {


/**
 * When source maps are enabled, `style-loader` uses a link element with a data-uri to
 * embed the css on the page. This breaks all relative urls because now they are relative to a
 * bundle instead of the current page.
 *
 * One solution is to only use full urls, but that may be impossible.
 *
 * Instead, this function "fixes" the relative urls to be absolute according to the current page location.
 *
 * A rudimentary test suite is located at `test/fixUrls.js` and can be run via the `npm test` command.
 *
 */

module.exports = function (css) {
  // get current location
  var location = typeof window !== "undefined" && window.location;

  if (!location) {
    throw new Error("fixUrls requires window.location");
  }

	// blank or null?
	if (!css || typeof css !== "string") {
	  return css;
  }

  var baseUrl = location.protocol + "//" + location.host;
  var currentDir = baseUrl + location.pathname.replace(/\/[^\/]*$/, "/");

	// convert each url(...)
	/*
	This regular expression is just a way to recursively match brackets within
	a string.

	 /url\s*\(  = Match on the word "url" with any whitespace after it and then a parens
	   (  = Start a capturing group
	     (?:  = Start a non-capturing group
	         [^)(]  = Match anything that isn't a parentheses
	         |  = OR
	         \(  = Match a start parentheses
	             (?:  = Start another non-capturing groups
	                 [^)(]+  = Match anything that isn't a parentheses
	                 |  = OR
	                 \(  = Match a start parentheses
	                     [^)(]*  = Match anything that isn't a parentheses
	                 \)  = Match a end parentheses
	             )  = End Group
              *\) = Match anything and then a close parens
          )  = Close non-capturing group
          *  = Match anything
       )  = Close capturing group
	 \)  = Match a close parens

	 /gi  = Get all matches, not the first.  Be case insensitive.
	 */
	var fixedCss = css.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function(fullMatch, origUrl) {
		// strip quotes (if they exist)
		var unquotedOrigUrl = origUrl
			.trim()
			.replace(/^"(.*)"$/, function(o, $1){ return $1; })
			.replace(/^'(.*)'$/, function(o, $1){ return $1; });

		// already a full url? no change
		if (/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/|\s*$)/i.test(unquotedOrigUrl)) {
		  return fullMatch;
		}

		// convert the url to a full url
		var newUrl;

		if (unquotedOrigUrl.indexOf("//") === 0) {
		  	//TODO: should we add protocol?
			newUrl = unquotedOrigUrl;
		} else if (unquotedOrigUrl.indexOf("/") === 0) {
			// path should be relative to the base url
			newUrl = baseUrl + unquotedOrigUrl; // already starts with '/'
		} else {
			// path should be relative to current directory
			newUrl = currentDir + unquotedOrigUrl.replace(/^\.\//, ""); // Strip leading './'
		}

		// send back the fixed url(...)
		return "url(" + JSON.stringify(newUrl) + ")";
	});

	// send back the fixed css
	return fixedCss;
};


/***/ }),

/***/ "./resources/asset/js/components/FilePreview.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/FilePreview.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var file_upload_with_preview__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! file-upload-with-preview */ "./node_modules/file-upload-with-preview/dist/file-upload-with-preview.esm.js");
/* harmony import */ var file_upload_with_preview_dist_file_upload_with_preview_min_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! file-upload-with-preview/dist/file-upload-with-preview.min.css */ "./node_modules/file-upload-with-preview/dist/file-upload-with-preview.min.css");
/* harmony import */ var file_upload_with_preview_dist_file_upload_with_preview_min_css__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(file_upload_with_preview_dist_file_upload_with_preview_min_css__WEBPACK_IMPORTED_MODULE_1__);


var upload = new file_upload_with_preview__WEBPACK_IMPORTED_MODULE_0__["default"]('myUniqueUploadId');
upload.cachedFileArray;
document.getElementById('testC').addEventListener('click', function () {
  alert('worked');
});
document.getElementById('check').innerHTML = " nice and nice";

/***/ }),

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


            if (post.type == 'submit' || post.name == 'token') {
              continue;
            } // check if there is no value


            var postName = post.name.replace('_', ' ');

            if (postName == "spouseName" || postName == "spouseMobile") {
              post.value = "11";
            }

            if (post.value === '' || post.value === 'select') {
              errMsg.innerHTML = "<li>".concat(postName, " cannot be left empty</li>");

              _this.error.push("<li>".concat(postName, " cannot be left empty</li>"));
            } else if (post.value.match(reg) === null) {
              errMsg.innerHTML = "<li> only letters and numbers are allowed<li>";

              _this.error.push("<li> only letters and numbers are allowed<li>");
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
      var msg = "<li> Please enter a valid email</li>";
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

/***/ "./resources/asset/js/components/formBuilder.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/formBuilder.js ***!
  \******************************************************/
/*! exports provided: Input */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Input", function() { return Input; });
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../global */ "./resources/asset/js/global.js");

"use strict";
/**
 * 
 * @param {That is the obj name} objData 
 * @param {* this is the div id} htmlId 
 */


var Input = function Input(objData, htmlId) {
  try {
    // check errors for the input
    if (objData == null) throw " data object is needed";
    if (htmlId == null) throw "html id is required";
    objData.map(function (element) {
      if (element.inputType === 'NORMAL_INPUT') {
        var renderHtml = "\n      <label> <strong>".concat(element.form.toUpperCase(), "</strong> </label>\n      <div class = 'form-group' id=").concat(element.attribute, "_div>\n         <label class='' for =").concat(element.attribute, "> \n         <strong>").concat(element.label.toUpperCase(), "</strong>\n         </label>\n         <input type=\"").concat(element.type, "\" class=\"form-control\" \n         id=\"").concat(element.attribute, "\"\n         name=\"").concat(element.attribute, "\"  placeholder=\"").concat(element.placeholder, "\"\n         />\n        <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n      </div>");
        if (renderHtml == null) throw 'NORMAL INPUT -' + element.attribute;
        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', renderHtml);
      } else if (element.inputType === 'SELECT') {
        var _renderHtml = "\n      <div class = 'form-group' id='".concat(element.attribute, "_div'>\n      <label for =").concat(element.attribute, "> <strong>").concat(element.label.toUpperCase(), "</strong> </label>   \n          <select class=\"form-control\" id=").concat(element.attribute, " name=").concat(element.attribute, ">\n                <option value= 'select'>\n                select\n                </option> \n                ").concat(element.options.map(function (el) {
          return "<option value=".concat(el, ">").concat(el, "</option>");
        }), "               \n          </select>     \n             <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n           </div>\n          ");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml);
      } else if (element.inputType === 'FILE') {
        var _renderHtml2 = "\n      <div class = 'form-group input-group mb-3' id='".concat(element.attribute, "_div'>\n       <div class=\"custom-file\">\n          <input type=\"file\" name=\"").concat(element.attribute, "\" class=\"custom-file-input\" id=").concat(element.attribute, ">\n          <label class=\"custom-file-label\" for=").concat(element.attribute, ">").concat(element.label, "</label>\n             <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n           </div>\n          </div>\n          ");

        if (_renderHtml2 == "") throw 'FILE -' + element.attribute;
        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml2);
      } else if (element.inputType === 'RADIO') {
        var _renderHtml3 = "\n      <div class='form-group col' id='".concat(element.attribute, "_div'>\n         <label for =").concat(element.attribute, "> <strong> ").concat(element.label.toUpperCase(), ": </strong>  </label> \n\n        <div class = 'form-check form-check-inline'>\n            ").concat(element.options.map(function (el) {
          return "\n              <input \n              class='form-check-input' \n              type='radio' \n              name=".concat(element.attribute, " \n              id=").concat(element.attribute, "_").concat(el, " \n              value=").concat(el, ">\n\n              <label class= 'form-check-label' > ").concat(el, "</label>");
        }), " \n      </div>  \n        <small id ='").concat(element.attribute, "_help' class='small text-muted'></small>\n            <small id =").concat(element.attribute, "_error class='error text-muted'></small>\n      </div>     \n      ");

        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml3);
      } else if (element.form === '3-col') {
        var _renderHtml4 = "\n      <label> <strong>".concat(element.label.toUpperCase(), "</strong> </label>\n          <div class = 'form-row ").concat(element.unique, "' id='").concat(element.unique, "_div'>\n          <div class='form-group col-md-4'>\n            <label for='").concat(element.options.attribute[0], "'>\n            ").concat(element.options.label[0], "\n            </label>\n            <input type='").concat(element.options.type[0], "' class='form-control' name='").concat(element.options.attribute[0], "'\n            id='").concat(element.options.attribute[0], "' placeholder='").concat(element.options.placeholder[0], "'>\n         <small id ='").concat(element.options.attribute[0], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[0], "_error class='error text-muted'></small>\n          </div>\n          <div class='form-group col-md-4'>\n            <label for='").concat(element.options.attribute[1], "'>").concat(element.options.label[1], "\n            </label>\n            <input type='").concat(element.options.type[1], "' class='form-control'  \n            id='").concat(element.options.attribute[1], "' name='").concat(element.options.attribute[1], "'\n            placeholder='").concat(element.options.placeholder[1], "'>\n         <small id ='").concat(element.options.attribute[1], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[1], "_error class='error text-muted'></small>\n          </div> \n          <div class='form-group col-md-4'>\n            <label for='").concat(element.options.attribute[2], "'>").concat(element.options.label[2], "\n            </label>\n            <input type='").concat(element.options.type[2], "' class='form-control' \n            id='").concat(element.options.attribute[2], "' name='").concat(element.options.attribute[2], "' \n            placeholder='").concat(element.options.placeholder[2], "'>\n         <small id ='").concat(element.options.attribute[2], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[2], "_error class='error text-muted'></small>\n          </div>  \n      </div>");

        if (_renderHtml4 == "") throw 'empty 3-col -' + element.options.attribute[1];
        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml4);
      } else if (element.form === '2-col') {
        var _renderHtml5 = "\n         <label> <strong>".concat(element.label.toUpperCase(), "</strong> </label>\n          <div class = 'form-row ").concat(element.unique, "' id='").concat(element.unique, "_div'>\n          <div class='form-group col-md-6'>\n            <label for='").concat(element.options.attribute[0], "'>\n            ").concat(element.options.label[0], "\n            </label>\n            <input type='").concat(element.options.type[0], "' class='form-control' \n            id='").concat(element.options.attribute[0], "' \n            placeholder='").concat(element.options.placeholder[0], "'\n            name='").concat(element.options.attribute[0], "'\n            >\n         <small id ='").concat(element.options.attribute[0], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[0], "_error class='error text-muted'></small>\n          </div>\n          <div class='form-group col-md-6'>\n            <label for='").concat(element.options.attribute[1], "'>").concat(element.options.label[1], "\n            </label>\n            <input type='").concat(element.options.type[1], "' class='form-control' \n            id='").concat(element.options.attribute[1], "' \n            placeholder='").concat(element.options.placeholder[1], "'\n            name='").concat(element.options.attribute[1], "'\n            >\n         <small id ='").concat(element.options.attribute[1], "_help' class='small text-muted'></small>\n            <small id =").concat(element.options.attribute[1], "_error class='error text-muted'></small>\n          </div> \n      </div>");

        if (_renderHtml5 == "") throw 'empty 2-col -' + element.options.attribute[1];
        document.getElementById(htmlId).insertAdjacentHTML('beforebegin', _renderHtml5);
      }
    });
  } catch (e) {
    Object(_global__WEBPACK_IMPORTED_MODULE_0__["showError"])(e);
  }
};

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
  alert("it worked");
  Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("firstName_help").innerHTML = "checking";
} catch (error) {
  Object(_global__WEBPACK_IMPORTED_MODULE_0__["log"])(error.message);
}

/***/ }),

/***/ "./resources/asset/js/components/login/index.js":
/*!******************************************************!*\
  !*** ./resources/asset/js/components/login/index.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _data_Login__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../data/Login */ "./resources/asset/js/data/Login.js");
/* harmony import */ var _formBuilder__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../formBuilder */ "./resources/asset/js/components/formBuilder.js");
/* harmony import */ var _global__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../global */ "./resources/asset/js/global.js");




try {
  Object(_formBuilder__WEBPACK_IMPORTED_MODULE_1__["Input"])(_data_Login__WEBPACK_IMPORTED_MODULE_0__["Login"], 'login');
} catch (error) {
  Object(_global__WEBPACK_IMPORTED_MODULE_2__["showError"])(error);
}

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


try {
  var showModal = function showModal() {
    return Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('id01').style.display = 'block';
  };

  Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('postMessage').addEventListener('click', showModal);
} catch (e) {
  console.log(e.message);
}

alert('helllllllo');

/***/ }),

/***/ "./resources/asset/js/components/organogram.js":
/*!*****************************************************!*\
  !*** ./resources/asset/js/components/organogram.js ***!
  \*****************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var google_charts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! google-charts */ "./node_modules/google-charts/dist/googleCharts.esm.js");

google_charts__WEBPACK_IMPORTED_MODULE_0__["GoogleCharts"].load(drawChart);
google_charts__WEBPACK_IMPORTED_MODULE_0__["GoogleCharts"].load('current', {
  'packages': ['orgchart']
});
document.getElementById('checkTest').innerHTML = "<h1>It worked well and fine</h1>";

var drawChart = function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Name');
  data.addColumn('string', 'Manager');
  data.addColumn('string', 'ToolTip'); // For each orgchart box, provide the name, manager, and tooltip to show.

  data.addRows([[{
    'v': 'Mike',
    'f': 'Mike<div style="color:red; font-style:italic">President</div>'
  }, '', 'The President'], [{
    'v': 'Jim',
    'f': 'Jim<div style="color:red; font-style:italic">Vice President</div>'
  }, 'Mike', 'VP'], ['Alice', 'Mike', ''], ['Bob', 'Jim', 'Bob Sponge'], ['Carol', 'Bob', '']]); // Create the chart.

  var chart = new google.visualization.OrgChart(document.getElementById('chart_div')); // Draw the chart, setting the allowHtml option to true for the tooltips.

  chart.draw(data, {
    'allowHtml': true
  });
};

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

/***/ "./resources/asset/js/components/register/index.js":
/*!*********************************************************!*\
  !*** ./resources/asset/js/components/register/index.js ***!
  \*********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _data_Personal_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../data/Personal.js */ "./resources/asset/js/data/Personal.js");
/* harmony import */ var _data_Contact_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../data/Contact.js */ "./resources/asset/js/data/Contact.js");
/* harmony import */ var _data_Work_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../data/Work.js */ "./resources/asset/js/data/Work.js");
/* harmony import */ var _formBuilder_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../formBuilder.js */ "./resources/asset/js/components/formBuilder.js");
/* harmony import */ var _data_Interest__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../data/Interest */ "./resources/asset/js/data/Interest.js");
/* harmony import */ var _data_Account__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../../data/Account */ "./resources/asset/js/data/Account.js");









try {
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Contact_js__WEBPACK_IMPORTED_MODULE_1__["Contact"], 'contact');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Personal_js__WEBPACK_IMPORTED_MODULE_0__["Personal"], 'personal');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Work_js__WEBPACK_IMPORTED_MODULE_2__["Work"], 'work');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Interest__WEBPACK_IMPORTED_MODULE_4__["Interest"], 'interest');
  Object(_formBuilder_js__WEBPACK_IMPORTED_MODULE_3__["Input"])(_data_Account__WEBPACK_IMPORTED_MODULE_5__["Account"], 'account');
} catch (e) {
  console.log(e.message, e.name);
}

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
      var addKids = " <div class=\"row\">\n            <div class=\"col\">\n            <input type=\"text\" placeholder = \"Enter child's full name - ".concat(no, "\" name =kid_name").concat(no, " class=\"form-control\" id=\"kid_name").concat(no, "\">\n            </div>\n            <div class=\"col\">\n           <input type=\"email\" placeholder = \"Enter child's email - ").concat(no, "\" name=kid_email").concat(no, " class=\"form-control\" id=\"kid_email").concat(no, "\">\n           </div>\n        </div><br>");

      if (!Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("kid".concat(no)) || !Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])("kidEmail".concat(no))) {
        Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids_div').insertAdjacentHTML('afterend', addKids);
      } else {
        Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids_div').insertAdjacentHTML('afterend', "none");
      }
    }
  } catch (error) {
    showError(error);
  }
}; // this is to activate the onchange event


Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('kids').addEventListener('change', show);

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

  formData.realTimeCheckLen(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].maxLength.id, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].maxLength.max); //real time check 

  formData.realTimeServer('spouseMobile', "/search?attribute=spouseMobile&subject=spouse&hint", 'spouseMobile_error');
  formData.realTimeServer('fatherMobile', '/search?attribute=fatherMobile&subject=father&hint', 'fatherMobile_error');
  formData.realTimeServer('motherMobile', '/search?attribute=motherMobile&subject=mother&hint', 'motherMobile_error'); // check if password matches real time

  formData.matchInput(_data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].password.pwd, _data_dataToCheck__WEBPACK_IMPORTED_MODULE_2__["dataToCheck"].password.pwd2 // dataToCheck.password.err
  ); // check if they have a father yes
  // formData.isChecked(dataToCheck.familyCheck.father[0],
  // 	dataToCheck.familyCheck.father[1],
  // 	'fatherEmail_error'
  // )
  // // check if they have a mother yes
  // formData.isChecked(dataToCheck.familyCheck.mother[0],
  // 	dataToCheck.familyCheck.mother[1],
  // 	'motherEmail_error'
  // )
  // // check if they have a spouse yes
  // formData.isChecked(dataToCheck.familyCheck.spouse[0],
  // 	dataToCheck.familyCheck.spouse[1],
  // 	'spouseEmail_error'
  // )
};

process();
Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').addEventListener('click', function () {
  try {
    if (Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('checkbox').checked) {
      formData.emailVal(); // sanitise email

      formData.massValidate(); // validate and sanitise data

      Object(_global__WEBPACK_IMPORTED_MODULE_1__["log"])(formData.error);

      if (formData.error.length <= 0) {
        Object(_global__WEBPACK_IMPORTED_MODULE_1__["id"])('submit').type = 'submit';
        console.log('submitted');
      } else {
        console.log(formData.error);
        alert('The form cannot be submitted. Please check the errors');
        process();
      }
    } else {
      alert('To continue, you need to agree to the Olaoguns handling your information as outlined in our privacy policy');
    }
  } catch (e) {
    console.log(e.message);
  }
});

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

var maiden = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('motherMaiden_help');
maiden.innerHTML = "Good to identify your family from mum's side";
var mobile = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('mobile_help');
mobile.innerHTML = "Nigeria: 2348036517179, UK: 447871717809";
var password = Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('password_help');
password.innerHTML = 'Must be 8-20 characters long.';
Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "none";

var showSpouse = function showSpouse(e) {
  if (e.target.value === "Yes") {
    Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "block";
  } else {
    Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('spouse_div').style.display = "none";
  }
};

Object(_global__WEBPACK_IMPORTED_MODULE_0__["id"])('maritalStatus').addEventListener('change', showSpouse);

/***/ }),

/***/ "./resources/asset/js/cust/main.js":
/*!*****************************************!*\
  !*** ./resources/asset/js/cust/main.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function () {
  var i = "box",
      n = {
    init: function init() {
      this.scroll_animate(), $("#b2top").click(function () {
        $("html, body").stop().animate({
          scrollTop: 0
        });
      }), $("#overview-aside-nav ul").navscroll({
        sec: 1e3,
        url_hash: !1,
        head_hight: 0
      }), $("#overview-mainnav").navscroll({
        sec: 1e3,
        url_hash: !1,
        head_hight: 0
      });
    },
    scroll_animate: function scroll_animate() {
      $(window).scroll(function () {
        for (var i = window.innerHeight, n = $(".anim").length, o = 0; o < n; o++) {
          $(window).scrollTop() > $(".anim").eq(o).offset().top - i / 4 && $(".anim").eq(o).addClass("on");
        }
      });
    }
  };
  n.init();
});

(function ($) {
  /* --------------------------------------------------
   Contact Pages
  -------------------------------------------------- */
  $('.show-map').on('click', function (e) {
    e.preventDefault();
    $('.contact-info-wrapper').toggleClass('map-open');
    $('.show-info-link').toggleClass('info-open');
  });
  $('.show-info-link').on('click', function (e) {
    e.preventDefault();
    $('.contact-info-wrapper').toggleClass('map-open');
    $(this).toggleClass('info-open');
  });
})(jQuery);
/* --------------------------------------------------
	Contact Form JS Validation & AJAX call 
-------------------------------------------------- */


$(function () {
  //	Regular Expressions
  var expEmail = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.[a-z]{2,4})$/;
  var expLettersOnly = /^[A-Za-z ]+$/; //	Checks if a field has the correct length

  function validateLength(fieldValue, minLength) {
    return $.trim(fieldValue).length > minLength;
  } //	Validate form on typing


  $('.form-ajax').on('keyup', 'input.validate-locally', function () {
    validateField($(this));
  }); //	AJAX call

  $('.form-ajax').submit(function (e) {
    e.preventDefault();
    var $this = $(this),
        action = $this.attr('action'); // The AJAX requrest

    $.post(action, $this.serialize(), function (data) {
      $('.ajax-message').html(data);
    });
  }); //	Validates the fileds

  function validateField(field) {
    var errorText = "",
        error = false,
        value = field.val(),
        siblings = field.siblings(".alert-error"); // Test the name field

    if (field.attr("name") === "name") {
      if (!validateLength(value, 2)) {
        error = true;
        errorText += '<i class="fa fa-info-circle"></i> The name is too short!<br>';
        $('input[name="name"]').addClass('input-error');
      } else {
        $('input[name="name"]').removeClass('input-error');
      }

      if (!expLettersOnly.test(value)) {
        error = true;
        errorText += '<i class="fa fa-info-circle"></i> The name can contain only letters and spaces!<br>';
        $('input[name="name"]').addClass('input-error-2');
      } else {
        $('input[name="name"]').removeClass('input-error-2');
      }
    } // Test the email field


    if (field.attr("name") === "email") {
      if (!expEmail.test(value)) {
        error = true;
        errorText += '<i class="fa fa-info-circle"></i> Enter correct email address!<br>';
        $('input[name="email"]').addClass('input-error');
      } else {
        $('input[name="email"]').removeClass('input-error');
      }
    } // Display the errors


    siblings.html(errorText);
  }
}); // Google Map Custom js

var marker;
var image = 'images/map-marker.png';

function initMap() {
  var myLatLng = {
    lat: 39.79,
    lng: -86.14
  }; // Specify features and elements to define styles.

  var styleArray = [{
    "featureType": "administrative",
    "elementType": "all",
    "stylers": [{
      "saturation": "-100"
    }]
  }, {
    "featureType": "administrative.province",
    "elementType": "all",
    "stylers": [{
      "visibility": "off"
    }]
  }, {
    "featureType": "landscape",
    "elementType": "all",
    "stylers": [{
      "saturation": -100
    }, {
      "lightness": 40
    }, {
      "visibility": "on"
    }]
  }, {
    "featureType": "poi",
    "elementType": "all",
    "stylers": [{
      "saturation": -100
    }, {
      "lightness": "50"
    }, {
      "visibility": "simplified"
    }]
  }, {
    "featureType": "road",
    "elementType": "all",
    "stylers": [{
      "saturation": "-100"
    }]
  }, {
    "featureType": "road.highway",
    "elementType": "all",
    "stylers": [{
      "visibility": "simplified"
    }]
  }, {
    "featureType": "road.arterial",
    "elementType": "all",
    "stylers": [{
      "lightness": "30"
    }]
  }, {
    "featureType": "road.local",
    "elementType": "all",
    "stylers": [{
      "lightness": "40"
    }]
  }, {
    "featureType": "transit",
    "elementType": "all",
    "stylers": [{
      "saturation": -100
    }, {
      "visibility": "simplified"
    }]
  }, {
    "featureType": "water",
    "elementType": "geometry",
    "stylers": [{
      "hue": "#ffff00"
    }, {
      "lightness": -20
    }, {
      "saturation": -97
    }]
  }, {
    "featureType": "water",
    "elementType": "labels",
    "stylers": [{
      "lightness": -25
    }, {
      "saturation": -100
    }]
  }];
  var map = new google.maps.Map(document.getElementById('map'), {
    center: myLatLng,
    scrollwheel: false,
    // Apply the map style array to the map.
    styles: styleArray,
    zoom: 9
  });
  var directionsDisplay = new google.maps.DirectionsRenderer({
    map: map
  }); // Create a marker and set its position.

  marker = new google.maps.Marker({
    map: map,
    icon: image,
    draggable: true,
    animation: google.maps.Animation.DROP,
    position: myLatLng
  });
  marker.addListener('click', toggleBounce);
}

function toggleBounce() {
  if (marker.getAnimation() !== null) {
    marker.setAnimation(null);
  } else {
    marker.setAnimation(google.maps.Animation.BOUNCE);
  }
}

/***/ }),

/***/ "./resources/asset/js/data/Account.js":
/*!********************************************!*\
  !*** ./resources/asset/js/data/Account.js ***!
  \********************************************/
/*! exports provided: Account */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Account", function() { return Account; });


var Account = [{
  form: '3-col',
  label: 'Create an account:',
  unique: 'createAccount',
  options: {
    label: ['Password', 'Confirm password', 'Secret word'],
    attribute: ['password', 'confirm_password', 'secretWord'],
    placeholder: ['xxxx', 'xxxx', 'one time security code'],
    type: ['password', 'password', 'password']
  }
}];

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
  form: 'Contact Information',
  label: 'Home Address',
  attribute: 'address',
  placeholder: 'Please, enter your first line of address',
  type: 'text',
  inputType: 'NORMAL_INPUT'
}, {
  form: '2-col',
  unique: 'region',
  label: "Area Code and Region",
  options: {
    label: ["Postcode /zip code/area code", "Region / State / District"],
    attribute: ['postcode', 'region'],
    placeholder: ['SN2 3BF / 234', 'London / Lagos state / New York'],
    type: ['text', 'text']
  }
}, {
  form: '2-col',
  unique: "contact",
  label: "How to reach you",
  options: {
    label: ["Email", "Mobile Number"],
    attribute: ['email', 'mobile'],
    placeholder: ['toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
    type: ['email', 'number']
  }
}, {
  label: 'Country',
  attribute: 'country',
  placeholder: null,
  type: 'select',
  options: ['Nigeria', 'UK', 'Canada', 'Europe', 'USA', 'China', 'Asia', 'Latin America'],
  inputType: 'SELECT'
} // {
//    label: 'mobile number',
//    attribute: 'mobile',
//    placeholder: 'include the area code - 234 or 1 or 44 ',
//    type: 'text'
// }
];

/***/ }),

/***/ "./resources/asset/js/data/Interest.js":
/*!*********************************************!*\
  !*** ./resources/asset/js/data/Interest.js ***!
  \*********************************************/
/*! exports provided: Interest */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Interest", function() { return Interest; });


var Interest = [{
  form: '3-col',
  label: 'Interest:',
  unique: 'interests',
  options: {
    label: ['Favourite Sport', 'Football team', 'Passion'],
    attribute: ['favSport', 'footballTeam', 'passion'],
    placeholder: ['Football, Tennis, F1', 'Chelsea, Liverpool', 'singing or tech or travelling'],
    type: ['text', 'text', 'text']
  }
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
  form: '',
  label: 'Email',
  attribute: 'email',
  placeholder: 'family@email.com',
  type: "text",
  inputType: 'NORMAL_INPUT'
}, {
  form: '',
  label: 'password',
  attribute: 'password',
  placeholder: 'SN55DE',
  type: 'password',
  inputType: 'NORMAL_INPUT'
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
  form: '3-col',
  label: 'Personal Information:',
  unique: "personal",
  options: {
    label: ['First Name', 'Surname', 'Alias'],
    attribute: ['firstName', 'lastName', 'alias'],
    placeholder: ['Wale', 'Olaogun', 'Modernman'],
    type: ['text', 'text', 'text']
  }
}, {
  label: "Are you married?",
  attribute: 'maritalStatus',
  type: 'select',
  options: ["Yes", "No"],
  inputType: 'SELECT'
}, {
  form: '2-col',
  unique: 'spouse',
  label: "Spouse's Details",
  options: {
    label: ["Spouse's name", "Spouse's mobile"],
    attribute: ['spouseName', 'spouseMobile'],
    placeholder: ["wife/husband's fullname", '23480364168089'],
    type: ['text', 'number']
  }
}, {
  form: '2-col',
  unique: 'father',
  label: "Father's Details",
  options: {
    label: ["Father's name", "Father's mobile"],
    attribute: ['fatherName', 'fatherMobile'],
    placeholder: ['Toyin Olaogun', '23480364168089'],
    type: ['text', 'number']
  }
}, {
  form: '3-col',
  label: "Mother's Details",
  unique: "mother",
  options: {
    label: ["Mother's name", "Mother's mobile", "Mother's maiden name"],
    attribute: ['motherName', 'motherMobile', 'motherMaiden'],
    placeholder: ['Toyin', '23480364168089', "surname before marriage"],
    type: ['text', 'number', 'text']
  }
}, {
  label: "Number of children",
  attribute: 'kids',
  type: 'select',
  options: [0, 1, 2, 3, 4, 5, 6],
  inputType: 'SELECT'
}, {
  label: "Gender",
  attribute: 'gender',
  type: 'select',
  options: ['Male', 'Female'],
  inputType: 'SELECT'
}, {
  label: "Number of siblings (Brothers/Sisters)",
  attribute: 'noSiblings',
  type: 'select',
  options: [0, 1, 2, 3, 4, 5, 6],
  inputType: 'SELECT'
}, {
  label: "Please, upload your picture",
  attribute: 'profileImage',
  type: 'file',
  inputType: 'FILE'
}, {
  form: '3-col',
  label: 'Date of Birth:',
  unique: "birth",
  options: {
    label: ['Day', 'Month', 'Year'],
    attribute: ['day', 'month', 'year'],
    placeholder: ['15', 'July', '1982'],
    type: ['number', 'text', 'number']
  }
}];

/***/ }),

/***/ "./resources/asset/js/data/Work.js":
/*!*****************************************!*\
  !*** ./resources/asset/js/data/Work.js ***!
  \*****************************************/
/*! exports provided: Work */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Work", function() { return Work; });


var Work = [{
  form: "Work details",
  label: 'employment status',
  attribute: 'employmentStatus',
  placeholder: null,
  options: ['Self-employed', 'Unemployed', 'Full-time-employment', 'Student'],
  inputType: 'SELECT'
}, {
  form: "",
  label: 'Occupation:',
  attribute: 'occupation',
  placeholder: 'Accountant, Housewife, Student, Business man etc',
  type: 'text',
  inputType: 'NORMAL_INPUT'
}];

/***/ }),

/***/ "./resources/asset/js/data/dataToCheck.js":
/*!************************************************!*\
  !*** ./resources/asset/js/data/dataToCheck.js ***!
  \************************************************/
/*! exports provided: dataToCheck */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "dataToCheck", function() { return dataToCheck; });


var dataToCheck = {
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
/* harmony import */ var _components_register_index__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/register/index */ "./resources/asset/js/components/register/index.js");
/* harmony import */ var _components_login_index__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/login/index */ "./resources/asset/js/components/login/index.js");
/* harmony import */ var _components_small_Input__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/small-Input */ "./resources/asset/js/components/small-Input.js");
/* harmony import */ var _components_register_processForm__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/register/processForm */ "./resources/asset/js/components/register/processForm.js");
/* harmony import */ var _components_register_modal_kids__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/register/modal/kids */ "./resources/asset/js/components/register/modal/kids.js");
/* harmony import */ var _components_register_modal_siblings__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./components/register/modal/siblings */ "./resources/asset/js/components/register/modal/siblings.js");
/* harmony import */ var _cust_main__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./cust/main */ "./resources/asset/js/cust/main.js");
/* harmony import */ var _cust_main__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_cust_main__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _components_profilePage__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./components/profilePage */ "./resources/asset/js/components/profilePage.js");
/* harmony import */ var _components_profilePage__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(_components_profilePage__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var _components_FilePreview__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./components/FilePreview */ "./resources/asset/js/components/FilePreview.js");
/* harmony import */ var _components_organogram__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./components/organogram */ "./resources/asset/js/components/organogram.js");
/* harmony import */ var _components_modal_profile__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./components/modal/profile */ "./resources/asset/js/components/modal/profile.js");
/* harmony import */ var _components_hidden__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./components/hidden */ "./resources/asset/js/components/hidden.js");












 // if (id('profilePage')) {
//     import("./components/modal/profile")
//     .then(result => result);
// } else if (id('registration')) {
//     import ('./components/small-Input')
//     .then(result => result)
// }

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

/******/ });