

/**存放按下时的元素*/
var shopGroupBeginX = 0;
var shopGroupBeginY = 0;
var shopGroupMoveX = 0;
var shopGroupMoveY = 0;

var needDelete = null;

/**总共价格**/
var allCost = 0;

function setDiv(){
	
	var divHeight = $(window).height() - 60 - 51 - 40;
	
	$('.foodListDiv').height(divHeight);
	
	
	
	/**为增加商品设置touch事件*/
	setTypeTopTouch('.addBtn',addHandlerSet);
	
	/**为减少商品设置touch事件*/
	setTypeTopTouch('.jianBtn',deleteHandlerSet);
	
	
	/**为选择设置touch事件*/
	setTypeTopTouch('.reSignDiv',reSignHanler);
	
	/**为全选设置touch事件*/
	setTypeTopTouch('.allReSignDiv',allReSignHanler);
	
	/**为编辑设置touch事件*/
	setTypeTopTouch('.shopGroupEdit',editOrNot);
	
	/**为全部删除设置touch事件*/
	setTypeTopTouch('#deleteAllBtn',deleteAllHandler);

}

/**批量删除商品**/
function deleteAllHandler()
{
	var allDelete = false;
	var blockArr = [];
	
	if($('.reSignImg').length>0)
	{
		for(var i=0; i<$('.reSignImg').length;i++)
		{
			var tempID = $('.reSignImg')[i].id
			if($('#'+tempID).css('display') == 'none')
			{
				allDelete = true;
			}else
			{
				blockArr.push(1);
			}
			
		}
		
		if(blockArr.length > 0)
		{
			if(allDelete == true)
			{
				showDeleteMsg('确定删除多个商品吗？',showWindow)
			}else
			{
				showDeleteMsg('确定删除全部商品吗？',showWindow)
			}
		}else
		{
			showDeleteMsg('请先选择需要删除的商品！',showWindow)
		}
		
		
	}else
	{
		showDeleteMsg('没有商品!',showWindow)
	}
	
	
}

function showWindow()
{
	setTypeTopTouch('#yesBtnID',confirmDelete);
	setTypeTopTouch('#noBtnID',canleDeleteProduct);
}

function confirmDelete()
{
	var arrDelete = [];
	for(var i = 0; i< $('.reSignImg').length;i++)
	{
		var reSignImgID = $('.reSignImg')[i].id;
		if($('#'+ reSignImgID).css('display') == 'block')
		{
			var tempID = reSignImgID.substr(9,reSignImgID.length);
			tempID = 'reSignDiv' + tempID;
			tempID = $('#'+tempID).parent('.foodListStyle');
			arrDelete.push(tempID)
		}
	}	
	
	for(var s = 0;s<arrDelete.length;s++)
	{
			
		var idNum = arrDelete[s].children('.reSignDiv').attr('id');
			
		idNum = idNum.substr(9,idNum.length)
			
		var showCost = $('.shopGroupCost').text();
			
		showCost = showCost.substr(3,showCost.length)
			
		showCost = parseInt(showCost);
			
		showCost -= getProductCost(idNum);
			
		showCost = '合计￥'+showCost;
			
		$('.shopGroupCost').text(showCost)
			
		arrDelete[s].remove();
	}
	
	
	
	$('#bgID').remove();
	$('#bigTitID').remove();
}


/**编辑方法**/
function editOrNot()
{
	if($('#shanchuDiv').css('display') == 'none')
	{
		$('#shanchuDiv').css('display','block');
		
		$('#jiesuanDiv').css('display','none');
	}else if($('#shanchuDiv').css('display') == 'block')
	{
		$('#shanchuDiv').css('display','none');
		
		$('#jiesuanDiv').css('display','block');
	}
}


/**选中商品按钮**/
function reSignHanler()
{
	var tempID = event.target.id;
	
	var tempImg = 'reSignImg' +  tempID.substr(9,tempID.length);
	
	if($('#'+tempImg).css('display') == 'none')
	{
		$('#'+tempImg).css('display','block')
		
		var allBool = false;
		
		for(var i = 0; i< $('.reSignImg').length;i++)
		{
			allBool = true;
			
			var reSignImgID = $('.reSignImg')[i].id;
			
			if($('#'+ reSignImgID).css('display') == 'none')
			{
				allBool = false;
				break;
			}
		}
		
		if(allBool == true)
		{
			$('.allReSignImg').css('display','block');
		}
		
		var showCost = $('.shopGroupCost').text();
			
		showCost = showCost.substr(3,showCost.length)
			
		showCost = parseInt(showCost);
		
		showCost = showCost + getProductCost(tempID.substr(9,tempID.length));
		
		showCost = '合计￥'+ showCost;
			
		$('.shopGroupCost').text(showCost)
		
	}else if($('#'+tempImg).css('display') == 'block')
	{
		
		$('.allReSignImg').css('display','none');
		
		var showCost = $('.shopGroupCost').text();
		
		showCost = showCost.substr(3,showCost.length)
			
		showCost = parseInt(showCost);
		
		showCost -= getProductCost(tempID.substr(9,tempID.length));
		
		showCost = '合计￥' + showCost;
			
		$('.shopGroupCost').text(showCost)
		
		$('#'+tempImg).css('display','none')
	}
	
}


/**全选商品方法**/
function allReSignHanler()
{
	
		if($('.allReSignImg').css('display') == 'none')
		{
			$('.allReSignImg').css('display','block');
			
			var foodListNum = $('.reSignImg').length;
			
			for(var i = 0;i<foodListNum;i++)
			{
				var tempID = $('.reSignImg')[i].id;
				
				if($('#'+tempID).css('display') == 'none')
				{
					tempID = tempID.substr(9,tempID.length);
				
					var showCost = $('.shopGroupCost').text();
						
					showCost = showCost.substr(3,showCost.length)
						
					showCost = parseInt(showCost);
					
					showCost = showCost + getProductCost(tempID);
					
					showCost = '合计￥'+ showCost;
						
					$('.shopGroupCost').text(showCost)
				}
				
			}
			
			$('.reSignImg').css('display','block');
			
			
		}else if($('.allReSignImg').css('display') == 'block')
		{
			$('.allReSignImg').css('display','none');
			
			var foodListNum = $('.reSignImg').length;
		
			$('.reSignImg').css('display','none');
			
			for(var i = 0;i<foodListNum;i++)
			{
				var tempID = $('.reSignImg')[i].id;
				
				tempID = tempID.substr(9,tempID.length);
				
				var showCost = $('.shopGroupCost').text();
					
				showCost = showCost.substr(3,showCost.length)
					
				showCost = parseInt(showCost);
				
				showCost = showCost - getProductCost(tempID);
				
				showCost = '合计￥'+ showCost;
					
				$('.shopGroupCost').text(showCost)
			}
		}
}



/**添加商品按钮**/
function addHandlerSet()
{
	var tempID = event.target.id;
		
	var labelID = 'numLabel' + tempID.substr(3,tempID.length);
		
	var jianID = 'jian' + tempID.substr(3,tempID.length);
		
	var moneyLabelID = 'moneyLabel' + tempID.substr(3,tempID.length);
	
	var choseImg = 'reSignImg' + tempID.substr(3,tempID.length);
		
	var tempNum = $('#'+labelID).text();
		
		if(tempNum == ''||tempNum == null)
		{
			$('#'+labelID).text(1)
			
			$('#'+jianID).css('display','block');
			
		}else
		{
			//tempNum = parseInt(tempNum) + 1;
			//$('#'+labelID).text(tempNum);
		}
		
		if($('#'+choseImg).css('display') == 'block')
		{
			var cost = $('#'+moneyLabelID).text();
		
			cost = parseInt(cost);
			
			var showCost = $('.shopGroupCost').text();
			
			showCost = showCost.substr(3,showCost.length)
			
			showCost = parseInt(showCost);
			
			showCost += cost;
			
			showCost = '合计￥'+showCost;
			
			$('.shopGroupCost').text(showCost)
		}
		
}

/**删除商品按钮**/
function deleteHandlerSet()
{
	
	var tempID = event.target.id;
	
	var labelID = 'numLabel' + tempID.substr(4,tempID.length)
		
	var tempNum = $('#'+labelID).text();
		
	var moneyLabelID = 'moneyLabel' + tempID.substr(4,tempID.length);
	
	var choseImg = 'reSignImg' + tempID.substr(4,tempID.length);
	
	if(tempNum > -1)
	{
		//tempNum = parseInt(tempNum) - 1;
		//
		//$('#'+labelID).text(tempNum);
		
	}else
	{
		needDelete = moneyLabelID;
		
		showDeleteMsg('确定要删除商品吗？',deleteOneProduct);
		
		return false;
	}

	
	
	if($('#'+choseImg).css('display') == 'block')
	{
		var cost = $('#'+moneyLabelID).text();
		
			cost = parseInt(cost);
			
			var showCost = $('.shopGroupCost').text();
			
			showCost = showCost.substr(3,showCost.length)
			
			showCost = parseInt(showCost);
			
			showCost -= cost;
			
			showCost = '合计￥'+showCost;
			
			$('.shopGroupCost').text(showCost)
	}
		
}

function deleteOneProduct()
{
	setTypeTopTouch('#yesBtnID',deleteProduct);
	setTypeTopTouch('#noBtnID',canleDeleteProduct);
}


/**计算单类商品总价*/
function getProductCost(idNum)
{
	
	var labelID = 'numLabel' + idNum;
		
	var moneyLabelID = 'moneyLabel' + idNum;
		
	var tempNum = $('#'+labelID).text();
	
	tempNum = parseInt(tempNum);
	
		
	var moneyOne = $('#'+moneyLabelID).text();
	
	moneyOne = parseInt(moneyOne);
	
	var moneyOneAll = tempNum * moneyOne;
	
	return moneyOneAll;
}



/**确认删除后删除**/
function deleteProduct()
{
	if(needDelete != null)
	{
		var oldSign = 'reSignImg' + needDelete.substr(10,needDelete.length);
		
		if($('#'+oldSign).css('display') == 'block')
		{
			var cost = $('#'+needDelete).text();
		
			cost = parseInt(cost);
			
			var showCost = $('.shopGroupCost').text();
			
			showCost = showCost.substr(3,showCost.length)
			
			showCost = parseInt(showCost);
			
			showCost -= cost;
			
			showCost = '合计￥'+showCost;
			
			$('.shopGroupCost').text(showCost)
		}
		
		$('#'+needDelete).parent('.foodListStyle').remove();
		$('#bgID').remove();
		$('#bigTitID').remove();
	}
	
	needDelete == null;
}
	

/**取消删除**/	
function canleDeleteProduct()
{
	$('#bgID').remove();
	$('#bigTitID').remove();
}




/**通用touch绑定事件*/
function setTypeTopTouch(name,callback)
{
	
	$(name).bind("touchstart",function(){
//		event.preventDefault();
		if(event.touches.length == 1)
		{
			var touch = event.touches[0];
		 	
		 	shopGroupBeginX = touch.pageX;
		 	
		 	shopGroupBeginY = touch.pageY;
		}
		
	});
	
	$(name).bind("touchmove",function(){
//		event.preventDefault();
		if(event.touches.length == 1)
		{
			var touch = event.touches[0];
			shopGroupMoveX = touch.pageX - shopGroupBeginX;
			shopGroupMoveY = touch.pageY - shopGroupBeginY;
		}
		
	});
	
	$(name).bind("touchend",function(){
		
		
		var touchMoveNum = shopGroupMoveX * shopGroupMoveX + (shopGroupMoveY * shopGroupMoveY);
		
		if(touchMoveNum > 225)
		{
			shopGroupMoveX = shopGroupMoveY = shopGroupBeginX = shopGroupBeginY = 0;
			return false;
		}

		if(callback != null)
		{
			callback();
		}
		
		
		
	});
}


/**删除商品窗口**/
function showDeleteMsg(tellWords,callback)
{
	var bgID = 'bgID';
	var bigTitID = 'bigTitID';
	var warringLabelID = 'warringLabelID';
	
	var topNumber = $(window).height()/4 + 'px';
	
	var bg = document.createElement('div');with(bg.style)
	{
		width = '100%';
		height = '100%';
		display = 'block';
		backgroundColor = '#000000';
		opacity = '0.7';
		position = 'fixed';
		top = '0';
		left = '0';
	}
	$("body").append(bg);
	bg.id = bgID;
	
	var bigTit = document.createElement('div');with(bigTit.style)
	{
		position = 'absolute';
		width = '80%';
		height = '127px';
		borderRadius = '10px';
		backgroundColor = '#FFFFFF';
		opacity = '0.9';
		left = '10%';
		top = topNumber;
	}
	$("body").append(bigTit);
	bigTit.id = bigTitID;
	
	
	var warringLabel = document.createElement('label');with(warringLabel.style)
	{
		float = 'left';
		width = '100%';
		fontSize = '18px';
		textAlign = 'center';
		paddingTop = '10px';
	}
	$("#"+bigTitID).append(warringLabel);
	
	warringLabel.id = warringLabelID;
	
	$("#"+warringLabelID).text('警告');
	
	
	var wMsgLabel = document.createElement('label');with(wMsgLabel.style)
	{
		float = 'left';
		width = '100%';
		fontSize = '14px';
		textAlign = 'center';
		paddingTop = '10px';
	}
	$("#"+bigTitID).append(wMsgLabel);
	
	wMsgLabel.id = 'wMsgLabelID';
	
	$("#wMsgLabelID").text(tellWords);
	
	
	var yesBtn = document.createElement('div');with(yesBtn.style)
	{
		float = 'left';
		width = '50%';
		height = '39px';
		borderTop = '1px solid #dddddd';
		borderRight = '1px solid #dddddd';
		marginTop = '20px';
	}
	$("#bigTitID").append(yesBtn);
	yesBtn.id = 'yesBtnID';
	
	var noBtn = document.createElement('div');with(noBtn.style)
	{
		float = 'right';
		width = '50%';
		height = '39px';
		borderTop = '1px solid #dddddd';
		marginTop = '20px';
	}
	$("#bigTitID").append(noBtn);
	noBtn.id = 'noBtnID';
	$('#noBtnID').width($('#noBtnID').width()-1);
	
	
	var yesLabel = document.createElement('label');with(yesLabel.style)
	{
		float = 'left';
		width = '100%';
		fontSize = '16px';
		textAlign = 'center';
		lineHeight = '39px';
	}
	$("#yesBtnID").append(yesLabel);
	
	yesLabel.id = 'yesLabelID';
	
	$("#yesLabelID").text('确定');
	
	
	var noLabel = document.createElement('label');with(noLabel.style)
	{
		float = 'left';
		width = '100%';
		fontSize = '16px';
		textAlign = 'center';
		lineHeight = '39px';
	}
	$("#noBtnID").append(noLabel);
	
	noLabel.id = 'noLabelID';
	
	$("#noLabelID").text('取消');

	
	if(callback != null)
	{
		callback();
	}
}
