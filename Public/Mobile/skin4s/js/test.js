!function() {
	function a() {
		if(document.documentElement.clientWidth < 600) {
			document.documentElement.style.fontSize = document.documentElement.clientWidth / 32 + "px";
		} else {
			document.documentElement.style.fontSize = "16.875px";
		}
	}
	var b = null;
	window.addEventListener("resize", function() {
		clearTimeout(b), b = setTimeout(a, 300)
	}, !1), a()
}(window);