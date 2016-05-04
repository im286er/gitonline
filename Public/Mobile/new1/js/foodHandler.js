


/**存放按下时的元素*/
var foondBeginX = 0;
var foondBeginY = 0;
var foondMoveX = 0;
var foondMoveY = 0;


/**商品列表设置*/
function setHandler()
{
	/**为分类设置touch事件*/
	setTypeTopTouch('.typeTop',typeTopSet);
	
	/**为加入购物车设置touch事件*/
	setTypeTopTouch('.addImg',addHandlerSet);
	
	/**为移出购物车设置touch事件*/
	setTypeTopTouch('.jianImg',deleteHandlerSet);

}

/**通用touch绑定事件*/
function setTypeTopTouch(name,callback)
{
	$(name).bind("touchstart",function(){
//		event.preventDefault();
		if(event.touches.length == 1)
		{
			var touch = event.touches[0];
		 	
		 	foondBeginX = touch.pageX;
		 	
		 	foondBeginY = touch.pageY;
		}
		
	});
	
	$(name).bind("touchmove",function(){
//		event.preventDefault();
		if(event.touches.length == 1)
		{
			var touch = event.touches[0];
			foondMoveX = touch.pageX - foondBeginX;
			foondMoveY = touch.pageY - foondBeginY;
		}
		
	});
	
	$(name).bind("touchend",function(){
		
//		event.preventDefault();
		
		var touchMoveNum = foondMoveX * foondMoveX + (foondMoveY * foondMoveY);
		
		if(touchMoveNum > 225)
		{
			foondMoveX = foondMoveY = foondBeginX = foondBeginY = 0;
			return false;
		}
		
		if(callback != null)
		{
			callback();
		}
		
		
		
	});
}


function typeTopSet()
{
	var tempID = event.target.id;
		
	var signID = 'signDiv' + tempID.substr(7,tempID.length)
		
	$('.signBlue').css('display','none');
			
	$('#' + signID).css({'display':'block','height':$('#'+tempID).height()});
}


function addHandlerSet()
{
	var tempID = event.target.id;
		
		var labelID = 'numLabel' + tempID.substr(3,tempID.length)
		
		var jianID = 'jian' + tempID.substr(3,tempID.length)
		
		var tempNum = $('#'+labelID).text();
		
		if(tempNum == ''||tempNum == null)
		{
			//$('#'+labelID).text(1)
			//
			//$('#'+jianID).css('display','block');
			
		}else
		{
			//tempNum = parseInt(tempNum) + 1;
			//$('#'+labelID).text(tempNum);
		}
		
		var rightNum = ($('#shopGroupBtn').width()-$('#shopGroupBtn img').width()) / 2 - 13;
		
		$('.roundBG').css('right',rightNum+'px');
		
		$('.roundBG').css('display','block');
		
		var shopGroupNum = $('.roundBG label').text();
		
		if(shopGroupNum == ''||shopGroupNum == null)
		{
			$('.roundBG label').text(1)
		}else
		{
			shopGroupNum = parseInt(shopGroupNum) + 1
			$('.roundBG label').text(shopGroupNum);
		}
		
		
}

function deleteHandlerSet()
{
	var tempID = event.target.id;
		
		var labelID = 'numLabel' + tempID.substr(4,tempID.length)
		
		var tempNum = $('#'+labelID).text();
		
		if(tempNum > 1)
		{
			
			//tempNum = parseInt(tempNum) - 1;
			//$('#'+labelID).text(tempNum);
			
		}else
		{
			//$('#'+labelID).text('');
			//
			//var thisDivID = event.target.id;
			//
			//$('#'+thisDivID).css('display','none');
		}
		
		var rightNum = ($('#shopGroupBtn').width()-$('#shopGroupBtn img').width()) / 2 - 13;
		
		$('.roundBG').css('right',rightNum+'px');
		
		var shopGroupNum = $('.roundBG label').text();
		
		if(shopGroupNum <= 1)
		{
			$('.roundBG label').text(0)
			
			$('.roundBG').css('display','none');
			
		}else
		{
			shopGroupNum = parseInt(shopGroupNum) - 1
			$('.roundBG label').text(shopGroupNum);
		}
}
