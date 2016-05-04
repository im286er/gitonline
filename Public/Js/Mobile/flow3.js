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
		//var gprice = g_info.gdprice > 0 ? g_info.gdprice : g_info.goprice;
		this.cartProduct[goods_id] = {gid:g_info.gid,number:1};
		this.cartostring();
	},
	updateProduct: function(goods_id,number){
		if(number == 0){
			delete this.cartProduct[goods_id];
		}else{
			this.cartProduct[goods_id].number = number;
		}
		this.cartostring();
	},
	saveCart: function(){
		//$.cookie("cart", JSON.stringify(this.cartProduct));
		this.refreshCart();
	},
	refreshCart: function(){
		var cartNumber = 0;
		var cartPrice = 0;
		$.each(this.cartProduct,function(index,o){
			var gprice = goods_info[o.gid].gdprice > 0 ? goods_info[o.gid].gdprice : goods_info[o.gid].goprice;
			cartNumber += parseInt(o.number);
			cartPrice  += parseFloat(o.number*gprice);
		})
		cartPrice = cartPrice.toFixed(2);
		$(".cartNumber").html(cartNumber);
		$(".cartPrice").html("￥"+cartPrice);
	},

	inCart: function() {
		var $this = this;
		var ff = $.cookie("ProductList");
		if( ff ) {
			$.each(ff.split("|"), function(index, ostr) {
				var opro = ostr.split("_");
				if(goods_info[opro[0]] != undefined) ( $this.cartProduct[opro[0]]={gid:opro[0], number:opro[1]} );
			})
			setTimeout(function() { $this.refreshCart(); }, 200);
		}
		return true;
	},
	cartostring : function () {
		$.cookie("ProductList", '', {path:'/'});
		var string = "";
		$.each(this.cartProduct, function(index, o) {
			string += o.gid+"_"+o.number+"|";
		})

		$.cookie("ProductList", string, {path:'/'});
	},
}


function getProductContent(cat,key){
	// var ff = $.cookie("ProductList");
	// alert(ff);
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
				cartObj.inCart();
				updateListNumber();
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
					cartObj.inCart();
					updateListNumber();
				}
			}
		}
	});
}

function addClickEvt() {
	$(".btn_left").unbind('click');
	$(".btn_right").unbind('click');

	$(".btn_left").click(function () {
		var goods_id = $(this).attr('gid');
		var old_number = $(".gnum_" + goods_id).html();

		if (old_number == undefined || old_number == '') {
			old_number = $(".gnum_" + goods_id).html();
		}

		if (old_number <= 0) {
			return false;
		}

		old_number--;
		if (old_number == 0){
			$(".gnum_" + goods_id).prev('img').css('display','none');
			old_number = '';
		}
		
		$(".gnum_" + goods_id).html(old_number);
		cartObj.updateCart(goods_id, old_number);
	});

	$(".btn_right").click(function () {
		var goods_id = $(this).attr('gid');
		var old_number = $(".gnum_" + goods_id).html();

		if (old_number == undefined || old_number == '') {
			old_number = $(".gnum_" + goods_id).html();
		}

		if (goods_info[goods_id].gstock != '-1' && old_number >= parseInt(goods_info[goods_id].gstock)) {
			return false;
		}

		old_number++;

		if (old_number > 0){
			$(".gnum_" + goods_id).prev('img').css('display','block');
		}
		$(".gnum_" + goods_id).html(old_number);
		cartObj.updateCart(goods_id, old_number);
	});
}


function updateListNumber(){
	$.each(cartObj.cartProduct,function(index,o){
		var m = $(".gnum_"+o.gid);
		if(m != undefined){
			m.css('display','show');
			m.prev('.btn_left').css('display','block');
			m.html(o.number);
		}
	})
}
