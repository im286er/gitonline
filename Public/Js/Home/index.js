$(function  () {
	/*$('#test1').mousewheel(function(event, delta, deltaX, deltaY) { // event : 事件 // delta : 滚动数量 正值向上，负值向下 }*/
	var siz=0;
	var flag=true;
	    flag1=true;
		hi=$(window).height();
	var le=$('#content li').length;
		$('html,body').mousewheel(function(event, delta, deltaX, deltaY){
			
		        if (delta<0&&flag) {
					flag=false;
					siz=$(window).scrollTop()>hi*(le-1)?hi*(le-1):hi+$(window).scrollTop();
					
					$('html,body').stop().animate({scrollTop:siz},time,function  () {
						flag=true;
					})
		        }else{
					if (flag) {
						flag=false;
                       siz=$(window).scrollTop()<hi*2?0:$(window).scrollTop()-hi;
					   $('html,body').stop().animate({scrollTop:siz},time,function  () {
						flag=true;
					})
					}
					}
					return false;
		})
			$('html,body').keydown(function  (event) {
			     if(event.keyCode==40&&flag){
				   flag=false;
					siz=$(window).scrollTop()>hi*(le-1)?hi*(le-1):hi+$(window).scrollTop();
					
					$('html,body').stop().animate({scrollTop:siz},time,function  () {
						flag=true;
					})
				 }else if(event.keyCode==38&&flag) {
					if (flag) {
						flag=false;
                       siz=$(window).scrollTop()<hi*2?0:$(window).scrollTop()-hi;
					   $('html,body').stop().animate({scrollTop:siz},time,function  () {
						flag=true;
					})
					}
				 }
				 
				 return false;
			})
				$(window).resize(function  () {
				    $(window).scrollTop((siz/hi)*$(window).height());
					siz=$(window).scrollTop();
					hi=$(window).height();
				}).scroll(function  () {
					if ($(window).scrollTop()>0&&flag1) {
						flag1=false;
						$('#back').show().hover(function  () {
							$(this).css({background:'none'})
						},function  () {
                            $(this).css({background:'#ccc'})
						}).stop().animate({borderRadius:'50%',opacity:1},1000).click(function  () {
								$('html,body').stop().animate({scrollTop:0},1000,function  () {
									flag1=true;
								})
						});
					}else {
                        if ($(window).scrollTop()==0) {
							$('#back').stop().animate({opacity:0},500,function  () {
								flag1=true;
							$('#back').css({borderRadius:0}).hide();
							})
                        }
						
					}
					
					
				})
})