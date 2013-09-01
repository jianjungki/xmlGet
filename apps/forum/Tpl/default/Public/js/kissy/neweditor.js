/*
Copyright (c) 2009, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
version: 2.8.0r4
*/
if(typeof YAHOO=="undefined"||!YAHOO){var YAHOO={}}YAHOO.namespace=function(){var a=arguments,b=null,d,e,c;for(d=0;d<a.length;d=d+1){c=(""+a[d]).split(".");b=YAHOO;for(e=(c[0]=="YAHOO")?1:0;e<c.length;e=e+1){b[c[e]]=b[c[e]]||{};b=b[c[e]]}}return b};YAHOO.log=function(b,a,c){var d=YAHOO.widget.Logger;if(d&&d.log){return d.log(b,a,c)}else{return false}};YAHOO.register=function(d,i,a){var e=YAHOO.env.modules,c,f,g,h,b;if(!e[d]){e[d]={versions:[],builds:[]}}c=e[d];f=a.version;g=a.build;h=YAHOO.env.listeners;c.name=d;c.version=f;c.build=g;c.versions.push(f);c.builds.push(g);c.mainClass=i;for(b=0;b<h.length;b=b+1){h[b](c)}if(i){i.VERSION=f;i.BUILD=g}else{YAHOO.log("mainClass is undefined for module "+d,"warn")}};YAHOO.env=YAHOO.env||{modules:[],listeners:[]};YAHOO.env.getVersion=function(a){return YAHOO.env.modules[a]||null};YAHOO.env.ua=function(){var e=function(i){var h=0;return parseFloat(i.replace(/\./g,function(){return(h++==1)?"":"."}))},b=navigator,c={ie:0,opera:0,gecko:0,webkit:0,mobile:null,air:0,caja:b.cajaVersion,secure:false,os:null},f=navigator&&navigator.userAgent,d=window&&window.location,g=d&&d.href,a;c.secure=g&&(g.toLowerCase().indexOf("https")===0);if(f){if((/windows|win32/i).test(f)){c.os="windows"}else{if((/macintosh/i).test(f)){c.os="macintosh"}}if((/KHTML/).test(f)){c.webkit=1}a=f.match(/AppleWebKit\/([^\s]*)/);if(a&&a[1]){c.webkit=e(a[1]);if(/ Mobile\//.test(f)){c.mobile="Apple"}else{a=f.match(/NokiaN[^\/]*/);if(a){c.mobile=a[0]}}a=f.match(/AdobeAIR\/([^\s]*)/);if(a){c.air=a[0]}}if(!c.webkit){a=f.match(/Opera[\s\/]([^\s]*)/);if(a&&a[1]){c.opera=e(a[1]);a=f.match(/Opera Mini[^;]*/);if(a){c.mobile=a[0]}}else{a=f.match(/MSIE\s([^;]*)/);if(a&&a[1]){c.ie=e(a[1])}else{a=f.match(/Gecko\/([^\s]*)/);if(a){c.gecko=1;a=f.match(/rv:([^\s\)]*)/);if(a&&a[1]){c.gecko=e(a[1])}}}}}}return c}();(function(){YAHOO.namespace("util","widget","example");if("undefined"!==typeof YAHOO_config){var d=YAHOO_config.listener,a=YAHOO.env.listeners,b=true,c;if(d){for(c=0;c<a.length;c++){if(a[c]==d){b=false;break}}if(b){a.push(d)}}}})();YAHOO.lang=YAHOO.lang||{};(function(){var h=YAHOO.lang,a=Object.prototype,b="[object Array]",g="[object Function]",c="[object Object]",e=[],d=["toString","valueOf"],f={isArray:function(i){return a.toString.apply(i)===b},isBoolean:function(i){return typeof i==="boolean"},isFunction:function(i){return(typeof i==="function")||a.toString.apply(i)===g},isNull:function(i){return i===null},isNumber:function(i){return typeof i==="number"&&isFinite(i)},isObject:function(i){return(i&&(typeof i==="object"||h.isFunction(i)))||false},isString:function(i){return typeof i==="string"},isUndefined:function(i){return typeof i==="undefined"},_IEEnumFix:(YAHOO.env.ua.ie)?function(j,k){var l,m,i;for(l=0;l<d.length;l=l+1){m=d[l];i=k[m];if(h.isFunction(i)&&i!=a[m]){j[m]=i}}}:function(){},extend:function(i,m,j){if(!m||!i){throw new Error("extend failed, please check that all dependencies are included.")}var k=function(){},l;k.prototype=m.prototype;i.prototype=new k();i.prototype.constructor=i;i.superclass=m.prototype;if(m.prototype.constructor==a.constructor){m.prototype.constructor=m}if(j){for(l in j){if(h.hasOwnProperty(j,l)){i.prototype[l]=j[l]}}h._IEEnumFix(i.prototype,j)}},augmentObject:function(n,i){if(!i||!n){throw new Error("Absorb failed, verify dependencies.")}var l=arguments,j,m,k=l[2];if(k&&k!==true){for(j=2;j<l.length;j=j+1){n[l[j]]=i[l[j]]}}else{for(m in i){if(k||!(m in n)){n[m]=i[m]}}h._IEEnumFix(n,i)}},augmentProto:function(i,j){if(!j||!i){throw new Error("Augment failed, verify dependencies.")}var l=[i.prototype,j.prototype],k;for(k=2;k<arguments.length;k=k+1){l.push(arguments[k])}h.augmentObject.apply(this,l)},dump:function(q,l){var o,m,j=[],i="{...}",p="f(){...}",k=", ",n=" => ";if(!h.isObject(q)){return q+""}else{if(q instanceof Date||("nodeType" in q&&"tagName" in q)){return q}else{if(h.isFunction(q)){return p}}}l=(h.isNumber(l))?l:3;if(h.isArray(q)){j.push("[");for(o=0,m=q.length;o<m;o=o+1){if(h.isObject(q[o])){j.push((l>0)?h.dump(q[o],l-1):i)}else{j.push(q[o])}j.push(k)}if(j.length>1){j.pop()}j.push("]")}else{j.push("{");for(o in q){if(h.hasOwnProperty(q,o)){j.push(o+n);if(h.isObject(q[o])){j.push((l>0)?h.dump(q[o],l-1):i)}else{j.push(q[o])}j.push(k)}}if(j.length>1){j.pop()}j.push("}")}return j.join("")},substitute:function(i,x,p){var t,u,v,m,l,j,n=[],w,s="dump",o=" ",y="{",k="}",q,r;for(;;){t=i.lastIndexOf(y);if(t<0){break}u=i.indexOf(k,t);if(t+1>=u){break}w=i.substring(t+1,u);m=w;j=null;v=m.indexOf(o);if(v>-1){j=m.substring(v+1);m=m.substring(0,v)}l=x[m];if(p){l=p(m,l,j)}if(h.isObject(l)){if(h.isArray(l)){l=h.dump(l,parseInt(j,10))}else{j=j||"";q=j.indexOf(s);if(q>-1){j=j.substring(4)}r=l.toString();if(r===c||q>-1){l=h.dump(l,parseInt(j,10))}else{l=r}}}else{if(!h.isString(l)&&!h.isNumber(l)){l="~-"+n.length+"-~";n[n.length]=w}}i=i.substring(0,t)+l+i.substring(u+1)}for(t=n.length-1;t>=0;t=t-1){i=i.replace(new RegExp("~-"+t+"-~"),"{"+n[t]+"}","g")}return i},trim:function(j){try{return j.replace(/^\s+|\s+$/g,"")}catch(i){return j}},merge:function(){var i={},k=arguments,l=k.length,j;for(j=0;j<l;j=j+1){h.augmentObject(i,k[j],true)}return i},later:function(j,p,i,n,m){j=j||0;p=p||{};var o=i,k=n,l,q;if(h.isString(i)){o=p[i]}if(!o){throw new TypeError("method undefined")}if(k&&!h.isArray(k)){k=[n]}l=function(){o.apply(p,k||e)};q=(m)?setInterval(l,j):setTimeout(l,j);return{interval:m,cancel:function(){if(this.interval){clearInterval(q)}else{clearTimeout(q)}}}},isValue:function(i){return(h.isObject(i)||h.isString(i)||h.isNumber(i)||h.isBoolean(i))}};h.hasOwnProperty=(a.hasOwnProperty)?function(j,i){return j&&j.hasOwnProperty(i)}:function(j,i){return !h.isUndefined(j[i])&&j.constructor.prototype[i]!==j[i]};f.augmentObject(h,f,true);YAHOO.util.Lang=h;h.augment=h.augmentProto;YAHOO.augment=h.augmentProto;YAHOO.extend=h.extend})();YAHOO.register("yahoo",YAHOO,{version:"2.8.0r4",build:"2449"});(function(){YAHOO.env._id_counter=YAHOO.env._id_counter||0;var ao=YAHOO.util,ai=YAHOO.lang,aE=YAHOO.env.ua,at=YAHOO.lang.trim,aN={},aJ={},ag=/^t(?:able|d|h)$/i,y=/color$/i,aj=window.document,z=aj.documentElement,aM="ownerDocument",aD="defaultView",av="documentElement",ax="compatMode",aP="offsetLeft",ae="offsetTop",aw="offsetParent",x="parentNode",aF="nodeType",aq="tagName",af="scrollLeft",aI="scrollTop",ad="getBoundingClientRect",au="getComputedStyle",aQ="currentStyle",ah="CSS1Compat",aO="BackCompat",aK="class",an="className",ak="",ar=" ",ay="(?:^|\\s)",aG="(?= |$)",Y="g",aB="position",aL="fixed",G="relative",aH="left",aC="top",az="medium",aA="borderLeftWidth",ac="borderTopWidth",ap=aE.opera,al=aE.webkit,am=aE.gecko,aa=aE.ie;ao.Dom={CUSTOM_ATTRIBUTES:(!z.hasAttribute)?{"for":"htmlFor","class":an}:{htmlFor:"for",className:aK},DOT_ATTRIBUTES:{},get:function(f){var c,a,e,g,d,b;if(f){if(f[aF]||f.item){return f}if(typeof f==="string"){c=f;f=aj.getElementById(f);b=(f)?f.attributes:null;if(f&&b&&b.id&&b.id.value===c){return f}else{if(f&&aj.all){f=null;a=aj.all[c];for(g=0,d=a.length;g<d;++g){if(a[g].id===c){return a[g]}}}}return f}if(YAHOO.util.Element&&f instanceof YAHOO.util.Element){f=f.get("element")}if("length" in f){e=[];for(g=0,d=f.length;g<d;++g){e[e.length]=ao.Dom.get(f[g])}return e}return f}return null},getComputedStyle:function(a,b){if(window[au]){return a[aM][aD][au](a,null)[b]}else{if(a[aQ]){return ao.Dom.IE_ComputedStyle.get(a,b)}}},getStyle:function(a,b){return ao.Dom.batch(a,ao.Dom._getStyle,b)},_getStyle:function(){if(window[au]){return function(b,d){d=(d==="float")?d="cssFloat":ao.Dom._toCamel(d);var a=b.style[d],c;if(!a){c=b[aM][aD][au](b,null);if(c){a=c[d]}}return a}}else{if(z[aQ]){return function(b,e){var a;switch(e){case"opacity":a=100;try{a=b.filters["DXImageTransform.Microsoft.Alpha"].opacity}catch(d){try{a=b.filters("alpha").opacity}catch(c){}}return a/100;case"float":e="styleFloat";default:e=ao.Dom._toCamel(e);a=b[aQ]?b[aQ][e]:null;return(b.style[e]||a)}}}}}(),setStyle:function(b,c,a){ao.Dom.batch(b,ao.Dom._setStyle,{prop:c,val:a})},_setStyle:function(){if(aa){return function(c,b){var a=ao.Dom._toCamel(b.prop),d=b.val;if(c){switch(a){case"opacity":if(ai.isString(c.style.filter)){c.style.filter="alpha(opacity="+d*100+")";if(!c[aQ]||!c[aQ].hasLayout){c.style.zoom=1}}break;case"float":a="styleFloat";default:c.style[a]=d}}else{}}}else{return function(c,b){var a=ao.Dom._toCamel(b.prop),d=b.val;if(c){if(a=="float"){a="cssFloat"}c.style[a]=d}else{}}}}(),getXY:function(a){return ao.Dom.batch(a,ao.Dom._getXY)},_canPosition:function(a){return(ao.Dom._getStyle(a,"display")!=="none"&&ao.Dom._inDoc(a))},_getXY:function(){if(aj[av][ad]){return function(j){var i,a,h,c,d,e,f,l,k,g=Math.floor,b=false;if(ao.Dom._canPosition(j)){h=j[ad]();c=j[aM];i=ao.Dom.getDocumentScrollLeft(c);a=ao.Dom.getDocumentScrollTop(c);b=[g(h[aH]),g(h[aC])];if(aa&&aE.ie<8){d=2;e=2;f=c[ax];if(aE.ie===6){if(f!==aO){d=0;e=0}}if((f===aO)){l=ab(c[av],aA);k=ab(c[av],ac);if(l!==az){d=parseInt(l,10)}if(k!==az){e=parseInt(k,10)}}b[0]-=d;b[1]-=e}if((a||i)){b[0]+=i;b[1]+=a}b[0]=g(b[0]);b[1]=g(b[1])}else{}return b}}else{return function(h){var a,g,f,d,c,e=false,b=h;if(ao.Dom._canPosition(h)){e=[h[aP],h[ae]];a=ao.Dom.getDocumentScrollLeft(h[aM]);g=ao.Dom.getDocumentScrollTop(h[aM]);c=((am||aE.webkit>519)?true:false);while((b=b[aw])){e[0]+=b[aP];e[1]+=b[ae];if(c){e=ao.Dom._calcBorders(b,e)}}if(ao.Dom._getStyle(h,aB)!==aL){b=h;while((b=b[x])&&b[aq]){f=b[aI];d=b[af];if(am&&(ao.Dom._getStyle(b,"overflow")!=="visible")){e=ao.Dom._calcBorders(b,e)}if(f||d){e[0]-=d;e[1]-=f}}e[0]+=a;e[1]+=g}else{if(ap){e[0]-=a;e[1]-=g}else{if(al||am){e[0]+=a;e[1]+=g}}}e[0]=Math.floor(e[0]);e[1]=Math.floor(e[1])}else{}return e}}}(),getX:function(a){var b=function(c){return ao.Dom.getXY(c)[0]};return ao.Dom.batch(a,b,ao.Dom,true)},getY:function(a){var b=function(c){return ao.Dom.getXY(c)[1]};return ao.Dom.batch(a,b,ao.Dom,true)},setXY:function(b,a,c){ao.Dom.batch(b,ao.Dom._setXY,{pos:a,noRetry:c})},_setXY:function(i,f){var e=ao.Dom._getStyle(i,aB),g=ao.Dom.setStyle,b=f.pos,a=f.noRetry,d=[parseInt(ao.Dom.getComputedStyle(i,aH),10),parseInt(ao.Dom.getComputedStyle(i,aC),10)],c,h;if(e=="static"){e=G;g(i,aB,e)}c=ao.Dom._getXY(i);if(!b||c===false){return false}if(isNaN(d[0])){d[0]=(e==G)?0:i[aP]}if(isNaN(d[1])){d[1]=(e==G)?0:i[ae]}if(b[0]!==null){g(i,aH,b[0]-c[0]+d[0]+"px")}if(b[1]!==null){g(i,aC,b[1]-c[1]+d[1]+"px")}if(!a){h=ao.Dom._getXY(i);if((b[0]!==null&&h[0]!=b[0])||(b[1]!==null&&h[1]!=b[1])){ao.Dom._setXY(i,{pos:b,noRetry:true})}}},setX:function(b,a){ao.Dom.setXY(b,[a,null])},setY:function(a,b){ao.Dom.setXY(a,[null,b])},getRegion:function(a){var b=function(c){var d=false;if(ao.Dom._canPosition(c)){d=ao.Region.getRegion(c)}else{}return d};return ao.Dom.batch(a,b,ao.Dom,true)},getClientWidth:function(){return ao.Dom.getViewportWidth()},getClientHeight:function(){return ao.Dom.getViewportHeight()},getElementsByClassName:function(f,b,e,c,j,d){b=b||"*";e=(e)?ao.Dom.get(e):null||aj;if(!e){return[]}var a=[],k=e.getElementsByTagName(b),h=ao.Dom.hasClass;for(var i=0,g=k.length;i<g;++i){if(h(k[i],f)){a[a.length]=k[i]}}if(c){ao.Dom.batch(a,c,j,d)}return a},hasClass:function(b,a){return ao.Dom.batch(b,ao.Dom._hasClass,a)},_hasClass:function(a,c){var b=false,d;if(a&&c){d=ao.Dom._getAttribute(a,an)||ak;if(c.exec){b=c.test(d)}else{b=c&&(ar+d+ar).indexOf(ar+c+ar)>-1}}else{}return b},addClass:function(b,a){return ao.Dom.batch(b,ao.Dom._addClass,a)},_addClass:function(a,c){var b=false,d;if(a&&c){d=ao.Dom._getAttribute(a,an)||ak;if(!ao.Dom._hasClass(a,c)){ao.Dom.setAttribute(a,an,at(d+ar+c));b=true}}else{}return b},removeClass:function(b,a){return ao.Dom.batch(b,ao.Dom._removeClass,a)},_removeClass:function(f,a){var e=false,d,c,b;if(f&&a){d=ao.Dom._getAttribute(f,an)||ak;ao.Dom.setAttribute(f,an,d.replace(ao.Dom._getClassRegex(a),ak));c=ao.Dom._getAttribute(f,an);if(d!==c){ao.Dom.setAttribute(f,an,at(c));e=true;if(ao.Dom._getAttribute(f,an)===""){b=(f.hasAttribute&&f.hasAttribute(aK))?aK:an;f.removeAttribute(b)}}}else{}return e},replaceClass:function(a,c,b){return ao.Dom.batch(a,ao.Dom._replaceClass,{from:c,to:b})},_replaceClass:function(g,a){var f,c,e,b=false,d;if(g&&a){c=a.from;e=a.to;if(!e){b=false}else{if(!c){b=ao.Dom._addClass(g,a.to)}else{if(c!==e){d=ao.Dom._getAttribute(g,an)||ak;f=(ar+d.replace(ao.Dom._getClassRegex(c),ar+e)).split(ao.Dom._getClassRegex(e));f.splice(1,0,ar+e);ao.Dom.setAttribute(g,an,at(f.join(ak)));b=true}}}}else{}return b},generateId:function(b,a){a=a||"yui-gen";var c=function(e){if(e&&e.id){return e.id}var d=a+YAHOO.env._id_counter++;if(e){if(e[aM]&&e[aM].getElementById(d)){return ao.Dom.generateId(e,d+a)}e.id=d}return d};return ao.Dom.batch(b,c,ao.Dom,true)||c.apply(ao.Dom,arguments)},isAncestor:function(c,a){c=ao.Dom.get(c);a=ao.Dom.get(a);var b=false;if((c&&a)&&(c[aF]&&a[aF])){if(c.contains&&c!==a){b=c.contains(a)}else{if(c.compareDocumentPosition){b=!!(c.compareDocumentPosition(a)&16)}}}else{}return b},inDocument:function(a,b){return ao.Dom._inDoc(ao.Dom.get(a),b)},_inDoc:function(c,a){var b=false;if(c&&c[aq]){a=a||c[aM];b=ao.Dom.isAncestor(a[av],c)}else{}return b},getElementsBy:function(a,b,f,d,i,e,c){b=b||"*";f=(f)?ao.Dom.get(f):null||aj;if(!f){return[]}var j=[],k=f.getElementsByTagName(b);for(var h=0,g=k.length;h<g;++h){if(a(k[h])){if(c){j=k[h];break}else{j[j.length]=k[h]}}}if(d){ao.Dom.batch(j,d,i,e)}return j},getElementBy:function(a,b,c){return ao.Dom.getElementsBy(a,b,c,null,null,null,true)},batch:function(a,c,f,e){var g=[],d=(e)?f:window;a=(a&&(a[aq]||a.item))?a:ao.Dom.get(a);if(a&&c){if(a[aq]||a.length===undefined){return c.call(d,a,f)}for(var b=0;b<a.length;++b){g[g.length]=c.call(d,a[b],f)}}else{return false}return g},getDocumentHeight:function(){var b=(aj[ax]!=ah||al)?aj.body.scrollHeight:z.scrollHeight,a=Math.max(b,ao.Dom.getViewportHeight());return a},getDocumentWidth:function(){var b=(aj[ax]!=ah||al)?aj.body.scrollWidth:z.scrollWidth,a=Math.max(b,ao.Dom.getViewportWidth());return a},getViewportHeight:function(){var a=self.innerHeight,b=aj[ax];if((b||aa)&&!ap){a=(b==ah)?z.clientHeight:aj.body.clientHeight}return a},getViewportWidth:function(){var a=self.innerWidth,b=aj[ax];if(b||aa){a=(b==ah)?z.clientWidth:aj.body.clientWidth}return a},getAncestorBy:function(a,b){while((a=a[x])){if(ao.Dom._testElement(a,b)){return a}}return null},getAncestorByClassName:function(c,b){c=ao.Dom.get(c);if(!c){return null}var a=function(d){return ao.Dom.hasClass(d,b)};return ao.Dom.getAncestorBy(c,a)},getAncestorByTagName:function(c,b){c=ao.Dom.get(c);if(!c){return null}var a=function(d){return d[aq]&&d[aq].toUpperCase()==b.toUpperCase()};return ao.Dom.getAncestorBy(c,a)},getPreviousSiblingBy:function(a,b){while(a){a=a.previousSibling;if(ao.Dom._testElement(a,b)){return a}}return null},getPreviousSibling:function(a){a=ao.Dom.get(a);if(!a){return null}return ao.Dom.getPreviousSiblingBy(a)},getNextSiblingBy:function(a,b){while(a){a=a.nextSibling;if(ao.Dom._testElement(a,b)){return a}}return null},getNextSibling:function(a){a=ao.Dom.get(a);if(!a){return null}return ao.Dom.getNextSiblingBy(a)},getFirstChildBy:function(b,a){var c=(ao.Dom._testElement(b.firstChild,a))?b.firstChild:null;return c||ao.Dom.getNextSiblingBy(b.firstChild,a)},getFirstChild:function(a,b){a=ao.Dom.get(a);if(!a){return null}return ao.Dom.getFirstChildBy(a)},getLastChildBy:function(b,a){if(!b){return null}var c=(ao.Dom._testElement(b.lastChild,a))?b.lastChild:null;return c||ao.Dom.getPreviousSiblingBy(b.lastChild,a)},getLastChild:function(a){a=ao.Dom.get(a);return ao.Dom.getLastChildBy(a)},getChildrenBy:function(c,d){var a=ao.Dom.getFirstChildBy(c,d),b=a?[a]:[];ao.Dom.getNextSiblingBy(a,function(e){if(!d||d(e)){b[b.length]=e}return false});return b},getChildren:function(a){a=ao.Dom.get(a);if(!a){}return ao.Dom.getChildrenBy(a)},getDocumentScrollLeft:function(a){a=a||aj;return Math.max(a[av].scrollLeft,a.body.scrollLeft)},getDocumentScrollTop:function(a){a=a||aj;return Math.max(a[av].scrollTop,a.body.scrollTop)},insertBefore:function(b,a){b=ao.Dom.get(b);a=ao.Dom.get(a);if(!b||!a||!a[x]){return null}return a[x].insertBefore(b,a)},insertAfter:function(b,a){b=ao.Dom.get(b);a=ao.Dom.get(a);if(!b||!a||!a[x]){return null}if(a.nextSibling){return a[x].insertBefore(b,a.nextSibling)}else{return a[x].appendChild(b)}},getClientRegion:function(){var a=ao.Dom.getDocumentScrollTop(),c=ao.Dom.getDocumentScrollLeft(),d=ao.Dom.getViewportWidth()+c,b=ao.Dom.getViewportHeight()+a;return new ao.Region(a,d,b,c)},setAttribute:function(c,b,a){ao.Dom.batch(c,ao.Dom._setAttribute,{attr:b,val:a})},_setAttribute:function(a,c){var b=ao.Dom._toCamel(c.attr),d=c.val;if(a&&a.setAttribute){if(ao.Dom.DOT_ATTRIBUTES[b]){a[b]=d}else{b=ao.Dom.CUSTOM_ATTRIBUTES[b]||b;a.setAttribute(b,d)}}else{}},getAttribute:function(b,a){return ao.Dom.batch(b,ao.Dom._getAttribute,a)},_getAttribute:function(c,b){var a;b=ao.Dom.CUSTOM_ATTRIBUTES[b]||b;if(c&&c.getAttribute){a=c.getAttribute(b,2)}else{}return a},_toCamel:function(c){var a=aN;function b(e,d){return d.toUpperCase()}return a[c]||(a[c]=c.indexOf("-")===-1?c:c.replace(/-([a-z])/gi,b))},_getClassRegex:function(b){var a;if(b!==undefined){if(b.exec){a=b}else{a=aJ[b];if(!a){b=b.replace(ao.Dom._patterns.CLASS_RE_TOKENS,"\\$1");a=aJ[b]=new RegExp(ay+b+aG,Y)}}}return a},_patterns:{ROOT_TAG:/^body|html$/i,CLASS_RE_TOKENS:/([\.\(\)\^\$\*\+\?\|\[\]\{\}\\])/g},_testElement:function(a,b){return a&&a[aF]==1&&(!b||b(a))},_calcBorders:function(a,d){var c=parseInt(ao.Dom[au](a,ac),10)||0,b=parseInt(ao.Dom[au](a,aA),10)||0;if(am){if(ag.test(a[aq])){c=0;b=0}}d[0]+=b;d[1]+=c;return d}};var ab=ao.Dom[au];if(aE.opera){ao.Dom[au]=function(c,b){var a=ab(c,b);if(y.test(b)){a=ao.Dom.Color.toRGB(a)}return a}}if(aE.webkit){ao.Dom[au]=function(c,b){var a=ab(c,b);if(a==="rgba(0, 0, 0, 0)"){a="transparent"}return a}}if(aE.ie&&aE.ie>=8&&aj.documentElement.hasAttribute){ao.Dom.DOT_ATTRIBUTES.type=true}})();YAHOO.util.Region=function(c,b,a,d){this.top=c;this.y=c;this[1]=c;this.right=b;this.bottom=a;this.left=d;this.x=d;this[0]=d;this.width=this.right-this.left;this.height=this.bottom-this.top};YAHOO.util.Region.prototype.contains=function(a){return(a.left>=this.left&&a.right<=this.right&&a.top>=this.top&&a.bottom<=this.bottom)};YAHOO.util.Region.prototype.getArea=function(){return((this.bottom-this.top)*(this.right-this.left))};YAHOO.util.Region.prototype.intersect=function(b){var d=Math.max(this.top,b.top),c=Math.min(this.right,b.right),a=Math.min(this.bottom,b.bottom),e=Math.max(this.left,b.left);if(a>=d&&c>=e){return new YAHOO.util.Region(d,c,a,e)}else{return null}};YAHOO.util.Region.prototype.union=function(b){var d=Math.min(this.top,b.top),c=Math.max(this.right,b.right),a=Math.max(this.bottom,b.bottom),e=Math.min(this.left,b.left);return new YAHOO.util.Region(d,c,a,e)};YAHOO.util.Region.prototype.toString=function(){return("Region {top: "+this.top+", right: "+this.right+", bottom: "+this.bottom+", left: "+this.left+", height: "+this.height+", width: "+this.width+"}")};YAHOO.util.Region.getRegion=function(d){var b=YAHOO.util.Dom.getXY(d),e=b[1],c=b[0]+d.offsetWidth,a=b[1]+d.offsetHeight,f=b[0];return new YAHOO.util.Region(e,c,a,f)};YAHOO.util.Point=function(a,b){if(YAHOO.lang.isArray(a)){b=a[1];a=a[0]}YAHOO.util.Point.superclass.constructor.call(this,b,a,b,a)};YAHOO.extend(YAHOO.util.Point,YAHOO.util.Region);(function(){var v=YAHOO.util,w="clientTop",r="clientLeft",n="parentNode",m="right",a="hasLayout",o="px",c="opacity",l="auto",t="borderLeftWidth",q="borderTopWidth",h="borderRightWidth",b="borderBottomWidth",e="visible",g="transparent",j="height",s="width",p="style",d="currentStyle",f=/^width|height$/,i=/^(\d[.\d]*)+(em|ex|px|gd|rem|vw|vh|vm|ch|mm|cm|in|pt|pc|deg|rad|ms|s|hz|khz|%){1}?/i,k={get:function(A,y){var z="",x=A[d][y];if(y===c){z=v.Dom.getStyle(A,c)}else{if(!x||(x.indexOf&&x.indexOf(o)>-1)){z=x}else{if(v.Dom.IE_COMPUTED[y]){z=v.Dom.IE_COMPUTED[y](A,y)}else{if(i.test(x)){z=v.Dom.IE.ComputedStyle.getPixel(A,y)}else{z=x}}}}return z},getOffset:function(A,z){var x=A[d][z],E=z.charAt(0).toUpperCase()+z.substr(1),D="offset"+E,C="pixel"+E,y="",B;if(x==l){B=A[D];if(B===undefined){y=0}y=B;if(f.test(z)){A[p][z]=B;if(A[D]>B){y=B-(A[D]-B)}A[p][z]=l}}else{if(!A[p][C]&&!A[p][z]){A[p][z]=x}y=A[p][C]}return y+o},getBorderWidth:function(z,x){var y=null;if(!z[d][a]){z[p].zoom=1}switch(x){case q:y=z[w];break;case b:y=z.offsetHeight-z.clientHeight-z[w];break;case t:y=z[r];break;case h:y=z.offsetWidth-z.clientWidth-z[r];break}return y+o},getPixel:function(A,B){var y=null,x=A[d][m],z=A[d][B];A[p][m]=z;y=A[p].pixelRight;A[p][m]=x;return y+o},getMargin:function(y,z){var x;if(y[d][z]==l){x=0+o}else{x=v.Dom.IE.ComputedStyle.getPixel(y,z)}return x},getVisibility:function(y,z){var x;while((x=y[d])&&x[z]=="inherit"){y=y[n]}return(x)?x[z]:e},getColor:function(x,y){return v.Dom.Color.toRGB(x[d][y])||g},getBorderColor:function(z,A){var y=z[d],x=y[A]||y.color;return v.Dom.Color.toRGB(v.Dom.Color.toHex(x))}},u={};u.top=u.right=u.bottom=u.left=u[s]=u[j]=k.getOffset;u.color=k.getColor;u[q]=u[h]=u[b]=u[t]=k.getBorderWidth;u.marginTop=u.marginRight=u.marginBottom=u.marginLeft=k.getMargin;u.visibility=k.getVisibility;u.borderColor=u.borderTopColor=u.borderRightColor=u.borderBottomColor=u.borderLeftColor=k.getBorderColor;v.Dom.IE_COMPUTED=u;v.Dom.IE_ComputedStyle=k})();(function(){var c="toString",a=parseInt,d=RegExp,b=YAHOO.util;b.Dom.Color={KEYWORDS:{black:"000",silver:"c0c0c0",gray:"808080",white:"fff",maroon:"800000",red:"f00",purple:"800080",fuchsia:"f0f",green:"008000",lime:"0f0",olive:"808000",yellow:"ff0",navy:"000080",blue:"00f",teal:"008080",aqua:"0ff"},re_RGB:/^rgb\(([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\)$/i,re_hex:/^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i,re_hex3:/([0-9A-F])/gi,toRGB:function(e){if(!b.Dom.Color.re_RGB.test(e)){e=b.Dom.Color.toHex(e)}if(b.Dom.Color.re_hex.exec(e)){e="rgb("+[a(d.$1,16),a(d.$2,16),a(d.$3,16)].join(", ")+")"}return e},toHex:function(e){e=b.Dom.Color.KEYWORDS[e]||e;if(b.Dom.Color.re_RGB.exec(e)){var f=(d.$1.length===1)?"0"+d.$1:Number(d.$1),g=(d.$2.length===1)?"0"+d.$2:Number(d.$2),h=(d.$3.length===1)?"0"+d.$3:Number(d.$3);e=[f[c](16),g[c](16),h[c](16)].join("")}if(e.length<6){e=e.replace(b.Dom.Color.re_hex3,"$1$1")}if(e!=="transparent"&&e.indexOf("#")<0){e="#"+e}return e.toLowerCase()}}}());YAHOO.register("dom",YAHOO.util.Dom,{version:"2.8.0r4",build:"2449"});YAHOO.util.CustomEvent=function(d,e,f,a,c){this.type=d;this.scope=e||window;this.silent=f;this.fireOnce=c;this.fired=false;this.firedWith=null;this.signature=a||YAHOO.util.CustomEvent.LIST;this.subscribers=[];if(!this.silent){}var b="_YUICEOnSubscribe";if(d!==b){this.subscribeEvent=new YAHOO.util.CustomEvent(b,this,true)}this.lastError=null};YAHOO.util.CustomEvent.LIST=0;YAHOO.util.CustomEvent.FLAT=1;YAHOO.util.CustomEvent.prototype={subscribe:function(d,c,b){if(!d){throw new Error("Invalid callback for subscriber to '"+this.type+"'")}if(this.subscribeEvent){this.subscribeEvent.fire(d,c,b)}var a=new YAHOO.util.Subscriber(d,c,b);if(this.fireOnce&&this.fired){this.notify(a,this.firedWith)}else{this.subscribers.push(a)}},unsubscribe:function(d,b){if(!d){return this.unsubscribeAll()}var c=false;for(var f=0,a=this.subscribers.length;f<a;++f){var e=this.subscribers[f];if(e&&e.contains(d,b)){this._delete(f);c=true}}return c},fire:function(){this.lastError=null;var b=[],a=this.subscribers.length;var f=[].slice.call(arguments,0),g=true,d,h=false;if(this.fireOnce){if(this.fired){return true}else{this.firedWith=f}}this.fired=true;if(!a&&this.silent){return true}if(!this.silent){}var e=this.subscribers.slice();for(d=0;d<a;++d){var c=e[d];if(!c){h=true}else{g=this.notify(c,f);if(false===g){if(!this.silent){}break}}}return(g!==false)},notify:function(d,g){var h,b=null,e=d.getScope(this.scope),a=YAHOO.util.Event.throwErrors;if(!this.silent){}if(this.signature==YAHOO.util.CustomEvent.FLAT){if(g.length>0){b=g[0]}try{h=d.fn.call(e,b,d.obj)}catch(c){this.lastError=c;if(a){throw c}}}else{try{h=d.fn.call(e,this.type,g,d.obj)}catch(f){this.lastError=f;if(a){throw f}}}return h},unsubscribeAll:function(){var a=this.subscribers.length,b;for(b=a-1;b>-1;b--){this._delete(b)}this.subscribers=[];return a},_delete:function(a){var b=this.subscribers[a];if(b){delete b.fn;delete b.obj}this.subscribers.splice(a,1)},toString:function(){return"CustomEvent: '"+this.type+"', context: "+this.scope}};YAHOO.util.Subscriber=function(a,c,b){this.fn=a;this.obj=YAHOO.lang.isUndefined(c)?null:c;this.overrideContext=b};YAHOO.util.Subscriber.prototype.getScope=function(a){if(this.overrideContext){if(this.overrideContext===true){return this.obj}else{return this.overrideContext}}return a};YAHOO.util.Subscriber.prototype.contains=function(a,b){if(b){return(this.fn==a&&this.obj==b)}else{return(this.fn==a)}};YAHOO.util.Subscriber.prototype.toString=function(){return"Subscriber { obj: "+this.obj+", overrideContext: "+(this.overrideContext||"no")+" }"};if(!YAHOO.util.Event){YAHOO.util.Event=function(){var h=false,g=[],e=[],d=0,j=[],c=0,b={63232:38,63233:40,63234:37,63235:39,63276:33,63277:34,25:9},a=YAHOO.env.ua.ie,i="focusin",f="focusout";return{POLL_RETRYS:500,POLL_INTERVAL:40,EL:0,TYPE:1,FN:2,WFN:3,UNLOAD_OBJ:3,ADJ_SCOPE:4,OBJ:5,OVERRIDE:6,CAPTURE:7,lastError:null,isSafari:YAHOO.env.ua.webkit,webkit:YAHOO.env.ua.webkit,isIE:a,_interval:null,_dri:null,_specialTypes:{focusin:(a?"focusin":"focus"),focusout:(a?"focusout":"blur")},DOMReady:false,throwErrors:false,startInterval:function(){if(!this._interval){this._interval=YAHOO.lang.later(this.POLL_INTERVAL,this,this._tryPreloadAttach,null,true)}},onAvailable:function(m,q,o,n,p){var l=(YAHOO.lang.isString(m))?[m]:m;for(var k=0;k<l.length;k=k+1){j.push({id:l[k],fn:q,obj:o,overrideContext:n,checkReady:p})}d=this.POLL_RETRYS;this.startInterval()},onContentReady:function(m,l,k,n){this.onAvailable(m,l,k,n,true)},onDOMReady:function(){this.DOMReadyEvent.subscribe.apply(this.DOMReadyEvent,arguments)},_addListener:function(w,y,n,t,p,k){if(!n||!n.call){return false}if(this._isValidCollection(w)){var m=true;for(var s=0,q=w.length;s<q;++s){m=this.on(w[s],y,n,t,p)&&m}return m}else{if(YAHOO.lang.isString(w)){var u=this.getEl(w);if(u){w=u}else{this.onAvailable(w,function(){YAHOO.util.Event._addListener(w,y,n,t,p,k)});return true}}}if(!w){return false}if("unload"==y&&t!==this){e[e.length]=[w,y,n,t,p];return true}var x=w;if(p){if(p===true){x=t}else{x=p}}var v=function(z){return n.call(x,YAHOO.util.Event.getEvent(z,w),t)};var l=[w,y,n,v,x,t,p,k];var r=g.length;g[r]=l;try{this._simpleAdd(w,y,v,k)}catch(o){this.lastError=o;this.removeListener(w,y,n);return false}return true},_getType:function(k){return this._specialTypes[k]||k},addListener:function(p,m,k,o,n){var l=((m==i||m==f)&&!YAHOO.env.ua.ie)?true:false;return this._addListener(p,this._getType(m),k,o,n,l)},addFocusListener:function(k,l,n,m){return this.on(k,i,l,n,m)},removeFocusListener:function(k,l){return this.removeListener(k,i,l)},addBlurListener:function(k,l,n,m){return this.on(k,f,l,n,m)},removeBlurListener:function(k,l){return this.removeListener(k,f,l)},removeListener:function(t,u,n){var s,p,k;u=this._getType(u);if(typeof t=="string"){t=this.getEl(t)}else{if(this._isValidCollection(t)){var m=true;for(s=t.length-1;s>-1;s--){m=(this.removeListener(t[s],u,n)&&m)}return m}}if(!n||!n.call){return this.purgeElement(t,false,u)}if("unload"==u){for(s=e.length-1;s>-1;s--){k=e[s];if(k&&k[0]==t&&k[1]==u&&k[2]==n){e.splice(s,1);return true}}return false}var r=null;var q=arguments[3];if("undefined"===typeof q){q=this._getCacheIndex(g,t,u,n)}if(q>=0){r=g[q]}if(!t||!r){return false}var l=r[this.CAPTURE]===true?true:false;try{this._simpleRemove(t,u,r[this.WFN],l)}catch(o){this.lastError=o;return false}delete g[q][this.WFN];delete g[q][this.FN];g.splice(q,1);return true},getTarget:function(m,k){var l=m.target||m.srcElement;return this.resolveTextNode(l)},resolveTextNode:function(k){try{if(k&&3==k.nodeType){return k.parentNode}}catch(l){}return k},getPageX:function(k){var l=k.pageX;if(!l&&0!==l){l=k.clientX||0;if(this.isIE){l+=this._getScrollLeft()}}return l},getPageY:function(l){var k=l.pageY;if(!k&&0!==k){k=l.clientY||0;if(this.isIE){k+=this._getScrollTop()}}return k},getXY:function(k){return[this.getPageX(k),this.getPageY(k)]},getRelatedTarget:function(k){var l=k.relatedTarget;if(!l){if(k.type=="mouseout"){l=k.toElement}else{if(k.type=="mouseover"){l=k.fromElement}}}return this.resolveTextNode(l)},getTime:function(m){if(!m.time){var k=new Date().getTime();try{m.time=k}catch(l){this.lastError=l;return k}}return m.time},stopEvent:function(k){this.stopPropagation(k);this.preventDefault(k)},stopPropagation:function(k){if(k.stopPropagation){k.stopPropagation()}else{k.cancelBubble=true}},preventDefault:function(k){if(k.preventDefault){k.preventDefault()}else{k.returnValue=false}},getEvent:function(n,l){var k=n||window.event;if(!k){var m=this.getEvent.caller;while(m){k=m.arguments[0];if(k&&Event==k.constructor){break}m=m.caller}}return k},getCharCode:function(k){var l=k.keyCode||k.charCode||0;if(YAHOO.env.ua.webkit&&(l in b)){l=b[l]}return l},_getCacheIndex:function(q,n,m,o){for(var p=0,k=q.length;p<k;p=p+1){var l=q[p];if(l&&l[this.FN]==o&&l[this.EL]==n&&l[this.TYPE]==m){return p}}return -1},generateId:function(l){var k=l.id;if(!k){k="yuievtautoid-"+c;++c;l.id=k}return k},_isValidCollection:function(k){try{return(k&&typeof k!=="string"&&k.length&&!k.tagName&&!k.alert&&typeof k[0]!=="undefined")}catch(l){return false}},elCache:{},getEl:function(k){return(typeof k==="string")?document.getElementById(k):k},clearCache:function(){},DOMReadyEvent:new YAHOO.util.CustomEvent("DOMReady",YAHOO,0,0,1),_load:function(k){if(!h){h=true;var l=YAHOO.util.Event;l._ready();l._tryPreloadAttach()}},_ready:function(k){var l=YAHOO.util.Event;if(!l.DOMReady){l.DOMReady=true;l.DOMReadyEvent.fire();l._simpleRemove(document,"DOMContentLoaded",l._ready)}},_tryPreloadAttach:function(){if(j.length===0){d=0;if(this._interval){this._interval.cancel();this._interval=null}return}if(this.locked){return}if(this.isIE){if(!this.DOMReady){this.startInterval();return}}this.locked=true;var n=!h;if(!n){n=(d>0&&j.length>0)}var o=[];var m=function(t,s){var u=t;if(s.overrideContext){if(s.overrideContext===true){u=s.obj}else{u=s.overrideContext}}s.fn.call(u,s.obj)};var k,l,p,q,r=[];for(k=0,l=j.length;k<l;k=k+1){p=j[k];if(p){q=this.getEl(p.id);if(q){if(p.checkReady){if(h||q.nextSibling||!n){r.push(p);j[k]=null}}else{m(q,p);j[k]=null}}else{o.push(p)}}}for(k=0,l=r.length;k<l;k=k+1){p=r[k];m(this.getEl(p.id),p)}d--;if(n){for(k=j.length-1;k>-1;k--){p=j[k];if(!p||!p.id){j.splice(k,1)}}this.startInterval()}else{if(this._interval){this._interval.cancel();this._interval=null}}this.locked=false},purgeElement:function(p,o,m){var r=(YAHOO.lang.isString(p))?this.getEl(p):p;var n=this.getListeners(r,m),q,l;if(n){for(q=n.length-1;q>-1;q--){var k=n[q];this.removeListener(r,k.type,k.fn)}}if(o&&r&&r.childNodes){for(q=0,l=r.childNodes.length;q<l;++q){this.purgeElement(r.childNodes[q],o,m)}}},getListeners:function(r,t){var o=[],s;if(!t){s=[g,e]}else{if(t==="unload"){s=[e]}else{t=this._getType(t);s=[g]}}var m=(YAHOO.lang.isString(r))?this.getEl(r):r;for(var p=0;p<s.length;p=p+1){var k=s[p];if(k){for(var n=0,l=k.length;n<l;++n){var q=k[n];if(q&&q[this.EL]===m&&(!t||t===q[this.TYPE])){o.push({type:q[this.TYPE],fn:q[this.FN],obj:q[this.OBJ],adjust:q[this.OVERRIDE],scope:q[this.ADJ_SCOPE],index:n})}}}}return(o.length)?o:null},_unload:function(l){var r=YAHOO.util.Event,o,p,q,m,n,k=e.slice(),s;for(o=0,m=e.length;o<m;++o){q=k[o];if(q){s=window;if(q[r.ADJ_SCOPE]){if(q[r.ADJ_SCOPE]===true){s=q[r.UNLOAD_OBJ]}else{s=q[r.ADJ_SCOPE]}}q[r.FN].call(s,r.getEvent(l,q[r.EL]),q[r.UNLOAD_OBJ]);k[o]=null}}q=null;s=null;e=null;if(g){for(p=g.length-1;p>-1;p--){q=g[p];if(q){r.removeListener(q[r.EL],q[r.TYPE],q[r.FN],p)}}q=null}r._simpleRemove(window,"unload",r._unload)},_getScrollLeft:function(){return this._getScroll()[1]},_getScrollTop:function(){return this._getScroll()[0]},_getScroll:function(){var l=document.documentElement,k=document.body;if(l&&(l.scrollTop||l.scrollLeft)){return[l.scrollTop,l.scrollLeft]}else{if(k){return[k.scrollTop,k.scrollLeft]}else{return[0,0]}}},regCE:function(){},_simpleAdd:function(){if(window.addEventListener){return function(n,m,k,l){n.addEventListener(m,k,(l))}}else{if(window.attachEvent){return function(n,m,k,l){n.attachEvent("on"+m,k)}}else{return function(){}}}}(),_simpleRemove:function(){if(window.removeEventListener){return function(n,m,k,l){n.removeEventListener(m,k,(l))}}else{if(window.detachEvent){return function(k,m,l){k.detachEvent("on"+m,l)}}else{return function(){}}}}()}}();(function(){var a=YAHOO.util.Event;a.on=a.addListener;a.onFocus=a.addFocusListener;a.onBlur=a.addBlurListener;if(a.isIE){if(self!==self.top){document.onreadystatechange=function(){if(document.readyState=="complete"){document.onreadystatechange=null;a._ready()}}}else{YAHOO.util.Event.onDOMReady(YAHOO.util.Event._tryPreloadAttach,YAHOO.util.Event,true);var b=document.createElement("p");a._dri=setInterval(function(){try{b.doScroll("left");clearInterval(a._dri);a._dri=null;a._ready();b=null}catch(c){}},a.POLL_INTERVAL)}}else{if(a.webkit&&a.webkit<525){a._dri=setInterval(function(){var c=document.readyState;if("loaded"==c||"complete"==c){clearInterval(a._dri);a._dri=null;a._ready()}},a.POLL_INTERVAL)}else{a._simpleAdd(document,"DOMContentLoaded",a._ready)}}a._simpleAdd(window,"load",a._load);a._simpleAdd(window,"unload",a._unload);a._tryPreloadAttach()})()}YAHOO.util.EventProvider=function(){};YAHOO.util.EventProvider.prototype={__yui_events:null,__yui_subscribers:null,subscribe:function(a,e,b,c){this.__yui_events=this.__yui_events||{};var d=this.__yui_events[a];if(d){d.subscribe(e,b,c)}else{this.__yui_subscribers=this.__yui_subscribers||{};var f=this.__yui_subscribers;if(!f[a]){f[a]=[]}f[a].push({fn:e,obj:b,overrideContext:c})}},unsubscribe:function(f,d,b){this.__yui_events=this.__yui_events||{};var a=this.__yui_events;if(f){var c=a[f];if(c){return c.unsubscribe(d,b)}}else{var g=true;for(var e in a){if(YAHOO.lang.hasOwnProperty(a,e)){g=g&&a[e].unsubscribe(d,b)}}return g}return false},unsubscribeAll:function(a){return this.unsubscribe(a)},createEvent:function(g,b){this.__yui_events=this.__yui_events||{};var d=b||{},e=this.__yui_events,c;if(e[g]){}else{c=new YAHOO.util.CustomEvent(g,d.scope||this,d.silent,YAHOO.util.CustomEvent.FLAT,d.fireOnce);e[g]=c;if(d.onSubscribeCallback){c.subscribeEvent.subscribe(d.onSubscribeCallback)}this.__yui_subscribers=this.__yui_subscribers||{};var a=this.__yui_subscribers[g];if(a){for(var f=0;f<a.length;++f){c.subscribe(a[f].fn,a[f].obj,a[f].overrideContext)}}}return e[g]},fireEvent:function(d){this.__yui_events=this.__yui_events||{};var b=this.__yui_events[d];if(!b){return null}var a=[];for(var c=1;c<arguments.length;++c){a.push(arguments[c])}return b.fire.apply(b,a)},hasEvent:function(a){if(this.__yui_events){if(this.__yui_events[a]){return true}}return false}};(function(){var a=YAHOO.util.Event,b=YAHOO.lang;YAHOO.util.KeyListener=function(i,d,h,g){if(!i){}else{if(!d){}else{if(!h){}}}if(!g){g=YAHOO.util.KeyListener.KEYDOWN}var f=new YAHOO.util.CustomEvent("keyPressed");this.enabledEvent=new YAHOO.util.CustomEvent("enabled");this.disabledEvent=new YAHOO.util.CustomEvent("disabled");if(b.isString(i)){i=document.getElementById(i)}if(b.isFunction(h)){f.subscribe(h)}else{f.subscribe(h.fn,h.scope,h.correctScope)}function e(m,n){if(!d.shift){d.shift=false}if(!d.alt){d.alt=false}if(!d.ctrl){d.ctrl=false}if(m.shiftKey==d.shift&&m.altKey==d.alt&&m.ctrlKey==d.ctrl){var l,o=d.keys,j;if(YAHOO.lang.isArray(o)){for(var k=0;k<o.length;k++){l=o[k];j=a.getCharCode(m);if(l==j){f.fire(j,m);break}}}else{j=a.getCharCode(m);if(o==j){f.fire(j,m)}}}}this.enable=function(){if(!this.enabled){a.on(i,g,e);this.enabledEvent.fire(d)}this.enabled=true};this.disable=function(){if(this.enabled){a.removeListener(i,g,e);this.disabledEvent.fire(d)}this.enabled=false};this.toString=function(){return"KeyListener ["+d.keys+"] "+i.tagName+(i.id?"["+i.id+"]":"")}};var c=YAHOO.util.KeyListener;c.KEYDOWN="keydown";c.KEYUP="keyup";c.KEY={ALT:18,BACK_SPACE:8,CAPS_LOCK:20,CONTROL:17,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,LEFT:37,META:224,NUM_LOCK:144,PAGE_DOWN:34,PAGE_UP:33,PAUSE:19,PRINTSCREEN:44,RIGHT:39,SCROLL_LOCK:145,SHIFT:16,SPACE:32,TAB:9,UP:38}})();YAHOO.register("event",YAHOO.util.Event,{version:"2.8.0r4",build:"2449"});YAHOO.register("yahoo-dom-event",YAHOO,{version:"2.8.0r4",build:"2449"});(function(){var l=YAHOO.lang,isFunction=l.isFunction,isObject=l.isObject,isArray=l.isArray,_toStr=Object.prototype.toString,Native=(YAHOO.env.ua.caja?window:this).JSON,_UNICODE_EXCEPTIONS=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,_ESCAPES=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,_VALUES=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,_BRACKETS=/(?:^|:|,)(?:\s*\[)+/g,_UNSAFE=/^[\],:{}\s]*$/,_SPECIAL_CHARS=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,_CHARS={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},UNDEFINED="undefined",OBJECT="object",NULL="null",STRING="string",NUMBER="number",BOOLEAN="boolean",DATE="date",_allowable={"undefined":UNDEFINED,string:STRING,"[object String]":STRING,number:NUMBER,"[object Number]":NUMBER,"boolean":BOOLEAN,"[object Boolean]":BOOLEAN,"[object Date]":DATE,"[object RegExp]":OBJECT},EMPTY="",OPEN_O="{",CLOSE_O="}",OPEN_A="[",CLOSE_A="]",COMMA=",",COMMA_CR=",\n",CR="\n",COLON=":",COLON_SP=": ",QUOTE='"';Native=_toStr.call(Native)==="[object JSON]"&&Native;function _char(c){if(!_CHARS[c]){_CHARS[c]="\\u"+("0000"+(+(c.charCodeAt(0))).toString(16)).slice(-4)}return _CHARS[c]}function _revive(data,reviver){var walk=function(o,key){var k,v,value=o[key];if(value&&typeof value==="object"){for(k in value){if(l.hasOwnProperty(value,k)){v=walk(value,k);if(v===undefined){delete value[k]}else{value[k]=v}}}}return reviver.call(o,key,value)};return typeof reviver==="function"?walk({"":data},""):data}function _prepare(s){return s.replace(_UNICODE_EXCEPTIONS,_char)}function _isSafe(str){return l.isString(str)&&_UNSAFE.test(str.replace(_ESCAPES,"@").replace(_VALUES,"]").replace(_BRACKETS,""))}function _parse(s,reviver){s=_prepare(s);if(_isSafe(s)){return _revive(eval("("+s+")"),reviver)}throw new SyntaxError("JSON.parse")}function _type(o){var t=typeof o;return _allowable[t]||_allowable[_toStr.call(o)]||(t===OBJECT?(o?OBJECT:NULL):UNDEFINED)}function _string(s){return QUOTE+s.replace(_SPECIAL_CHARS,_char)+QUOTE}function _indent(s,space){return s.replace(/^/gm,space)}function _stringify(o,w,space){if(o===undefined){return undefined}var replacer=isFunction(w)?w:null,format=_toStr.call(space).match(/String|Number/)||[],_date=YAHOO.lang.JSON.dateToString,stack=[],tmp,i,len;if(replacer||!isArray(w)){w=undefined}if(w){tmp={};for(i=0,len=w.length;i<len;++i){tmp[w[i]]=true}w=tmp}space=format[0]==="Number"?new Array(Math.min(Math.max(0,space),10)+1).join(" "):(space||EMPTY).slice(0,10);function _serialize(h,key){var value=h[key],t=_type(value),a=[],colon=space?COLON_SP:COLON,arr,i,keys,k,v;if(isObject(value)&&isFunction(value.toJSON)){value=value.toJSON(key)}else{if(t===DATE){value=_date(value)}}if(isFunction(replacer)){value=replacer.call(h,key,value)}if(value!==h[key]){t=_type(value)}switch(t){case DATE:case OBJECT:break;case STRING:return _string(value);case NUMBER:return isFinite(value)?value+EMPTY:NULL;case BOOLEAN:return value+EMPTY;case NULL:return NULL;default:return undefined}for(i=stack.length-1;i>=0;--i){if(stack[i]===value){throw new Error("JSON.stringify. Cyclical reference")}}arr=isArray(value);stack.push(value);if(arr){for(i=value.length-1;i>=0;--i){a[i]=_serialize(value,i)||NULL}}else{keys=w||value;i=0;for(k in keys){if(keys.hasOwnProperty(k)){v=_serialize(value,k);if(v){a[i++]=_string(k)+colon+v}}}}stack.pop();if(space&&a.length){return arr?OPEN_A+CR+_indent(a.join(COMMA_CR),space)+CR+CLOSE_A:OPEN_O+CR+_indent(a.join(COMMA_CR),space)+CR+CLOSE_O}else{return arr?OPEN_A+a.join(COMMA)+CLOSE_A:OPEN_O+a.join(COMMA)+CLOSE_O}}return _serialize({"":o},"")}YAHOO.lang.JSON={useNativeParse:!!Native,useNativeStringify:!!Native,isSafe:function(s){return _isSafe(_prepare(s))},parse:function(s,reviver){return Native&&YAHOO.lang.JSON.useNativeParse?Native.parse(s,reviver):_parse(s,reviver)},stringify:function(o,w,space){return Native&&YAHOO.lang.JSON.useNativeStringify?Native.stringify(o,w,space):_stringify(o,w,space)},dateToString:function(d){function _zeroPad(v){return v<10?"0"+v:v}return d.getUTCFullYear()+"-"+_zeroPad(d.getUTCMonth()+1)+"-"+_zeroPad(d.getUTCDate())+"T"+_zeroPad(d.getUTCHours())+COLON+_zeroPad(d.getUTCMinutes())+COLON+_zeroPad(d.getUTCSeconds())+"Z"},stringToDate:function(str){var m=str.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.(\d{3}))?Z$/);if(m){var d=new Date();d.setUTCFullYear(m[1],m[2]-1,m[3]);d.setUTCHours(m[4],m[5],m[6],(m[7]||0));return d}return str}};YAHOO.lang.JSON.isValid=YAHOO.lang.JSON.isSafe})();YAHOO.register("json",YAHOO.lang.JSON,{version:"2.8.0r4",build:"2449"});YAHOO.util.Connect={_msxml_progid:["Microsoft.XMLHTTP","MSXML2.XMLHTTP.3.0","MSXML2.XMLHTTP"],_http_headers:{},_has_http_headers:false,_use_default_post_header:true,_default_post_header:"application/x-www-form-urlencoded; charset=UTF-8",_default_form_header:"application/x-www-form-urlencoded",_use_default_xhr_header:true,_default_xhr_header:"XMLHttpRequest",_has_default_headers:true,_default_headers:{},_poll:{},_timeOut:{},_polling_interval:50,_transaction_id:0,startEvent:new YAHOO.util.CustomEvent("start"),completeEvent:new YAHOO.util.CustomEvent("complete"),successEvent:new YAHOO.util.CustomEvent("success"),failureEvent:new YAHOO.util.CustomEvent("failure"),abortEvent:new YAHOO.util.CustomEvent("abort"),_customEvents:{onStart:["startEvent","start"],onComplete:["completeEvent","complete"],onSuccess:["successEvent","success"],onFailure:["failureEvent","failure"],onUpload:["uploadEvent","upload"],onAbort:["abortEvent","abort"]},setProgId:function(a){this._msxml_progid.unshift(a)},setDefaultPostHeader:function(a){if(typeof a=="string"){this._default_post_header=a}else{if(typeof a=="boolean"){this._use_default_post_header=a}}},setDefaultXhrHeader:function(a){if(typeof a=="string"){this._default_xhr_header=a}else{this._use_default_xhr_header=a}},setPollingInterval:function(a){if(typeof a=="number"&&isFinite(a)){this._polling_interval=a}},createXhrObject:function(b){var d,a,f;try{a=new XMLHttpRequest();d={conn:a,tId:b,xhr:true}}catch(e){for(f=0;f<this._msxml_progid.length;++f){try{a=new ActiveXObject(this._msxml_progid[f]);d={conn:a,tId:b,xhr:true};break}catch(c){}}}finally{return d}},getConnectionObject:function(a){var c,b=this._transaction_id;try{if(!a){c=this.createXhrObject(b)}else{c={tId:b};if(a==="xdr"){c.conn=this._transport;c.xdr=true}else{if(a==="upload"){c.upload=true}}}if(c){this._transaction_id++}}catch(d){}return c},asyncRequest:function(b,e,c,a){var d,f,g=(c&&c.argument)?c.argument:null;if(this._isFileUpload){f="upload"}else{if(c.xdr){f="xdr"}}d=this.getConnectionObject(f);if(!d){return null}else{if(c&&c.customevents){this.initCustomEvents(d,c)}if(this._isFormSubmit){if(this._isFileUpload){this.uploadFile(d,c,e,a);return d}if(b.toUpperCase()=="GET"){if(this._sFormData.length!==0){e+=((e.indexOf("?")==-1)?"?":"&")+this._sFormData}}else{if(b.toUpperCase()=="POST"){a=a?this._sFormData+"&"+a:this._sFormData}}}if(b.toUpperCase()=="GET"&&(c&&c.cache===false)){e+=((e.indexOf("?")==-1)?"?":"&")+"rnd="+new Date().valueOf().toString()}if(this._use_default_xhr_header){if(!this._default_headers["X-Requested-With"]){this.initHeader("X-Requested-With",this._default_xhr_header,true)}}if((b.toUpperCase()==="POST"&&this._use_default_post_header)&&this._isFormSubmit===false){this.initHeader("Content-Type",this._default_post_header)}if(d.xdr){this.xdr(d,b,e,c,a);return d}d.conn.open(b,e,true);if(this._has_default_headers||this._has_http_headers){this.setHeader(d)}this.handleReadyState(d,c);d.conn.send(a||"");if(this._isFormSubmit===true){this.resetFormState()}this.startEvent.fire(d,g);if(d.startEvent){d.startEvent.fire(d,g)}return d}},initCustomEvents:function(a,b){var c;for(c in b.customevents){if(this._customEvents[c][0]){a[this._customEvents[c][0]]=new YAHOO.util.CustomEvent(this._customEvents[c][1],(b.scope)?b.scope:null);a[this._customEvents[c][0]].subscribe(b.customevents[c])}}},handleReadyState:function(c,b){var d=this,a=(b&&b.argument)?b.argument:null;if(b&&b.timeout){this._timeOut[c.tId]=window.setTimeout(function(){d.abort(c,b,true)},b.timeout)}this._poll[c.tId]=window.setInterval(function(){if(c.conn&&c.conn.readyState===4){window.clearInterval(d._poll[c.tId]);delete d._poll[c.tId];if(b&&b.timeout){window.clearTimeout(d._timeOut[c.tId]);delete d._timeOut[c.tId]}d.completeEvent.fire(c,a);if(c.completeEvent){c.completeEvent.fire(c,a)}d.handleTransactionResponse(c,b)}},this._polling_interval)},handleTransactionResponse:function(c,f,a){var j,d,h=(f&&f.argument)?f.argument:null,b=(c.r&&c.r.statusText==="xdr:success")?true:false,g=(c.r&&c.r.statusText==="xdr:failure")?true:false,e=a;try{if((c.conn.status!==undefined&&c.conn.status!==0)||b){j=c.conn.status}else{if(g&&!e){j=0}else{j=13030}}}catch(i){j=13030}if((j>=200&&j<300)||j===1223||b){d=c.xdr?c.r:this.createResponseObject(c,h);if(f&&f.success){if(!f.scope){f.success(d)}else{f.success.apply(f.scope,[d])}}this.successEvent.fire(d);if(c.successEvent){c.successEvent.fire(d)}}else{switch(j){case 12002:case 12029:case 12030:case 12031:case 12152:case 13030:d=this.createExceptionObject(c.tId,h,(a?a:false));if(f&&f.failure){if(!f.scope){f.failure(d)}else{f.failure.apply(f.scope,[d])}}break;default:d=(c.xdr)?c.response:this.createResponseObject(c,h);if(f&&f.failure){if(!f.scope){f.failure(d)}else{f.failure.apply(f.scope,[d])}}}this.failureEvent.fire(d);if(c.failureEvent){c.failureEvent.fire(d)}}this.releaseObject(c);d=null},createResponseObject:function(d,g){var a={},e={},i,b,h,c;try{b=d.conn.getAllResponseHeaders();h=b.split("\n");for(i=0;i<h.length;i++){c=h[i].indexOf(":");if(c!=-1){e[h[i].substring(0,c)]=YAHOO.lang.trim(h[i].substring(c+2))}}}catch(f){}a.tId=d.tId;a.status=(d.conn.status==1223)?204:d.conn.status;a.statusText=(d.conn.status==1223)?"No Content":d.conn.statusText;a.getResponseHeader=e;a.getAllResponseHeaders=b;a.responseText=d.conn.responseText;a.responseXML=d.conn.responseXML;if(g){a.argument=g}return a},createExceptionObject:function(b,f,a){var d=0,c="communication failure",g=-1,h="transaction aborted",e={};e.tId=b;if(a){e.status=g;e.statusText=h}else{e.status=d;e.statusText=c}if(f){e.argument=f}return e},initHeader:function(a,b,c){var d=(c)?this._default_headers:this._http_headers;d[a]=b;if(c){this._has_default_headers=true}else{this._has_http_headers=true}},setHeader:function(a){var b;if(this._has_default_headers){for(b in this._default_headers){if(YAHOO.lang.hasOwnProperty(this._default_headers,b)){a.conn.setRequestHeader(b,this._default_headers[b])}}}if(this._has_http_headers){for(b in this._http_headers){if(YAHOO.lang.hasOwnProperty(this._http_headers,b)){a.conn.setRequestHeader(b,this._http_headers[b])}}this._http_headers={};this._has_http_headers=false}},resetDefaultHeaders:function(){this._default_headers={};this._has_default_headers=false},abort:function(d,b,a){var e,g=(b&&b.argument)?b.argument:null;d=d||{};if(d.conn){if(d.xhr){if(this.isCallInProgress(d)){d.conn.abort();window.clearInterval(this._poll[d.tId]);delete this._poll[d.tId];if(a){window.clearTimeout(this._timeOut[d.tId]);delete this._timeOut[d.tId]}e=true}}else{if(d.xdr){d.conn.abort(d.tId);e=true}}}else{if(d.upload){var f="yuiIO"+d.tId;var c=document.getElementById(f);if(c){YAHOO.util.Event.removeListener(c,"load");document.body.removeChild(c);if(a){window.clearTimeout(this._timeOut[d.tId]);delete this._timeOut[d.tId]}e=true}}else{e=false}}if(e===true){this.abortEvent.fire(d,g);if(d.abortEvent){d.abortEvent.fire(d,g)}this.handleTransactionResponse(d,b,true)}return e},isCallInProgress:function(a){a=a||{};if(a.xhr&&a.conn){return a.conn.readyState!==4&&a.conn.readyState!==0}else{if(a.xdr&&a.conn){return a.conn.isCallInProgress(a.tId)}else{if(a.upload===true){return document.getElementById("yuiIO"+a.tId)?true:false}else{return false}}}},releaseObject:function(a){if(a&&a.conn){a.conn=null;a=null}}};(function(){var c=YAHOO.util.Connect,b={};function f(k){var j='<object id="YUIConnectionSwf" type="application/x-shockwave-flash" data="'+k+'" width="0" height="0"><param name="movie" value="'+k+'"><param name="allowScriptAccess" value="always"></object>',i=document.createElement("div");document.body.appendChild(i);i.innerHTML=j}function h(i,l,k,m,j){b[parseInt(i.tId)]={o:i,c:m};if(j){m.method=l;m.data=j}i.conn.send(k,m,i.tId)}function e(i){f(i);c._transport=document.getElementById("YUIConnectionSwf")}function g(){c.xdrReadyEvent.fire()}function a(i,j){if(i){c.startEvent.fire(i,j.argument);if(i.startEvent){i.startEvent.fire(i,j.argument)}}}function d(j){var i=b[j.tId].o,k=b[j.tId].c;if(j.statusText==="xdr:start"){a(i,k);return}j.responseText=decodeURI(j.responseText);i.r=j;if(k.argument){i.r.argument=k.argument}this.handleTransactionResponse(i,k,j.statusText==="xdr:abort"?true:false);delete b[j.tId]}c.xdr=h;c.swf=f;c.transport=e;c.xdrReadyEvent=new YAHOO.util.CustomEvent("xdrReady");c.xdrReady=g;c.handleXdrResponse=d})();(function(){var e=YAHOO.util.Connect,c=YAHOO.util.Event;e._isFormSubmit=false;e._isFileUpload=false;e._formNode=null;e._sFormData=null;e._submitElementValue=null;e.uploadEvent=new YAHOO.util.CustomEvent("upload"),e._hasSubmitListener=function(){if(c){c.addListener(document,"click",function(h){var i=c.getTarget(h),j=i.nodeName.toLowerCase();if((j==="input"||j==="button")&&(i.type&&i.type.toLowerCase()=="submit")){e._submitElementValue=encodeURIComponent(i.name)+"="+encodeURIComponent(i.value)}});return true}return false}();function b(k,p,u){var l,v,m,o,h,n=false,r=[],i=0,s,q,t,j,w;this.resetFormState();if(typeof k=="string"){l=(document.getElementById(k)||document.forms[k])}else{if(typeof k=="object"){l=k}else{return}}if(p){this.createFrame(u?u:null);this._isFormSubmit=true;this._isFileUpload=true;this._formNode=l;return}for(s=0,q=l.elements.length;s<q;++s){v=l.elements[s];h=v.disabled;m=v.name;if(!h&&m){m=encodeURIComponent(m)+"=";o=encodeURIComponent(v.value);switch(v.type){case"select-one":if(v.selectedIndex>-1){w=v.options[v.selectedIndex];r[i++]=m+encodeURIComponent((w.attributes.value&&w.attributes.value.specified)?w.value:w.text)}break;case"select-multiple":if(v.selectedIndex>-1){for(t=v.selectedIndex,j=v.options.length;t<j;++t){w=v.options[t];if(w.selected){r[i++]=m+encodeURIComponent((w.attributes.value&&w.attributes.value.specified)?w.value:w.text)}}}break;case"radio":case"checkbox":if(v.checked){r[i++]=m+o}break;case"file":case undefined:case"reset":case"button":break;case"submit":if(n===false){if(this._hasSubmitListener&&this._submitElementValue){r[i++]=this._submitElementValue}n=true}break;default:r[i++]=m+o}}}this._isFormSubmit=true;this._sFormData=r.join("&");this.initHeader("Content-Type",this._default_form_header);return this._sFormData}function f(){this._isFormSubmit=false;this._isFileUpload=false;this._formNode=null;this._sFormData=""}function g(j){var i="yuiIO"+this._transaction_id,h;if(YAHOO.env.ua.ie){h=document.createElement('<iframe id="'+i+'" name="'+i+'" />');if(typeof j=="boolean"){h.src="javascript:false"}}else{h=document.createElement("iframe");h.id=i;h.name=i}h.style.position="absolute";h.style.top="-1000px";h.style.left="-1000px";document.body.appendChild(h)}function d(l){var i=[],k=l.split("&"),j,h;for(j=0;j<k.length;j++){h=k[j].indexOf("=");if(h!=-1){i[j]=document.createElement("input");i[j].type="hidden";i[j].name=decodeURIComponent(k[j].substring(0,h));i[j].value=decodeURIComponent(k[j].substring(h+1));this._formNode.appendChild(i[j])}}return i}function a(t,i,s,u){var n="yuiIO"+t.tId,m="multipart/form-data",k=document.getElementById(n),r=(document.documentMode&&document.documentMode===8)?true:false,h=this,l=(i&&i.argument)?i.argument:null,j,o,v,p,w,q;w={action:this._formNode.getAttribute("action"),method:this._formNode.getAttribute("method"),target:this._formNode.getAttribute("target")};this._formNode.setAttribute("action",s);this._formNode.setAttribute("method","POST");this._formNode.setAttribute("target",n);if(YAHOO.env.ua.ie&&!r){this._formNode.setAttribute("encoding",m)}else{this._formNode.setAttribute("enctype",m)}if(u){j=this.appendPostData(u)}this._formNode.submit();this.startEvent.fire(t,l);if(t.startEvent){t.startEvent.fire(t,l)}if(i&&i.timeout){this._timeOut[t.tId]=window.setTimeout(function(){h.abort(t,i,true)},i.timeout)}if(j&&j.length>0){for(o=0;o<j.length;o++){this._formNode.removeChild(j[o])}}for(v in w){if(YAHOO.lang.hasOwnProperty(w,v)){if(w[v]){this._formNode.setAttribute(v,w[v])}else{this._formNode.removeAttribute(v)}}}this.resetFormState();q=function(){if(i&&i.timeout){window.clearTimeout(h._timeOut[t.tId]);delete h._timeOut[t.tId]}h.completeEvent.fire(t,l);if(t.completeEvent){t.completeEvent.fire(t,l)}p={tId:t.tId,argument:i.argument};try{p.responseText=k.contentWindow.document.body?k.contentWindow.document.body.innerHTML:k.contentWindow.document.documentElement.textContent;p.responseXML=k.contentWindow.document.XMLDocument?k.contentWindow.document.XMLDocument:k.contentWindow.document}catch(x){}if(i&&i.upload){if(!i.scope){i.upload(p)}else{i.upload.apply(i.scope,[p])}}h.uploadEvent.fire(p);if(t.uploadEvent){t.uploadEvent.fire(p)}c.removeListener(k,"load",q);setTimeout(function(){document.body.removeChild(k);h.releaseObject(t)},100)};c.addListener(k,"load",q)}e.setForm=b;e.resetFormState=f;e.createFrame=g;e.appendPostData=d;e.uploadFile=a})();YAHOO.register("connection",YAHOO.util.Connect,{version:"2.8.0r4",build:"2449"});
/*
Copyright (c) 2009, Kissy UI Library. All rights reserved.
MIT Licensed.
http://kissy.googlecode.com/

Date: 2009-11-20 14:54:53
Revision: 263
*/
var KISSY = window.KISSY || {};

KISSY.Editor = function(textarea, config) {
    var E = KISSY.Editor;

    if (!(this instanceof E)) {
        return new E(textarea, config);
    } else {
        if (!E._isReady) {
            E._setup();
        }
        return new E.Instance(textarea, config);
    }
 };

(function(E) {
    var Lang = YAHOO.lang;

    Lang.augmentObject(E, {
        /**
         * 版本? //sociax 修改?,update at 2010-2-27
         */
        version: "0.2c",

        /**
         * 语言配置,? lang 目录添加
         */
        lang: {},

        /**
         * ?有添加的模块
         * ?:mod = { name: modName, fn: initFn, details: {...} }
         */
        mods: {},

        /**
         * ?有注册的插件
         * ?:plugin = { name: pluginName, type: pluginType, init: initFn, ... }
         */
        plugins: {},

        /**
         * 添加模块
         */
        add: function(name, fn, details) {

            this.mods[name] = {
                name: name,
                fn: fn,
                details: details || {}
            };

            return this; // chain support
        },

        /**
         * 添加插件
         * @param {string|Array} name
         */
        addPlugin: function(name, p) {
            var arr = typeof name == "string" ? [name] : name,
                plugins = this.plugins,
                key, i, len;

            for (i = 0,len = arr.length; i < len; ++i) {
                key = arr[i];

                if (!plugins[key]) { // 不允许覆?
                    plugins[key] = Lang.merge(p, {
                        name: key
                    });
                }
            }
        },

        /**
         * 是否已完? setup
         */
        _isReady: false,

        /**
         * setup to use
         */
        _setup: function() {
            this._loadModules();
            this._isReady = true;
        },

        /**
         * 已加载的模块
         */
        _attached: {},

        /**
         * 加载注册的所有模?
         */
        _loadModules: function() {
            var mods = this.mods,
                attached = this._attached,
                name, m;

            for (name in mods) {
                m = mods[name];

                if (!attached[name] && m) { // 不允许覆?
                    attached[name] = m;

                    if (m.fn) {
                        m.fn(this);
                    }
                }

                // 注意:m.details 暂时没用?,仅是预留的扩展接?
            }

            // TODO
            // lang 的加载可以延迟到实例化时,只加载当? lang
        }
    });

 })(KISSY.Editor);

KISSY.Editor.add("config", function(E) {

    E.config = {
        /**
         * 基本路径
         */
      	base: EDITER_BASE,
		//base: "http://my.huawei.com/kmp/public/js/kissy/",

		/**
		 * 上传地址
		 */
		upload_url: EDITER_UPLOAD,
		//upload_url: "http://my.huawei.com/kmp/index.php?app=home&mod=Attach",

        /**
         * 语言
         */
        language: LANGUAGE_NAME,

        /**
         * 主题
         */
        theme: "default",

        /**
         * Toolbar 上功能插?
         */
        toolbar: [
            "source",
            "",
            /*"undo", "redo",
            "",*/

			//"fontName", 
			//"fontSize",
			"bold", "italic", "underline", "strikeThrough",  "foreColor", "backColor",
            "",

            //"link",
			"justifyLeft", "justifyCenter", "justifyRight","","smiley","image","video", 
			//"",
					
			//"table",	
			//"title1","code", 
          //  "",

          //  "insertOrderedList", "insertUnorderedList", "outdent", "indent", "justifyLeft", "justifyCenter", "justifyRight"
	
            //"",
            //"removeformat"
        ],

        /**
         * Statusbar 上的插件
         */
        statusbar: [
            //"wordcount",
            "resize"
        ],

        /**
         * 插件的配?
         */
        pluginsConfig: { }

        /**
         * 自动聚焦
         */
        // autoFocus: false
    };

 });
 

KISSY.Editor.add("lang~en", function(E) {

    E.lang["en"] = {

        // Toolbar buttons
        source: {
          text            : "Source",
          title           : "Source"
        },
        undo: {
          text            : "Undo",
          title           : "Undo (Ctrl+Z)"
        },
        redo: {
          text            : "Redo",
          title           : "Redo (Ctrl+Y)"
        },
        fontName: {
          text            : "Font Name",  
          title           : "Font Name",
          options         : {
              "Arial"           : "Arial",
              "Times New Roman" : "Times New Roman",
              "Arial Black"     : "Arial Black",
              "Arial Narrow"    : "Arial Narrow",
              "Comic Sans MS"   : "Comic Sans MS",
              "Courier New"     : "Courier New",
              "Garamond"        : "Garamond",
              "Georgia"         : "Georgia",
              "Tahoma"          : "Tahoma",
              "Trebuchet MS"    : "Trebuchet MS",
              "Verdana"         : "Verdana"
          }
        },
        fontSize: {
          text            : "Size",
          title           : "Font size",
          options         : {
             // "8"               : "1",
            //  "10"              : "2",
              "12"              : "3",
              "14"              : "4",
              "18"              : "5",
              "24"              : "6"
            //  "36"              : "7"
          }
        },
        bold: {
            text          : "Bold",
            title         : "Bold (Ctrl+B)"
        },
        italic: {
            text          : "Italic",
            title         : "Italick (Ctrl+I)"
        },
        underline: {
            text          : "Underline",
            title         : "Underline (Ctrl+U)"
        },
        strikeThrough: {
            text          : "Strikeout",
            title         : "Strikeout"
        },
        link: {
            text          : "Link",
            title         : "Insert/Edit link",
            href          : "URL:",
            target        : "Open link in new window",
            remove        : "Remove link"
        },
        smiley: {
            text          : "Smiley",
            title         : "Insert smiley"
        },
        blockquote: {
            text          : "Blockquote",
            title         : "Insert blockquote"
        },
        image: {
            text          : "Image",
            title         : "Insert image",
            tab_link      : "Web Image",
            tab_local     : "Local Image",
            tab_album     : "Album Image",
            label_link    : "Enter image web address:",
            label_local   : "Browse your computer for the image file to upload:",
            label_album   : "Select the image from your album:",
            uploading     : "Uploading...",
            upload_error  : "Exception occurs when uploading file.",
            upload_filter : "Only allow PNG, GIF, JPG image type.",
            ok            : "Insert"
        },
		video: {
            text          : "video",
            title         : "Insert video",
            tab_link      : "Web video",
            tab_local     : "Local video",
            tab_album     : "Album video",
            label_link    : "Enter video web address:",
            label_local   : "Browse your computer for the video file to upload:",
            label_album   : "Select the video from your album:",
            uploading     : "Uploading...",
            upload_error  : "Exception occurs when uploading file.",
            upload_filter : "Only allow PNG, GIF, JPG video type.",
			label_link_width: "Width",
			label_link_height: "Height",
            ok            : "Insert"
        },
		
        insertOrderedList: {
            text          : "Numbered List",
            title         : "Numbered List (Ctrl+7)"
        },
        insertUnorderedList: {
            text          : "Bullet List",
            title         : "Bullet List (Ctrl+8)"
        },
        outdent: {
            text          : "Decrease Indent",
            title         : "Decrease Indent"
        },
        indent: {
            text          : "Increase Indent",
            title         : "Increase Indent"
        },
        justifyLeft: {
            text          : "Left Justify",
            title         : "Left Justify (Ctrl+L)"
        },
        justifyCenter: {
            text          : "Center Justify",
            title         : "Center Justify (Ctrl+E)"
        },
        justifyRight: {
            text          : "Right Justify",
            title         : "Right Justify (Ctrl+R)"
        },
        foreColor: {
            text          : "Text Color",
            title         : "Text Color"
        },
        backColor: {
            text          : "Text Background Color",
            title         : "Text Background Color"
        },
        maximize: {
          text            : "Maximize",
          title           : "Maximize"
        },
        removeformat: {
          text            : "Remove Format",
          title           : "Remove Format"
        },
        wordcount: {
          tmpl            : "Remain %remain% words (include html code)"
        },
        resize: {
            larger_text   : "Larger",
            larger_title  : "Enlarge the editor",
            smaller_text  : "Smaller",
            smaller_title : "Shrink the editor"
        },
        title1: {
            text          : "H1",
            title         : "Add title"
        },
		code :{
			text          : "Highlighter Code",
			tab_link      : "Code Highlighter Setting",
			label_link_selected :"Code Type",
			label_link    : "Code Content:",
			title         : "Highlighter Code",
			ok            : "Insert"
		},
		table :{
			text          : "Table",
			tab_link      : "Table Setting ",
			cols          : "Cols",
			rows		  : "Rows",
			title         : "Insert Table",
			ok            : "Insert",
			label_admin   : "Operate",
			add_left_cols : "Left insert cols",
			add_right_cols: "Right insert cols", 
			add_top_rows  : "Top insert rows",
			add_button_rows: "Botton insert rows",
			delete_cols: "Delete Cols", 
			delete_rows: "Delete Rows",
			delete_table: "Delete Table"
		},
        // Common messages and labels
        common: {
            ok            : "OK",
            cancel        : "Cancel"
        }
    };

 });

KISSY.Editor.add("lang~zh-cn", function(E) {

    E.lang["zh-cn"] = {

        // Toolbar buttons
        source: {
          text            : "源码",
          title           : "源码"
        },
        undo: {
          text            : "撤销",
          title           : "撤销"
        },
        redo: {
          text            : "重做",
          title           : "重做"
        },
        fontName: {
          text            : "字体",
          title           : "字体",
          options         : {
              "宋体"		: "宋体",
              "黑体"		: "黑体",
			  "微软雅黑"	: "微软雅黑",
              "隶书"		: "隶书",
              "楷体"		: "楷体",
              //"幼圆"		: "幼圆",
              "Georgia"		: "Georgia",
              "Impact"		: "Impact",
              "Courier New"	: "Courier New",
              "Arial"		: "Arial",
              "Verdana"		: "Verdana",
              "Tahoma"		: "Tahoma"
              //"Garamond"        : "Garamond",
              //"Times New Roman" : "Times New Roman"
          }
        },
        fontSize: {
          text            : "大小",
          title           : "大小",
          options         : {
             // "8"               : "1",
             // "10"              : "2",
              "12"              : "3",
              "14"              : "4",
              "18"              : "5",
              "24"              : "6"
            //  "36"              : "7"
          }
        },
        bold: {
            text          : "粗体",
            title         : "粗体"
        },
        italic: {
            text          : "斜体",
            title         : "斜体"
        },
        underline: {
            text          : "下划线",
            title         : "下划线"
        },
        strikeThrough: {
            text          : "删除线",
            title         : "删除线"
        },
        link: {
            text          : "链接",
            title         : "插入/编辑链接",
            href          : "URL:",
            target        : "在新窗口打开链接",
            remove        : "移除链接"
        },
        smiley: {
            text          : "表情",
            title         : "插入表情"
        },
        blockquote: {
            text          : "引用",
            title         : "引用"
        },
		image: {
            text          : "图片",
            title         : "插入图片",
            tab_link      : "网络图片",
		
            tab_local     : "本地上传",
            tab_album     : "我的相册",
            label_link    : "请输入图片地址:",
			label_link_width :"宽度",
			label_link_height:"高度", 
            label_local   : "请选择本地图片:",
            label_album   : "请选择相册图片:",
            uploading     : "正在上传...",
            upload_error  : "对不起,上传文件时发生了错误:",
            upload_filter : "仅支持 JPG, PNG ? GIF 图片,请重新选择",
            ok            : "插入"
        },
        video: {
            text          : "视频",
            title         : "插入视频",
            tab_link      : "网络视频",
		
            tab_local     : "本地上传",
            tab_album     : "我的相册",
            label_link    : "请输入视频地址:",
			label_link_width :"宽度",
			label_link_height:"高度", 
            label_local   : "请选择本地视频:",
            label_album   : "请选择相册视频:",
            uploading     : "正在上传...",
            upload_error  : "对不起,上传文件时发生了错误:",
            upload_filter : "仅支持 JPG, PNG, GIF 视频,请重重新选择",
            ok            : "插入"
        },
        insertOrderedList: {
            text          : "有序列表",
            title         : "有序列表"
        },
        insertUnorderedList: {
            text          : "无序列表",
            title         : "无序列表"
        },
        outdent: {
            text          : "减少缩进",
            title         : "减少缩进"
        },
        indent: {
            text          : "增加缩进",
            title         : "增加缩进"
        },
        justifyLeft: {
            text          : "左对齐",
            title         : "左对齐"
        },
        justifyCenter: {
            text          : "居中对齐",
            title         : "居中对齐"
        },
        justifyRight: {
            text          : "右对齐",
            title         : "右对齐"
        },
        foreColor: {
            text          : "文本颜色",
            title         : "文本颜色"
        },
        backColor: {
            text          : "背景颜色",
            title         : "背景颜色"
        },
        maximize: {
          text            : "全屏编辑",
          title           : "全屏编辑"
        },
        removeformat: {
          text            : "清除格式",
          title           : "清除格式"
        },
        wordcount: {
          tmpl            : "您已输入 %remain% 字(含 html 代码)"
        },
        resize: {
            larger_text   : "增大",
            larger_title  : "增大编辑区域",
            smaller_text  : "缩小",
            smaller_title : "缩小编辑区域"
        },
        title1: {
            text          : "大标题",
            title         : "给词条增加大标题"
        },
		code :{
			text          : "代码高亮",
			tab_link      : "代码高亮设置",
			label_link_selected :"代码类型",
			label_link    : "请输入代码内容:",
			title         : "将代码高亮",
			ok            : "插入"
		},
		table :{
			text          : "表格",
			tab_link      : "表格参数 ",
			cols          : "列",
			rows		  : "行",
			title         : "插入表格",
			ok            : "插入",
			label_admin   : "操作",
			add_left_cols : "左边插入列",
			add_right_cols: "右边插入列", 
			add_top_rows  : "顶部插入行",
			add_button_rows: "底部插入行",
			delete_cols: "删除列", 
			delete_rows: "删除行",
			delete_table: "删除表格"
		},
        // Common messages and labels
        common: {
            ok            : "确定",
            cancel        : "取消"
        }
    };

 });

KISSY.Editor.add("core~plugin", function(E) {

    /**
     * 插件种类
     */
    E.PLUGIN_TYPE = {
        CUSTOM: 0,
        TOOLBAR_SEPARATOR: 1,
        TOOLBAR_BUTTON: 2,
        TOOLBAR_MENU_BUTTON: 4,
        TOOLBAR_SELECT: 8,
        STATUSBAR_ITEM: 16,
        FUNC: 32 // 纯功能?质插件,? UI
    };

 });

KISSY.Editor.add("core~instance", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        ie = UA.ie,
        EDITOR_CLASSNAME = "ks-editor",
        EDITOR_TMPL  =  '<div class="ks-editor-toolbar"></div>' +
                        '<div class="ks-editor-content"><iframe frameborder="0" allowtransparency="1"></iframe></div>' +
                        '<div class="ks-editor-statusbar"></div>',

        CONTENT_TMPL =  '<!doctype html>' +
                        '<html>' +
                        '<head>' +
                        '<title>Rich Text Area</title>' +
                        '<meta http-equiv="content-type" content="text/html; charset=gb18030" />' +
                        '<link type="text/css" href="{CONTENT_CSS}" rel="stylesheet" />' +
                        '</head>' +
                        '<body spellcheck="false" class="ks-editor-post">{CONTENT}</body>' +
                        '</html>',

        THEMES_DIR = "themes";

    /**
     * 编辑器的实例?
     */
    E.Instance = function(textarea, config) {
        /**
         * 相关联的 textarea 元素
         */
        this.textarea = Dom.get(textarea);

        /**
         * 配置?
         */
        this.config = Lang.merge(E.config, config || {});

        /**
         * 以下? renderUI 中赋?
         * @property container
         * @property contentWin
         * @property contentDoc
         * @property statusbar
         */

        /**
         * 与该实例相关的插?
         */
        //this.plugins = [];

        /**
         * 是否处于源码编辑状??
         */
        this.sourceMode = false;

        /**
         * 工具?
         */
        this.toolbar = new E.Toolbar(this);

        /**
         * 状?栏
         */
        this.statusbar = new E.Statusbar(this);

        // init
        this._init();
    };

    Lang.augmentObject(E.Instance.prototype, {
        /**
         * 初始化方?
         */
        _init: function() {
            this._renderUI();
            this._initPlugins();
            this._initAutoFocus();
        },

        _renderUI: function() {
            this._renderContainer();
            this._setupContentPanel();
        },

        /**
         * 初始化所有插?
         */
        _initPlugins: function() {
            var key, p,
                staticPlugins = E.plugins,
                plugins = [];

            // 每个实例,拥有?份自己的 plugins 列表
            for(key in staticPlugins) {
                plugins[key] = Lang.merge(staticPlugins[key]);
            }
            this.plugins = plugins;

            // 工具栏上的插?
            this.toolbar.init();

            // 状?栏上的插件
            this.statusbar.init();
            
            // 其它功能性插?
            for (key in plugins) {
                p = plugins[key];
                if (p.inited) continue;

                if (p.type === E.PLUGIN_TYPE.FUNC) {
                    p.editor = this; // ? p 增加 editor 属??
                    if (p.init) {
                        p.init();
                    }
                    p.inited = true;
                }
            }
        },

        /**
         * 生成 DOM 结构
         */
        _renderContainer: function() {
            var textarea = this.textarea,
                region = Dom.getRegion(textarea),
                width = (region.right - region.left - 2) + "px", // YUI ? getRegion ? 2px 偏差
                height = (region.bottom - region.top - 2) + "px",
                container = document.createElement("div"),
                content, iframe;

            container.className = EDITOR_CLASSNAME;
            container.style.width = "780px";
            container.innerHTML = EDITOR_TMPL;

            content = container.childNodes[1];
            content.style.width = "780px";
            content.style.height = height;

            iframe = content.childNodes[0];
            iframe.style.width = "780px";
            iframe.style.height = "100%"; // 使得 resize 插件能正常工?
            iframe.setAttribute("frameBorder", 0);

            textarea.style.display = "none";
            Dom.insertBefore(container, textarea);

            this.container = container;
            this.toolbar.domEl = container.childNodes[0];
            this.contentWin = iframe.contentWindow;
            this.contentDoc = iframe.contentWindow.document;
            
            this.statusbar.domEl = container.childNodes[2];

            // TODO 目前是根? textatea 的宽度来设定 editor 的宽度?可以?虑 config 里指定宽?
        },

        _setupContentPanel: function() {
            var doc = this.contentDoc,
                config = this.config,
                contentCSS = "content" + (config.debug ? "" : "-min") + ".css",
                contentCSSUrl = config.base + THEMES_DIR + "/" + config.theme + "/" + contentCSS,
                self = this;

            // 初始? iframe 的内?
            doc.open();
            doc.write(CONTENT_TMPL
                    .replace("{CONTENT_CSS}", contentCSSUrl)
                    .replace("{CONTENT}", this.textarea.value));
            doc.close();

            if (ie) {
                // ? contentEditable ??,否则 ie 下?区为黑底白?
                doc.body.contentEditable = "true";
            } else {
                // firefox ? designMode 的支持更?
                doc.designMode = "on";
            }

            // ?1:? tinymce ?,designMode = "on" 放在 try catch 里??
            //     原因是在 firefox ?,当iframe ? display: none 的容器里,会导致错误??
            //     但经过我测试,firefox 3+ 以上已无此现象??
            // ?2: ie ? contentEditable = true.
            //     原因是在 ie ?,IE needs to use contentEditable or it will display non secure items for HTTPS
            // Ref:
            //   - Differences between designMode and contentEditable
            //     http://74.125.153.132/search?q=cache:5LveNs1yHyMJ:nagoon97.wordpress.com/2008/04/20/differences-between-designmode-and-contenteditable/+ie+contentEditable+designMode+different&cd=6&hl=en&ct=clnk

            // TODO: 让初始输入文字始终在 p 标签?
            // 下面的处理办法不妥当
			//            if (Lang.trim(doc.body.innerHTML).length === 0) {
			//                if(UA.gecko) {
			//                    doc.body.innerHTML = '<p><br _moz_editor_bogus_node="TRUE" _moz_dirty=""/></p>';
			//                } else {
			//                    doc.body.innerHTML = '<p></p>';
			//                }
			//            }

            if(ie) {
                // 点击? iframe doc ? body 区域?,还原焦点位置
                Event.on(doc, "click", function() {
                    if (doc.activeElement.parentNode.nodeType === 9) { // 点击? doc ?
                        self._focusToEnd();
                    }
                });
				this.fixIE6Undo(doc.body);
            }
        },

        _initAutoFocus: function() {
            if (this.config.autoFocus) {
                this._focusToEnd();
            }
        },

        /**
         * 将光标定位到?后一个元?
         */
        _focusToEnd: function() {
            this.contentWin.focus();

            var lastChild = this.contentDoc.body.lastChild,
                range = E.Range.getSelectionRange(this.contentWin);

            if (UA.ie) {
                try { // 有时会报?:编辑? ie ?,切换源代?,再切换回?,点击编辑器框?,有无效指针的JS错误
                    range.moveToElementText(lastChild);
                } catch(ex) { }
                range.collapse(false);
                range.select();

            } else {
                try {
                    range.setEnd(lastChild, lastChild.length);
                } catch(ex) { }
                range.collapse(false);
            }
        },
        
        /**
         * 将光标定位到?后一个元?
         */
        _focusToFirst: function() {
            this.contentWin.focus();

            var lastChild = this.contentDoc.body.firstChild,
                range = E.Range.getSelectionRange(this.contentWin);
            if (UA.ie) {
                try { // 有时会报?:编辑? ie ?,切换源代?,再切换回?,点击编辑器框?,有无效指针的JS错误
                    range.moveToElementText(lastChild);
                } catch(ex) { }
                range.collapse(false);
                range.select();

            } else {
                try {
                    range.setEnd(lastChild, 0);
                } catch(ex) {}
                range.collapse(false);
            }
        },

        /**
         * 获取焦点
         */
        focus: function() {
          this._focusToEnd();
        },

        /**
         * 执行 execCommand
         */
        execCommand: function(commandName, val, styleWithCSS) {
			//alert(commandName);
            this.contentWin.focus(); // 还原焦点
            E.Command.exec(this.contentDoc, commandName, val, styleWithCSS);
        },

        /**
         * 获取数据
         */
        getData: function() {
            if(this.sourceMode) {
                return this.textarea.value;
            }
            return this.getContentDocData();
        },

        /**
         * 获取 contentDoc 中的数据
         */
        getContentDocData: function() {
            var bd = this.contentDoc.body,
                data = "", p = E.plugins["save"];

            // Firefox ?,_moz_editor_bogus_node, _moz_dirty 等特有属?
            // 这些特有属??,在用 innerHTML 获取?,自动过滤?
            data = bd.innerHTML;

            if(data == "<br>") data = ""; // firefox 下会自动生成?? br

            if(p && p.filterData) {
                data = p.filterData(data);

				//Sociax_Update_Mark:	2010-2-25 修改 自动替换?粘贴数据,上传文档中本地图?
				
				//data = data.replace(/<(\/)?b ([^>]*)>/gi,'<$1strong>');
				data = data.replace(/<(\/)?em>/gi,'<$1i>');

				/**
				 * 过滤 word 粘贴过来的垃圾数?
				 * Ref: CKEditor - pastefromword plugin
				 */
				if(data.indexOf("mso") > 0 ){

		            // Remove <meta xx...>
		            data = data.replace(/<meta[^>]*>/ig, "");
		            // Remove <link rel="xx" href="file:///...">
		            data = data.replace(/<link rel="\S+" href="file:[^>]*">/ig, "");
		            // Remove <!--[if gte mso 9|10]>...<![endif]-->
		            data = data.replace(/<!--\[if gte mso [0-9]{1,2}\]>[\s\S]*?<!\[endif\]-->/ig, "");
					data = data.replace(/<!--\[if gte vml [0-9]{1,2}\]>[\s\S]*?<!\[endif\]-->/ig, "");
					data = data.replace(/<!--\[if !mso\]>[\s\S]*?<!\[endif\]-->/ig, "");
		            // Remove <style xx...>
		            data = data.replace(/<style[\s\S]*?<\/style>/ig, "");

		            // Remove class="MsoNormal"
		            //data = data.replace(/ class="?MsoNormal.*?"?/ig, "");

					// Remove onmouseover and onmouseout events (from MS Word comments effect)
					data = data.replace(/<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/gi, "<$1$3");
					data = data.replace(/<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/gi, "<$1$3");
					data = data.replace(/<(\w[^>]*) onclick="([^\"]*)"([^>]*)/gi, "<$1$3");
					data = data.replace(/<(\w[^>]*) onload="([^\"]*)"([^>]*)/gi, "<$1$3");
					data = data.replace(/<(\w[^>]*) onblur="([^\"]*)"([^>]*)/gi, "<$1$3");
					data = data.replace(/<(\w[^>]*) onchange="([^\"]*)"([^>]*)/gi, "<$1$3");

					// The original <Hn> tag send from Word is something like this: <Hn style="margin-top:0px;margin-bottom:0px">
					data = data.replace(/<H(\d)([^>]*)>/gi, "<h$1>");

					// Word likes to insert extra <font> tags, when using MSIE. (Wierd).
					//data = data.replace(/<(H\d)><FONT[^>]*>([\s\S]*?)<\/FONT><\/\1>/gi, "<$1>$2<\/$1>");
				    //data = data.replace(/<(H\d)><EM>([\s\S]*?)<\/EM><\/\1>/gi, "<$1>$2<\/$1>");
					if(YAHOO.env.ua.ie){
						// 过滤Word中的图片
						//data = data.replace(/<v:shape.*style="([^\"]*)".*<v:imagedata.*src="([^\"]*)"><\/v:imagedata>[^(v:)]*<\/v:shape>/gi, '<img style="$1" src="$2" />');
						data = data.replace(/<v:imagedata([^>]*)src="([^\"]*)">[^:]*<\/v:imagedata>/gi, '<img src="$2" />');
						data = data.replace(/<(\/?)v:shape([^>]*)>/gi, '');
						//将word中的公式wmz全部转换成gif格式地址
						data = data.replace(/clip_image009\.(wmz|emz)/gi, 'clip_image010.gif');
						data = data.replace(/clip_image099\.(wmz|emz)/gi, 'clip_image100.gif');
						data = data.replace(/clip_image(0*)([1-9]+)\.(wmz|emz)/gi, function(i,j,k){ m = new Number(k)+1 ; return 'clip_image'+j+m+'.gif'});
					}

					// 过滤多余标签 <v:shapetype <v:stroke <v:f <v:formulas <v:path <o:lock <w:wrap
					data = data.replace(/<v:shapetype.*<\/v:shapetype>/gi, '');
					data = data.replace(/<v:stroke.*<\/v:stroke>/gi, '');
					data = data.replace(/<v:formulas.*<\/v:formulas>/gi, '');
					data = data.replace(/<v:f.*<\/v:f>/gi, '');
					data = data.replace(/<v:path.*<\/v:path>/gi, '');
					data = data.replace(/<o:lock.*<\/o:lock>/gi, '');
					data = data.replace(/<w:wrap.*<\/w:wrap>/gi, '');

					// 删除定位问题
					data = data.replace(/POSITION: absolute;/gi, '');
					data = data.replace(/POSITION: relative;/gi, '');
		
					// Remove XML elements and declarations
					data = data.replace(/<\\?\?xml[^>]*>/gi, "") ;
				}

				if(data.match(/file:[^><]*\.(jpg|png|bmp|gif)/gi)!=null){
					//data = data.replace(/\\/gi, '/');
					//判断浏览?,IE支持?下操?,IE之外弹出提示只有IE才能上传本地图片
					if(YAHOO.env.ua.ie){
						try{
							//var eWebEditorObj	=	new ActiveXObject('eWebSoft.eWebEditorActiveX');
							var eWebEditorObj	=	new ActiveXObject("eWebEditorClient.eWebEditor");
							var posturl	=	this.config.upload_url+'&act=ewebeditor&type=local';
							var dynObj	=	document.createElement('div');

							dynObj.innerHTML = "<OBJECT ID='eWebEditorClient' CLASSID='CLSID:D39A5EBE-F907-4EC2-BCDF-A72F58DA01F4' width=0 height=0 CODEBASE='eWebEditorClient.CAB#version=1,7,0,0'>";
							
							//var eWebEditorClient	=	document.getElementById('eWebEditorClient');
							
							if( eWebEditorObj.Test() == 'eWebEditor' ){

								eWebEditorObj.LocalUpload(data, posturl);
								//alert(eWebEditorObj.Status);
								if(eWebEditorObj.Error == '') {
									alert('Word中文件上传成?!');
									data	=	eWebEditorObj.Body;
									bd.innerHTML	=	data;
								}
							}
						}
						catch(e){
							//提示安装控件
							$.tbox.yes('<p><a href="'+this.config.base+'eWebEditorClientInstall.exe"><font size="12">要自动上传Word或本地图?,请点击这里下载安装控?!</font></a></p>','请安装控?');
						}
					}
					//else{
					//	alert('要自动上传Word或本地图?,请更换IE浏览?,或?删除内容中的本地图片??');
					//}
				}
            }

            return data;
        },

        /**
         * 获取选中区域? Range 对象
         */
        getSelectionRange: function() {
            return E.Range.getSelectionRange(this.contentWin);
        },
		
		applyUndoStep : function(e){
			//this.focus();
			//alert(e._undoQueue[e._undoQueueIndex]);
			//var r = E.Range.saveRange(this);
			 // 还原焦点
            //this.contentWin.focus();
 			//r.select(); // 还原选区
            //r.pasteHTML(e._undoQueue[e._undoQueueIndex]);
			//clearTimeout(_undoTime);
			//_undoTime = setTimeout(function(){
			if(e._undoQueue[e._undoQueueIndex] != 'undefined'){
				e.innerHTML = e._undoQueue[e._undoQueueIndex];
			}
			//},100);
			//alert(1);
			//alert(e._undoQueue[e._undoQueueIndex]);
			
		},
		
		fixIE6Undo:function(editor){
			if(ie != 6 ) return false;
			
			//设置?个全?的时间撮
			
			var _undoTimer = null;
			
			//设置undo的最大步?
			var _maxUndoStep = 50;
			
			
			//处理编辑器中的内?
			var bd = editor;
			
			bd._undoQueue = new Array();
			bd._bookmarkQueue = new Array();
			bd._undoQueueIndex = 0;
			bd._bookmarkQueue.push([0,0,bd.scrollTop]);
			
			Event.on(bd, "keyup", function(ev){
				var keyCode = ev.keyCode;
				clearTimeout(_undoTimer);
				//只要按着ctrl就返回这次事? 
				if(keyCode == 90 || keyCode == 17) return false;
				_undoTimer = setTimeout(function(){
					var uQue = bd._undoQueue;
					var bQue = bd._bookmarkQueue;
					//alert(bd.innerHtml != uQue[bd._undoQueueIndex]);
					//bd._undoQueueIndex = bd._undoQueueIndex != 0 ? 0 : bd._undoQueueIndex - 1;
					if(bd.innerHTML != uQue[bd._undoQueueIndex-1]){
						//队列中的?后一个?已经被改变
						var len = uQue.length - bd._undoQueueIndex;
						if(len > 0){
							//切分undo队列
							uQue.splice(bd._undoQueueIndex,len);
						}
						
						//如果大于?大返回步骤?将队列的最后一个之出筏
						if(uQue.length >= _maxUndoStep){
							uQue.shift();
							bd._undoQueueIndex --;
						}
						
						//添加新的队列以及?
						bd._undoQueueIndex = uQue.push(bd.innerHTML);
					}
				},300);
			}, this, true);
		}
    });

 });

KISSY.Editor.add("core~toolbar", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Lang = YAHOO.lang,
        isIE = YAHOO.env.ua.ie,
        isIE6 = isIE === 6,
        TYPE = E.PLUGIN_TYPE,
        TOOLBAR_SEPARATOR_TMPL = '<div class="ks-editor-stripbar-sep ks-inline-block"></div>',

        TOOLBAR_BUTTON_TMPL = '' +
		'<div class="ks-editor-toolbar-button ks-inline-block" title="{TITLE}">' +
			'<div class="ks-editor-toolbar-button-outer-box">' +
				'<div class="ks-editor-toolbar-button-inner-box">' +
					'<span class="ks-editor-toolbar-item ks-editor-toolbar-{NAME}">{TEXT}</span>' +
				'</div>' +
			'</div>' +
		'</div>',

        TOOLBAR_MENU_BUTTON_TMPL = '' +
		'<div class="ks-editor-toolbar-menu-button-caption ks-inline-block">' +
			'<span class="ks-editor-toolbar-item ks-editor-toolbar-{NAME}">{TEXT}</span>' +
		'</div>' +
		'<div class="ks-editor-toolbar-menu-button-dropdown ks-inline-block"></div>',

        TOOLBAR_MENU_BUTTON = "ks-editor-toolbar-menu-button",
        TOOLBAR_SELECT = "ks-editor-toolbar-select",
        TOOLBAR_BUTTON_ACTIVE = "ks-editor-toolbar-button-active",
        TOOLBAR_BUTTON_HOVER = "ks-editor-toolbar-button-hover",
        TOOLBAR_BUTTON_SELECTED = "ks-editor-toolbar-button-selected",
    
        STATE_CMDS = "fontName,fontSize,bold,italic,underline,strikeThrough"
                     + "insertOrderedList,insertUnorderedList"
                     + "justifyLeft,justifyCenter,justifyRight",

        div = document.createElement("div"); // 通用 el 容器


    E.Toolbar = function(editor) {

        /**
         * 相关联的编辑器实?
         */
        this.editor = editor;

        /**
         * 相关联的配置
         */
        this.config = editor.config;

        /**
         * 当前语言
         */
        this.lang = E.lang[this.config.language];

        /**
         * ?有加载的工具栏插?
         */
        this.items = [];

        /**
         * ?有需要动态更新状态的工具栏插件项
         */
        this.stateItems = [];
    };
    
    Lang.augmentObject(E.Toolbar.prototype, {

        /**
         * 初始化工具条
         */
        init: function() {
            var items = this.config.toolbar,
                plugins = this.editor.plugins,
                key, p;

            // 遍历配置?,找到相关插件?,并添加到工具栏上
            for (var i = 0, len = items.length; i < len; ++i) {
                key = items[i];
                if (key) {
                    if (!(key in plugins)) continue; // 配置项里?,但加载的插件里无,直接忽略

                    // 添加插件?
                    p = plugins[key];
                    this._addItem(p);

                    this.items.push(p);
                    if(STATE_CMDS.indexOf(p.name) !== -1) {
                        this.stateItems.push(p);
                    }

                } else { // 添加分隔?
                    this._addSeparator();
                }
            }

            // 状?更?
            this._initUpdateState();
        },

        /**
         * 添加工具栏项
         */
        _addItem: function(p) {
            var el, type = p.type, lang = this.lang, html;

            // ? plugin 没有设置 lang ?,采用默认语言配置
            // TODO: 考虑重构? instance 模块?,因为 lang 仅跟实例相关
            if (!p.lang) p.lang = Lang.merge(lang["common"], this.lang[p.name] || {});

            // 根据模板构建 DOM
            html = TOOLBAR_BUTTON_TMPL
                    .replace("{TITLE}", p.lang.title || "")
                    .replace("{NAME}", p.name)
                    .replace("{TEXT}", p.lang.text || "");
            if (isIE6) {
                html = html
                        .replace("outer-box", "outer-box ks-inline-block")
                        .replace("inner-box", "inner-box ks-inline-block");
            }
            div.innerHTML = html;

            // 得到 domEl
            p.domEl = el = div.firstChild;

            // 根据插件类型,调整 DOM 结构
            if (type == TYPE.TOOLBAR_MENU_BUTTON || type == TYPE.TOOLBAR_SELECT) {
                // ?:select 是一种特殊的 menu button
                this._renderMenuButton(p);

                if(type == TYPE.TOOLBAR_SELECT) {
                    this._renderSelect(p);
                }
            }

            // 绑定事件
            this._bindItemUI(p);

            // 添加到工具栏
            this._addToToolbar(el);

            // 调用插件自己的初始化函数,这是插件的个性化接口
            // init 放在添加到工具栏后面,可以保证 DOM 操作比如? region 等操作的正确?
            p.editor = this.editor; // ? p 增加 editor 属??
            if (p.init) {
                p.init();
            }

            // 标记为已初始化完?
            p.inited = true;
        },

        /**
         * 初始化下拉按钮的 DOM
         */
        _renderMenuButton: function(p) {
            var el = p.domEl,
                innerBox = el.getElementsByTagName("span")[0].parentNode;

            Dom.addClass(el, TOOLBAR_MENU_BUTTON);
            innerBox.innerHTML = TOOLBAR_MENU_BUTTON_TMPL
                    .replace("{NAME}", p.name)
                    .replace("{TEXT}", p.lang.text || "");
        },

        /**
         * 初始? selectBox ? DOM
         */
        _renderSelect: function(p) {
            Dom.addClass(p.domEl, TOOLBAR_SELECT);
        },

        /**
         * 给工具栏项绑定事?
         */
        _bindItemUI: function(p) {
            var el = p.domEl;

            // 1. 注册点击时的响应函数
            if (p.exec) {
                Event.on(el, "click", function() {
                    p.exec();
                });
            }

            // 2. 添加鼠标点击?,按钮按下的效?
            Event.on(el, "mousedown", function() {
                Dom.addClass(el, TOOLBAR_BUTTON_ACTIVE);
            });
            Event.on(el, "mouseup", function() {
                Dom.removeClass(el, TOOLBAR_BUTTON_ACTIVE);
            });
            // TODO 完善效果:在鼠标左键按下状?,将鼠标移出和移入按钮?,按钮状?的切换
            // ?:firefox ?,按住左键,将鼠标移出和移入按钮?,不会触发 mouseout. ?要研究下 google 是如何实现的
            Event.on(el, "mouseout", function(e) {
                var toElement = Event.getRelatedTarget(e), isChild;

                try {
                    if (el.contains) {
                        isChild = el.contains(toElement);
                    } else if (el.compareDocumentPosition) {
                        isChild = el.compareDocumentPosition(toElement) & 8;
                    }
                } catch(e) {
                    isChild = false; // 已经移动? iframe ?
                }
                if (isChild) return;

                Dom.removeClass(el, TOOLBAR_BUTTON_ACTIVE);
            });

            // 3. ie6 ?,模拟 hover
            if(isIE6) {
                Event.on(el, "mouseenter", function() {
                    Dom.addClass(el, TOOLBAR_BUTTON_HOVER);
                });
                Event.on(el, "mouseleave", function() {
                    Dom.removeClass(el, TOOLBAR_BUTTON_HOVER);
                });
            }
        },

        /**
         * 添加分隔?
         */
        _addSeparator: function() {
            div.innerHTML = TOOLBAR_SEPARATOR_TMPL;
            this._addToToolbar(div.firstChild);
        },

        /**
         * ? item ? 分隔? 添加到工具栏
         */
        _addToToolbar: function(el) {
            if(isIE) el = E.Dom.setItemUnselectable(el);
            this.domEl.appendChild(el);
        },

        /**
         * 初始化按钮状态的动?更?
         */
        _initUpdateState: function() {
            var doc = this.editor.contentDoc,
                self = this;

            Event.on(doc, "click", function() { self.updateState(); });
            Event.on(doc, "keyup", function(ev) {
                var keyCode = ev.keyCode;

                // PGUP,PGDN,END,HOME: 33 - 36
                // LEFT,UP,RIGHT,DOWN:37 - 40
                // BACKSPACE: 8
                // ENTER: 13
                // DEL: 46
                if((keyCode >= 33 && keyCode <= 40)
                    || keyCode === 8
                    || keyCode === 13
                    || keyCode === 46) {
                    self.updateState();
                }
            });

            // TODO: 监控粘贴时的事件,粘贴后需要更新按钮状?
        },

        /**
         * 按钮状?的动?更?(包括按钮选中状?的更新、字体字号的更新、颜色的动?更新等)
         * 遵守 Google Docs 的原?,让所有按钮始终可点击,只更新状?,不禁用按?
         */
        updateState: function(filterNames) {
            var items = this.stateItems, p;
            filterNames = filterNames ? filterNames.join("|") : "";

            for(var i = 0, len = items.length; i < len; i++) {
                p = items[i];
                
                if(filterNames && filterNames.indexOf(p.name) === -1)
                    continue;

                // 调用插件自己的状态更新函?
                if(p.updateState) {
                    p.updateState();
                    continue;
                }

                // 默认的状态更新函?
                this.updateItemState(p);
            }

            // TODO: webkit ?,对齐的状态没获取?
        },

        updateItemState: function(p) {
            var doc = this.editor.contentDoc;

            // 默认的状态更新函?
            try {
                if (doc.queryCommandEnabled(p.name)) {
                    if (doc.queryCommandState(p.name)) {
                        Dom.addClass(p.domEl, TOOLBAR_BUTTON_SELECTED);
                    } else {
                        Dom.removeClass(p.domEl, TOOLBAR_BUTTON_SELECTED);
                    }
                }
            } catch(ex) {
            }
        }
    });

 });

KISSY.Editor.add("core~statusbar", function(E) {

    var Y = YAHOO.util, Lang = YAHOO.lang,
        isIE = YAHOO.env.ua.ie,

        SEP_TMPL = '<div class="ks-editor-stripbar-sep kissy-inline-block"></div>',
        ITEM_TMPL = '<div class="ks-editor-statusbar-item ks-editor-{NAME} ks-inline-block"></div>',

        div = document.createElement("div"); // 通用 el 容器

    E.Statusbar = function(editor) {

        /**
         * 相关联的编辑器实?
         */
        this.editor = editor;

        /**
         * 相关联的配置
         */
        this.config = editor.config;

        /**
         * 当前语言
         */
        this.lang = E.lang[this.config.language];
    };
    
    Lang.augmentObject(E.Statusbar.prototype, {

        /**
         * 初始?
         */
        init: function() {
            var items = this.config.statusbar,
                plugins = this.editor.plugins,
                key;

            // 遍历配置?,找到相关插件?,并添加到工具栏上
            for (var i = 0, len = items.length; i < len; ++i) {
                key = items[i];
                if (key) {
                    if (!(key in plugins)) continue; // 配置项里?,但加载的插件里无,直接忽略

                    // 添加插件?
                    this._addItem(plugins[key]);

                } else { // 添加分隔?
                    this._addSep();
                }
            }
        },

        /**
         * 添加工具栏项
         */
        _addItem: function(p) {
            var el, lang = this.lang;

            // ? plugin 没有设置 lang ?,采用默认语言配置
            // TODO: 考虑重构? instance 模块?,因为 lang 仅跟实例相关
            if (!p.lang) p.lang = Lang.merge(lang["common"], this.lang[p.name] || {});

            // 根据模板构建 DOM
            div.innerHTML = ITEM_TMPL.replace("{NAME}", p.name);

            // 得到 domEl
            p.domEl = el = div.firstChild;

            // 添加到工具栏
            this._addToToolbar(el);

            // 调用插件自己的初始化函数,这是插件的个性化接口
            // init 放在添加到工具栏后面,可以保证 DOM 操作比如? region 等操作的正确?
            p.editor = this.editor; // ? p 增加 editor 属??
            if (p.init) {
                p.init();
            }

            // 标记为已初始化完?
            p.inited = true;
        },

        /**
         * 添加分隔?
         */
        _addSep: function() {
            div.innerHTML = SEP_TMPL;
            this._addToToolbar(div.firstChild);
        },

        /**
         * ? item ? 分隔? 添加到状态栏
         */
        _addToToolbar: function(el) {
            if(isIE) el = E.Dom.setItemUnselectable(el);
            this.domEl.appendChild(el);
        }
    });

 });

KISSY.Editor.add("core~menu", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event,
        UA = YAHOO.env.ua,

        DISPLAY = "display",
        NONE = "none",
        EMPTY = "",
        DROP_MENU_CLASS = "ks-editor-drop-menu",
        SHADOW_CLASS = "ks-editor-drop-menu-shadow",
        CONTENT_CLASS = "ks-editor-drop-menu-content",
        SELECTED_CLASS = "ks-editor-toolbar-button-selected",
        SHIM_CLASS = DROP_MENU_CLASS + "-shim", //  // iframe shim ? class
        shim; // 共用?? shim 即可
    
    E.Menu = {

        /**
         * 生成下拉?
         * @param {KISSY.Editor} editor dropMenu ?属的编辑器实?
         * @param {HTMLElement} trigger
         * @param {Array} offset dropMenu 位置的偏移量
         * @return {HTMLElement} dropMenu
         */
        generateDropMenu: function(editor, trigger, offset) {
            var dropMenu = document.createElement("div"),
                 self = this;

            // 添加阴影?
            dropMenu.innerHTML = '<div class="' + SHADOW_CLASS + '"></div>'
                               + '<div class="' + CONTENT_CLASS + '"></div>';
            
            // 生成 DOM
            dropMenu.className = DROP_MENU_CLASS;
            dropMenu.style[DISPLAY] = NONE;
            document.body.appendChild(dropMenu);

            // 点击触点?,显示下拉?
            // ?:?个编辑器实例,?多只能有?个激活的下拉?
            Event.on(trigger, "click", function(ev) {
                // 不向上传?,自己控制
                // 否则 document 上监控点击后,会关闭刚打开? dropMenu
                Event.stopPropagation(ev);

                // 隐藏当前?活的下拉?
                editor.activeDropMenu && self._hide(editor);

                // 打开当前 trigger ? dropMenu
                if(editor.activeDropMenu != dropMenu) {
                    self._setDropMenuPosition(trigger, dropMenu, offset); // 延迟到显示时调整位置
                    editor.activeDropMenu = dropMenu;
                    editor.activeDropButton = trigger;
                    self._show(editor);

                } else { // 第二次点击在 trigger ?,关闭 activeDropMenu, 并置? null. 否则会导致第三次点击打不?
                    editor.activeDropMenu = null;
                    editor.activeDropButton = null;
                }
            });

            // document 捕获到点击时,关闭当前?活的下拉?
            Event.on([document, editor.contentDoc], "click", function() {
                if(editor.activeDropMenu) {
                    self.hideActiveDropMenu(editor);

                    // 还原选区和焦?
                    if (this == editor.contentDoc) {
                        // TODO: [bug 58]  ?要重写一? focusmanager 来统?管理焦点
						//                        if (UA.ie) {
						//                            var range = editor.getSelectionRange();
						//                            range.select();
						//                        }
                        editor.contentWin.focus();
                    }
                }
            });

            // 改变窗口大小?,动?调整位?
            this._initResizeEvent(trigger, dropMenu, offset);

            // 返回
            return dropMenu.childNodes[1]; // 返回 content 部分
        },

        /**
         * 设置 dropMenu 的位?
         */
        _setDropMenuPosition: function(trigger, dropMenu, offset) {
            var r = Dom.getRegion(trigger),
                left = r.left, top = r.bottom;

            if(offset) {
                left += offset[0];
                top += offset[1];
            }

            dropMenu.style.left = left + "px";
            dropMenu.style.top = top + "px";
        },

        _isVisible: function(el) {
            if(!el) return false;
            return el.style[DISPLAY] != NONE;
        },

        /**
         * 隐藏编辑器当前打?的下拉框
         */
        hideActiveDropMenu: function(editor) {
            this._hide(editor);
            editor.activeDropMenu = null;
            editor.activeDropButton = null;
        },

        _hide: function(editor) {
            var dropMenu = editor.activeDropMenu,
                dropButton = editor.activeDropButton;

            if(dropMenu) {
                shim && (shim.style[DISPLAY] = NONE);

                dropMenu.style[DISPLAY] = NONE;
                //dropMenu.style.visibility = "hidden";
                // ?:visibilty 方式会导致ie?,上传并插入文?(选择了?取文件?)?,编辑区域焦点丢失
            }

            dropButton && (Dom.removeClass(dropButton, SELECTED_CLASS));
        },

        _show: function(editor) {
            var dropMenu = editor.activeDropMenu,
                dropButton = editor.activeDropButton;

            if (dropMenu) {
                dropMenu.style[DISPLAY] = EMPTY;

                if (UA.ie === 6) {
                    this._updateShimRegion(dropMenu);
                    shim.style[DISPLAY] = EMPTY;
                }
            }

            dropButton && (Dom.addClass(dropButton, SELECTED_CLASS));
        },

        _updateShimRegion: function(el) {
            if(el) {
                if(UA.ie === 6) {
                    if(!shim) this._initShim();
                    this._setShimRegion(el);
                }
            }
        },

        /**
         * window.onresize ?,重新调整 dropMenu 的位?
         */
        _initResizeEvent: function(trigger, dropMenu, offset) {
            var self = this, resizeTimer;

            Event.on(window, "resize", function() {
                if (resizeTimer) {
                    clearTimeout(resizeTimer);
                }

                resizeTimer = setTimeout(function() {
                    if(self._isVisible(dropMenu)) { // 仅在显示?,?要动态调?
                        self._setDropMenuPosition(trigger, dropMenu, offset);
                    }
                }, 50);
            });
        },

        _initShim: function() {
            shim = document.createElement("iframe");
            shim.src = "about:blank";
            shim.className = SHIM_CLASS;
            shim.style.position = "absolute";
            shim.style[DISPLAY] = NONE;
            shim.style.border = NONE;
            document.body.appendChild(shim);
        },

        /**
         * 设置 shim ? region
         * @protected
         */
        _setShimRegion: function(el) {
            if (shim && this._isVisible(el)) {
                var r = Dom.getRegion(el);
                if (r.width > 0) {
                    shim.style.left = r.left + "px";
                    shim.style.top = r.top + "px";
                    shim.style.width = (r.width - 1) + "px"; // 少一像素,否则 ie6 下会露出?像素
                    shim.style.height = (r.height - 1) + "px";
                }
            }
        }
    };

 });

KISSY.Editor.add("core~dom", function(E) {

    var UA = YAHOO.env.ua;

    E.Dom = {

        /**
         * 获取元素的文本内?
         */
        getText: function(el) {
            return el ? (el.textContent || '') : '';
        },

        /**
         * 让元素不可??,解决 ie ? selection 丢失的问?
         */
        setItemUnselectable: function(el) {
            var arr, i, len, n, a;

            arr = el.getElementsByTagName("*");
            for (i = -1, len = arr.length; i < len; ++i) {
                a = (i == -1) ? el : arr[i];

                n = a.nodeName;
                if (n && n != "INPUT") {
                    a.setAttribute("unselectable", "on");
                }
            }

            return el;
        },

        // Ref: CKEditor - core/dom/elementpath.js
        BLOCK_ELEMENTS: {

            /* 结构元素 */
            blockquote:1,
            div:1,
            h1:1,h2:1,h3:1,h4:1,h5:1,h6:1,
            hr:1,
            p:1,

            /* 文本格式元素 */
            address:1,
            center:1,
            pre:1,

            /* 表单元素 */
            form:1,
            fieldset:1,
            caption:1,

            /* 表格元素 */
            table:1,
            tbody:1,
            tr:1, th:1, td:1,

            /* 列表元素 */
            ul:1, ol:1, dl:1,
            dt:1, dd:1, li:1
        }
    };

    // for ie
    if (UA.ie) {
        E.Dom.getText = function(el) {
            return el ? (el.innerText || '') : '';
        };
    }

 });

KISSY.Editor.add("core~color", function(E) {

    var TO_STRING = "toString",
        PARSE_INT = parseInt,
        RE = RegExp;

    E.Color = {
        KEYWORDS: {
            black: "000",
            silver: "c0c0c0",
            gray: "808080",
            white: "fff",
            maroon: "800000",
            red: "f00",
            purple: "800080",
            fuchsia: "f0f",
            green: "008000",
            lime: "0f0",
            olive: "808000",
            yellow: "ff0",
            navy: "000080",
            blue: "00f",
            teal: "008080",
            aqua: "0ff"
        },

        re_RGB: /^rgb\(([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\)$/i,
        re_hex: /^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i,
        re_hex3: /([0-9A-F])/gi,

        toRGB: function(val) {
            if (!this.re_RGB.test(val)) {
                val = this.toHex(val);
            }

            if(this.re_hex.exec(val)) {
                val = "rgb(" + [
                    PARSE_INT(RE.$1, 16),
                    PARSE_INT(RE.$2, 16),
                    PARSE_INT(RE.$3, 16)
                ].join(", ") + ")";
            }
            return val;
        },

        toHex: function(val) {
            val = this.KEYWORDS[val] || val;

            if (this.re_RGB.exec(val)) {
                var r = (RE.$1 >> 0)[TO_STRING](16),
                    g = (RE.$2 >> 0)[TO_STRING](16),
                    b = (RE.$3 >> 0)[TO_STRING](16);

                val = [
                    r.length == 1 ? "0" + r : r,
                    g.length == 1 ? "0" + g : g,
                    b.length == 1 ? "0" + b : b
                ].join("");
            }

            if (val.length < 6) {
                val = val.replace(this.re_hex3, "$1$1");
            }

            if (val !== "transparent" && val.indexOf("#") < 0) {
                val = "#" + val;
            }

            return val.toLowerCase();
        },

        /**
         * Convert the custom integer (B G R) format to hex format.
         */
        int2hex: function(val) {
            var red, green, blue;

            val = val >> 0;
            red = val & 255;
            green = (val >> 8) & 255;
            blue = (val >> 16) & 255;

            return this.toHex("rgb(" + red + "," + green +"," + blue + ")");
        }
    };

 });

KISSY.Editor.add("core~command", function(E) {

    var ua = YAHOO.env.ua,

        CUSTOM_COMMANDS = {
            backColor: ua.gecko ? "hiliteColor" : "backColor"
        },
        TAG_COMMANDS = "bold,italic,underline,strikeThrough",
        STYLE_WITH_CSS = "styleWithCSS",
        EXEC_COMMAND = "execCommand";
    
    E.Command = {

        /**
         * 执行 doc.execCommand
         */
        exec: function(doc, cmdName, val, styleWithCSS) {
            cmdName = CUSTOM_COMMANDS[cmdName] || cmdName;

            this._preExec(doc, cmdName, styleWithCSS);
            doc[EXEC_COMMAND](cmdName, false, val);
        },

        _preExec: function(doc, cmdName, styleWithCSS) {

            // 关闭 gecko 浏览器的 styleWithCSS 特??,使得产生的内容和 ie ??
            if (ua.gecko) {
                var val = typeof styleWithCSS === "undefined" ? (TAG_COMMANDS.indexOf(cmdName) === -1) : styleWithCSS;
                doc[EXEC_COMMAND](STYLE_WITH_CSS, false, val);
            }
        }
    };

 });

KISSY.Editor.add("core~range", function(E) {

    var isIE = YAHOO.env.ua.ie;

    E.Range = {

        /**
         * 获取选中区域对象
         */
        getSelectionRange: function(win) {
            var doc = win.document,
                selection, range;
            if (win.getSelection) { // W3C
                selection = win.getSelection();

                if (selection.getRangeAt) {
                    range = selection.getRangeAt(0);

                } else { // for Old Webkit! 高版本的已经支持 getRangeAt
                    range = doc.createRange();
                    range.setStart(selection.anchorNode, selection.anchorOffset);
                    range.setEnd(selection.focusNode, selection.focusOffset);
                }

            } else if (doc.selection) { // IE
                range = doc.selection.createRange();
            }

            return range;
        },

        /**
         * 获取容器
         */
        getCommonAncestor: function(range) {
            return range.startContainer || // w3c
                   (range.parentElement && range.parentElement()) || // ms TextRange
                   (range.commonParentElement && range.commonParentElement()); // ms IHTMLControlRange
        },

        /**
         * 获取选中文本
         */
        getSelectedText: function(range) {
            if("text" in range) return range.text;
            return range.toString ? range.toString() : ""; // ms IHTMLControlRange ? toString 方法
        },
		/**
		 * sociax :2010-3-12 增加
		*/
        getCursorAncestor: function(editor){
            isIE && editor.contentWin.focus(); // 确保下面这行 range 是编辑区域的

        	var range = editor.getSelectionRange();
        	var container = this.getCommonAncestor(range);
        	
        	if(container.nodeName == 'DIV' && container.className == 'hdwiki_tmml'){
        		if (isIE) {
        			var p = document.createElement("p");
        			var test = container.parentNode.innerHTML;

    		    }else{
            		range.setStartAfter(container);
    		    }
        	}else{
        		if(container.parentNode.nodeName == 'DIV' && container.parentNode.className == 'hdwiki_tmml'){
                		range.setStartAfter(container);
        		}
        	}
        },
        /**
         * 保存选区 for ie
         */
        saveRange: function(editor) {
            // 1. 保存 range, 以便还原
            isIE && editor.contentWin.focus(); // 确保下面这行 range 是编辑区域的,否则 [Issue 39]

            // 2. 聚集到按钮上,隐藏光标,否则 ie 下光标会显示在层上面
            // 通过 blur / focus 等方式在 ie7- 下无?
            // 注意:2 ? 1 冲突。权衡?虑,还是取消2
            //isIE && editor.contentDoc.selection.empty();

            return editor.getSelectionRange();
        }
    };

 });
KISSY.Editor.add("smilies~config~default", function(E) {

    E.Smilies = E.Smilies || {};

    E.Smilies["default"] = {

        name: "default",

        mode: "icons",

        cols: 10,
        
        fileNames: [],

        fileExt: "gif"
    };

    for(var i = 1; i< 81;i++){
    	E.Smilies['default']['fileNames'].push(i);
    }
});

KISSY.Editor.add("plugins~base", function(E) {

    var TYPE = E.PLUGIN_TYPE,
        buttons  = "bold,italic,underline,strikeThrough," +
                   "insertOrderedList,insertUnorderedList";

    E.addPlugin(buttons.split(","), {
        /**
         * 种类:普?按?
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 响应函数
         */
        exec: function() {
            // 执行命令
            this.editor.execCommand(this.name);

            // 更新状??
            this.editor.toolbar.updateState();
        }
    });

 });

KISSY.Editor.add("plugins~color", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event,
        UA = YAHOO.env.ua,
        isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE,

        PALETTE_TABLE_TMPL = '<div class="ks-editor-palette-table"><table><tbody>{TR}</tbody></table></div>',
        PALETTE_CELL_TMPL = '<td class="ks-editor-palette-cell"><div class="ks-editor-palette-colorswatch" title="{COLOR}" style="background-color:{COLOR}"></div></td>',

        COLOR_GRAY = ["000", "444", "666", "999", "CCC", "EEE", "F3F3F3", "FFF"],
        COLOR_NORMAL = ["F00", "F90", "FF0", "0F0", "0FF", "00F", "90F", "F0F"],
        COLOR_DETAIL = [
                "F4CCCC", "FCE5CD", "FFF2CC", "D9EAD3", "D0E0E3", "CFE2F3", "D9D2E9", "EAD1DC",
                "EA9999", "F9CB9C", "FFE599", "B6D7A8", "A2C4C9", "9FC5E8", "B4A7D6", "D5A6BD",
                "E06666", "F6B26B", "FFD966", "93C47D", "76A5AF", "6FA8DC", "8E7CC3", "C27BAD",
                "CC0000", "E69138", "F1C232", "6AA84F", "45818E", "3D85C6", "674EA7", "A64D79",
                "990000", "B45F06", "BF9000", "38761D", "134F5C", "0B5394", "351C75", "741B47",
                "660000", "783F04", "7F6000", "274E13", "0C343D", "073763", "20124D", "4C1130"
        ],

        PALETTE_CELL_CLS = "ks-editor-palette-colorswatch",
        PALETTE_CELL_SELECTED = "ks-editor-palette-cell-selected";

    E.addPlugin(["foreColor", "backColor"], {
        /**
         * 种类:菜单按钮
         */
        type: TYPE.TOOLBAR_MENU_BUTTON,

        /**
         * 当前选取?
         */
        color: "",

        /**
         * 当前颜色指示?
         */
        _indicator: null,

        /**
         * 取色?
         */
        swatches: null,

        /**
         * 关联的下拉菜单框
         */
        dropMenu: null,

        range: null,

        /**
         * 初始?
         */
        init: function() {
            var el = this.domEl,
                caption = el.getElementsByTagName("span")[0].parentNode;

            this.color = this._getDefaultColor();

            Dom.addClass(el, "ks-editor-toolbar-color-button");
            caption.innerHTML = '<div class="ks-editor-toolbar-color-button-indicator" style="border-bottom-color:' + this.color + '">'
                               + caption.innerHTML
                               + '</div>';

            this._indicator = caption.firstChild;

            this._renderUI();
            this._bindUI();

            this.swatches = Dom.getElementsByClassName(PALETTE_CELL_CLS, "div", this.dropMenu);
        },

        _renderUI: function() {
            // 有两种方?:
            //  1. 仿照 MS Office 2007, 仅当点击下拉箭头?,才弹出下拉框。点? caption ?,直接设置颜色?
            //  2. 仿照 Google Docs, 不区? caption ? dropdown,让每次点击都弹出下拉框??
            // 从?辑上讲,方案1不错。但?,考虑 web 页面?,按钮比较?,方案2这样反?能增加易用性??
            // 这里采用方案2

            this.dropMenu = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]);

            // 生成下拉框内的内?
            this._generatePalettes();

            // 针对 ie,设置不可选择
            if (isIE) E.Dom.setItemUnselectable(this.dropMenu);
        },

        _bindUI: function() {
            // 注册选取事件
            this._bindPickEvent();

            Event.on(this.domEl, "click", function() {
                // 保存 range, 以便还原
                this.range = this.editor.getSelectionRange();

                // 聚集到按钮上,隐藏光标,否则 ie 下光标会显示在层上面
                // ?:通过 blur / focus 等方式在 ie7- 下无?
                isIE && this.editor.contentDoc.selection.empty();

                // 更新选中?
                this._updateSelectedColor(this.color);
            }, this, true);
        },

        /**
         * 生成取色?
         */
        _generatePalettes: function() {
            var htmlCode = "";

            // 黑白色板
            htmlCode += this._getPaletteTable(COLOR_GRAY);

            // 常用色板
            htmlCode += this._getPaletteTable(COLOR_NORMAL);

            // 详细色板
            htmlCode += this._getPaletteTable(COLOR_DETAIL);

            // 添加? DOM ?
            this.dropMenu.innerHTML = htmlCode;
        },

        _getPaletteTable: function(colors) {
            var i, len = colors.length, color,
                trs = "<tr>";

            for(i = 0, len = colors.length; i < len; ++i) {
                if(i != 0 && i % 8 == 0) {
                    trs += "</tr><tr>";
                }

                color = E.Color.toRGB("#" + colors[i]).toUpperCase();
                //console.log("color = " + color);
                trs += PALETTE_CELL_TMPL.replace(/{COLOR}/g, color);
            }
            trs += "</tr>";

            return PALETTE_TABLE_TMPL.replace("{TR}", trs);
        },

        /**
         * 绑定取色事件
         */
        _bindPickEvent: function() {
            var self = this;

            Event.on(this.dropMenu, "click", function(ev) {
                var target = Event.getTarget(ev),
                    attr = target.getAttribute("title");

                if(attr && attr.indexOf("RGB") === 0) {
                    self._doAction(attr);
                }

                // 关闭悬浮?
                Event.stopPropagation(ev);
                E.Menu.hideActiveDropMenu(self.editor);
                // ?:在这里阻止掉事件冒泡,自己处理对话框的关闭,是因?
                // ? Firefox ?,当执? doAction ?,doc 获取? click
                // 触发 updateState ?,还获取不到当前的颜色值??
                // 这样?,对?能也有好处,这种情况下不?要更? updateState
            });
        },

        /**
         * 执行操作
         */
        _doAction: function(val) {
            if (!val) return;

            // 更新当前?
            this.setColor(E.Color.toHex(val));

            // 还原选区
            var range = this.range;
            if (isIE && range.select) range.select();

            // 执行命令
            this.editor.execCommand(this.name, this.color);
        },
        
        /**
         * 设置颜色
         * @param {string} val 格式 #RRGGBB or #RGB
         */
        setColor: function(val) {
            this.color = val;

            this._updateIndicatorColor(val);
            this._updateSelectedColor(val);
        },

        /**
         * 更新指示器的颜色
         * @param val HEX 格式
         */
        _updateIndicatorColor: function(val) {
            // 更新 indicator
            this._indicator.style.borderBottomColor = val;
        },

        /**
         * 更新下拉菜单中?中的颜?
         * @param {string} val 格式 #RRGGBB or #RGB
         */
        _updateSelectedColor: function(val) {
            var i, len, swatch, swatches = this.swatches;

            for(i = 0, len = swatches.length; i < len; ++i) {
                swatch = swatches[i];

                // 获取? backgroundColor 在不同浏览器?,格式有差?,?要统?转换后再比较
                if(E.Color.toHex(swatch.style.backgroundColor) == val) {
                    Dom.addClass(swatch.parentNode, PALETTE_CELL_SELECTED);
                } else {
                    Dom.removeClass(swatch.parentNode, PALETTE_CELL_SELECTED);
                }
            }
        },

        /**
         * 更新按钮状??
         */
        // ie ?,queryCommandValue 无法正确获取? backColor 的??
        // 干脆禁用此功?,模仿 Office2007 的处?,显示?后的选取?
		//        updateState: function() {
		//            var doc = this.editor.contentDoc,
		//                name = this.name, t, val;
		//
		//            if(name == "backColor" && UA.gecko) name = "hiliteColor";
		//
		//            try {
		//                if (doc.queryCommandEnabled(name)) {
		//                    t = doc.queryCommandValue(name);
		//
		//                    if(isIE && typeof t == "number") { // ie?,对于 backColor, 有时返回 int 格式,有时又会直接返回 hex 格式
		//                        t = E.Color.int2hex(t);
		//                    }
		//                    if (t === "transparent") t = ""; // 背景色为透明色时,取默认色
		//                    if(t === "rgba(0, 0, 0, 0)") t = ""; // webkit 的背景色? rgba ?
		//
		//                    val = t ? E.Color.toHex(t) : this._getDefaultColor(); // t 为空字符串时,表示点击在空行或尚未设置样式的地?
		//                    if (val && val != this.color) {
		//                        this.color = val;
		//                        this._updateIndicatorColor(val);
		//                    }
		//                }
		//            } catch(ex) {
		//            }
		//        },

        _getDefaultColor: function() {
            return (this.name == "foreColor") ? "#000000" : "#ffffff";
        }
    });

 });

KISSY.Editor.add("plugins~font", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event,
        UA = YAHOO.env.ua,
        TYPE = E.PLUGIN_TYPE,

        OPTION_ITEM_HOVER_CLS = "ks-editor-option-hover",
        SELECT_TMPL = '<ul class="ks-editor-select-list">{LI}</ul>',
        OPTION_TMPL = '<li class="ks-editor-option" data-value="{VALUE}">' +
                          '<span class="ks-editor-option-checkbox"></span>' +
                          '<span style="{STYLE}">{KEY}</span>' +
                      '</li>',
        OPTION_SELECTED = "ks-editor-option-selected",
        WEBKIT_FONT_SIZE = {
            "10px" : 1,
            "13px" : 2,
            "16px" : 3,
            "18px" : 4,
            "24px" : 5,
            "32px" : 6,
            "48px" : 7
        };

    E.addPlugin(["fontName", "fontSize"], {
        /**
         * 种类:菜单按钮
         */
        type: TYPE.TOOLBAR_SELECT,

        /**
         * 当前选中?
         */
        selectedValue: "",

        /**
         * 选择框头?
         */
        selectHead: null,

        /**
         * 关联的下拉?择列表
         */
        selectList: null,

        /**
         * 下拉框里的所有?项?
         */
        options: [],

        /**
         * 下拉列表?
         */
        items: null,

        /**
         * 选中的项
         */
        selectedItem: null,

        /**
         * 选中区域对象
         */
        range: null,

        /**
         * 初始?
         */
        init: function() {
            this.options = this.lang.options;
            this.selectHead = this.domEl.getElementsByTagName("span")[0];

            this._renderUI();
            this._bindUI();
        },

        _renderUI: function() {
            // 初始化下拉框 DOM
            this.selectList = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]);
            this._renderSelectList();
            this.items = this.selectList.getElementsByTagName("li");
        },

        _bindUI: function() {
            // 注册选取事件
            this._bindPickEvent();

            Event.on(this.domEl, "click", function() {
                // 保存 range, 以便还原
                this.range = this.editor.getSelectionRange();

                // 聚集到按钮上,隐藏光标,否则 ie 下光标会显示在层上面
                // ?:通过 blur / focus 等方式在 ie7- 下无?
                UA.ie && this.editor.contentDoc.selection.empty();

                // 更新下拉框中的?中?
                if(this.selectedValue) {
                    this._updateSelectedOption(this.selectedValue);
                } else if(this.selectedItem) {
                    Dom.removeClass(this.selectedItem, OPTION_SELECTED);
                    this.selectedItem = null;
                }
                
            }, this, true);
        },

        /**
         * 初始化下拉框 DOM
         */
        _renderSelectList: function() {
            var htmlCode = "", options = this.options,
                key, val;

            for(key in options) {
                val = options[key];

                htmlCode += OPTION_TMPL
                        .replace("{VALUE}", val)
                        .replace("{STYLE}", this._getOptionStyle(key, val))
                        .replace("{KEY}", key);
            }

            // 添加? DOM ?
            this.selectList.innerHTML = SELECT_TMPL.replace("{LI}", htmlCode);

            // 添加个?化 class
            Dom.addClass(this.selectList, "ks-editor-drop-menu-" + this.name);
        },

        /**
         * 绑定取色事件
         */
        _bindPickEvent: function() {
            var self = this;

            Event.on(this.selectList, "click", function(ev) {
                var target = Event.getTarget(ev);

                if(target.nodeName != "LI") {
                    target = Dom.getAncestorByTagName(target, "li");
                }
                if(!target) return;

                self._doAction(target.getAttribute("data-value"));

                // 关闭悬浮?
                Event.stopPropagation(ev);
                E.Menu.hideActiveDropMenu(self.editor);
                // ?:在这里阻止掉事件冒泡,自己处理对话框的关闭,是因?
                // ? Firefox ?,当执? doAction ?,doc 获取? click
                // 触发 updateState ?,还获取不到当前的颜色值??
                // 这样?,对?能也有好处,这种情况下不?要更? updateState
            });

            // ie6 ?,模拟 hover
            if(UA.ie === 6) {
                Event.on(this.items, "mouseenter", function() {
                    Dom.addClass(this, OPTION_ITEM_HOVER_CLS);
                });
                Event.on(this.items, "mouseleave", function() {
                    Dom.removeClass(this, OPTION_ITEM_HOVER_CLS);
                });
            }
        },

        /**
         * 执行操作
         */
        _doAction: function(val) {
            if(!val) return;

            this.selectedValue = val;

            // 更新当前?
            this._setOption(val);

            // 还原选区
            var range = this.range;
            if(UA.ie && range.select) range.select();

            // 执行命令
            this.editor.execCommand(this.name, this.selectedValue);
        },

        /**
         * 选中某一?
         */
        _setOption: function(val) {
            // 更新头部
            this._updateHeadText(this._getOptionKey(val));

            // 更新列表选中?
            this._updateSelectedOption(val);
        },

        _getOptionStyle: function(key, val) {
          if(this.name == "fontName") {
              return "font-family:" + val;
          } else { // font size
              return "font-size:" + key + "px";
          }
        },

        _getOptionKey: function(val) {
            var options = this.options, key;
            
            for(key in options) {
                if(options[key] == val) {
                    return key;
                }
            }
            return null;
        },

        _updateHeadText: function(val) {
            this.selectHead.innerHTML = val;
        },

        /**
         * 更新下拉框的选中?
         */
        _updateSelectedOption: function(val) {
            var items = this.items,
                i, len = items.length, item;

            for(i = 0; i < len; ++i) {
                item = items[i];

                if(item.getAttribute("data-value") == val) {
                    Dom.addClass(item, OPTION_SELECTED);
                    this.selectedItem = item;
                } else {
                    Dom.removeClass(item, OPTION_SELECTED);
                }
            }
        },

        /**
         * 更新按钮状??
         */
        updateState: function() {
            var doc = this.editor.contentDoc,
                options = this.options,
                name = this.name, key, val;

            try {
                if (doc.queryCommandEnabled(name)) {
                    val = doc.queryCommandValue(name);

                    if(UA.webkit && name == "fontSize") {
                        val = this._getWebkitFontSize(val);
                    }
                    
                    val && (key = this._getOptionKey(val));
                    //console.log(key + " : " + val);

                    if (key in options) {
                        if(val != this.selectedValue) {
                            this.selectedValue = val;
                            this._updateHeadText(key);
                        }
                    } else {
                        this.selectedValue = "";
                        this._updateHeadText(this.lang.text);
                    }
                }

            } catch(ex) {
            }
        },

        _getWebkitFontSize: function(val) {
            if(val in WEBKIT_FONT_SIZE) return WEBKIT_FONT_SIZE[val];
            return null;
        }
    });

 });
KISSY.Editor.add("plugins~smiley", function(E) {

    var Y = YAHOO.util, Event = Y.Event, Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        TYPE = E.PLUGIN_TYPE,

        DIALOG_CLS = "ks-editor-smiley-dialog",
        ICONS_CLS = "ks-editor-smiley-icons",
        SPRITE_CLS = "ks-editor-smiley-sprite",

        defaultConfig = {
                tabs: ["default"]
            };

    E.addPlugin("smiley", {
        /**
         * 种类：按?
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 配置?
         */
        config: {},

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联? range 对象
         */
        range: null,

        /**
         * 初始化函?
         */
        init: function() {
            this.config = Lang.merge(defaultConfig, this.editor.config.pluginsConfig[this.name] || {});

            this._renderUI();
            this._bindUI();
        },

        /**
         * 初始化对话框界面
         */
        _renderUI: function() {
            var dialog = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]);

            dialog.className += " " + DIALOG_CLS;
            this.dialog = dialog;
            this._renderDialog();

            if(UA.ie) E.Dom.setItemUnselectable(dialog);
        },

        _renderDialog: function() {
            var smileyConfig = E.Smilies[this.config["tabs"][0]], // TODO: 支持多个 tab
                mode = smileyConfig["mode"];

            if(mode === "icons") this._renderIcons(smileyConfig);
            else if(mode === "sprite") this._renderSprite(smileyConfig);

        },

        _renderIcons: function(config) {
            var base = this.editor.config.base + "smilies/" + config["name"] + "/",
                fileNames = config["fileNames"],
                fileExt = "." + config["fileExt"],
                cols = config["cols"],
                htmlCode = [],
                i, len = fileNames.length, name;

            htmlCode.push('<div class="' + ICONS_CLS + '">');
            for(i = 0; i < len; i++) {
                name = fileNames[i];

                htmlCode.push(
                        '<img src="' + base +  name + fileExt
                        + '" alt="' + name
                        + '" title="' + name
                        + '" />');

                if(i % cols === cols - 1) htmlCode.push("<br />");
            }
            htmlCode.push('</div');

            this.dialog.innerHTML = htmlCode.join("");
        },

        _renderSprite: function(config) {
            var base = config.base,
                filePattern = config["filePattern"],
                fileExt = "." + config["fileExt"],
                len = filePattern.end + 1,
                step = filePattern.step,
                i, code = [];

            code.push('<div class="' + SPRITE_CLS + ' ks-clearfix" style="' + config["spriteStyle"] + '">');
            for(i = 0; i < len; i += step) {
                code.push(
                        '<span data-icon="' + base +  i + fileExt
                        + '" style="' + config["unitStyle"] + '"></span>');
            }
            code.push('</div');

            this.dialog.innerHTML = code.join("");
        },

        /**
         * 绑定事件
         */
        _bindUI: function() {
            var self = this;

            // range 处理
            Event.on(this.domEl, "click", function() {
                self.range = E.Range.saveRange(self.editor);
            });

            // 注册表单按钮点击事件
            Event.on(this.dialog, "click", function(ev) {
                var target = Event.getTarget(ev);

                switch(target.nodeName) {
                    case "IMG":
                        self._insertImage(target.src, target.getAttribute("alt"));
                        break;
                    case "SPAN":
                        self._insertImage(target.getAttribute("data-icon"), "");
                        break;
                    default: // 点击在非按钮处，停止冒泡，保留对话框
                        Event.stopPropagation(ev);
                }
            });
        },

        /**
         * 插入图片
         */
        _insertImage: function(url, alt) {
            url = Lang.trim(url);

            // url 为空时，不处?
            if (url.length === 0) {
                return;
            }

            var editor = this.editor,
                range = this.range;

            // 插入图片
            if (window.getSelection) { // W3C
                var img = editor.contentDoc.createElement("span");
                img.className = "mr5";
                img.innerHTML = "[face:"+alt+"]";

                range.deleteContents(); // 清空选中内容
                range.insertNode(img); // 插入图片

                // 使得连续插入图片时，添加在后?
                if(UA.webkit) {
                    var selection = editor.contentWin.getSelection();
                    selection.addRange(range);
                    selection.collapseToEnd();
                } else {
                    range.setStartAfter(img);
                }

                editor.contentWin.focus(); // 显示光标

            } else if(document.selection) { // IE
                if("text" in range) { // TextRange
                    range.pasteHTML("[face:"+alt+"]");

                } else { // ControlRange
                   // editor.execCommand("insertImage", url);
                }
            }
        }
    });

 });
KISSY.Editor.add("plugins~image", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Connect = Y.Connect, Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE,

        DIALOG_CLS = "ks-editor-image",
        BTN_OK_CLS = "ks-editor-btn-ok",
        BTN_CANCEL_CLS = "ks-editor-btn-cancel",
        TAB_CLS = "ks-editor-image-tabs",
        TAB_CONTENT_CLS = "ks-editor-image-tab-content",
        UPLOADING_CLS = "ks-editor-image-uploading",
        ACTIONS_CLS = "ks-editor-dialog-actions",
        NO_TAB_CLS = "ks-editor-image-no-tab",
        SELECTED_TAB_CLS = "ks-editor-image-tab-selected",

        TABS_TMPL = { local: '<li rel="local" class="' + SELECTED_TAB_CLS  + '">{tab_local}</li>',
                      link: '<li rel="link">{tab_link}</li>',
                      album: '<li rel="album">{tab_album}</li>'
                    },

        DIALOG_TMPL = ['<form action="javascript: void(0)">',
                          '<ul class="', TAB_CLS ,' ks-clearfix">',
                          '</ul>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="local" style="display: none">',
                              '<label>{label_local}</label>',
                              '<input type="file" size="40" name="imgFile" unselectable="on" />',
                              '{local_extraCode}',
                          '</div>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="link">',
                              '<label>{label_link}</label>',
                              '<input name="imgUrl" size="50" />',
                          '</div>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="album" style="display: none">',
                              '<label>{label_album}</label>',
                              '<p style="width: 300px">尚未实现...</p>', // TODO: 从相册中选择图片
                          '</div>',
                          '<div class="', UPLOADING_CLS, '" style="display: none">',
                              '<p style="width: 300px">{uploading}</p>',
                          '</div>',
                          '<div class="', ACTIONS_CLS ,'">',
                              '<button name="ok" class="', BTN_OK_CLS, '">{ok}</button>',
                              '<span class="', BTN_CANCEL_CLS ,'">{cancel}</span>',
                          '</div>',
                      '</form>'].join(""),

        defaultConfig = {
            tabs: ["local", "link"],
            upload: {
                actionUrl: E.config.upload_url+'&act=kissy&type=local',
                filter: "png|gif|jpg|jpeg|bmp",
                filterMsg: "", // 默认? this.lang.upload_filter
                enableXdr: false,
                connectionSwf: "http://a.tbcdn.cn/yui/2.8.0r4/build/connection/connection.swf",
                formatResponse: function(data) {
                    var ret = [];
                    for (var key in data) ret.push(data[key]);
                    return ret;
                },
                extraCode: ""
            }
        };

    E.addPlugin("image", {
        /**
         * 种类:按钮
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 配置?
         */
        config: {},

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联的表?
         */
        form: null,

        /**
         * 关联? range 对象
         */
        range: null,

        currentTab: null,
        currentPanel: null,
        uploadingPanel: null,
        actionsBar: null,

        /**
         * 初始化函?
         */
        init: function() {
            var pluginConfig = this.editor.config.pluginsConfig[this.name] || {};
            defaultConfig.upload.filterMsg = this.lang["upload_filter"];
            this.config = Lang.merge(defaultConfig, pluginConfig);
            this.config.upload = Lang.merge(defaultConfig.upload, pluginConfig.upload || {});

            this._renderUI();
            this._bindUI();

            this.actionsBar = Dom.getElementsByClassName(ACTIONS_CLS, "div", this.dialog)[0];
            this.uploadingPanel = Dom.getElementsByClassName(UPLOADING_CLS, "div", this.dialog)[0];
            this.config.upload.enableXdr && this._initXdrUpload();
        },

        /**
         * 初始化对话框界面
         */
        _renderUI: function() {
            var dialog = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]),
                lang = this.lang;

            // 添加自定义项
            lang["local_extraCode"] = this.config.upload.extraCode;

            dialog.className += " " + DIALOG_CLS;
            dialog.innerHTML = DIALOG_TMPL.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            this.dialog = dialog;
            this.form = dialog.getElementsByTagName("form")[0];
            if(isIE) E.Dom.setItemUnselectable(dialog);

            this._renderTabs();
        },

        _renderTabs: function() {
            var lang = this.lang, self = this,
                ul = Dom.getElementsByClassName(TAB_CLS, "ul", this.dialog)[0],
                panels = Dom.getElementsByClassName(TAB_CONTENT_CLS, "div", this.dialog);

            // 根据配置添加 tabs
            var keys = this.config["tabs"], html = "";
            for(var k = 0, l = keys.length; k < l; k++) {
                html += TABS_TMPL[keys[k]];
            }

            // 文案
            ul.innerHTML = html.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            // 只有?? tabs 时不显示
            var tabs = ul.childNodes, len = panels.length;
            if(tabs.length === 1) {
                Dom.addClass(this.dialog, NO_TAB_CLS);
            }

            // 切换
            switchTab(tabs[0]); // 默认选中第一个Tab
            Event.on(tabs, "click", function() {
                switchTab(this);
            });

            function switchTab(trigger) {
                var j = 0, rel = trigger.getAttribute("rel");
                for (var i = 0; i < len; i++) {
                    if(tabs[i]) Dom.removeClass(tabs[i], SELECTED_TAB_CLS);
                    panels[i].style.display = "none";

                    if (panels[i].getAttribute("rel") == rel) {
                        j = i;
                    }
                }

                // ie6 ?,?更新 iframe shim
                if(UA.ie === 6) E.Menu._updateShimRegion(self.dialog);

                Dom.addClass(trigger, SELECTED_TAB_CLS);
                panels[j].style.display = "";

                self.currentTab = trigger.getAttribute("rel");
                self.currentPanel = panels[j];
            }
        },

        /**
         * 绑定事件
         */
        _bindUI: function() {
            var self = this;

            // 显示/隐藏对话框时的事?
            Event.on(this.domEl, "click", function() {
                // 仅在显示时更?
                if (self.dialog.style.visibility === isIE ? "hidden" : "visible") { // 事件的触发顺序不?
                    self._syncUI();
                }
            });

            // 注册表单按钮点击事件
            Event.on(this.dialog, "click", function(ev) {
                var target = Event.getTarget(ev),
                    currentTab = self.currentTab;

                switch(target.className) {
                    case BTN_OK_CLS:
                        if(currentTab === "local") {
                            Event.stopPropagation(ev);
                            self._insertLocalImage();
                        } else {
                            self._insertWebImage();
                        }
                        break;
                    case BTN_CANCEL_CLS: // 直接?上冒?,关闭对话?
                        break;
                    default: // 点击在非按钮?,停止冒泡,保留对话?
                        Event.stopPropagation(ev);
                }
            });
        },

        /**
         * 初始化跨域上?
         */
        _initXdrUpload: function() {
            var tabs = this.config["tabs"];

            for(var i = 0, len = tabs.length; i < len; i++) {
                if(tabs[i] === "local") { // 有上? tab 时才进行以下操作
                    Connect.transport(this.config.upload.connectionSwf);
                    //Connect.xdrReadyEvent.subscribe(function(){ alert("xdr ready"); });
                    break;
                }
            }
        },

        _insertLocalImage: function() {
            var form = this.form,
                uploadConfig = this.config.upload,
                imgFile = form["imgFile"].value,
                actionUrl = uploadConfig.actionUrl,
                self = this, ext;

            if (imgFile && actionUrl) {

                // ?查文件类型是否正?
                if(uploadConfig.filter !== "*") {
                    ext = imgFile.substring(imgFile.lastIndexOf(".") + 1).toLowerCase();
                    if(uploadConfig.filter.indexOf(ext) == -1) {
                        alert(uploadConfig.filterMsg);
                        self.form.reset();
                        return;
                    }
                }

                // 显示上传滚动?
                this.uploadingPanel.style.display = "";
                this.currentPanel.style.display = "none";
                this.actionsBar.style.display = "none";
                if(UA.ie === 6) E.Menu._updateShimRegion(this.dialog); // ie6 ?,还需更新 iframe shim

                // 发?? XHR
                Connect.setForm(form, true);
                Connect.asyncRequest("post", actionUrl, {
                    upload: function(o) {
                        try {
                            // 标准格式如下:
                            // 成功?,返回 ["0", "图片地址"]
                            // 失败?,返回 ["1", "错误信息"]
                            var data = uploadConfig.formatResponse(Lang.JSON.parse(o.responseText));
                            if (data[0] == "0") {
                                self._insertImage(data[1]);
                                self._hideDialog();
                            } else {
                                self._onUploadError(data[1]);
                            }
                        }
                        catch(ex) {
                            self._onUploadError(
                                    Lang.dump(ex) +
                                    "\no = " + Lang.dump(o) +
                                    "\n[from upload catch code]");
                        }
                    },
                    xdr: uploadConfig.enableXdr
                });
            } else {
                self._hideDialog();
            }
        },

        _onUploadError: function(msg) {
            alert(this.lang["upload_error"] + "\n\n" + msg);
            this._hideDialog();

            // 测试了以下错误类?:
            //   - json parse 异常,包括 actionUrl 不存在?未登录、跨域等各种因素
            //   - 服务器端返回错误信息 ["1", "error msg"]
        },

        _insertWebImage: function() {
            var imgUrl = this.form["imgUrl"].value;
            imgUrl && this._insertImage(imgUrl);
        },

        /**
         * 隐藏对话?
         */
        _hideDialog: function() {
            var activeDropMenu = this.editor.activeDropMenu;
            if(activeDropMenu && Dom.isAncestor(activeDropMenu, this.dialog)) {
                E.Menu.hideActiveDropMenu(this.editor);
            }

            // 还原焦点
            this.editor.contentWin.focus();
        },

        /**
         * 更新界面上的表单?
         */
        _syncUI: function() {
            // 保存 range, 以便还原
            this.range = E.Range.saveRange(this.editor);

            // reset
            this.form.reset();

            // restore
            this.uploadingPanel.style.display = "none";
            this.currentPanel.style.display = "";
            this.actionsBar.style.display = "";
        },

        /**
         * 插入图片
         */
        _insertImage: function(url, alt) {
            url = Lang.trim(url);

            // url 为空?,不处?
            if (url.length === 0) {
                return;
            }

            var editor = this.editor,
                range = this.range;

            // 插入图片
            if (window.getSelection) { // W3C
                var img = editor.contentDoc.createElement("img");
                img.src = url;
                if(alt) img.setAttribute("alt", alt);

                range.deleteContents(); // 清空选中内容
                range.insertNode(img); // 插入图片

                // 使得连续插入图片?,添加在后?
                if(UA.webkit) {
                    var selection = editor.contentWin.getSelection();
                    selection.addRange(range);
                    selection.collapseToEnd();
                } else {
                    range.setStartAfter(img);
                }

                editor.contentWin.focus(); // 显示光标

            } else if(document.selection) { // IE
                // 还原焦点
                editor.contentWin.focus();

                if("text" in range) { // TextRange
                    range.select(); // 还原选区

                    var html = '<img src="' + url + '"';
                    alt && (html += ' "alt="' + alt + '"');
                    html += '>';
                    range.pasteHTML(html);

                } else { // ControlRange
                    range.execCommand("insertImage", false, url);
                }
            }
        }
    });

 });

KISSY.Editor.add("plugins~indent", function(E) {

    var //Y = YAHOO.util, Dom = Y.Dom, Lang = YAHOO.lang,
        TYPE = E.PLUGIN_TYPE,
        //UA = YAHOO.env.ua,

		//        INDENT_ELEMENTS = Lang.merge(E.Dom.BLOCK_ELEMENTS, {
		//            li: 0 // 取消 li 元素的单独缩?,? ol/ul 整体缩进
		//        }),
		//        INDENT_STEP = "40",
		//        INDENT_UNIT = "px",

        plugin = {
            /**
             * 种类:普?按?
             */
            type: TYPE.TOOLBAR_BUTTON,

            /**
             * 响应函数
             */
            exec: function() {
                // 执行命令
                this.editor.execCommand(this.name,"25");

                // 更新状??
                // 缩进?,可能会干? list 等状?
                this.editor.toolbar.updateState();
            }
        };

    // ?:ie ?,默认使用 blockquote 元素来实现缩?
    // 下面采用自主操作 range 的方式来实现,以保持和其它浏览器一?
    // ie ?,暂时依旧用默认的
	//    if (UA.ie) {
	//
	//        plugin.exec = function() {
	//            var range = this.editor.getSelectionRange(),
	//                parentEl, indentableAncestor;
	//
	//            if(range.parentElement) { // TextRange
	//                parentEl = range.parentElement();
	//            } else if(range.item) { // ControlRange
	//                parentEl = range.item(0);
	//            } else { // 不做任何处理
	//                return;
	//            }
	//
	//            // TODO: ? CKEditor ??,完全实现多区域的 iterator
	//            // 下面? blockquote 临时解决?常见的?区的多个块的父级元素刚好是body的情?
	//            // 注意:要求 blockquote 的样式为缩进样式
	//            if(parentEl === this.editor.contentDoc.body) {
	//                this.editor.execCommand(this.name);
	//                return;
	//            }
	//            // end of 临时解决方案
	//
	//            // 获取可缩进的父元?
	//            if (isIndentableElement(parentEl)) {
	//                 indentableAncestor = parentEl;
	//            } else {
	//                 indentableAncestor = getIndentableAncestor(parentEl);
	//            }
	//
	//            // 设置 margin-left
	//            if (indentableAncestor) {
	//                var val = parseInt(indentableAncestor.style.marginLeft) >> 0;
	//                val += (this.name === "indent" ? +1 : -1) * INDENT_STEP;
	//
	//                indentableAncestor.style.marginLeft = val + INDENT_UNIT;
	//            }
	//
	//            /**
	//             * 获取可缩进的父元?
	//             */
	//            function getIndentableAncestor(el) {
	//                return Dom.getAncestorBy(el, function(elem) {
	//                    return isIndentableElement(elem);
	//                });
	//            }
	//
	//            /**
	//             * 判断是否可缩进元?
	//             */
	//            function isIndentableElement(el) {
	//                return INDENT_ELEMENTS[el.nodeName.toLowerCase()];
	//            }
	//        };
	//    }

    // 注册插件
    E.addPlugin(["indent", "outdent"], plugin);
 });

KISSY.Editor.add("plugins~justify", function(E) {

    var TYPE = E.PLUGIN_TYPE;

	NAMES = ["justifyLeft", "justifyCenter", "justifyRight"],

	plugin = {
		/**
		 * 种类:普?按?
		 */
		type: TYPE.TOOLBAR_BUTTON,

		/**
		 * 响应函数
		 */
		exec: function() {
			// 执行命令
			this.editor.execCommand(this.name);

			// 更新状??
			this.editor.toolbar.updateState(NAMES);
		}
	};

	// 注册插件
	E.addPlugin(NAMES, plugin);

 });

KISSY.Editor.add("plugins~keystroke", function(E) {

    var Y = YAHOO.util, Event = Y.Event,
        UA = YAHOO.env.ua,
		
        TYPE = E.PLUGIN_TYPE;


    E.addPlugin("keystroke", {
        /**
         * 种类
         */
        type: TYPE.FUNC,

        /**
         * 初始?
         */
        init: function() {
            var editor = this.editor;

            // [bug fix] ie7- ?,按下 Tab 键后,光标还在编辑器中闪烁,并且回车提交无效
            if (UA.ie && UA.ie < 8) {
                Event.on(editor.contentDoc, "keydown", function(ev) {
                    if(ev.keyCode == 9) {
                        this.selection.empty();
                    }
                });
            }

            // Ctrl + Enter 提交
            var form = editor.textarea.form;
            if (form) {
                new YAHOO.util.KeyListener(
                        editor.contentDoc,
                        { ctrl: true, keys: 13 },
                        {
                            fn: function() {
                                    if (!editor.sourceMode) {
                                        editor.textarea.value = editor.getData();
                                    }
                                    form.submit();
                                }
                        }
                ).enable();
            }

			// sociax 2010-3-12 增加 回车换行
		    if (form) {
                new YAHOO.util.KeyListener(
                        editor.contentDoc,
                        {keys: 13 },
                        {
                            fn: function() {
                                    if (!editor.sourceMode) {
                                    	E.Range.getCursorAncestor(editor);
                                    	return;
                                    }
                                }
                        }
                ).enable();
            }
			// sociax 2010-4-28 增加 IE6的undo以及redo控制
		    if (form && UA.ie === 6) {
				var undoDoc = editor.contentDoc.body;
                new YAHOO.util.KeyListener(
                        editor.contentDoc,
                        { ctrl: true, keys: 90 }, // ctrl+z
                        {
                            fn: function() {
									if(undoDoc._undoQueueIndex > 0){
										undoDoc._undoQueueIndex -- ;
										editor.applyUndoStep(undoDoc);
										return;
									}
                                }
                        }
                ).enable();
				
				new YAHOO.util.KeyListener(
                        editor.contentDoc,
                        { ctrl: true, keys: 89 }, // ctrl+y
                        {
                            fn: function() {
									if(undoDoc._undoQueueIndex + 1 < undoDoc._undoQueueIndex.length){
										undoDoc._undoQueueIndex ++ ;
										editor.applyUndoStep(undoDoc);
										return;
									}
                                }
                        }
                ).enable();
            }
        }

    });
 });

KISSY.Editor.add("plugins~link", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Lang = YAHOO.lang,
        UA = YAHOO.env.ua, isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE, Range = E.Range,
        timeStamp = new Date().getTime(),
        HREF_REG = /^\w+:\/\/.*|#.*$/,

        DIALOG_CLS = "ks-editor-link",
        NEW_LINK_CLS = "ks-editor-link-newlink-mode",
        BTN_OK_CLS = "ks-editor-btn-ok",
        BTN_CANCEL_CLS = "ks-editor-btn-cancel",
        BTN_REMOVE_CLS = "ks-editor-link-remove",
        DEFAULT_HREF = "http://",

        DIALOG_TMPL = ['<form onsubmit="return false"><ul>',
                          '<li class="ks-editor-link-href"><label>{href}</label><input name="href" style="width: 220px" value="" type="text" /></li>',
                          '<li class="ks-editor-link-target"><input name="target" id="target_"', timeStamp ,' type="checkbox" /> <label for="target_"', timeStamp ,'>{target}</label></li>',
                          '<li class="ks-editor-dialog-actions">',
                              '<button name="ok" class="', BTN_OK_CLS, '">{ok}</button>',
                              '<span class="', BTN_CANCEL_CLS ,'">{cancel}</span>',
                              '<span class="', BTN_REMOVE_CLS ,'">{remove}</span>',
                          '</li>',
                      '</ul></form>'].join("");

    E.addPlugin("link", {
        /**
         * 种类:按钮
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联的表?
         */
        form: null,

        /**
         * 关联? range 对象
         */
        range: null,

        /**
         * 初始化函?
         */
        init: function() {
            this._renderUI();
            this._bindUI();
        },

        /**
         * 初始化对话框界面
         */
        _renderUI: function() {
            var dialog = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]),
                lang = this.lang;

            dialog.className += " " + DIALOG_CLS;
            dialog.innerHTML = DIALOG_TMPL.replace(/\{([^}]+)\}/g, function(match, key) {
                return lang[key] ? lang[key] : key;
            });

            this.dialog = dialog;
            this.form = dialog.getElementsByTagName("form")[0];

            // webkit 调用默认? exeCommand, ?隐藏 target 设置
            UA.webkit && (this.form.target.parentNode.style.display = "none");

            isIE && E.Dom.setItemUnselectable(dialog);
        },

        /**
         * 绑定事件
         */
        _bindUI: function() {
            var form = this.form, self = this;

            // 显示/隐藏对话框时的事?
            Event.on(this.domEl, "click", function() {
                // 仅在显示时更?
                if(self.dialog.style.visibility === isIE ? "hidden" : "visible") { // 事件的触发顺序不?
                    self._syncUI();
                }
            });

            // 注册表单按钮点击事件
            Event.on(this.dialog, "click", function(ev) {
                var target = Event.getTarget(ev);

                switch(target.className) {
                    case BTN_OK_CLS:
                        self._createLink(form.href.value, form.target.checked);
                        break;
                    case BTN_CANCEL_CLS: // 直接?上冒?,关闭对话?
                        break;
                    case BTN_REMOVE_CLS:
                        self._unLink();
                        break;
                    default: // 点击在非按钮?,停止冒泡,保留对话?
                        Event.stopPropagation(ev);
                }
            });
        },

        /**
         * 更新界面上的表单?
         */
        _syncUI: function() {
            // 保存 range, 以便还原
            this.range = E.Range.saveRange(this.editor);

            var form = this.form, container, a;

            container = Range.getCommonAncestor(this.range);
            a = (container.nodeName == "A") ? container : Dom.getAncestorByTagName(container, "A");

            // 修改链接界面
            if(a) {
                form.href.value = a.href;
                form.target.checked = a.target === "_blank";
                Dom.removeClass(form, NEW_LINK_CLS);

            } else { // 新建链接界面
               // form.href.value = DEFAULT_HREF;
                form.target.checked = false;
                Dom.addClass(form, NEW_LINK_CLS);
            }

            // 放在 setTimout ?,? for ie
            setTimeout(function() {
                form.href.select();
            }, 50);
        },

        /**
         * 创建/修改链接
         */
        _createLink: function(href, target) {
            href = this._getValidHref(href);

            // href 为空?,移除链接
            if (href.length === 0) {
                this._unLink();
                return;
            }

            var range = this.range,
                div = document.createElement("div"),
                a, container, fragment;

            // 修改链接
            container = Range.getCommonAncestor(range);
            a = (container.nodeName == "A") ? container : Dom.getAncestorByTagName(container, "A");
            if (a) {
                a.href = href;
                if (target) a.setAttribute("target", "_blank");
                else a.removeAttribute("target");
                return;
            }

            // 创建链接
            a = document.createElement("a");
            a.href = href;
            if (target) a.setAttribute("target", "_blank");

            if (isIE) {
                if (range.select) range.select();
                
                if("text" in range) { // TextRange
                    a.innerHTML = range.htmlText || href;
                    div.innerHTML = "";
                    div.appendChild(a);
                    range.pasteHTML(div.innerHTML);
                } else { // ControlRange
                    // TODO: ControlRange 链接? target 实现
                    this.editor.execCommand("createLink", href);
                }

            } else if(UA.webkit) { // TODO: https://bugs.webkit.org/show_bug.cgi?id=16867
                this.editor.execCommand("createLink", href);

            } else { // W3C
                if(range.collapsed) {
                    a.innerHTML = href;
                } else {
                    fragment = range.cloneContents();
                    while(fragment.firstChild) {
                        a.appendChild(fragment.firstChild);
                    }
                }
                range.deleteContents(); // 删除原内?
                range.insertNode(a); // 插入链接
                range.selectNode(a); // 选中链接
            }
        },

        _getValidHref: function(href) {
            href = Lang.trim(href);
            if(href && !HREF_REG.test(href)) { // 不为? ? 不符合标准模? abcd://efg
               href = href; // 添加默认前缀
            }
            return href;
        },

        /**
         * 移除链接
         */
        _unLink: function() {
            var editor = this.editor,
                range = this.range,
                selectedText = Range.getSelectedText(range),
                container = Range.getCommonAncestor(range),
                parentEl;

            // 没有选中文字?
            if (!selectedText && container.nodeType == 3) {
                parentEl = container.parentNode;
                if (parentEl.nodeName == "A") {
                    parentEl.parentNode.replaceChild(container, parentEl);
                }
            } else {
                if(range.select) range.select();
                editor.execCommand("unLink", null);
            }
        }
    });

 });

KISSY.Editor.add("plugins~resize", function(E) {

    var Y = YAHOO.util, Event = Y.Event,
        UA = YAHOO.env.ua,
        TYPE = E.PLUGIN_TYPE,

        TMPL = '<span class="ks-editor-resize-larger" title="{larger_title}">{larger_text}</span>'
             + '<span class="ks-editor-resize-smaller" title="{smaller_title}">{smaller_text}</span>';


    E.addPlugin("resize", {

        /**
         * 种类:状?栏插件
         */
        type: TYPE.STATUSBAR_ITEM,

        contentEl: null,

        currentHeight: 0,

        /**
         * 初始?
         */
        init: function() {
            this.contentEl = this.editor.container.childNodes[1];
            this.currentHeight = parseInt(this.contentEl.style.height);

            this.renderUI();
            this.bindUI();
        },

        renderUI: function() {
            var lang = this.lang;

            this.domEl.innerHTML = TMPL.replace(/\{([^}]+)\}/g, function(match, key) {
                            return lang[key] ? lang[key] : key;
                        });
        },

        bindUI: function() {
            var spans = this.domEl.getElementsByTagName("span"),
                largerEl = spans[0],
                smallerEl = spans[1],
                contentEl = this.contentEl;

            Event.on(largerEl, "click", function() {
                this.currentHeight += 100;
                this._doResize();
            }, this, true);

            Event.on(smallerEl, "click", function() {

                // 不能小于 0
                if (this.currentHeight < 100) {
                    this.currentHeight = 0;
                } else {
                    this.currentHeight -= 100;
                }

                this._doResize();
            }, this, true);
        },

        _doResize: function() {
            this.contentEl.style.height = this.currentHeight + "px";

            // 本来通过设置 textarea ? height: 100% 自动就?应高度?
            // ? ie7- ? css 方案有问?,因此干脆用下面这? js 搞定
            this.editor.textarea.style.height = this.currentHeight + "px";
        }

    });

 });

KISSY.Editor.add("plugins~save", function(E) {

    var Y = YAHOO.util, Event = Y.Event,
        TYPE = E.PLUGIN_TYPE,

        TAG_MAP = {
            b: { tag: "strong" },
            i: { tag: "em" },
            u: { tag: "span", style: "text-decoration:underline" },
            strike: { tag: "span", style: "text-decoration:line-through" }
        };


    E.addPlugin("save", {
        /**
         * 种类
         */
        type: TYPE.FUNC,

        /**
         * 初始?
         */
        init: function() {
            var editor = this.editor,
                textarea = editor.textarea,
                form = textarea.form;

            if(form) {
                Event.on(form, "submit", function() {
                    if(!editor.sourceMode) {
                        //var val = editor.getData();
                        // 统一样式  由后台控?
						//                        if(val && val.indexOf('<div class="ks-editor-post">') !== 0) {
						//                            val = '<div class="ks-editor-post">' + val + '</div>';
						//                        }
                        textarea.value = editor.getData();
                    }
                });
            }
        },
		
		getEditor: function(){
			return this.editor;
		
		},
        /**
         * 过滤数据
         */
        filterData: function(data) {
			

            data = data.replace(/<(\/?)([^>\s]+)([^>]*)>/g, function(m, slash, tag, attr) {

                // ? ie 的大写标签转换为小写
                tag = tag.toLowerCase();

                // 让标签语义化
                var map = TAG_MAP[tag],
                    ret = tag;

                // 仅针? <tag> 这种不含属?的标签做进?步处?
                if(map && !attr) {
                    ret = map["tag"];
                    if(!slash && map["style"]) {
                        ret += ' style="' + map["style"] + '"';
                    }
                }

                return "<" + slash + ret + attr + ">";
            });

			return data;
            // ?:
            //  1. ? data 很大?,上面? replace 可能会有性能问题?
            //    (更新:已经将多? replace 合并成了??,正常情况?,不会有?能问题)
            //
            //  2. 尽量语义?,google 的实?,但未必对
            // TODO: 进一步优?,比如 <span style="..."><span style="..."> 两个span可以合并为一?

            // FCKEditor 实现了部分语义化
            // Google Docs 采用是实用主?
            // KISSY Editor 的原则是:在保证实用的基础?,尽量语义?
        }
    });
 });

KISSY.Editor.add("plugins~source", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom,
        UA = YAHOO.env.ua,
        TYPE = E.PLUGIN_TYPE,

        TOOLBAR_BUTTON_SELECTED = "ks-editor-toolbar-button-selected",
        SRC_MODE_CLS = "ks-editor-src-mode";

    /**
     * 查看源代码插?
     */
    E.addPlugin("source", {
        /**
         * 种类:普?按?
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 初始化函?
         */
        init: function() {
            var editor = this.editor;

            this.iframe = editor.contentWin.frameElement;
            this.textarea = editor.textarea;

            // ? textarea 放入 iframe 下面
            this.iframe.parentNode.appendChild(editor.textarea);

            // 添加 class
            Dom.addClass(this.domEl, "ks-editor-toolbar-source-button");
        },

        /**
         * 响应函数
         */
        exec: function() {
            var editor = this.editor,
                srcOn = editor.sourceMode;

            // 同步数据
            if(srcOn) {
                editor.contentDoc.body.innerHTML = this.textarea.value;
            } else {
                this.textarea.value = editor.getContentDocData();
            }

            // [bug fix] ie7-?,切换到源码时,iframe 的光标还可见,?隐藏?
            if(UA.ie && UA.ie < 8) {
                editor.contentDoc.selection.empty();
            }

            // 切换显示
            this.textarea.style.display = srcOn ? "none" : "";
            this.iframe.style.display = srcOn ? "" : "none";

            // 更新状??
            editor.sourceMode = !srcOn;

            // 更新按钮状??
            this._updateButtonState();
        },

        /**
         * 更新按钮状??
         */
        _updateButtonState: function() {
            var editor = this.editor,
                srcOn = editor.sourceMode;

            if(srcOn) {
                Dom.addClass(editor.container, SRC_MODE_CLS);
                Dom.addClass(this.domEl, TOOLBAR_BUTTON_SELECTED);
            } else {
                Dom.removeClass(editor.container, SRC_MODE_CLS);
                Dom.removeClass(this.domEl, TOOLBAR_BUTTON_SELECTED);
            }
        }

    });

 });

KISSY.Editor.add("plugins~undo", function(E) {

    var TYPE = E.PLUGIN_TYPE,UA = YAHOO.env.ua;

    E.addPlugin(["undo", "redo"], {
        /**
         * 种类:普?按?
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 响应函数
         */
        exec: function() {
				//undoDoc._undoQueueIndex -- ;
				//editor.applyUndoStep(undoDoc);
            // TODO 接管
			if(UA.ie === 6){
				if(this.editor.contentDoc.body._undoQueueIndex > 0 ){
					this.editor.contentDoc.body._undoQueueIndex -- ;
					this.editor.applyUndoStep(this.editor.contentDoc.body);
				}
			}else{
				this.editor.execCommand(this.name);
			}
        }
    });

 });

KISSY.Editor.add("plugins~wordcount", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Lang = YAHOO.lang,
        TYPE = E.PLUGIN_TYPE,
        ALARM_CLS = "ks-editor-wordcount-alarm",

        defaultConfig = {
            total       : 50000,
            threshold   : 100
        };

    E.addPlugin("wordcount", {

        /**
         * 种类:状?栏插件
         */
        type: TYPE.STATUSBAR_ITEM,

        total: Infinity,

        remain: Infinity,

        threshold: 0,

        remainEl: null,

        /**
         * 初始?
         */
        init: function() {
            var config = Lang.merge(defaultConfig, this.editor.config.pluginsConfig[this.name] || {});
            this.total = config["total"];
            this.threshold = config["threshold"];

            this.renderUI();
            this.bindUI();

            // 确保更新字数在内容加载完成后
            var self = this;
            setTimeout(function() {
                self.syncUI();
            }, 50);
        },

        renderUI: function() {
            this.domEl.innerHTML = this.lang["tmpl"]
                    .replace("%remain%", "<em>" + this.total + "</em>");

            this.remainEl = this.domEl.getElementsByTagName("em")[0];
        },

        bindUI: function() {
            var editor = this.editor;

            Event.on(editor.textarea, "keyup", this.syncUI, this, true);

            Event.on(editor.contentDoc, "keyup", this.syncUI, this, true);

            // TODO: 插入链接/表情等有问题
            Event.on(editor.container, "click", this.syncUI, this, true);
        },

        syncUI: function() {
            //this.remain = this.total - this.editor.getData().length;
			this.remain = this.editor.getData().length;
            this.remainEl.innerHTML = this.remain;

            if(this.remain <= this.threshold) {
                Dom.addClass(this.domEl, ALARM_CLS);
            } else {
                Dom.removeClass(this.domEl, ALARM_CLS);
            }
        }
    });

 });

KISSY.Editor.add("plugins~title1", function(E) {


    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Lang = YAHOO.lang,
        UA = YAHOO.env.ua, isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE, Range = E.Range;

    E.addPlugin("title1", {
        /**
         * 种类
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联的表?
         */
        form: null,

        /**
         * 关联 range 对象
         */
        range: null,

        /**
         * 初始化函?
         */
        init: function() {
            this._bindUI();
        },

        /**
         * 绑定事件
         */
        _bindUI: function() {
            var form = this.form, self = this,a;
           
            // 点击按钮的事?
            Event.on(this.domEl, "click", function() {
	            self.range = E.Range.saveRange(self.editor);
				self._createTitle();
            });
        },

        /**
         * 创建/修改大标?
         */
        _createTitle: function() {
            var editor = this.editor,
            range = this.range,
                //selectedText = Range.getSelectedText(range),
                //container = range.getCommonAncestor(range),
            parentEl;
			//salert(container);
			//alert(range);
			
            var div = editor.contentDoc.createElement("div"),
            a, container, fragment;

			// 创建链接
			a = editor.contentDoc.createElement("div");
			a.setAttribute("class", "hdwiki_tmml");
            
            if (isIE) {
                if (range.select) range.select();
                
                if("text" in range) { // TextRange
                    a.innerHTML = range.htmlText || href;
                    div.innerHTML = "";
                    div.appendChild(a);
                    range.pasteHTML(div.innerHTML);
                } else { // ControlRange
                    // TODO: ControlRange 链接?? target 实现
                    this.editor.execCommand("createLink", href);
                }

            } else { // W3C
                if(range.collapsed) {
                    a.innerHTML = href;
                } else {
                    fragment = range.cloneContents();
                    while(fragment.firstChild) {
                        a.appendChild(fragment.firstChild);
                    }
                }
                a.innerHTML += "<br>";
                range.deleteContents(); // 删除原内?
                range.insertNode(a); // 插入标题
            }

            this.editor.contentWin.focus();
        },

        /**
         * 移除大标?
         */
        _unTitle: function() {
            var editor = this.editor,
                range = this.range,
                selectedText = Range.getSelectedText(range),
                container = Range.getCommonAncestor(range),
                parentEl;
			//判断选中区域父元素是否是hi标题
        }
    });
  });
  
  
  
KISSY.Editor.add("plugins~code", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Connect = Y.Connect, Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE,

        DIALOG_CLS = "ks-editor-image",
        BTN_OK_CLS = "ks-editor-btn-ok",
        BTN_CANCEL_CLS = "ks-editor-btn-cancel",
        TAB_CLS = "ks-editor-image-tabs",
        TAB_CONTENT_CLS = "ks-editor-image-tab-content",
        UPLOADING_CLS = "ks-editor-image-uploading",
        ACTIONS_CLS = "ks-editor-dialog-actions",
        NO_TAB_CLS = "ks-editor-image-no-tab",
        SELECTED_TAB_CLS = "ks-editor-image-tab-selected",

        TABS_TMPL = {link: '<li rel="link">{tab_link}</li>'},

        DIALOG_TMPL = ['<form action="javascript: void(0)">',
                          '<ul class="', TAB_CLS ,' ks-clearfix">',
                          '</ul>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="local" style="display: none">',
                              '<label>{label_local}</label>',
                              '<input type="file" size="40" name="imgFile" unselectable="on" />',
                              '{local_extraCode}',
                          '</div>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="link">',
							  '<label>{label_link_selected}</label>',
                              '<select name="codeSelect">\
							  	<option value="java">java</option>\
								<option value="c">C</option>		\
								<option value="perl">perl</option> \
							  	<option value="php">php</option>\
								<option value="bash">bash</option>\
								<option value="cpp">C++</option>		\
								<option value="vb">vb</option>		\
								<option value="ruby">ruby</option>		\
								<option value="ruby">python</option>		\
								<option value="html4strict">html</option>		\
								<option value="javascript">javascript</option>		\
								<option value="jquery">jquery</option>		\
								<option value="latex">latex</option>		\
								<option value="lisp">lisp</option> \
								<option value="mxml">mxml</option> \
								<option value="xml">xml</option> \
								<option value="sql">sql</option> \
								<option value="mysql">mysql</option> \
								<option value="oracle11">oracle11</option> \
								<option value="oracle8">oracle8</option> \
								<option value="gdb">gdb</option>		\
							  </select>',
                              '<label>{label_link}</label>',
                              '<textarea id="editer_codeContent" cols="" rows="10" class="text" style="width:330px;overflow:auto;" name="codeValue"></textarea>',
                          '</div>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="album" style="display: none">',
                              '<label>{label_album}</label>',
                              '<p style="width: 300px">尚未实现...</p>', // TODO: 从相册中选择图片
                          '</div>',
                          '<div class="', UPLOADING_CLS, '" style="display: none">',
                              '<p style="width: 300px">{uploading}</p>',
                          '</div>',
                          '<div class="', ACTIONS_CLS ,'">',
                              '<button name="ok" class="', BTN_OK_CLS, '">{ok}</button>',
                              '<span class="', BTN_CANCEL_CLS ,'">{cancel}</span>',
                          '</div>',
                      '</form>'].join(""),

        defaultConfig = {
            tabs: ["link"],
            upload: {
                actionUrl: E.config.upload_url+'&act=kissy&type=local',
                filter: "png|gif|jpg|jpeg|bmp",
                filterMsg: "", // 默认? this.lang.upload_filter
                enableXdr: false,
                connectionSwf: "http://a.tbcdn.cn/yui/2.8.0r4/build/connection/connection.swf",
                formatResponse: function(data) {
                    var ret = [];
                    for (var key in data) ret.push(data[key]);
                    return ret;
                },
                extraCode: ""
            }
        };

    E.addPlugin("code", {
        /**
         * 种类:按钮
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 配置?
         */
        config: {},

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联的表?
         */
        form: null,

        /**
         * 关联? range 对象
         */
        range: null,

        currentTab: null,
        currentPanel: null,
        uploadingPanel: null,
        actionsBar: null,

        /**
         * 初始化函?
         */
        init: function() {
            var pluginConfig = this.editor.config.pluginsConfig[this.name] || {};
            defaultConfig.upload.filterMsg = this.lang["upload_filter"];
            this.config = Lang.merge(defaultConfig, pluginConfig);
            this.config.upload = Lang.merge(defaultConfig.upload, pluginConfig.upload || {});

            this._renderUI();
            this._bindUI();

            this.actionsBar = Dom.getElementsByClassName(ACTIONS_CLS, "div", this.dialog)[0];
            this.uploadingPanel = Dom.getElementsByClassName(UPLOADING_CLS, "div", this.dialog)[0];
            this.config.upload.enableXdr && this._initXdrUpload();
        },

        /**
         * 初始化对话框界面
         */
        _renderUI: function() {
            var dialog = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]),
                lang = this.lang;
            // 添加自定义项
            lang["local_extraCode"] = this.config.upload.extraCode;
            dialog.className += " " + DIALOG_CLS;
            dialog.innerHTML = DIALOG_TMPL.replace(/\{([^}]+)\}/g, function(match, key) {
				
                return (key in lang) ? lang[key] : key;
            });

            this.dialog = dialog;
            this.form = dialog.getElementsByTagName("form")[0];
            //if(isIE) E.Dom.setItemUnselectable(dialog);

            this._renderTabs();
        },
		
    _renderTabs: function() {
            var lang = this.lang, self = this,
                ul = Dom.getElementsByClassName(TAB_CLS, "ul", this.dialog)[0],
                panels = Dom.getElementsByClassName(TAB_CONTENT_CLS, "div", this.dialog);

            // 根据配置添加 tabs
            var keys = this.config["tabs"], html = "";
            for(var k = 0, l = keys.length; k < l; k++) {
                html += TABS_TMPL[keys[k]];
            }

            // 文案
            ul.innerHTML = html.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            // 只有?? tabs 时不显示
            var tabs = ul.childNodes, len = panels.length;
            if(tabs.length === 1) {
                Dom.addClass(this.dialog, NO_TAB_CLS);
            }

            // 切换
            switchTab(tabs[0]); // 默认选中第一个Tab


            function switchTab(trigger) {
                var j = 0, rel = trigger.getAttribute("rel");
                for (var i = 0; i < len; i++) {
                    if(tabs[i]) Dom.removeClass(tabs[i], SELECTED_TAB_CLS);
                    panels[i].style.display = "none";

                    if (panels[i].getAttribute("rel") == rel) {
                        j = i;
                    }
                }

                // ie6 ?,?更新 iframe shim
                if(UA.ie === 6) E.Menu._updateShimRegion(self.dialog);

                Dom.addClass(trigger, SELECTED_TAB_CLS);
                panels[j].style.display = "";

                self.currentTab = trigger.getAttribute("rel");
                self.currentPanel = panels[j];
            }
        },
        /**
         * 绑定事件
         */
        _bindUI: function() {
            var self = this;

            // 显示/隐藏对话框时的事?
            Event.on(this.domEl, "click", function() {
                // 仅在显示时更?
                if (self.dialog.style.visibility === isIE ? "hidden" : "visible") { // 事件的触发顺序不?
                    self._syncUI();
                }
            });

            // 注册表单按钮点击事件
            Event.on(this.dialog, "click", function(ev) {
                var target = Event.getTarget(ev),
                    currentTab = self.currentTab;

                switch(target.className) {
                    case BTN_OK_CLS:
                            self._insertWebImage();
                        break;
                    case BTN_CANCEL_CLS: // 直接?上冒?,关闭对话?
                        break;
                    default: // 点击在非按钮?,停止冒泡,保留对话?
                        Event.stopPropagation(ev);
                }
            });
        },

        /**
         * 初始化跨域上?
         */
        _initXdrUpload: function() {
            var tabs = this.config["tabs"];

            for(var i = 0, len = tabs.length; i < len; i++) {
                if(tabs[i] === "local") { // 有上? tab 时才进行以下操作
                    Connect.transport(this.config.upload.connectionSwf);
                    //Connect.xdrReadyEvent.subscribe(function(){ alert("xdr ready"); });
                    break;
                }
            }
        },

 

        _onUploadError: function(msg) {
            alert(this.lang["upload_error"] + "\n\n" + msg);
            this._hideDialog();

            // 测试了以下错误类?:
            //   - json parse 异常,包括 actionUrl 不存在?未登录、跨域等各种因素
            //   - 服务器端返回错误信息 ["1", "error msg"]
        },

        _insertWebImage: function() {
            var code = this.form["codeValue"].value,clang = this.form["codeSelect"].value;
            code && this._insertImage(code,'',clang);
        },

        /**
         * 隐藏对话?
         */
        _hideDialog: function() {
            var activeDropMenu = this.editor.activeDropMenu;
            if(activeDropMenu && Dom.isAncestor(activeDropMenu, this.dialog)) {
                E.Menu.hideActiveDropMenu(this.editor);
            }

            // 还原焦点
            this.editor.contentWin.focus();
        },

        /**
         * 更新界面上的表单?
         */
        _syncUI: function() {
            // 保存 range, 以便还原
            this.range = E.Range.saveRange(this.editor);

            // reset
            this.form.reset();

            // restore
            this.uploadingPanel.style.display = "none";
            this.currentPanel.style.display = "";
            this.actionsBar.style.display = "";
        },

        /**
         * 插入图片
         */
        _insertImage: function(url, alt,clang) {
            url = Lang.trim(url);
            // url 为空?,不处?
            if (url.length === 0) {
                return;
            }

            var editor = this.editor,
                range = this.range;
            // 插入图片
            if (window.getSelection) { // W3C
              	var codeContent = editor.contentDoc.createElement("div");

				var code = '[code lang="'+clang+'" line="1"]'+url+'[/code]';
				codeContent.innerHTML += code;
                range.deleteContents(); // 清空选中内容
                range.insertNode(codeContent); // 插入图片

                // 使得连续插入图片?,添加在后?
                if(UA.webkit) {
                    var selection = editor.contentWin.getSelection();
                    selection.addRange(range);
                    selection.collapseToEnd();
                } else {
                    range.setStartAfter(codeContent);
                }

                editor.contentWin.focus(); // 显示光标

            } else if(document.selection) { // IE
                // 还原焦点
                editor.contentWin.focus();

                if("text" in range) { // TextRange
                    range.select(); // 还原选区
                    var codeContent = document.createElement("div");
                    var html = '[code lang="'+clang+'" line="1"]' + url+'[/code]';
					html = html.replace(/[\n\r\t]/g,'<br />');
					codeContent.innerHTML += html;
                    range.pasteHTML(codeContent.innerHTML);
                } else { // ControlRange
                    range.execCommand("insertImage", false, url);
                }
            }
        }
    });

  });  
  
KISSY.Editor.add("plugins~video", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Connect = Y.Connect, Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE,

        DIALOG_CLS = "ks-editor-image",
        BTN_OK_CLS = "ks-editor-btn-ok",
        BTN_CANCEL_CLS = "ks-editor-btn-cancel",
        TAB_CLS = "ks-editor-image-tabs",
        TAB_CONTENT_CLS = "ks-editor-image-tab-content",
        UPLOADING_CLS = "ks-editor-image-uploading",
        ACTIONS_CLS = "ks-editor-dialog-actions",
        NO_TAB_CLS = "ks-editor-image-no-tab",
        SELECTED_TAB_CLS = "ks-editor-image-tab-selected",

        TABS_TMPL = {link: '<li rel="link">{tab_link}</li>'},

        DIALOG_TMPL = ['<form action="javascript: void(0)">',
                          '<ul class="', TAB_CLS ,' ks-clearfix">',
                          '</ul>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="local" style="display: none">',
                              '<label>{label_local}</label>',
                              '<input type="file" size="40" name="imgFile" unselectable="on" />',
                              '{local_extraCode}',
                          '</div>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="link">',
                              '<label>{label_link}</label>',
                              '<input name="imgUrl" size="50" />',
							   '<label>{label_link_width}</label>',
                              '<input name="imgwidth" size="10" value="400"/>',
							  '<label>{label_link_height}</label>',
                              '<input name="imgheight" size="10" value="300"/>',
                          '</div>',
                          '<div class="', TAB_CONTENT_CLS, '" rel="album" style="display: none">',
                              '<label>{label_album}</label>',
                              '<p style="width: 300px">尚未实现...</p>', // TODO: 从相册中选择图片
                          '</div>',
                          '<div class="', UPLOADING_CLS, '" style="display: none">',
                              '<p style="width: 300px">{uploading}</p>',
                          '</div>',
                          '<div class="', ACTIONS_CLS ,'">',
                              '<button name="ok" class="', BTN_OK_CLS, '">{ok}</button>',
                              '<span class="', BTN_CANCEL_CLS ,'">{cancel}</span>',
                          '</div>',
                      '</form>'].join(""),

        defaultConfig = {
            tabs: ["link"],
            upload: {
                actionUrl: E.config.upload_url+'&act=kissy&type=local',
                filter: "png|gif|jpg|jpeg|bmp",
                filterMsg: "", // 默认? this.lang.upload_filter
                enableXdr: false,
                connectionSwf: "http://a.tbcdn.cn/yui/2.8.0r4/build/connection/connection.swf",
                formatResponse: function(data) {
                    var ret = [];
                    for (var key in data) ret.push(data[key]);
                    return ret;
                },
                extraCode: ""
            }
        };

    E.addPlugin("video", {
        /**
         * 种类:按钮
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 配置?
         */
        config: {},

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联的表?
         */
        form: null,

        /**
         * 关联? range 对象
         */
        range: null,

        currentTab: null,
        currentPanel: null,
        uploadingPanel: null,
        actionsBar: null,

        /**
         * 初始化函?
         */
        init: function() {
            var pluginConfig = this.editor.config.pluginsConfig[this.name] || {};
            defaultConfig.upload.filterMsg = this.lang["upload_filter"];
            this.config = Lang.merge(defaultConfig, pluginConfig);
            this.config.upload = Lang.merge(defaultConfig.upload, pluginConfig.upload || {});

            this._renderUI();
            this._bindUI();

            this.actionsBar = Dom.getElementsByClassName(ACTIONS_CLS, "div", this.dialog)[0];
            this.uploadingPanel = Dom.getElementsByClassName(UPLOADING_CLS, "div", this.dialog)[0];
            this.config.upload.enableXdr && this._initXdrUpload();
        },

        /**
         * 初始化对话框界面
         */
        _renderUI: function() {
            var dialog = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]),
                lang = this.lang;

            // 添加自定义项
            lang["local_extraCode"] = this.config.upload.extraCode;

            dialog.className += " " + DIALOG_CLS;
            dialog.innerHTML = DIALOG_TMPL.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            this.dialog = dialog;
            this.form = dialog.getElementsByTagName("form")[0];
            if(isIE) E.Dom.setItemUnselectable(dialog);

            this._renderTabs();
        },

        _renderTabs: function() {
            var lang = this.lang, self = this,
                ul = Dom.getElementsByClassName(TAB_CLS, "ul", this.dialog)[0],
                panels = Dom.getElementsByClassName(TAB_CONTENT_CLS, "div", this.dialog);

            // 根据配置添加 tabs
            var keys = this.config["tabs"], html = "";
            for(var k = 0, l = keys.length; k < l; k++) {
                html += TABS_TMPL[keys[k]];
            }

            // 文案
            ul.innerHTML = html.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            // 只有?? tabs 时不显示
            var tabs = ul.childNodes, len = panels.length;
            if(tabs.length === 1) {
                Dom.addClass(this.dialog, NO_TAB_CLS);
            }

            // 切换
            switchTab(tabs[0]); // 默认选中第一个Tab

            function switchTab(trigger) {
                var j = 0, rel = trigger.getAttribute("rel");
                for (var i = 0; i < len; i++) {
                    if(tabs[i]) Dom.removeClass(tabs[i], SELECTED_TAB_CLS);
                    panels[i].style.display = "none";

                    if (panels[i].getAttribute("rel") == rel) {
                        j = i;
                    }
                }

                // ie6 ?,?更新 iframe shim
                if(UA.ie === 6) E.Menu._updateShimRegion(self.dialog);

                Dom.addClass(trigger, SELECTED_TAB_CLS);
                panels[j].style.display = "";

                self.currentTab = trigger.getAttribute("rel");
                self.currentPanel = panels[j];
            }
        },

        /**
         * 绑定事件
         */
        _bindUI: function() {
            var self = this;

            // 显示/隐藏对话框时的事?
            Event.on(this.domEl, "click", function() {
                // 仅在显示时更?
                if (self.dialog.style.visibility === isIE ? "hidden" : "visible") { // 事件的触发顺序不?
                    self._syncUI();
                }
            });

            // 注册表单按钮点击事件
            Event.on(this.dialog, "click", function(ev) {
                var target = Event.getTarget(ev),
                    currentTab = self.currentTab;

                switch(target.className) {
                    case BTN_OK_CLS:
                            self._insertWebImage();
                        break;
                    case BTN_CANCEL_CLS: // 直接?上冒?,关闭对话?
                        break;
                    default: // 点击在非按钮?,停止冒泡,保留对话?
                        Event.stopPropagation(ev);
                }
            });
        },

        /**
         * 初始化跨域上?
         */
        _initXdrUpload: function() {
            var tabs = this.config["tabs"];

            for(var i = 0, len = tabs.length; i < len; i++) {
                if(tabs[i] === "local") { // 有上? tab 时才进行以下操作
                    Connect.transport(this.config.upload.connectionSwf);
                    //Connect.xdrReadyEvent.subscribe(function(){ alert("xdr ready"); });
                    break;
                }
            }
        },

 

        _onUploadError: function(msg) {
            alert(this.lang["upload_error"] + "\n\n" + msg);
            this._hideDialog();

            // 测试了以下错误类?:
            //   - json parse 异常,包括 actionUrl 不存在?未登录、跨域等各种因素
            //   - 服务器端返回错误信息 ["1", "error msg"]
        },

        _insertWebImage: function() {
            var imgUrl = this.form["imgUrl"].value,width = this.form["imgwidth"].value || 100+"%",height = this.form['imgheight'].value || 100+"%";
            imgUrl && this._insertImage(imgUrl,'',width,height);
        },

        /**
         * 隐藏对话?
         */
        _hideDialog: function() {
            var activeDropMenu = this.editor.activeDropMenu;
            if(activeDropMenu && Dom.isAncestor(activeDropMenu, this.dialog)) {
                E.Menu.hideActiveDropMenu(this.editor);
            }

            // 还原焦点
            this.editor.contentWin.focus();
        },

        /**
         * 更新界面上的表单?
         */
        _syncUI: function() {
            // 保存 range, 以便还原
            this.range = E.Range.saveRange(this.editor);

            // reset
            this.form.reset();

            // restore
            this.uploadingPanel.style.display = "none";
            this.currentPanel.style.display = "";
            this.actionsBar.style.display = "";
        },

        /**
         * 插入图片
         */
        _insertImage: function(url, alt,width,height) {
            url = Lang.trim(url);
            // url 为空?,不处?
            if (url.length === 0) {
                return;
            }

            var editor = this.editor,
                range = this.range;

            // 插入图片
            if (window.getSelection) { // W3C
                var video = editor.contentDoc.createElement("embed");
                video.src = url;
				video.setAttribute('autostart',true);
				video.setAttribute('width',width);
				video.setAttribute('height',height);
				
                if(alt) video.setAttribute("alt", alt);

                range.deleteContents(); // 清空选中内容
                range.insertNode(video); // 插入图片

                // 使得连续插入图片?,添加在后?
                if(UA.webkit) {
                    var selection = editor.contentWin.getSelection();
                    selection.addRange(range);
                    selection.collapseToEnd();
                } else {
                    range.setStartAfter(video);
                }

                editor.contentWin.focus(); // 显示光标

            } else if(document.selection) { // IE
                // 还原焦点
                editor.contentWin.focus();

                if("text" in range) { // TextRange
                    range.select(); // 还原选区

                    var html = '<embed src="' + url + '" width="'+width+'"'+ '" height="'+height+'"'+ '" autostart="true"';
                    html += '></embed>';
                    range.pasteHTML(html);
                } else { // ControlRange
                    range.execCommand("insertImage", false, url);
                }
            }
        }
    });

 });

  

  
 
KISSY.Editor.add("plugins~table", function(E) {

    var Y = YAHOO.util, Dom = Y.Dom, Event = Y.Event, Connect = Y.Connect, Lang = YAHOO.lang,
        UA = YAHOO.env.ua,
        isIE = UA.ie,
        TYPE = E.PLUGIN_TYPE,

        DIALOG_CLS = "ks-editor-image",
        BTN_OK_CLS = "ks-editor-btn-ok",
        BTN_CANCEL_CLS = "ks-editor-btn-cancel",
        TAB_CLS = "ks-editor-image-tabs",
        TAB_CONTENT_CLS = "ks-editor-image-tab-content",
        UPLOADING_CLS = "ks-editor-image-uploading",
        ACTIONS_CLS = "ks-editor-dialog-actions",
        NO_TAB_CLS = "ks-editor-image-no-tab",
        SELECTED_TAB_CLS = "ks-editor-image-tab-selected",

        TABS_TMPL = {admin:'<li rel="admin">{label_admin}</li>'},
		//	text          : "插入表格",
		//	tab_link      : "插入表格参数 ",
		//	cols          : "?",
		///	rows		  : "?",
		//	label_link    : "请输入代码内?:",
		//	title         : "将代码高?",
		//	ok            : "插入"
  		DIALOG_TMPL = ['<form action="javascript: void(0)">',
                          '<ul class="', TAB_CLS ,' ks-clearfix">',
                          '</ul>',
						   '<div class="', TAB_CONTENT_CLS, '" rel="admin" style="width:240px;">',
							    '<div style="float:right;width:100px;text-aline:left;">',
								  '<p><a class="add_left_cols" href="javascript:void(0)">{add_left_cols}</a></p>', 
								  '<p><a class="add_right_cols" href="javascript:void(0)">{add_right_cols}</a></p>', 
								  '<p><a class="add_top_rows" href="javascript:void(0)">{add_top_rows}</a></p>', 
								  '<p><a class="add_button_rows" href="javascript:void(0)">{add_button_rows}</a></p>', 
								  '<p><a class="delete_cols" href="javascript:void(0)">{delete_cols}</a></p>', 
								  '<p><a class="delete_rows" href="javascript:void(0)">{delete_rows}</a></p>', 
								  '<p><a class="delete_table" href="javascript:void(0)">{delete_table}</a></p>', 
								'</div>',
							  '<label>{rows}</label>',
                              '<input name="cols" size="10" value="3"/>',
							  '<label>{cols}</label>',
                              '<input name="rows" size="10" value="3"/>',
                          '</div>',
                          '<div class="', ACTIONS_CLS ,'">',
                              '<button name="ok" class="', BTN_OK_CLS, '">{ok}</button>',
                              '<span class="', BTN_CANCEL_CLS ,'">{cancel}</span>',
                          '</div>',
                      '</form>'].join(""),
                      defaultConfig = {
                          tabs: ["admin"],
                          upload: {
                              actionUrl: E.config.upload_url + '&act=kissy&type=local',
                              filter: "png|gif|jpg|jpeg|bmp",
                              filterMsg: "", // 默认? this.lang.upload_filter
                              enableXdr: false,
                              connectionSwf: "http://a.tbcdn.cn/yui/2.8.0r4/build/connection/connection.swf",
                              formatResponse: function(data){
                                  var ret = [];
                                  for (var key in data) 
                                      ret.push(data[key]);
                                  return ret;
                              },
                              extraCode: ""
                          }
                      };

    E.addPlugin("table", {
        /**
         * 种类:按钮
         */
        type: TYPE.TOOLBAR_BUTTON,

        /**
         * 配置?
         */
        config: {},

        /**
         * 关联的对话框
         */
        dialog: null,

        /**
         * 关联的表?
         */
        form: null,

        /**
         * 关联? range 对象
         */
        range: null,

        currentTab: null,
        currentPanel: null,
        uploadingPanel: null,
        actionsBar: null,

        /**
         * 初始化函?
         */
        init: function() {
   			var pluginConfig = this.editor.config.pluginsConfig[this.name] || {};
            defaultConfig.upload.filterMsg = this.lang["upload_filter"];
            this.config = Lang.merge(defaultConfig, pluginConfig);
            this.config.upload = Lang.merge(defaultConfig.upload, pluginConfig.upload || {});

            this._renderUI();
            this._bindUI();

            this.actionsBar = Dom.getElementsByClassName(ACTIONS_CLS, "div", this.dialog)[0];
           //this.uploadingPanel = Dom.getElementsByClassName(UPLOADING_CLS, "div", this.dialog)[0];
            this.config.upload.enableXdr && this._initXdrUpload();
        },

       /**
         * 初始化对话框界面
         */
        _renderUI: function() {
            var dialog = E.Menu.generateDropMenu(this.editor, this.domEl, [1, 0]),
                lang = this.lang;

            // 添加自定义项
            lang["local_extraCode"] = this.config.upload.extraCode;

            dialog.className += " " + DIALOG_CLS;
            dialog.innerHTML = DIALOG_TMPL.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            this.dialog = dialog;
            this.form = dialog.getElementsByTagName("form")[0];
            if(isIE) E.Dom.setItemUnselectable(dialog);

            this._renderTabs();
        },

        _renderTabs: function() {
            var lang = this.lang, self = this,
                ul = Dom.getElementsByClassName(TAB_CLS, "ul", this.dialog)[0],
                panels = Dom.getElementsByClassName(TAB_CONTENT_CLS, "div", this.dialog);
            // 根据配置添加 tabs
            var keys = this.config["tabs"], html = "";
            for(var k = 0, l = keys.length; k < l; k++) {
                html += TABS_TMPL[keys[k]];
            }

            // 文案
            ul.innerHTML = html.replace(/\{([^}]+)\}/g, function(match, key) {
                return (key in lang) ? lang[key] : key;
            });

            // 只有?? tabs 时不显示
            var tabs = ul.childNodes, len = panels.length;
            if(tabs.length === 1) {
                Dom.addClass(this.dialog, NO_TAB_CLS);
            }

            // 切换
            this.switchTab(tabs[0]); // 默认选中第一个Tab
            Event.on(tabs, "click", function() {
                self.switchTab(this);
            });

           
        },
		switchTab:function(trigger) {
            var lang = this.lang, self = this,
                ul = Dom.getElementsByClassName(TAB_CLS, "ul", this.dialog)[0],
                panels = Dom.getElementsByClassName(TAB_CONTENT_CLS, "div", this.dialog);
				var tabs = ul.childNodes, len = panels.length;
				
                var j = 0, rel = trigger.getAttribute("rel");
                for (var i = 0; i < len; i++) {
                    if(tabs[i]) Dom.removeClass(tabs[i], SELECTED_TAB_CLS);
                    panels[i].style.display = "none";

                    if (panels[i].getAttribute("rel") == rel) {
                        j = i;
                    }
                }

                // ie6 ?,?更新 iframe shim
                //if(UA.ie === 6) E.Menu._updateShimRegion(self.dialog);

                Dom.addClass(trigger, SELECTED_TAB_CLS);
                panels[j].style.display = "";

                self.currentTab = trigger.getAttribute("rel");
                self.currentPanel = panels[j];
            },
        /**
         * 绑定事件
         */
        _bindUI: function() {
            var self = this,
  			ul = Dom.getElementsByClassName(TAB_CLS, "ul", this.dialog)[0];
			var tabs = ul.childNodes;
            // 显示/隐藏对话框时的事?
            Event.on(this.domEl, "click", function() {		
                // 仅在显示时更?
                if (self.dialog.style.visibility === isIE ? "hidden" : "visible") { // 事件的触发顺序不?
					self._syncUI();
                }
            });

            // 注册表单按钮点击事件
            Event.on(this.dialog, "click", function(ev){
                var target = Event.getTarget(ev), currentTab = self.currentTab;

                
				switch (target.className) {
                    case BTN_OK_CLS:
						self.switchTab(tabs[0]);
                        self._insertTable();
                        break;
                    case BTN_CANCEL_CLS: // 直接?上冒?,关闭对话?
                    	self.switchTab(tabs[0]);
                        break;
                    case "add_left_cols": //左边添加??
                        self.insetTableCols('left');
                        break;
                    case "add_right_cols":
                        self.insetTableCols('right');
                        break;
                    case "add_top_rows":
                        self.insetTableCols('top');
                        break;
                    case "add_button_rows":
                        self.insetTableCols('button');
                        break;
					case "delete_cols":
                        self.deleteTable('cols');
                        break;
                    case "delete_rows":
                        self.deleteTable('rows');
                        break;
                    case "delete_table":
                        self.deleteTable('table');
                        break;	
                    default: // 点击在非按钮?,停止冒泡,保留对话?
                        Event.stopPropagation(ev);
                }
            });
        },
		
		deleteTable:function(opt){
			var range = this.editor.getSelectionRange();
        	var container = E.Range.getCommonAncestor(range);
			if(-[1,]){
				var tdIndex = container.parentNode.cellIndex;
				var trIndex = container.parentNode.parentNode.rowIndex;
			}else{
				var tdIndex = container.cellIndex;
				var trIndex = container.parentNode.rowIndex;
			}
			while(container.nodeName != "TABLE"){
					container = container.parentNode;
			}
			switch(opt){
				case "cols":
					var trChild = container.getElementsByTagName('TR');
					for(var i=0;i < trChild.length;i++){
						//TDContent.innerHTML = "&nbsp;";
						//alert(trChild[i]);
						trChild[i].deleteCell(tdIndex);
					}
				break;
				case "rows":
					container.deleteRow(trIndex);
				break;
				case "table":
					container.parentNode.removeChild(container);
			}
		},
		
		insetTableCols:function(opt){
			// 保存 range, 以便还原

       		var range = this.editor.getSelectionRange();
			var container = E.Range.getCommonAncestor(range);
			
			if(-[1,]){
				var tdIndex = container.parentNode.cellIndex;
				var trIndex = container.parentNode.parentNode.rowIndex;
			}else{
				var tdIndex = container.cellIndex;
				var trIndex = container.parentNode.rowIndex;
			}
        	
			var TRContent = this.editor.contentDoc.createElement("TR");
			while(container.parentNode.nodeName != "TABLE"){
				container = container.parentNode;
			}
			var trChild = container.getElementsByTagName('TR');
			var length = container.firstChild.getElementsByTagName('TD').length;
			switch(opt){
				case "left":
	                //container.appendChild(codeContent);
					for(var i=0;i < trChild.length;i++){
						//TDContent.innerHTML = "&nbsp;";
						//alert(trChild[i]);
						var td = trChild[i].insertCell(tdIndex);
						td.innerHTML = "&nbsp;"
					}
					break;
				case "right":
					for(var i=0;i < trChild.length;i++){
						var td = trChild[i].insertCell(tdIndex+1);
						td.innerHTML = "&nbsp;"
					}
					break;
				case "top":
					var tr = container.insertRow(trIndex);
					for(var i=0;i<length;i++){
						var TDContent = this.editor.contentDoc.createElement("TD");
						TDContent.innerHTML = "&nbsp;";
						tr.appendChild(TDContent);;
					}
					break;
				case "button":
					var tr = container.insertRow(trIndex+1);
					for(var i=0;i<length;i++){
						var TDContent = this.editor.contentDoc.createElement("TD");
						TDContent.innerHTML = "&nbsp;";
						tr.appendChild(TDContent);;
					}
					
					//container.appendChild(TRContent);
					break;	
			}
			//alert(container.parentNode.nodeName);
			//alert(container.parentNode.parentNode.nodeName);
			//alert(container.ownerDocument.nodeName);
        	//if(container.nodeName == 'DIV' && container.className == 'hdwiki_tmml'){
        	//	if (isIE) {
        	//		var p = document.createElement("p");
        	//		var test = container.parentNode.innerHTML;
        	//		alert(test);
//
    		//    }else{
           // 		range.setStartAfter(container);
    		//    }
        	//}else{
        	//	if(container.parentNode.nodeName == 'DIV' && container.parentNode.className == 'hdwiki_tmml'){
          ////      		range.setStartAfter(container);
        	//	}
        	//}


			//if (isIE) {
           //     if (range.select) range.select();
                
            //    if("text" in range) { // TextRange
          //          a.innerHTML = range.htmlText || href;
          //          div.innerHTML = "";
          //          div.appendChild(a);
         //           range.pasteHTML(div.innerHTML);
         //       }
         //   } else { // W3C
               // if(range.collapsed) {
               //     a.innerHTML = href;
              //  } else {
               //     fragment = range.cloneContents();
               //     while(fragment.firstChild) {
              //          a.appendChild(fragment.firstChild);
              //      }
              //  }
              //  a.innerHTML += "<br>";
              //  range.deleteContents(); // 删除原内?
             //   range.insertNode(a); // 插入标题
       //     }

            this.editor.contentWin.focus();
		},

        /**
         * 初始化跨域上?
         */
        _initXdrUpload: function() {
            var tabs = this.config["tabs"];

            for(var i = 0, len = tabs.length; i < len; i++) {
                if(tabs[i] === "local") { // 有上? tab 时才进行以下操作
                    Connect.transport(this.config.upload.connectionSwf);
                    //Connect.xdrReadyEvent.subscribe(function(){ alert("xdr ready"); });
                    break;
                }
            }
        },
        

 

        _onUploadError: function(msg) {
            alert(this.lang["upload_error"] + "\n\n" + msg);
            this._hideDialog();

            // 测试了以下错误类?:
            //   - json parse 异常,包括 actionUrl 不存在?未登录、跨域等各种因素
            //   - 服务器端返回错误信息 ["1", "error msg"]
        },

        _insertTable: function() {
            var cols = this.form["cols"].value,rows = this.form["rows"].value;
            rows && this._inserTableData(cols,rows);
        },

        /**
         * 隐藏对话?
         */
        _hideDialog: function() {
            var activeDropMenu = this.editor.activeDropMenu;
            if(activeDropMenu && Dom.isAncestor(activeDropMenu, this.dialog)) {
                E.Menu.hideActiveDropMenu(this.editor);
            }

            // 还原焦点
            this.editor.contentWin.focus();
        },

        /**
         * 更新界面上的表单?
         */
        _syncUI: function() {
            // 保存 range, 以便还原
            this.range = E.Range.saveRange(this.editor);

            // reset
            this.form.reset();

            // restore
            //this.uploadingPanel.style.display = "none";
            this.currentPanel.style.display = "";
            this.actionsBar.style.display = "";
        },

        /**
         * 插入图片
         */
        _inserTableData: function(cols,rows) {
            cols = Lang.trim(cols);
            // url 为空?,不处?
            if (cols.length === 0) {
                return;
            }

            var editor = this.editor,
                range = this.range;
			
			
			var html = '<table class="kissy-table" border="1">';
			for (var i = 0; i < cols; i++) {
				html += '<tr>';
				for (var j = 0; j < rows; j++) {
					html += '<td>&nbsp;</td>';
				}
				html += '</tr>';
			}
			html += '</table>';
			
            // 插入表格
            if (window.getSelection) { // W3C
              	var codeContent = editor.contentDoc.createElement("div");

				codeContent.innerHTML += html;
                range.deleteContents(); // 清空选中内容
                range.insertNode(codeContent); // 插入图片

                // 使得连续插入图片?,添加在后?
                if(UA.webkit) {
                    var selection = editor.contentWin.getSelection();
                    selection.addRange(range);
                    selection.collapseToEnd();
                } else {
                    range.setStartAfter(codeContent);
                }

                editor.contentWin.focus(); // 显示光标

            } else if(document.selection) { // IE
                // 还原焦点
                editor.contentWin.focus();

                if("text" in range) { // TextRange
                    range.select(); // 还原选区
                    var codeContent = document.createElement("div");
					html = html.replace(/[\n\r\t]/g,'<br />');
					codeContent.innerHTML += html;
                    range.pasteHTML(codeContent.innerHTML);
                }
            }
        }
    });

  });