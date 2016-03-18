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
	if(msystem == 'ios'){
		connectWebViewJavascriptBridge(function(bridge) {
			bridge.send(requestData, function(response) {
				if(needMethod){eval(needMethod); }else{return response;}
			});
		})
	}else if(window._WebView_JS_Common){
		var response = window._WebView_JS_Common.VersionName(requestData);
		if(needMethod){eval(needMethod); }else{return response;}
	}
}

//登录
function checkLogin(u,needMethod){
	var utoken = interactive('{"type":"login","linkurl":"'+u+'"}',needMethod);
	if(!needMethod){
		return utoken;
	}
}

//支付
function payOrder(pay_type,order_id){
	var result = interactive('{"type":"pay","pay_type":"'+pay_type+'","order_id":"'+order_id+'"}');
	return result;
}

/***发送重新定向***/
function sendRedirect(data){
	var str1 = '{ "type": "redirect", "data": "'+data+'" }';
	interactive(str1);
}


/***发送重新定向***/
function sendShareStr(data){
	var str1 = '{ "type": "shareshop", "sid": "'+data+'" }';
	interactive(str1);
}

/***发送类型字符串***/
function sendStr(str,needMethod){
	var returnstr = interactive('{ "type": "'+str+'" }',needMethod);
	if(!needMethod){
		return returnstr;
	}
}
/***发送商户地图导航***/
function sendMapGps(sname,address,longitude,latitude,vid){
	var str1 = '{"type":"mapgps","sname":"'+sname+'","address":"'+address+'","longitude":"'+longitude+'","latitude":"'+latitude+'","vid":"'+vid+'"}';
	return interactive(str1);
}

/***建立聊天***/
function baichuanIM(user1,user2){
	var str = '{ "type": "baichuanim", "user1": "'+user1+'","user2":"'+user2+'"}';
	interactive(str);
}

/***提交商家ID***/
function baichuanMerchant(utype,data){
	var str = '{ "type": "merchant", "utype": "'+utype+'","data":"'+data+'"}';
	interactive(str);
}

//收货地址
function changeAddress(u){
	var result = interactive('{"type":"address","linkurl":"'+u+'"}');
	return result;
}

/***直接发送字符串***/
function sendStrType(str,needMethod){
	var returnstr = interactive(str,needMethod);
	if(!needMethod){
		return returnstr;
	}
}

