!function(e){var t={};function o(r){if(t[r])return t[r].exports;var n=t[r]={i:r,l:!1,exports:{}};return e[r].call(n.exports,n,n.exports,o),n.l=!0,n.exports}o.m=e,o.c=t,o.d=function(e,t,r){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},o.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(e,t){if(1&t&&(e=o(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(o.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)o.d(r,n,function(t){return e[t]}.bind(null,n));return r},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="",o(o.s=2)}([function(e,t){function o(t){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?(e.exports=o=function(e){return typeof e},e.exports.default=e.exports,e.exports.__esModule=!0):(e.exports=o=function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e.exports.default=e.exports,e.exports.__esModule=!0),o(t)}e.exports=o,e.exports.default=e.exports,e.exports.__esModule=!0},function(e,t,o){"use strict";var r=o(0),n=o.n(r),u={ini:function(){u.subBrokenImgs(),jQuery("body").bind("DOMSubtreeModified",(function(){u.subBrokenImgs()}))},subBrokenImgs:function(){jQuery('img[src=""]:not(.placehodor-processed)').each((function(){jQuery(this).attr("src",placehodor.sub_url),jQuery(this).addClass(".placehodor-processed")})),document.querySelectorAll("img").forEach((function(e){e.classList&&!e.classList.contains("placehodor-processed")&&(e.classList.add("placehodor-processed"),e.onerror=function(){var e=this.src.match(/([0-9]+)x([0-9]+)/);if(e&&"object"===n()(e)&&e.length>=3){var t=parseInt(e[1]),o=parseInt(e[2]),r=placehodor.sub_url.split(".");if(r&&"object"===n()(r)&&r.length>0){var u=r[r.length-1],c=placehodor.sub_url.replace(".".concat(u),"");c="".concat(c,"-").concat(t,"x").concat(o,".").concat(u),this.src=c}}else this.src=placehodor.sub_url})}))}};t.a=u},function(e,t,o){"use strict";o.r(t),o(3);var r=o(1);jQuery((function(){r.a.ini()}))},function(e,t,o){}]);