function edit(type,id,sid,cid)
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
	}else if(type == "shop"){
		editshop(type,id);
	}else if (type == "class") {
		editclass(type,id,sid,cid);
	}
}


function edititem(type,id,action,sid,cid)
{
	if(type == "indexproone"){
		/*代码省略,和edit一样，或者直接ajax处理 */
		if (action == 'edit') {
			editGoods(type,id,sid);
		}else if (action == 'add') {
			addGoods(type,id,sid,cid);
		}
	}else if (type == "activity") {
		if (action == 'edit') {
			editActivity(type,id,sid);
		}else if (action == 'add') {
			addActivity(type,id,sid,cid);
		}
	}else if (type == "coupon") {
		if (action == 'edit') {
			editCoupon(type,id,sid);
		}else if (action == 'add') {
			addCoupon(type,id,sid,cid);
		}
	}

	
}



var layerid = null;

function editCoupon(type,id,sid){
	var content = "/Sales/editjq/dtype/2/sid/"+sid+"/id/"+id+".html";
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改优惠券',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '900px'],
        content: content
    });
}

function addCoupon(type,id,sid,cid){
	var content = "/Sales/addjq/dtype/2/cid/"+cid+"/sid/"+sid+"/id/"+id+".html";
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '添加优惠券',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '900px'],
        content: content
    });
}


function editActivity(type,id,sid){
	var content = "/Message/editdh/dtype/2/sid/"+sid+"/id/"+id+".html";
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改活动',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '900px'],
        content: content
    });
}

function addActivity(type,id,sid,cid){
	var content = "/Message/addhd/dtype/2/cid/"+cid+"/sid/"+sid+"/id/"+id+".html";
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '添加活动',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '900px'],
        content: content
    });
}

function editGoods(type,id,sid){
	var content = "/Sales/editGoods/dtype/2/sid/"+sid+"/gid/"+id+"/pg/.html";
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改商品',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '900px'],
        content: content
    });
}

function addGoods(type,id,sid,cid){
	var content = "/Sales/addGoods/dtype/2/sid/"+sid+"/cid/"+cid+"/ctype/1.html";
	
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改商品',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '900px'],
        content: content
    });
}

function editbanner(type,id)
{
	var content = $('#banner_url').val();
	layerid = layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改背景图',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['400px', '500px'],
        content: content
    });

}

function editmenu(type,id)
{
	var content = $('#menu_url').val();
	layerid =  layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改菜单',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['800px', '500px'],
        content: content
    });

}

function editindexprolist(type,id)
{
	var content = $('#prolist_url').val();
	layerid =  layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改首页推荐产品',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['500px', '500px'],
        content: content
    });

}

function editshop(type,id)
{
	var content = $('#shop_url').val();
	layerid =  layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改商家信息',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['500px', '500px'],
        content: content
    });

}

function editclass(type,id,sid,cid)
{
	// var content = $('#activity_url').val();
	var content = "/Design/inc_class/sid/"+sid+"/cid/"+cid+".html";
	layerid =  layer.open({
        type: 2,
        skin: 'layui-layer-lan',
        title: '修改商家信息',
        fix: false,
        shadeClose: false,
        maxmin: false,
        area: ['500px', '500px'],
        content: content
    });

}

function closelayer()
{
	if(layerid != null)
	{
		layer.close(layerid);
	}
}

function setPage()
{
	
	$('#mobanBtn').bind('click',function(){
		
		$('#mobanBtn').css({'background-color':'#f1f1f1','color':'#000'})
		
		$('#mobanBtn').parent().siblings().children('label').css({'background-color':'#949494','color':'#fff'})
		
		$('.moban-div-content').show()
		
		$('.lanmu-div-content').hide()
		
		$('.gongneng-div-content').hide()
			
		//$(".nano").nanoScroller();
		
		setCookie('defaultpage', 'mobanBtn', 1);
	})
	
	$('#lanMuBtn').bind('click',function(){
		
		$('#lanMuBtn').css({'background-color':'#f1f1f1','color':'#000'})
		
		$('#lanMuBtn').parent().siblings().children('label').css({'background-color':'#949494','color':'#fff'})
		
		$('.moban-div-content').hide()
		
		$('.lanmu-div-content').show()
		
		$('.gongneng-div-content').hide()
		
		//$(".nano").nanoScroller();
		
		setCookie('defaultpage', 'lanMuBtn', 1);
	})
	
	$('#gongNengBtn').bind('click',function(){
		
		$('#gongNengBtn').css({'background-color':'#f1f1f1','color':'#000'})
		
		$('#gongNengBtn').parent().siblings().children('label').css({'background-color':'#949494','color':'#fff'})
		
		$('.moban-div-content').hide()
		
		$('.lanmu-div-content').hide()
		
		$('.gongneng-div-content').show()
		
		//$(".nano").nanoScroller();
		
		setCookie('defaultpage', 'gongNengBtn', 1);
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

