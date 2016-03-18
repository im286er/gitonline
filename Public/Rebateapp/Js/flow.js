var goods_info = {};
var cartObj = {
		cartProduct: {},
		updateCart: function(goods_id,number){
			if(!(goods_id in this.cartProduct) ){
				this.addProduct(goods_id,goods_info[goods_id]);
			}else{
				this.updateProduct(goods_id,number);
			}
			this.saveCart();
		},
		addProduct: function(goods_id,g_info){
			var gprice = g_info.gdprice > 0 ? g_info.gdprice : g_info.goprice;
			this.cartProduct[goods_id] = {gid:g_info.gid,number:1,gname:g_info.gname,gprice:gprice,gstock:g_info.gstock,yprice:g_info.goprice,gvrebate:g_info.gvrebate}; 
		},
		updateProduct: function(goods_id,number){
			if(number == 0){
				delete this.cartProduct[goods_id];
			}else{
				this.cartProduct[goods_id].number = number;	
			}
		},
		saveCart: function(){
			//$.cookie("cart", JSON.stringify(this.cartProduct));
			this.refreshCart();
		},
		refreshCart: function(){
			 var cartNumber = 0;
			 var cartPrice = 0;
			 $.each(this.cartProduct,function(index,o){
				 cartNumber += parseInt(o.number);
				 cartPrice  += parseFloat(o.number*o.gprice);
			 })
			cartPrice = cartPrice.toFixed(2);
			 $(".cartNumber").html(cartNumber);
			 $(".cartPrice").html("￥"+cartPrice);
		},
		inCart: function(){
			//var ff = $.cookie("cart");
			//this.cartProduct = eval('('+ ff +')');;
			//this.refreshCart();
		}
	}

function getProductContent(cat,key){
	$.ajax( {    
	    url:reqUrl,   
	    data:{    
	             cid : cat,    
	             key : key,
	    },    
	    type:'post',    
	    cache:false,    
	    dataType:'json',    
	    success:function(data) {    
	        if(data.msg =="true" ){
	        	goods_info = eval('('+ data.product +')');
	            $("#productContent").html(data.content);
	            addClickEvt();
	            updateListNumber();
	            $("#okBtn").html('选好了');
	        }
	     },
	     error:function(XMLHttpRequest, textStatus, errorThrown){
		    	//alert(JSON.stringify(XMLHttpRequest));
		    	if(XMLHttpRequest.status == '200'){
		    		var data = eval('('+ XMLHttpRequest.responseText +')');
		    		if(data.msg =="true" ){
			        	goods_info = eval('('+ data.product +')');
		    			//goods_info = data.product;
			            $("#productContent").html(data.content);
			            addClickEvt();
			            updateListNumber();
			            $("#okBtn").html('选好了');
			        }
		    	}
		 }
	});
}
function addClickEvt(){
	$(".btn_left").unbind('click');
	$(".btn_right").unbind('click');
	$(".pro_box").unbind('click');
	$(".showbox3").unbind('click');
	
	$(".btn_left").click(function(){
		var goods_id = $(this).attr('gid');
		var old_number = $("#number_"+goods_id).html();
		
		if(old_number == undefined){
			old_number = $(".gnum_"+goods_id).html();
		}
		
		if(old_number > 0){
			old_number--;
		}else{
			return false;
		}
		$(".gnum_"+goods_id).html(old_number);
		$("#pro_number").html(old_number);
		cartObj.updateCart(goods_id,old_number);
	});
	
	$(".btn_right").click(function(){
		var goods_id = $(this).attr('gid');
		var old_number = $("#number_"+goods_id).html();
		
		if(old_number == undefined){
			old_number = $(".gnum_"+goods_id).html();
		}
		
		if(goods_info[goods_id].gstock != '-1' && old_number >= parseInt(goods_info[goods_id].gstock)){
			return false;
		}
		
		old_number++;
		
		$(".gnum_"+goods_id).html(old_number);
		$("#pro_number").html(old_number);
		cartObj.updateCart(goods_id,old_number);
	});
	
	$(".pro_box").click(function(e){
		changeGoodsInfo(this);
		//e.stopPropagation(); 
		//$(".box1").removeClass("hide"); 
		//EV_modeAlert('envon');
		$("#modal-goodbox").show();
	});
	$('.showbox3').click(function(){
    	if(JSON.stringify(cartObj.cartProduct) == '{}'){
    		//alert("没有选中任何商品");
    		var chmsg = dialog({title: '提示',content: '没有选中任何商品',id: 'm1'});
    		chmsg.show();
    		return false;
    	}
    	$.cookie("cart", JSON.stringify(cartObj.cartProduct),{path:'/'});
    	location.href = flowUrl;
    });
}
function changeGoodsInfo(obj){
	var gid = $(obj).attr("gid");
	var gprice = goods_info[gid].gdprice > 0 ? goods_info[gid].gdprice : goods_info[gid].goprice;
	
	$("#pro_title").html(goods_info[gid].gname);
	if(goods_info[gid].gimg){
		$("#pro_img").attr("src",goods_info[gid].gimg);
	}
	$("#pro_price").html(gprice);
	var des = goods_info[gid].gdescription ? goods_info[gid].gdescription : '商品暂无描述';
	$("#pro_des").html(des);
	$(".btn_all").attr("gid",gid);
	var n = cartObj.cartProduct[gid] ? cartObj.cartProduct[gid].number : 0;
	$("#pro_number").html(n);
}
function updateListNumber(){
	$.each(cartObj.cartProduct,function(index,o){
		var m = $("#number_"+o.gid);
		if(m != undefined){
			m.html(o.number);
		}
	})
}

$(document).ready(function(){
       
        $('.showbox3').click(function(){
        	if(JSON.stringify(cartObj.cartProduct) == '{}'){
        		//alert("没有选中任何商品");
        		var chmsg = dialog({title: '提示',content: '没有选中任何商品',id: 'm1'});
        		chmsg.show();
        		return false;
        	}
        	$.cookie("cart", JSON.stringify(cartObj.cartProduct),{path:'/'});
        	location.href = flowUrl;
        });
        

 
    }); 