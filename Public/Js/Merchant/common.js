//弹窗
function DialogFrameFun(width, height, src) {
	var DialogTop = 100;
	if(document.body.clientHeight<1000)DialogTop = 50;
	document.getElementById('DialogFrame').src = src;
	document.getElementById('DialogFrame').width = width;
	document.getElementById('DialogFrame').height = height;
	$("#DialogFrameModel").children("div.DialogFrameModel").width(width);
	$("#DialogFrameModel").children("div.DialogFrameModel").animate({top:DialogTop+"px"}).end().fadeIn();
}

//删除分类
function DeleMenu( vcid ) {
	if( vcid && confirm('确定要删除吗？相应数据也会被删除') ) {
		$.post(DeleteClassPath, {cid:vcid}, function( data ) {
			if( parseInt(data) != 0 ) $('#ClassList li[data-id='+vcid+']').remove();
		});
	}		
}

//禁用商品后不显示
function GoodsStatusById( vid , type,gstatus) {
	if( vid && type ) {
		$.post(StatusUrlPath, {id:vid, type:type,gstatus:gstatus}, function( data ) {
			if( parseInt(data) != 0 ) {
				//$("#gtableslist tr[data-id="+vid+"]").remove();
				window.location.reload();
			}
		});
	}	
}


//删除视频\商品
function DeleteVideoById( vid , type) {
	if( vid && confirm('确定要删除吗？相应数据也会被删除') ) {
		$.post(DeleteInfoUrlPath, {id:vid, type:type}, function( data ) {
			if( parseInt(data) != 0 ) {
				$("#gtableslist tr[data-id="+vid+"]").remove();
			}
		});
	}	
}

//删除打印机
function DeletePrintById( printid ) 
{
	if( printid && confirm('确定要删除吗？相应数据也会被删除') ) {
		$.post(DeleteInfoUrlPath, {id:printid}, function( data ) {
			if( parseInt(data) != 0 ) {
				$("#gtableslist tr[data-id="+printid+"]").remove();
			}
		});
	}	
}

//动态插入商品
function InsertGoods(data, type, model) {
	var html = '<tr data-id="'+data.gid+'" data-type="product" data-gtype="'+data.gtype+'" data-cid="'+data.cid+'" data-order="'+data.gorder+'">';
	html += '<td class="vertical" width="60px"><input type="checkbox"></td>';
	html += '<td width="220px">';
	html += '<dl class="table-content">';
	html += '<dt class="pull-left"><img src="'+data.gimg+'" alt=""></dt>';
	html += '<dd class="">	';
	html += '			<p class="gname">'+data.gname+'</p>';
	html += '			<p class="c-a2a2a6 gdescription">'+data.gdescription+'</p>';
	if(data.gtype == 0){
		html += '			<span class="c-bf242a">活动价￥<span class="gdprice" data-goprice="'+data.goprice+'">'+data.gdprice+'</span></span>';
		html += '			<span class="c-bf242a">原价￥<span class="goprice" data-goprice="'+data.goprice+'">'+data.goprice+'</span></span>';  
	}else{
		html += '			<span class="c-bf242a">最低消费￥<span class="goprice" data-goprice="'+data.goprice+'">'+data.goprice+'</span></span>';
		html += '			<span class="c-bf242a">优惠价格￥<span class="gdprice" data-goprice="'+data.goprice+'">'+data.gdprice+'</span></span>';
	}
	html += '		</dd>';
	html += '	</dl>';
	html += '</td>';
	
	if(data.gtype == 0){
		var ttt = 1;
	}else{
		var ttt = 2;
	}
	if(data.gstock == -1){
		var st = '无限制';
	}else{
		var st = data.gstock;
	}
	if(data.sid !=0 && data.gtype == 0 ) {
		html += '<td width="100px" class="vertical ">库存:<span class="gstock">'+st+'</span></td>';
	}
	html += '<td class="vertical">';
	html += ' 	<input type="button" value="修改" class="addshopp" onclick="DialogFrameFun(800, 700, \'/Sales/editGoods/sid/'+data.sid+'/gid/'+data.gid+'.html\')">';
	html += '   <input type="button" class="btnstop" style="color:#ff9900;" value="下线" onClick="GoodsStatusById(\''+data.gid+'\','+ttt+',2)">';
	html += '	<input type="button" value="删除" class="btndelete" style="color:red;" onclick="DeleteVideoById(\''+data.gid+'\', 1)">';
	html += '</td>';
	html += '</tr>';
	
	
	
	if( type=='a' && model=='p' ) {
		var PwindowObj = window.parent.document.getElementById('gtableslist');
		$(PwindowObj).children("tbody").eq(0).prepend(html);	
	} else if( type=='u' && model=='p' ) {
		var PwindowObj = window.parent.document.getElementById('gtableslist');
		$(PwindowObj).children("tbody").children("tr[data-id="+data.gid+"]").remove();
		$(PwindowObj).children("tbody").eq(0).prepend(html);	
	}
}

//动态添加打印机
function InsertPrints(data, type, model) {
	var html = '<tr data-id="'+data.print_id+'">';
	html += '<td class="vertical" width="60px"><input value="'+data.print_id+'" name="printid[]" type="checkbox"></td>';
	html += '<td width="40%">'+data.print_name+'</td>';
	html += '<td width="35%">'+data.print_addtime+'</td>';
	html += '<td class="vertical">';
	html += ' 	<input type="button" value="修改" class="addshopp" onclick="DialogFrameFun(650, 190, \'/Print/editPrint/sid/'+data.print_sid+'/pid/'+data.print_id+'.html\')">';
	html += '	<input type="button" value="删除" class="btndelete" onclick="DeletePrintById(\''+data.print_id+'\', 1)">';
	html += '</td>';
	html += '</tr>';
	if( type=='a' && model=='p' ) {
		var PwindowObj = window.parent.document.getElementById('gtableslist');
		$(PwindowObj).children("tbody").eq(0).prepend(html);	
	} else if( type=='u' && model=='p' ) {
		var PwindowObj = window.parent.document.getElementById('gtableslist');
		$(PwindowObj).children("tbody").children("tr[data-id="+data.print_id+"]").remove();
		$(PwindowObj).children("tbody").eq(0).prepend(html);	
	}
}

//动态插入视频
function InsertVideo(data, type, model) {
	var html = '<tr data-id="'+data.gid+'" data-type="product" data-cid="'+data.cid+'">';
	html += '<td class="vertical" width="60px"><input type="checkbox" value="'+data.gid+'"></td>';
	html += '<td width="350px">';
	html += '<dl class="table-content">';
	html += '<dt class="pull-left"><img src="'+data.gimg+'" alt=""></dt>';
	html += '<dd class="">	';
	html += '			<p class="gname">'+data.gname+'</p>';
	html += '			<p class="c-a2a2a6 gdescription">'+data.gdescription+'</p>';
	html += '		</dd>';
	html += '	</dl>';
	html += '</td>';
	html += '<td class="vertical">';
	html += ' 	<input type="button" value="修改" class="addshopp" onclick="DialogFrameFun(650, 450, \'/Info/editVideo/sid/'+data.sid+'/gid/'+data.gid+'.html\')">';
	html += '	<input type="button" value="删除" class="btndelete" onclick="DeleteVideoById(\''+data.gid+'\', 2)">';
	html += '</td>';
	html += '</tr>';
	
	if( type=='a' && model=='p' ) {
		var PwindowObj = window.parent.document.getElementById('gtableslist');
		$(PwindowObj).children("tbody").eq(0).prepend(html);	
	} else if( type=='u' && model=='p' ) {
		var PwindowObj = window.parent.document.getElementById('gtableslist');
		$(PwindowObj).children("tbody").children("tr[data-id="+data.gid+"]").remove();
		$(PwindowObj).children("tbody").eq(0).prepend(html);	
	}
}

$(document).ready(function(e) {
	//当鼠标移动到分类上时，出现一个删除和修改菜单
    $("#ClassList").on('mouseenter', 'li', function() {
		$(this).children("b").show();
	});
	$("#ClassList").on('mouseleave', 'li', function() {
		$(this).children("b").hide();
	});
	
	
	$('#checkbox').click(function(){
		var checkboxs=$('.tables input[type="checkbox"]'); 
		for(var i=0;i<checkboxs.length;i++){
			if(checkboxs[i].checked==true){
				checkboxs[i].checked=false;
			}else{
				checkboxs[i].checked=true;
			}
		};
	});
	
	$('#btn-delete').click(function(){
		
		var checkboxs=$('.tables input[type="checkbox"]'); 
		if( confirm('确定要删除吗？相应数据也会被删除') ) {
			var class_id = "";
			for(var j=0; j<checkboxs.length; j++) {
				if(checkboxs[j].checked == true) {
					var parents=checkboxs.eq(j).parent().parent();
					var data = type ? {id:parents.attr('data-id'), type:type} : {id:parents.attr('data-id')};
					$.post(DeleteInfoUrlPath,  data); parents.remove();
				}
			}
		}
	});
});










