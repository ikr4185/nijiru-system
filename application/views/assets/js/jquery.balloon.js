/**
 * Hover balloon on elements without css and images.
 *
 * Copyright (c) 2011 Hayato Takenaka
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * @author: Hayato Takenaka (https://urin.github.io)
 * @version: 1.0.3 - 2016/07/02
 **/
!function(t){function e(){this.initialize.apply(this,arguments)}function o(o,i,a){function s(t,e,o,i,n){const s=Math.round(i/1.7320508);e.inactive()["setBorder"+o.camel.pos.f](i)["setBorder"+o.camel.pos.c1](s)["setBorder"+o.camel.pos.c2](s)["set"+o.camel.pos.p1](o.isTopLeft?-i:t.inner[o.size.p])["set"+o.camel.pos.c1](t.inner[o.size.c]/a.tipPosition-s).active().$.css("border-"+o.pos.f+"-color",n)}i.stop(!0,!0);var l,r;const p={position:"absolute",height:"0",width:"0",border:"solid 0 transparent"},c=new e(o),d=new e(i);if(d.setTop(-a.offsetY+(a.position&&a.position.indexOf("top")>=0?c.top-d.height:a.position&&a.position.indexOf("bottom")>=0?c.bottom:c.center.top-d.height/2)),d.setLeft(a.offsetX+(a.position&&a.position.indexOf("left")>=0?c.left-d.width:a.position&&a.position.indexOf("right")>=0?c.right:c.center.left-d.width/2)),a.tipSize>0){i.data("outerTip")&&(i.data("outerTip").remove(),i.removeData("outerTip")),i.data("innerTip")&&(i.data("innerTip").remove(),i.removeData("innerTip")),l=new e(t("<div>").css(p).appendTo(i)),r=new e(t("<div>").css(p).appendTo(i));for(var u,h=0;h<n.pos.length;h++){if(u=n.getRelativeNames(h),d.center[u.pos.c1]>=c[u.pos.c1]&&d.center[u.pos.c1]<=c[u.pos.c2])if(h%2===0){if(d[u.pos.o]>=c[u.pos.o]&&d[u.pos.f]>=c[u.pos.f])break}else if(d[u.pos.o]<=c[u.pos.o]&&d[u.pos.f]<=c[u.pos.f])break;u=null}u?(d["set"+u.camel.pos.p1](d[u.pos.p1]+(u.isTopLeft?1:-1)*(a.tipSize-d["border"+u.camel.pos.o])),s(d,l,u,a.tipSize,i.css("border-"+u.pos.o+"-color"),a.tipPosition),s(d,r,u,a.tipSize-2*d["border"+u.camel.pos.o],i.css("background-color"),a.tipPosition),i.data("outerTip",l.$).data("innerTip",r.$)):t.each([l.$,r.$],function(){this.remove()})}}function i(e,o){const i=e.data("balloon")&&e.data("balloon").get(0);return!(i&&(i===o.relatedTarget||t.contains(i,o.relatedTarget)))}const n={pos:t.extend(["top","bottom","left","right"],{camel:["Top","Bottom","Left","Right"]}),size:t.extend(["height","width"],{camel:["Height","Width"]}),getRelativeNames:function(t){const e={pos:{o:t,f:t%2===0?t+1:t-1,p1:t%2===0?t:t-1,p2:t%2===0?t+1:t,c1:2>t?2:0,c2:2>t?3:1},size:{p:2>t?0:1,c:2>t?1:0}},o={};for(var i in e){o[i]||(o[i]={});for(var a in e[i])o[i][a]=n[i][e[i][a]],o.camel||(o.camel={}),o.camel[i]||(o.camel[i]={}),o.camel[i][a]=n[i].camel[e[i][a]]}return o.isTopLeft=o.pos.o===o.pos.p1,o}};!function(){function o(t,e){if(null==e)return o(t,!0),o(t,!1);const i=n.getRelativeNames(e?0:2);return t[i.size.p]=t.$["outer"+i.camel.size.p](),t[i.pos.f]=t[i.pos.o]+t[i.size.p],t.center[i.pos.o]=t[i.pos.o]+t[i.size.p]/2,t.inner[i.pos.o]=t[i.pos.o]+t["border"+i.camel.pos.o],t.inner[i.size.p]=t.$["inner"+i.camel.size.p](),t.inner[i.pos.f]=t.inner[i.pos.o]+t.inner[i.size.p],t.inner.center[i.pos.o]=t.inner[i.pos.f]+t.inner[i.size.p]/2,t}const i={setBorder:function(t,e){return function(i){return this.$.css("border-"+t.toLowerCase()+"-width",i+"px"),this["border"+t]=i,this.isActive?o(this,e):this}},setPosition:function(t,e){return function(i){return this.$.css(t.toLowerCase(),i+"px"),this[t.toLowerCase()]=i,this.isActive?o(this,e):this}}};e.prototype={initialize:function(e){this.$=e,t.extend(!0,this,this.$.offset(),{center:{},inner:{center:{}}});for(var o=0;o<n.pos.length;o++)this["border"+n.pos.camel[o]]=parseInt(this.$.css("border-"+n.pos[o]+"-width"))||0;this.active()},active:function(){return this.isActive=!0,o(this),this},inactive:function(){return this.isActive=!1,this}};for(var a=0;a<n.pos.length;a++)e.prototype["setBorder"+n.pos.camel[a]]=i.setBorder(n.pos.camel[a],2>a),a%2===0&&(e.prototype["set"+n.pos.camel[a]]=i.setPosition(n.pos.camel[a],2>a))}(),t.fn.balloon=function(e){return this.one("mouseenter",function o(n){const a=t(this),s=this,l=a.on("mouseenter",function(t){i(a,t)&&a.showBalloon()}).off("mouseenter",o).showBalloon(e).data("balloon");l&&l.on("mouseleave",function(e){s===e.relatedTarget||t.contains(s,e.relatedTarget)||a.hideBalloon()}).on("mouseenter",function(e){s===e.relatedTarget||t.contains(s,e.relatedTarget)||(l.stop(!0,!0),a.showBalloon())})}).on("mouseleave",function(e){const o=t(this);i(o,e)&&o.hideBalloon()})},t.fn.showBalloon=function(e){var i,n;return(e||!this.data("options"))&&(null===t.balloon.defaults.css&&(t.balloon.defaults.css={}),this.data("options",t.extend(!0,{},t.balloon.defaults,e||{}))),e=this.data("options"),this.each(function(){var a;if(i=t(this),a=!i.data("balloon"),n=i.data("balloon")||t("<div>"),a||!n.data("active")){n.data("active",!0),clearTimeout(n.data("minLifetime"));const s=t.isFunction(e.contents)?e.contents.apply(this):e.contents||i.attr("title")||i.attr("alt");i.removeAttr("title"),!e.url&&""===s||null==s||(t.isFunction(e.contents)||(e.contents=s),e.url?n.data("ajaxDisabled")||(""!==s&&null!=s&&(e.html?n.empty().append(s):n.text(s)),clearTimeout(n.data("ajaxDelay")),n.data("ajaxDelay",setTimeout(function(){n.load(t.isFunction(e.url)?e.url.apply(i.get(0)):e.url,function(t,a,s){("success"===a||"notmodified"===a)&&(n.data("ajaxDisabled",!0),e.ajaxContentsMaxAge>=0&&setTimeout(function(){n.data("ajaxDisabled",!1)},e.ajaxContentsMaxAge),e.ajaxComplete&&e.ajaxComplete(t,a,s),o(i,n,e))})},e.ajaxDelay))):e.html?n.empty().append(s):n.text(s),a?(n.addClass(e.classname).css(e.css||{}).css({visibility:"hidden",position:"absolute"}).appendTo("body"),i.data("balloon",n),o(i,n,e),n.hide().css("visibility","visible")):o(i,n,e),n.data("delay",setTimeout(function(){e.showAnimation?e.showAnimation.apply(n.stop(!0,!0),[e.showDuration,function(){e.showComplete&&e.showComplete.apply(n)}]):n.show(e.showDuration,function(){this.style.removeAttribute&&this.style.removeAttribute("filter"),e.showComplete&&e.showComplete.apply(n)}),e.maxLifetime&&(clearTimeout(n.data("maxLifetime")),n.data("maxLifetime",setTimeout(function(){i.hideBalloon()},e.maxLifetime)))},e.delay)))}})},t.fn.hideBalloon=function(){const e=this.data("options");return this.data("balloon")?this.each(function(){const o=t(this),i=o.data("balloon");clearTimeout(i.data("delay")),clearTimeout(i.data("minLifetime")),clearTimeout(i.data("ajaxDelay")),i.data("minLifetime",setTimeout(function(){e.hideAnimation?e.hideAnimation.apply(i.stop(!0,!0),[e.hideDuration,function(o){t(this).data("active",!1),e.hideComplete&&e.hideComplete(o)}]):i.stop(!0,!0).hide(e.hideDuration,function(o){t(this).data("active",!1),e.hideComplete&&e.hideComplete(o)})},e.minLifetime))}):this},t.balloon={defaults:{contents:null,html:!1,classname:null,url:null,ajaxComplete:null,ajaxDelay:500,ajaxContentsMaxAge:-1,delay:0,minLifetime:200,maxLifetime:0,position:"top",offsetX:0,offsetY:0,tipSize:8,tipPosition:2,showDuration:100,showAnimation:null,hideDuration:80,hideAnimation:function(t,e){this.fadeOut(t,e)},showComplete:null,hideComplete:null,css:{fontSize:".7rem",minWidth:".7rem",padding:".2rem .5rem",border:"1px solid rgba(212, 212, 212, .4)",borderRadius:"3px",boxShadow:"2px 2px 4px #555",color:"#eee",backgroundColor:"#111",opacity:.85,zIndex:"32767",textAlign:"left"}}}}(jQuery);

$(function() {

	$(".j_count").balloon({
		contents: $(this).children("j_newbies").text,
		html: false,
		classname: "balloon",
		url: null,
		ajaxComplete: null,
		ajaxDelay: 500,
		ajaxContentsMaxAge: -1,
		delay: 0,
		minLifetime: 10,
		maxLifetime: 0,
		position: "top",
		offsetX: 0,
		offsetY: 0,
		tipSize: 15,
		tipPosition: 2,
		showDuration: 10,
		showAnimation: null,
		hideDuration: 10,
		hideAnimation: function (d, c) { this.fadeOut(d, c); },
		showComplete: null,
		hideComplete: null,
		css: {
			maxWidth: "200px",
			wordBreak: "break-all",
			fontSize: ".7rem",
			minWidth: ".7rem",
			padding: ".2rem .5rem",
			border: "1px solid rgba(212, 212, 212, .4)",
			borderRadius: "3px",
			boxShadow: "2px 2px 4px #555",
			color: "#eee",
			backgroundColor: "#111",
			opacity: "0.85",
			zIndex: "32767",
			textAlign: "left"
		}
	});

});