$(function(){
	var class_name="";
	var class_id = "";
	/*保存*/
	clickSaveCancel.Save();
	/*切换table*/
	clickSaveCancel.getTableFun();
	/*取消*/
	clickSaveCancel.Cancel();
	/*queding*/
	clickSaveCancel.clickSubmit();
	/*鼠标移动到分类管理菜单 添加一个修改 和删除 按钮*/
	hoverFun();
	//左侧单击排序
	orderByFun();
	//单击右侧排序
	orderByFuns();
	//单击checkbox
	checkboxFun();

});
/*鼠标移动到分类管理菜单 添加一个修改 和删除 按钮*/
var hoverFun=function(){
	$("#list").on('mouseenter','li',function(){
		var html='<b class="pull-right" id="b"><i class="writeicon" id="newadd"></i><i class="deleteicon" id="delete"></i></b>';
		$(this).append(html);
		class_id = $(this).attr("data-id");		//Eric添加
		class_name = $(this).text();			//Eric添加		
		$(this).siblings().removeClass("curr");	//Eric添加		
		$(this).attr("class","curr");			//Eric添加		
		modalShow($('#newadd'),$('#modal'));
		deleteFun($("#delete"));
	});
	$("#list").on('mouseleave','li',function(){
		$(this).find($("#b")).remove();
	});	
}
/*左侧单击排序*/
var orderByFun=function(){
	$('#orderby').click(function(){
		$('#addorder').addClass('hide').removeClass('show');
		$('#button').addClass('show').removeClass('hide');
		var list = $('#list li');
		for(var i=0;i<list.length;i++){
 			list.eq(i).prepend('<input type="text" class="addtxt" value='+ list.eq(i).attr("data-order") +'>');
 		}
		//$('#list li').prepend('<input type="text" class="addtxt" value='+ $('#list li').eq().attr("data-order") +'>');
		removeInput($('#finish'));
		removeInput($('#cancel'));
	});
}
/*右侧单击排序*/
var orderByFuns=function(){
	$('#orderbyshopp').click(function(){
		var checkboxs=$('.tables input[type="checkbox"]');
		for(var i=0;i<checkboxs.length;i++){
			tdParent=checkboxs.eq(i).parent();			
			tdParent.append('<input type="text" class="addtxt" value='+ tdParent.parent().attr("data-order") +'>');
			checkboxs.eq(i).remove();
		}
		$('#addorders').addClass('hide').removeClass('show');
		$('#buttons').addClass('show').removeClass('hide');	
		$('.manage-margin').addClass('hide').removeClass('show');
		removeInputFun($('#finishs'));
		removeInputFun($('#cancels'));
	});
}

/*弹出层*/
var modalShow=function(clickBtn,modalobj){
	var _revealModal=$('.reveal-modal');
	$(clickBtn).on('click',function(){
		
		if(clickBtn.attr("id") == 'add'){				//Eric添加
			class_id = "";								//Eric添加
			class_name = "";							//Eric添加
		}												//Eric添加
		else if(clickBtn.attr("id") == 'addshopp'){
			$("#addMenuForm input[type='text']").val("");
			$("#addMenuForm textarea").val("");
		}
		else if(clickBtn.attr("class") == 'addshopp'){
			var parent = $(this).parent().parent();
			var target = $(".modal-content");
			target.find("#gname").val(parent.find(".gname").text());
			target.find("#gdescription").val(parent.find(".gdescription").text());
			target.find("#goprice").val(parent.find(".gdprice").attr("data-goprice"));
			target.find("#gdprice").val(parent.find(".gdprice").text());
			target.find("#gstock").val(parent.find(".gstock").text());
			target.find("#preview img").attr("src",parent.find(".gimg").attr("src"));
			target.find("#gid").attr("value",parent.attr("data-id"));
			//target.find(".gtype").attr("checked",false);
			target.find(".gtype[value="+parent.attr("data-gtype")+"]").attr("checked",true);
			target.find("#cid option[value="+parent.attr("data-cid")+"]").attr("selected",true);
		}
		if(clickBtn.attr("id") == 'add'){
			$("#name").val(class_name);				//Eric添加
		}
		$(_revealModal).animate({top:'100px'});
		$(modalobj).fadeIn();
		$('.close').on('click',function(){
			$(_revealModal).animate({top:'-1000px'});
			$(modalobj).fadeOut();
		});
		$(".btn-hui").on('click',function(){			//Eric添加
			$(_revealModal).animate({top:'-1000px'});	//Eric添加
			$(modalobj).fadeOut();						//Eric添加
		})
	});
}

/*删除*/
var deleteFun=function(obj){
	$(obj).click(function(){		
		var parents=$(this).parent().parent();
		var data_type = parents.attr('data-type');
		if(data_type == "product"){
			class_id = parents.attr('data-id');
		}
		var flag=confirm('确定要删除吗？相应数据也会被删除');
		if(flag==true){
			//console.log(parents.attr('data-id')+'=========='+parents.attr('data-type'));
			_delete_ajax(class_id,data_type,parents);	
		}		
	});
};
function _delete_ajax(class_id,data_type,parents){
	$.ax(
			delclass_url,
			{"cid":class_id,"data_type":data_type},
            null,
            "post",
            "text", 
            function(data){
				if(data == "yes"){
					parents.remove();
					if(data_type=="manage"){
						var target = $('#cid option[value='+class_id+']');
						target.remove();
					}
				}
        	   else{
        		   alert("操作失败，请重试！");	        		  
        	   }
            }, 
            function(){
                alert("操作失败，请重试！");
            }
     );
}
/*分类管理左侧*/
var removeInput=function(obj){
	$(obj).click(function(){
		$('#list li').find('input').remove();
		$(this).parent().addClass('hide').removeClass('show');
		$('#addorder').addClass('show').removeClass('hide');
	});
}
/*商品管理*/
var removeInputFun=function(obj){
	$(obj).click(function(){
		$('.addtxt').parent().append('<input type="checkbox">')
		$('.addtxt').remove();
		$('#buttons').addClass('hide').removeClass('show');
		$('#addorders').addClass('show').removeClass('hide');
		$('.manage-margin').addClass('show').removeClass('hide');
	});
}
/*点击全选删除*/
var checkboxFun=function(){
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
		var flag=confirm('确定要删除吗？相应数据也会被删除');
		if(flag==true){
			var class_id = "";
			for(var j=0;j<checkboxs.length;j++){
				if(checkboxs[j].checked==true){
					var parents=checkboxs.eq(j).parent().parent();
					var data_type = parents.attr('data-type');
					
					class_id += parents.attr('data-id')+",";	
					//console.log(parents.attr('data-id')+'-------'+parents.attr('data-type'));
					
					parents.remove();
				}
			}
			_delete_ajax(class_id,data_type,parents);	
		}
	});
}
function PreviewImage(fileObj,imgPreviewId,divPreviewId){
    var allowExtention=".jpg,.bmp,.gif,.png";//允许上传文件的后缀名document.getElementById("hfAllowPicSuffix").value;
    var extention=fileObj.value.substring(fileObj.value.lastIndexOf(".")+1).toLowerCase();            
    var browserVersion= window.navigator.userAgent.toUpperCase();
    if(allowExtention.indexOf(extention)>-1){ 
        if(fileObj.files){//HTML5实现预览，兼容chrome、火狐7+等
            if(window.FileReader){
                var reader = new FileReader(); 
                reader.onload = function(e){
                    document.getElementById(imgPreviewId).setAttribute("src",e.target.result);
                }  
                reader.readAsDataURL(fileObj.files[0]);
            }else if(browserVersion.indexOf("SAFARI")>-1){
                alert("不支持Safari6.0以下浏览器的图片预览!");
            }
        }else if (browserVersion.indexOf("MSIE")>-1){
            if(browserVersion.indexOf("MSIE 6")>-1){//ie6
                document.getElementById(imgPreviewId).setAttribute("src",fileObj.value);
            }else{//ie[7-9]
                fileObj.select();
                if(browserVersion.indexOf("MSIE 9")>-1)
                    fileObj.blur();//不加上document.selection.createRange().text在ie9会拒绝访问
                var newPreview =document.getElementById(divPreviewId+"New");
                if(newPreview==null){
                    newPreview =document.createElement("div");
                    newPreview.setAttribute("id",divPreviewId+"New");
                    newPreview.style.width = document.getElementById(imgPreviewId).width+"px";
                    newPreview.style.height = document.getElementById(imgPreviewId).height+"px";
                    newPreview.style.border="solid 1px #d2e2e2";
                }
                newPreview.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale',src='" + document.selection.createRange().text + "')";                            
                var tempDivPreview=document.getElementById(divPreviewId);
                tempDivPreview.parentNode.insertBefore(newPreview,tempDivPreview);
                tempDivPreview.style.display="none";                    
            }
        }else if(browserVersion.indexOf("FIREFOX")>-1){//firefox
            var firefoxVersion= parseFloat(browserVersion.toLowerCase().match(/firefox\/([\d.]+)/)[1]);
            if(firefoxVersion<7){//firefox7以下版本
                document.getElementById(imgPreviewId).setAttribute("src",fileObj.files[0].getAsDataURL());
            }else{//firefox7.0+                    
                document.getElementById(imgPreviewId).setAttribute("src",window.URL.createObjectURL(fileObj.files[0]));
            }
        }else{
            document.getElementById(imgPreviewId).setAttribute("src",fileObj.value);
        }         
    }else{
        alert("仅支持"+allowExtention+"为后缀名的文件!");
        fileObj.value="";//清空选中文件
        if(browserVersion.indexOf("MSIE")>-1){                        
            fileObj.select();
            document.selection.clear();
        }                
        fileObj.outerHTML=fileObj.outerHTML;
    }
}
 


