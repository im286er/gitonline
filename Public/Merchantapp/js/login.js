// JavaScript Document
function login(){	
	var username=document.getElementById("username");	
	var pass=document.getElementById("password");	
	if(username.value=="")	{		
		alert("请输入用户名");		
		username.focus();		
		return;
	}	
	if(pass.value=="")	{		
		alert("请输入密码");		
		return;	
	}return true;}