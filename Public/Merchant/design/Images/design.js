$(function(){
    $(".editbox").each(function(){
	    addmenu($(this));
	});
	
	function addmenu(dm)
	{
		var type = dm.attr("data-type");
		var id = dm.attr("id");
		
		dm.append(" <div class='editmenu' data-type='"+type+"' data-id='"+id+"'  >修改</div>");
		 
	}
	
	$(".editmenu").click(function(){
		
	    var type = $(this).attr("data-type");
		var id   = $(this).attr("data-id");
		var cid  = $('#cid').val();
		var sid  = $('#sid').val();
		
		window.parent.edit(type,id,sid,cid);	
	});
	
	
	/*单个商品*/
	
	$(".editboxitem").each(function(){
	    additem($(this));
	});
	
	function additem(dm)
	{
		var type = dm.attr("data-type");
		var id = dm.attr("id");
		
		dm.append(" <div class='edititemmenulist'><div class='edititemmenu' data-type='"+type+"' data-id='"+id+"' data-action='edit'  ><i class='glyphicon glyphicon-edit'></i> 编辑</div><div class='edititemmenu' data-type='"+type+"' data-id='"+id+"' data-action='add'  ><i class='glyphicon glyphicon-plus-sign'></i> 添加</div><hr /><div class='edititemmenu text-center' data-type='"+type+"' data-id='"+id+"' data-action='delete'  ><i class='glyphicon glyphicon-trash'></i></div></div>");
		 
	}
	
	
	
	$(".edititemmenu").click(function(){
		
	    var type =  $(this).attr("data-type");
		var id = $(this).attr("data-id");
		var action = $(this).attr("data-action");
		var sid  = $('#sid').val();
		var cid  = $('#cid').val();
		
		if (action == 'delete') {
			if (confirm("确定删除？")) {
				if (type == 'indexproone') {
					var content = "/Sales/deleGoods.html";
				}else if (type == 'activity') {
					var content = "/Message/delhd.html";
				}else if (type == 'coupon') {
					var content = "/Sales/deljq.html";
				}else if (type == 'video') {
					var content = "/Info/delGoods.html";
				}
				
				$.post(content, { 'id':id}, function(data){
					$('.sales'+id).remove();
				});
			}else{
				return false;
			}
		}else{
			window.parent.edititem(type,id,action,sid,cid);
		}

	});
	
	
	
	
}); 