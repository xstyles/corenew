parcelRequire=function(e,r,t,n){var i,o="function"==typeof parcelRequire&&parcelRequire,u="function"==typeof require&&require;function f(t,n){if(!r[t]){if(!e[t]){var i="function"==typeof parcelRequire&&parcelRequire;if(!n&&i)return i(t,!0);if(o)return o(t,!0);if(u&&"string"==typeof t)return u(t);var c=new Error("Cannot find module '"+t+"'");throw c.code="MODULE_NOT_FOUND",c}p.resolve=function(r){return e[t][1][r]||r},p.cache={};var l=r[t]=new f.Module(t);e[t][0].call(l.exports,p,l,l.exports,this)}return r[t].exports;function p(e){return f(p.resolve(e))}}f.isParcelRequire=!0,f.Module=function(e){this.id=e,this.bundle=f,this.exports={}},f.modules=e,f.cache=r,f.parent=o,f.register=function(r,t){e[r]=[function(e,r){r.exports=t},{}]};for(var c=0;c<t.length;c++)try{f(t[c])}catch(e){i||(i=e)}if(t.length){var l=f(t[t.length-1]);"object"==typeof exports&&"undefined"!=typeof module?module.exports=l:"function"==typeof define&&define.amd?define(function(){return l}):n&&(this[n]=l)}if(parcelRequire=f,i)throw i;return f}({"Sw79":[function(require,module,exports) {
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var e=wp,t=e.blockEditor.InspectorControls,i=e.components,s=i.Disabled,r=i.Notice,l=i.PanelBody,n=i.ToggleControl,d=e.element,o=d.Fragment,a=d.createElement,u=e.i18n.__,b=bp,p=b.blockComponents.ServerSideRender,c=b.blockData.getCurrentWidgetsSidebar,y=function(e){var i=e.attributes,d=e.setAttributes,b=e.clientId,y=i.displayTitle,g=c(b);return g&&g.id&&-1!==["sidebar-buddypress-members","sidebar-buddypress-groups"].indexOf(g.id)?a(r,{status:"error",isDismissible:!1},a("p",null,u("The BuddyPress Primary Navigation block shouldn't be used into this widget area. Please remove it.","buddypress"))):a(o,null,a(t,null,a(l,{title:u("Settings","buddypress"),initialOpen:!0},a(n,{label:u("Include navigation title","buddypress"),checked:!!y,onChange:function(){d({displayTitle:!y})}}))),a(s,null,a(p,{block:"bp/primary-nav",attributes:i})))},g=y;exports.default=g;
},{}],"uKqo":[function(require,module,exports) {
"use strict";Object.defineProperty(exports,"__esModule",{value:!0}),exports.default=void 0;var e=wp,t=e.blocks.createBlock,r={from:[{type:"block",blocks:["core/legacy-widget"],isMatch:function(e){var t=e.idBase,r=e.instance;return!(null==r||!r.raw)&&"bp_nouveau_sidebar_object_nav_widget"===t},transform:function(e){var r=e.instance;return t("bp/primary-nav",{displayTitle:r.raw.bp_nouveau_widget_title})}}]},a=r;exports.default=a;
},{}],"ygAa":[function(require,module,exports) {
"use strict";var r=i(require("./primary-nav/edit")),e=i(require("./primary-nav/transforms"));function i(r){return r&&r.__esModule?r:{default:r}}var t=wp,s=t.blocks.registerBlockType,a=t.i18n.__;s("bp/primary-nav",{title:a("Primary navigation","buddypress"),description:a("Displays BuddyPress primary nav in the sidebar of your site. Make sure to use it as the first widget of the sidebar and only once.","buddypress"),icon:{background:"#fff",foreground:"#d84800",src:"buddicons-community"},category:"buddypress",attributes:{displayTitle:{type:"boolean",default:!0}},edit:r.default,transforms:e.default});
},{"./primary-nav/edit":"Sw79","./primary-nav/transforms":"uKqo"}]},{},["ygAa"], null)
//# sourceMappingURL=/bp-core/js/blocks/primary-nav.js.map