
/**存放按下时的元素*/
var beginX = 0;
var beginY = 0;
var moveX = 0;
var moveY = 0;


/**存放是否同一元素*/
var doBool = false;

/**初始化起始页面**/
function initIndex()
{
	
	setIndex();
	/**选择菜谱*/
	setBindTouch('foodBtn','foodPage.html',backUnChose);
	
	/**选择商家*/
	setBindTouch('shopBtn','shopPage.html',backUnChose);
	
	/**选择评价*/
	setBindTouch('commentBtn','commentPage.html',backUnChose);
	
	/**选择活动*/
	setBindTouch('eventBtn','eventPage.html',backUnChose);
	
	/**选择首页*/
	setBindTouch('homeBtn','foodPage.html',initChoseTabelBar);
	/**选择购物车*/
	setBindTouch('shopGroupBtn','shopGroupPage.html',initChoseTabelBar);
	/**选择下载页*/
	setBindTouch('downloadBtn','index.html',initChoseTabelBar);
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
		
		openLink(htmlName);
		
	});
}


function openLink(htmlName)
{
	location.href = './' + htmlName;
}



/**界面设置*/
function setIndex()
{
	var contentHeight = $(window).height() - $('.topDIV').height() - 51;
	$("#contentDiv").height(contentHeight);
}


/**重置选择器*/
function backUnChose()
{
	$("#foodBtn div").css('display','none');
	$("#commentBtn div").css('display','none');
	$("#shopBtn div").css('display','none');
	$("#eventBtn div").css('display','none');
	
	$("#foodBtn img").attr('src','./img/foodA.png');
	$("#commentBtn img").attr('src','./img/commentA.png');
	$("#shopBtn img").attr('src','./img/shopA.png');
	$("#eventBtn img").attr('src','./img/eventA.png');
}

function initChoseTabelBar()
{
	$("#homeBtn img").attr('src','./img/homeA.png');
	$("#shopGroupBtn img").attr('src','./img/shopGroupA.png');
	$("#downloadBtn img").attr('src','./img/downloadA.png');
	$("#mineBtn img").attr('src','./img/mineA.png');
	$('.roundBG').css('display','none');
}