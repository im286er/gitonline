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
	    var type =  $(this).attr("data-type");
		var id = $(this).attr("id");
		
		window.parent.edit(type,id);	
	});
	
	
}); 