(window["sb-chart-block"]=window["sb-chart-block"]||[]).push([[1],{7:function(e,t,n){}}]),function(e){function t(t){for(var o,c,i=t[0],u=t[1],a=t[2],p=0,d=[];p<i.length;p++)c=i[p],Object.prototype.hasOwnProperty.call(r,c)&&r[c]&&d.push(r[c][0]),r[c]=0;for(o in u)Object.prototype.hasOwnProperty.call(u,o)&&(e[o]=u[o]);for(s&&s(t);d.length;)d.shift()();return l.push.apply(l,a||[]),n()}function n(){for(var e,t=0;t<l.length;t++){for(var n=l[t],o=!0,i=1;i<n.length;i++){var u=n[i];0!==r[u]&&(o=!1)}o&&(l.splice(t--,1),e=c(c.s=n[0]))}return e}var o={},r={0:0},l=[];function c(t){if(o[t])return o[t].exports;var n=o[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,c),n.l=!0,n.exports}c.m=e,c.c=o,c.d=function(e,t,n){c.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},c.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},c.t=function(e,t){if(1&t&&(e=c(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(c.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)c.d(n,o,function(t){return e[t]}.bind(null,o));return n},c.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return c.d(t,"a",t),t},c.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},c.p="";var i=window["sb-chart-block"]=window["sb-chart-block"]||[],u=i.push.bind(i);i.push=t,i=i.slice();for(var a=0;a<i.length;a++)t(i[a]);var s=u;l.push([10,1]),n()}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=window.wp.i18n},function(e,t){e.exports=window.wp.components},function(e,t){e.exports=window.wp.blockEditor},function(e,t){e.exports=window.wp.blocks},function(e,t){e.exports=window.wp.editor},function(e,t){e.exports=window.wp.compose},,function(e,t){e.exports=window.lodash},function(e,t,n){},function(e,t,n){"use strict";n.r(t);var o=n(4),r=n(1),l=(n(7),n(0)),c=n(5),i=n(3),u=n(2),a=(n(8),n(6));n(9);var s=Object(a.withInstanceId)((function(e){var t=e.attributes,n=(e.className,e.isSelected,e.setAttributes);return e.instanceId,Object(l.createElement)(l.Fragment,null,Object(l.createElement)(i.InspectorControls,null,Object(l.createElement)(u.PanelBody,null,Object(l.createElement)(u.PanelRow,null)),Object(l.createElement)(u.PanelBody,null,Object(l.createElement)(u.PanelRow,null,Object(l.createElement)(u.RangeControl,{label:Object(r.__)("Limit","wp-top12"),value:t.limit,initialPosition:12,onChange:function(e){n({limit:e})},min:1,max:1e3,allowReset:!0})))),Object(l.createElement)("div",{className:"wp-block-wp-top12"},Object(l.createElement)(i.PlainText,{value:t.includes,placeholder:Object(r.__)("Enter include strings"),onChange:function(e){n({includes:e})}}),Object(l.createElement)(i.PlainText,{value:t.excludes,placeholder:Object(r.__)("Enter exclude strings"),onChange:function(e){n({excludes:e})}}),Object(l.createElement)(i.PlainText,{value:t.slugs,placeholder:Object(r.__)("Enter slugs"),onChange:function(e){n({slugs:e})}}),Object(l.createElement)(c.ServerSideRender,{block:"wp-top12/wp-top12",attributes:t})))}));Object(o.registerBlockType)("wp-top12/wp-top12",{title:Object(r.__)("Top plugins","wp-top12"),description:Object(r.__)("Displays the top 12 plugins.","wp-top12"),category:"widgets",icon:"admin-plugins",keywords:[Object(r.__)("Plugins","wp-top12"),Object(r.__)("Top n","wp-top12")],supports:{html:!1,align:!1},attributes:{includes:{type:"string",default:""},excludes:{type:"string",default:""},slugs:{type:"string",default:""},limit:{type:"integer",default:12}},edit:s,save:function(e){return e.attributes,console.log("Save()"),null}})}]);