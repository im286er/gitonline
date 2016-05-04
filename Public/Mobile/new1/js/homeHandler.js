

/**存放数据请求*/
var pageXHR = null;

/**存放按下时的元素*/
var beginX = 0;
var beginY = 0;
var moveX = 0;
var moveY = 0;


function initHomePage()
{
	
	/**选择首页*/
	setBindTouch('homeBtn','foodPage.html',initChoseTabelBar);
	/**选择购物车*/
	setBindTouch('shopGroupBtn','shopGroupPage.html',initChoseTabelBar);
	/**选择下载页*/
	setBindTouch('downloadBtn','foodPage.html',initChoseTabelBar);
	/**选择个人页*/
	setBindTouch('mineBtn','minePage.html',initChoseTabelBar);
	
}


function setBindTouch(name,htmlName,callback)
{
	$("#"+name).bind("touchstart",function(){
		event.preventDefault();
		if(event.touches.length == 1)
		{
			var touch = event.touches[0];
		 	
		 	beginX = touch.pageX;
		 	
		 	beginY = touch.pageY;
		}
		
	});
	
	$("#"+name).bind("touchmove",function(){
		event.preventDefault();
		if(event.touches.length == 1)
		{
			var touch = event.touches[0];
			moveX = touch.pageX - beginX;
			moveY = touch.pageY - beginY;
		}
		
	});
	
	$("#"+name).bind("touchend",function(){
		
		event.preventDefault();
		
		var touchMoveNum = moveX * moveX + (moveY * moveY);
		
		if(touchMoveNum > 225)
		{
			moveX = moveY = beginX = beginY = 0;
			return false;
		}
		
		if(callback!=null)
		{
			callback();
		}
		
		var nameImg = name + ' img';
		
		var nameDiv = name + ' .lineDiv';
		
		var imgSrc = './img/' + name.substr(0,name.length-3) + 'B.png'
		
		$("#"+nameImg).attr('src',imgSrc);
		
		$("#"+nameDiv).css('display','block');
		
		changePage(htmlName);
		
	});
}


/**加载功能页面*/
function changePage(htmlName)
{
	location.href = './' + htmlName;
}


function initChoseTabelBar()
{
	$("#homeBtn img").attr('src','./img/homeA.png');
	$("#shopGroupBtn img").attr('src','./img/shopGroupA.png');
	$("#downloadBtn img").attr('src','./img/downloadA.png');
	$("#mineBtn img").attr('src','./img/mineA.png');
	$('.roundBG').css('display','none');
}
