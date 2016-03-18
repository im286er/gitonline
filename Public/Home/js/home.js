// page1
var oPage1Slide = $(".page1-slide ul");
var oWidth = $(".page1-slide").width();
var aBullets = $(".slide-bullets span");

var index1 = 0;
var left1 = 0;
var timer1;
function page1Autoplay() {
	index1++;
	if(index1 > 1) {
		index1 = 0;
	}
	page1Slide(index1);
}
function page1Slide(index) {
	left1 = -oWidth*index;
	oPage1Slide.animate({'left': left1+'px'}, '400');
	aBullets.removeClass('active');
	aBullets.eq(index1).addClass('active');
}

var timer1 = setInterval(page1Autoplay, 3000);

aBullets.click(function(event) {
	clearInterval(timer1);
	var _this = $(this);
	index1 = _this.index();
	page1Slide(index1);
	timer1 = setInterval(page1Autoplay, 3000);
});

// page3
var background = {
	shop_background_pos: 0,
	shop_background_pos_time: null,
	rode_background_pos: 0,
	rode_background_pos_time: null,
	tree_background_pos: 0,
	tree_background_pos_time: null,
	people_position_time: null,
	people_position_now: 0,
	people_position_img: ['/Public/Home/images/right-0.png', '/Public/Home/images/right-1.png', '/Public/Home/images/right-0.png','/Public/Home/images/right-2.png'],
	stop_background_post: [480, 1050, 1660, 2200],
	window_clear_post1: null,
	window_clear_post2: null,
	window_clear_post3: null,
	window_firest_bool: false,

	StartTreeBackgroundPos: function() {
		background.tree_background_pos_time = setInterval(
			function() {
				if( background.tree_background_pos > 1800 ) background.tree_background_pos = 0;
				$(".tree").css({"background-position":"-"+background.tree_background_pos+"px 0px"});
				background.tree_background_pos ++;
			}, 6
		);
	},
	StartShopBackgroundPos: function() {
		background.shop_background_pos_time = setInterval(
			function() {
				if( background.shop_background_pos > 2250 ) background.shop_background_pos = 0;
				$(".shop").css({"background-position":"-"+background.shop_background_pos+"px 0px"});
				background.shop_background_pos ++;
			}, 10
		);
	},
	StartRodeBackgroundPos: function() {
		background.rode_background_pos_time = setInterval(
			function() {
				if( background.rode_background_pos > 2000 ) background.rode_background_pos = 0;
				$(".line-rode").css({"background-position":"-"+background.shop_background_pos+"px 0px"});
				background.rode_background_pos ++;
			}, 5
		);
	},
	StartPeoplePos: function() {
		background.people_position_time = setInterval(
			function() {
				document.getElementById("obj").src = background.people_position_img[background.people_position_now];
				if( background.people_position_now >= background.people_position_img.length-1 ) {
					background.people_position_now = 0;
				} else {
					background.people_position_now ++;
				}
			}, 400
		)
	},
	WindowStart: function() {
		setTimeout(function(){ $(".page2-txt").animate({bottom:"75%",opacity:"1"}, 450); }, 10);
		if( background.window_firest_bool ) {
			setTimeout(function(){ $(".page2-slide").animate({bottom:"0"}, 750); }, 10);
		}
		setTimeout(function(){ $(".people").animate({bottom:"50px",}, 450); }, 10);
	},
	//动画初始化
	WindowStop: function() {
		clearInterval( background.shop_background_pos_time );
		clearInterval( background.tree_background_pos_time );
		clearInterval( background.people_position_time );
		clearInterval( background.rode_background_pos_time );
		clearTimeout( background.window_clear_post1 );
		clearTimeout( background.window_clear_post2 );
		clearTimeout( background.window_clear_post3 );
		background.window_firest_bool = true;
		$("div[id^='portal']").css({display:"none"});
		document.getElementById("obj").src = '/Public/Home/images/right-0.png';
	},
	IsStop: function() {
		var isstop = background.InStop();
		if( isstop !== false ) {
			background.ScrollStop( isstop );
		}
	},
	InStop: function() {
		for(s = 0; s < background.stop_background_post.length; s ++) {
			if( background.stop_background_post[s] == background.shop_background_pos ) {
				return s;
			}
		}
		return false;
	},
	ScrollStart: function(bool) {
		if(bool) background.WindowStart();
		background.window_clear_post3 = setTimeout(function() {
			background.StartTreeBackgroundPos();
			background.StartShopBackgroundPos();
			background.StartRodeBackgroundPos();
		    background.StartPeoplePos();
		},2000)
		setInterval(function() { background.IsStop(); }, 1);
	},
	ScrollStop: function( isstop ) {
		clearInterval( background.shop_background_pos_time );
		clearInterval( background.tree_background_pos_time );
		clearInterval( background.people_position_time );
		clearInterval( background.rode_background_pos_time );
		document.getElementById("obj").src = '/Public/Home/images/right-3.png';

		background.shop_background_pos += 2; //一次移动2px，跳过此停止
		document.getElementById("portal"+isstop).style.display = "";
		background.window_clear_post1 = setTimeout(function() { background.ScrollStart(false); }, 1000)
		background.window_clear_post2 = setTimeout(function() { document.getElementById("portal"+isstop).style.display = "none"; }, 3000)
	}
}

// var scrollobj = Object.create(background);

// page4开始
function xx_4() {
	//出场动画
	setTimeout(function(){
		$(".os-txt1").animate({bottom:"75%",opacity:"1"},750);
	},0);
	setTimeout(function(){
		$(".icon1>img").animate({height:"180px",width:"180px", opacity:"1",marginLeft:"0px", marginTop:"0px"},750);
	},1000);
	setTimeout(function(){
		$(".icon2>img").animate({height:"180px",width:"180px", opacity:"1",marginLeft:"253px", marginTop:"0px"},750);
		$(".title1").animate({bottom:"-40%", opacity:"1"},750);
	},1500);
	setTimeout(function(){
		$(".icon3>img").animate({height:"180px",width:"180px", opacity:"1",marginLeft:"506px", marginTop:"0px"},750);
		$(".title2").animate({bottom:"-40%", opacity:"1"},750);
	},2000);
	setTimeout(function(){
		$(".icon4>img").animate({height:"180px",width:"180px", opacity:"1",marginLeft:"759px", marginTop:"0px"},750);
		$(".title3").animate({bottom:"-40%", opacity:"1"},750);
	},2500);
	setTimeout(function(){
		$(".content1").animate({bottom:"-80%", opacity:"1"},750);
	},2000);
	setTimeout(function(){
		$(".content2").animate({bottom:"-80%", opacity:"1"},750);
	},2500);
	setTimeout(function(){
		$(".content3").animate({bottom:"-80%", opacity:"1"},750);
	},3000);
	setTimeout(function(){
		$(".title4").animate({bottom:"-40%", opacity:"1"},750);
	},2500);
	setTimeout(function(){
		$(".content4").animate({bottom:"-80%", opacity:"1"},750);
	},3000);
	//鼠标浮上去
	$(".pic01").mouseover(function(){
		$(this).animate({width:"0",height:"0", marginTop:"90px", marginLeft:"90px"})
			.attr("src","/Public/Home/images/b.png").stop(true,true)
			.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"0"});
	});
	$(".pic02").mouseover(function(){
		$(this).animate({width:"0",height:"0", marginTop:"90px", marginLeft:"343px"})
			.attr("src","/Public/Home/images/5.png").stop(true,true)
			.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"253px"},"1000");
	});
	$(".pic03").mouseover(function(){
		$(this).animate({width:"0",height:"0", marginTop:"90px", marginLeft:"596px"})
			.attr("src","/Public/Home/images/6.png").stop(true,true)
			.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"506px"});
	});
	$(".pic04").mouseover(function(){
		$(this).animate({width:"0",height:"0", marginTop:"90px", marginLeft:"849px"})
			.attr("src","/Public/Home/images/3.png").stop(true,true)
			.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"759px"});
	});
	//鼠标离开
	$(".pic01").mouseout(function(){
		$(this).animate({width:"0px",height:"0px", marginTop:"90px", marginLeft:"90px"},
			function(){
				$(".pic01").attr("src","/Public/Home/images/7.png")
					.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"0"});;
			});
	});
	$(".pic02").mouseout(function(){
		$(this).animate({width:"0px",height:"0px", marginTop:"90px", marginLeft:"343px"},
			function(){
				$(".pic02").attr("src","/Public/Home/images/1.png")
					.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"253px"},"1000");
			});
	});
	$(".pic03").mouseout(function(){
		$(this).animate({width:"0px",height:"0px", marginTop:"90px", marginLeft:"596px"},
			function(){
				$(".pic03").attr("src","/Public/Home/images/2.png")
					.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"506px"},"1000");
			});
	});
	$(".pic04").mouseout(function(){
		$(this).animate({width:"0px",height:"0px", marginTop:"90px", marginLeft:"849px"},
			function(){
				$(".pic04").attr("src","/Public/Home/images/4.png")
					.animate({width:"180px",height:"180px", marginTop:"0", marginLeft:"759px"});;
			});
	});
};


//page8开始
function xx_8() {
	setTimeout(function(){
		$(".os-txt1").animate({bottom:"75%",opacity:"1"},750);
	},100);
	setTimeout(function(){
		$(".case").animate({bottom:"100px",opacity:"1"},750);
	},100);
	setTimeout(function(){
		$(".logo1").animate({width:"122px",height:"100px",marginLeft:"0",marginTop:"220px"},300).hover(function() {
            $(this).animate({width:"140px",height:"115px",marginLeft:"0",marginTop:"212px"});
        });
		$(".logo1").mouseleave(function() {
            $(this).animate({"width":"122px","height":"100px","marginLeft":"0","marginTop":"220px"});
        });
	},1100);
	setTimeout(function(){
		$(".logo2").animate({width:"122px",height:"100px",marginLeft:"0",marginTop:"220px"},300).hover(function() {
            $(this).animate({"width":"140px","height":"115px","marginLeft":"0","marginTop":"212px"});
        });
		$(".logo2").mouseleave(function() {
            $(this).animate({"width":"122px","height":"100px","marginLeft":"0","marginTop":"220px"});
        });
	},1400);
    setTimeout(function(){
		$(".plus").animate({width:"104px",height:"75px",marginLeft:"-30px",marginTop:"232px"},200)
	},1700);
};


//page9开始
 $(function() {
    $('.nine-rig ul li').hover(function(){
		$('.nine-rig ul').find('li').removeClass('current');
			$(this).addClass('current');
	});
	$(".aboutus").mousemove(function(){
		$(this).addClass('current');
		$(".one-n").css("display","block");
		$(".two-n").hide();
		$(".three-n").hide();
	});
	$(".culture").mousemove(function(){
		$(this).addClass('current');
		$(".one-n").hide().animate({top:"-500px"});
		$(".two-n").show();
		$(".three-n").hide().animate({top:"-500px"});
	});
	$(".honor").mousemove(function(){
		$(this).addClass('current');
		$(".one-n").hide().animate({top:"-500px"});
		$(".two-n").hide().animate({top:"-500px"});
		$(".three-n").show().fadeIn();
	});
});