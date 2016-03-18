// 下拉刷新
function pullDown(obj) {
	$(obj).xpull({
		'pullThreshold':150,
	    'maxPullThreshold':0,
	    'onPullEndFunc':function(){
	    	window.location.reload();
	    },
	    'spinnerTimeout':2000
	});
};

// 页面滚动顶部透明度变化
function header(obj, color) {
	var oWindow = $(window),
		oHeader = $(obj),
		opacity, scrollTop;
	oWindow.scroll(function(event) {
		if(oWindow.scrollTop() >= 100) {
			scrollTop = 100;
		} else {
			scrollTop = oWindow.scrollTop();
		}
		opacity = parseInt(scrollTop)/100;
		oHeader.css('background-color', 'rgba('+color[0]+','+color[1]+','+color[2]+','+opacity+')');
	});
};