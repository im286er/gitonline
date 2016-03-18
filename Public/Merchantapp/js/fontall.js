// JavaScript Document
(function (doc, win) {
  var docEl = doc.documentElement,
    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
    recalc = function () {
      var clientWidth = docEl.clientWidth;
      if (!clientWidth) return;
      docEl.style.fontSize = 10 * (clientWidth /320) + 'px';
    };
 
  if (!doc.addEventListener) return;
  win.addEventListener(resizeEvt, recalc, false);
  doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);


window.onload = function() { 
    var H=$(".six-grid ul li").height();
	var hOne=$(".add a").height();
	var TOP=(H-hOne)/2;
  
	$(".add img").css("marginTop",TOP);
	$(".add img").css("marginBottom",TOP);
};
