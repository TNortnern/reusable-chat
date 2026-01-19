var ReusableChat=function(e){"use strict";
/**
   * @license
   * Copyright 2019 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */var t;const s=globalThis,i=s.ShadowRoot&&(void 0===s.ShadyCSS||s.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,n=Symbol(),a=new WeakMap;let r=class{constructor(e,t,s){if(this._$cssResult$=!0,s!==n)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o;const t=this.t;if(i&&void 0===e){const s=void 0!==t&&1===t.length;s&&(e=a.get(t)),void 0===e&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),s&&a.set(t,e))}return e}toString(){return this.cssText}};const o=e=>new r("string"==typeof e?e:e+"",void 0,n),l=(e,...t)=>{const s=1===e.length?e[0]:t.reduce((t,s,i)=>t+(e=>{if(!0===e._$cssResult$)return e.cssText;if("number"==typeof e)return e;throw Error("Value passed to 'css' function must be a 'css' function result: "+e+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(s)+e[i+1],e[0]);return new r(s,e,n)},c=i?e=>e:e=>e instanceof CSSStyleSheet?(e=>{let t="";for(const s of e.cssRules)t+=s.cssText;return o(t)})(e):e,{is:d,defineProperty:h,getOwnPropertyDescriptor:p,getOwnPropertyNames:u,getOwnPropertySymbols:g,getPrototypeOf:m}=Object,v=globalThis,y=v.trustedTypes,b=y?y.emptyScript:"",f=v.reactiveElementPolyfillSupport,x=(e,t)=>e,w={toAttribute(e,t){switch(t){case Boolean:e=e?b:null;break;case Object:case Array:e=null==e?e:JSON.stringify(e)}return e},fromAttribute(e,t){let s=e;switch(t){case Boolean:s=null!==e;break;case Number:s=null===e?null:Number(e);break;case Object:case Array:try{s=JSON.parse(e)}catch(i){s=null}}return s}},$=(e,t)=>!d(e,t),C={attribute:!0,type:String,converter:w,reflect:!1,useDefault:!1,hasChanged:$};
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */Symbol.metadata??(Symbol.metadata=Symbol("metadata")),v.litPropertyMetadata??(v.litPropertyMetadata=new WeakMap);let k=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=C){if(t.state&&(t.attribute=!1),this._$Ei(),this.prototype.hasOwnProperty(e)&&((t=Object.create(t)).wrapped=!0),this.elementProperties.set(e,t),!t.noAccessor){const s=Symbol(),i=this.getPropertyDescriptor(e,s,t);void 0!==i&&h(this.prototype,e,i)}}static getPropertyDescriptor(e,t,s){const{get:i,set:n}=p(this.prototype,e)??{get(){return this[t]},set(e){this[t]=e}};return{get:i,set(t){const a=null==i?void 0:i.call(this);null==n||n.call(this,t),this.requestUpdate(e,a,s)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??C}static _$Ei(){if(this.hasOwnProperty(x("elementProperties")))return;const e=m(this);e.finalize(),void 0!==e.l&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(x("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(x("properties"))){const e=this.properties,t=[...u(e),...g(e)];for(const s of t)this.createProperty(s,e[s])}const e=this[Symbol.metadata];if(null!==e){const t=litPropertyMetadata.get(e);if(void 0!==t)for(const[e,s]of t)this.elementProperties.set(e,s)}this._$Eh=new Map;for(const[t,s]of this.elementProperties){const e=this._$Eu(t,s);void 0!==e&&this._$Eh.set(e,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){const t=[];if(Array.isArray(e)){const s=new Set(e.flat(1/0).reverse());for(const e of s)t.unshift(c(e))}else void 0!==e&&t.push(c(e));return t}static _$Eu(e,t){const s=t.attribute;return!1===s?void 0:"string"==typeof s?s:"string"==typeof e?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){var e;this._$ES=new Promise(e=>this.enableUpdating=e),this._$AL=new Map,this._$E_(),this.requestUpdate(),null==(e=this.constructor.l)||e.forEach(e=>e(this))}addController(e){var t;(this._$EO??(this._$EO=new Set)).add(e),void 0!==this.renderRoot&&this.isConnected&&(null==(t=e.hostConnected)||t.call(e))}removeController(e){var t;null==(t=this._$EO)||t.delete(e)}_$E_(){const e=new Map,t=this.constructor.elementProperties;for(const s of t.keys())this.hasOwnProperty(s)&&(e.set(s,this[s]),delete this[s]);e.size>0&&(this._$Ep=e)}createRenderRoot(){const e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return((e,t)=>{if(i)e.adoptedStyleSheets=t.map(e=>e instanceof CSSStyleSheet?e:e.styleSheet);else for(const i of t){const t=document.createElement("style"),n=s.litNonce;void 0!==n&&t.setAttribute("nonce",n),t.textContent=i.cssText,e.appendChild(t)}})(e,this.constructor.elementStyles),e}connectedCallback(){var e;this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),null==(e=this._$EO)||e.forEach(e=>{var t;return null==(t=e.hostConnected)?void 0:t.call(e)})}enableUpdating(e){}disconnectedCallback(){var e;null==(e=this._$EO)||e.forEach(e=>{var t;return null==(t=e.hostDisconnected)?void 0:t.call(e)})}attributeChangedCallback(e,t,s){this._$AK(e,s)}_$ET(e,t){var s;const i=this.constructor.elementProperties.get(e),n=this.constructor._$Eu(e,i);if(void 0!==n&&!0===i.reflect){const a=(void 0!==(null==(s=i.converter)?void 0:s.toAttribute)?i.converter:w).toAttribute(t,i.type);this._$Em=e,null==a?this.removeAttribute(n):this.setAttribute(n,a),this._$Em=null}}_$AK(e,t){var s,i;const n=this.constructor,a=n._$Eh.get(e);if(void 0!==a&&this._$Em!==a){const e=n.getPropertyOptions(a),r="function"==typeof e.converter?{fromAttribute:e.converter}:void 0!==(null==(s=e.converter)?void 0:s.fromAttribute)?e.converter:w;this._$Em=a;const o=r.fromAttribute(t,e.type);this[a]=o??(null==(i=this._$Ej)?void 0:i.get(a))??o,this._$Em=null}}requestUpdate(e,t,s,i=!1,n){var a;if(void 0!==e){const r=this.constructor;if(!1===i&&(n=this[e]),s??(s=r.getPropertyOptions(e)),!((s.hasChanged??$)(n,t)||s.useDefault&&s.reflect&&n===(null==(a=this._$Ej)?void 0:a.get(e))&&!this.hasAttribute(r._$Eu(e,s))))return;this.C(e,t,s)}!1===this.isUpdatePending&&(this._$ES=this._$EP())}C(e,t,{useDefault:s,reflect:i,wrapped:n},a){s&&!(this._$Ej??(this._$Ej=new Map)).has(e)&&(this._$Ej.set(e,a??t??this[e]),!0!==n||void 0!==a)||(this._$AL.has(e)||(this.hasUpdated||s||(t=void 0),this._$AL.set(e,t)),!0===i&&this._$Em!==e&&(this._$Eq??(this._$Eq=new Set)).add(e))}async _$EP(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}const e=this.scheduleUpdate();return null!=e&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){var e;if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(const[e,t]of this._$Ep)this[e]=t;this._$Ep=void 0}const e=this.constructor.elementProperties;if(e.size>0)for(const[t,s]of e){const{wrapped:e}=s,i=this[t];!0!==e||this._$AL.has(t)||void 0===i||this.C(t,void 0,s,i)}}let t=!1;const s=this._$AL;try{t=this.shouldUpdate(s),t?(this.willUpdate(s),null==(e=this._$EO)||e.forEach(e=>{var t;return null==(t=e.hostUpdate)?void 0:t.call(e)}),this.update(s)):this._$EM()}catch(i){throw t=!1,this._$EM(),i}t&&this._$AE(s)}willUpdate(e){}_$AE(e){var t;null==(t=this._$EO)||t.forEach(e=>{var t;return null==(t=e.hostUpdated)?void 0:t.call(e)}),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EM(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Eq&&(this._$Eq=this._$Eq.forEach(e=>this._$ET(e,this[e]))),this._$EM()}updated(e){}firstUpdated(e){}};k.elementStyles=[],k.shadowRootOptions={mode:"open"},k[x("elementProperties")]=new Map,k[x("finalized")]=new Map,null==f||f({ReactiveElement:k}),(v.reactiveElementVersions??(v.reactiveElementVersions=[])).push("2.1.2");
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
const S=globalThis,_=e=>e,I=S.trustedTypes,A=I?I.createPolicy("lit-html",{createHTML:e=>e}):void 0,E="$lit$",R=`lit$${Math.random().toFixed(9).slice(2)}$`,T="?"+R,O=`<${T}>`,M=document,U=()=>M.createComment(""),z=e=>null===e||"object"!=typeof e&&"function"!=typeof e,P=Array.isArray,N="[ \t\n\f\r]",L=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,j=/-->/g,D=/>/g,B=RegExp(`>|${N}(?:([^\\s"'>=/]+)(${N}*=${N}*(?:[^ \t\n\f\r"'\`<>=]|("|')|))|$)`,"g"),H=/'/g,W=/"/g,q=/^(?:script|style|textarea|title)$/i,F=(G=1,(e,...t)=>({_$litType$:G,strings:e,values:t})),K=Symbol.for("lit-noChange"),J=Symbol.for("lit-nothing"),V=new WeakMap,Y=M.createTreeWalker(M,129);var G;function Z(e,t){if(!P(e)||!e.hasOwnProperty("raw"))throw Error("invalid template strings array");return void 0!==A?A.createHTML(t):t}class X{constructor({strings:e,_$litType$:t},s){let i;this.parts=[];let n=0,a=0;const r=e.length-1,o=this.parts,[l,c]=((e,t)=>{const s=e.length-1,i=[];let n,a=2===t?"<svg>":3===t?"<math>":"",r=L;for(let o=0;o<s;o++){const t=e[o];let s,l,c=-1,d=0;for(;d<t.length&&(r.lastIndex=d,l=r.exec(t),null!==l);)d=r.lastIndex,r===L?"!--"===l[1]?r=j:void 0!==l[1]?r=D:void 0!==l[2]?(q.test(l[2])&&(n=RegExp("</"+l[2],"g")),r=B):void 0!==l[3]&&(r=B):r===B?">"===l[0]?(r=n??L,c=-1):void 0===l[1]?c=-2:(c=r.lastIndex-l[2].length,s=l[1],r=void 0===l[3]?B:'"'===l[3]?W:H):r===W||r===H?r=B:r===j||r===D?r=L:(r=B,n=void 0);const h=r===B&&e[o+1].startsWith("/>")?" ":"";a+=r===L?t+O:c>=0?(i.push(s),t.slice(0,c)+E+t.slice(c)+R+h):t+R+(-2===c?o:h)}return[Z(e,a+(e[s]||"<?>")+(2===t?"</svg>":3===t?"</math>":"")),i]})(e,t);if(this.el=X.createElement(l,s),Y.currentNode=this.el.content,2===t||3===t){const e=this.el.content.firstChild;e.replaceWith(...e.childNodes)}for(;null!==(i=Y.nextNode())&&o.length<r;){if(1===i.nodeType){if(i.hasAttributes())for(const e of i.getAttributeNames())if(e.endsWith(E)){const t=c[a++],s=i.getAttribute(e).split(R),r=/([.?@])?(.*)/.exec(t);o.push({type:1,index:n,name:r[2],strings:s,ctor:"."===r[1]?ie:"?"===r[1]?ne:"@"===r[1]?ae:se}),i.removeAttribute(e)}else e.startsWith(R)&&(o.push({type:6,index:n}),i.removeAttribute(e));if(q.test(i.tagName)){const e=i.textContent.split(R),t=e.length-1;if(t>0){i.textContent=I?I.emptyScript:"";for(let s=0;s<t;s++)i.append(e[s],U()),Y.nextNode(),o.push({type:2,index:++n});i.append(e[t],U())}}}else if(8===i.nodeType)if(i.data===T)o.push({type:2,index:n});else{let e=-1;for(;-1!==(e=i.data.indexOf(R,e+1));)o.push({type:7,index:n}),e+=R.length-1}n++}}static createElement(e,t){const s=M.createElement("template");return s.innerHTML=e,s}}function Q(e,t,s=e,i){var n,a;if(t===K)return t;let r=void 0!==i?null==(n=s._$Co)?void 0:n[i]:s._$Cl;const o=z(t)?void 0:t._$litDirective$;return(null==r?void 0:r.constructor)!==o&&(null==(a=null==r?void 0:r._$AO)||a.call(r,!1),void 0===o?r=void 0:(r=new o(e),r._$AT(e,s,i)),void 0!==i?(s._$Co??(s._$Co=[]))[i]=r:s._$Cl=r),void 0!==r&&(t=Q(e,r._$AS(e,t.values),r,i)),t}class ee{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){const{el:{content:t},parts:s}=this._$AD,i=((null==e?void 0:e.creationScope)??M).importNode(t,!0);Y.currentNode=i;let n=Y.nextNode(),a=0,r=0,o=s[0];for(;void 0!==o;){if(a===o.index){let t;2===o.type?t=new te(n,n.nextSibling,this,e):1===o.type?t=new o.ctor(n,o.name,o.strings,this,e):6===o.type&&(t=new re(n,this,e)),this._$AV.push(t),o=s[++r]}a!==(null==o?void 0:o.index)&&(n=Y.nextNode(),a++)}return Y.currentNode=M,i}p(e){let t=0;for(const s of this._$AV)void 0!==s&&(void 0!==s.strings?(s._$AI(e,s,t),t+=s.strings.length-2):s._$AI(e[t])),t++}}class te{get _$AU(){var e;return(null==(e=this._$AM)?void 0:e._$AU)??this._$Cv}constructor(e,t,s,i){this.type=2,this._$AH=J,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=s,this.options=i,this._$Cv=(null==i?void 0:i.isConnected)??!0}get parentNode(){let e=this._$AA.parentNode;const t=this._$AM;return void 0!==t&&11===(null==e?void 0:e.nodeType)&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=Q(this,e,t),z(e)?e===J||null==e||""===e?(this._$AH!==J&&this._$AR(),this._$AH=J):e!==this._$AH&&e!==K&&this._(e):void 0!==e._$litType$?this.$(e):void 0!==e.nodeType?this.T(e):(e=>P(e)||"function"==typeof(null==e?void 0:e[Symbol.iterator]))(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==J&&z(this._$AH)?this._$AA.nextSibling.data=e:this.T(M.createTextNode(e)),this._$AH=e}$(e){var t;const{values:s,_$litType$:i}=e,n="number"==typeof i?this._$AC(e):(void 0===i.el&&(i.el=X.createElement(Z(i.h,i.h[0]),this.options)),i);if((null==(t=this._$AH)?void 0:t._$AD)===n)this._$AH.p(s);else{const e=new ee(n,this),t=e.u(this.options);e.p(s),this.T(t),this._$AH=e}}_$AC(e){let t=V.get(e.strings);return void 0===t&&V.set(e.strings,t=new X(e)),t}k(e){P(this._$AH)||(this._$AH=[],this._$AR());const t=this._$AH;let s,i=0;for(const n of e)i===t.length?t.push(s=new te(this.O(U()),this.O(U()),this,this.options)):s=t[i],s._$AI(n),i++;i<t.length&&(this._$AR(s&&s._$AB.nextSibling,i),t.length=i)}_$AR(e=this._$AA.nextSibling,t){var s;for(null==(s=this._$AP)||s.call(this,!1,!0,t);e!==this._$AB;){const t=_(e).nextSibling;_(e).remove(),e=t}}setConnected(e){var t;void 0===this._$AM&&(this._$Cv=e,null==(t=this._$AP)||t.call(this,e))}}class se{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,s,i,n){this.type=1,this._$AH=J,this._$AN=void 0,this.element=e,this.name=t,this._$AM=i,this.options=n,s.length>2||""!==s[0]||""!==s[1]?(this._$AH=Array(s.length-1).fill(new String),this.strings=s):this._$AH=J}_$AI(e,t=this,s,i){const n=this.strings;let a=!1;if(void 0===n)e=Q(this,e,t,0),a=!z(e)||e!==this._$AH&&e!==K,a&&(this._$AH=e);else{const i=e;let r,o;for(e=n[0],r=0;r<n.length-1;r++)o=Q(this,i[s+r],t,r),o===K&&(o=this._$AH[r]),a||(a=!z(o)||o!==this._$AH[r]),o===J?e=J:e!==J&&(e+=(o??"")+n[r+1]),this._$AH[r]=o}a&&!i&&this.j(e)}j(e){e===J?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}}class ie extends se{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===J?void 0:e}}class ne extends se{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==J)}}class ae extends se{constructor(e,t,s,i,n){super(e,t,s,i,n),this.type=5}_$AI(e,t=this){if((e=Q(this,e,t,0)??J)===K)return;const s=this._$AH,i=e===J&&s!==J||e.capture!==s.capture||e.once!==s.once||e.passive!==s.passive,n=e!==J&&(s===J||i);i&&this.element.removeEventListener(this.name,this,s),n&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){var t;"function"==typeof this._$AH?this._$AH.call((null==(t=this.options)?void 0:t.host)??this.element,e):this._$AH.handleEvent(e)}}class re{constructor(e,t,s){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=s}get _$AU(){return this._$AM._$AU}_$AI(e){Q(this,e)}}const oe=S.litHtmlPolyfillSupport;null==oe||oe(X,te),(S.litHtmlVersions??(S.litHtmlVersions=[])).push("3.3.2");const le=globalThis;
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */class ce extends k{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var e;const t=super.createRenderRoot();return(e=this.renderOptions).renderBefore??(e.renderBefore=t.firstChild),t}update(e){const t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=((e,t,s)=>{const i=(null==s?void 0:s.renderBefore)??t;let n=i._$litPart$;if(void 0===n){const e=(null==s?void 0:s.renderBefore)??null;i._$litPart$=n=new te(t.insertBefore(U(),e),e,void 0,s??{})}return n._$AI(e),n})(t,this.renderRoot,this.renderOptions)}connectedCallback(){var e;super.connectedCallback(),null==(e=this._$Do)||e.setConnected(!0)}disconnectedCallback(){var e;super.disconnectedCallback(),null==(e=this._$Do)||e.setConnected(!1)}render(){return K}}ce._$litElement$=!0,ce.finalized=!0,null==(t=le.litElementHydrateSupport)||t.call(le,{LitElement:ce});const de=le.litElementPolyfillSupport;null==de||de({LitElement:ce}),(le.litElementVersions??(le.litElementVersions=[])).push("4.2.2");
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
const he=e=>(t,s)=>{void 0!==s?s.addInitializer(()=>{customElements.define(e,t)}):customElements.define(e,t)},pe={attribute:!0,type:String,converter:w,reflect:!1,hasChanged:$},ue=(e=pe,t,s)=>{const{kind:i,metadata:n}=s;let a=globalThis.litPropertyMetadata.get(n);if(void 0===a&&globalThis.litPropertyMetadata.set(n,a=new Map),"setter"===i&&((e=Object.create(e)).wrapped=!0),a.set(s.name,e),"accessor"===i){const{name:i}=s;return{set(s){const n=t.get.call(this);t.set.call(this,s),this.requestUpdate(i,n,e,!0,s)},init(t){return void 0!==t&&this.C(i,void 0,e,t),t}}}if("setter"===i){const{name:i}=s;return function(s){const n=this[i];t.call(this,s),this.requestUpdate(i,n,e,!0,s)}}throw Error("Unsupported decorator location: "+i)};
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */function ge(e){return(t,s)=>"object"==typeof s?ue(e,t,s):((e,t,s)=>{const i=t.hasOwnProperty(s);return t.constructor.createProperty(s,e),i?Object.getOwnPropertyDescriptor(t,s):void 0})(e,t,s)}
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */function me(e){return ge({...e,state:!0,attribute:!1})}
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
/**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   */
function ve(e,t){return(t,s,i)=>((e,t,s)=>(s.configurable=!0,s.enumerable=!0,Reflect.decorate&&"object"!=typeof t&&Object.defineProperty(e,t,s),s))(t,s,{get(){return(t=>{var s;return(null==(s=t.renderRoot)?void 0:s.querySelector(e))??null})(this)}})}class ye{constructor(e,t){this.sessionToken=null,this.baseUrl=e.replace(/\/$/,""),this.apiKey=t}setSessionToken(e){this.sessionToken=e}clearSessionToken(){this.sessionToken=null}getSessionToken(){return this.sessionToken}async request(e,t={}){const s={"Content-Type":"application/json",Accept:"application/json",...t.headers||{}};this.sessionToken&&(s.Authorization=`Bearer ${this.sessionToken}`);const i=await fetch(`${this.baseUrl}${e}`,{...t,headers:s});if(!i.ok){const e=await i.json().catch(()=>({message:"Request failed"}));throw{message:e.message||e.error||"Request failed",errors:e.errors,status:i.status}}const n=await i.text();return n?JSON.parse(n):{}}async init(){return this.request("/api/embed/init",{method:"POST",body:JSON.stringify({key:this.apiKey})})}async createSession(e,t,s,i){return this.request("/api/embed/session",{method:"POST",body:JSON.stringify({key:this.apiKey,user_id:e,user_name:t,user_email:s,user_avatar:i})})}async getConversations(e=1,t){const s=new URLSearchParams({page:String(e)});return t&&s.set("type",t),this.request(`/api/widget/conversations?${s}`)}async getConversation(e){return this.request(`/api/widget/conversations/${e}`)}async getMessages(e,t=1){return(await this.request(`/api/widget/conversations/${e}?page=${t}`)).messages}async createConversation(e,t,s){return this.request("/api/widget/conversations",{method:"POST",body:JSON.stringify({type:e,participant_ids:t,name:s})})}async leaveConversation(e){await this.request(`/api/widget/conversations/${e}`,{method:"DELETE"})}async sendMessage(e,t,s=[]){return this.request(`/api/widget/conversations/${e}/messages`,{method:"POST",body:JSON.stringify({content:t,attachment_ids:s})})}async sendTyping(e,t){await this.request(`/api/widget/conversations/${e}/typing`,{method:"POST",body:JSON.stringify({is_typing:t})})}async markAsRead(e){await this.request(`/api/widget/conversations/${e}/read`,{method:"POST"})}async addReaction(e,t,s){await this.request(`/api/widget/conversations/${e}/messages/${t}/reactions`,{method:"POST",body:JSON.stringify({emoji:s})})}async removeReaction(e,t,s){await this.request(`/api/widget/conversations/${e}/messages/${t}/reactions/${encodeURIComponent(s)}`,{method:"DELETE"})}async reportMessage(e,t,s){await this.request(`/api/widget/conversations/${e}/messages/${t}/report`,{method:"POST",body:JSON.stringify({reason:s})})}async blockUser(e){await this.request(`/api/widget/users/${e}/block`,{method:"POST"})}async unblockUser(e){await this.request(`/api/widget/users/${e}/block`,{method:"DELETE"})}async uploadAttachment(e,t,s){return new Promise((i,n)=>{const a=new FormData;a.append("file",t);const r=new XMLHttpRequest;r.upload.addEventListener("progress",e=>{if(e.lengthComputable&&s){const t=Math.round(e.loaded/e.total*100);s(t)}}),r.addEventListener("load",()=>{if(r.status>=200&&r.status<300)try{const e=JSON.parse(r.responseText);i(e)}catch(e){n(new Error("Invalid response"))}else try{const e=JSON.parse(r.responseText);n({message:e.message||"Upload failed",status:r.status})}catch{n({message:"Upload failed",status:r.status})}}),r.addEventListener("error",()=>{n({message:"Network error",status:0})}),r.addEventListener("abort",()=>{n({message:"Upload cancelled",status:0})}),r.open("POST",`${this.baseUrl}/api/widget/conversations/${e}/attachments`),r.setRequestHeader("Authorization",`Bearer ${this.sessionToken}`),r.setRequestHeader("Accept","application/json"),r.send(a)})}async getMe(){return this.request("/api/widget/me")}async verifySession(){try{return await this.getMe(),!0}catch(e){if(401===e.status)return!1;throw e}}}class be{constructor(e,t,s){this.socket=null,this.subscribedChannels=new Set,this.pendingSubscriptions=new Set,this.messageCallbacks=new Set,this.typingCallbacks=new Set,this.connectionCallbacks=new Set,this.readReceiptCallbacks=new Set,this.reconnectAttempts=0,this.maxReconnectAttempts=10,this.baseReconnectDelay=1e3,this.socketId=null,this.isConnecting=!1,this.connectionPromise=null,this.apiUrl=e,this.sessionToken=s;const i=new URL(e),n="https:"===i.protocol||i.hostname.includes("railway.app"),a=n?"wss":"ws",r=n?443:8080,o=i.hostname;this.wsUrl=`${a}://${o}:${r}/app/${t}?protocol=7&client=js&version=8.3.0`}connect(){var e;return(null==(e=this.socket)?void 0:e.readyState)===WebSocket.OPEN?Promise.resolve():(this.isConnecting&&this.connectionPromise||(this.isConnecting=!0,this.connectionPromise=new Promise((e,t)=>{try{this.socket=new WebSocket(this.wsUrl),this.socket.onopen=()=>{console.log("[RC Widget] WebSocket connected")},this.socket.onclose=e=>{console.log("[RC Widget] WebSocket disconnected",e.code,e.reason),this.socketId=null,this.isConnecting=!1,this.subscribedChannels.clear(),this.connectionCallbacks.forEach(e=>e(!1)),1e3!==e.code&&this.attemptReconnect()},this.socket.onerror=e=>{console.error("[RC Widget] WebSocket error:",e),this.isConnecting=!1,t(e)},this.socket.onmessage=t=>{try{const s=JSON.parse(t.data);this.handleMessage(s,e)}catch(s){}}}catch(s){this.isConnecting=!1,t(s)}})),this.connectionPromise)}handleMessage(e,t){const s=e.event;switch(s){case"pusher:connection_established":const i="string"==typeof e.data?JSON.parse(e.data):e.data;this.socketId=i.socket_id,this.reconnectAttempts=0,this.isConnecting=!1,this.connectionCallbacks.forEach(e=>e(!0)),console.log("[RC Widget] Pusher connection established, socket_id:",this.socketId),this.pendingSubscriptions.forEach(e=>{this.subscribeToChannel(e)}),this.pendingSubscriptions.clear(),t&&t();break;case"pusher_internal:subscription_succeeded":const n=e.channel;this.subscribedChannels.add(n),console.log("[RC Widget] Subscribed to channel:",n);break;case"pusher:error":console.error("[RC Widget] Pusher error:",e.data);break;case"message.created":case".message.created":this.handleNewMessage(e);break;case"user.typing":case".user.typing":this.handleTypingIndicator(e);break;case"message.read":case".message.read":this.handleReadReceipt(e);break;default:if((null==s?void 0:s.startsWith("."))||(null==s?void 0:s.startsWith("App\\"))){const t=s.replace(/^\./,"").replace(/^App\\Events\\/,"");t.includes("MessageCreated")||"message.created"===t?this.handleNewMessage(e):(t.includes("UserTyping")||"user.typing"===t)&&this.handleTypingIndicator(e)}}}handleNewMessage(e){var t;const s="string"==typeof e.data?JSON.parse(e.data):e.data,i=(null==(t=e.channel)?void 0:t.replace("private-conversation.",""))||s.conversation_id||"";this.messageCallbacks.forEach(e=>e(s,i))}handleTypingIndicator(e){var t;const s="string"==typeof e.data?JSON.parse(e.data):e.data,i=(null==(t=e.channel)?void 0:t.replace("private-conversation.",""))||s.conversation_id||"";this.typingCallbacks.forEach(e=>e(s,i))}handleReadReceipt(e){const t="string"==typeof e.data?JSON.parse(e.data):e.data;this.readReceiptCallbacks.forEach(e=>e(t))}attemptReconnect(){if(this.reconnectAttempts>=this.maxReconnectAttempts)return void console.error("[RC Widget] Max reconnection attempts reached");this.reconnectAttempts++;const e=Math.min(this.baseReconnectDelay*Math.pow(2,this.reconnectAttempts-1)+1e3*Math.random(),3e4);console.log(`[RC Widget] Reconnecting in ${Math.round(e/1e3)}s (attempt ${this.reconnectAttempts})...`),setTimeout(()=>{this.connect().then(()=>{const e=Array.from(this.subscribedChannels);this.subscribedChannels.clear(),e.forEach(e=>{const t=e.replace("private-conversation.","");this.subscribeConversation(t)})}).catch(()=>{})},e)}async authenticateChannel(e){if(!this.socketId)return console.error("[RC Widget] Cannot authenticate channel: no socket_id"),null;try{const t=new URLSearchParams({socket_id:this.socketId,channel_name:e,auth_token:this.sessionToken}),s=await fetch(`${this.apiUrl}/api/widget/broadcasting/auth`,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded",Authorization:`Bearer ${this.sessionToken}`},body:t.toString()});if(!s.ok){const e=await s.json().catch(()=>({}));return console.error("[RC Widget] Auth failed:",e),null}return await s.json()}catch(t){return console.error("[RC Widget] Auth request failed:",t),null}}async subscribeToChannel(e){if(this.socket&&this.socket.readyState===WebSocket.OPEN){if(!this.subscribedChannels.has(e))if(e.startsWith("private-")){const t=await this.authenticateChannel(e);if(!t)return void console.error("[RC Widget] Failed to authenticate channel:",e);this.socket.send(JSON.stringify({event:"pusher:subscribe",data:{channel:e,auth:t.auth,channel_data:t.channel_data}}))}else this.socket.send(JSON.stringify({event:"pusher:subscribe",data:{channel:e}}))}else this.pendingSubscriptions.add(e)}async subscribeConversation(e){const t=`private-conversation.${e}`;this.socket&&this.socket.readyState===WebSocket.OPEN?await this.subscribeToChannel(t):this.pendingSubscriptions.add(t)}subscribeUser(e){const t=`private-user.${e}`;this.subscribeToChannel(t)}unsubscribeConversation(e){var t;const s=`private-conversation.${e}`;this.subscribedChannels.delete(s),this.pendingSubscriptions.delete(s),(null==(t=this.socket)?void 0:t.readyState)===WebSocket.OPEN&&this.socket.send(JSON.stringify({event:"pusher:unsubscribe",data:{channel:s}}))}onMessage(e){return this.messageCallbacks.add(e),()=>this.messageCallbacks.delete(e)}onTyping(e){return this.typingCallbacks.add(e),()=>this.typingCallbacks.delete(e)}onConnection(e){return this.connectionCallbacks.add(e),()=>this.connectionCallbacks.delete(e)}onReadReceipt(e){return this.readReceiptCallbacks.add(e),()=>this.readReceiptCallbacks.delete(e)}isConnected(){var e;return(null==(e=this.socket)?void 0:e.readyState)===WebSocket.OPEN&&null!==this.socketId}disconnect(){this.pendingSubscriptions.clear(),this.subscribedChannels.clear(),this.socket&&(this.socket.close(1e3,"User initiated disconnect"),this.socket=null),this.socketId=null,this.isConnecting=!1,this.connectionPromise=null}}const fe="rc_",xe={get(e){try{const t=localStorage.getItem(fe+e);return t?JSON.parse(t):null}catch{return null}},set(e,t){try{localStorage.setItem(fe+e,JSON.stringify(t))}catch{}},remove(e){try{localStorage.removeItem(fe+e)}catch{}},getSessionKey:e=>e?`session_${e}`:"session",getSessionToken(e){const t=this.get(this.getSessionKey(e));if(!t)return null;if(t.expiresAt){if(new Date(t.expiresAt)<=new Date)return this.clearSession(e),null}return t.token},setSession(e){this.set(this.getSessionKey(e.userId),e)},getSession(e){const t=this.get(this.getSessionKey(e));if(!t)return null;if(t.expiresAt){if(new Date(t.expiresAt)<=new Date)return this.clearSession(e),null}return t},setSessionToken(e,t){const s=this.getSession(t);s?(s.token=e,this.setSession(s)):this.setSession({token:e,userId:t||"",userName:""})},clearSession(e){this.remove(this.getSessionKey(e)),this.remove("user")},getUser(){return this.get("user")},setUser(e){this.set("user",e)},getDraft(e){return(this.get("drafts")||{})[e]||null},setDraft(e,t){const s=this.get("drafts")||{};t.trim()?s[e]=t:delete s[e],this.set("drafts",s)},clearDraft(e){const t=this.get("drafts")||{};delete t[e],this.set("drafts",t)},getWidgetState(){return this.get("widget_state")},setWidgetState(e){this.set("widget_state",e)},clearAll(){Object.keys(localStorage).filter(e=>e.startsWith(fe)).forEach(e=>{try{localStorage.removeItem(e)}catch{}})}},we=':host{--rc-primary: #667eea;--rc-primary-dark: #5a67d8;--rc-secondary: #764ba2;--rc-bg: #ffffff;--rc-bg-secondary: #f7fafc;--rc-text: #1a202c;--rc-text-secondary: #718096;--rc-border: #e2e8f0;--rc-shadow: 0 10px 40px rgba(0, 0, 0, .15);--rc-radius: 16px;--rc-radius-sm: 8px;--rc-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;font-family:var(--rc-font);font-size:14px;line-height:1.5;color:var(--rc-text)}:host([theme="dark"]){--rc-bg: #1a202c;--rc-bg-secondary: #2d3748;--rc-text: #f7fafc;--rc-text-secondary: #a0aec0;--rc-border: #4a5568}*{box-sizing:border-box;margin:0;padding:0}button{font-family:inherit;cursor:pointer;border:none;background:none}input,textarea{font-family:inherit;font-size:inherit}';var $e=Object.defineProperty,Ce=Object.getOwnPropertyDescriptor,ke=(e,t,s,i)=>{for(var n,a=i>1?void 0:i?Ce(t,s):t,r=e.length-1;r>=0;r--)(n=e[r])&&(a=(i?n(t,s,a):n(a))||a);return i&&a&&$e(t,s,a),a};e.ReusableChat=class extends ce{constructor(){super(...arguments),this.initDelay=0,this.apiKey="",this.userId="",this.userName="",this.userEmail="",this.userAvatar="",this.position="bottom-right",this.theme="light",this.accentColor="",this.showBranding=!0,this.apiUrl="https://api-production-de24c.up.railway.app",this.wsHost="api-production-de24c.up.railway.app",this.wsKey="reusable-chat-key",this.isOpen=!1,this.wsConnected=!1,this.isLoading=!0,this.isLoadingMessages=!1,this.session=null,this.conversations=[],this.selectedConversation=null,this.messages=[],this.unreadCount=0,this.messageInput="",this.pendingAttachments=[],this.typingUsers=new Map,this.lightboxImage=null,this.isSending=!1,this.api=null,this.ws=null,this.typingTimeout=null,this.lastTypingSent=0,this.initialized=!1,this.cleanupCallbacks=[]}async connectedCallback(){super.connectedCallback(),this.initDelay=300*e.ReusableChat.initCounter,e.ReusableChat.initCounter++,this.initDelay>0&&await new Promise(e=>setTimeout(e,this.initDelay)),await this.initialize()}disconnectedCallback(){super.disconnectedCallback(),this.cleanupResources()}async initialize(){if(!this.initialized){if(this.initialized=!0,!this.apiKey)return this.emitError(new Error("api-key attribute is required"),"MISSING_API_KEY"),console.error("[Reusable Chat] api-key attribute is required"),void(this.isLoading=!1);this.api=new ye(this.apiUrl,this.apiKey);try{const e=xe.getSessionToken(this.userId);if(e&&this.userId){this.api.setSessionToken(e);try{const{data:t}=await this.api.getConversations();this.conversations=t,this.session={token:e,user:{id:this.userId,name:this.userName},expires_at:""},this.setupWebSocket(e)}catch{xe.clearSession(this.userId),await this.createSession()}}else this.userId&&this.userName&&await this.createSession();this.emitReady()}catch(e){const t=e instanceof Error?e:new Error("Initialization error");this.emitError(t,"INIT_ERROR"),console.error("[Reusable Chat] Initialization error:",e)}finally{this.isLoading=!1}}}async createSession(){if(this.api&&this.userId&&this.userName)try{const e=await this.api.createSession(this.userId,this.userName,this.userEmail,this.userAvatar);this.session=e,this.api.setSessionToken(e.token),xe.setSessionToken(e.token,this.userId);const{data:t}=await this.api.getConversations();this.conversations=t,this.unreadCount=t.reduce((e,t)=>e+(t.unread_count||0),0),this.setupWebSocket(e.token)}catch(e){const t=e instanceof Error?e:new Error("Session creation error");this.emitError(t,"SESSION_ERROR"),console.error("[Reusable Chat] Session creation error:",e)}}setupWebSocket(e){this.ws=new be(this.apiUrl,this.wsKey,e);const t=this.ws.onConnection(e=>{this.wsConnected=e});this.cleanupCallbacks.push(t);const s=this.ws.onMessage((e,t)=>{var s;this.emitMessage(e,t),(null==(s=this.selectedConversation)?void 0:s.id)===t&&(this.messages=this.messages.filter(t=>!t.isOptimistic||t.id!==e.id),this.messages=[...this.messages,e],this.scrollToBottom()),this.updateConversationLastMessage(t,e)});this.cleanupCallbacks.push(s);const i=this.ws.onTyping((e,t)=>{var s;if((null==(s=this.selectedConversation)?void 0:s.id)===t&&e.user_id!==this.userId)if(e.is_typing){const t=this.typingUsers.get(e.user_id);(null==t?void 0:t.timeout)&&clearTimeout(t.timeout);const s=setTimeout(()=>{this.typingUsers.delete(e.user_id),this.typingUsers=new Map(this.typingUsers)},3e3);this.typingUsers.set(e.user_id,{...e,timeout:s}),this.typingUsers=new Map(this.typingUsers)}else{const t=this.typingUsers.get(e.user_id);(null==t?void 0:t.timeout)&&clearTimeout(t.timeout),this.typingUsers.delete(e.user_id),this.typingUsers=new Map(this.typingUsers)}});this.cleanupCallbacks.push(i),this.ws.connect()}cleanupResources(){this.cleanupCallbacks.forEach(e=>e()),this.cleanupCallbacks=[],this.ws&&(this.ws.disconnect(),this.ws=null)}updateConversationLastMessage(e,t){this.conversations=this.conversations.map(s=>s.id===e?{...s,last_message:t,updated_at:t.created_at}:s)}open(){this.isOpen||(this.isOpen=!0,this.emitOpen())}close(){this.isOpen&&(this.isOpen=!1,this.emitClose())}toggle(){this.isOpen?this.close():this.open()}async sendMessage(e){if(!this.api||!this.selectedConversation||!e.trim())return null;const t=e.trim(),s={id:`optimistic-${Date.now()}`,content:t,sender_id:this.userId,sender:{id:this.userId,name:this.userName},attachments:[],created_at:(new Date).toISOString(),isOptimistic:!0};this.messages=[...this.messages,s],await this.updateComplete,this.scrollToBottom();try{const e=await this.api.sendMessage(this.selectedConversation.id,t,[]);return this.messages=this.messages.map(t=>t.id===s.id?e:t),this.emitSend(e,this.selectedConversation.id),e}catch(i){this.messages=this.messages.map(e=>e.id===s.id?{...e,isOptimistic:!1,failed:!0}:e);const e=i instanceof Error?i:new Error("Failed to send message");throw this.emitError(e,"SEND_ERROR"),e}}async setUser(e,t,s,i){this.userId=e,this.userName=t,void 0!==s&&(this.userEmail=s),void 0!==i&&(this.userAvatar=i),xe.clearSession(this.userId),this.session=null,this.conversations=[],this.messages=[],this.selectedConversation=null,this.cleanupResources(),this.initialized=!1,this.isLoading=!0,await this.initialize()}destroy(){this.cleanupResources(),xe.clearSession(this.userId),this.session=null,this.conversations=[],this.messages=[],this.selectedConversation=null,this.isOpen=!1,this.wsConnected=!1,this.unreadCount=0,this.initialized=!1}get isOpened(){return this.isOpen}get isConnected(){return this.wsConnected}get unreadMessages(){return this.unreadCount}emitReady(){this.dispatchEvent(new CustomEvent("rc-ready",{bubbles:!0,composed:!0}))}emitOpen(){this.dispatchEvent(new CustomEvent("rc-open",{bubbles:!0,composed:!0}))}emitClose(){this.dispatchEvent(new CustomEvent("rc-close",{bubbles:!0,composed:!0}))}emitMessage(e,t){this.dispatchEvent(new CustomEvent("rc-message",{bubbles:!0,composed:!0,detail:{message:e,conversationId:t}}))}emitSend(e,t){this.dispatchEvent(new CustomEvent("rc-send",{bubbles:!0,composed:!0,detail:{message:e,conversationId:t}}))}emitError(e,t){this.dispatchEvent(new CustomEvent("rc-error",{bubbles:!0,composed:!0,detail:{error:e,code:t}}))}toggleOpen(){this.toggle()}async selectConversation(e){var t;this.selectedConversation=e,this.isLoadingMessages=!0,this.messages=[],this.typingUsers.clear();try{if(this.api){const{messages:s}=await this.api.getConversation(e.id);this.messages=s.data,null==(t=this.ws)||t.subscribeConversation(e.id),await this.api.markAsRead(e.id),this.conversations=this.conversations.map(t=>t.id===e.id?{...t,unread_count:0}:t),this.unreadCount=this.conversations.reduce((e,t)=>e+(t.unread_count||0),0)}}catch(s){const e=s instanceof Error?s:new Error("Failed to load messages");this.emitError(e,"LOAD_ERROR"),console.error("[Reusable Chat] Failed to load messages:",s)}finally{this.isLoadingMessages=!1,await this.updateComplete,this.scrollToBottom()}}goBack(){var e;this.selectedConversation&&(null==(e=this.ws)||e.unsubscribeConversation(this.selectedConversation.id)),this.selectedConversation=null,this.messages=[],this.messageInput="",this.pendingAttachments=[],this.typingUsers.clear()}async handleSend(){if(!this.messageInput.trim()&&0===this.pendingAttachments.length||this.isSending)return;if(!this.api||!this.selectedConversation)return;const e=this.messageInput.trim(),t=this.pendingAttachments.filter(e=>e.uploaded).map(e=>e.uploaded.id),s={id:`optimistic-${Date.now()}`,content:e,sender_id:this.userId,sender:{id:this.userId,name:this.userName},attachments:this.pendingAttachments.filter(e=>e.uploaded).map(e=>({id:e.uploaded.id,name:e.uploaded.name,type:e.uploaded.type,url:e.uploaded.url,size:e.file.size})),created_at:(new Date).toISOString(),isOptimistic:!0};this.messages=[...this.messages,s],this.messageInput="",this.pendingAttachments=[],this.isSending=!0,await this.updateComplete,this.scrollToBottom(),this.adjustTextareaHeight();try{const i=await this.api.sendMessage(this.selectedConversation.id,e,t);this.messages=this.messages.map(e=>e.id===s.id?i:e),this.emitSend(i,this.selectedConversation.id)}catch(i){const e=i instanceof Error?i:new Error("Failed to send message");this.emitError(e,"SEND_ERROR"),console.error("[Reusable Chat] Failed to send message:",i),this.messages=this.messages.map(e=>e.id===s.id?{...e,isOptimistic:!1,failed:!0}:e)}finally{this.isSending=!1}}handleInputChange(e){const t=e.target;this.messageInput=t.value,this.adjustTextareaHeight(),this.sendTypingIndicator()}handleKeyDown(e){"Enter"!==e.key||e.shiftKey||(e.preventDefault(),this.handleSend())}adjustTextareaHeight(){if(!this.messageInputEl)return;this.messageInputEl.style.height="auto";this.messageInputEl.style.height=Math.min(this.messageInputEl.scrollHeight,100)+"px"}sendTypingIndicator(){if(!this.api||!this.selectedConversation)return;const e=Date.now();e-this.lastTypingSent<2e3||(this.lastTypingSent=e,this.api.sendTyping(this.selectedConversation.id,!0),this.typingTimeout&&clearTimeout(this.typingTimeout),this.typingTimeout=setTimeout(()=>{var e;null==(e=this.api)||e.sendTyping(this.selectedConversation.id,!1)},3e3))}handleAttachClick(){var e;null==(e=this.fileInput)||e.click()}async handleFileSelect(e){const t=e.target,s=t.files;if(s&&this.api&&this.selectedConversation){for(const e of Array.from(s)){const t=`pending-${Date.now()}-${Math.random()}`,s={id:t,file:e,uploading:!0,preview:e.type.startsWith("image/")?URL.createObjectURL(e):void 0};this.pendingAttachments=[...this.pendingAttachments,s];try{const s=await this.api.uploadAttachment(this.selectedConversation.id,e);this.pendingAttachments=this.pendingAttachments.map(e=>e.id===t?{...e,uploading:!1,uploaded:s}:e)}catch(i){this.pendingAttachments=this.pendingAttachments.map(e=>e.id===t?{...e,uploading:!1,error:"Upload failed"}:e)}}t.value=""}}removePendingAttachment(e){const t=this.pendingAttachments.find(t=>t.id===e);(null==t?void 0:t.preview)&&URL.revokeObjectURL(t.preview),this.pendingAttachments=this.pendingAttachments.filter(t=>t.id!==e)}scrollToBottom(){this.messagesContainer&&(this.messagesContainer.scrollTop=this.messagesContainer.scrollHeight)}openLightbox(e){this.lightboxImage=e}closeLightbox(){this.lightboxImage=null}formatTime(e){const t=new Date(e),s=(new Date).getTime()-t.getTime(),i=Math.floor(s/6e4);if(i<1)return"Just now";if(i<60)return`${i}m ago`;const n=Math.floor(i/60);if(n<24)return`${n}h ago`;const a=Math.floor(n/24);return a<7?`${a}d ago`:t.toLocaleDateString()}getInitials(e){return e.split(" ").map(e=>e[0]).join("").toUpperCase().slice(0,2)}isOwnMessage(e){return e.sender_id===this.userId}getParticipantNames(){if(!this.selectedConversation)return"";const e=this.selectedConversation.participants.filter(e=>e.id!==this.userId);return 0===e.length?"No participants":1===e.length?e[0].name:2===e.length?`${e[0].name} and ${e[1].name}`:`${e[0].name} and ${e.length-1} others`}formatFileSize(e){return e<1024?e+" B":e<1048576?(e/1024).toFixed(1)+" KB":(e/1048576).toFixed(1)+" MB"}renderConversationList(){return this.isLoading?F`
        <div class="content">
          ${[1,2,3].map(()=>F`
            <div class="skeleton-item">
              <div class="skeleton-avatar"></div>
              <div class="skeleton-text">
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
              </div>
            </div>
          `)}
        </div>
      `:0===this.conversations.length?F`
        <div class="content">
          <div class="empty-state">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p class="empty-title">No conversations yet</p>
            <p class="empty-subtitle">Messages will appear here</p>
            <slot name="empty"></slot>
          </div>
        </div>
      `:F`
      <div class="content">
        ${this.conversations.map(e=>{var t,s,i,n,a;return F`
          <div class="conversation-item" @click=${()=>this.selectConversation(e)}>
            <div class="avatar">${this.getInitials((null==(t=e.participants.find(e=>e.id!==this.userId))?void 0:t.name)||(null==(s=e.participants[0])?void 0:s.name)||"U")}</div>
            <div class="conv-info">
              <div class="conv-header">
                <span class="conv-name">${(null==(i=e.participants.find(e=>e.id!==this.userId))?void 0:i.name)||(null==(n=e.participants[0])?void 0:n.name)||"Unknown"}</span>
                ${e.last_message?F`
                  <span class="conv-time">${this.formatTime(e.last_message.created_at)}</span>
                `:J}
              </div>
              <div class="conv-preview-row">
                <span class="conv-preview">${(null==(a=e.last_message)?void 0:a.content)||"No messages"}</span>
                ${e.unread_count>0?F`
                  <span class="conv-unread">${e.unread_count}</span>
                `:J}
              </div>
            </div>
          </div>
        `})}
      </div>
    `}renderMessageThread(){return F`
      <div class="content messages-content" id="messages-container">
        ${this.isLoadingMessages?F`
          <div class="loading-messages">
            <div class="spinner"></div>
          </div>
        `:0===this.messages.length?F`
          <div class="empty-state">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p class="empty-title">No messages yet</p>
            <p class="empty-subtitle">Start the conversation!</p>
          </div>
        `:F`
          <div class="messages-list">
            ${this.messages.map(e=>this.renderMessage(e))}
          </div>
        `}
        ${this.renderTypingIndicator()}
      </div>
      ${this.renderInputArea()}
    `}renderMessage(e){var t,s,i;const n=this.isOwnMessage(e),a=e.attachments&&e.attachments.length>0;return F`
      <div class="message ${n?"own":"other"} ${e.isOptimistic?"optimistic":""} ${e.failed?"failed":""}">
        ${n?J:F`
          <div class="message-avatar">
            ${(null==(t=e.sender)?void 0:t.avatar_url)?F`<img src="${e.sender.avatar_url}" alt="${e.sender.name}" />`:F`<span>${this.getInitials((null==(s=e.sender)?void 0:s.name)||"U")}</span>`}
          </div>
        `}
        <div class="message-content">
          ${!n&&this.selectedConversation&&this.selectedConversation.participants.length>2?F`
            <div class="message-sender">${null==(i=e.sender)?void 0:i.name}</div>
          `:J}
          ${a?F`
            <div class="message-attachments">
              ${e.attachments.map(e=>this.renderAttachment(e))}
            </div>
          `:J}
          ${e.content?F`
            <div class="message-bubble">
              <span class="message-text">${e.content}</span>
            </div>
          `:J}
          <div class="message-meta">
            <span class="message-time">${this.formatTime(e.created_at)}</span>
            ${e.isOptimistic?F`<span class="message-status">Sending...</span>`:J}
            ${e.failed?F`<span class="message-status failed">Failed</span>`:J}
          </div>
        </div>
      </div>
    `}renderAttachment(e){return e.type.startsWith("image/")?F`
        <div class="attachment-image" @click=${()=>this.openLightbox(e.url)}>
          <img src="${e.url}" alt="${e.name}" loading="lazy" />
        </div>
      `:F`
      <a class="attachment-file" href="${e.url}" target="_blank" download="${e.name}">
        <svg class="file-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        <div class="file-info">
          <span class="file-name">${e.name}</span>
          <span class="file-size">${this.formatFileSize(e.size)}</span>
        </div>
        <svg class="download-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
      </a>
    `}renderTypingIndicator(){if(0===this.typingUsers.size)return J;const e=Array.from(this.typingUsers.values()).map(e=>e.name),t=1===e.length?`${e[0]} is typing`:2===e.length?`${e[0]} and ${e[1]} are typing`:`${e[0]} and ${e.length-1} others are typing`;return F`
      <div class="typing-indicator">
        <div class="typing-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <span class="typing-text">${t}</span>
      </div>
    `}renderInputArea(){return F`
      <div class="input-area">
        ${this.pendingAttachments.length>0?F`
          <div class="pending-attachments">
            ${this.pendingAttachments.map(e=>F`
              <div class="pending-attachment ${e.error?"error":""}">
                ${e.preview?F`
                  <img src="${e.preview}" alt="${e.file.name}" />
                `:F`
                  <div class="pending-file">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                      <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                  </div>
                `}
                ${e.uploading?F`
                  <div class="upload-overlay">
                    <div class="spinner small"></div>
                  </div>
                `:J}
                <button class="remove-attachment" @click=${()=>this.removePendingAttachment(e.id)}>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
            `)}
          </div>
        `:J}
        <div class="input-row">
          <button class="input-btn attach-btn" @click=${this.handleAttachClick} title="Attach file">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            </svg>
          </button>
          <input type="file" id="file-input" multiple hidden @change=${this.handleFileSelect} />
          <textarea
            id="message-input"
            class="message-input"
            placeholder="Type a message..."
            rows="1"
            .value=${this.messageInput}
            @input=${this.handleInputChange}
            @keydown=${this.handleKeyDown}
          ></textarea>
          <button
            class="input-btn send-btn ${this.messageInput.trim()||this.pendingAttachments.some(e=>e.uploaded)?"active":""}"
            @click=${this.handleSend}
            ?disabled=${!this.messageInput.trim()&&!this.pendingAttachments.some(e=>e.uploaded)}
            title="Send message"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
        <slot name="footer"></slot>
      </div>
    `}renderLightbox(){return this.lightboxImage?F`
      <div class="lightbox" @click=${this.closeLightbox}>
        <button class="lightbox-close" @click=${this.closeLightbox}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
        <img src="${this.lightboxImage}" alt="Full size" @click=${e=>e.stopPropagation()} />
      </div>
    `:J}render(){const e="bottom-left"===this.position?"left: 20px;":"right: 20px;",t=this.accentColor?`\n      --rc-primary: ${this.accentColor};\n      --rc-primary-dark: ${this.accentColor};\n    `:"";return F`
      <style>
        :host {
          /* CSS Custom Properties - can be overridden from outside */
          --rc-primary: var(--rc-primary-color, #667eea);
          --rc-primary-dark: var(--rc-primary-color, #5a67d8);
          --rc-secondary: var(--rc-secondary-color, #764ba2);
          --rc-bg: var(--rc-background-color, ${"dark"===this.theme?"#1a202c":"#ffffff"});
          --rc-bg-secondary: ${"dark"===this.theme?"#2d3748":"#f7fafc"};
          --rc-text: var(--rc-text-color, ${"dark"===this.theme?"#f7fafc":"#1a202c"});
          --rc-text-secondary: ${"dark"===this.theme?"#a0aec0":"#718096"};
          --rc-border: var(--rc-border-color, ${"dark"===this.theme?"#4a5568":"#e2e8f0"});
          --rc-font: var(--rc-font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
          --rc-radius: var(--rc-border-radius, 16px);
          --rc-radius-sm: calc(var(--rc-border-radius, 16px) / 2);
          --rc-shadow: var(--rc-shadow, 0 10px 40px rgba(0, 0, 0, 0.15));

          ${t}

          position: fixed;
          bottom: 20px;
          ${e}
          z-index: 999999;
          font-family: var(--rc-font);
          font-size: 14px;
          line-height: 1.5;
          color: var(--rc-text);
        }

        /* Bubble */
        .bubble {
          width: 60px;
          height: 60px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          box-shadow: var(--rc-shadow);
          transition: transform 0.2s;
          position: relative;
        }

        .bubble:hover {
          transform: scale(1.05);
        }

        .bubble svg {
          width: 28px;
          height: 28px;
        }

        .badge {
          position: absolute;
          top: -4px;
          right: -4px;
          background: #ef4444;
          color: white;
          font-size: 12px;
          font-weight: bold;
          min-width: 20px;
          height: 20px;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0 6px;
        }

        /* Window */
        .window {
          position: absolute;
          bottom: 80px;
          ${"bottom-left"===this.position?"left: 0;":"right: 0;"}
          width: 380px;
          height: calc(100vh - 120px);
          max-height: 600px;
          min-height: 400px;
          background: var(--rc-bg);
          border-radius: var(--rc-radius);
          box-shadow: var(--rc-shadow);
          display: flex;
          flex-direction: column;
          overflow: hidden;
          opacity: ${this.isOpen?"1":"0"};
          transform: ${this.isOpen?"translateY(0) scale(1)":"translateY(20px) scale(0.95)"};
          pointer-events: ${this.isOpen?"auto":"none"};
          transition: opacity 0.2s, transform 0.2s;
        }

        @media (max-width: 420px) {
          .window {
            width: calc(100vw - 40px);
          }
        }

        /* Header */
        .header {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          padding: 16px;
          display: flex;
          align-items: center;
          gap: 12px;
          flex-shrink: 0;
        }

        .header-back {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.1);
          cursor: pointer;
          transition: background 0.15s;
        }

        .header-back:hover {
          background: rgba(255, 255, 255, 0.2);
        }

        .header-back svg {
          width: 20px;
          height: 20px;
        }

        .header-info {
          flex: 1;
          min-width: 0;
        }

        .header-title {
          font-weight: 600;
          font-size: 16px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .header-subtitle {
          font-size: 12px;
          opacity: 0.8;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .status-dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background: ${this.wsConnected?"#34d399":"#fbbf24"};
        }

        .header-slot {
          display: flex;
          align-items: center;
        }

        ::slotted([slot="header"]) {
          margin-right: 8px;
        }

        .close-btn {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 32px;
          height: 32px;
          color: white;
          cursor: pointer;
        }

        /* Content */
        .content {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
        }

        .messages-content {
          display: flex;
          flex-direction: column;
          padding: 12px;
        }

        /* Skeleton loading */
        .skeleton-item {
          display: flex;
          gap: 12px;
          padding: 12px;
        }

        .skeleton-avatar {
          width: 44px;
          height: 44px;
          border-radius: 50%;
          background: linear-gradient(90deg, var(--rc-bg-secondary) 25%, var(--rc-border) 50%, var(--rc-bg-secondary) 75%);
          background-size: 200% 100%;
          animation: shimmer 1.5s infinite;
        }

        .skeleton-text {
          flex: 1;
          display: flex;
          flex-direction: column;
          gap: 8px;
          justify-content: center;
        }

        .skeleton-line {
          height: 14px;
          border-radius: 4px;
          background: linear-gradient(90deg, var(--rc-bg-secondary) 25%, var(--rc-border) 50%, var(--rc-bg-secondary) 75%);
          background-size: 200% 100%;
          animation: shimmer 1.5s infinite;
        }

        .skeleton-line.short {
          width: 60%;
        }

        @keyframes shimmer {
          0% { background-position: 200% 0; }
          100% { background-position: -200% 0; }
        }

        /* Empty state */
        .empty-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100%;
          text-align: center;
          padding: 40px 20px;
          color: var(--rc-text-secondary);
        }

        .empty-icon {
          width: 64px;
          height: 64px;
          margin-bottom: 16px;
          opacity: 0.5;
        }

        .empty-title {
          font-weight: 600;
          margin-bottom: 4px;
          color: var(--rc-text);
        }

        .empty-subtitle {
          font-size: 13px;
        }

        /* Conversation list */
        .conversation-item {
          display: flex;
          gap: 12px;
          padding: 12px;
          border-radius: var(--rc-radius-sm);
          cursor: pointer;
          transition: background 0.15s;
        }

        .conversation-item:hover {
          background: var(--rc-bg-secondary);
        }

        .avatar {
          width: 44px;
          height: 44px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 600;
          font-size: 14px;
          flex-shrink: 0;
          overflow: hidden;
        }

        .avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .conv-info {
          flex: 1;
          min-width: 0;
        }

        .conv-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 8px;
          margin-bottom: 2px;
        }

        .conv-name {
          font-weight: 600;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .conv-time {
          font-size: 11px;
          color: var(--rc-text-secondary);
          flex-shrink: 0;
        }

        .conv-preview-row {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 8px;
        }

        .conv-preview {
          font-size: 13px;
          color: var(--rc-text-secondary);
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .conv-unread {
          background: var(--rc-primary);
          color: white;
          font-size: 11px;
          font-weight: 600;
          min-width: 18px;
          height: 18px;
          border-radius: 9px;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0 5px;
          flex-shrink: 0;
        }

        /* Messages */
        .loading-messages {
          display: flex;
          align-items: center;
          justify-content: center;
          height: 100%;
        }

        .spinner {
          width: 32px;
          height: 32px;
          border: 3px solid var(--rc-border);
          border-top-color: var(--rc-primary);
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
        }

        .spinner.small {
          width: 16px;
          height: 16px;
          border-width: 2px;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        .messages-list {
          display: flex;
          flex-direction: column;
          gap: 8px;
          min-height: 0;
        }

        .message {
          display: flex;
          gap: 8px;
          max-width: 85%;
        }

        .message.own {
          flex-direction: row-reverse;
          align-self: flex-end;
        }

        .message.other {
          align-self: flex-start;
        }

        .message.optimistic {
          opacity: 0.7;
        }

        .message.failed .message-bubble {
          background: #fee2e2 !important;
        }

        .message-avatar {
          width: 28px;
          height: 28px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 10px;
          font-weight: 600;
          flex-shrink: 0;
          overflow: hidden;
          align-self: flex-end;
        }

        .message-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .message-content {
          display: flex;
          flex-direction: column;
          gap: 4px;
          min-width: 0;
        }

        .message.own .message-content {
          align-items: flex-end;
        }

        .message-sender {
          font-size: 11px;
          font-weight: 600;
          color: var(--rc-text-secondary);
          padding-left: 8px;
        }

        .message-bubble {
          padding: 10px 14px;
          border-radius: 18px;
          word-wrap: break-word;
          overflow-wrap: break-word;
        }

        .message.own .message-bubble {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          border-bottom-right-radius: 4px;
        }

        .message.other .message-bubble {
          background: var(--rc-bg-secondary);
          color: var(--rc-text);
          border-bottom-left-radius: 4px;
        }

        .message-text {
          white-space: pre-wrap;
        }

        .message-meta {
          display: flex;
          gap: 6px;
          font-size: 10px;
          color: var(--rc-text-secondary);
          padding: 0 8px;
        }

        .message-status.failed {
          color: #ef4444;
        }

        .message-attachments {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }

        .attachment-image {
          border-radius: 12px;
          overflow: hidden;
          cursor: pointer;
          max-width: 200px;
        }

        .attachment-image img {
          display: block;
          width: 100%;
          height: auto;
        }

        .attachment-file {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 10px 12px;
          background: var(--rc-bg-secondary);
          border-radius: 12px;
          text-decoration: none;
          color: var(--rc-text);
          transition: background 0.15s;
        }

        .attachment-file:hover {
          background: var(--rc-border);
        }

        .file-icon {
          width: 24px;
          height: 24px;
          flex-shrink: 0;
        }

        .file-info {
          flex: 1;
          min-width: 0;
          display: flex;
          flex-direction: column;
        }

        .file-name {
          font-size: 13px;
          font-weight: 500;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .file-size {
          font-size: 11px;
          color: var(--rc-text-secondary);
        }

        .download-icon {
          width: 20px;
          height: 20px;
          flex-shrink: 0;
          opacity: 0.5;
        }

        /* Typing indicator */
        .typing-indicator {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 8px 12px;
          color: var(--rc-text-secondary);
          font-size: 13px;
        }

        .typing-dots {
          display: flex;
          gap: 3px;
        }

        .typing-dots span {
          width: 6px;
          height: 6px;
          background: var(--rc-text-secondary);
          border-radius: 50%;
          animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) {
          animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
          animation-delay: 0.4s;
        }

        @keyframes typing {
          0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
          30% { transform: translateY(-4px); opacity: 1; }
        }

        /* Input area */
        .input-area {
          padding: 12px;
          border-top: 1px solid var(--rc-border);
          flex-shrink: 0;
        }

        .pending-attachments {
          display: flex;
          gap: 8px;
          margin-bottom: 8px;
          flex-wrap: wrap;
        }

        .pending-attachment {
          position: relative;
          width: 60px;
          height: 60px;
          border-radius: 8px;
          overflow: hidden;
          background: var(--rc-bg-secondary);
        }

        .pending-attachment.error {
          border: 2px solid #ef4444;
        }

        .pending-attachment img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .pending-file {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .pending-file svg {
          width: 24px;
          height: 24px;
          color: var(--rc-text-secondary);
        }

        .upload-overlay {
          position: absolute;
          inset: 0;
          background: rgba(0, 0, 0, 0.5);
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .upload-overlay .spinner {
          border-color: rgba(255, 255, 255, 0.3);
          border-top-color: white;
        }

        .remove-attachment {
          position: absolute;
          top: 2px;
          right: 2px;
          width: 20px;
          height: 20px;
          background: rgba(0, 0, 0, 0.6);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }

        .remove-attachment svg {
          width: 12px;
          height: 12px;
          color: white;
        }

        .input-row {
          display: flex;
          align-items: flex-end;
          gap: 8px;
        }

        .input-btn {
          width: 36px;
          height: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          color: var(--rc-text-secondary);
          transition: background 0.15s, color 0.15s;
          flex-shrink: 0;
        }

        .input-btn:hover {
          background: var(--rc-bg-secondary);
        }

        .input-btn svg {
          width: 20px;
          height: 20px;
        }

        .send-btn.active {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
        }

        .send-btn:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }

        .message-input {
          flex: 1;
          border: 1px solid var(--rc-border);
          border-radius: 18px;
          padding: 8px 14px;
          resize: none;
          outline: none;
          max-height: 100px;
          min-height: 36px;
          background: var(--rc-bg);
          color: var(--rc-text);
          line-height: 1.4;
        }

        .message-input:focus {
          border-color: var(--rc-primary);
        }

        .message-input::placeholder {
          color: var(--rc-text-secondary);
        }

        /* Footer slot */
        ::slotted([slot="footer"]) {
          display: block;
          margin-top: 8px;
        }

        /* Branding */
        .branding {
          text-align: center;
          padding: 8px;
          font-size: 11px;
          color: var(--rc-text-secondary);
          border-top: 1px solid var(--rc-border);
          flex-shrink: 0;
        }

        .branding a {
          color: inherit;
          text-decoration: none;
        }

        .branding a:hover {
          color: var(--rc-primary);
        }

        /* Lightbox */
        .lightbox {
          position: fixed;
          inset: 0;
          background: rgba(0, 0, 0, 0.9);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 1000000;
          animation: fadeIn 0.2s;
        }

        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        .lightbox img {
          max-width: 90%;
          max-height: 90%;
          object-fit: contain;
          border-radius: 8px;
        }

        .lightbox-close {
          position: absolute;
          top: 20px;
          right: 20px;
          width: 40px;
          height: 40px;
          background: rgba(255, 255, 255, 0.1);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: background 0.15s;
        }

        .lightbox-close:hover {
          background: rgba(255, 255, 255, 0.2);
        }

        .lightbox-close svg {
          width: 24px;
          height: 24px;
          color: white;
        }
      </style>

      <div class="bubble" @click=${this.toggleOpen}>
        ${this.isOpen?F`
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        `:F`
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        `}
        ${this.unreadCount>0?F`<div class="badge">${this.unreadCount>99?"99+":this.unreadCount}</div>`:J}
      </div>

      <div class="window">
        <div class="header">
          ${this.selectedConversation?F`
            <button class="header-back" @click=${this.goBack}>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <div class="header-info">
              <div class="header-title">${this.getParticipantNames()}</div>
              <div class="header-subtitle">
                <span class="status-dot"></span>
                ${this.selectedConversation.participants.length} participant${1!==this.selectedConversation.participants.length?"s":""}
              </div>
            </div>
          `:F`
            <div class="header-info">
              <div class="header-title">Messages</div>
              <div class="header-subtitle">
                <span class="status-dot"></span>
                ${this.wsConnected?"Connected":"Connecting..."}
              </div>
            </div>
          `}
          <div class="header-slot">
            <slot name="header"></slot>
          </div>
          <button class="close-btn" @click=${()=>this.close()}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        ${this.selectedConversation?this.renderMessageThread():this.renderConversationList()}

        ${!this.selectedConversation&&this.showBranding?F`
          <div class="branding">
            <a href="https://github.com/TNortnern/reusable-chat" target="_blank">
              Powered by Reusable Chat
            </a>
          </div>
        `:J}
      </div>

      ${this.renderLightbox()}
    `}},e.ReusableChat.styles=l`${o(we)}`,e.ReusableChat.initCounter=0,ke([ge({attribute:"api-key",reflect:!0})],e.ReusableChat.prototype,"apiKey",2),ke([ge({attribute:"user-id",reflect:!0})],e.ReusableChat.prototype,"userId",2),ke([ge({attribute:"user-name",reflect:!0})],e.ReusableChat.prototype,"userName",2),ke([ge({attribute:"user-email",reflect:!0})],e.ReusableChat.prototype,"userEmail",2),ke([ge({attribute:"user-avatar",reflect:!0})],e.ReusableChat.prototype,"userAvatar",2),ke([ge({attribute:"position",reflect:!0})],e.ReusableChat.prototype,"position",2),ke([ge({attribute:"theme",reflect:!0})],e.ReusableChat.prototype,"theme",2),ke([ge({attribute:"accent-color",reflect:!0})],e.ReusableChat.prototype,"accentColor",2),ke([ge({type:Boolean,attribute:"show-branding",reflect:!0})],e.ReusableChat.prototype,"showBranding",2),ke([ge({attribute:"api-url",reflect:!0})],e.ReusableChat.prototype,"apiUrl",2),ke([ge({attribute:"ws-host",reflect:!0})],e.ReusableChat.prototype,"wsHost",2),ke([ge({attribute:"ws-key",reflect:!0})],e.ReusableChat.prototype,"wsKey",2),ke([me()],e.ReusableChat.prototype,"isOpen",2),ke([me()],e.ReusableChat.prototype,"wsConnected",2),ke([me()],e.ReusableChat.prototype,"isLoading",2),ke([me()],e.ReusableChat.prototype,"isLoadingMessages",2),ke([me()],e.ReusableChat.prototype,"session",2),ke([me()],e.ReusableChat.prototype,"conversations",2),ke([me()],e.ReusableChat.prototype,"selectedConversation",2),ke([me()],e.ReusableChat.prototype,"messages",2),ke([me()],e.ReusableChat.prototype,"unreadCount",2),ke([me()],e.ReusableChat.prototype,"messageInput",2),ke([me()],e.ReusableChat.prototype,"pendingAttachments",2),ke([me()],e.ReusableChat.prototype,"typingUsers",2),ke([me()],e.ReusableChat.prototype,"lightboxImage",2),ke([me()],e.ReusableChat.prototype,"isSending",2),ke([ve("#message-input")],e.ReusableChat.prototype,"messageInputEl",2),ke([ve("#messages-container")],e.ReusableChat.prototype,"messagesContainer",2),ke([ve("#file-input")],e.ReusableChat.prototype,"fileInput",2),e.ReusableChat=ke([he("reusable-chat")],e.ReusableChat);var Se=Object.defineProperty,_e=Object.getOwnPropertyDescriptor,Ie=(e,t,s,i)=>{for(var n,a=i>1?void 0:i?_e(t,s):t,r=e.length-1;r>=0;r--)(n=e[r])&&(a=(i?n(t,s,a):n(a))||a);return i&&a&&Se(t,s,a),a};return e.ReusableChatInline=class extends ce{constructor(){super(...arguments),this.initDelay=0,this.apiKey="",this.userId="",this.userName="",this.userEmail="",this.userAvatar="",this.conversationId="",this.theme="light",this.accentColor="",this.showBranding=!0,this.apiUrl="https://api-production-de24c.up.railway.app",this.wsHost="api-production-de24c.up.railway.app",this.wsKey="reusable-chat-key",this.wsConnected=!1,this.isLoading=!0,this.isLoadingMessages=!1,this.session=null,this.messages=[],this.messageInput="",this.pendingAttachments=[],this.typingUsers=new Map,this.lightboxImage=null,this.isSending=!1,this.error=null,this.api=null,this.ws=null,this.typingTimeout=null,this.lastTypingSent=0,this.initialized=!1,this.cleanupCallbacks=[]}async connectedCallback(){super.connectedCallback(),this.initDelay=300*e.ReusableChatInline.initCounter,e.ReusableChatInline.initCounter++,this.initDelay>0&&await new Promise(e=>setTimeout(e,this.initDelay)),await this.initialize()}disconnectedCallback(){super.disconnectedCallback(),this.cleanupResources()}updated(e){super.updated(e),e.has("conversationId")&&this.initialized&&this.conversationId&&this.loadConversation()}async initialize(){if(!this.initialized){if(this.initialized=!0,!this.apiKey)return this.error="api-key attribute is required",this.emitError(new Error("api-key attribute is required"),"MISSING_API_KEY"),console.error("[Reusable Chat Inline] api-key attribute is required"),void(this.isLoading=!1);if(!this.conversationId)return this.error="conversation-id attribute is required",this.emitError(new Error("conversation-id attribute is required"),"MISSING_CONVERSATION_ID"),console.error("[Reusable Chat Inline] conversation-id attribute is required"),void(this.isLoading=!1);this.api=new ye(this.apiUrl,this.apiKey);try{const e=xe.getSessionToken(this.userId);if(e&&this.userId){this.api.setSessionToken(e);try{await this.api.verifySession(),this.session={token:e,user:{id:this.userId,name:this.userName},expires_at:""},this.setupWebSocket(e),await this.loadConversation()}catch{xe.clearSession(this.userId),await this.createSession()}}else this.userId&&this.userName?await this.createSession():(this.error="user-id and user-name are required",this.emitError(new Error("user-id and user-name are required"),"MISSING_USER_INFO"),console.error("[Reusable Chat Inline] user-id and user-name are required"));this.emitReady()}catch(e){const t=e instanceof Error?e:new Error("Initialization error");this.error=t.message,this.emitError(t,"INIT_ERROR"),console.error("[Reusable Chat Inline] Initialization error:",e)}finally{this.isLoading=!1}}}async createSession(){if(this.api&&this.userId&&this.userName)try{const e=await this.api.createSession(this.userId,this.userName,this.userEmail,this.userAvatar);this.session=e,this.api.setSessionToken(e.token),xe.setSessionToken(e.token,this.userId),this.setupWebSocket(e.token),await this.loadConversation()}catch(e){const t=e instanceof Error?e:new Error("Session creation error");this.error=t.message,this.emitError(t,"SESSION_ERROR"),console.error("[Reusable Chat Inline] Session creation error:",e)}}async loadConversation(){var e;if(this.api&&this.conversationId){this.isLoadingMessages=!0,this.messages=[],this.typingUsers.clear(),this.error=null;try{const{messages:t}=await this.api.getConversation(this.conversationId);this.messages=t.data,null==(e=this.ws)||e.subscribeConversation(this.conversationId),await this.api.markAsRead(this.conversationId)}catch(t){const e=t instanceof Error?t:new Error("Failed to load messages");this.error=e.message,this.emitError(e,"LOAD_ERROR"),console.error("[Reusable Chat Inline] Failed to load messages:",t)}finally{this.isLoadingMessages=!1,await this.updateComplete,this.scrollToBottom()}}}setupWebSocket(e){this.ws=new be(this.apiUrl,this.wsKey,e);const t=this.ws.onConnection(e=>{var t;this.wsConnected=e,e&&this.conversationId&&(null==(t=this.ws)||t.subscribeConversation(this.conversationId))});this.cleanupCallbacks.push(t);const s=this.ws.onMessage((e,t)=>{this.emitMessage(e,t),this.conversationId===t&&(this.messages=this.messages.filter(t=>!t.isOptimistic||t.id!==e.id),this.messages=[...this.messages,e],this.scrollToBottom())});this.cleanupCallbacks.push(s);const i=this.ws.onTyping((e,t)=>{if(this.conversationId===t&&e.user_id!==this.userId)if(e.is_typing){const t=this.typingUsers.get(e.user_id);(null==t?void 0:t.timeout)&&clearTimeout(t.timeout);const s=setTimeout(()=>{this.typingUsers.delete(e.user_id),this.typingUsers=new Map(this.typingUsers)},3e3);this.typingUsers.set(e.user_id,{...e,timeout:s}),this.typingUsers=new Map(this.typingUsers)}else{const t=this.typingUsers.get(e.user_id);(null==t?void 0:t.timeout)&&clearTimeout(t.timeout),this.typingUsers.delete(e.user_id),this.typingUsers=new Map(this.typingUsers)}});this.cleanupCallbacks.push(i),this.ws.connect()}cleanupResources(){this.cleanupCallbacks.forEach(e=>e()),this.cleanupCallbacks=[],this.ws&&(this.conversationId&&this.ws.unsubscribeConversation(this.conversationId),this.ws.disconnect(),this.ws=null)}async sendMessage(e){if(!this.api||!this.conversationId||!e.trim())return null;const t=e.trim(),s={id:`optimistic-${Date.now()}`,content:t,sender_id:this.userId,sender:{id:this.userId,name:this.userName},attachments:[],created_at:(new Date).toISOString(),isOptimistic:!0};this.messages=[...this.messages,s],await this.updateComplete,this.scrollToBottom();try{const e=await this.api.sendMessage(this.conversationId,t,[]);return this.messages=this.messages.map(t=>t.id===s.id?e:t),this.emitSend(e,this.conversationId),e}catch(i){this.messages=this.messages.map(e=>e.id===s.id?{...e,isOptimistic:!1,failed:!0}:e);const e=i instanceof Error?i:new Error("Failed to send message");throw this.emitError(e,"SEND_ERROR"),e}}async setUser(e,t,s,i){this.userId=e,this.userName=t,void 0!==s&&(this.userEmail=s),void 0!==i&&(this.userAvatar=i),xe.clearSession(this.userId),this.session=null,this.messages=[],this.cleanupResources(),this.initialized=!1,this.isLoading=!0,await this.initialize()}destroy(){this.cleanupResources(),xe.clearSession(this.userId),this.session=null,this.messages=[],this.wsConnected=!1,this.initialized=!1,this.error=null}get isConnected(){return this.wsConnected}async refresh(){await this.loadConversation()}emitReady(){this.dispatchEvent(new CustomEvent("rc-ready",{bubbles:!0,composed:!0}))}emitMessage(e,t){this.dispatchEvent(new CustomEvent("rc-message",{bubbles:!0,composed:!0,detail:{message:e,conversationId:t}}))}emitSend(e,t){this.dispatchEvent(new CustomEvent("rc-send",{bubbles:!0,composed:!0,detail:{message:e,conversationId:t}}))}emitError(e,t){this.dispatchEvent(new CustomEvent("rc-error",{bubbles:!0,composed:!0,detail:{error:e,code:t}}))}async handleSend(){if(!this.messageInput.trim()&&0===this.pendingAttachments.length||this.isSending)return;if(!this.api||!this.conversationId)return;const e=this.messageInput.trim(),t=this.pendingAttachments.filter(e=>e.uploaded).map(e=>e.uploaded.id),s={id:`optimistic-${Date.now()}`,content:e,sender_id:this.userId,sender:{id:this.userId,name:this.userName},attachments:this.pendingAttachments.filter(e=>e.uploaded).map(e=>({id:e.uploaded.id,name:e.uploaded.name,type:e.uploaded.type,url:e.uploaded.url,size:e.file.size})),created_at:(new Date).toISOString(),isOptimistic:!0};this.messages=[...this.messages,s],this.messageInput="",this.pendingAttachments=[],this.isSending=!0,await this.updateComplete,this.scrollToBottom(),this.adjustTextareaHeight();try{const i=await this.api.sendMessage(this.conversationId,e,t);this.messages=this.messages.map(e=>e.id===s.id?i:e),this.emitSend(i,this.conversationId)}catch(i){const e=i instanceof Error?i:new Error("Failed to send message");this.emitError(e,"SEND_ERROR"),console.error("[Reusable Chat Inline] Failed to send message:",i),this.messages=this.messages.map(e=>e.id===s.id?{...e,isOptimistic:!1,failed:!0}:e)}finally{this.isSending=!1}}handleInputChange(e){const t=e.target;this.messageInput=t.value,this.adjustTextareaHeight(),this.sendTypingIndicator()}handleKeyDown(e){"Enter"!==e.key||e.shiftKey||(e.preventDefault(),this.handleSend())}adjustTextareaHeight(){if(!this.messageInputEl)return;this.messageInputEl.style.height="auto";this.messageInputEl.style.height=Math.min(this.messageInputEl.scrollHeight,100)+"px"}sendTypingIndicator(){if(!this.api||!this.conversationId)return;const e=Date.now();e-this.lastTypingSent<2e3||(this.lastTypingSent=e,this.api.sendTyping(this.conversationId,!0),this.typingTimeout&&clearTimeout(this.typingTimeout),this.typingTimeout=setTimeout(()=>{var e;null==(e=this.api)||e.sendTyping(this.conversationId,!1)},3e3))}handleAttachClick(){var e;null==(e=this.fileInput)||e.click()}async handleFileSelect(e){const t=e.target,s=t.files;if(s&&this.api&&this.conversationId){for(const e of Array.from(s)){const t=`pending-${Date.now()}-${Math.random()}`,s={id:t,file:e,uploading:!0,preview:e.type.startsWith("image/")?URL.createObjectURL(e):void 0};this.pendingAttachments=[...this.pendingAttachments,s];try{const s=await this.api.uploadAttachment(this.conversationId,e);this.pendingAttachments=this.pendingAttachments.map(e=>e.id===t?{...e,uploading:!1,uploaded:s}:e)}catch(i){this.pendingAttachments=this.pendingAttachments.map(e=>e.id===t?{...e,uploading:!1,error:"Upload failed"}:e)}}t.value=""}}removePendingAttachment(e){const t=this.pendingAttachments.find(t=>t.id===e);(null==t?void 0:t.preview)&&URL.revokeObjectURL(t.preview),this.pendingAttachments=this.pendingAttachments.filter(t=>t.id!==e)}scrollToBottom(){this.messagesContainer&&(this.messagesContainer.scrollTop=this.messagesContainer.scrollHeight)}openLightbox(e){this.lightboxImage=e}closeLightbox(){this.lightboxImage=null}formatTime(e){const t=new Date(e),s=(new Date).getTime()-t.getTime(),i=Math.floor(s/6e4);if(i<1)return"Just now";if(i<60)return`${i}m ago`;const n=Math.floor(i/60);if(n<24)return`${n}h ago`;const a=Math.floor(n/24);return a<7?`${a}d ago`:t.toLocaleDateString()}getInitials(e){return e.split(" ").map(e=>e[0]).join("").toUpperCase().slice(0,2)}isOwnMessage(e){return e.sender_id===this.userId}formatFileSize(e){return e<1024?e+" B":e<1048576?(e/1024).toFixed(1)+" KB":(e/1048576).toFixed(1)+" MB"}renderMessage(e){var t,s,i;const n=this.isOwnMessage(e),a=e.attachments&&e.attachments.length>0;return F`
      <div class="message ${n?"own":"other"} ${e.isOptimistic?"optimistic":""} ${e.failed?"failed":""}">
        ${n?J:F`
          <div class="message-avatar">
            ${(null==(t=e.sender)?void 0:t.avatar_url)?F`<img src="${e.sender.avatar_url}" alt="${e.sender.name}" />`:F`<span>${this.getInitials((null==(s=e.sender)?void 0:s.name)||"U")}</span>`}
          </div>
        `}
        <div class="message-content">
          ${n?J:F`
            <div class="message-sender">${null==(i=e.sender)?void 0:i.name}</div>
          `}
          ${a?F`
            <div class="message-attachments">
              ${e.attachments.map(e=>this.renderAttachment(e))}
            </div>
          `:J}
          ${e.content?F`
            <div class="message-bubble">
              <span class="message-text">${e.content}</span>
            </div>
          `:J}
          <div class="message-meta">
            <span class="message-time">${this.formatTime(e.created_at)}</span>
            ${e.isOptimistic?F`<span class="message-status">Sending...</span>`:J}
            ${e.failed?F`<span class="message-status failed">Failed</span>`:J}
          </div>
        </div>
      </div>
    `}renderAttachment(e){return e.type.startsWith("image/")?F`
        <div class="attachment-image" @click=${()=>this.openLightbox(e.url)}>
          <img src="${e.url}" alt="${e.name}" loading="lazy" />
        </div>
      `:F`
      <a class="attachment-file" href="${e.url}" target="_blank" download="${e.name}">
        <svg class="file-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        <div class="file-info">
          <span class="file-name">${e.name}</span>
          <span class="file-size">${this.formatFileSize(e.size)}</span>
        </div>
        <svg class="download-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
      </a>
    `}renderTypingIndicator(){if(0===this.typingUsers.size)return J;const e=Array.from(this.typingUsers.values()).map(e=>e.name),t=1===e.length?`${e[0]} is typing`:2===e.length?`${e[0]} and ${e[1]} are typing`:`${e[0]} and ${e.length-1} others are typing`;return F`
      <div class="typing-indicator">
        <div class="typing-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <span class="typing-text">${t}</span>
      </div>
    `}renderInputArea(){return F`
      <div class="input-area">
        ${this.pendingAttachments.length>0?F`
          <div class="pending-attachments">
            ${this.pendingAttachments.map(e=>F`
              <div class="pending-attachment ${e.error?"error":""}">
                ${e.preview?F`
                  <img src="${e.preview}" alt="${e.file.name}" />
                `:F`
                  <div class="pending-file">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                      <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                  </div>
                `}
                ${e.uploading?F`
                  <div class="upload-overlay">
                    <div class="spinner small"></div>
                  </div>
                `:J}
                <button class="remove-attachment" @click=${()=>this.removePendingAttachment(e.id)}>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
            `)}
          </div>
        `:J}
        <div class="input-row">
          <button class="input-btn attach-btn" @click=${this.handleAttachClick} title="Attach file">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            </svg>
          </button>
          <input type="file" id="file-input" multiple hidden @change=${this.handleFileSelect} />
          <textarea
            id="message-input"
            class="message-input"
            placeholder="Type a message..."
            rows="1"
            .value=${this.messageInput}
            @input=${this.handleInputChange}
            @keydown=${this.handleKeyDown}
          ></textarea>
          <button
            class="input-btn send-btn ${this.messageInput.trim()||this.pendingAttachments.some(e=>e.uploaded)?"active":""}"
            @click=${this.handleSend}
            ?disabled=${!this.messageInput.trim()&&!this.pendingAttachments.some(e=>e.uploaded)}
            title="Send message"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
        <slot name="footer"></slot>
      </div>
    `}renderLightbox(){return this.lightboxImage?F`
      <div class="lightbox" @click=${this.closeLightbox}>
        <button class="lightbox-close" @click=${this.closeLightbox}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
        <img src="${this.lightboxImage}" alt="Full size" @click=${e=>e.stopPropagation()} />
      </div>
    `:J}renderContent(){return this.isLoading?F`
        <div class="messages-container" id="messages-container">
          <div class="loading-container">
            <div class="spinner"></div>
          </div>
        </div>
      `:this.error?F`
        <div class="messages-container" id="messages-container">
          <div class="error-state">
            <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p class="error-title">Error</p>
            <p class="error-message">${this.error}</p>
          </div>
        </div>
      `:F`
      <div class="messages-container" id="messages-container">
        ${this.isLoadingMessages?F`
          <div class="loading-container">
            <div class="spinner"></div>
          </div>
        `:0===this.messages.length?F`
          <div class="empty-state">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p class="empty-title">No messages yet</p>
            <p class="empty-subtitle">Start the conversation!</p>
          </div>
        `:F`
          <div class="messages-list">
            ${this.messages.map(e=>this.renderMessage(e))}
          </div>
        `}
        ${this.renderTypingIndicator()}
      </div>
      ${this.renderInputArea()}
    `}render(){const e=this.accentColor?`\n      --rc-primary: ${this.accentColor};\n      --rc-primary-dark: ${this.accentColor};\n    `:"";return F`
      <style>
        :host {
          /* CSS Custom Properties - can be overridden from outside */
          --rc-primary: var(--rc-primary-color, #667eea);
          --rc-primary-dark: var(--rc-primary-color, #5a67d8);
          --rc-secondary: var(--rc-secondary-color, #764ba2);
          --rc-bg: var(--rc-background-color, ${"dark"===this.theme?"#1a202c":"#ffffff"});
          --rc-bg-secondary: ${"dark"===this.theme?"#2d3748":"#f7fafc"};
          --rc-text: var(--rc-text-color, ${"dark"===this.theme?"#f7fafc":"#1a202c"});
          --rc-text-secondary: ${"dark"===this.theme?"#a0aec0":"#718096"};
          --rc-border: var(--rc-border-color, ${"dark"===this.theme?"#4a5568":"#e2e8f0"});
          --rc-font: var(--rc-font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
          --rc-radius: var(--rc-border-radius, 16px);
          --rc-radius-sm: calc(var(--rc-border-radius, 16px) / 2);
          --rc-shadow: var(--rc-shadow, 0 4px 12px rgba(0, 0, 0, 0.1));

          ${e}

          display: flex;
          flex-direction: column;
          width: 100%;
          height: 100%;
          min-height: 300px;
          max-height: var(--rc-max-height, calc(100vh - 100px));
          background: var(--rc-bg);
          border: 1px solid var(--rc-border);
          border-radius: var(--rc-radius);
          overflow: hidden;
          font-family: var(--rc-font);
          font-size: 14px;
          line-height: 1.5;
          color: var(--rc-text);
        }

        /* Header (optional) */
        .header {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 12px 16px;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          flex-shrink: 0;
        }

        .header-info {
          flex: 1;
          min-width: 0;
        }

        .header-title {
          font-weight: 600;
          font-size: 15px;
        }

        .header-subtitle {
          font-size: 12px;
          opacity: 0.8;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .status-dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background: ${this.wsConnected?"#34d399":"#fbbf24"};
        }

        /* Messages container */
        .messages-container {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
          display: flex;
          flex-direction: column;
        }

        /* Loading state */
        .loading-container {
          display: flex;
          align-items: center;
          justify-content: center;
          height: 100%;
          flex: 1;
        }

        .spinner {
          width: 32px;
          height: 32px;
          border: 3px solid var(--rc-border);
          border-top-color: var(--rc-primary);
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
        }

        .spinner.small {
          width: 16px;
          height: 16px;
          border-width: 2px;
        }

        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        /* Error state */
        .error-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100%;
          text-align: center;
          padding: 40px 20px;
          color: var(--rc-text-secondary);
        }

        .error-icon {
          width: 48px;
          height: 48px;
          margin-bottom: 16px;
          color: #ef4444;
        }

        .error-title {
          font-weight: 600;
          margin-bottom: 4px;
          color: #ef4444;
        }

        .error-message {
          font-size: 13px;
        }

        /* Empty state */
        .empty-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100%;
          text-align: center;
          padding: 40px 20px;
          color: var(--rc-text-secondary);
        }

        .empty-icon {
          width: 64px;
          height: 64px;
          margin-bottom: 16px;
          opacity: 0.5;
        }

        .empty-title {
          font-weight: 600;
          margin-bottom: 4px;
          color: var(--rc-text);
        }

        .empty-subtitle {
          font-size: 13px;
        }

        /* Messages */
        .messages-list {
          display: flex;
          flex-direction: column;
          gap: 8px;
          min-height: 0;
        }

        .message {
          display: flex;
          gap: 8px;
          max-width: 85%;
        }

        .message.own {
          flex-direction: row-reverse;
          align-self: flex-end;
        }

        .message.other {
          align-self: flex-start;
        }

        .message.optimistic {
          opacity: 0.7;
        }

        .message.failed .message-bubble {
          background: #fee2e2 !important;
        }

        .message-avatar {
          width: 28px;
          height: 28px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 10px;
          font-weight: 600;
          flex-shrink: 0;
          overflow: hidden;
          align-self: flex-end;
        }

        .message-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .message-content {
          display: flex;
          flex-direction: column;
          gap: 4px;
          min-width: 0;
        }

        .message.own .message-content {
          align-items: flex-end;
        }

        .message-sender {
          font-size: 11px;
          font-weight: 600;
          color: var(--rc-text-secondary);
          padding-left: 8px;
        }

        .message-bubble {
          padding: 10px 14px;
          border-radius: 18px;
          word-wrap: break-word;
          overflow-wrap: break-word;
        }

        .message.own .message-bubble {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
          border-bottom-right-radius: 4px;
        }

        .message.other .message-bubble {
          background: var(--rc-bg-secondary);
          color: var(--rc-text);
          border-bottom-left-radius: 4px;
        }

        .message-text {
          white-space: pre-wrap;
        }

        .message-meta {
          display: flex;
          gap: 6px;
          font-size: 10px;
          color: var(--rc-text-secondary);
          padding: 0 8px;
        }

        .message-status.failed {
          color: #ef4444;
        }

        .message-attachments {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }

        .attachment-image {
          border-radius: 12px;
          overflow: hidden;
          cursor: pointer;
          max-width: 200px;
        }

        .attachment-image img {
          display: block;
          width: 100%;
          height: auto;
        }

        .attachment-file {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 10px 12px;
          background: var(--rc-bg-secondary);
          border-radius: 12px;
          text-decoration: none;
          color: var(--rc-text);
          transition: background 0.15s;
        }

        .attachment-file:hover {
          background: var(--rc-border);
        }

        .file-icon {
          width: 24px;
          height: 24px;
          flex-shrink: 0;
        }

        .file-info {
          flex: 1;
          min-width: 0;
          display: flex;
          flex-direction: column;
        }

        .file-name {
          font-size: 13px;
          font-weight: 500;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }

        .file-size {
          font-size: 11px;
          color: var(--rc-text-secondary);
        }

        .download-icon {
          width: 20px;
          height: 20px;
          flex-shrink: 0;
          opacity: 0.5;
        }

        /* Typing indicator */
        .typing-indicator {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 8px 12px;
          color: var(--rc-text-secondary);
          font-size: 13px;
        }

        .typing-dots {
          display: flex;
          gap: 3px;
        }

        .typing-dots span {
          width: 6px;
          height: 6px;
          background: var(--rc-text-secondary);
          border-radius: 50%;
          animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) {
          animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
          animation-delay: 0.4s;
        }

        @keyframes typing {
          0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
          30% { transform: translateY(-4px); opacity: 1; }
        }

        /* Input area */
        .input-area {
          padding: 12px;
          border-top: 1px solid var(--rc-border);
          flex-shrink: 0;
          background: var(--rc-bg);
        }

        .pending-attachments {
          display: flex;
          gap: 8px;
          margin-bottom: 8px;
          flex-wrap: wrap;
        }

        .pending-attachment {
          position: relative;
          width: 60px;
          height: 60px;
          border-radius: 8px;
          overflow: hidden;
          background: var(--rc-bg-secondary);
        }

        .pending-attachment.error {
          border: 2px solid #ef4444;
        }

        .pending-attachment img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .pending-file {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .pending-file svg {
          width: 24px;
          height: 24px;
          color: var(--rc-text-secondary);
        }

        .upload-overlay {
          position: absolute;
          inset: 0;
          background: rgba(0, 0, 0, 0.5);
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .upload-overlay .spinner {
          border-color: rgba(255, 255, 255, 0.3);
          border-top-color: white;
        }

        .remove-attachment {
          position: absolute;
          top: 2px;
          right: 2px;
          width: 20px;
          height: 20px;
          background: rgba(0, 0, 0, 0.6);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }

        .remove-attachment svg {
          width: 12px;
          height: 12px;
          color: white;
        }

        .input-row {
          display: flex;
          align-items: flex-end;
          gap: 8px;
        }

        .input-btn {
          width: 36px;
          height: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          color: var(--rc-text-secondary);
          transition: background 0.15s, color 0.15s;
          flex-shrink: 0;
        }

        .input-btn:hover {
          background: var(--rc-bg-secondary);
        }

        .input-btn svg {
          width: 20px;
          height: 20px;
        }

        .send-btn.active {
          background: linear-gradient(135deg, var(--rc-primary), var(--rc-secondary));
          color: white;
        }

        .send-btn:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }

        .message-input {
          flex: 1;
          border: 1px solid var(--rc-border);
          border-radius: 18px;
          padding: 8px 14px;
          resize: none;
          outline: none;
          max-height: 100px;
          min-height: 36px;
          background: var(--rc-bg);
          color: var(--rc-text);
          line-height: 1.4;
        }

        .message-input:focus {
          border-color: var(--rc-primary);
        }

        .message-input::placeholder {
          color: var(--rc-text-secondary);
        }

        /* Footer slot */
        ::slotted([slot="footer"]) {
          display: block;
          margin-top: 8px;
        }

        /* Branding */
        .branding {
          text-align: center;
          padding: 8px;
          font-size: 11px;
          color: var(--rc-text-secondary);
          border-top: 1px solid var(--rc-border);
          flex-shrink: 0;
        }

        .branding a {
          color: inherit;
          text-decoration: none;
        }

        .branding a:hover {
          color: var(--rc-primary);
        }

        /* Lightbox */
        .lightbox {
          position: fixed;
          inset: 0;
          background: rgba(0, 0, 0, 0.9);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 1000000;
          animation: fadeIn 0.2s;
        }

        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        .lightbox img {
          max-width: 90%;
          max-height: 90%;
          object-fit: contain;
          border-radius: 8px;
        }

        .lightbox-close {
          position: absolute;
          top: 20px;
          right: 20px;
          width: 40px;
          height: 40px;
          background: rgba(255, 255, 255, 0.1);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: background 0.15s;
        }

        .lightbox-close:hover {
          background: rgba(255, 255, 255, 0.2);
        }

        .lightbox-close svg {
          width: 24px;
          height: 24px;
          color: white;
        }
      </style>

      <slot name="header">
        <div class="header">
          <div class="header-info">
            <div class="header-title">Chat</div>
            <div class="header-subtitle">
              <span class="status-dot"></span>
              ${this.wsConnected?"Connected":"Connecting..."}
            </div>
          </div>
        </div>
      </slot>

      ${this.renderContent()}

      ${this.showBranding?F`
        <div class="branding">
          <a href="https://github.com/TNortnern/reusable-chat" target="_blank">
            Powered by Reusable Chat
          </a>
        </div>
      `:J}

      ${this.renderLightbox()}
    `}},e.ReusableChatInline.styles=l`${o(we)}`,e.ReusableChatInline.initCounter=0,Ie([ge({attribute:"api-key",reflect:!0})],e.ReusableChatInline.prototype,"apiKey",2),Ie([ge({attribute:"user-id",reflect:!0})],e.ReusableChatInline.prototype,"userId",2),Ie([ge({attribute:"user-name",reflect:!0})],e.ReusableChatInline.prototype,"userName",2),Ie([ge({attribute:"user-email",reflect:!0})],e.ReusableChatInline.prototype,"userEmail",2),Ie([ge({attribute:"user-avatar",reflect:!0})],e.ReusableChatInline.prototype,"userAvatar",2),Ie([ge({attribute:"conversation-id",reflect:!0})],e.ReusableChatInline.prototype,"conversationId",2),Ie([ge({attribute:"theme",reflect:!0})],e.ReusableChatInline.prototype,"theme",2),Ie([ge({attribute:"accent-color",reflect:!0})],e.ReusableChatInline.prototype,"accentColor",2),Ie([ge({type:Boolean,attribute:"show-branding",reflect:!0})],e.ReusableChatInline.prototype,"showBranding",2),Ie([ge({attribute:"api-url",reflect:!0})],e.ReusableChatInline.prototype,"apiUrl",2),Ie([ge({attribute:"ws-host",reflect:!0})],e.ReusableChatInline.prototype,"wsHost",2),Ie([ge({attribute:"ws-key",reflect:!0})],e.ReusableChatInline.prototype,"wsKey",2),Ie([me()],e.ReusableChatInline.prototype,"wsConnected",2),Ie([me()],e.ReusableChatInline.prototype,"isLoading",2),Ie([me()],e.ReusableChatInline.prototype,"isLoadingMessages",2),Ie([me()],e.ReusableChatInline.prototype,"session",2),Ie([me()],e.ReusableChatInline.prototype,"messages",2),Ie([me()],e.ReusableChatInline.prototype,"messageInput",2),Ie([me()],e.ReusableChatInline.prototype,"pendingAttachments",2),Ie([me()],e.ReusableChatInline.prototype,"typingUsers",2),Ie([me()],e.ReusableChatInline.prototype,"lightboxImage",2),Ie([me()],e.ReusableChatInline.prototype,"isSending",2),Ie([me()],e.ReusableChatInline.prototype,"error",2),Ie([ve("#message-input")],e.ReusableChatInline.prototype,"messageInputEl",2),Ie([ve("#messages-container")],e.ReusableChatInline.prototype,"messagesContainer",2),Ie([ve("#file-input")],e.ReusableChatInline.prototype,"fileInput",2),e.ReusableChatInline=Ie([he("reusable-chat-inline")],e.ReusableChatInline),customElements.get("reusable-chat")||customElements.define("reusable-chat",e.ReusableChat),customElements.get("reusable-chat-inline")||customElements.define("reusable-chat-inline",e.ReusableChatInline),document.querySelectorAll("reusable-chat, reusable-chat-inline").forEach(e=>{}),console.log("[Reusable Chat] Widget loaded v1.0.0"),Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),e}({});
//# sourceMappingURL=widget.iife.js.map
