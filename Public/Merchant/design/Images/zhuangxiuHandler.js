function edit(type,id)
{
	 
	if(type == "banner")
	{
		editbanner(type,id);
	}else if(type == "menu")
	{
		editmenu(type,id);
	}else if(type == "indexprolist")
	{
		editindexprolist(type,id);
	}
}

function editbanner(type,id)
{
	 layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改背景图',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['400px', '500px'],
        content: 'inc_banner.html' 
    });

}

function editmenu(type,id)
{
	  layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改菜单',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '500px'],
        content: 'inc_menu.html' 
    });

}

function editindexprolist(type,id)
{
	  layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改首页推荐产品',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['500px', '500px'],
        content: 'inc_indexprolist.html' 
    });

}

function setPage()
{
	
	$('#mobanBtn').bind('click',function(){
		$('#mobanBtn').css({'background-color':'#f1f1f1','color':'#000'})
		
		$('#mobanBtn').parent().siblings().children('label').css({'background-color':'#949494','color':'#fff'})
		
		$('.moban-div-content').show()
		
		$('.lanmu-div-content').hide()
		
		$('.gongneng-div-content').hide()
			
		$(".nano").nanoScroller();
	})
	
	$('#lanMuBtn').bind('click',function(){
		
		$('#lanMuBtn').css({'background-color':'#f1f1f1','color':'#000'})
		
		$('#lanMuBtn').parent().siblings().children('label').css({'background-color':'#949494','color':'#fff'})
		
		$('.moban-div-content').hide()
		
		$('.lanmu-div-content').show()
		
		$('.gongneng-div-content').hide()
		
		$(".nano").nanoScroller();
	})
	
	$('#gongNengBtn').bind('click',function(){
		
		$('#gongNengBtn').css({'background-color':'#f1f1f1','color':'#000'})
		
		$('#gongNengBtn').parent().siblings().children('label').css({'background-color':'#949494','color':'#fff'})
		
		$('.moban-div-content').hide()
		
		$('.lanmu-div-content').hide()
		
		$('.gongneng-div-content').show()
		
		$(".nano").nanoScroller();
	})
	
	
	/**模版功能分类标签*/
	$('.typeListUl li label').bind('click',function(){

		//console.log(this)
		$('.typeListUl li label').css({'color':'#000','background-color':'#fff'});
		this.style.color = '#fff';
		this.style.backgroundColor = '#00bbe8';
	})
	
	/**系统功能配置按钮*/
	$('.gongneng-systemBtn li div').bind('click',function(){
		
		var tempClass = '.' + this.parentNode.parentNode.className;
		
		$(tempClass).children().children('div').removeClass('blueWhite');

		this.className = 'bdr3 blueWhite gongneng-systemBtnLabel yh16';
		
		showSystemBase(this.innerText)
		
	})
	

	
	/**委托事件*/
	$(document).on('click','#setLmTop',function(){
		
		changeUlList()
	})
	
	
	$(document).on('click','#addNewLMBtn',function(){
		
	})
}


function showSystemBase(str)
{
	
	
	$('.gongneng-base').hide();
		
	$('.gongneng-marketingList').hide();
		
	$('.gongneng-dealList').hide();
		
	$('.gongneng-interactList').hide();
		
	$('.gongneng-extensionList').hide();
		
	$('.gongneng-QRcodeBg').hide();
	
		
	if(str == '基础功能')
	{
		$('.gongneng-base').show();
		
		return false;
	}
	
	if(str == '营销')
	{
		
		$('.gongneng-marketingList').show();
		
		return false;
	}
	
	if(str == '交易支付')
	{
		$('.gongneng-dealList').show();
		
		return false;
	}
	
	if(str == '互动')
	{
		$('.gongneng-interactList').show();
		
		return false;
	}
	
	if(str == '推广吸粉')
	{
		$('.gongneng-extensionList').show();
		
		return false;
	}
	
	if(str == '二维码')
	{
		$('.gongneng-QRcodeBg').show();
		
		return false;
	}
}

function createLM()
{
	var newLi = document.createElement('li');
	
	var newDivA = document.createElement('div');
	
	newDivA.className = 'lm-kind-div yh16';
	
	newLi.appendChild(newDivA);
	
	var newDivB = document.createElement('div');
	
	newDivB.className = 'checkDiv';
	
	newDivA.appendChild(newDivB);
	
	var newImg = document.createElement('img');
	
	newImg.className = 'leftSign mLeft10';
	
	newImg.src = '帝鼠OS商户管理平台_files/leftSign.png';
	
	newDivB.appendChild(newImg);
	
	
	
	$('.lanmu-List-Ul').append(newLi)
}


/**栏目列表的排序**/
function changeUlList()
{
	var tempLiA = $("#setLmTop").parent().parent()[0];
		
		var tempLiB = null;
		
		var oldNum = 0;
		
		var newNum = 0;
		
		var ulArr = $('.lanmu-List-Ul').children();
		
		for(var i=0;i<ulArr.length;i++)
		{
			if(tempLiA == ulArr[i])
			{
				if(i>0)
				{
					oldNum = i;
					
					newNum = i - 1;
					
					tempLiB = ulArr[newNum];
					
					ulArr[newNum] = tempLiA;
					
					ulArr[oldNum] = tempLiB;
					
					$('.lanmu-List-Ul').html('');
					
					break;
				}
			}
		}
		
		for(var s=0; s<ulArr.length;s++)
		{
			$('.lanmu-List-Ul').append(ulArr[s])
		}
}

