function submitSeat(){
	  var seat_date = $("#seat_date").val();
	  var seat_name = $("#seat_name").val();
	  var seat_tel = $("#seat_tel").val();
	  var seat_code = $("#seat_code").val();
	  
	  var re = /^1\d{10}$/;
	  
	  var msg = '';
	  
	  if(seat_date == ''){
		  //alert('请选择日期');
		  msg = dialog({title: '提示',content: '请选择日期',id: 'm1'});
		  msg.show();
		  return false;
	  }
	  
	  //做一个日期判断，如果当前的日期大于选择的日期，则返回错误
	  var seatDate = seat_date.replace(new RegExp("-", 'g') , "/");
	  if(Date.parse(seatDate) < Date.parse(new Date())) {
	  	 msg = dialog({title: '提示',content: '请选择合适的日期',id: 'm1'});
		 msg.show();
		 return false;
	  }
	  
	  if(seat_name == ''){
		  //alert('请输入姓名');
		  msg = dialog({title: '提示',content: '请输入姓名',id: 'm1'});
		  msg.show();
		  return false;
	  }
	  if(seat_tel == ''){
		  //alert('请输入手机号');
		  msg = dialog({title: '提示',content: '请输入手机号',id: 'm1'});
		  msg.show();
		  return false;
	  }else if(!re.test(seat_tel)){
		  //alert('手机号格式不正确');
		  msg = dialog({title: '提示',content: '手机号格式不正确',id: 'm1'});
		  msg.show();
		  return false;
	  }
	  if(seat_code == ''){
		  //alert('请输入验证码');
		  msg = dialog({title: '提示',content: '请输入验证码',id: 'm1'});
		  msg.show();
		  return false;
	  }
	  $.ajax( {    
		    url:reqUrl,   
		    data:{    
		    	seat_date : seat_date,    
		    	seat_name : seat_name,
		    	seat_tel  : seat_tel,
		    	seat_code : seat_code,
		    },    
		    type:'post',    
		    cache:false,    
		    dataType:'json',    
		    success:function(data) {    
		        if(data.msg =="true" ){
		        	EV_modeAlert('envon4');
		        }else if(data.msg == "unlogin"){
		        	location.href = data.url;
		        }else if(data.msg == "verify_err1"){
		        	//alert('验证码不正确');
		        	msg = dialog({title: '提示',content: '验证码不正确',id: 'm1'});
		  		    msg.show();
		        }else if(data.msg == "verify_err2"){
		        	//alert('验证码已失效,请重新获取');
		        	msg = dialog({title: '提示',content: '验证码已失效,请重新获取',id: 'm1'});
		  		    msg.show();
		        }
		     },
		     error:function(XMLHttpRequest, textStatus, errorThrown){
			    	//alert(JSON.stringify(XMLHttpRequest));
			    	if(XMLHttpRequest.status == '200'){
			    		var data = eval('('+ XMLHttpRequest.responseText +')');
			    		if(data.msg =="true" ){
				        	EV_modeAlert('envon4');
				        }else if(data.msg == "unlogin"){
				        	location.href = data.url;
				        }else if(data.msg == "verify_err1"){
				        	//alert('验证码不正确');
				        	msg = dialog({title: '提示',content: '验证码不正确',id: 'm1'});
				  		    msg.show();
				        }else if(data.msg == "verify_err2"){
				        	//alert('验证码已失效,请重新获取');
				        	msg = dialog({title: '提示',content: '验证码已失效,请重新获取',id: 'm1'});
				  		    msg.show();
				        }
			    	}
			 }
		});
  }
$(document).ready(function(){
    $('.close-btn').click(function(){
        $('.box3').fadeOut(function(){ $('#screen').hide(); });
        return false;
    });
     $('.ornot').click(function(){
        $('.box3').fadeOut(function(){ $('#screen').hide(); });
             location.href = indexUrl ;
    });
    $('.showbox3').click(function(){
    	/*
        var h = $(document).height();
        $('#TB_overlayBG').css({ 'height': h });	
        $('#TB_overlayBG').show();
        $('.box3').center();
        $('.box3').fadeIn();*/
        return false;
    });
    
});
$(document).ready(function(){
    $('.close-btn').click(function(){
        $('.box4').fadeOut(function(){ $('#screen').hide(); });
        return false;
    });
    
    $('.seat_con').click(function(){
    	submitSeat();
    	/*
        var h = $(document).height();
        $('#TB_overlayBG').css({ 'height': h });	
        $('#TB_overlayBG').show();
        $('.box4').center();
        $('.box4').fadeIn();
        return false;*/
    });
    $('.btn-verify').click(function(){
    	var seat_tel = $("#seat_tel").val();
    	var re = /^1\d{10}$/;
    	
    	var msg = '';
    	if(seat_tel == ''){
  		  //alert('请输入手机号');
    	  msg = dialog({title: '提示',content: '请输入手机号',id: 'm1'});
  		  msg.show();
  		  return false;
  	  	}else if(!re.test(seat_tel)){
  		  //alert('手机号格式不正确');
  	  	  msg = dialog({title: '提示',content: '手机号格式不正确',id: 'm1'});
		  msg.show();
  		  return false;
  	  	}
    	
    	time(this);
    	
    	$.ajax( {    
		    url:vUrl,   
		    data:{
		    	tel:seat_tel
		    },    
		    type:'post',    
		    cache:false,    
		    dataType:'json',    
		    success:function(data) {    
		        if(data.msg =="true" ){
		        	//alert('验证码已发送');
		        	msg = dialog({title: '提示',content: '验证码已发送',id: 'm1'});
		  		    msg.show();
		        }
		     },
		     error:function(XMLHttpRequest, textStatus, errorThrown){
			    	//alert(JSON.stringify(XMLHttpRequest));
			    	if(XMLHttpRequest.status == '200'){
			    		var data = eval('('+ XMLHttpRequest.responseText +')');
			    		if(data.msg =="true" ){
				        	//alert('验证码已发送');
			    			msg = dialog({title: '提示',content: '验证码已发送',id: 'm1'});
				  		    msg.show();
				        }
			    	}
			 }
		});
    });
});
var wait=60;
function time(o) {
	if (wait == 0) {
		o.removeAttribute("disabled");
		$(o).html('发送验证码');
		wait = 60;
	} else {
		o.setAttribute("disabled", true);
		$(o).html('重新发送(' + wait + ')');
		wait--;
		setTimeout(function() {
			time(o)
		},
		1000)
	}
}