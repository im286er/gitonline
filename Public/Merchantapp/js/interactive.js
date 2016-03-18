function connectWebViewJavascriptBridge(callback) {
	if (window.WebViewJavascriptBridge) {
			callback(WebViewJavascriptBridge)
	} else {
		document.addEventListener('WebViewJavascriptBridgeReady', function() {
			callback(WebViewJavascriptBridge)
		}, false)
	}
}
connectWebViewJavascriptBridge(function(bridge) {
	bridge.init(function(message, responseCallback) {
	})
});

function interactive(requestData,needMethod){
	if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
		connectWebViewJavascriptBridge(function(bridge) {
			bridge.send(requestData, function(response) {
				if(needMethod){eval(needMethod); }else{return response;}
			});
		})
	}else if(window._WebView_JS_Info){
		var response = window._WebView_JS_Info.VersionName(requestData);
		if(needMethod){eval(needMethod); }else{return response;}
	}else{
		if(needMethod){eval(needMethod);};
	}
}

/***提交商家ID***/
function baichuanMerchant(utype,data){
	var str = '{ "type": "merchant", "utype": "'+utype+'","data":"'+data+'"}';
	interactive(str);
}