<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>页面提示</title>
<style type="text/css" media="screen">
* { padding:0; margin:0; font-size:12px; }
a:link, a:visited { text-decoration:none; color:#0068A6; }
a:hover, a:active { color:#FF6600; text-decoration:underline; }
.showMsg { border:1px solid #1E64C8; width:450px; height:172px; position:absolute; top:44%; left:50%; margin:-87px 0 0 -255px; text-align:center; }
.showMsg h5 { background:url("__PUBLIC__/Images/msg/msg.png") no-repeat 0 0; color:#FFF; padding-left:35px; height:25px; line-height:26px; *line-height:28px; overflow:hidden; font-size:14px; text-align:left; }
.showMsg .content { padding:46px 12px 10px 45px; font-size:14px; height:64px; text-align:left; }
.showMsg .bottom { background:#E4ECF7; margin:0 1px 1px; line-height:26px; *line-height:30px; height:26px; text-align:center; }
.showMsg .ok, .showMsg .guery { background:url("__PUBLIC__/Images/msg/msg_bg.png") no-repeat 0 -560px; }
.showMsg .guery { background-position:left -460px; }
.content.guery {display:inline-block; *display:inline; max-width:330px;}
#wait { padding-left:5px; }
</style>
</head>

<body>
<div class="showMsg">
    <h5>提示信息</h5>
    <present name="message" >
    	<div class="content guery">{$message}<b id="wait"><?php echo($waitSecond); ?></b></div>
    <else/>
    	<div class="content guery">{$error}<b id="wait"><?php echo($waitSecond); ?></b></div>
    </present>
    <div class="bottom"><a id="href" href="{$jumpUrl}">如果您的浏览器没有自动跳转，请点击这里</a></div>
    <if condition="isset($returnjs)"><script style="text/javascript">{$returnjs}</script></if>
</div>
<script type="text/javascript">
(function(){
	var wait = document.getElementById('wait'),href = document.getElementById('href').href;
	var interval = setInterval(function() {
		var time = --wait.innerHTML; if(time <= 0) { location.href = href; clearInterval(interval); };
	}, 1000);
})();
</script>
</body>
</html>

