$("#addMenu").on('click',function(){
	var str_data = $('#addMenuForm').serialize() + "&gimg=" + $("#preview img").attr("src");
	$.ax(
		addmenu_url, 	// 请求地址
		str_data, 		// POST数据
		null,
		"post",   		//传输方式
		"json",   		//返回数据类型
		function(data) {
			   if(data.msg == "update") {
				   addMenu(data,"update");
				   modalShow($('.addshopp'),$('#addShopp'));
				   deleteFun($(".btndelete"));
			   } else if(data != "no") {
				   addMenu(data,"add");
				   modalShow($('.addshopp'),$('#addShopp'));
				   deleteFun($(".btndelete"));
			   } else {
				   alert("操作失败，请重试！");
			   }
		}, function(){ alert("操作失败，请重试！"); }
	);		
	$(".modal").fadeOut();
});
	
function addMenu(data, type){
	var html = '<tr data-id="'+data.gid+'" data-type="product" data-gtype="'+data.gtype+'" data-cid="'+data.cid+'" data-order="'+data.gorder+'">';
	html += '<td class="vertical" width="60px"><input type="checkbox"></td>';
	html += '<td width="210px">';
	html += '<dl class="table-content">';
	html += '<dt class="pull-left"><img src="'+root_path+'/'+data.gimg+'" alt=""></dt>';
	html += '<dd class="pull-left">	';
	html += '			<p class="gname">'+data.gname+'</p>';
	html += '			<p class="c-a2a2a6 gdescription">'+data.gdescription+'</p>';
	html += '			<span class="c-bf242a">活动价￥<span class="gdprice" data-goprice="'+data.goprice+'">'+data.gdprice+'</span></span>';
 
	html += '		</dd>';
	html += '	</dl>';
	html += '</td>';
	html += '<td width="100px" class="vertical ">库存:<span class="gstock">'+data.gstock+'</span></td>';
	html += '<td class="vertical">';
	html += ' 	<input type="button" value="修改" class="addshopp">';
	html += '	<input type="button" value="删除" class="btndelete">'; 
	html += '</td>';
	html += '</tr>';
	if(type == "add"){
		$(".tables tbody").eq(0).prepend(html);
	}
	else if(type == "update"){
		$(".tables tbody tr[data-id="+data.gid+"]").remove();
		$(".tables tbody").eq(0).prepend(html);
	}
}



/*
 * Eric 修改
 */
var clickSaveCancel={
		Save:function(){
			$('#save').click(function(){
				if($("#name").val() == ""){
					alert("请输入内容！");
					return false;
				}
				$.ax(
						addclass_url,
						{cid:class_id,cname:$("#name").val()},
		                null,
		                "post",
		                "json", 
		                function(data){
							if(data.msg == "update"){
								var node =  $(".list li[data-id='"+class_id+"']");
				        		$(".list li[data-id='"+class_id+"']").text($("#name").val());
				        	   }
							else if(data != "no"){
								var html = '<li data-id="'+ data.cid +'" data-type="manage" data-order="'+ data.order +'"><a href="javaScript:void(0)">' + data.cname + '</a></li>';
								$(".list").append(html);
								$("#cid").append('<option value="'+data.cid+'">'+data.cname+'</option>');
							}
							else{
								alert("操作失败，请重试！");	        		  
							}
							$('#modal').fadeOut();
		                }, 
		                function(){
		                    alert("操作失败，请重试！");
		                }
		            );
				
			});
		},
		Cancel:function(){
			$('#btncancel').click(function(){
				$('#modal').fadeOut();
			});
		},
		getTableFun:function(){
			$('#list li').on('click','a',function(){
				var getIndex,getParent=$(this).parent();
				getIndex=$(getParent).index();
				$('#getTable table').hide();
				$('#getTable table').eq(getIndex).show();
			});
		},
		clickSure:function(){
			$(".btn").on('click',function(){
				var dit = $(this).parent().attr('data-bid');
				var bimg = $("#imgHeadPhoto"+dit).attr("src");
				$(this).parent().find(".bimg"+dit).text(bimg);
			});
		},
		clickSubmit:function(){
			$('#btnSubmit').on('click',function(){
				var mVal_s=$('input[name="mbdzh1"]').val();
				var mVal=$('input[name="mbdzh"]').val();
				if(mVal_s==mVal){
					return true;
				}else{
					alert('支付宝账号输入有误，请重新输入');
					return false;
				}
			});
		}
};

$("#finishs").click(function(){
	var tr = $(".tables tbody tr");
	var post_data="";
	for(var i=0;i<tr.length;i++){
		post_data+=tr.eq(i).attr("data-id") + "-" + tr.eq(i).find(".addtxt").val()+";";
		tr.eq(i).attr("data-order", tr.eq(i).find(".addtxt").val()) ;
	}
	$.ax(
			reordermune_url,
			{str:post_data},
            null,
            "post",
            "text", 
            function(data){
				if(data == "yes"){
					history.go(0);
	        	}
            }, 
            function(){
                //alert("操作失败，请重试！");
            }
        );
});

$("#finish").click(function(){
	var list = $(".list li");
	var post_data="";
	for(var i=0;i<list.length;i++){
		post_data+=list.eq(i).attr("data-id") + "-" + list.eq(i).children("input").val()+";";
		list.eq(i).attr("data-order", list.eq(i).children("input").val()) ;
	}
	$.ax(
			reorderclass_url,
			{str:post_data},
            null,
            "post",
            "text", 
            function(data){
				if(data == "yes"){
					var ols = new Object();
					var ts = new Array();
					$(".list").find("li").each(function(i,v){
						var sp = $(v).attr("data-order");
						ts.push(sp);
						ols[sp] = $(v);
					});
					ts.sort(function(a, b){
						return a-b;
					});
					$(".list").empty();
					for(var k=0;k<ts.length;k++){
						ols[ts[k]].appendTo($(".list"));
					}
	        	}
            }, 
            function(){
                //alert("操作失败，请重试！");
            }
        );
});
/**
 * ajax封装
 * url 发送请求的地址
 * data 发送到服务器的数据，数组存储，如：{"date": new Date().getTime(), "state": 1}
 * async 默认值: true。默认设置下，所有请求均为异步请求。如果需要发送同步请求，请将此选项设置为 false。
 *       注意，同步请求将锁住浏览器，用户其它操作必须等待请求完成才可以执行。
 * type 请求方式("POST" 或 "GET")， 默认为 "GET"
 * dataType 预期服务器返回的数据类型，常用的如：xml、html、json、text
 * successfn 成功回调函数
 * errorfn 失败回调函数
 */
jQuery.ax=function(url, data, async, type, dataType, successfn, errorfn) {
    async = (async==null || async=="" || typeof(async)=="undefined")? "true" : async;
    type = (type==null || type=="" || typeof(type)=="undefined")? "post" : type;
    dataType = (dataType==null || dataType=="" || typeof(dataType)=="undefined")? "json" : dataType;
    data = (data==null || data=="" || typeof(data)=="undefined")? {"date": new Date().getTime()} : data;
    $.ajax({
        type: type,
        async: async,
        data: data,
        url: url,
        dataType: dataType,
        success: function(d){
            successfn(d);
        },
        error: function(e){
            errorfn(e);
        }
    });
};
