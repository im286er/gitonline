var turnplate={
		restaraunts:[],				//大转盘奖品名称
		colors:[],					//大转盘奖品区块对应背景颜色
		outsideRadius:192,			//大转盘外圆的半径
		textRadius:155,				//大转盘奖品位置距离圆心的距离
		insideRadius:68,			//大转盘内圆的半径
		startAngle:0,				//开始角度
		bRotate:false				//false:停止;ture:旋转
};

//$(function(){
	//动态添加大转盘的奖品与奖品区域背景颜色
	turnplate.restaraunts = prize_list;
	turnplate.colors = ["#FFF4D6", "#FFFFFF", "#FFF4D6", "#FFFFFF","#FFF4D6", "#FFFFFF", "#FFF4D6", "#FFFFFF","#FFF4D6", "#FFFFFF"];

	
	var rotateTimeOut = function (){
		$('#wheelcanvas').rotate({
			angle:0,
			animateTo:2160,
			duration:8000,
			callback:function (){
				alert('网络超时，请检查您的网络设置！');
			}
		});
	};

	//旋转转盘 item:奖品位置; txt：提示语;
	var rotateFn = function (item, data){
		var angles = item * (360 / turnplate.restaraunts.length) - (360 / (turnplate.restaraunts.length*2));
		if(angles<270){
			angles = 270 - angles; 
		}else{
			angles = 360 - angles + 270;
		}
		$('#wheelcanvas').stopRotate();
		$('#wheelcanvas').rotate({
			angle:0,
			animateTo:angles+1800,
			duration:8000,
			callback:function (){
				//alert(txt);
				ShowResults(data);
				turnplate.bRotate = !turnplate.bRotate;
				$('#pointer').attr('src','/Public/Mobile/default/img/turnplate-pointer.png');
			}
		});
	};

	$('#pointer').click(function (){
		if(opentype == 'flapp'){
			checkLogin(linkurl,'choujiang(response)');
		}else{
			if(mid > 0){
				choujiang(1);
			}else{
				location.href = loginurl;
			}
		}
	});
	$('.btn_again').click(function (){		
		$("#ShowPrizeResults").hide();	
		if(opentype == 'flapp'){
			checkLogin(linkurl,'choujiang(response)');
		}else{
			if(mid > 0){
				choujiang(1);
			}else{
				location.href = loginurl;
			}
		}
	});
	function choujiang(response){
		if(response && $('#pointer').attr('src') == '/Public/Mobile/default/img/turnplate-pointer.png'){
			$.post("/DZP/getPrize", {utk:response,jid:jid},function(data){
				$("#last_num").html('今日您还有'+data.last_num+'次抽奖机会');
				if(data.code == 1){
					if(turnplate.bRotate)return;
					turnplate.bRotate = !turnplate.bRotate;
					$('#pointer').attr('src','/Public/Mobile/default/img/turnplate-pointer_ed.png');
					rotateFn(data.k+1 , data);				
				}else{
					$(".results-content").removeClass("succeed").addClass("fail");
					$(".results-txt").html('对不起,您今天的抽奖次数已经用完了!');
					$("#ShowPrizeResults").show();
				}
			},"json");
		}
	}
//});

function rnd(n, m){
	var random = Math.floor(Math.random()*(m-n+1)+n);
	return random;
	
}


function drawRouletteWheel() {    
  var canvas = document.getElementById("wheelcanvas");    
  if (canvas.getContext) {
	  //根据奖品个数计算圆周角度
	  var arc = Math.PI / (turnplate.restaraunts.length/2);
	  var ctx = canvas.getContext("2d");
	  //在给定矩形内清空一个矩形
	  ctx.clearRect(0,0,422,422);
	  //strokeStyle 属性设置或返回用于笔触的颜色、渐变或模式  
	  ctx.strokeStyle = "#FFBE04";
	  //font 属性设置或返回画布上文本内容的当前字体属性
	  ctx.font = '16px Microsoft YaHei';      
	  for(var i = 0; i < turnplate.restaraunts.length; i++) {       
		  var angle = turnplate.startAngle + i * arc;
		  ctx.fillStyle = turnplate.colors[i];
		  ctx.beginPath();
		  //arc(x,y,r,起始角,结束角,绘制方向) 方法创建弧/曲线（用于创建圆或部分圆）    
		  ctx.arc(211, 211, turnplate.outsideRadius, angle, angle + arc, false);    
		  ctx.arc(211, 211, turnplate.insideRadius, angle + arc, angle, true);
		  ctx.stroke();  
		  ctx.fill();
		  //锁画布(为了保存之前的画布状态)
		  ctx.save();   
		  
		  //----绘制奖品开始----
		  ctx.fillStyle = "#E5302F";
		  var text = turnplate.restaraunts[i];
		  var line_height = 17;
		  //translate方法重新映射画布上的 (0,0) 位置
		  ctx.translate(211 + Math.cos(angle + arc / 2) * turnplate.textRadius, 211 + Math.sin(angle + arc / 2) * turnplate.textRadius);
		  
		  //rotate方法旋转当前的绘图
		  ctx.rotate(angle + arc / 2 + Math.PI / 2);
		  
		  /** 下面代码根据奖品类型、奖品名称长度渲染不同效果，如字体、颜色、图片效果。(具体根据实际情况改变) **/
		  if(text.length>6){//奖品名称长度超过一定范围 
			  text = text.substring(0,6)+"||"+text.substring(6);
			  var texts = text.split("||");
			  for(var j = 0; j<texts.length; j++){
				  ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
			  }
		  }else{
			  //在画布上绘制填色的文本。文本的默认颜色是黑色
			  //measureText()方法返回包含一个对象，该对象包含以像素计的指定字体宽度
			  ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
		  }
		  
		  //把当前画布返回（调整）到上一个save()状态之前 
		  ctx.restore();
		  //----绘制奖品结束----
	  }     
  } 
}

function ShowResults(data){
	if(data.ptype==0){
		$(".results-content").removeClass("succeed").addClass("fail");
		$(".results-txt").html('姿势不对？换个姿势试试~!');
	
	}else{
		$(".results-content").removeClass("fail").addClass("succeed");
		$(".results-txt").html('抽中了'+data.pname+'<br>'+'逆天了有没有！');
		
	}
	$("#ShowPrizeResults").show();
}
$('.j-close-results').click(function(){
	$("#ShowPrizeResults").hide();
})
$('.header-right').click(function(){
	if(opentype == 'flapp'){
		checkLogin(linkurl2,'myprize(response)');
	}else{
		if(mid > 0){
			myprize(1);
		}else{
			location.href = loginurl2;
		}
	}
})
$('.btn_share').click(function(){
	if(opentype == 'flapp'){
		checkLogin(linkurl,'btn_share(response)');
	}else{
		if(mid > 0){
			btn_share(1);
		}else{
			location.href = loginurl;
		}
	}
})
//分享
function btn_share(utoken){
	if(utoken){
		var str1 = '{ "type": "shareDZP", "dzp_jid": "'+jid+'", "dzp_mid": "'+mid+'","sid":"0" }';
		interactive(str1);
	}
}
$(document).ready(function(){
	drawRouletteWheel();
});